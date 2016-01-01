<?php

namespace Soluble\Schema\Source\Mysql;

use Soluble\DbWrapper\Adapter\MysqlAdapter;
use Soluble\Schema\Exception;

abstract class AbstractMysqlDriver implements MysqlDriverInterface
{
    /**
     * @var MysqlAdapter
     */
    protected $adapter;

    /**
     * Schema name
     * @var string
     */
    protected $schema;

    /**
     * Used to restore innodb stats mysql global variable
     * @var string
     */
    protected $mysql_innodbstats_value;


    /**
     *
     * @param MysqlAdapter $adapter
     * @param string $schema database name
     */
    public function __construct(MysqlAdapter $adapter, $schema)
    {
        $this->adapter = $adapter;
        $this->schema = $schema;
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
}
