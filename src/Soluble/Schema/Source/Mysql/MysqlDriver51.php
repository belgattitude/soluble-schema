<?php

namespace Soluble\Schema\Source\Mysql;

use Soluble\DbWrapper\Adapter\AdapterInterface;
use ArrayObject;
use Zend\Config\Config;
use Soluble\Schema\Exception;

class MysqlDriver51 extends AbstractMysqlDriver
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Schema name.
     *
     * @var string
     */
    protected $schema;

    /**
     * {@inheritdoc}
     */
    public function __construct(AdapterInterface $adapter, $schema)
    {
        parent::__construct($adapter, $schema);
    }

    /**
     * {@inheritdoc}
     *
     * @throws Exception\ErrorException
     */
    public function getSchemaConfig($table = null, $include_options = true)
    {
        $results = $this->executeQuery($table);

        $references = [];
        $config = new Config(['tables' => []], true);
        $tables = $config->offsetGet('tables');

        foreach ($results as $r) {
            // Setting table information
            $table_name = $r['table_name'];
            if (!$tables->offsetExists($table_name)) {
                $table_def = [
                    'name' => $table_name,
                    'columns' => [],
                    'primary_keys' => [],
                    'unique_keys' => [],
                    'foreign_keys' => [],
                    'references' => [],
                    'indexes' => [],
                ];
                if ($include_options) {
                    $table_def['options'] = [
                       'comment' => $r['table_comment'],
                       'collation' => $r['table_collation'],
                       'type' => $r['table_type'],
                       'engine' => $r['engine']
                    ];
                }
                $tables->offsetSet($table_name, $table_def);
            }
            $table = $tables->offsetGet($table_name);
            $columns = $table->columns;
            $column_name = $r['column_name'];

            $data_type = strtolower($r['data_type']);

            $col_def = [
                'type' => $data_type,
                'primary' => ($r['constraint_type'] == 'PRIMARY KEY'),
                'nullable' => ($r['is_nullable'] == 'YES'),
                'default' => $r['column_default']
            ];
            if (($r['constraint_type'] == 'PRIMARY KEY')) {
                $col_def['primary'] = true;
                $col_def['autoincrement'] = ($r['extra'] == 'auto_increment');
            }

            $has_charset = false;
            if (in_array($data_type, ['int', 'tinyint', 'mediumint', 'bigint', 'int', 'smallint', 'year'])) {
                $col_def['unsigned'] = (bool) preg_match('/unsigned/', strtolower($r['column_type']));
                $col_def['precision'] = is_numeric($r['numeric_precision']) ? (int) $r['numeric_precision'] : null;
            } elseif (in_array($data_type, ['real', 'double precision', 'decimal', 'numeric', 'float', 'dec', 'fixed'])) {
                $col_def['precision'] = is_numeric($r['numeric_precision']) ? (int) $r['numeric_precision'] : null;
                $col_def['scale'] = is_numeric($r['numeric_scale']) ? (int) $r['numeric_scale'] : null;
            } elseif (in_array($data_type, ['timestamp', 'date', 'time', 'datetime'])) {
                // nothing yet
            } elseif (in_array($data_type, ['char', 'varchar', 'binary', 'varbinary', 'text', 'tinytext', 'mediumtext', 'longtext'])) {
                $col_def['octet_length'] = is_numeric($r['character_octet_length']) ? (int) $r['character_octet_length'] : null;
                $col_def['length'] = is_numeric($r['character_maximum_length']) ? (int) $r['character_maximum_length'] : null;
                $has_charset = true;
            } elseif (in_array($data_type, ['blob', 'tinyblob', 'mediumblob', 'longblob'])) {
                $col_def['octet_length'] = (int) $r['character_octet_length'];
                $col_def['length'] = (int) $r['character_maximum_length'];
            } elseif (in_array($data_type, ['enum', 'set'])) {
                $col_def['octet_length'] = (int) $r['character_octet_length'];
                $col_def['length'] = (int) $r['character_maximum_length'];
                $def = $r['column_type'];

                preg_match_all("/'([^']+)'/", $def, $matches);
                if (is_array($matches[1]) && count($matches) > 0) {
                    $col_def['values'] = $matches[1];
                }
            }

            if ($include_options) {
                $col_def['options'] = [
                        'comment' => $r['column_comment'],
                        'definition' => $r['column_type'],
                        'column_key' => $r['column_key'],
                        'ordinal_position' => $r['ordinal_position'],
                        'constraint_type' => $r['constraint_type'], // 'PRIMARY KEY', 'FOREIGN_KEY', 'UNIQUE'
                    ];
                if ($has_charset) {
                    $col_def['options']['charset'] = $r['character_set_name'];
                    $col_def['options']['collation'] = $r['collation_name'];
                }
            }

            $columns[$column_name] = $col_def;

            $foreign_keys = $table->foreign_keys;
            $unique_keys = $table->unique_keys;

            $constraint_name = $r['constraint_name'];
            $referenced_table_name = $r['referenced_table_name'];
            $referenced_column_name = $r['referenced_column_name'];
            switch ($r['constraint_type']) {
                case 'PRIMARY KEY':
                    $table->primary_keys = array_merge($table->primary_keys->toArray(), (array) $column_name);
                    break;
                case 'UNIQUE':
                    if (!$unique_keys->offsetExists($constraint_name)) {
                        $unique_keys[$constraint_name] = [];
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
                    $fk = [
                       'referenced_table' => $referenced_table_name,
                       'referenced_column' => $referenced_column_name,
                       'constraint_name' => $constraint_name
                    ];
                    $foreign_keys[$column_name] = $fk;
                    //$table->references[$referenced_table_name] = array($column_name => $r['referenced_column_name']);

                    if (!array_key_exists($referenced_table_name, $references)) {
                        $references[$referenced_table_name] = [];
                    }

                    $k = "$table_name:$referenced_column_name->$column_name";
                    $references[$referenced_table_name][$k] = [
                        'column' => $column_name,
                        'referencing_table' => $table_name,
                        'referencing_column' => $referenced_column_name,
                        'constraint_name' => $constraint_name
                    ];
                    break;
            }
        }

        foreach ($references as $referenced_table_name => $refs) {
            if ($tables->offsetExists($referenced_table_name)) {
                $table = $tables[$referenced_table_name];
                $table->references = $refs;
            }
        }

        $array = new ArrayObject($config->toArray());
        unset($config);

        return $array;
    }

    /**
     * Return information schema query.
     *
     * @param string|null $table
     *
     * @return string
     */
    protected function getQuery($table = null)
    {
        $qSchema = $this->adapter->quoteValue($this->schema);

        if ($table !== null) {
            $qTable = $this->adapter->quoteValue($table);
            $table_clause = "and (t.TABLE_NAME = $qTable or (kcu.referenced_table_name = $qTable and kcu.constraint_name = 'FOREIGN KEY'))";
            $table_join_condition = '(t.table_name = kcu.table_name or  kcu.referenced_table_name = t.table_name)';
        } else {
            $table_join_condition = 't.table_name = kcu.table_name';
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

        return $query;
    }

    /**
     * Execute information schema query.
     *
     * @param string|null $table table name or null
     *
     * @return ArrayObject
     *
     * @throws Exception\ErrorException
     */
    protected function executeQuery($table = null)
    {
        $query = $this->getQuery($table);
        $this->disableInnoDbStats();
        try {
            $results = $this->adapter->query($query);
        } catch (\Exception $e) {
            $this->restoreInnoDbStats();
            throw new Exception\ErrorException(__METHOD__ . ': ' . $e->getMessage());
        }
        $this->restoreInnoDbStats();

        return $results;
    }
}
