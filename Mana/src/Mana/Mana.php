<?php

namespace Mana;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\scheduler\PluginTask;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class Mana extends PluginBase implements Listener {
	private $mana, $db, $config;
	private static $instance;
	public function onLoad()
	{
		self::$instance = $this;
	}
	public function onEnable() {
		$this->getServer ()->getLogger ()->info ( TextFormat::BLUE . "[ Mana ] 타카미야 마나...아니 마나 플러그인 활성화 !" );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		@mkdir ( $this->getDataFolder () );
		$this->mana = new Config ( $this->getDataFolder () . "mana.yml", Config::YAML );
		$this->db = $this->mana->getAll ();
		$task = new IncreaseManaTask ( $this, $this );
		$this->config = (new Config ( $this->getDataFolder () . "config.yml", Config::YAML, [ 
				"period" => 60,
				"amount" => 60 
		] ))->getAll ();
		$this->getServer ()->getScheduler ()->scheduleRepeatingTask ( $task, $this->config ["period"] * 20 );
	}
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer ();
		$name = $player->getName ();
		if (! isset ( $this->db [$name] )) {
			$this->db [$name] = [ ];
			$this->db [$name] ["mana"] = 0;
			$this->saveMana ();
		}
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if ($command == "마나설정") {
			if (! isset ( $args [0] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $args [1] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $this->db [$args [0]] )) {
				$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 서버에 들어온 적이 없습니다." );
				return true;
			}
			if (! is_numeric ( $args [1] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			$this->setMana ( $args [0], $args [1] );
			$sender->sendMessage ( TextFormat::BLUE . "[ 마나 ] 성공적으로 명령을 실행했습니다." );
			return true;
		}
		if ($command == "마나뺏기") {
			if (! isset ( $args [0] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $args [1] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $this->db [$args [0]] )) {
				$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 서버에 들어온 적이 없습니다." );
				return true;
			}
			if (! is_numeric ( $args [1] )) {
				$this->usageMsg ( $sender );
				$sender->sendMessage ( TextFormat::BLUE . "[ 마나 ] 성공적으로 명령을 실행했습니다." );
				return true;
			}
			$this->decreaseMana ( $args [0], $args [1] );
			return true;
		}
		if ($command == "마나주기") {
			if (! isset ( $args [0] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $args [1] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			if (! isset ( $this->db [$args [0]] )) {
				$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 서버에 들어온 적이 없습니다." );
				return true;
			}
			if (! is_numeric ( $args [1] )) {
				$this->usageMsg ( $sender );
				return true;
			}
			$this->increaseMana ( $args [0], $args [1] );
			$sender->sendMessage ( TextFormat::BLUE . "[ 마나 ] 성공적으로 명령을 실행했습니다." );
			return true;
		}
		if ($command == "내마나") {
			$name = $sender->getName ();
			$sender->sendMessage ( TextFormat::BLUE . "당신의 마나: " . TextFormat::YELLOW . $this->seeMana ( $name ) );
			return true;
		}
		if ($command == "마나보기") {
			if (! isset ( $args [0] )) {
				$sender->sendMessage ( TextFormat::RED . "사용법: /마나보기 <닉네임>" );
				return true;
			}
			if (! isset ( $this->db [$args [0]] )) {
				$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 서버에 들어온 적이 없습니다." );
				return true;
			}
			$sender->sendMessage ( TextFormat::BLUE . $args [0] . "님의 마나: " . TextFormat::YELLOW . $this->seeMana ( $args [0] ) );
			return true;
		}
	}
	public function manaAutoIncrease($player) {
		$this->db [$player->getName ()] ["mana"] += $this->config ["amount"];
		$this->saveMana ();
	}
	/**
	 * 
	 * @param string $player
	 * @param string $amount
	 */
	public function increaseMana($player, $amount) {
		$this->db [$player]["mana"] += $amount;
		$this->saveMana ();
	}
	/**
	 * 
	 * @param string $player
	 * @param string $amount
	 */
	public function decreaseMana($player, $amount) {
		$this->db [$player]["mana"] -= $amount;
		$this->saveMana ();
	}
	/**
	 * 
	 * @param string $player
	 * @param string $amount
	 */
	public function setMana($player, $amount) {
		$this->db [$player]["mana"] = $amount;
		$this->saveMana ();
	}
	public function saveMana() {
		$this->mana->setAll ( $this->db );
		$this->mana->save ();
	}
	public function seeMana($player) {
		return $this->db [$player]["mana"];
	}
	public static function getInstance(){
		return self::$instance;
	}
	public function usageMsg($sender) {
		if ($sender instanceof Player) {
			$sender->sendMessage ( TextFormat::DARK_AQUA . "========사용법========" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나설정 <닉네임> <숫자>" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나뺏기 <닉네임> <숫자>" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나주기 <닉네임> <숫자>" );
		} else {
			$sender->sendMessage ( TextFormat::DARK_AQUA . "========사용법========" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나설정 <닉네임> <숫자>" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나뺏기 <닉네임> <숫자>" );
			$sender->sendMessage ( TextFormat::DARK_AQUA . "/마나주기 <닉네임> <숫자>" );
		}
	}
	public function onDisable() {
		$this->saveMana ();
	}
}
class IncreaseManaTask extends PluginTask {
	protected $owner, $plugin;
	public function __construct(Plugin $owner, Mana $plugin) {
		parent::__construct ( $owner );
		$this->plugin = $plugin;
	}
	public function onRun($currentTick) {
		foreach ( $this->plugin->getServer ()->getOnlinePlayers () as $player ) {
			$this->plugin->manaAutoIncrease ( $player );
		}
	}
}
