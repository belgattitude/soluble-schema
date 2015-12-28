<?php
namespace Soluble\Schema\Column\Definition;

class StringColumn extends AbstractColumnDefinition implements TextColumnInterface
{
    /**
     *
     * @var int
     */
    protected $characterMaximumLength = null;



    /**
     * @return int|null the $characterMaximumLength
     */
    public function getCharacterMaximumLength()
    {
        return $this->characterMaximumLength;
    }

    /**
     * @param int $characterMaximumLength the $characterMaximumLength to set
     * @return StringColumn
     */
    public function setCharacterMaximumLength($characterMaximumLength)
    {
        $this->characterMaximumLength = $characterMaximumLength;
        return $this;
    }
}
