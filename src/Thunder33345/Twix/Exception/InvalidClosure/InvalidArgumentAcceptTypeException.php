<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Exception\InvalidClosure;
use Thunder33345\Twix\Exception\TwixExceptionInterface;

class InvalidArgumentAcceptTypeException extends InvalidClosureException implements TwixExceptionInterface{
  static public function render($accept,$what){
    return new static('Function Must ONLY Accept '.$accept.' NOT '.$what);
  }
}