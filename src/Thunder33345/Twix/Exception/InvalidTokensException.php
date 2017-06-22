<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Exception;

class InvalidTokensException extends \InvalidArgumentException implements TwixExceptionInterface
{
 static public function render($what)
  {
    return new static('Missing Required Fields In Tokens, Missing Value "'.$what.'"');
  }
}