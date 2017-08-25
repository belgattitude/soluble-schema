<?php

namespace Soluble\Schema\Source;

use Soluble\Schema\Exception;
use Soluble\DbWrapper\Adapter\AdapterInterface;

abstract class AbstractSchemaSource implements SchemaSourceInterface
{
    /**
     * Default schema name.
     *
     * @var string
     */
    protected $schema;

    /**
     * Schema signature.
     *
     * @var string
     */
    protected $schemaSignature;

    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Constructor.
     *
     * @param AdapterInterface $adapter
     * @param string|null      $schema  default schema, taken from adapter if not given
     *
     * @throws Exception\InvalidArgumentException for invalid connection
     * @throws Exception\InvalidUsageException    thrown if no schema can be found
     */
    public function __construct(AdapterInterface $adapter, $schema = null)
    {
        $this->adapter = $adapter;
        if ($schema === null) {
            $schema = $this->adapter->getConnection()->getCurrentSchema();
            if ($schema === false || $schema == '') {
                $msg = 'Database name (schema) parameter missing and no default schema set on connection';
                throw new Exception\InvalidUsageException($msg);
            }
        }
        $this->setDefaultSchema($schema);
        $this->setSchemaSignature();
    }

    /**
     * {@inheritdoc}
     */
    public function getColumns($table)
    {
        return array_keys($this->getColumnsInformation($table));
    }

    /**
     * {@inheritdoc}
     */
    public function getTableInformation($table)
    {
        $infos = $this->getTablesInformation();

        return $infos[$table];
    }

    /**
     * {@inheritdoc}
     */
    public function getTables()
    {
        return array_keys($this->getTablesInformation());
    }

    /**
     * {@inheritdoc}
     */
    public function hasTable($table)
    {
        $tables = $this->getTables();

        return in_array($table, $tables);
    }

    /**
     * Check whether a table parameter is valid and exists.
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     *
     * @return self
     */
    protected function validateTable($table)
    {
        $this->checkTableArgument($table);
        if (!$this->hasTable($table)) {
            throw new Exception\TableNotFoundException(__METHOD__ . ": Table '$table' does not exists in database '{$this->schema}'");
        }

        return $this;
    }

    /**
     * Check whether a schema parameter is valid.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string $schema
     *
     * @return self
     */
    protected function validateSchema($schema)
    {
        if (!is_string($schema) || trim($schema) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ': Schema name must be a valid string or an empty string detected');
        }

        return $this;
    }

    /**
     * Set default schema.
     *
     * @throws Exception\InvalidArgumentException
     *
     * @param string $schema
     *
     * @return self
     */
    protected function setDefaultSchema($schema)
    {
        $this->validateSchema($schema);
        $this->schema = $schema;

        return $this;
    }

    /**
     * @param string $table
     *
     * @throws Exception\InvalidArgumentException
     */
    protected function checkTableArgument($table = null)
    {
        if ($table !== null) {
            if (!is_string($table) || trim($table) == '') {
                throw new Exception\InvalidArgumentException(__METHOD__ . ' Table name must be a valid string or an empty string detected');
            }
        }
    }

    /**
     * Return current schema signature for caching.
     */
    protected function setSchemaSignature()
    {
        $host = $this->adapter->getConnection()->getHost();
        $schema = $this->schema;
        $this->schemaSignature = "$host:$schema";
    }
}
