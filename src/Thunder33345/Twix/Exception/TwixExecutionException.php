<?php
/** Created By Thunder33345 **/
namespace Thunder33345\Twix\Exception;
class TwixExecutionException extends \RuntimeException implements TwixExceptionInterface{
  static public function render($trace){
    return new static('Something Went Wrong While Executing A Function',0,$trace);
  }
}