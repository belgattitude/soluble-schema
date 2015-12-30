<?php
namespace Soluble\Schema\Source\Mysql;

use Soluble\Schema\Exception;
use Soluble\Schema\Source;
use Soluble\Schema\Db\Wrapper\MysqlConnectionAdapter;
use Zend\Config\Config;

class MysqlInformationSchema extends Source\AbstractSource
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
     * Used to restore innodb stats mysql global variable
     * @var string
     */
    protected $mysql_innodbstats_value;

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
     * Constructor
     * 
     * @param \PDO|\mysqli $connection
     * @param string $schema default schema, taken from adapter if not given
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

        $config = $this->getObjectConfig($table, $include_options);

        if (!array_key_exists($table, $config['tables'])) {
            throw new Exception\TableNotFoundException(__METHOD__ . ". Table '$table' in database schema '{$schema}' not found.");
        }

        if ($this->useLocalCaching) {
            if (!array_key_exists($schema, self::$localCache)) {
                self::$localCache[$schema] = array();
            }
            self::$localCache[$schema] = array_merge_recursive(self::$localCache[$schema], $config);
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
     * @return array
     */
    protected function getSchemaConfig($include_options = null)
    {
        if ($include_options === null) {
            $include_options = $this->include_options;
        }
        $schema = $this->schema;
        if ($this->useLocalCaching && in_array($schema, self::$fullyCachedSchemas)) {
            return self::$localCache[$schema];
        }

        $config = $this->getObjectConfig($table = null, $include_options);
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
     * Return object (table/schema) configuration
     *
     * @throws Exception\ErrorException
     *
     * @param string $table
     * @param boolean|null $include_options
     * @return array
     */
    protected function getObjectConfig($table = null, $include_options = null)
    {
        if ($include_options === null) {
            $include_options = $this->include_options;
        }
        
        $schema = $this->schema;
        $qSchema = $this->adapter->quoteValue($schema);

        if ($table !== null) {
            $qTable = $this->adapter->quoteValue($table);
            $table_clause = "and (t.TABLE_NAME = $qTable or (kcu.referenced_table_name = $qTable and kcu.constraint_name = 'FOREIGN KEY'))";
            $table_join_condition = "(t.table_name = kcu.table_name or  kcu.referenced_table_name = t.table_name)";
        } else {
            $table_join_condition = "t.table_name = kcu.table_name";
            $table_clause = '';
        }

        $query = "

            SELECT
                    t.table_name,
                    c.column_name,
                    c.data_type,
                    c.column_type,

                    c.extra,

                    tc.constraint_type,
                    kcu.constraint_name,
                    kcu.referenced_table_name,
                    kcu.referenced_column_name,

                    c.column_default,
                    c.is_nullable,
                    c.numeric_precision,
                    c.numeric_scale,
                    c.character_octet_length,
                    c.character_maximum_length,
                    c.ordinal_position,

                    c.column_key, -- UNI/MUL/PRI
                    c.character_set_name,


                    c.collation_name,

                    c.column_comment,

                    t.table_type,
                    t.engine,
                    t.table_comment,
                    t.table_collation

            FROM `INFORMATION_SCHEMA`.`COLUMNS` c
            INNER JOIN `INFORMATION_SCHEMA`.`TABLES` t on c.TABLE_NAME = t.TABLE_NAME
            LEFT JOIN `INFORMATION_SCHEMA`.`KEY_COLUMN_USAGE` kcu
               on (
                    $table_join_condition
                     and kcu.table_schema = t.table_schema
                     and kcu.column_name = c.column_name
                 )
              LEFT JOIN
                `INFORMATION_SCHEMA`.`TABLE_CONSTRAINTS` tc
               on (
                     t.table_name = tc.table_name
                      and tc.table_schema = t.table_schema
                      and tc.constraint_name = kcu.constraint_name
                  )


            where c.TABLE_SCHEMA = $qSchema
            and t.TABLE_SCHEMA = $qSchema
            $table_clause
            and (kcu.table_schema = $qSchema  or kcu.table_schema is null)

            and (kcu.column_name = c.column_name or kcu.column_name is null)
            order by t.table_name, c.ordinal_position
        ";

        $this->disableInnoDbStats();
        try {
            $results = $this->adapter->query($query);
        } catch (\Exception $e) {
            //@codeCoverageIgnoreStart
            $this->restoreInnoDbStats();
            throw new Exception\ErrorException(__METHOD__ . ": " . $e->getMessage());
            //@codeCoverageIgnoreEnd
        }
        $this->restoreInnoDbStats();

        $references = array();
        $config = new Config(array('tables' => array()), true);
        $tables = $config->offsetGet('tables');


        foreach ($results as $r) {
            // Setting table information
            $table_name = $r['table_name'];
            if (!$tables->offsetExists($table_name)) {
                $table_def = array(
                    'name'          => $table_name,
                    'columns'       => array(),
                    'primary_keys'  => array(),
                    'unique_keys'   => array(),
                    'foreign_keys'  => array(),
                    'references'    => array(),
                    'indexes'       => array(),
                );
                if ($include_options) {
                    $table_def['options'] = array(
                       'comment'   => $r['table_comment'],
                       'collation' => $r['table_collation'],
                       'type'      => $r['table_type'],
                       'engine'    => $r['engine']
                    );
                }
                $tables->offsetSet($table_name, $table_def);
            }
            $table   = $tables->offsetGet($table_name);
            $columns = $table->columns;
            $column_name = $r['column_name'];

            $data_type = strtolower($r['data_type']);

            $col_def = array(
                'type'          => $data_type,
                'primary'       => ($r['constraint_type'] == 'PRIMARY KEY'),
                'nullable'      => ($r['is_nullable'] == 'YES'),
                'default'       => $r['column_default']
            );
            if (($r['constraint_type'] == 'PRIMARY KEY')) {
                $col_def['primary'] = true;
                $col_def['autoincrement'] = ($r['extra'] == 'auto_increment');
            }

            $has_charset = false;
            if (in_array($data_type, array('int', 'tinyint', 'mediumint', 'bigint', 'int', 'smallint', 'year'))) {
                $col_def['unsigned']  = (bool) preg_match('/unsigned/', strtolower($r['column_type']));
                $col_def['precision'] = is_numeric($r['numeric_precision']) ? (int) $r['numeric_precision'] : null;
            } elseif (in_array($data_type, array('real', 'double precision', 'decimal', 'numeric', 'float', 'dec', 'fixed'))) {
                $col_def['precision'] = is_numeric($r['numeric_precision']) ? (int) $r['numeric_precision'] : null;
                $col_def['scale']     = is_numeric($r['numeric_scale']) ? (int) $r['numeric_scale'] : null;
            } elseif (in_array($data_type, array('timestamp', 'date', 'time', 'datetime'))) {
                // nothing yet
            } elseif (in_array($data_type, array('char', 'varchar', 'binary', 'varbinary', 'text', 'tinytext', 'mediumtext', 'longtext'))) {
                $col_def['octet_length'] = is_numeric($r['character_octet_length']) ? (int) $r['character_octet_length'] : null;
                $col_def['length'] = is_numeric($r['character_maximum_length']) ? (int) $r['character_maximum_length'] : null;
                $has_charset = true;
            } elseif (in_array($data_type, array('blob', 'tinyblob', 'mediumblob', 'longblob'))) {
                $col_def['octet_length'] = (int) $r['character_octet_length'];
                $col_def['length'] = (int) $r['character_maximum_length'];
            } elseif (in_array($data_type, array('enum', 'set'))) {
                $col_def['octet_length'] = (int) $r['character_octet_length'];
                $col_def['length'] = (int) $r['character_maximum_length'];
                $def = $r['column_type'];

                preg_match_all("/'([^']+)'/", $def, $matches);
                if (is_array($matches[1]) && count($matches) > 0) {
                    $col_def['values'] = $matches[1];
                }
            }

            if ($include_options) {
                $col_def['options'] = array(
                        'comment'           => $r['column_comment'],
                        'definition'        => $r['column_type'],
                        'column_key'        => $r['column_key'],
                        'ordinal_position'  => $r['ordinal_position'],
                        'constraint_type'   => $r['constraint_type'], // 'PRIMARY KEY', 'FOREIGN_KEY', 'UNIQUE'
                    );
                if ($has_charset) {
                    $col_def['options']['charset']     = $r['character_set_name'];
                    $col_def['options']['collation']   = $r['collation_name'];
                }
            }

            $columns[$column_name] = $col_def;

            $foreign_keys = $table->foreign_keys;
            $unique_keys  = $table->unique_keys;

            $constraint_name = $r['constraint_name'];
            $referenced_table_name = $r['referenced_table_name'];
            $referenced_column_name = $r['referenced_column_name'];
            switch ($r['constraint_type']) {
                case 'PRIMARY KEY':
                    $table->primary_keys = array_merge($table->primary_keys->toArray(), (array) $column_name);
                    break;
                case 'UNIQUE':
                    if (!$unique_keys->offsetExists($constraint_name)) {
                        $unique_keys[$constraint_name] = array();
                    }
                    $unique_keys[$constraint_name] = array_merge($unique_keys[$constraint_name]->toArray(), (array) $column_name);
                    break;
                case 'FOREIGN KEY':
                    /*
                    if (!$foreign_keys->offsetExists($constraint_name)) {
                        $foreign_keys[$constraint_name] = array();
                    }
                     *
                     */
                    $fk = array(
                       'referenced_table'  => $referenced_table_name,
                       'referenced_column' => $referenced_column_name,
                       'constraint_name' => $constraint_name
                    );
                    $foreign_keys[$column_name] = $fk;
                    //$table->references[$referenced_table_name] = array($column_name => $r['referenced_column_name']);

                    if (!array_key_exists($referenced_table_name, $references)) {
                        $references[$referenced_table_name] = array();
                    }

                    $k = "$table_name:$referenced_column_name->$column_name";
                    $references[$referenced_table_name][$k] = array(
                        'column' => $column_name,
                        'referencing_table' => $table_name,
                        'referencing_column' => $referenced_column_name,
                        'constraint_name' => $constraint_name
                    );
                    break;
            }
        }

        foreach ($references as $referenced_table_name => $refs) {
            if ($tables->offsetExists($referenced_table_name)) {
                $table = $tables[$referenced_table_name];
                $table->references = $refs;
            }
        }

        $array = $config->toArray();
        unset($config);
        return $array;

    }

    /**
     * Disable innodbstats will increase speed of metadata lookups
     *
     * @return void
     */
    protected function disableInnoDbStats()
    {
        $sql = "show global variables like 'innodb_stats_on_metadata'";
        try {
            $results = $this->adapter->query($sql);
            if (count($results) > 0) {
                $row = $results->offsetGet(0);

                $value = strtoupper($row['Value']);
                // if 'on' no need to do anything
                if ($value != 'OFF') {
                    $this->mysql_innodbstats_value = $value;
                    // disabling innodb_stats
                    $this->adapter->execute("set global innodb_stats_on_metadata='OFF'");
                }
            }
        } catch (\Exception $e) {
            // do nothing, silently fallback
        }
    }


    /**
     * Restore old innodbstats variable
     * @return void
     */
    protected function restoreInnoDbStats()
    {
        $value = $this->mysql_innodbstats_value;
        if ($value !== null) {
            // restoring old variable
            $this->adapter->execute("set global innodb_stats_on_metadata='$value'");
        }
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
