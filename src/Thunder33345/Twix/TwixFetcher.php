<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Thunder33345\Twix\Exception\TwixExecutionException;
use Thunder33345\Twix\Exception\TwixRequestException;

class TwixFetcher extends AsyncTask
{
  //Debug values...
  //turn all to false for production
  const debug = false;
  const disabled = false;
  private $tokens,$url,$method,$fields,$then,$id;

  /*
   * DO NOT INVOKE __Construct manually use Twix::getBuilder()
   * There's minimum value checking remained here thus any actions may cause the server to crash due to this begin an async task
   */
  public function __construct(array $tokens,string $url,$method,array $fields = [],callable $then = null,$id = null)
  {
    parent::__construct();
    if(self::debug) echo "Task constructed\n";
    $this->tokens = serialize($tokens);
    $this->url = $url;
    $this->method = $method;
    $this->fields = serialize($fields);
    //@formatter:off
    if($method !== Twix::REQUEST_GET AND $method !== Twix::REQUEST_POST)
      throw new \InvalidArgumentException('Invalid Argument: $method expecting Twix::REQUEST_GET OR Twix::REQUEST_POST');
    //@formatter:on
    $this->then = $then;

    $this->id = serialize($id);//let it fail silently

  }

  public function onRun()
  {
    if(self::debug) echo "Task running\n";
    $url = $this->url;
    $requestMethod = $this->method;
    $fields = unserialize($this->fields);
    $twitter = $this->getTwitter();
    if(!self::disabled) {
      switch($requestMethod){
        case Twix::REQUEST_GET:
          $twitter = $twitter->setGetfield($fields)->buildOauth($url,"GET");
          break;
        case Twix::REQUEST_POST:
          $twitter = $twitter->buildOauth($url,"POST")->setPostfields($fields);
          break;
        default:
          //How tf would this even happen anyways?
          throw new \InvalidArgumentException('Invalid Argument: $method expecting Twix::REQUEST_GET OR Twix::REQUEST_POST');
      }
      try{
        //Pointless to catch and throw if i am doing nothing
        $respond = $twitter->performRequest();
        $http = $twitter->getHttpStatusCode();
      }
      catch(\Exception$exception){
        //at least they know where that come from
        //would be nice to be able to remedy it
        throw TwixRequestException::render($exception);
      }
    } else {
      $respond = 'Not available(Inactive)';
      $http = 200;
    }
    $this->setResult(['respond' => $respond,'httpCode' => $http]);
    if(self::debug) echo "Task ran\n";
  }

  public function onCompletion(Server $server)
  {
    if(self::debug) echo "Task Completed\n";
    if($this->then === null) return;
    $results = $this->getResult();
    $respond = $results['respond'];
    $httpCode = $results['httpCode'];
    $id = unserialize($this->id);
    $then = $this->then;
    try{
      $then(new TwixResult($server,$respond,$httpCode,$id));
    }
    catch(\Exception$exception){
      throw TwixExecutionException::render($exception);
    }
  }

  private function getTwitter() { return new TwitterAPIExchange(unserialize($this->tokens)); }
}