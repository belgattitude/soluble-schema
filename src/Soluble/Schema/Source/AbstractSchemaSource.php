<?php
namespace Soluble\Schema\Source;

use Soluble\Schema\Exception;

abstract class AbstractSchemaSource implements SchemaSourceInterface
{
    /**
     * Default schema name
     * @var string
     */
    protected $schema;



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
     * Check whether a table parameter is valid and exists
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return AbstractSource
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
     * Check whether a schema parameter is valid
     *
     * @throws Exception\InvalidArgumentException

     * @param string $schema
     * @return AbstractSource
     */
    protected function validateSchema($schema)
    {
        if (!is_string($schema) || trim($schema) == '') {
            throw new Exception\InvalidArgumentException(__METHOD__ . ": Schema name must be a valid string or an empty string detected");
        }
        return $this;
    }

    /**
     * Set default schema
     *
     * @throws Exception\InvalidArgumentException
     * @param string $schema
     * @return AbstractSource
     */
    protected function setDefaultSchema($schema)
    {
        $this->validateSchema($schema);
        $this->schema = $schema;
        return $this;
    }

    /**
     *
     * @param string $table
     * @throws Exception\InvalidArgumentException
     */
    protected function checkTableArgument($table = null)
    {
        if ($table !== null) {
            if (!is_string($table) || trim($table) == '') {
                throw new Exception\InvalidArgumentException(__METHOD__ . " Table name must be a valid string or an empty string detected");
            }
        }
    }
}
