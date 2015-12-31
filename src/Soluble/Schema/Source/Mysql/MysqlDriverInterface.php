<?php

namespace Soluble\Schema\Source\Mysql;

interface MysqlDriverInterface
{

    /**
     * Return object (table/schema) configuration
     *
     * @throws Exception\ErrorException
     *
     * @param string $table
     * @param boolean $include_options
     * @return \ArrayObject
     */
    public function getSchemaConfig($table = null, $include_options = true);
}
