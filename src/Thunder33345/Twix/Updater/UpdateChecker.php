<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Updater;

use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Thunder33345\Twix\Twix;

//messy updater file,
//todo refactor maybe

class UpdateChecker
{
  const PREFIX = '[TwixUpdater]';
  const ENDPOINT = 'https://raw.githubusercontent.com/ThunderDoesPlugins/Twix/master/.updater.json';

  private $enable = false;
  private $hasUpdate = false;
  private $twix;
  private $server;
  private $config;

  private $data = [];
  private $branch = [];

  public function __construct(Twix $twix,Config $config)
  {
    $this->twix = $twix;
    $this->server = $twix->getServer();
    $config = $this->config = $config->get('updater');
    if($config['enable'] == true) $this->enable = true;

    if($this->isEnabled()) $this->doCheck();
  }

  public function doCheck() { $this->server->getScheduler()->scheduleAsyncTask(new UpdateGetter(self::ENDPOINT)); }

  public function updateCallBack($rawData)
  {
    $data = json_decode($rawData,true);
    if(!is_array($data)) {
      $this->server->getLogger()->info(self::PREFIX.' Error trying to phrase version string');
      $this->server->getLogger()->debug(self::PREFIX.' Reply from endpoint: '.$rawData);
      return;
    }

    $this->data = $data;

    $config = $this->config;
    $version = $this->twix->getDescription()->getVersion();
    if(!isset($config['branch'])) $branch = '*'; else $branch = $config['branch'];
    if(!isset($data[$branch])) $branch = reset($data);//need better way of falling back
    else $branch = $data[$branch];

    $branch['branch'] = array_search($branch,$data);
    //$branch['branch'] = key($branch);
    $this->branch = $branch;

    $upVersion = $branch['version'];
    if(version_compare($upVersion,$version) === 1) {
      $this->hasUpdate = true;
      $this->showConsoleUpdate();
    }
  }

  public function showConsoleUpdate()
  {
    $logger = $this->server->getLogger();
    $upTime = $this->branch["time"];
    $branch = $this->branch;
    $now = new \DateTime('@'.time());
    $upTimeDate = new \DateTime('@'.$upTime);
    $diff = $now->diff($upTimeDate);
    if($diff instanceof \DateInterval) $format = $diff->format('%a Days %h:%i:%s');

    $logger->warning('---------- Twix Update Notifier ----------');
    $logger->warning('Your current version of Twix is out of date. Current Version: '.$this->twix->getDescription()->getVersion());
    $str = 'Latest Version: '.$branch['version'].' Released on '.date("D M j h:i:s Y",$upTime);
    if(isset($format)) $str .= ' ('.$format.' ago)';
    $str .= ' Branch: '.$branch['branch'];
    $logger->warning($str);
    $logger->warning('Info: '.$branch['info']);
    $logger->warning('Short Info: '.$branch['short_info']);
    $logger->warning('Download Url: '.$branch['download_url']);
    $logger->warning('---------- '.str_repeat('-',20).' ----------');
  }

  public function showPlayerUpdate(Player $player)
  {
    $upTime = $this->branch["time"];
    $branch = $this->branch;
    $now = new \DateTime('@'.time());
    $upTimeDate = new \DateTime('@'.$upTime);
    $diff = $now->diff($upTimeDate);
    if($diff instanceof \DateInterval) $format = $diff->format('%a Days %h:%i:%s');
    $player->sendMessage(TextFormat::LIGHT_PURPLE.self::PREFIX.' Your Current Version of Twix ('.$this->twix->getDescription()->getVersion().') is out of date');

    $str = 'Latest Version: '.$branch['version'].' Released on '.date("D M j h:i:s Y",$upTime);
    if(isset($format)) $str .= ' ('.$format.' ago)';

    $player->sendMessage(TextFormat::LIGHT_PURPLE.self::PREFIX.' '.$str);
    $player->sendMessage(TextFormat::LIGHT_PURPLE.self::PREFIX.' Changes: '.$branch['short_info']);

  }

  public function hasUpdate() { return $this->hasUpdate; }

  public function isEnabled() { return $this->enable === true; }

  public function getBranch() { return $this->branch; }

  public function getData() { return $this->data; }

  private function __Template()
  {
    //@formatter:off
    $data = [
     'branchname...' =>
      [
       'time' => 'UNIX release time',
       'version' => '0.0.1',
       'info' => 'added some features, fixed some features!',
       'short_info' => 'some cool new feature!',
       'download_url' => 'http://127.0.0.1/download',
     ]
     //@formatter:on

    ];
  }
}
