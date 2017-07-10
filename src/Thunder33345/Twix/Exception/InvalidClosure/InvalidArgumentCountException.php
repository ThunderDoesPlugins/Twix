<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Exception\InvalidClosure;
use Thunder33345\Twix\Exception\TwixExceptionInterface;

class InvalidArgumentCountException extends InvalidClosureException implements TwixExceptionInterface{
  static public function render(){
    return new static('Function Can Only Have One Parameter');
  }
}