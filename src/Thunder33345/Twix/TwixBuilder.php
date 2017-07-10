<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix;

use Thunder33345\Twix\Exception\IllegalIdException;
use Thunder33345\Twix\Exception\InvalidClosure\IllegalStaticException;
use Thunder33345\Twix\Exception\InvalidClosure\InvalidArgumentAcceptException;
use Thunder33345\Twix\Exception\InvalidClosure\InvalidArgumentAcceptTypeException;
use Thunder33345\Twix\Exception\InvalidClosure\InvalidArgumentCountException;
use Thunder33345\Twix\Exception\InvalidMethodException;
use Thunder33345\Twix\Exception\InvalidTokensException;
use Thunder33345\Twix\Exception\MissingValueException;
use Thunder33345\Twix\Objects\CompletionError;
use Thunder33345\Twix\Objects\FetchError;

class TwixBuilder
{
  //Mandatory
  private $tokens,$url,$method;

  //Optionals
  private $fields,$then,$id;

  //Errors
  private $error,$fetchError;

  static public function get()
  {
    return new static();
  }

  /**
   * API Tokens
   * @param array $tokens
   * @return $this
   */
  public function setTokens(array $tokens)
  {
    $required = ['oauth_access_token','oauth_access_token_secret','consumer_key','consumer_secret'];
    foreach($required as $checkFor){
      if(!isset($tokens[$checkFor])) {
        throw InvalidTokensException::render($checkFor);
      }
    }

    $this->tokens = $tokens;
    return $this;
  }

  /**
   * Endpoint URL
   * @param string $url
   * @return $this
   */
  public function setUrl(string $url)
  {
    $this->url = $url;
    return $this;
  }

  /**
   * Methodc(Twix::REQUEST_POST OR Twix::REQUEST_GET)
   * @param $method
   * @return $this
   */
  public function setMethod($method)
  {
    $method = strtolower($method);//be not so strict
    if($method !== Twix::REQUEST_GET AND $method !== Twix::REQUEST_POST) {
      throw InvalidMethodException::render($method);
    }
    $this->method = $method;
    return $this;
  }

  /**
   * Fields to be posted
   * @param array $fields
   * @return $this
   */
  public function setFields(array $fields)
  {
    $this->fields = $fields;
    return $this;
  }

  /**
   * What to do after the completion of the task
   * This will be executed at the main thread
   * @param \Closure $function
   * @return $this
   * @throws \Exception
   *
   * Please keep your function free of static variables (use($vars)) as it seems to be unstable used in async
   * Your function is expected to be
   * function (TwixResult $result) {/do something/}
   * the only variable that will be returned is TwixResult which will contain everything you need
   */
  public function then(\Closure $function)
  {
    $reflect = new \ReflectionFunction($function);

    $parameters = $reflect->getParameters();

    $static = $reflect->getStaticVariables();
    if(count($static) > 0) throw IllegalStaticException::render();

    if(count($parameters) !== 1) throw InvalidArgumentCountException::render();

    foreach($parameters as $parameter){
      if($parameter->getType() === null) throw InvalidArgumentAcceptException::render('TwixResult');
      if((string)$parameter->getType() !== TwixResult::class) throw InvalidArgumentAcceptTypeException::render(TwixResult::class,(string)$parameter->getType());
    }

    $this->then = $function;
    return $this;
  }

  /*
   * something serialize-able, only useful if you have a then, useful for identification proposes
   */
  public function setId($id)
  {
    try{
      serialize($id);
    }
    catch(\Exception$exception){
      throw IllegalIdException::render($exception);
    }
    $this->id = $id;
    return $this;
  }

  /**
   * Called when there's a exception thrown onCompletion
   * @param \Closure $error
   * @return $this
   */

  public function error(\Closure $error)
  {
    $reflect = new \ReflectionFunction($error);

    $static = $reflect->getStaticVariables();
    $parameters = $reflect->getParameters();

    if(count($static) > 0) throw IllegalStaticException::render();

    if(count($parameters) !== 1) throw InvalidArgumentCountException::render();

    foreach($parameters as $parameter){
      if($parameter->getType() === null) throw InvalidArgumentAcceptException::render('CompletionError');
      if((string)$parameter->getType() !== CompletionError::class) throw InvalidArgumentAcceptTypeException::render('CompletionError',(string)$parameter->getType());
    }

    $this->error = $error;
    return $this;
  }

  /**
   * Called when there's a exception thrown onRun
   * @param \Closure $error
   * @return $this
   */
  public function fetchError(\Closure $error)
  {
    $reflect = new \ReflectionFunction($error);

    $static = $reflect->getStaticVariables();
    $parameters = $reflect->getParameters();

    if(count($static) > 0) throw IllegalStaticException::render();

    if(count($parameters) !== 1) throw InvalidArgumentCountException::render();

    foreach($parameters as $parameter){
      if($parameter->getType() === null) throw InvalidArgumentAcceptException::render('FetchError');
      if((string)$parameter->getType() !== FetchError::class) throw InvalidArgumentAcceptTypeException::render('FetchError',(string)$parameter->getType());
    }

    $this->fetchError = $error;
    return $this;
  }

  /*
   * Returns the result
   */
  public function getResult()
  {
    if(!isset($this->tokens)) throw MissingValueException::render('Tokens','TwixBuilder::setToken()');
    if(!isset($this->url)) throw MissingValueException::render('Url','TwixBuilder::setUrl()');
    if(!isset($this->method)) throw MissingValueException::render('Method','TwixBuilder::setMethod');
    $token = $this->tokens;
    $url = $this->url;
    $method = $this->method;
    if(isset($this->fields)) $fields = $this->fields; else $fields = [];

    if(isset($this->then)) $then = $this->then; else $then = null;
    if(isset($this->id) AND $this->id !== null) $id = $this->id; else $id = [];
    if(isset($this->error)) $error = $this->error; else $error = null;
    if(isset($this->fetchError)) $fetchError = $this->fetchError; else $fetchError = null;

    $result = new TwixFetcher($token,$url,$method,$fields,$then,$id,$error,$fetchError);
    return $result;
  }

  /*
   * Getters, they have absolutely no reason to be here...
   */
  public function getTokens() { return $this->tokens; }

  public function getUrl() { return $this->url; }

  public function getMethod() { return $this->method; }

  public function getFields() { return $this->fields; }

  public function getId() { return $this->id; }
}