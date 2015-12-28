<?php
namespace Soluble\Schema\Exception;

use Soluble\Schema\Exception\ExceptionInterface;

class NoPrimaryKeyException extends \ErrorException implements ExceptionInterface
{
}
