<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class TwixHelper
{

  const ENDPOINT_UPDATE = 'https://api.twitter.com/1.1/statuses/update.json';
  const ENDPOINT_VERIFY = 'https://api.twitter.com/1.1/account/verify_credentials.json';
  const ENDPOINT_SHOW = 'https://api.twitter.com/1.1/users/show.json';

  private $twix;
  private $server;

  public function __construct(Twix $twix)
  {
    $this->twix = $twix;
    $this->server = $twix->getServer();
  }

  public function sendPost(string $message,callable $then = null,array $fields = [],$id = null,$tokens = null)
  {
    $fFields = ['status' => $message] + $fields;
    $builder = $this->getBuilder();
    if($tokens === null) $tokens = $this->getTokens();
    $builder->setTokens($tokens);
    $builder->setMethod(Twix::REQUEST_POST);
    $builder->setUrl(self::ENDPOINT_UPDATE);
    $builder->setFields($fFields);
    $builder->setId($id);
    $builder->then($then);
    $result = $builder->getResult();
    $this->scheduleAsyncTask($result);
  }

  public function verifyCredentials(callable $then = null,array $fields = [],$id = null,$tokens = null)
  {
    $builder = $this->getBuilder();
    if($tokens === null) $tokens = $this->getTokens();
    $builder->setTokens($tokens);
    $builder->setMethod(Twix::REQUEST_GET)->setUrl(self::ENDPOINT_VERIFY);
    $builder->setFields($fields);
    $builder->setId($id);
    $builder->then($then);
    $result = $builder->getResult();
    $this->scheduleAsyncTask($result);
  }

  public function getProfileByUsername(string $username,callable $then = null,array $fields = [],$id = null,$tokens = null)
  {
    $fields = ['screen_name' => $username] + $fields;
    $builder = $this->getBuilder();
    if($tokens === null) $tokens = $this->getTokens();
    $builder->setTokens($tokens);
    $builder->setMethod(Twix::REQUEST_GET)->setUrl(self::ENDPOINT_SHOW);
    $builder->setFields($fields);
    $builder->setId($id);
    $builder->then($then);
    $this->scheduleAsyncTask($builder->getResult());
  }

  public function getProfileByID(string $uid,callable $then = null,array $fields = [],$id = null,$tokens = null)
  {
    $fields = ['user_id' => $uid] + $fields;
    $builder = $this->getBuilder();
    if($tokens === null) $tokens = $this->getTokens();
    $builder->setTokens($tokens);
    $builder->setMethod(Twix::REQUEST_GET)->setUrl(self::ENDPOINT_SHOW);
    $builder->setFields($fields);
    $builder->setId($id);
    $builder->then($then);
    $this->scheduleAsyncTask($builder->getResult());
  }

  public function scheduleAsyncTask(AsyncTask $task) { $this->getServer()->getScheduler()->scheduleAsyncTask($task); }

  public function getBuilder() { return new TwixBuilder(); }

  public function getTokens() { return $this->twix->getTokens(); }

  public function getTwix(): Twix { return $this->twix; }

  public function getServer(): Server { return $this->server; }
}