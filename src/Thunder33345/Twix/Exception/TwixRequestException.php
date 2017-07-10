<?php
/** Created By Thunder33345 **/
namespace Thunder33345\Twix\Exception;
class TwixRequestException extends \RuntimeException implements TwixExceptionInterface{
  static public function render(\Exception$trace){
    echo "Origin trace: ";
    var_dump($trace);

    return new static('Something Went Wrong While Fetching: '.$trace->getTraceAsString(),0,$trace);
  }
}