<?php

namespace Tagme;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Player;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\block\Block;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\block\BlockBreakEvent;

class Tagme extends PluginBase implements Listener {
	private $tag, $db, $config, $shop, $shopdb, $nickcolorshop, $nickcolordb, $chatcolorshop, $chatcolordb;
	public function onEnable() {
		$this->getServer ()->getLogger ()->info ( TextFormat::GREEN . "[ 태그미 ] 칭호 설정할 준비 되셨나요?" );
		$this->getServer ()->getPluginManager ()->registerEvents ( $this, $this );
		@mkdir ( $this->getDataFolder () );
		$this->tag = new Config ( $this->getDataFolder () . "database.yml", Config::YAML );
		$this->db = $this->tag->getAll ();
		$this->config = new Config ( $this->getDataFolder () . "config.yml", Config::YAML, [ 
				"default" => "유저",
				"format" => "[ ",
				"format2" => " ] " 
		] );
		$this->config = $this->config->getAll ();
		$this->shopdb = new Config ( $this->getDataFolder () . "shopdb.yml", Config::YAML );
		$this->shop = $this->shopdb->getAll ();
		$this->nickcolorshop = new Config ( $this->getDataFolder () . "nickcolorshop.yml", Config::YAML );
		$this->nickcolordb = $this->nickcolorshop->getAll ();
		$this->chatcolorshop = new Config ( $this->getDataFolder () . "chatcolorshop.yml", Config::YAML );
		$this->chatcolordb = $this->chatcolorshop->getAll ();
	}
	public function onJoin(PlayerJoinEvent $event) {
		$name = $event->getPlayer ()->getName ();
		$this->writeData ( $name );
		$event->getPlayer()->setNameTag("§" . $this->getTagColor ( $name ) . $this->getFormat () . $this->getTag ( $name ) . $this->getFormat2 () . "§" . $this->getNameColor ( $name ) . $this->getSettedName ( $name ));
	}
	public function onChat(PlayerChatEvent $event) {
		$event->setCancelled ();
		$player = $event->getPlayer ();
		$name = $player->getName ();
		$this->getServer ()->broadcastMessage ( "§" . $this->getTagColor ( $name ) . $this->getFormat () . $this->getTag ( $name ) . $this->getFormat2 () . "§" . $this->getNameColor ( $name ) . $this->getSettedName ( $name ) . "§" . $this->getChatColor ( $name ) . " > " . $event->getMessage () );
	}
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if (! isset ( $args [0] )) {
			$this->errorMsg ( $sender );
			return true;
		}
		if (! isset ( $args [1] )) {
			$this->errorMsg ( $sender );
			return true;
		}
		if ($command == "칭호설정") {
			if (strtolower ( $args [1] ) == "op") {
				$this->setOptag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "vip") {
				$this->setViptag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "rvip") {
				$this->setRviptag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "svip") {
				$this->setSviptag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "어드민") {
				$this->setAdmintag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "부어드민") {
				$this->setSubadmintag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "탈맵마스터") {
				$this->setJumpmapMastertag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "갑부") {
				$this->setRichtag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			if (strtolower ( $args [1] ) == "암살자") {
				$this->setKillertag ( $args [0] );
				$this->succesfulMsg ( $sender );
				return true;
			}
			$this->setTag ( $args [0], $args [1] );
			$this->succesfulMsg ( $sender );
			return true;
		}
		if ($command == "닉네임설정") {
			$this->setName ( $args [0], $args [1] );
			$this->succesfulMsg ( $sender );
			return true;
		}
		if ($command == "채팅색") {
			$this->setChatColor ( $args [0], $args [1] );
			$this->succesfulMsg ( $sender );
			return true;
		}
		if ($command == "칭호색") {
			$this->setTagColor ( $args [0], $args [1] );
			$this->succesfulMsg ( $sender );
			return true;
		}
		if ($command == "닉네임색") {
			$this->setNameColor ( $args [0], $args [1] );
			$this->succesfulMsg ( $sender );
			return true;
		}
	}
	public function onSignChange(SignChangeEvent $event) {
		$player = $event->getPlayer ();
		if ($event->getLine ( 0 ) == "tagshop") {
			if (! $player->isOp ()) {
				$player->sendMessage ( TextFormat::RED . "칭호상점 생성 권한이 없습니다." );
				$event->setCancelled ();
				return false;
			}
			if ($event->getLine ( 1 ) == "" || $event->getLine ( 2 ) == "" || $event->getLine ( 3 ) == "" || ! is_numeric ( $event->getLine ( 3 ) )) {
				$player->sendMessage ( TextFormat::RED . "[ 태그미 ] 올바른 상점 형식이 아닙니다 !" );
				$event->setCancelled ();
				return false;
			}
			$event->setLine ( 0, TextFormat::DARK_AQUA . "[ 칭호상점 ]" );
			$tag = $event->getLine ( 2 );
			if (count ( $tag ) != 1) {
				$player->sendMessage ( TextFormat::RED . "[ 태그미 ] 올바른 상점 형식이 아닙니다 !" );
				$event->setCancelled ();
				return false;
			}
			$this->writeShopData ( $event->getBlock ()->getX (), $event->getBlock ()->getY (), $event->getBlock ()->getZ (), $event->getLine ( 1 ), $tag, $event->getLine ( 3 ) );
			$event->setLine ( 3, TextFormat::DARK_GREEN . "가격: " . $event->getLine ( 3 ) );
			return true;
		}
		// ====================================================================================================================
		if ($event->getLine ( 0 ) == "namecolorshop") {
			if (! $player->isOp ()) {
				$player->sendMessage ( TextFormat::RED . "칭호상점 생성 권한이 없습니다." );
				$event->setCancelled ();
				return false;
			}
			if ($event->getLine ( 1 ) == "" || $event->getLine ( 2 ) == "" || ! is_numeric ( $event->getLine ( 2 ) )) {
				$player->sendMessage ( TextFormat::RED . "[ 태그미 ] 올바른 상점 형식이 아닙니다 !" );
				$event->setCancelled ();
				return false;
			}
			// 0라인에 [닉네임색상점], 1라인에 색깔코드, 2라인에 가격
			$event->setLine ( 0, TextFormat::DARK_AQUA . "[ 닉네임색 상점 ]" );
			$tag = $event->getLine ( 2 );
			$this->writeNameColorShopData ( $event->getBlock ()->getX (), $event->getBlock ()->getY (), $event->getBlock ()->getZ (), $event->getLine ( 1 ), $event->getLine ( 2 ) );
			$event->setLine ( 2, TextFormat::DARK_GREEN . "가격: " . $event->getLine ( 2 ) );
			return true;
		}
		// ===================================================================================================================
		if ($event->getLine ( 0 ) == "chatcolorshop") {
			if (! $player->isOp ()) {
				$player->sendMessage ( TextFormat::RED . "칭호상점 생성 권한이 없습니다." );
				$event->setCancelled ();
				return false;
			}
			if ($event->getLine ( 1 ) == "" || $event->getLine ( 2 ) == "" || ! is_numeric ( $event->getLine ( 2 ) )) {
				$player->sendMessage ( TextFormat::RED . "[ 태그미 ] 올바른 상점 형식이 아닙니다 !" );
				$event->setCancelled ();
				return false;
			}
			$event->setLine ( 0, TextFormat::DARK_AQUA . "[ 채팅색 상점 ]" );
			$tag = $event->getLine ( 2 );
			$this->writeChatColorShopData ( $event->getBlock ()->getX (), $event->getBlock ()->getY (), $event->getBlock ()->getZ (), $event->getLine ( 1 ), $event->getLine ( 2 ) );
			$event->setLine ( 2, TextFormat::DARK_GREEN . "가격: " . $event->getLine ( 2 ) );
			return true;
		}
	}
	public function writeNameColorShopData($x, $y, $z, $namecolor, $price) {
		if (! isset ( $this->nickcolordb [$x . "." . $y . "." . $z] )) {
			$this->nickcolordb [$x . "." . $y . "." . $z] = [ ];
			$this->nickcolordb [$x . "." . $y . "." . $z] ["nickcolor"] = $namecolor;
			$this->nickcolordb [$x . "." . $y . "." . $z] ["price"] = $price;
			$this->saveData ();
		}
	}
	public function writeChatColorShopData($x, $y, $z, $chatcolor, $price) {
		if (! isset ( $this->chatcolordb [$x . "." . $y . "." . $z] )) {
			$this->chatcolordb [$x . "." . $y . "." . $z] = [ ];
			$this->chatcolordb [$x . "." . $y . "." . $z] ["chatcolor"] = $chatcolor;
			$this->chatcolordb [$x . "." . $y . "." . $z] ["price"] = $price;
			$this->saveData ();
		}
	}
	public function onSignTouch(PlayerInteractEvent $event) {
		$player = $event->getPlayer ();
		$x = $event->getBlock ()->getX ();
		$y = $event->getBlock ()->getY ();
		$z = $event->getBlock ()->getZ ();
		$name = $player->getName ();
		if ($event->getBlock ()->getId () == Block::SIGN_POST || $event->getBlock ()->getId () == Block::WALL_SIGN) {
			if (isset ( $this->shop [$x . "." . $y . "." . $z] )) {
				if (EconomyAPI::getInstance ()->myMoney ( $player ) > $this->getShopPrice ( $x, $y, $z ) - 1) {
					$this->setTag ( $name, $this->getShopTag ( $x, $y, $z ) );
					$this->setTagColor ( $name, $this->getShopTagColor ( $x, $y, $z ) );
					EconomyAPI::getInstance ()->reduceMoney ( $player, $this->getShopPrice ( $x, $y, $z ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "[ 태그미 ] 칭호를 구매하였습니다." );
					$event->setCancelled ();
				} else {
					$event->setCancelled();
					$player->sendMessage(TextFormat::RED . "[ 태그미 ] 돈이 부족합니다.");
					return false;
				}
			}
			
			if (isset ( $this->nickcolordb [$x . "." . $y . "." . $z] )) {
				if (EconomyAPI::getInstance ()->myMoney ( $player ) > $this->getNickShopPrice ( $x, $y, $z ) - 1) {
					$this->setNameColor ( $name, $this->getShopNickcolor ( $x, $y, $z ) );
					EconomyAPI::getInstance ()->reduceMoney ( $player, $this->getNickShopPrice ( $x, $y, $z ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "[ 태그미 ] 닉네임색을 구매하였습니다." );
					$event->setCancelled ();
				} else {
					$event->setCancelled();
					$player->sendMessage(TextFormat::RED . "[ 태그미 ] 돈이 부족합니다.");
					return false;
				}
			}
			if (isset ( $this->chatcolordb [$x . "." . $y . "." . $z] )) {
				if (EconomyAPI::getInstance ()->myMoney ( $player ) > $this->getChatShopPrice ( $x, $y, $z ) - 1) {
					$this->setChatColor ( $name, $this->getShopChatcolor ( $x, $y, $z ) );
					EconomyAPI::getInstance ()->reduceMoney ( $player, $this->getChatShopPrice ( $x, $y, $z ) );
					$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "[ 태그미 ] 채팅색을 구매하였습니다." );
					$event->setCancelled ();
				} else {
					$event->setCancelled();
					$player->sendMessage(TextFormat::RED . "[ 태그미 ] 돈이 부족합니다.");
					return false;
				}
			}
		}
	}
	public function onBreak(BlockBreakEvent $event) {
		$x = $event->getBlock ()->getX ();
		$y = $event->getBlock ()->getY ();
		$z = $event->getBlock ()->getZ ();
		if (isset ( $this->shop [$x . "." . $y . "." . $z] )) {
			if ($event->getPlayer ()->isOp ()) {
				unset ( $this->shop [$x . "." . $y . "." . $z] );
				$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "상점을 삭제했습니다." );
			} else {
				$event->setCancelled ();
				$event->getPlayer ()->sendMessage ( TextFormat::RED . "칭호상점을 삭제할 권한이 없습니다." );
				return true;
			}
		}
		if (isset ( $this->nickcolordb [$x . "." . $y . "." . $z] )) {
			if ($event->getPlayer ()->isOp ()) {
				unset ( $this->nickcolordb [$x . "." . $y . "." . $z] );
				$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "상점을 삭제했습니다." );
			} else {
				$event->setCancelled ();
				$event->getPlayer ()->sendMessage ( TextFormat::RED . "칭호상점을 삭제할 권한이 없습니다." );
				return true;
			}
		}
		if (isset ( $this->chatcolordb [$x . "." . $y . "." . $z] )) {
			if ($event->getPlayer ()->isOp ()) {
				unset ( $this->chatcolordb [$x . "." . $y . "." . $z] );
				$event->getPlayer ()->sendMessage ( TextFormat::GREEN . "상점을 삭제했습니다." );
			} else {
				$event->setCancelled ();
				$event->getPlayer ()->sendMessage ( TextFormat::RED . "칭호상점을 삭제할 권한이 없습니다." );
				return true;
			}
		}
	}
	/**
	 *
	 * @param string $x        	
	 * @param string $y        	
	 * @param string $z        	
	 * @param string $tag        	
	 * @param string $tagcolor        	
	 * @param string $nickcolor        	
	 * @param string $chatcolor        	
	 * @param string $price        	
	 */
	public function writeShopData($x, $y, $z, $tag, $tagcolor, $price) {
		if (! isset ( $this->shop [$x . "." . $y . "." . $z] )) {
			$this->shop [$x . "." . $y . "." . $z] = [ ];
			$this->shop [$x . "." . $y . "." . $z] ["tag"] = $tag;
			$this->shop [$x . "." . $y . "." . $z] ["tagcolor"] = $tagcolor;
			$this->shop [$x . "." . $y . "." . $z] ["price"] = $price;
			$this->saveData ();
		}
	}
	public function getShopTag($x, $y, $z) {
		if (isset ( $this->shop [$x . "." . $y . "." . $z] )) {
			return $this->shop [$x . "." . $y . "." . $z] ["tag"];
		}
	}
	public function getShopTagcolor($x, $y, $z) {
		if (isset ( $this->shop [$x . "." . $y . "." . $z] )) {
			return $this->shop [$x . "." . $y . "." . $z] ["tagcolor"];
		}
	}
	public function getShopNickcolor($x, $y, $z) {
		if (isset ( $this->nickcolordb [$x . "." . $y . "." . $z] )) {
			return $this->nickcolordb [$x . "." . $y . "." . $z] ["nickcolor"];
		}
	}
	public function getShopChatcolor($x, $y, $z) {
		if (isset ( $this->chatcolordb [$x . "." . $y . "." . $z] )) {
			return $this->chatcolordb [$x . "." . $y . "." . $z] ["chatcolor"];
		}
	}
	public function getChatShopPrice($x, $y, $z) {
		if (isset ( $this->chatcolordb [$x . "." . $y . "." . $z] )) {
			return $this->chatcolordb [$x . "." . $y . "." . $z] ["price"];
		}
	}
	public function getNickShopPrice($x, $y, $z) {
		if (isset ( $this->nickcolordb [$x . "." . $y . "." . $z] )) {
			return $this->nickcolordb [$x . "." . $y . "." . $z] ["price"];
		}
	}
	public function getShopPrice($x, $y, $z) {
		if (isset ( $this->shop [$x . "." . $y . "." . $z] )) {
			return $this->shop [$x . "." . $y . "." . $z] ["price"];
		}
	}
	public function getFormat() {
		return $this->config ["format"];
	}
	public function getFormat2() {
		return $this->config ["format2"];
	}
	public function getDefaultTag() {
		return $this->config ["default"];
	}
	public function getTag($name) {
		return $this->db [$name] ["tag"];
	}
	public function getsettedName($name) {
		return $this->db [$name] ["name"];
	}
	public function writeData($name) {
		if (! isset ( $this->db [$name] )) {
			$this->db [$name] = [ ];
			$this->db [$name] ["tag"] = $this->getDefaultTag ();
			$this->db [$name] ["name"] = $name;
			$this->db [$name] ["chat-color"] = "e";
			$this->db [$name] ["tag-color"] = "e";
			$this->db [$name] ["name-color"] = "f";
			$this->saveData ();
			// $this->db [$name]["mute"] = false;
		}
	}
	public function saveData() {
		$this->tag->setAll ( $this->db );
		$this->tag->save ();
		$this->shopdb->setAll ( $this->shop );
		$this->shopdb->save ();
		$this->chatcolorshop->setAll ( $this->chatcolordb );
		$this->chatcolorshop->save ();
		$this->nickcolorshop->setAll ( $this->nickcolordb );
		$this->nickcolorshop->save ();
	}
	public function getTagColor($name) {
		return $this->db [$name] ["tag-color"];
	}
	public function getChatColor($name) {
		return $this->db [$name] ["chat-color"];
	}
	public function getNameColor($name) {
		return $this->db [$name] ["name-color"];
	}
	public function errorMsg($sender) {
		if ($sender instanceof Player) {
			$sender->sendMessage ( TextFormat::BLUE . "====== 사용법 ======" );
			$sender->sendMessage ( TextFormat::BLUE . "/닉네임색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/채팅색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/칭호색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/칭호설정 <플레이어명> <칭호>" );
			$sender->sendMessage ( TextFormat::BLUE . "/닉네임설정 <플레이어명> <닉네임>" );
		} else {
			$sender->sendMessage ( TextFormat::BLUE . "====== 사용법 ======" );
			$sender->sendMessage ( TextFormat::BLUE . "/닉네임색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/채팅색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/칭호색 <플레이어명> <색깔코드>" );
			$sender->sendMessage ( TextFormat::BLUE . "/칭호설정 <플레이어명> <칭호>" );
			$sender->sendMessage ( TextFormat::BLUE . "/닉네임설정 <플레이어명> <닉네임>" );
		}
	}
	public function setTag($name, $tag) {
		$this->db [$name] ["tag"] = $tag;
		$this->saveData ();
	}
	public function setTagColor($name, $color) {
		$this->db [$name] ["tag-color"] = $color;
		$this->saveData ();
	}
	public function setNameColor($name, $color) {
		$this->db [$name] ["name-color"] = $color;
		$this->saveData ();
	}
	public function setChatColor($name, $color) {
		$this->db [$name] ["chat-color"] = $color;
		$this->saveData ();
	}
	public function setName($name, $nick) {
		$this->db [$name] ["name"] = $nick;
		$this->saveData ();
	}
	public function setViptag($name) {
		$this->setTag ( $name, "VIP" );
		$this->setChatColor ( $name, "d" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "d" );
		$this->saveData ();
	}
	public function setOptag($name) {
		$this->setTag ( $name, "OP" );
		$this->setChatColor ( $name, "a" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "a" );
		$this->saveData ();
	}
	public function setAdmintag($name) {
		$this->setTag ( $name, "어드민" );
		$this->setChatColor ( $name, "b" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "b" );
		$this->saveData ();
	}
	public function setSviptag($name) {
		$this->setTag ( $name, "SVIP" );
		$this->setChatColor ( $name, "6" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "6" );
		$this->saveData ();
	}
	public function setRviptag($name) {
		$this->setTag ( $name, "RVIP" );
		$this->setChatColor ( $name, "c" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "c" );
		$this->saveData ();
	}
	public function setJumpmapMastertag($name) {
		$this->setTag ( $name, "탈맵 마스터" );
		$this->setChatColor ( $name, "3" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "3" );
		$this->saveData ();
	}
	public function setRichtag($name) {
		$this->setTag ( $name, "갑부" );
		$this->setChatColor ( $name, "6" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "6" );
		$this->saveData ();
	}
	public function setKillertag($name) {
		$this->setTag ( $name, "암살자" );
		$this->setChatColor ( $name, "4" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "4" );
		$this->saveData ();
	}
	public function setSubadmintag($name) {
		$this->setTag ( $name, "부어드민" );
		$this->setChatColor ( $name, "c" );
		$this->setNameColor ( $name, "f" );
		$this->setTagColor ( $name, "c" );
		$this->saveData ();
	}
	public function succesfulMsg($sender) {
		$sender->sendMessage ( TextFormat::GREEN . "성공적으로 명령을 수행했습니다." );
	}
	public function onDisable() {
		$this->saveData ();
	}
}