<?php

namespace PlayerSkill;

use Mana\Mana;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\level\Explosion;
use pocketmine\level\Position;
use pocketmine\network\protocol\AddEntityPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\entity\Entity;
use pocketmine\network\protocol\ExplodePacket;

class PlayerSkill extends PluginBase implements Listener {
	public function onEnable() {
		if ($this->getServer ()->getPluginManager ()->getPlugin ( "Mana" ) == null) {
			$this->getServer ()->getLogger ()->error ( "[ 플레이어스킬 ] 이 플러그인을 사용하려면 Mana플러그인이 필요합니다 !" );
			$this->getServer ()->getPluginManager ()->disablePlugin ( $this );
			return true;
		}
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->getServer ()->getLogger ()->info ( TextFormat::GOLD . "[ 플레이어스킬 ] 블레이즈 막대가 필요합니다." );
	}
	public function onTouch(PlayerInteractEvent $event) {
		$id = $event->getItem()->getId();
		$pname = $event->getPlayer ()->getName ();
		$player = $event->getPlayer ();
		
		if ($id == 369) {
			if (Mana::getInstance ()->seeMana ( $pname ) > 99) {
				$light = new AddEntityPacket ();
				$light->type = 93;
				$light->eid = Entity::$entityCount ++;
				$light->metadata = array ();
				$light->speedX = 0;
				$light->speedY = 0;
				$light->speedZ = 0;
				$light->x = $event->getBlock()->getX();
				$light->y = $event->getBlock()->getY();
				$light->z = $event->getBlock()->getZ();
				Server::broadcastPacket ( $this->getServer ()->getOnlinePlayers (), $light );
				Mana::getInstance ()->decreaseMana ( $pname, 100 );
				$packet = new ExplodePacket();
				$packet->x = $event->getBlock()->getX();
				$packet->y = $event->getBlock()->getY();
				$packet->z = $event->getBlock()->getZ();
				$packet->radius = 3;
				Server::broadcastPacket($this->getServer()->getOnlinePlayers(), $packet);
				$event->setCancelled ();
			} else {
				$player->sendMessage ( TextFormat::RED . "마나가 부족합니다." );
			}
			return true;
		}
		
		if ($id == 341) {
			if (Mana::getInstance ()->seeMana ( $pname ) > 69) {
				$packet = new ExplodePacket();
				$packet->x = $event->getBlock()->getX();
				$packet->y = $event->getBlock()->getY();
				$packet->z = $event->getBlock()->getZ();
				$packet->radius = 1;
				Server::broadcastPacket($this->getServer()->getOnlinePlayers(), $packet);
				Mana::getInstance ()->decreaseMana ( $pname, 70 );
			} else {
				$player->sendMessage ( TextFormat::RED . "마나가 부족합니다." );
			}
			return true;
		}
		if ($id == 337) {
			if (Mana::getInstance ()->seeMana ( $pname ) > 79) {
				$packet = new ExplodePacket();
				$packet->x = $event->getBlock()->getX();
				$packet->y = $event->getBlock()->getY();
				$packet->z = $event->getBlock()->getZ();
				$packet->radius = 2;
				Server::broadcastPacket($this->getServer()->getOnlinePlayers(), $packet);
				Mana::getInstance ()->decreaseMana ( $pname, 80 );
			} else {
				$player->sendMessage ( TextFormat::RED . "마나가 부족합니다." );
			}
			return true;
		}
	}
}