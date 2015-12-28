<?php

namespace Soluble\Schema\Column\Definition;

abstract class AbstractColumnDefinition
{
    /**
     *
     * @var string
     */
    protected $name = null;

    /**
     *
     * @var string
     */
    protected $tableName = null;

    /**
     *
     * @var string
     */
    protected $schemaName = null;

    /**
     *
     * @var
     */
    protected $ordinalPosition = null;

    /**
     *
     * @var string
     */
    protected $columnDefault = null;

    /**
     *
     * @var bool
     */
    protected $isNullable = null;

    /**
     *
     * @var string
     */
    protected $dataType = null;

    /**
     *
     * @var string
     */
    protected $nativeDataType = null;


    /**
     * @var string
     */
    protected $alias;

    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * @var string
     */
    protected $catalog;

    /**
     *
     * @var boolean
     */
    protected $isPrimary = false;

    /**
     *
     * @var boolean
     */
    protected $isGroup = false;




    /**
     * Constructor
     *
     * @param string $name
     * @param string $tableName
     * @param string $schemaName
     */
    public function __construct($name, $tableName = null, $schemaName = null)
    {
        $this->setName($name);
        $this->setTableName($tableName);
        $this->setSchemaName($schemaName);
    }

    /**
     * Set name
     *
     * @param string $name
     * @return AbstractColumnDefinition
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get table name
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * Set table name
     *
     * @param string $tableName
     * @return AbstractColumnDefinition
     */
    public function setTableName($tableName)
    {
        if (trim($tableName) == '') {
            $tableName = null;
        }
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Set schema name
     *
     * @param string $schemaName
     * @return AbstractColumnDefinition
     */
    public function setSchemaName($schemaName)
    {
        if (trim($schemaName) == '') {
            $schemaName = null;
        }
        $this->schemaName = $schemaName;
        return $this;
    }

    /**
     * Get schema name
     *
     * @return string
     */
    public function getSchemaName()
    {
        return $this->schemaName;
    }

    /**
     * @return int $ordinalPosition
     */
    public function getOrdinalPosition()
    {
        return $this->ordinalPosition;
    }

    /**
     * @param int $ordinalPosition to set
     * @return AbstractColumnDefinition
     */
    public function setOrdinalPosition($ordinalPosition)
    {
        $this->ordinalPosition = $ordinalPosition;
        return $this;
    }

    /**
     * @return null|string the $columnDefault
     */
    public function getColumnDefault()
    {
        return $this->columnDefault;
    }

    /**
     * @param string $columnDefault to set
     * @return AbstractColumnDefinition
     */
    public function setColumnDefault($columnDefault)
    {
        $this->columnDefault = $columnDefault;
        return $this;
    }


    /**
     * @param bool $isNullable to set
     * @return AbstractColumnDefinition
     */
    public function setIsNullable($isNullable)
    {
        $this->isNullable = $isNullable;
        return $this;
    }

    /**
     * @return bool $isNullable
     */
    public function isNullable()
    {
        return $this->isNullable;
    }


    /**
     * @param bool $isPrimary to set
     * @return AbstractColumnDefinition
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;
        return $this;
    }

    /**
     * @return bool $isPrimary
     */
    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @return null|string the $dataType
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param string $dataType the $dataType to set
     * @return AbstractColumnDefinition
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
        return $this;
    }


    /**
     * @return null|string the $nativeDataType
     */
    public function getNativeDataType()
    {
        return $this->nativeDataType;
    }



    /**
     * @param string $nativeDataType the $dataType to set
     * @return AbstractColumnDefinition
     */
    public function setNativeDataType($nativeDataType)
    {
        $this->nativeDataType = $nativeDataType;
        return $this;
    }


    /**
     *
     * @param string $alias column alias name
     * @return AbstractColumnDefinition
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string column alais
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     *
     * @param string $tableAlias table alias name
     * @return AbstractColumnDefinition
     */
    public function setTableAlias($tableAlias)
    {
        if (trim($tableAlias) == '') {
            $tableAlias = null;
        }
        $this->tableAlias = $tableAlias;
        return $this;
    }

    /**
     *
     * @return string table alias
     */
    public function getTableAlias()
    {
        return $this->tableAlias;
    }

    /**
     *
     * @param string $catalog db catalog
     * @return AbstractColumnDefinition
     */
    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
        return $this;
    }

    /**
     * @return string catalog
     */
    public function getCatalog()
    {
        return $this->catalog;
    }



    /**
     * @param bool $isGroup when the column is grouped
     * @return AbstractColumnDefinition
     */
    public function setIsGroup($isGroup)
    {
        $this->isGroup = $isGroup;
        return $this;
    }

    /**
     * @return bool $isGroup
     */
    public function isGroup()
    {
        return $this->isGroup;
    }


    /**
     * @return boolean
     */
    public function isComputed()
    {
        return ($this->tableName == '');
    }


    /**
     * Tells whether the column is numeric
     *
     * @return boolean
     */
    public function isNumeric()
    {
        return ($this instanceof NumericColumnInterface);
    }

    /**
     * Tells whether the column is textual
     *
     * @return boolean
     */
    public function isText()
    {
        return ($this instanceof TextColumnInterface);
    }

    /**
     * Tells whether the column is a date
     *
     * @return boolean
     */
    public function isDate()
    {
        return ($this instanceof DateColumnInterface);
    }

    /**
     * Tells whether the column is a timestamp
     *
     * @return boolean
     */
    public function isDatetime()
    {
        return ($this instanceof DatetimeColumnInterface);
    }

    /**
     * Return an array version of the column definition
     * @return array
     */
    public function toArray()
    {
        $reflectionClass = new \ReflectionClass(get_class($this));
        $array = array();
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->isProtected()) {
                $property->setAccessible(true);
                $array[$property->getName()] = $property->getValue($this);
                $property->setAccessible(false);
            }
        }
        return $array;
    }
}
