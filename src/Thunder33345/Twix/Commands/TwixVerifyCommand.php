<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use Thunder33345\Twix\Twix;
use Thunder33345\Twix\TwixResult;

class TwixVerifyCommand extends TwixCommand
{
  public function __construct(Twix $twix)
  {
    parent::__construct('twixverify',$twix);
    $this->setPermission('twix.verify');
    $this->setDescription('Get Info Of Your Current Account');
    $this->setUsage('/twixverify');
    $this->setAliases(['twverify','twv']);
  }

  public function execute(CommandSender $sender,$commandLabel,array $args)
  {
    $sender->sendMessage('Trying to obtain info of your current account.');

    $function = function(TwixResult $result) {
      $id = $result->getId();
      $sender = $id['sender'];
      $messages = [];
      if($result->getHttpRespond() !== 200) {
        $messages[] = 'Failed to obtain info about your current account.';
        $messages[] = 'HTTP Code: '.$result->getHttpRespond().' Respond: '.$result->getRespond();
      } else {
        $data = json_decode($result->getRespond(),true);
        //these cant be used as an attack vector right?? hopefully it's fine...
        if($data['verified']) $data['verified'] = 'Yes'; else$data['verified'] = 'No';
        $data['description'] = preg_replace("/\r|\n/"," ",$data['description']);
        $messages[] = 'Username: @'.$data['screen_name'].' Name: '.$data['name'].' Verified: '.$data['verified'];
        $messages[] = 'Biography: '.$data['description'];
        $messages[] = 'Location: '.$data['location'];
        $messages[] = 'Followers: '.$data['followers_count'];
        $messages[] = 'Following: '.$data['friends_count'];
        $messages[] = 'Posts: '.$data['statuses_count'].' | Favourites: '.$data['favourites_count'];
      }

      $player = $result->getServer()->getPlayer($sender);

      if($player instanceof Player) foreach($messages as $message) $player->sendMessage($message);

      elseif($sender == "CONSOLE") foreach($messages as $message) $result->getServer()->getLogger()->info($message);
    };

    $this->getPlugin()->getTwixHelper()->verifyCredentials($function,[],['sender' => $sender->getName()]);
    return true;
  }

}