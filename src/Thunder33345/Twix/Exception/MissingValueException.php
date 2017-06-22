<?php
/** Created By Thunder33345 **/
namespace Thunder33345\Twix\Exception;

class MissingValueException extends \InvalidArgumentException implements TwixExceptionInterface {
 static public function render($what,$where){
    return new static('Missing Required '.$what.', Expecting Call To '.$where);
  }
}