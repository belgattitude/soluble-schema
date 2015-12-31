<?php
namespace Soluble\Schema\Source;

use Soluble\Schema\Exception;
use Soluble\Schema\Source;
use Soluble\Schema\Db\Wrapper\MysqlConnectionAdapter;
use ArrayObject;

class MysqlInformationSchema extends Source\AbstractSchemaSource
{
    /**
     * Schema name
     *
     * @var string
     */
    protected $schema;

    /**
     * @var MysqlConnectionAdapter
     */
    protected $adapter;


    /**
     * Whether to include full schema options like comment, collations...
     * @var boolean
     */
    protected $include_options = true;

    /**
     *
     * @var array
     */
    protected static $localCache = array();


    /**
     *
     * @var boolean
     */
    protected $useLocalCaching = true;

    /**
     *
     * @var array
     */
    protected static $fullyCachedSchemas = array();


    /**
     *
     * @var Mysql\MysqlDriverInterface
     */
    protected $driver;

    /**
     * Constructor
     *
     * @param \PDO|\mysqli $connection
     * @param string|null $schema default schema, taken from adapter if not given
     * @throws Exception\InvalidArgumentException for invalid connection
     * @throws Exception\InvalidUsageException thrown if no schema can be found.
     */
    public function __construct($connection, $schema = null)
    {
        try {
            $this->adapter = new MysqlConnectionAdapter($connection);
        } catch (Exception\InvalidArgumentException $e) {
            $msg = "MysqlInformationSchema requires a valid 'mysqli' or 'pdo:mysql' connection object ({$e->getMessage()}).";
            throw new Exception\InvalidArgumentException($msg);
        }

        if ($schema === null) {
            $schema = $this->adapter->getCurrentSchema();
            if ($schema === false || $schema == '') {
                $msg = "Database name (schema) parameter missing and no default schema set on connection";
                throw new Exception\InvalidUsageException($msg);
            }
        }

        $this->driver = new Mysql\MysqlDriver51($this->adapter, $schema);

        $this->setDefaultSchema($schema);
    }


    /**
     * {@inheritdoc}
     */
    public function getUniqueKeys($table, $include_primary = false)
    {
        $this->loadCacheInformation($table);
        $uniques = (array) self::$localCache[$this->schema]['tables'][$table]['unique_keys'];
        if ($include_primary) {
            try {
                $pks = $this->getPrimaryKeys($table);
                if (count($pks) > 0) {
                    $uniques = array_merge($uniques, array('PRIMARY' => $pks));
                }
            } catch (Exception\NoPrimaryKeyException $e) {
                // Ignore exception
            }
        }
        return $uniques;
    }


    /**
     * {@inheritdoc}
     */
    public function getIndexesInformation($table)
    {
        $this->loadCacheInformation($table);
        return self::$localCache[$this->schema]['tables'][$table]['indexes'];
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryKey($table)
    {
        $pks = $this->getPrimaryKeys($table);
        if (count($pks) > 1) {
            $keys = join(',', $pks);
            throw new Exception\MultiplePrimaryKeyException(__METHOD__ . ". Multiple primary keys found on table '{$this->schema}'.'$table':  $keys");
        }
        return $pks[0];
    }


    /**
     * {@inheritdoc}
     */
    public function getPrimaryKeys($table)
    {
        $this->loadCacheInformation($table);
        $pks = self::$localCache[$this->schema]['tables'][$table]['primary_keys'];
        if (count($pks) == 0) {
            throw new Exception\NoPrimaryKeyException(__METHOD__ . ". No primary keys found on table  '{$this->schema}'.'$table'.");
        }
        return $pks;
    }


    /**
     * {@inheritdoc}
     */
    public function getColumnsInformation($table)
    {
        $this->loadCacheInformation($table);
        return self::$localCache[$this->schema]['tables'][$table]['columns'];
    }


    /**
     * {@inheritdoc}
     */
    public function getForeignKeys($table)
    {
        $this->loadCacheInformation($table);
        return self::$localCache[$this->schema]['tables'][$table]['foreign_keys'];
    }

    /**
     * {@inheritdoc}
     */
    public function getReferences($table)
    {
        $this->loadCacheInformation($table);
        return self::$localCache[$this->schema]['tables'][$table]['references'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTablesInformation()
    {
        $this->loadCacheInformation(null);
        return self::$localCache[$this->schema]['tables'];
    }

    /**
     * Get a table configuration
     *
     * @throws Exception\ErrorException
     * @throws Exception\TableNotFoundException
     *
     * @param string $table table name
     * @param boolean|null $include_options include extended information
     * @return array
     */
    protected function getTableConfig($table, $include_options = null)
    {
        if ($include_options === null) {
            $include_options = $this->include_options;
        }

        $schema = $this->schema;

        if ($this->useLocalCaching &&
                isset(self::$localCache[$schema]['tables'][$table])) {
            return self::$localCache[$schema]['tables'][$table];
        }

        $config = $this->driver->getSchemaConfig($table, $include_options);

        if (!array_key_exists($table, $config['tables'])) {
            throw new Exception\TableNotFoundException(__METHOD__ . ". Table '$table' in database schema '{$schema}' not found.");
        }

        if ($this->useLocalCaching) {
            if (!array_key_exists($schema, self::$localCache)) {
                self::$localCache[$schema] = array();
            }
            self::$localCache[$schema] = array_merge_recursive(self::$localCache[$schema], (array) $config);
        }

        return $config['tables'][$table];
    }


    /**
     * Get schema configuration
     *
     * @throws Exception\ErrorException
     * @throws Exception\SchemaNotFoundException
     *
     * @param boolean|null $include_options include extended information
     * @return ArrayObject
     */
    public function getSchemaConfig($include_options = null)
    {
        if ($include_options === null) {
            $include_options = $this->include_options;
        }
        $schema = $this->schema;
        if ($this->useLocalCaching && in_array($schema, self::$fullyCachedSchemas)) {
            return self::$localCache[$schema];
        }

        $config = $this->driver->getSchemaConfig($table = null, $include_options);
        if (count($config['tables']) == 0) {
            throw new Exception\SchemaNotFoundException(__METHOD__ . " Error: schema '{$schema}' not found or without any table or view");
        }
        if ($this->useLocalCaching) {
            self::$localCache[$schema] = $config;
            self::$fullyCachedSchemas[] = $schema;
        }
        return $config;
    }

    /**
     *
     * @param string $table
     * @throws Exception\InvalidArgumentException
     * @throws Exception\TableNotFoundException
     *
     */
    protected function loadCacheInformation($table = null)
    {
        $schema = $this->schema;
        $this->checkTableArgument($table);

        if (!in_array($schema, self::$fullyCachedSchemas)) {
            if ($table !== null) {
                $this->getTableConfig($table);
            } else {
                $this->getSchemaConfig();
            }
        } elseif ($table !== null) {
            // Just in case to check if table exists
            $this->getTableConfig($table);
        }
    }

    /**
     * Clear local cache information for the current schema
     *
     * @throws Exception\InvalidArgumentException
     */
    public function clearCacheInformation()
    {
        $schema = $this->schema;
        if (array_key_exists($schema, self::$localCache)) {
            unset(self::$localCache[$schema]);
            if (($key = array_search($schema, self::$fullyCachedSchemas)) !== false) {
                unset(self::$fullyCachedSchemas[$key]);
            }
        }
    }
}
