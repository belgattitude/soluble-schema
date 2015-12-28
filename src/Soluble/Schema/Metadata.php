<?php

namespace Soluble\Schema;

use Zend\Db\Adapter\Adapter;

class Metadata
{
    /**
     * Adapter
     *
     * @var Adapter
     */
    protected $adapter = null;

    /**
     * @var \Soluble\Schema\Source\AbstractSource
     */
    protected $source = null;

    /**
     * Constructor
     * @throws Exception\UnsupportedDriverException
     * @param Adapter $adapter
     */
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->source = $this->createSourceFromAdapter($adapter);
    }


    /**
     *
     * @return \Soluble\Schema\Source\AbstractSource
     */
    public function getSource()
    {
        return $this->source;
    }


    /**
     * Automatically create source from adapter
     *
     * @throws Exception\UnsupportedDriverException
     * @param \Zend\Db\Adapter\Adapter $adapter
     * @param string $schema database schema to use or null to current schema defined by the adapter
     * @return \Soluble\Schema\Source\AbstractSource
     */
    protected function createSourceFromAdapter(Adapter $adapter, $schema = null)
    {
        $adapter_name = strtolower($adapter->getPlatform()->getName());
        switch ($adapter_name) {
            case 'mysql':
                $source =  new Source\Mysql\InformationSchema($adapter, $schema);
                break;
            default:
                throw new Exception\UnsupportedDriverException("Currently only MySQL is supported, driver set '$adapter_name'");
        }

        return $source;
    }

    /**
     * Return underlying database adapter
     * @return Adapter
     */
    public function getDbAdapter()
    {
        return $this->adapter;
    }
}
