<?php
   namespace SKYJoin;
   
   use pocketmine\event\player\PlayerJoinEvent;
   use pocketmine\event\Listener;
   use pocketmine\utils\TextFormat;
   use pocketmine\plugin\PluginBase;
   
   class SKYJoin extends PluginBase implements Listener {
   	public function onEnable(){
   		$this->getServer()->getPluginManager()->RegisterEvents($this, $this);
   		$this->getServer()->getLogger()->error("스카이서버 전용 플러그인 활성화 !");
   	}
   	public function onPlayerJoin(PlayerJoinEvent $event){
   		if ($event->getPlayer()->getName() == "delos") {
   			$this->getServer()->broadCastMessage(TextFormat::AQUA. "[ 서버 ] 개발자 델로스님이 서버에 강림하셨습니다 !");
   			return true;
   		}
   		if ($event->getPlayer()->getName() == "NetherKom") {
   			$this->getServer()->broadCastMessage(TextFormat::RED . "[ 서버 ] 총어드민 네더콤님이 서버에 강림하셨습니다 !");
   			return true;
   		}
   		if ($event->getPlayer()->isOp(true)){
   			$this->getServer()->broadCastMessage(TextFormat::GREEN . "[ 서버 ] OP " . $event->getPlayer()->getName() . " 님이 접속하셨습니다 !");
   		}
  }
}
?>