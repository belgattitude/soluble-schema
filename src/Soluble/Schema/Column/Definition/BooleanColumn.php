<?php
namespace Soluble\Schema\Column\Definition;

class BooleanColumn extends AbstractColumnDefinition implements NumericColumnInterface
{
    /**
     * @return bool
     */
    public function getNumericUnsigned()
    {
        return false;
    }

    /**
     * @param bool $numericUnsigned
     * @return BooleanColumn
     */
    public function setNumericUnsigned($numericUnsigned)
    {
        // do nothing
        $numericUnsigned = false;
        return $this;
    }


    /**
     * @return bool
     */
    public function isNumericUnsigned()
    {
        return false;
    }
}
