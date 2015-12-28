<?php

namespace Soluble\Schema\Source\Mysql;

use Soluble\Schema\Exception;
use ArrayObject;

class MysqlConnectionAdapter {

    const DRIVER_TYPE_PDO = 'pdo';
    const DRIVER_TYPE_MYSQLI = 'mysqli';

    /**
     *
     * @var string 
     */
    protected $driver;

    /**
     *
     * @var \mysqli|null
     */
    protected $mysqli;

    /**
     *
     * @var \PDO|null
     */
    protected $pdo;

    /**
     * 
     * @throws Exception\InvalidArgumentException
     * @param mysqli|PDO $conn
     */
    public function __construct($conn) {
        if ($conn instanceof \mysqli) {
            $this->mysqli = $conn;
            $this->type = self::DRIVER_TYPE_MYSQLI;
        } elseif ($conn instanceof \PDO) {
            $this->pdo = $conn;
            $this->type = self::DRIVER_TYPE_PDO;
        } else {
            $msg = "MysqlConnectionAdapter requires connection to be either 'pdo:mysql' or 'mysqli'";
            throw new Exception\InvalidArgumentException($msg);
        }
    }

    /**
     * Return current schema name
     * @return string|false
     */
    public function getCurrentSchema() {
        $query = 'SELECT DATABASE() as current_schema';
        $results = $this->query($query);
        if (count($results) == 0) {
            return false;
        }
        return $results[0]['current_schema'];
    }

    /**
     * 
     * @param string $value
     * @return string
     */
    public function quoteValue($value) {
        if ($this->type == self::DRIVER_TYPE_MYSQLI) {
            $quoted = $this->mysqli->real_escape_string($value);
        } else {
            $quoted = $this->pdo->quote($value);
        }
        return $quoted;
    }

    /**
     * Execute query and return query as an ArrayObject
     * 
     * @param string $query
     * @return ArrayObject
     */
    public function query($query) {
        if ($this->type == self::DRIVER_TYPE_MYSQLI) {
            $results = $this->executeMysqli($query);
        } else {
            $results = $this->executePDO($query);
        }
        return $results;
    }

    /**
     * 
     * @param string $query
     * @return ArrayObject
     */
    protected function executePDO($query) {

        try {
            $stmt = $this->pdo->query($query, \PDO::FETCH_ASSOC);
            if (!$stmt) {
                throw new Exception\InvalidArgumentException("Query cannot be executed [$query].");
            }
            $results = new ArrayObject();
            foreach ($stmt as $row) {
                $results->append($row);
            }
        } catch (\Exception $e) {
            $msg = "Query error: {$e->getMessage}";
            throw new Exception\InvalidArgumentException($msg);
        }
        return $results;
    }

    /**
     * 
     * @param string $query
     * @return ArrayObject
     */
    protected function executeMysqli($query) {

        try {
            $stmt = $this->mysqli->query($query);
            if (!$stmt) {
                throw new Exception\InvalidArgumentException("Query cannot be executed [$query].");
            }
            $results = new ArrayObject();
            foreach ($stmt as $row) {
                $results->append($row);
            }
        } catch (\Exception $e) {
            $msg = "Query error: {$e->getMessage}";
            throw new Exception\InvalidArgumentException($msg);
        }
        return $results;

        
    }

}
