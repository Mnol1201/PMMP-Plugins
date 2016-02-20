<?php

namespace TerrorManager;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class TerrorManager extends PluginBase implements Listener {
	private $subnet, $sdb, $playersub;
	public function onEnable() {
		$this->getServer ()->getLogger ()->error ( "[ 테러매니저 ] 이제 이 서버에는 테러가 허용되지 않습니다 !" );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		@mkdir ( $this->getDataFolder () );
		$this->subnet = new Config ( $this->getDataFolder () . "subnet.yml", Config::YAML );
		$this->sdb = $this->subnet->getAll ();
	}
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer ();
		$name = $event->getPlayer ()->getName ();
		$ip = $event->getPlayer ()->getAddress ();
		$sb = explode ( ".", $ip );
		if (! isset ( $this->sdb [$sb [0] . "." . $sb [1] . "." . $sb [2]] )) {
			$this->sdb [$sb [0] . "." . $sb [1] . "." . $sb [2]] = [ ];
			$this->sdb [$sb [0] . "." . $sb [1] . "." . $sb [2]] ["sban"] = false;
			$this->sdb [$sb [0] . "." . $sb [1] . "." . $sb [2]] ["onuse"] = $name;
			$this->subnet->setAll ( $this->sdb );
			$this->subnet->save ();
			return true;
		}
		if ($this->sdb [$sb [0] . "." . $sb [1] . "." . $sb [2]] ["sban"] == true) {
			$this->kickPlayer ( $player );
			return true;
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if (! isset ( $args [0] )) {
			$sender->sendMessage ( TextFormat::BLUE . "사용법: /서브넷밴 <서브넷>" );
			$sender->sendMessage ( TextFormat::BLUE . "(예시) /서브넷밴 192.168.0" );
			$sender->sendMessage ( TextFormat::BLUE . "사용법: /서브넷밴해제 <서브넷>" );
			$sender->sendMessage ( TextFormat::BLUE . "(예시) /서브넷밴해제 192.168.0" );
			return true;
		}
		$token = $args [0];
		$subnet = explode ( ".", $token );
		if (count ( $subnet ) != 3) {
			$sender->sendMessage ( TextFormat::RED . "올바른 서브넷 형식이 아닙니다 !" );
			return true;
		}
		if (! isset ( $this->sdb [$args[0]] )) {
			$sender->sendMessage ( TextFormat::RED . "그런 서브넷을 가진 플레이어가 없습니다 !" );
			return true;
		}

		if ($command == "서브넷밴") {
			if ($this->sdb [$args[0]] ["sban"] == false) {
				$this->sdb [$args[0]] ["sban"] = true;
				$this->subnet->setAll ( $this->sdb );
				$this->subnet->save ();
				$this->broadcastMsg ( TextFormat::RED . "[ 테러매니저 ] " . $sender->getName () . "님이 " . $args [0] . " 을 서브넷밴 처리하셨습니다 !" );
				$target = $this->getServer()->getPlayer($this->sdb [$args[0]] ["onuse"]);
				$this->kickPlayer($target);
			} else {
				$sender->sendMessage ( TextFormat::RED . "그 서브넷은 이미 밴처리 되어 있습니다." );
				return true;
			}
		}
		if ($command == "서브넷밴해제") {
			if ($this->sdb [$args[0]] ["sban"]) {
				$this->sdb [$args[0]] ["sban"] = false;
				$this->subnet->setAll ( $this->sdb );
				$this->subnet->save ();
				$this->broadcastMsg ( TextFormat::GREEN . "[ 테러매니저 ] " . $sender->getName () . "님이 " . $args [0] . " 을 서브넷밴해제 하셨습니다 !" );
			} else {
				$sender->sendMessage ( TextFormat::RED . "그 서브넷은 이미 밴해제 처리 되어 있습니다." );
				return true;
			}
		}
		if($command == "서브넷정보"){
			if($this->sdb[$args[0]]["sban"] == true){
				$sender->sendMessage(TextFormat::RED . "이 서브넷은 밴처리 되어있습니다.");
				$sender->sendMessage(TextFormat::YELLOW . "이 서브넷을 사용중인 플레이어: " . $this->sdb [$args[0]] ["onuse"]);
			}
			else {
				$sender->sendMessage(TextFormat::GREEN . "이 서브넷은 밴처리 되지 않았습니다.");
				$sender->sendMessage(TextFormat::YELLOW . "이 서브넷을 사용중인 플레이어: " . $this->sdb [$args[0]] ["onuse"]);
			}
		}
	}
	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$name = $player->getName ();
		$id = $event->getItem ()->getId ();
		if ($id == 46 || $id == 10 || $id == 11 || $id == 325 && $event->getItem ()->getDamage () == 10) {
			$event->setCancelled ();
			$this->broadcastMsg ( TextFormat::RED . "[ 테러매니저 ]" . $name . " 님이 테러를 시도하였습니다 !" );
			$player->kick ( "테러 시도로 킥당하셨습니다." );
		}
	}
	public function onChat(PlayerChatEvent $event) {
		$chat = $event->getMessage ();
		$fltrmsg = array (
				"섹스",
				"자지",
				"보지",
				"시발",
				"씨발",
				"개새",
				"느금",
				"꼬추",
				"보추",
				"fuck",
				"sex",
				"애미",
				"ㅂㅅ",
				"ㅅㅂ",
				"앰창",
				"븅신",
				"병신",
				"ㅉㅉ",
				"찌찌",
				"대가리",
				"ㄴㄱㅁ",
				"부랄",
				"불알",
				"고환",
				"정소",
				"시팔",
				"슈벌",
				"시벌",
				"싀발",
				"십팔",
				"쉬발",
				"씨팔",
				"난소",
				"빠구리",
				"새끼",
				"보털",
				"씹",
				"좃",
				"좇",
				"젖",
				"잦",
				"섹",
				"ㅉ",
				"ㅗ",
				"쯧",
				"섹.스",
				"자.지",
				"보.지",
				"시.발",
				"씨.발",
				"개.새",
				"느.금",
				"꼬.추",
				"보.추",
				"fu.ck",
				"s.ex",
				"애.미",
				"ㅂ.ㅅ",
				"ㅅ.ㅂ",
				"앰.창",
				"븅.신",
				"병.신",
				"ㅉ.ㅉ",
				"찌.찌",
				"대.가.리",
				"ㄴ.ㄱㅁ",
				"부.랄",
				"불.알",
				"고.환",
				"정.소",
				"시.팔",
				"슈.벌",
				"시.벌",
				"싀.발",
				"십.팔",
				"쉬.발",
				"씨.팔",
				"난.소",
				"빠.구리",
				"새.끼",
				"보.털",
				"섹ㅣ스",
				"자ㅣ지",
				"보ㅣ지",
				"시ㅣ발",
				"씨ㅣ발",
				"개ㅣ새",
				"느ㅣ금",
				"꼬ㅣ추",
				"보ㅣ추",
				"f1uck",
				"s1ex",
				"애ㅣ미",
				"ㅂㅣㅅ",
				"ㅅㅣㅂ",
				"앰ㅣ창",
				"븅ㅣ신",
				"병ㅣ신",
				"ㅉㅣㅉ",
				"찌ㅣ찌",
				"대ㅣ가리",
				"ㄴㅣㄱㅁ",
				"부ㅣ랄",
				"불ㅣ알",
				"고ㅣ환",
				"정ㅣ소",
				"시ㅣ팔",
				"슈ㅣ벌",
				"시ㅣ벌",
				"싀ㅣ발",
				"십ㅣ팔",
				"쉬ㅣ발",
				"씨ㅣ팔",
				"난ㅣ소",
				"빠ㅣ구리",
				"새ㅣ끼",
				"보ㅣ털",
				"섹1스",
				"자1지",
				"보1지",
				"시1발",
				"씨1발",
				"개1새",
				"느1금",
				"꼬1추",
				"보1추",
				"fu1ck",
				"s1ex",
				"애1미",
				"ㅂ1ㅅ",
				"ㅅ1ㅂ",
				"앰1창",
				"븅1신",
				"병1신",
				"ㅉ1ㅉ",
				"찌1찌",
				"대1가리",
				"ㄴ1ㄱㅁ",
				"부1랄",
				"불1알",
				"고1환",
				"정1소",
				"시1팔",
				"슈1벌",
				"시1벌",
				"싀1발",
				"십1팔",
				"쉬1발",
				"씨1팔",
				"난1소",
				"빠1구리",
				"새1끼",
				"보1털",
				"섹2스",
				"자2지",
				"보2지",
				"시2발",
				"씨2발",
				"개2새",
				"느2금",
				"꼬2추",
				"보2추",
				"fu2ck",
				"s2ex",
				"애2미",
				"ㅂ2ㅅ",
				"ㅅ2ㅂ",
				"앰2창",
				"븅2신",
				"병2신",
				"ㅉ2ㅉ",
				"찌2찌",
				"대2가리",
				"ㄴ2ㄱㅁ",
				"부2랄",
				"불2알",
				"고2환",
				"정2소",
				"시2팔",
				"슈2벌",
				"시2벌",
				"싀2발",
				"십2팔",
				"쉬2발",
				"씨2팔",
				"난2소",
				"빠2구리",
				"새2끼",
				"보2털",
				"섹ㅡ스",
				"자ㅡ지",
				"보ㅡ지",
				"시ㅡ발",
				"씨ㅡ발",
				"개ㅡ새",
				"느ㅡ금",
				"꼬ㅡ추",
				"보ㅡ추",
				"fuㅡck",
				"sㅡex",
				"애ㅡ미",
				"ㅂㅡㅅ",
				"ㅅㅡㅂ",
				"앰ㅡ창",
				"븅ㅡ신",
				"병ㅡ신",
				"ㅉㅡㅉ",
				"찌ㅡ찌",
				"대ㅡ가리",
				"ㄴㅡㄱㅁ",
				"부ㅡ랄",
				"불ㅡ알",
				"고ㅡ환",
				"정ㅡ소",
				"시ㅡ팔",
				"슈ㅡ벌",
				"시ㅡ벌",
				"싀ㅡ발",
				"십ㅡ팔",
				"쉬ㅡ발",
				"씨ㅡ팔",
				"난ㅡ소",
				"빠ㅡ구리",
				"새ㅡ끼",
				"보ㅡ털",
				"니기미" 
		);
		$filtered = str_replace ( $fltrmsg, "***", $chat );
		$event->setMessage ( $filtered );
	}
	public function kickPlayer($player) {
		if ($player instanceof Player) {
			$player->kick ( TextFormat::RED . "서브넷밴 처리되셨습니다." );
		}
	}
	public function broadcastMsg($message) {
		$this->getServer ()->broadcastMessage ( $message );
	}
	public function onDisable() {
		$this->getServer ()->getLogger ()->info ( TextFormat::RED . "[ 테러매니저 ] 오늘도 테러없는 즐거운 서버 되셨나요?" );
		$this->subnet->setAll ( $this->sdb );
		$this->subnet->save ();
	}
}