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
     * Return all uniques keys defined for a table.
     *
     * By default it does not include the primary key, simply set
     * the $include_primary parameter to true to get it. In this case
     * the associative key will be 'PRIMARY'.
     *
     * If no unique keys can be found returns an empty array
     *
     * <code>
     * // The resulting array look like
     * [
     *    "unique_index_name_1" => [
     *           "column_name_1", "column_name_2"
     *          ],
     *    "unique_index_name_2" => ["column_name_1"]
     * ]
     * </code>
     *
     * @param string $table table name
     * @param boolean $include_primary include primary keys in the list
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     * @return array associative ['index_name' => ['col1', 'col2'], 'index_name_2' => ['col3']]
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
     * Return table primary key
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\NoPrimaryKeyException when no pk or multiple pk found
     * @throws Exception\MultiplePrimaryKeyException when multiple pk found
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table
     * @return string primary key
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
     * @return array primary keys ['col1', 'col3']
     */
    abstract public function getPrimaryKeys($table);


    /**
     * Retrieve full columns informations from a table
     *
     * <code>
     * // The resulting array looks like
     * [
     *  ["column_name_1"] => [
     *   ["type"]      => (string)  "Database type, i.e: 'char', 'int', 'bigint', 'decimal'...",
     *   ["primary"]   => (boolean) "Whether column is (part of) a primary key",
     *   ["nullable"]  => (boolean) "Whether column is nullable",
     *   ["default"]   => (string)  "Default value for column or null if none",
     *
     *   // Specific to primary key(s) columns
     *   ["autoincrement"] => (boolean) "Whether the primary key is autoincremented"
     *
     *   // Specific to numeric, decimal, boolean... types
     *   ["unsigned"]  => (boolean) "Whether the column is unsigned",
     *   ["precision"] => (int)     "Number precision (or maximum length)",
     *
     *   // Specific to character oriented types as well as enum, blobs...
     *   ["length"]       => (int) "Maximum length",
     *   ["octet_length"] => (int) "Maximum length in octets (differs from length when using multibyte charsets",
     *
     *   // Columns specific ddl information
     *   ["options"]  => [
     *            "comment"          => "Column comment",
     *            "definition"       => "DDL definition, i.e. varchar(250)",
     *            "ordinal_position" => "Column position number",
     *            "constraint_type"  => "Type of constraint if applicable",
     *            "column_key"       => "",
     *            "charset"          => "Column charset, i.e. 'utf8'",
     *            "collation"        => "Column collation, i.e. 'utf8_unicode_ci'"
     *          ],
     *   ],
     *   ["column_name_2"] => [
     *       //...
     *   ]
     * ]
     * </code>
     *
     * @see \Soluble\Schema\Source\AbstractSource::getColumns() for only column names
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table table name
     * @return array associative array i.e. ['colname' => ['type' => 'char', 'primary' => false, ...]]
     */
    abstract public function getColumnsInformation($table);


    /**
     * Retrieve foreign keys information
     *
     * <code>
     * // The resulting array looks like
     * [
     *    "column_name_1" => [
     *        "referenced_table"  => "Referenced table name",
     *        "referenced_column" => "Referenced column name",
     *        "constraint_name"   => "Constraint name i.e. 'FK_6A2CA10CBC21F742'"
     *    ],
     *    "column_name_2" => [
     *           // ...
     *    ]
     * ]
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table table name
     *
     * @return array relations associative array ['col_name_1' => ['referenced_table' => 'tab1', 'referenced_column' => 'col1', 'constraint_name' => 'FK...']]
     */
    abstract public function getForeignKeys($table);

    /**
     * Retrieve references (relation) to the given table
     *
     * References allows you to get informations about all tables
     * referencing this table
     *
     * <code>
     * // The resulting array looks like
     * [
     *    "ref_table:ref_column->column1" => [
     *       "column"             => "Colum name in this table",
     *       "referencing_table"  => "Referencing table name",
     *       "referencing_column" => "Column name in the referencing table",
     *       "constraint_name"    => "Constaint name i.e. 'FK_6A2CA10CBC21F742'"
     *    ],
     *    "ref_table:ref_column->column2" => [
     *        //...
     *    ]
     * ]
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     * @throws Exception\TableNotFoundException
     *
     * @param string $table table name
     *
     * @return array relations associative array ['col_name_1' => ['referenced_table' => 'tab1', 'referenced_column' => 'col1', 'constraint_name' => 'FK...']]
     */

    abstract public function getReferences($table);

    /**
     * Get full schema configuration
     *
     * @throws Exception\ErrorException
     * @throws Exception\SchemaNotFoundException
     *
     * @return \ArrayObject
     */
    abstract public function getSchemaConfig();

    /**
     * Return full information of all tables present in schema
     *
     * <code>
     * // The resulting array looks like
     * [
     *  ["table_name_1"] => [
     *    ["name"]    => (string) 'Table name'
     *    ["columns"] => [ // Columns information,
     *                     // @see AbstractSource::getColumnsInformation()
     *                     "col name_1" => ["name" => "", "type" => "", ...]',
     *                     "col name_2" => ["name" => "", "type" => "", ...]'
     *                   ]
     *    ["primary_keys"] => [ // Primary key column(s) or empty
     *                      "pk_col1", "pk_col2"
     *                   ],
     *    ["unique_keys"]  => [ // Uniques constraints or empty if none
     *                      "unique_index_name_1" => ["col1", "col3"],
     *                      "unique_index_name_2" => ["col4"]
     *                   ],
     *    ["foreign_keys"] => [ // Foreign keys columns and their references or empty if none
     *                       "col_1" => [
     *                                    "referenced_table"  => "Referenced table name",
     *                                    "referenced_column" => "Referenced column name",
     *                                    "constraint_name"   => "Constraint name i.e. 'FK_6A2CA10CBC21F742'"
     *                                  ],
     *                       "col_2" => [ // ...
     *                                  ]
     *                      ],
     *    ["references"] => [ // Relations referencing this table
     *                       "ref_table:ref_column->column1" => [
     *                          "column"             => "Colum name in this table",
     *                          "referencing_table"  => "Referencing table name",
     *                          "referencing_column" => "Column name in the referenceing table",
     *                          "constraint_name"    => "Constaint name i.e. 'FK_6A2CA10CBC21F742'"
     *                          ],
     *                        "referencing_table_2" => [ //...
     *                          ],
     *                      ]
     *    ["indexes"]  => [],
     *    ['options']  => [ // Specific table creation options
     *                      "comment"   => "Table comment",
     *                      "collation" => "Table collation, i.e. 'utf8_general_ci'",
     *                      "type"      => "Table type, i.e: 'BASE TABLE'",
     *                      "engine"    => "Engine type if applicable, i.e. 'InnoDB'",
     *                    ]
     *  ],
     *  ["table_name_2"] => [
     *     //...
     *  ]
     * ]
     * </code>
     *
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ErrorException
     * @throws Exception\ExceptionInterface
     *
     * @return array associative array indexed by table name
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
     * @param string $table table name
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
     * @return array indexed array with table names: ['table1', 'table2']
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
