<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Objects;

use pocketmine\Server;

class CompletionError
{
  private $exception;
  private $server;
  private $respond;
  private $httpRespond;
  private $id;

  public function __construct(\Exception $exception,Server $server,$result,$httpRespond,$id)
  {
    $this->exception = $exception;
    $this->server = $server;
    $this->respond = $result;
    $this->httpRespond = $httpRespond;
    $this->id = $id;
  }

  public function getException() { return $this->exception; }

  public function getServer(): Server { return $this->server; }

  public function getRespond() { return $this->respond; }

  public function getHttpRespond() { return $this->httpRespond; }

  public function getId() { return $this->id; }
}