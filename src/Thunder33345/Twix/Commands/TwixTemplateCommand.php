<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Commands;

use pocketmine\command\CommandSender;
use Thunder33345\Twix\Twix;
use Thunder33345\Twix\TwixResult;
use pocketmine\Player;

class TwixTemplateCommand extends TwixCommand
{
  public function __construct(Twix $twix)
  {
    parent::__construct('',$twix);
    $this->setPermission('twix.');
    $this->setDescription('todo');
    $this->setUsage('todo');
    $this->setAliases(['todo']);
  }
  public function execute(CommandSender $sender,$commandLabel,array $args)
  {

  }
}