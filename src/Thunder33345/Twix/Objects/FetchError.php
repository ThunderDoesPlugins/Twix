<?php /** Created By Thunder33345 **/

namespace Thunder33345\Twix\Objects;
class FetchError
{
  private $exception,$response,$http,$tries;
  private $retry = false;

  public function __construct(\Exception $exception,$response,$http,$tries)
  {
    $this->exception = $exception;
    $this->response = $response;
    $this->http = $http;
    $this->tries = $tries;
  }

  public function setRetry(bool $retry) { $this->retry = $retry; }

  public function getRetry() { return $this->retry; }

  public function getTries() { return $this->tries; }

  public function getException(): \Exception { return $this->exception; }

  public function getHttp() { return $this->http; }

  public function getResponse() { return $this->response; }
}