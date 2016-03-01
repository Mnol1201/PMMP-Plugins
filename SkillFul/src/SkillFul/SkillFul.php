<?php

namespace SkillFul;

use CashShop\CashShop;
use Mana\Mana;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\entity\Effect;

class SkillFul extends PluginBase implements Listener {
	private $job, $jdb, $kill, $kdb;
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->job = new Config ( $this->getDataFolder () . "jobs.yml", Config::YAML );
		$this->jdb = $this->job->getAll ();
		$this->kill = new Config ( $this->getDataFolder () . "kills.yml", Config::YAML );
		$this->kdb = $this->kill->getAll ();
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		$name = $sender->getName ();
		if ($command == "직업") {
			if (! $sender instanceof Player) {
				$sender->sendMessage ( TextFormat::RED . "콘솔 내에선 사용이 불가능합니다." );
				return true;
			}
			if (! isset ( $args [0] )) {
				$this->errorMsg ( $sender );
				return true;
			}
			switch ($args [0]) {
				case "가입" :
					if ($this->getJob ( $name ) !== "무소속") {
						$sender->sendMessage ( TextFormat::RED . "이미 다른 직업에 가입되어 있습니다 !" );
						break;
					}
					if (! isset ( $args [1] )) {
						$this->errorMsg ( $sender );
						break;
					}
					switch ($args [1]) {
						case "마법사" :
							$this->setJob ( $name, "마법사" );
							$sender->sendMessage ( TextFormat::DARK_PURPLE . "마법사로 전직하셨습니다 !" );
							break;
						case "전사" :
							$this->setJob ( $name, "전사" );
							$sender->sendMessage ( TextFormat::BLUE . "전사로 전직하셨습니다 !" );
							break;
						case "궁수" :
							$this->setJob ( $name, "궁수" );
							$sender->sendMessage ( TextFormat::YELLOW . "궁수로 전직하셨습니다 !" );
							break;
						default :
							$this->errorMsg ( $sender );
							break;
					}
				case "보기" :
					if (! isset ( $args [1] )) {
						$this->errorMsg ( $sender );
						break;
					}
					if (! isset ( $this->jdb [$args [1]] )) {
						$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 없습니다." );
						break;
					}
					$sender->sendMessage ( TextFormat::YELLOW . $args [1] . "님의 직업: " . $this->getJob ( $args [1] ) );
					break;
				case "경험치보기" :
					if (! isset ( $args [1] )) {
						$this->errorMsg ( $sender );
						break;
					}
					if (! isset ( $this->jdb [$args [1]] )) {
						$sender->sendMessage ( TextFormat::RED . "그런 플레이어는 없습니다." );
						break;
					}
					$sender->sendMessage ( TextFormat::YELLOW . $args [1] . "님의 경험치: " . $this->getExp ( $args [1] ) );
					break;
				case "탈퇴" :
					if ($this->getJob ( $name ) === "무소속") {
						$sender->sendMessage ( TextFormat::RED . "아무 직업에도 소속되어 있지 않습니다 !" );
						break;
					}
					$this->unregisterJob ( $name );
					$sender->sendMessage ( TextFormat::GREEN . "해당 직업을 그만두셨습니다 !" );
					break;
				case "내정보" :
					$sender->sendMessage ( TextFormat::YELLOW . "내 직업: " . TextFormat::GOLD . $this->getJob ( $name ) );
					$sender->sendMessage ( TextFormat::GOLD . "내 경험치: " . TextFormat::GOLD . $this->getExp ( $name ) );
					break;
			}
			return true;
		}
	}
	public function onBlockTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$id = $event->getItem ()->getId ();
		$world = $this->getWorld ( $player );
		$name = $player->getName ();
		$job = $this->getJob ( $name );
		$x = $event->getBlock ()->getX ();
		$y = $event->getBlock ()->getY ();
		$z = $event->getBlock ()->getZ ();
		if ($id === 369) {
			if ($world !== "world") {
				if ($job === "마법사") {
					if (Mana::getInstance ()->seeMana ( $name ) >= 30) {
						$packet = new AddEntityPacket ();
						$packet->eid = Entity::$entityCount ++;
						$packet->x = $x;
						$packet->y = $y;
						$packet->z = $z;
						$packet->type = 93;
						$packet->speedX = 0;
						$packet->speedY = 0;
						$packet->speedZ = 0;
						$packet->metadata = array ();
						$this->getServer ()->broadcastPacket ( $this->getServer ()->getOnlinePlayers (), $packet );
						$explosion = new Explosion ( new Position ( $x, $y, $z, $event->getBlock ()->getLevel () ), 1 );
						$explosion->explodeB ();
						Mana::getInstance ()->decreaseMana ( $name, 30 );
					} else {
						$player->sendMessage ( TextFormat::RED . "마나가 부족합니다." );
					}
				} else {
					$player->sendMessage ( TextFormat::RED . "마법사가 아닙니다 !" );
				}
			} else {
				$player->sendMessage ( TextFormat::RED . "여기서는 스킬을 사용할 수 없습니다 !" );
			}
			return true;
		}
		if ($id === Item::CLAY) {
			if ($world !== "world") {
				if ($job === "마법사") {
					if (Mana::getInstance ()->seeMana ( $name ) >= 30) {
						$explosion = new Explosion ( new Position ( $x, $y, $z, $event->getBlock ()->getLevel () ), 2 );
						$explosion->explodeB ();
						Mana::getInstance ()->decreaseMana ( $name, 30 );
					} else {
						$player->sendMessage ( TextFormat::RED . "마나가 부족합니다." );
					}
				} else {
					$player->sendMessage ( TextFormat::RED . "마법사가 아닙니다 !" );
				}
			} else {
				$player->sendMessage ( TextFormat::RED . "여기서는 스킬을 사용할 수 없습니다 !" );
			}
			return true;
		}
	}
	public function onHit(EntityDamageEvent $event) {
		$sac = $event->getEntity ();
		if ($sac instanceof Player) {
			$cause = $sac->getLastDamageCause ();
			if ($cause instanceof EntityDamageByEntityEvent) {
				$damager = $cause->getDamager ();
				if ($damager instanceof Player) {
					$dname = $damager->getName ();
					$sname = $sac->getName ();
					$id = $damager->getInventory ()->getItemInHand ();
					if ($id === Item::GOLD_SWORD) {
						if ($this->getWorld ( $damager ) !== "world") {
							if ($this->getJob ( $dname ) === "전사") {
								if(Mana::getInstance()->seeMana($dname) >= 100){
									Mana::getInstance()->decreaseMana($dname, 100);
									$sac->setHealth($sac->getHealth() - 4);
									$light = new AddEntityPacket();
									$light->x = $sac->getX();
									$light->y = $sac->getY();
									$light->z = $sac->getZ();
									$light->metadata = array();
									$light->speedX = 0;
									$light->speedY = 0;
									$light->speedZ = 0;
									$light->eid = Entity::$entityCount++;
									$this->getServer()->broadcastPacket($this->getServer()->getOnlinePlayers(), $light);
								} else {
									$damager->sendMessage(TextFormat::RED . "마나가 부족합니다.");
								}
							}
						} else {
							$event->setCancelled();
							$damager->sendMessage(TextFormat::RED . "여기서는 스킬을 사용할 수 없습니다 !");
						}
						return true;
					}
					if($id === Item::REDSTONE){
						if($this->getWorld($damager) !== "world"){
							if($this->getJob($dname) === "마법사"){
								if(Mana::getInstance()->seeMana($dname) >= 100){
									Mana::getInstance()->decreaseMana($dname, 100);
									$effect = Effect::getEffect(19);
									$effect->setDuration(60);
									$effect->setAmplifier(2);
									$effect->setColor(255, 0, 0);
									$sac->addEffect($effect);
								} else {
									$damager->sendMessage(TextFormat::RED . "마나가 부족합니다.");
								}
							}
						} else {
							$event->setCancelled();
							$damager->sendMessage(TextFormat::RED . "여기서는 스킬을 사용할 수 없습니다 !");
						}
						return true;
					}
				}
			}
		}
	}
	public function onKill(PlayerDeathEvent $event) {
		$sac = $event->getEntity ();
		if ($sac instanceof Player) {
			$cause = $sac->getLastDamageCause ();
			if ($cause instanceof EntityDamageByEntityEvent) {
				$damager = $cause->getDamager ();
				if ($damager instanceof Player) {
					$dname = $damager->getName ();
					$sname = $sac->getName ();
					if (isset ( $this->kdb [$name] ) && isset ( $this->jdb [$dname] )) {
						$this->kdb [$dname] ["kills"] ++;
						$this->kdb [$sname] ["kills"] = 0;
						$this->jdb [$dname] ["exp"] += 100;
						$damager->sendMessage ( TextFormat::YELLOW . $sname . "님을 죽이셨습니다. (보상: 100exp, 10캐쉬" );
						CashShop::runFunction ()->increaseCash ( $dname, 10 );
					}
					if ($this->kdb [$dname] ["kills"] = 3) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . $dname . "님이 학살 중입니다 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
					if ($this->kdb [$dname] ["kills"] = 4) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . $dname . "님이 미쳐 날뛰고 있습니다 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
					if ($this->kdb [$dname] ["kills"] = 5) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . $dname . "님을 도저히 막을 수 없습니다 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
					if ($this->kdb [$dname] ["kills"] = 6) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . $dname . "님이 전장을 지배하고 있습니다 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
					if ($this->kdb [$dname] ["kills"] = 7) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . $dname . "님은 전장의 화신입니다 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
					if ($this->kdb [$dname] ["kills"] > 7) {
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "전설의 " . $dname . "님 !" );
						$this->getServer ()->broadcastMessage ( TextFormat::RED . "❄ -------------------------❄" );
						return true;
					}
				}
			}
		}
	}
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer ();
		$name = $player->getName ();
		$this->writePlayerData ( $name );
	}
	public function writePlayerData($name) {
		if (! isset ( $this->jdb [$name] )) {
			$this->jdb [$name] = [ ];
			$this->jdb [$name] ["job"] = "무소속";
			$this->jdb [$name] ["exp"] = 0;
			$this->kdb [$name] ["kills"] = 0;
			$this->saveData ();
			$this->saveKdata ();
		}
	}
	public function saveKdata() {
		$this->kill->setAll ( $this->kdb );
		$this->kill->save ();
	}
	private function errorMsg(Player $sender) {
		$sender->sendMessage ( TextFormat::BLUE . "====== 사용법 ======" );
		$sender->sendMessage ( TextFormat::BLUE . "/직업 <가입> <마법사|전사|궁수>" );
		$sender->sendMessage ( TextFormat::BLUE . "/직업 <직업보기|경험치보기> <플레이어명>" );
		$sender->sendMessage ( TextFormat::BLUE . "/직업 <탈퇴|내정보>" );
	}
	public function setJob($name, $job) {
		if (isset ( $this->jdb [$name] )) {
			$this->jdb [$name] ["job"] = $job;
			$this->saveData ();
		}
	}
	public function getJob($name) {
		if (isset ( $this->jdb [$name] )) {
			return $this->jdb [$name] ["job"];
		}
	}
	public function getExp($name) {
		if (isset ( $this->jdb [$name] )) {
			return $this->jdb [$name] ["exp"];
		}
	}
	public function unregisterJob($name) {
		if (isset ( $this->jdb [$name] )) {
			$this->jdb [$name] ["job"] = "무소속";
			$this->jdb [$name] ["exp"] = 0;
			$this->saveData ();
		}
	}
	private function saveData() {
		$this->job->setAll ( $this->jdb );
		$this->job->save ();
	}
	private function getWorld(Player $player) {
		return $player->getLevel ()->getName ();
	}
}