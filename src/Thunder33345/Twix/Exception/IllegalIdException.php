<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Exception;

class IllegalIdException extends \InvalidArgumentException implements TwixExceptionInterface
{
 static public function render($trace)
  {
    return new static('Illegal ID, ID Cannot Be Serialized',0,$trace);
  }
}