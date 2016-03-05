<?php

namespace main;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\command\ConsoleCommandSender;

class main extends PluginBase implements Listener {
	const Quiz_Apply_mode = 1;
	const Moving_mode = 2;
	const Default_mode = 0;
	private $mode = [ ];
	public function onEnable() {
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		$this->mode = self::Default_mode;
	}
	public function onCommand(CommandSender $player, Command $command, $label, array $args) {
		if($command == "퀴즈"){
			if(! isset($args[0])){
				$this->errorMsg($player);
				return true;
			}
			switch($args[0]){
				case "시작":
					if($this->getMode() === self::Default_mode){
						$this->setQuizApplyMode();
						$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 퀴즈가 시작되었습니다 !");
						$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 퀴즈 출제자는 문제를 내주세요 !");
						$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 퀴즈 출제 전까지 움직임과 채팅이 제한됩니다.");
					} else {
						$player->sendMessage(TextFormat::RED . "지금은 퀴즈를 시작할 수 없습니다 !");
					}
					break;
				case "종료":
					if($this->getMode() === self::Quiz_Apply_mode || $this->getMode() === self::Moving_mode){
						$this->setDefaultMode();
						$this->getServer()->broadcastMessage(TextFormat::RED."[ O/X퀴즈 ] 퀴즈가 종료되었습니다.");
					} else {
						$player->sendMessage(TextFormat::RED . "퀴즈 진행중이 아닙니다 !");
					}
					break;
				case "정답발표":
					if(! isset($args[1])){
						$this->errorMsg($player);
						break;
					}
					if($this->mode !== self::Moving_mode){
						$player->sendMessage(TextFormat::RED . "아직 퀴즈를 내지 않았습니다.");
						break;
					}
					switch(strtolower($args[1])){
						case "o":
							$this->AnswerisO();
							break;
						case "x":
							$this->AnswerisX();
							break;
					}
					break;
				default:
			}
			return true;
		}
		if($command == "퀴즈내기"){
			if(! isset($args[0])){
				$this->errorMsg($player);
				return true;
			}
			if($this->mode === self::Quiz_Apply_mode){
			$quiz = implode(" ", $args);
			$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 퀴즈 내용: ".$quiz);
			$this->getServer()->broadcastMessage(TextFormat::BLUE . "O라고 생각하시는 분들은 파란색 양털 위로,");
			$this->getServer()->broadcastMessage(TextFormat::RED . "X라고 생각하시는 분들은 빨간색 양털 위로 올라가 주세요 !");
			$this->mode = self::Moving_mode;
			} else {
				$player->sendMessage(TextFormat::RED . "퀴즈 진행중이 아닙니다 !");
			}
			return true;
		}
	}
	public function onMove(PlayerMoveEvent $event){
		if($event->getPlayer()->isOp()){
			return;
		}
		if($this->mode === self::Quiz_Apply_mode){
			$player = $event->getPlayer();
			$event->setCancelled();
		}
	}
	public function onChat(PlayerChatEvent $event){
		if($event->getPlayer()->isOp()){
			return;
		}
		$player = $event->getPlayer();
		if($this->mode === self::Quiz_Apply_mode || $this->mode === self::Moving_mode){
			$event->setMessage(null);
			$player->sendMessage(TextFormat::RED . "퀴즈를 내는 중입니다. 채팅을 하실 수 없습니다.");
		}
	}
	public function setDefaultMode(){
		$this->mode = self::Default_mode;
	}
	public function setQuizApplyMode() {
		$this->mode = self::Quiz_Apply_mode;
	}
	public function setMovingMode() {
		$this->mode = self::Moving_mode;
	}
	public function getMode() {
		return $this->mode;
	}
	public function errorMsg($player) {
		if($player instanceof Player){
		$player->sendMessage ( TextFormat::BLUE . "--== 사용법 리스트 ==--" );
		$player->sendMessage ( TextFormat::BLUE . "/퀴즈 <시작|종료>" );
		$player->sendMessage ( TextFormat::BLUE . "/퀴즈내기 <퀴즈>" );
		$player->sendMessage ( TextFormat::BLUE . "/퀴즈 <정답발표> <O|X>" );
		return true;
		} else {
			$player->sendMessage ( TextFormat::BLUE . "--== 사용법 리스트 ==--" );
			$player->sendMessage ( TextFormat::BLUE . "/퀴즈 <시작|종료>" );
			$player->sendMessage ( TextFormat::BLUE . "/퀴즈내기 <퀴즈>" );
			$player->sendMessage ( TextFormat::BLUE . "/퀴즈 <정답발표> <O|X>" );
		}
	}
	public function AnswerisO(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$block = $p->getLevel()->getBlock($p->getPosition()->subtract(0,1,0));
			if($block->getId() == 35 && $block->getDamage() == 14){
				$p->kill();
				$p->sendMessage(TextFormat::RED . "[ O/X퀴즈 ] 퀴즈에서 탈락하셨습니다.");
			}
		}
		$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 정답은 O 입니다.");
		$this->mode = self::Quiz_Apply_mode;
	}
	public function AnswerisX(){
		foreach($this->getServer()->getOnlinePlayers() as $p){
			$block = $p->getLevel()->getBlock($p->getPosition()->subtract(0,1,0));
			if($block->getId() == 35 && $block->getDamage() == 11){
				$p->kill();
				$p->sendMessage(TextFormat::RED . "[ O/X퀴즈 ] 퀴즈에서 탈락하셨습니다.");
			}
		}
		$this->getServer()->broadcastMessage(TextFormat::GOLD . "[ O/X퀴즈 ] 정답은 X 입니다.");
		$this->mode = self::Quiz_Apply_mode;
	}
}