<?php

namespace Soluble\Schema\Column\Definition;

class DecimalColumn extends AbstractColumnDefinition implements NumericColumnInterface
{
    /**
     *
     * @var int
     */
    protected $numericPrecision = null;

    /**
     *
     * @var int
     */
    protected $numericScale = null;


    /**
     *
     * @var boolean
     */
    protected $numericUnsigned = null;


    /**
     * @return bool
     */
    public function getNumericUnsigned()
    {
        return $this->numericUnsigned;
    }

    /**
     * @param  bool $numericUnsigned
     * @return DecimalColumn
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
     * Return the precision, for example
     * salary DECIMAL(5,2)
     * In this example, 5 is the precision and 2 is the scale.
     * Standard SQL requires that DECIMAL(5,2) be able to store any value
     * with five digits and two decimals, so values that can be stored in
     * the salary column range from -999.99 to 999.99.
     *
     * @return int the $numericPrecision
     */
    public function getNumericPrecision()
    {
        return $this->numericPrecision;
    }

    /**
     * @param int $numericPrecision the $numericPrevision to set
     * @return DecimalColumn
     */
    public function setNumericPrecision($numericPrecision)
    {
        $this->numericPrecision = $numericPrecision;
        return $this;
    }

    /**
     * Return the scale (number of decimal digits)
     * salary DECIMAL(5,2)
     * In this example, 5 is the precision and 2 is the scale.
     * Standard SQL requires that DECIMAL(5,2) be able to store any value
     * with five digits and two decimals, so values that can be stored in
     * the salary column range from -999.99 to 999.99.
     *
     * @return integer the $numericScale
     */
    public function getNumericScale()
    {
        return $this->numericScale;
    }

    /**
     * @param integer $numericScale the $numericScale to set
     * @return DecimalColumn
     */
    public function setNumericScale($numericScale)
    {
        $this->numericScale = $numericScale;
        return $this;
    }
}
