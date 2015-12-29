<?php

namespace Soluble\Schema;

use Soluble\Schema\Source;

class Metadata
{
    /**
     * Internal database connection
     *
     * @var mixed
     */
    protected $connection = null;

    /**
     * @var Source\AbstractSource
     */
    protected $source = null;

    /**
     * Constructor
     * @throws Exception\UnsupportedDriverException
     * @param \PDO|\mysqli|mixed $connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->source = $this->createSourceFromConnection($connection);
    }


    /**
     *
     * @return Source\AbstractSource
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Automatically create source from adapter
     *
     * @throws Exception\UnsupportedDriverException
     * @param \PDO|\mysqli|mixed $connection
     * @param string $schema database schema to use or null to current schema defined by the adapter
     * @return Source\AbstractSource
     */
    protected function createSourceFromConnection($connection, $schema = null)
    {
        $driver_name = null;
        if ($connection instanceof \PDO) {
            $driver_name = 'pdo_' . strtolower($connection->getAttribute(\PDO::ATTR_DRIVER_NAME));
        } elseif ($connection instanceof \mysqli) {
            $driver_name = 'mysql';
        }

        switch ($driver_name) {
            case 'pdo_mysql':
            case 'mysql':
                $source =  new Source\Mysql\MysqlInformationSchema($connection, $schema);
                break;
            default:
                throw new Exception\UnsupportedDriverException("Currently only MySQL is supported '$driver_name'");
        }

        return $source;
    }

    /**
     * Return underlying database connection
     * @return mixed|\PDO|\mysqli
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
