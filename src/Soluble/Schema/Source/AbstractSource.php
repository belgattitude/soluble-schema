<?php
namespace Soluble\Schema\Source;

use Soluble\Schema\Exception;

abstract class AbstractSource
{
    /**
     * Default schema name
     * @var string
     */
    protected $schema;



    /**
     * Get unique keys on table
     *
     * @param string $table table name
     * @param boolean $include_primary include primary keys in the list
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     * @return array
     */
    abstract public function getUniqueKeys($table, $include_primary = false);


    /**
     * Return indexes information on a table
     *
     * @param string $table table name
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @return array
     */
    abstract public function getIndexesInformation($table);

    /**
     * Return unique table primary key
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException when no pk or multiple pk found
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return string|int primary key
     */
    abstract public function getPrimaryKey($table);


    /**
     * Return composite primary keys
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return array primary keys
     */
    abstract public function getPrimaryKeys($table);


    /**
     * Return column information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return array associative array [column_name => infos]
     */
    abstract public function getColumnsInformation($table);


    /**
     * Return relations information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     *
     * @return array relations
     */
    abstract public function getRelations($table);

    /**
     * Return table informations
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @return array associative array indexed by table_name
     */
    abstract public function getTablesInformation();


    /**
     * Return column information
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return array
     */

    public function getColumns($table)
    {
        return array_keys($this->getColumnsInformation($table));
    }


    /**
     * Return information about a specific table
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @param string $table
     * @return array
     */
    public function getTableInformation($table)
    {
        $infos = $this->getTablesInformation();
        return $infos[$table];
    }

    /**
     * Return a list of table names
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @return array
     */
    public function getTables()
    {
        return array_keys($this->getTablesInformation());
    }


    /**
     * Check whether a table exists in the specified or current scheme
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @param string $table
     * @return bool
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

    protected function validateTable($table)
    {
        $this->checkTableArgument($table);
        if (!$this->hasTable($table)) {
            throw new Exception\TableNotFoundException(__METHOD__ . ": Table '$table' does not exists in database '{$this->schema}'");
        }
        return $this;
    }
    */

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
