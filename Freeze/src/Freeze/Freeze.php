<?php

namespace Freeze;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerJoinEvent;

class Freeze extends PluginBase implements Listener {
	private $player_status, $db;
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getServer ()->getLogger ()->info ( TextFormat::AQUA . "[ 프리즈 ] 꽁꽁 얼어붙어라 !" );
		$this->getServer ()->getLogger ()->info ( TextFormat::AQUA . "[ 프리즈 ] 널 얼려버리겠다 !" );
		@mkdir ( $this->getDataFolder () );
		$this->player_status = new Config ( $this->getDataFolder () . "playerDB.yml", Config::YAML );
		$this->db = $this->player_status->getAll ();
	}
	public function onJoin(PlayerJoinEvent $event) {
		$name = $event->getPlayer ()->getName ();
		$this->writePlayerData ( $name );
	}
	/**
	 * public function onChat(PlayerChatEvent $event) {
	 * $player = $event->getPlayer ();
	 * $name = $player->getName ();
	 * if ($this->is_mute ( $name ) == true) {
	 * $event->setCancelled ();
	 * $player->sendMessage ( TextFormat::RED . "[서버 ] 뮤트 처리되어 채팅이 불가능합니다 !" );
	 * $event->setMessage("");
	 * $event->getHandlers()->bake();
	 * }
	 * }
	 */
	public function onMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer ();
		$name = $player->getName ();
		if ($this->is_frozen ( $name ) == true) {
			$event->setCancelled ();
			$player->sendMessage ( TextFormat::AQUA . "[ 서버 ] 꽁꽁 얼어서 움직일 수 없습니다 !" );
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if (! isset ( $args [0] )) {
			$sender->sendMessage ( TextFormat::AQUA . "======== 사용법 ========" );
			$sender->sendMessage ( TextFormat::AQUA . "/프리즈 <플레이어명>" );
			$sender->sendMessage ( TextFormat::AQUA . "/뮤트 <플레이어명>" );
		}
		if ($command == "프리즈") {
			if (! isset ( $this->db [$args [0]] )) {
				$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 들어온 적이 없습니다." );
				return true;
			}
			if ($this->is_frozen ( $args [0] ) == false) {
				$this->makeFreeze ( $args [0] );
				$sender->sendMessage ( TextFormat::AQUA . "[ 서버 ] " . $args [0] . "님을 꽁꽁 얼렸습니다." );
			} else {
				$this->Melt ( $args [0] );
				$sender->sendMessage ( TextFormat::AQUA . "[ 서버 ] " . $args [0] . "님을 녹였습니다." );
			}
		}
	}
	public function makeFreeze($name) {
		$this->db [$name] ["frozen"] = true;
		$this->saveData ();
	}
	/**
	 * public function Mute($name) {
	 * $this->db [$name] ["mute"] = true;
	 * $this->saveData ();
	 * }
	 * public function unMute($name) {
	 * $this->db [$name] ["mute"] = false;
	 * $this->saveData ();
	 * }
	 */
	public function Melt($name) {
		$this->db [$name] ["frozen"] = false;
		$this->saveData ();
	}
	/**
	 * public function is_mute($name) {
	 * return $this->db [$name] ["mute"];
	 * }
	 */
	public function is_frozen($name) {
		return $this->db [$name] ["frozen"];
	}
	public function writePlayerData($name) {
		if (! isset ( $this->db [$name] )) {
			$this->db [$name] = [ ];
			$this->db [$name] ["frozen"] = false;
		/**
		 * $this->db [$name] ["mute"] = false;
		 * $this->saveData ();
		 */
		}
	}
	public function saveData() {
		$this->player_status->setAll ( $this->db );
		$this->player_status->save ();
	}
}