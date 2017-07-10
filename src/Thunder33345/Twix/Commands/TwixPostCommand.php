<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use Thunder33345\Twix\Twix;
use Thunder33345\Twix\TwixResult;

class TwixPostCommand extends TwixCommand
{
  public function __construct(Twix $twix)
  {
    parent::__construct('twixpost',$twix);
    $this->setPermission('twix.post');
    $this->setDescription('Post a tweet to twitter.');
    $this->setUsage('/twixpost <message>');
    $this->setAliases(['twpost','twp']);
  }

  public function execute(CommandSender $sender,$commandLabel,array $args)
  {
    if(!isset($args[0])) {
      $sender->sendMessage('Please include a message.');
      return false;
    }
    $string = implode(" ",$args);
    $sender->sendMessage("Trying to post '$string'...");

    $function = function(TwixResult $result) {
      $id = $result->getId();
      $sender = $id['sender'];
      $msg = $id['msg'];
      $messages = [];
      if($result->getHttpRespond() !== 200) {
        $messages[] = 'Failed to send your post('.$msg.').';
        $messages[] = 'HTTP Code: '.$result->getHttpRespond().' Respond: '.$result->getRespond();
      } else {
        $messages[] = 'Your post has been sent.';
      }
      $player = $result->getServer()->getPlayer($sender);
      if($player instanceof Player) foreach($messages as $message) $player->sendMessage($message);

      elseif($sender == "CONSOLE") foreach($messages as $message) $result->getServer()->getLogger()->info($message);

      $result->getServer()->getLogger()->info('Twix: "'.$sender.'" Posted "'.$msg.'" to twitter.');
    };

    $this->getPlugin()->getTwixHelper()->sendPost($string,$function,[],['sender' => $sender->getName(),'msg' => $string]);
    return true;
  }
}