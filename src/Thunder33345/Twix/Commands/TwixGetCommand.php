<?php
/** Created By Thunder33345 **/

namespace Thunder33345\Twix\Commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use Thunder33345\Twix\Twix;
use Thunder33345\Twix\TwixResult;

class TwixGetCommand extends TwixCommand
{
  public function __construct(Twix $twix)
  {
    parent::__construct('twixget',$twix);
    $this->setPermission('twix.get');
    $this->setDescription('Get a twitter profile by username.');
    $this->setUsage('/twixget <username>');
    $this->setAliases(['twget','twg']);
  }

  public function execute(CommandSender $sender,$commandLabel,array $args)
  {
    if(!isset($args[0])) {
      $sender->sendMessage('Please include a username.');
      return false;
    }

    $user = str_replace('@','',$args[0]);

    $sender->sendMessage('Trying to obtain info of "@'.$user.'".');

    $function = function(TwixResult $result) {
      $id = $result->getId();
      $sender = $id['sender'];
      $user = $id['user'];
      $messages = [];
      if($result->getHttpRespond() !== 200) {
        $messages[] = 'Failed to obtain info about your "@'.$user.'" account.';
        $messages[] = 'HTTP Code: '.$result->getHttpRespond().' Respond: '.$result->getRespond();
      } else {
        $data = json_decode($result->getRespond(),true);
//        var_dump($data['entities']);

        if($data['verified']) $data['verified'] = 'Yes'; else$data['verified'] = 'No';
        $data['description'] = preg_replace("/\r|\n/"," ",$data['description']);
        $messages[] = 'Username: @'.$data['screen_name'].' Name: '.$data['name'].' Verified: '.$data['verified'];
        $messages[] = 'Biography: '.$data['description'];//todo phrase URLS
        $messages[] = 'Location: '.$data['location'];
        $messages[] = 'Followers: '.$data['followers_count'];
        $messages[] = 'Following: '.$data['friends_count'];
        $messages[] = 'Posts: '.$data['statuses_count'].' | Favourites: '.$data['favourites_count'];
      }

      $player = $result->getServer()->getPlayer($sender);

      if($player instanceof Player) foreach($messages as $message) $player->sendMessage($message);

      elseif($sender == "CONSOLE") foreach($messages as $message) $result->getServer()->getLogger()->info($message);
    };

    $this->getPlugin()->getTwixHelper()->getProfileByUsername($user,$function,[],['sender' => $sender->getName(),'user' => $user]);
    return true;
  }
}