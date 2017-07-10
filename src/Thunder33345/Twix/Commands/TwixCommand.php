<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Commands;

use pocketmine\command\PluginCommand;
use Thunder33345\Twix\Twix;

class TwixCommand extends PluginCommand
{
  protected $owningPlugin;

  public function __construct($name,Twix $twix)
  {
    parent::__construct($name,$twix);
    $this->owningPlugin = $twix;
    $this->setPermissionMessage('Insufficient Permission');
  }

  /**
   * @return Twix
   */
  public function getPlugin()
  {
    return $this->owningPlugin;
  }

  public function getServer(){
    return $this->owningPlugin->getServer();
  }
}