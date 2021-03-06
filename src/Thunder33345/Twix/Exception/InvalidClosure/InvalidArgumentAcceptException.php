<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Exception\InvalidClosure;
use Thunder33345\Twix\Exception\TwixExceptionInterface;

class InvalidArgumentAcceptException extends InvalidClosureException implements TwixExceptionInterface{
  static public function render($what){
    return new static('Function Must Accept '.$what);
  }
}