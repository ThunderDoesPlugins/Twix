<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Utils;
use Thunder33345\Twix\Twix;

class UpdateGetter extends AsyncTask
{
  private $endpoint;
  private $error;
  private $data;

  public function __construct($endpoint)
  {
    parent::__construct();
    $this->endpoint = $endpoint;
  }

  public function onRun()
  {
    $error = '';
    $this->data = Utils::getURL($this->endpoint,30,[],$error);
    $this->error = $error;

  }

  public function onCompletion(Server $server)
  {
    $twix = $server->getPluginManager()->getPlugin('Twix');
    if(!$twix instanceof Twix) {
      $server->getLogger()->notice(UpdateChecker::PREFIX.' Error While trying to check update(cant get self plugin)');
      return;
    }
    if($twix->isDisabled()) return;
    $updater = $twix->getUpdateChecker();
    if(!$updater instanceof UpdateChecker) return;
    if($this->error !== ''){
      $server->getLogger()->notice(UpdateChecker::PREFIX.' Update check failed due to "'.$this->error.'"');
      return;
    }
    $updater->updateCallBack($this->data);
  }
}