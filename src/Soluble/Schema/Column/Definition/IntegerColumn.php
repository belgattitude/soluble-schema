<?php
namespace Soluble\Schema\Column\Definition;

class IntegerColumn extends AbstractColumnDefinition implements NumericColumnInterface
{
    /**
     *
     * @var bool
     */
    protected $numericUnsigned = null;

    /**
     *
     * @var bool
     */
    protected $isAutoIncrement;

    /**
     * @return bool
     */
    public function getNumericUnsigned()
    {
        return $this->numericUnsigned;
    }

    /**
     * @param  bool $numericUnsigned
     * @return IntegerColumn
     */
    public function setNumericUnsigned($numericUnsigned)
    {
        $this->numericUnsigned = $numericUnsigned;
        return $this;
    }


    /**
     * @return bool
     */
    public function isNumericUnsigned()
    {
        return $this->numericUnsigned;
    }


    /**
     * @param bool $isAutoIncrement to set
     * @return IntegerColumn
     */
    public function setIsAutoIncrement($isAutoIncrement)
    {
        $this->isAutoIncrement = $isAutoIncrement;
        return $this;
    }

    /**
     * @return bool $isAutoIncrement
     */
    public function isAutoIncrement()
    {
        return $this->isAutoIncrement;
    }
}
