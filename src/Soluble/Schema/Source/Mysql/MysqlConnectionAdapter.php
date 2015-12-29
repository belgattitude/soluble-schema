<?php

namespace Soluble\Schema\Source\Mysql;

/**
 * Wrapper for basic mysqli/pdo usage
 */

use Soluble\Schema\Exception;
use ArrayObject;

class MysqlConnectionAdapter
{

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
     * @var string
     */
    protected $type;
    
    /**
     *
     * @throws Exception\InvalidArgumentException
     * @param \mysqli|\PDO $conn
     */
    public function __construct($conn)
    {
        if ($conn instanceof \mysqli) {
            $this->mysqli = $conn;
            $this->type = self::DRIVER_TYPE_MYSQLI;
        } elseif ($conn instanceof \PDO && $conn->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'mysql') {
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
    public function getCurrentSchema()
    {
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
    public function quoteValue($value)
    {
        if ($this->type == self::DRIVER_TYPE_MYSQLI) {
            $quoted = "'" . $this->mysqli->real_escape_string($value) . "'";
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
    public function query($query)
    {
        if ($this->type == self::DRIVER_TYPE_MYSQLI) {
            $results = $this->queryMysqli($query);
        } else {
            $results = $this->queryPDO($query);
        }
        return $results;
    }
    
    /**
     * Execute special sql like set names...
     * @param string $query
     * @return void
     */
    public function execute($query)
    {
        if ($this->type == self::DRIVER_TYPE_MYSQLI) {
            $this->queryMysqli($query, false);
        } else {
            $this->executePDO($query);
        }
    }

    /**
     *
     * @param string $query
     * @return void
     */
    protected function executePDO($query)
    {
        try {
            $this->pdo->exec($query);
        } catch (\Exception $e) {
            $msg = "PDOException : {$e->getMessage()} [$query]";
            throw new Exception\InvalidArgumentException($msg);
        }
    }
    
    
    /**
     *
     * @param string $query
     * @return ArrayObject
     */
    protected function queryPDO($query)
    {
        try {
            $stmt = $this->pdo->query($query, \PDO::FETCH_ASSOC);
            if (!$stmt) {
                throw new Exception\InvalidArgumentException("Query cannot be executed [$query].");
            }
            $results = new ArrayObject();
            foreach ($stmt as $row) {
                $results->append($row);
            }
        } catch (Exception\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            $msg = "PDOException : {$e->getMessage()} [$query]";
            throw new Exception\InvalidArgumentException($msg);
        }
        return $results;
    }

    /**
     *
     * @param string $query
     * @param boolean $throw_exception_if_empty if empty result (like set command...)
     * @return ArrayObject
     */
    protected function queryMysqli($query, $throw_exception_if_empty=true)
    {
        try {
            $r = $this->mysqli->query($query);
            
            $results = new ArrayObject();
            
            if (!$r) {
                throw new Exception\InvalidArgumentException("Query cannot be executed [$query].");
            } elseif ($throw_exception_if_empty && !$r instanceof \mysqli_result) {
                throw new Exception\InvalidArgumentException("Query didn't return any result [$query].");
            } elseif($r instanceof \mysqli_result)  {
                while ($row = $r->fetch_assoc()) {
                    $results->append($row);
                }
            }
            
        } catch (Exception\InvalidArgumentException $e) {
            throw $e;
        } catch (\Exception $e) {
            $msg = "MysqliException: {$e->getMessage()} [$query]";
            throw new Exception\InvalidArgumentException($msg);
        }
        return $results;
    }
}
