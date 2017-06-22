<?php
/** Created By Thunder33345 **/
//declare(strict_types=1);
namespace Thunder33345\Twix;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\AsyncTask;
use Thunder33345\Twix\Updater\UpdateChecker;

class Twix extends PluginBase implements Listener
{
  /*
   * TODO list
   * list:
   *
   *  Test Update Notifier
   *
   * X Serialize Exception: thrown on TwixBuilder::setId() if object is not serializable
   * X Closure Error exception: thrown on TwixFetcher::onCompletion(), warps around original exception if possible
   * X Invalid closure exception: thrown when trying to use TwixBuilder::then() with invalid closure on builder
   * X Missing argument exception: thrown on TwixBuilder::getResults() with missing mandatory parameters
   * X Invalid method exception: thrown on TwixBuilder::setMethod() and TwixFetcher::__construct() if the method is not GET or POST
   * X Missing token exception: thrown on TwixBuilder::setToken() and TwixFetcher::__construct() if the token are not set/invalid format
   */
  const REQUEST_GET = 'get';
  const REQUEST_POST = 'post';
  const ENDPOINT_UPDATE = 'https://api.twitter.com/1.1/statuses/update.json';
  private $tokens = [];
  /**
   * @var UpdateChecker $updateChecker
   */
  private $updateChecker;
  private $hasUpdate = false;

  public function onLoad()
  {

  }

  public function onEnable()
  {
    @mkdir($this->getDataFolder());
    $this->saveDefaultConfig();
    $this->saveResource('config_secret.yml');
    if(file_exists($this->getDataFolder().'/config_secret.yml')) {// used for git
      $this->getLogger()->warning('DEV MODE ACTIVE! - Attempting to overwrite default config with secret file...');
      $newFile = $this->getDataFolder().'/config_secret.yml';
      //$ref = new \ReflectionClass($this); //blame shogic //give up lol
      $oldFile = $this->getDataFolder().'/config.yml';
      file_put_contents($oldFile,file_get_contents($newFile));
      $this->getConfig()->reload();
    }
    $this->getServer()->getPluginManager()->registerEvents($this,$this);
    $this->reloadTokens();
    $this->updateChecker = new UpdateChecker($this,$this->getConfig());
    //$this->getServer()->getUpdater()->getUpdateInfo();
  }

  public function onDisable()
  {

  }

  public function onCommand(CommandSender $sender,Command $command,$label,array $args)
  {
    if(!$sender->hasPermission('twix.use')) {
      $sender->sendMessage('Insufficient Permissions');
      return;
    }
    if(!isset($args[0])) {
      $sender->sendMessage('please include msg');
      return;
    }
    $string = implode(" ",$args);
    $sender->sendMessage("Trying to post '$string'");
    $function = function(TwixResult $result) {
      $id = $result->getId();
      $sender = $id['sender'];
      $msg = $id['msg'];
      if($result->getHttpRespond() !== 200) {
        $result->getServer()->getLogger()->info('Tweet: "'.$sender.'" Post ('.$msg.') Has Fail To Sent');
        $player = $result->getServer()->getPlayer($sender);
        if($player instanceof Player) $player->sendMessage('Your Post Has Fail To Sent');
        $result->getServer()->getLogger()->info('Tweet Failed To Sent HTTP Code: '.$result->getHttpRespond().' Respond: '.$result->getRespond());
        return;
      }
      $result->getServer()->getLogger()->info('Tweet: "'.$sender.'" Posted "'.$msg.'" to twitter!');
      $player = $result->getServer()->getPlayer($sender);
      if($player instanceof Player) $player->sendMessage('Your Post "'.$msg.'" Has Been Sent');
    };
    $this->sendPost($string,$function,[],['sender' => $sender->getName(),'msg' => $string]);
  }

  public function EventPlayerJoin(PlayerJoinEvent $joinEvent)
  {
    $player = $joinEvent->getPlayer();
    if($this->hasUpdate AND $player->hasPermission('twix.update')) $this->getUpdateChecker()->showPlayerUpdate($player);
  }

  public function sendPost(string $message,callable $then = null,array $fields = [],$id = null)
  {
    $fFields = ['status' => $message] + $fields;
    $builder = $this->getBuilder();
    $builder->setTokens($this->getTokens());
    $builder->setMethod(self::REQUEST_POST);
    $builder->setUrl(self::ENDPOINT_UPDATE);
    $builder->setFields($fFields);
    $builder->setId($id);
    $builder->then($then);
    $result = $builder->getResult();
    $this->scheduleAsyncTask($result);
  }

  public function getBuilder() { return new TwixBuilder(); }

  private function scheduleAsyncTask(AsyncTask $task) { $this->getServer()->getScheduler()->scheduleAsyncTask($task); }

  public function getTokens(): array { return $this->tokens; }

  public function reloadTokens()
  {
    $this->getConfig()->reload();
    //@formatter:off
    $this->tokens = [
     'consumer_key'=> $this->getConfig()->get('consumer-key'),
     'consumer_secret'=> $this->getConfig()->get('consumer-secret'),
     'oauth_access_token' => $this->getConfig()->get('oauth-token'),
     'oauth_access_token_secret'=> $this->getConfig()->get('oauth-secret'),
    ];
    //@formatter:on
  }

  public function getUpdateChecker() { return $this->updateChecker; }
}