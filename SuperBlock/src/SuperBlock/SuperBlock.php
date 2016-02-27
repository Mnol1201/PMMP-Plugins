<?php

namespace SuperBlock;

use onebone\economyapi\EconomyAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\level\Position;

class SuperBlock extends PluginBase implements Listener {
	private $touch_mode = [ ], $blocks, $bdb, $config, $_config;
	public function onEnable() {
		@mkdir ( $this->getDataFolder () );
		$this->blocks = new Config ( $this->getDataFolder () . "blocks.yml", Config::YAML );
		$this->bdb = $this->blocks->getAll ();
		$this->_config = new Config ( $this->getDataFolder () . "config.yml", Config::YAML );
		$this->config = $this->_config->getAll ();
		$this->config ["command-block"] = 45;
		$this->config ["interact-block"] = 98;
		$this->config ["message-block"] = 47;
		if (! isset ( $this->config ["moveon-block"] )) {
			$this->config ["moveon-block"] = true;
			return true;
		}
		$this->_config->setAll ( $this->config );
		$this->_config->save ();
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
	}
	public function onTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		$id = $event->getBlock ()->getId ();
		$level = $event->getBlock ()->getLevel ()->getName ();
		$x = $block->getX ();
		$y = $block->getY ();
		$z = $block->getZ ();
		if ($id == $this->getCmdBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				$this->getServer ()->getCommandMap ()->dispatch ( $player, $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] );
				$player->sendMessage ( TextFormat::GREEN . "명령어를 실행했습니다." );
			}
			return true;
		}
		if ($id == $this->getIntBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				$msg = array (
						"아아아앆 아프잖아 ! 왜때리니 ㅜㅜ",
						"아으.. 피곤하다",
						"왜 부르니? 혹시 내 얼굴에 뭐 묻은거라도...?",
						"흠.. 지능을 갖게되니 정말 좋아 !",
						"난 이름이 " . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . "라구.",
						"안녕, " . $event->getPlayer ()->getName () 
				);
				$message = $msg [mt_rand ( 0, 5 )];
				$player->sendMessage ( "[ 인공지능블럭 ] " . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . " > " . TextFormat::WHITE . $message );
			}
			return true;
		}
		if ($id == $this->getMsgBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				$player->sendMessage ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] );
			}
			return true;
		}
	}
	public function onBreakBlock(BlockBreakEvent $event) {
		$block = $event->getBlock ();
		$id = $event->getBlock ()->getId ();
		$player = $event->getPlayer ();
		$level = $event->getBlock ()->getLevel ()->getName ();
		$x = $block->getX ();
		$y = $block->getY ();
		$z = $block->getZ ();
		if ($id == $this->GetCmdBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				if ($player->isOp ()) {
					unset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] );
					$player->sendMessage ( TextFormat::GREEN . "커맨드블록을 삭제했습니다." );
				} else {
					$event->setCancelled ();
					$player->sendMessage ( TextFormat::RED . "블럭을 부술 권한이 없습니다." );
				}
			}
			return true;
		}
		if ($id == $this->getIntBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				if ($player->isOp ()) {
					unset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] );
					$msg = array (
							"왜 날 부수는건데..?",
							"후에에에에엥 ㅜㅜ",
							"으흒으흐흒 흐윾 ㅜㅜ",
							"날 떠나려고...? 우리 정이 이제 다했나보네.." 
					);
					$message = $msg [mt_rand ( 0, 3 )];
					$player->sendMessage ( "[ 인공지능블럭 ]" . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . " > " . TextFormat::WHITE . $message );
				} else {
					$event->setCancelled ();
					$player->sendMessage ( "[ 인공지능블럭 ]" . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . TextFormat::RED . " > 넌 날 부술 자격이 없어 !" );
				}
			}
			return true;
		}
		if ($id == $this->getMsgBlockCode ()) {
			if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
				if ($player->isOp ()) {
					unset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] );
					$player->sendMessage ( TextFormat::GREEN . "메시지 블록을 삭제했습니다." );
				} else {
					$event->setCancelled ();
					$player->sendMessage ( TextFormat::RED . "블럭을 부술 권한이 없습니다." );
				}
			}
			return true;
		}
	}
	public function onSignChange(SignChangeEvent $event) {
		$sign = $event->getBlock ();
		$x = $sign->getX ();
		$y = $sign->getY () - 1;
		$z = $sign->getZ ();
		$player = $event->getPlayer ();
		if ($event->getBlock ()->getSide ( Vector3::SIDE_DOWN )->getId () == $this->getCmdBlockCode ()) {
			if ($event->getLine ( 0 ) == "commandblock") {
				if ($player->isOp ()) {
					if ($event->getLine ( 1 ) == "") {
						$event->getPlayer ()->sendMessage ( TextFormat::RED . "잘못된 형식입니다 !" );
						$event->setCancelled ();
						return false;
					}
					$this->writeBlockData ( $x, $y, $z, $event->getBlock ()->getLevel ()->getName (), "commandblock", $event->getLine ( 1 ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "커맨드블럭이 설정되었습니다 !" );
				} else {
					$player->sendMessage ( TextFormat::RED . "권한이 없습니다." );
				}
			}
			return true;
		}
		if ($event->getBlock ()->getSide ( Vector3::SIDE_DOWN )->getId () == $this->getIntBlockCode ()) {
			if ($event->getLine ( 0 ) == "interactblock") {
				if ($player->isOp ()) {
					if ($event->getLine ( 1 ) == "") {
						$event->getPlayer ()->sendMessage ( TextFormat::RED . "잘못된 형식입니다." );
						$event->setCancelled ();
						return false;
					}
					$this->writeBlockData ( $x, $y, $z, $event->getBlock ()->getLevel ()->getName (), "interactblock", $event->getLine ( 1 ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "해당 블럭에게 " . $event->getLine ( 1 ) . "라는 이름을 붙여주었습니다 !" );
				} else {
					$player->sendMessage ( TextFormat::RED . "권한이 없습니다." );
				}
			}
			return true;
		}
		if ($event->getBlock ()->getSide ( Vector3::SIDE_DOWN )->getId () == $this->getMsgBlockCode ()) {
			if ($event->getLine ( 0 ) == "msgblock") {
				if ($player->isOp ()) {
					if ($event->getLine ( 1 ) == "") {
						$event->getPlayer ()->sendMessage ( TextFormat::RED . "잘못된 형식입니다 !" );
						$event->setCancelled ();
						return false;
					}
					$this->writeBlockData ( $x, $y, $z, $event->getBlock ()->getLevel ()->getName (), "messageblock", $event->getLine ( 1 ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "메시지블럭이 설정되었습니다 !" );
				} else {
					$player->sendMessage ( TextFormat::RED . "권한이 없습니다." );
				}
			}
			return true;
		}
	}
	public function onMove(PlayerMoveEvent $event) {
		if ($this->config ["moveon-block"] == true) {
			$player = $event->getPlayer ();
			$level = $event->getPlayer ()->getLevel ()->getName ();
			$block = $event->getPlayer ()->getLevel ()->getBlock ( $player->getPosition ()->subtract ( 0, 1, 0 ) );
			$x = $block->getX ();
			$y = $block->getY ();
			$z = $block->getZ ();
			if ($block->getId () == $this->getCmdBlockCode ()) {
				if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
					$this->getServer ()->getCommandMap ()->dispatch ( $player, $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] );
					$player->sendMessage ( TextFormat::GREEN . "명령을 실행했습니다." );
				}
				return true;
			}
			if ($block->getId () == $this->getIntBlockCode ()) {
				if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
					$msg = array (
							"저기.. 밑에 누구 있거든요? 함부로 밟지 마세요.",
							"흐이익.. 자라나는 꿈나무를 밟으시면 안되죠 !",
							"아 너 왤케 무겁니 ? ㅡㅡ" 
					);
					$message = $msg [mt_rand ( 0, 2 )];
					$player->sendMessage ( "[ 인공지능블럭 ] " . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . " > " . $message );
					if (mt_rand ( 0, 3 ) == 1) {
						if (EconomyAPI::getInstance ()->myMoney ( $player ) < 10000) {
							$random = mt_rand ( 1, 1000 );
							EconomyAPI::getInstance ()->addMoney ( $player, $random );
							$player->sendMessage ( "[ 인공지능블럭 ] " . $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] . " > " . $random . "원 줄게. 오다가 주웠다구." );
						}
					}
				}
				return true;
			}
			if ($block->getId () == $this->getMsgBlockCode ()) {
				if (isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
					$player->sendMessage ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] );
				}
				return true;
			}
		}
	}
	public function getCmdBlockCode() {
		if (isset ( $this->config ["command-block"] )) {
			return $this->config ["command-block"];
		}
	}
	public function getIntBlockCode() {
		if (isset ( $this->config ["interact-block"] )) {
			return $this->config ["interact-block"];
		}
	}
	public function getMsgBlockCode() {
		if (isset ( $this->config ["message-block"] )) {
			return $this->config ["message-block"];
		}
	}
	public function writeBlockData($x, $y, $z, $level, $type, $var) {
		if (! isset ( $this->bdb [$x . "." . $y . "." . $z . ":" . $level] )) {
			$this->bdb [$x . "." . $y . "." . $z . ":" . $level] = [ ];
			$this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["type"] = $type;
			$this->bdb [$x . "." . $y . "." . $z . ":" . $level] ["var"] = $var;
			$this->blocks->setAll ( $this->bdb );
			$this->blocks->save ();
		} else {
			return false;
		}
	}
}