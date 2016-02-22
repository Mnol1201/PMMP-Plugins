<?php

namespace MyPlugin;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class MyPlugin extends PluginBase implements Listener {
	private $pluginlog, $pdb;
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getServer ()->getLogger ()->info ( TextFormat::RED . "[ 마이플러그인 ] 플러그인을 관리하는 플러그인" );
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if ($command == "플러그인") {
			if (! $sender instanceof Player) {
				if (! isset ( $args [0] )) {
					$this->errorMsg ( $sender );
					return true;
				}
				switch ($args [0]) {
					case "비활성화" :
						if (! isset ( $args [1] )) {
							$this->errorMsg ( $sender );
							break;
						}
						if ($this->getServer ()->getPluginManager ()->getPlugin ( $args [1] ) == null) {
							$sender->sendMessage ( TextFormat::RED . "그런 플러그인은 없습니다." );
							break;
						}
						$this->disable ( $args [1] );
						return true;
					case "활성화" :
						if (! isset ( $args [1] )) {
							$this->errorMsg ( $sender );
							break;
						}
						$this->enable ( $args [1] );
						return true;
					case "리스트" :
						$this->getServer ()->getCommandMap ()->dispatch ( $sender, "plugins" );
						return true;
					default :
						$this->errorMsg ( $sender );
				}
			} else {
				$sender->sendMessage ( TextFormat::RED . "콘솔에서만 사용할 수 있습니다." );
			}
		}
	}
	public function disable($plugin) {
		$this->getServer ()->getPluginManager ()->disablePlugin ( $this->getServer ()->getPluginManager ()->getPlugin ( $plugin ) );
	}
	public function enable($plugin) {
		$this->getServer ()->getPluginManager ()->enablePlugin ( $this->getServer ()->getPluginManager ()->getPlugin ( $plugin ) );
	}
	public function errorMsg($player) {
		if (! $player instanceof Player) {
			$player->sendMessage ( TextFormat::BLUE . "사용법: /플러그인 <비활성화|활성화> <플러그인명>" );
			$player->sendMessage ( TextFormat::BLUE . "사용법: /플러그인 리스트" );
		}
	}
}