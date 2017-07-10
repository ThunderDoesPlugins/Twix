<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use Thunder33345\Twix\Exception\InvalidMethodException;
use Thunder33345\Twix\Objects\CompletionError;
use Thunder33345\Twix\Objects\FetchError;

class TwixFetcher extends AsyncTask
{
  //Debug values...
  //turn all const to false for production
  const debug = false;
  const disabled = false;

  private $tokens,$url,$method,$fields,$then,$id;
  private $error,$errorFetch;

  /*
   * DO NOT INVOKE __Construct manually use Twix::getBuilder()
   * There's minimum value checking remained here thus any actions may cause the server to crash due to this begin an async task
   */
  public function __construct(array $tokens,string $url,$method,array $fields = [],callable $then = null,$id = null,callable $error = null,callable $errorFetch = null)
  {
    if(self::debug) echo "Task constructed\n";
    parent::__construct();
    $this->tokens = serialize($tokens);
    $this->url = $url;
    $this->method = $method;
    if($method !== Twix::REQUEST_GET AND $method !== Twix::REQUEST_POST) throw InvalidMethodException::render();

    $this->fields = serialize($fields);
    $this->id = serialize($id);//let it fail silently
    $this->then = $then;
    $this->error = $error;
    $this->errorFetch = $errorFetch;
  }

  public function onRun()
  {
    if(self::debug) echo "Task running\n";
    $tries = 0;
    TwixFetcherRetry:
    $tries++;
    $url = $this->url;
    $requestMethod = $this->method;
    $fields = unserialize($this->fields);
    $twitter = $this->getTwitter();
    if(self::disabled) {
      $respond = 'Not available(Inactive)';
      $http = 200;
    } else {
      switch(strtolower($requestMethod)){
        case Twix::REQUEST_GET:
          $fields = '?'.http_build_query($fields,'','&');
          $twitter = $twitter->setGetfield($fields)->buildOauth($url,"GET");
          break;
        case Twix::REQUEST_POST:
          $twitter = $twitter->buildOauth($url,"POST")->setPostfields($fields);
          break;
        default:
          //How tf would this even happen anyways?
          throw InvalidMethodException::render($requestMethod);
      }
      //Maybe better error values??
      $respond = null;
      $http = null;
      try{
        $respond = $twitter->performRequest();
        $http = $twitter->getHttpStatusCode();
      }
      catch(\Exception$exception){
        if($this->errorFetch !== null) {
          $call = $this->errorFetch;
          $fetchError = new FetchError($exception,$respond,$http,$tries);
          $call($fetchError);
          if($fetchError->getRetry()) goto TwixFetcherRetry;
        }
      }
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
    if($then !== null) try{
      $then(new TwixResult($server,$respond,$httpCode,$id));
    }
    catch(\Exception$exception){
      if($this->error !== null) {
        $error = $this->error;
        $completionError = new CompletionError($exception,$server,$respond,$httpCode,$id);
        $error($completionError);
      }
    }
  }

  private function getTwitter() { return new TwitterAPIExchange(unserialize($this->tokens)); }
}