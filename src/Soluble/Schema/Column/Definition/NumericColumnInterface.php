<?php
namespace Soluble\Schema\Column\Definition;

interface NumericColumnInterface
{
    /**
     * @return bool
     */
    public function getNumericUnsigned();

    /**
     * @param  bool $numericUnsigned
     */
    public function setNumericUnsigned($numericUnsigned);


    /**
     * @return bool
     */
    public function isNumericUnsigned();
}
