<?php

namespace CashShop;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;

class CashShop extends PluginBase implements Listener {
	const ON_TOUCH_MODE = 1;
	const ON_DEFAULT_MODE = null;
	private $cash, $cdb, $shop, $sdb;
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->cash = new Config ( $this->getDataFolder () . "playercash.yml", Config::YAML );
		$this->cdb = $this->cash->getAll ();
		$this->shop = new Config ( $this->getDataFolder () . "shops.yml", Config::YAML );
		$this->sdb = $this->shop->getAll ();
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function onJoin(PlayerJoinEvent $event){
		$name = $event->getPlayer()->getName();
		$this->writePlayerData($name);
	}
	public function writePlayerData($name) {
		if (! isset ( $this->cdb [$name] )) {
			$this->getServer()->getLogger()->debug("[ 캐시샵 ] ".$name . "님의 데이터를 찾지 못했습니다. 생성중...");
			$this->cdb[$name] = [ ];
			$this->cdb [$name]["cash"] = 0;
			$this->saveCash();
		}
	}
	public function increaseCash($name, $amount) {
		if(isset($this->cdb[$name])){
			$this->cdb[$name]["cash"] += $amount;
			$this->saveCash();
		}
	}
	public function saveCash(){
		$this->cash->setAll($this->cdb);
		$this->cash->save();
	}
	public function saveShop(){
		$this->shop->setAll($this->sdb);
		$this->shop->save();
	}
	public function reduceCash($name, $amount) {
		if(isset($this->cdb[$name])){
			$this->cdb[$name]["cash"] -= $amount;
			$this->saveCash();
		}
	}
	public function setCash($name, $amount) {
		if(isset($this->cdb[$name])){
			$this->cdb[$name]["cash"] = $amount;
			$this->saveCash();
		}
	}
}