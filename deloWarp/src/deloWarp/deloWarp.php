<?php

namespace deloWarp;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;

use pocketmine\utils\TextFormat;

use pocketmine\utils\Config;

use pocketmine\Player;

use pocketmine\command\CommandSender;

use pocketmine\command\Command;

use pocketmine\level\Position;

use pocketmine\block\Block;

use pocketmine\event\block\SignChangeEvent;

use pocketmine\event\player\PlayerInteractEvent;

use pocketmine\event\block\BlockBreakEvent;

use pocketmine\level\Level;

class deloWarp extends PluginBase implements Listener {

	private $warps, $db, $portal, $pdb;

	public function onEnable() {

		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );

		$this->getServer ()->getLogger ()->info ( TextFormat::DARK_PURPLE . "[ deloWarp ] 4차원 세계로 떠나는 델로워프" );

		@mkdir ( $this->getDataFolder () );

		$this->warps = new Config ( $this->getDataFolder () . "warps.yml", Config::YAML );

		$this->db = $this->warps->getAll ();

		$this->portal = new Config ( $this->getDataFolder () . "portals.yml", Config::YAML );

		$this->pdb = $this->portal->getAll ();

	}

	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {

		if ($sender instanceof Player) {

			if ($command == "워프생성") {

				if (! isset ( $args [0] )) {

					$this->errorMsgOp ( $sender );

					return true;

				}

				if (isset ( $this->db [$args [0]] )) {

					$sender->sendMessage ( TextFormat::RED . "그 이름의 워프포인트는 이미 있습니다 !" );

					return true;

				}

				$this->writeData ( $args [0], $sender->getLevel ()->getName (), $sender->getX (), $sender->getY (), $sender->getZ () );

				$sender->sendMessage ( TextFormat::GREEN . "성공적으로 워프를 생성했습니다." );

				return true;

			}

			if ($command == "워프삭제") {

				if (! isset ( $args [0] )) {

					$this->errorMsgOp ( $sender );

					return true;

				}

				if (! isset ( $this->db [$args [0]] )) {

					$sender->sendMessage ( TextFormat::RED . "그 이름의 워프포인트는 존재하지 않습니다 !" );

				}

				if (isset ( $this->db [$args [0]] )) {

					unset ( $this->db [$args [0]] );

					$sender->sendMessage ( TextFormat::GREEN . "성공적으로 워프를 삭제했습니다." );

				}

				return true;

			}

			if ($command == "워프") {

				if (! isset ( $args [0] )) {

					$this->errorMsg ( $sender );

					return true;

				}

				if (! isset ( $this->db [$args [0]] )) {

					$sender->sendMessage ( TextFormat::RED . "그런 이름의 워프포인트는 존재하지 않습니다 !" );

					return true;

				}

				if (isset ( $this->db [$args [0]] )) {

					$this->warp ( $sender, $this->getWarpLevel ( $args [0] ), $this->getWarpX ( $args [0] ), $this->getWarpY ( $args [0] ), $this->getWarpZ ( $args [0] ) );

					$sender->sendMessage ( TextFormat::GREEN . "[ 서버 ] " . $args [0] . "(으)로 이동하셨습니다." );

				}

				return true;

			}

		} else {

			$sender->sendMessage ( TextFormat::RED . "콘솔에서는 사용하실 수 없습니다." );

		}

	}

	public function onSignChange(SignChangeEvent $event) {

		if ($event->getLine ( 0 ) == "워프") {

			if (! $event->getPlayer ()->isOp ()) {

				$event->setCancelled ();

				$event->getPlayer ()->sendMessage ( TextFormat::RED . "워프생성 권한이 없습니다." );

				return false;

			}

			if ($event->getLine ( 1 ) == "") {

				$event->getPlayer ()->sendMessage ( TextFormat::RED . "올바른 워프형식이 아닙니다 !" );

				$event->setCancelled ();

				return false;

			}

			if (! isset ( $this->db [$event->getLine ( 1 )] )) {

				$event->getPlayer ()->sendMessage ( TextFormat::RED . "그런 워프포인트는 없습니다." );

				$event->setCancelled ();

				return false;

			}

			$event->setLine ( 0, TextFormat::DARK_AQUA . "[ 터치하여 워프 ]" );

			$x = $event->getBlock ()->getX ();

			$y = $event->getBlock ()->getY ();

			$z = $event->getBlock ()->getZ ();

			$this->writePortalData ( $event->getLine ( 1 ), $x, $y, $z );

		}

	}

	public function onSignTouch(PlayerInteractEvent $event) {

		$player = $event->getPlayer ();

		$x = $event->getBlock ()->getX ();

		$y = $event->getBlock ()->getY ();

		$z = $event->getBlock ()->getZ ();

		if (isset ( $this->pdb [$x . "." . $y . "." . $z] )) {

			$this->warp ( $player, $this->getWarpLevel ( $this->returnPortalData ( $x, $y, $z ) ), $this->getWarpX ( $this->returnPortalData ( $x, $y, $z ) ), $this->getWarpY ( $this->returnPortalData ( $x, $y, $z ) ), $this->getWarpZ ( $this->returnPortalData ( $x, $y, $z ) ) );

			$player->sendMessage ( TextFormat::GREEN . "[ 서버 ]" . $this->returnPortalData ( $x, $y, $z ) . "(으)로 이동하셨습니다." );

		}

	}

	public function onSignBreak(BlockBreakEvent $event) {
  if($event->getBlock()->getId() == Block::SIGN_POST || $event->getBlock()->getId() == Block::WALL_SIGN){
		$x = $event->getBlock ()->getX ();

		$y = $event->getBlock ()->getY ();

		$z = $event->getBlock ()->getZ ();

		if ($event->getPlayer ()->isOp ()) {

			if (isset ( $this->pdb [$x . "." . $y . "." . $z] )) {

				unset ( $this->pdb [$x . "." . $y . "." . $z] );

				$event->getPlayer ()->sendMessage ( TextFormat::GREEM . "해당 포탈을 삭제했습니다." );

				$event->setCancelled();

			}

		}else{

			$event->getPlayer()->sendMessage(TextFormat::RED . "포탈을 삭제할 권한이 없습니다.");

			$event->setCancelled();

		}
  }
	}

	public function warp($player, $level, $x, $y, $z) {

		if ($player instanceof Player) {

			$player->teleport ( new Position ( $x, $y, $z, $this->getServer()->getLevelByName($level) ), $player->getYaw (), $player->getPitch () );

		}

	}

	public function writePortalData($warpname, $x, $y, $z) {

		if (! isset ( $this->pdb [$x . "." . $y . "." . $z] )) {

			$this->pdb [$x . "." . $y . "." . $z] = [ ];

			$this->pdb [$x . "." . $y . "." . $z] ["warpname"] = $warpname;

			$this->saveData();

		}

	}

	public function returnPortalData($x, $y, $z) {

		if (isset ( $this->pdb [$x . "." . $y . "." . $z] )) {

			return $this->pdb [$x . "." . $y . "." . $z] ["warpname"];

		}

	}

	public function writeData($warpname, $level, $x, $y, $z) {

		if (! isset ( $this->db [$warpname] )) {

			$this->db [$warpname] = [ ];

			$this->db [$warpname] ["x"] = $x;

			$this->db [$warpname] ["y"] = $y;

			$this->db [$warpname] ["z"] = $z;

			$this->db [$warpname] ["level"] = $level;

			$this->saveData ();

		}

	}

	public function saveData() {

		$this->warps->setAll ( $this->db );

		$this->warps->save ();

		$this->portal->setAll ( $this->pdb );

		$this->portal->save ();

	}

	public function errorMsg($sender) {

		if ($sender instanceof Player) {

			$sender->sendMessage ( TextFormat::BLUE . "======== 사용법 ========" );

			$sender->sendMessage ( TextFormat::BLUE . "/워프 <워프포인트>" );

		}

	}

	public function errorMsgOp($sender) {

		if ($sender instanceof Player) {

			$sender->sendMessage ( TextFormat::BLUE . "======== 사용법 ========" );

			$sender->sendMessage ( TextFormat::BLUE . "/워프생성 <워프포인트>" );

			$sender->sendMessage ( TextFormat::BLUE . "/워프삭제 <워프포인트>" );

			$sender->sendMessage ( TextFormat::BLUE . "/워프 <워프포인트>" );

		}

	}

	public function getWarpX($warpname) {

		if (isset ( $this->db [$warpname] )) {

			return $this->db [$warpname] ["x"];

		}

	}

	public function getWarpY($warpname) {

		if (isset ( $this->db [$warpname] )) {

			return $this->db [$warpname] ["y"];

		}

	}

	public function getWarpZ($warpname) {

		if (isset ( $this->db [$warpname] )) {

			return $this->db [$warpname] ["z"];

		}

	}

	public function getWarpLevel($warpname) {

		if (isset ( $this->db [$warpname] )) {

			return $this->db [$warpname] ["level"];

		}

	}

}

?>
