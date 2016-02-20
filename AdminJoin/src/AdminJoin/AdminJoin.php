<?php

namespace AdminJoin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;

class AdminJoin extends PluginBase implements Listener {
	private $message, $db, $config, $ops;
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->message = new Config ( $this->getDataFolder () . "messages.yml", Config::YAML );
		$this->db = $this->message->getAll ();
		$this->writeData ();
		$this->config = new Config ( $this->getDataFolder () . "config.yml", Config::YAML );
		$this->ops = $this->config->getAll ();
		$this->writeOpList ();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	public function onJoin(PlayerJoinEvent $event) {
		$name = $event->getPlayer ()->getName ();
		if ($name == $this->ops ["JoinMessages"] ["admin"]) {
			$event->setJoinMessage ( null );
			$this->getServer ()->broadcastMessage ( str_replace ( "@player", $name, $this->db ["messages"] ["admin-join"] ) );
			return true;
		}
		if ($name == $this->ops ["JoinMessages"] ["sub-admin"]) {
			$event->setJoinMessage ( null );
			$this->getServer ()->broadcastMessage ( str_replace ( "@player", $name, $this->db ["messages"] ["sub-admin-join"] ) );
			return true;
		}
		if ($name == $this->ops ["JoinMessages"] ["paid"]) {
			$event->setJoinMessage ( null );
			$this->getServer ()->broadcastMessage ( str_replace ( "@player", $name, $this->db ["messages"] ["paid-join"] ) );
			return true;
		}
		if ($event->getPlayer ()->isOp ()) {
			$event->setJoinMessage ( null );
			$this->getServer ()->broadcastMessage ( str_replace ( "@player", $name, $this->db ["messages"] ["op-join"] ) );
		}
	}
	public function writeData() {
		if (! isset ( $this->db ["messages"] )) {
			$this->db ["messages"] = [ ];
			$this->db ["messages"] ["admin-join"] = "§b[ 서버 ] 어드민 @player 님이 서버에 접속하셨습니다 !";
			$this->db ["messages"] ["sub-admin-join"] = "§c[ 서버 ] 부어드민 @player 님이 서버에 접속하셨습니다 !";
			$this->db ["messages"] ["op-join"] = "§a[ 서버 ] OP @player 님이 서버에 접속하셨습니다 !";
			$this->db ["messages"] ["paid-join"] = "§6[ 서버 ] 후원자 @player 님이 서버에 접속하셨습니다 !";
			$this->saveData ();
		}
	}
	public function writeOpList() {
		if (! isset ( $this->ops ["JoinMessages"] )) {
			$this->ops ["JoinMessages"] = [ ];
			$this->ops ["JoinMessages"] ["admin"] = "notSetted";
			$this->ops ["JoinMessages"] ["sub-admin"] = "notSetted";
			$this->ops ["JoinMessages"] ["paid"] = "notSetted";
			$this->config->setAll ( $this->ops );
			$this->config->save ();
		}
	}
	public function saveData() {
		$this->message->setAll ( $this->db );
		$this->message->save ();
	}
}
?>