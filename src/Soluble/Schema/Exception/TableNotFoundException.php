<?php
namespace Soluble\Schema\Exception;

use Soluble\Schema\Exception\ExceptionInterface;

class TableNotFoundException extends \ErrorException implements ExceptionInterface
{
}
