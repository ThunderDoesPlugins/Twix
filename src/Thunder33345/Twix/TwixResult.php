<?php /** Created By Thunder33345 **/

namespace Thunder33345\Twix;

use pocketmine\Server;

class TwixResult
{
  private $server;
  private $respond;
  private $httpRespond;
  private $id;

  public function __construct(Server $server,$result,$httpRespond,$id)
  {
    $this->server = $server;
    $this->respond = $result;
    $this->httpRespond = $httpRespond;
    $this->id = $id;
  }

  public function getServer(): Server { return $this->server; }

  public function getRespond() { return $this->respond; }

  public function getHttpRespond() { return $this->httpRespond; }

  public function getId() { return $this->id; }
}