<?php

namespace HungerGames;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Player;
use pocketmine\Plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class HungerGames extends PluginBase implements Listener {
	private $killrate, $message, $time, $killdb, $ongame = [ ];
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getServer ()->getLogger ()->notice ( TextFormat::BLUE . "[ 헝거게임 ] 플러그인이 활성화 되었습니다 !" );
		$this->getServer()->getLogger()->notice(TextFormat::BLUE . "[ 헝거게임 ] ChestRefill 플러그인과 함께 사용하는것을 권장합니다.");
		/**@mkdir ( $this->getDataFolder () );
		/*$this->killrate = new Config ( $this->getDataFolder () . "killrate.yml", Config::YAML );
		/*$this->killdb = $this->killrate->getAll ();
		 */
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if ($command == "게임시작") {
			$gamestarttask = new GamestartTask ( $this, $this );
			$this->ready ();
			$chestpickingtask = new ChestPickTask ( $this, $this );
			$this->getServer ()->getScheduler ()->scheduleDelayedTask ( $chestpickingtask, 600 );
			$this->getServer ()->getScheduler ()->scheduleDelayedTask ( $gamestarttask, 1200 );
			return true;
		}
		if ($command == "게임종료") {
			if (! $this->onGame () == true) {
				$sender->sendMessage ( TextFormat::RED . "[ 헝거게임 ] 지금은 게임 중이 아닙니다." );
			} else {
				$this->gameEnd ();
			}
			return true;
		}
	}
	public function onDamage(EntityDamageEvent $event) {
		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager ();
			if ($damager instanceof Player) {
				if ($this->ongame == 2 || $this->ongame == 3) {
					$event->setCancelled ();
					$damager->sendMessage ( TextFormat::RED . "[ 헝거게임 ] 아직 PVP를 하실 수 없습니다 !" );
				}
			}
		}
	}
	public function onChestTouch(PlayerInteractEvent $event) {
		if ($event->getBlock ()->getId () == 54) {
			if ($this->ongame == 2) {
				if (! $event->getPlayer ()->isOp ()) {
					$event->setCancelled ();
					$event->getPlayer ()->sendMessage ( TextFormat::RED . "[ 헝거게임 ] 아직 상자를 열 수 없습니다 !" );
				}
			}
		}
	}
	public function gameStart() {
		$this->ongame = 1;
		$this->getServer ()->broadcastMessage ( TextFormat::GREEN . "[ 헝거게임 ] PVP가 허용되었습니다 !" );
		$this->getServer ()->broadcastMessage ( TextFormat::GREEN . "[ 헝거게임 ] 싸워서 살아남으세요 !" );
	}
	public function ready() {
		$this->ongame = 2;
		$this->getServer ()->broadcastMessage ( TextFormat::RED . "[ 헝거게임 ] 게임 준비 시간입니다...[30초]" );
	}
	public function gameEnd() {
		$this->ongame = 0;
		$this->getServer ()->broadcastMessage ( TextFormat::RED . "[ 헝거게임 ] 게임이 종료되었습니다." );
	}
	public function ChestPicking() {
		$this->ongame = 3;
		$this->getServer ()->broadcastMessage ( TextFormat::GREEN . "[ 헝거게임 ] 앞으로 30초간 PVP가 금지됩니다." );
		$this->getServer ()->broadcastMessage ( TextFormat::GOLD . "[ 헝거게임 ] 상자에서 아이템을 가져가세요 !" );
	}
	public function onGame() {
		if ($this->ongame == 1) {
			return true;
		} else {
			return false;
		}
	}
}

// -------------------------------------------------------------------------------------------
class GamestartTask extends PluginTask {
	protected $owner, $plugin;
	public function __construct(Plugin $owner, HungerGames $plugin) {
		parent::__construct ( $owner );
		$this->plugin = $plugin;
	}
	public function onRun($currentTick) {
		$this->plugin->gameStart ();
	}
}
class ChestPickTask extends PluginTask {
	protected $owner, $plugin;
	public function __construct(Plugin $owner, HungerGames $plugin) {
		parent::__construct ( $owner );
		$this->plugin = $plugin;
	}
	public function onRun($currentTick) {
		$this->plugin->ChestPicking();
	}
}
