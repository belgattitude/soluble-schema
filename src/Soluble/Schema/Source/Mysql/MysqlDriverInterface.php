<?php

namespace Soluble\Schema\Source\Mysql;

use Soluble\Schema\Exception;

interface MysqlDriverInterface
{
    /**
     * Return object (table/schema) configuration.
     *
     * @throws Exception\ErrorException
     *
     * @param string $table
     * @param bool   $include_options
     *
     * @return \ArrayObject
     */
    public function getSchemaConfig($table = null, $include_options = true);
}
