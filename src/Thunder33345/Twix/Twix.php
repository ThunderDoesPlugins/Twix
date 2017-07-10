<?php
/** Created By Thunder33345 **/
//declare(strict_types=1);
namespace Thunder33345\Twix;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\plugin\PluginBase;
use Thunder33345\Twix\Commands\TwixGetCommand;
use Thunder33345\Twix\Commands\TwixPostCommand;
use Thunder33345\Twix\Commands\TwixVerifyCommand;
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

  private $tokens = [];
  /**
   * @var UpdateChecker $updateChecker
   */
  private $updateChecker;
  private $hasUpdate = false;

  /**
   * @var TwixHelper $twixHelper
   */
  private $twixHelper;

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

    $this->twixHelper = new TwixHelper($this);

    $this->getServer()->getCommandMap()->register('twixpost',new TwixPostCommand($this));
    $this->getServer()->getCommandMap()->register('twixverify',new TwixVerifyCommand($this));
    $this->getServer()->getCommandMap()->register('twixget',new TwixGetCommand($this));
  }

  public function onDisable()
  {

  }

  public function onCommand(CommandSender $sender,Command $command,$label,array $args)
  {
    //todo redirect twix to sub commands
    if($label == 'twix'){
      $helps = [
       'Commands:',
       '/Command (args) (shortcut)',
       '/TwixGet <User> (twg)',
       '/TwixPost <message> (twp)',
       '/TwixVerify (twv)',
      ];
      foreach($helps as $help)
      $sender->sendMessage($help);
    }
  }

  public function EventPlayerJoin(PlayerJoinEvent $joinEvent)
  {
    $player = $joinEvent->getPlayer();
    if($this->hasUpdate AND $player->hasPermission('twix.update')) $this->getUpdateChecker()->showPlayerUpdate($player);
  }

  public function getTwixHelper(){return $this->twixHelper;}

  public function getBuilder() { return new TwixBuilder(); }

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