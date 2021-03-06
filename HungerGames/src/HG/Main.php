<?php
namespace HG;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\OfflinePlayer;
use pocketmine\utils\Config;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\CallbackTask;
use pocketmine\block\Block;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerQuitEvent;
use onebone\economyapi\EconomyAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use ResetChest\Main as ResetChest;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerMoveEvent;

class Main extends PluginBase implements Listener
{
	
	private static $obj = null;
	public static function getInstance()
	{
		return self::$obj;
	}
	public function onEnable()
	{
		if(!self::$obj instanceof Main)
		{
			self::$obj = $this;
		}
		$this->getServer()->getPluginManager()->registerEvents($this,$this);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this,"gameTimber"]),20);
		@mkdir($this->getDataFolder(), 0777, true);
		$this->config=new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
		if($this->config->exists("lastpos"))
		{
			$this->sign=$this->config->get("sign");
			$this->pos1=$this->config->get("pos1");
			$this->pos2=$this->config->get("pos2");
			$this->pos3=$this->config->get("pos3");
			$this->pos4=$this->config->get("pos4");
			$this->pos5=$this->config->get("pos5");
			$this->pos6=$this->config->get("pos6");
			$this->pos7=$this->config->get("pos7");
			$this->pos8=$this->config->get("pos8");
			$this->pos9=$this->config->get("pos9");
			$this->pos10=$this->config->get("pos10");
			$this->lastpos=$this->config->get("lastpos");
			$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
			$this->signlevel=$this->getServer()->getLevelByName($this->config->get("sign")["level"]);
			$this->sign=new Vector3($this->sign["x"],$this->sign["y"],$this->sign["z"]);
			$this->pos1=new Vector3($this->pos1["x"]+0.5,$this->pos1["y"],$this->pos1["z"]+0.5);
			$this->pos2=new Vector3($this->pos2["x"]+0.5,$this->pos2["y"],$this->pos2["z"]+0.5);
			$this->pos3=new Vector3($this->pos3["x"]+0.5,$this->pos3["y"],$this->pos3["z"]+0.5);
			$this->pos4=new Vector3($this->pos4["x"]+0.5,$this->pos4["y"],$this->pos4["z"]+0.5);
			$this->pos5=new Vector3($this->pos5["x"]+0.5,$this->pos5["y"],$this->pos5["z"]+0.5);
			$this->pos6=new Vector3($this->pos6["x"]+0.5,$this->pos6["y"],$this->pos6["z"]+0.5);
			$this->pos7=new Vector3($this->pos7["x"]+0.5,$this->pos7["y"],$this->pos7["z"]+0.5);
			$this->pos8=new Vector3($this->pos8["x"]+0.5,$this->pos8["y"],$this->pos8["z"]+0.5);
			$this->pos9=new Vector3($this->pos9["x"]+0.5,$this->pos9["y"],$this->pos9["z"]+0.5);
=======
			$this->pos10=new Vector3($this->pos10["x"]+0.5,$this->pos10["y"],$this->pos10["z"]+0.5);			
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
			$this->lastpos=new Vector3($this->lastpos["x"]+0.5,$this->lastpos["y"],$this->lastpos["z"]+0.5);
		}
		if(!$this->config->exists("endTime"))
		{
			$this->config->set("endTime",180);
		}
		if(!$this->config->exists("gameTime"))
		{
			$this->config->set("gameTime",300);
		}
		if(!$this->config->exists("waitTime"))
		{
			$this->config->set("waitTime",180);
		}
		if(!$this->config->exists("godTime"))
		{
			$this->config->set("godTime",10);
		}
		$this->endTime=(int)$this->config->get("endTime");//游戏时间
		$this->gameTime=(int)$this->config->get("gameTime");//游戏时间
		$this->waitTime=(int)$this->config->get("waitTime");//等待时间
		$this->godTime=(int)$this->config->get("godTime");//无敌时间
		$this->gameStatus=0;//当前状态
		$this->lastTime=0;//还没开始
		$this->players=array();//加入游戏的玩家
		$this->SetStatus=array();//设置状态
		$this->all=0;//最大玩家数量
		$this->config->save();
		$this->getServer()->getLogger()->info(TextFormat::BLUE."[HG] LOADED everything!");
	
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args)
	{
		if($command->getName()=="lobby")
		{
			if($this->gameStatus>=2)
			{
				$sender->sendMessage("[HG] The Game Has Started. You Cannot Go Back To The Lobby.");
				return;
			}
			if(isset($this->players[$sender->getName()]))
			{	
				unset($this->players[$sender->getName()]);
				$sender->setLevel($this->signlevel);
				$sender->teleport($this->signlevel->getSpawnLocation());
				$sender->sendMessage("[HG] Back to lobby...");
				$this->sendToAll("[HG] Player " .$sender->getName(). " exit from game");
				$this->changeStatusSign();
				if($this->gameStatus==1 && count($this->players)<2)
				{
					$this->gameStatus=0;
					$this->lastTime=0;
					$this->sendToAll("[HG] Less than 2 players,stopped countdown");
					/*foreach($this->players as $pl)
					{
						$p=$this->getServer()->getPlayer($pl["id"]);
						$p->setLevel($this->signlevel);
						$p->teleport($this->signlevel->getSpawnLocation());
						unset($p,$pl);
					}*/
				}
			}
			else
			{
				$sender->sendMessage("[HG] You are not in the game.");
			}
			return true;
		}
		if(!isset($args[0])){unset($sender,$cmd,$label,$args);return false;};
		switch ($args[0])
		{
		case "set":
			if($this->config->exists("lastpos"))
			{
				$sender->sendMessage("[HG] The game was set before,please use /fsg remove and try again.");
			}
			else
			{
				$name=$sender->getName();
				$this->SetStatus[$name]=0;
				$sender->sendMessage("[HG] Please tap the status sign.");
			}
			break;
		case "remove":
			$this->config->remove("sign");
			$this->config->remove("pos1");
			$this->config->remove("pos2");
			$this->config->remove("pos3");
			$this->config->remove("pos4");
			$this->config->remove("pos5");
			$this->config->remove("pos6");
			$this->config->remove("pos7");
			$this->config->remove("pos8");
			$this->config->remove("pos9");
			$this->config->remove("pos10");
			$this->config->remove("lastpos");
			$this->config->save();
			unset($this->sign,$this->pos1,$this->pos2,$this->pos3,$this->pos4,$this->pos5,$this->pos6,$this->pos7,$this->pos8,$this->pos9,$this->pos10,$this->lastpos);
<<<<<<< HEAD
			$sender->sendMessage("[HG] succeeded in deleting game settings");
=======
			$sender->sendMessage(TextFormat::GREEN . "Game settings successfully removed.");
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
			break;
		case "start":
			$this->sendToAll("[HG] Forced Match Start");
			$this->gameStatus=1;
			$this->lastTime=5;
			break;
		case "reload":
			unset($this->config);
			@mkdir($this->getDataFolder(), 0777, true);
			$this->config=new Config($this->getDataFolder() . "config.yml", Config::YAML, array());
			if($this->config->exists("lastpos"))
			{
				$this->sign=$this->config->get("sign");
				$this->pos1=$this->config->get("pos1");
				$this->pos2=$this->config->get("pos2");
				$this->pos3=$this->config->get("pos3");
				$this->pos4=$this->config->get("pos4");
				$this->pos5=$this->config->get("pos5");
				$this->pos6=$this->config->get("pos6");
				$this->pos7=$this->config->get("pos7");
				$this->pos8=$this->config->get("pos8");
<<<<<<< HEAD
				$this->pos8=$this->config->get("pos9");
				$this->pos8=$this->config->get("pos10");
=======
				$this->pos9=$this->config->get("pos9");
				$this->pos10=$this->config->get("pos10");
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				$this->lastpos=$this->config->get("lastpos");
				$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
				$this->signlevel=$this->getServer()->getLevelByName($this->config->get("sign")["level"]);
				$this->sign=new Vector3($this->sign["x"],$this->sign["y"],$this->sign["z"]);
				$this->pos1=new Vector3($this->pos1["x"]+0.5,$this->pos1["y"],$this->pos1["z"]+0.5);
				$this->pos2=new Vector3($this->pos2["x"]+0.5,$this->pos2["y"],$this->pos2["z"]+0.5);
				$this->pos3=new Vector3($this->pos3["x"]+0.5,$this->pos3["y"],$this->pos3["z"]+0.5);
				$this->pos4=new Vector3($this->pos4["x"]+0.5,$this->pos4["y"],$this->pos4["z"]+0.5);
				$this->pos5=new Vector3($this->pos5["x"]+0.5,$this->pos5["y"],$this->pos5["z"]+0.5);
				$this->pos6=new Vector3($this->pos6["x"]+0.5,$this->pos6["y"],$this->pos6["z"]+0.5);
				$this->pos7=new Vector3($this->pos7["x"]+0.5,$this->pos7["y"],$this->pos7["z"]+0.5);
				$this->pos8=new Vector3($this->pos8["x"]+0.5,$this->pos8["y"],$this->pos8["z"]+0.5);
				$this->pos9=new Vector3($this->pos9["x"]+0.5,$this->pos9["y"],$this->pos9["z"]+0.5);
<<<<<<< HEAD
=======
				$this->pos10=new Vector3($this->pos10["x"]+0.5,$this->pos10["y"],$this->pos10["z"]+0.5);	
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				$this->lastpos=new Vector3($this->lastpos["x"]+0.5,$this->lastpos["y"],$this->lastpos["z"]+0.5);
			}
			if(!$this->config->exists("endTime"))
			{
				$this->config->set("endTime",600);
			}
			if(!$this->config->exists("gameTime"))
			{
				$this->config->set("gameTime",300);
			}
			if(!$this->config->exists("waitTime"))
			{
				$this->config->set("waitTime",180);
			}
			if(!$this->config->exists("godTime"))
			{
				$this->config->set("godTime",15);
			}
			$this->endTime=(int)$this->config->get("endTime");
			$this->gameTime=(int)$this->config->get("gameTime");
			$this->waitTime=(int)$this->config->get("waitTime");
			$this->godTime=(int)$this->config->get("godTime");
			$this->gameStatus=0;
			$this->lastTime=0;
			$this->players=array();
			$this->SetStatus=array();
			$this->all=0;
			$this->config->save();
			$sender->sendMessage("[HG] Config reloaded");
			break;
		default:
			return false;
			break;
		}
		return true;
	}
	
	/* public function onPlayerRespawn(PlayerRespawnEvent $event){
        $player = $event->getPlayer();
        if($this->config->exists("lastpos"))
        {
				$v3=$this->signlevel->getSpawnLocation();
				$event->setRespawnPosition(new Position($v3->x,$v3->y,$v3->z,$this->signlevel));
			}
		}
	*/
	public function onPlace(BlockPlaceEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		$block=$event->getBlock();
		if($this->PlayerIsInGame($event->getPlayer()->getName()) || $block->getLevel()==$this->level)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($block,$event);
	}
	
	public function onMove(PlayerMoveEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		if($this->PlayerIsInGame($event->getPlayer()->getName()) && $this->gameStatus<=1)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($event);
	}
	public function onBreak(BlockBreakEvent $event)
	{
		if(!isset($this->sign))
		{
			return;
		}
		$sign = $this->config->get("sign");
		$block=$event->getBlock();
		if($this->PlayerIsInGame($event->getPlayer()->getName()) || ($block->getX()==$sign["x"] && $block->getY()==$sign["y"] && $block->getZ()==$sign["z"] && $block->getLevel()->getFolderName()==$sign["level"]) || $block->getLevel()==$this->level)
		{
			if(!$event->getPlayer()->isOp())
			{
				$event->setCancelled();
			}
		}
		unset($sign,$block,$event);
	}
	
	public function onPlayerCommand(PlayerCommandPreprocessEvent $event)
	{
		if(!$this->PlayerIsInGame($event->getPlayer()->getName()) || $event->getPlayer()->isOp() || substr($event->getMessage(),0,1)!="/")
		{
			unset($event);
			return;
		}
		switch(strtolower(explode(" ",$event->getMessage())[0]))
		{
		case "/kill":
		case "/lobby":
			
			break;
		default:
			$event->setCancelled();
			$event->getPlayer()->sendMessage("[HG] You Are Now In The Game You Cannot Use Commands Besides...");
			$event->getPlayer()->sendMessage("[HG] /kill or /lobby to exit game");
			break;
		}
		unset($event);
	}
	
	/* public function onDamage(EntityDamageEvent $event)
	{
		$player = $event->getEntity();
		if ($event instanceof EntityDamageByEntityEvent)
		{
        	$player = $event->getEntity();
        	$killer = $event->getDamager();
			if($player instanceof Player && $killer instanceof Player)
			{
		    	if($this->PlayerIsInGame($player->getName()) && ($this->gameStatus==2 || $this->gameStatus==1))
		    	{
		    		$event->setCancelled();
		    	}
		    	if($this->PlayerIsInGame($player->getName()) && !$this->PlayerIsInGame($killer->getName()) && !$killer->isOp())
		    	{
		    		$event->setCancelled();
		    		$killer->sendMessage("//ADD money for PlayerDeathEvent");
		    		$killer->kill();
		    	}
		    }
		}
		
		unset($player,$killer,$event);
	} */
	
	public function PlayerIsInGame($name)
	{
		return isset($this->players[$name]);
	}
	
	public function PlayerDeath(PlayerDeathEvent $event){
		if($this->gameStatus==3 || $this->gameStatus==4)
		{
			if(isset($this->players[$event->getEntity()->getName()]))
			{
				$this->ClearInv($event->getEntity());
				unset($this->players[$event->getEntity()->getName()]);
				if(count($this->players)>1)
				{
					$this->sendToAll("[HG] Player {$event->getEntity()->getName()} died");
				$this->sendToAll("[HG] Players Left " .count($this->players));
					$this->sendToAll("[HG] Time Left  ".$this->lastTime." seconds");
				}
				$this->changeStatusSign();
			}
			
		}
	}
	
	public function sendToAll($msg){
		foreach($this->players as $pl)
		{
			$this->getServer()->getPlayer($pl["id"])->sendMessage($msg);
		}
		$this->getServer()->getLogger()->info($msg);
		unset($pl,$msg);
	}
	
	public function gameTimber(){
		if(!isset($this->lastpos) || $this->lastpos==array())
		{
			return;
		}
		if(!$this->signlevel instanceof Level)
		{
			$this->level = $this->getServer()->getLevelByName($this->config->get("pos1")["level"]);
			$this->signlevel = $this->getServer()->getLevelByName($this->config->get("sign")["level"]);
			if(!$this->signlevel instanceof Level)
			{
				return;
			}
		}
		$this->changeStatusSign();
		if($this->gameStatus==0)
		{
			$i=0;
			foreach($this->players as $key=>$val)
			{
				$i++;
				$p=$this->getServer()->getPlayer($val["id"]);
				//echo($i."\n");
				//$p->setLevel($this->level);
				eval("\$p->teleport(\$this->pos".$i.");");
				unset($p);
			}
		}
		if($this->gameStatus==1)
		{
			$this->lastTime--;
			$i=0;
			foreach($this->players as $key=>$val)
			{
				$i++;
				$p=$this->getServer()->getPlayer($val["id"]);
				//echo($i."\n");
				//$p->setLevel($this->level);
				eval("\$p->teleport(\$this->pos".$i.");");
				unset($p);
			}
			switch($this->lastTime)
			{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$this->sendToAll(TextFormat::YELLOW."Starting in ".$this->lastTime.".");
				break;	
			case 10:
<<<<<<< HEAD
			case 20:
			$this->sendToAll("[HG] The match is beginning");
				break;
			case 30:
				$this->sendToAll("[HG] The game will start in " .$this->lastTime. " seconds");
=======
				$this->sendToAll(TextFormat::YELLOW."The match will start in 0:10.");
				break;
			case 30:
				$this->sendToAll(TextFormat::YELLOW."The match will start in 0:30.");
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				break;
			case 60:
				$this->sendToAll(" [HG] The game will start in one minute");
				break;
			case 90:
				$this->sendToAll("[HG] The game will start in one minute thirty seconds");
				break;
			case 120:
				$this->sendToAll("[HG] The game will start in two minutes");
				break;
			case 150:
				$this->sendToAll("[HG] The game will start in two minutes thirty seconds");
				break;
			case 0:
				$this->gameStatus=2;
<<<<<<< HEAD
				$this->sendToAll("[HG] THE GAMES HAVE BEGUN");
				$this->sendToAll("[HG] Chest Have Been Filled!");
				$this->lastTime = $this->godTime;
=======
				$this->sendToAll(TextFormat::YELLOW."The match has started.");
				$this->lastTime=$this->godTime;
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				$this->resetChest();
				foreach($this->players as $key=>$val)
				{
					$p=$this->getServer()->getPlayer($val["id"]);
					$p->setMaxHealth(25);
					$p->setHealth(25);
					$p->setLevel($this->level);
				}
				$this->all=count($this->players);
				break;
			}
		}
		if($this->gameStatus==2)
		{
			$this->lastTime--;
			if($this->lastTime<=0)
			{
				$this->gameStatus=3;
				$this->sendToAll("[HG] ");
				$this->lastTime=$this->gameTime;
				$this->resetChest();
			}
		}
		if($this->gameStatus==3 || $this->gameStatus==4)
		{
			if(count($this->players)==1)
			{
				$this->sendToAll(" [HG] Congratulations! You have won the game");
				foreach($this->players as &$pl)
				{
					$p=$this->getServer()->getPlayer($pl["id"]);
					Server::getInstance()->broadcastMessage("[HG] Congratulates to " .$p->getName(). " for whom that has won the game");
					$p->setLevel($this->signlevel);
					$p->getInventory()->clearAll();
					$p->setMaxHealth(25);
					$p->setHealth(25);
					$p->teleport($this->signlevel->getSpawnLocation());
					unset($pl,$p);
				}
				$this->clearChest();
				$this->players=array();
				$this->gameStatus=0;
				$this->lastTime=0;
				return;
			}
			else if(count($this->players)==0)
			{
				Server::getInstance()->broadcastMessage("[HG] The Games Have Ended");
				$this->gameStatus=0;
				$this->lastTime=0;
				$this->clearChest();
				$this->ClearAllInv();
				return;
			}
		}
		if($this->gameStatus==3)
		{
			$this->lastTime--;
			switch($this->lastTime)
			{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$this->sendToAll(TextFormat::YELLOW."Deathmatch will start in " .$this->lastTime. ".");
				break;	
			case 10:
<<<<<<< HEAD
				$this->sendToAll("[HG] " .$this->lastTime. " seconds left for the death match");
=======
				$this->sendToAll(TextFormat::YELLOW."Deathmatch will start in 0:10.");
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				break;
			case 0:
				$this->sendToAll("[HG] the death match begins");
				foreach($this->players as $pl)
				{
					$p=$this->getServer()->getPlayer($pl["id"]);
					$p->setLevel($this->level);
					$p->teleport($this->lastpos);
					unset($p,$pl);
				}
				$this->gameStatus=4;
				$this->lastTime = $this->gameTime;
				break;
			}
		}
		if($this->gameStatus==4)
		{
			$this->lastTime--;
			switch($this->lastTime)
			{
			case 1:
			case 2:
			case 3:
			case 4:
			case 5:
				$this->sendToAll("Ending in " .$this->lastTime. ".");
				break;	
			case 10:
				$this->sendToAll("The match will end in 0:30.");
				break;
			//case 20:
			case 30:
<<<<<<< HEAD
				$this->sendToAll("[HG] there are " .$this->lastTime. " seconds to the end of the game");
=======
				$this->sendToAll("The match will end in 0:30.");
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				break;
			case 0:
				$this->sendToAll("[HG] games ended");
				Server::getInstance()->broadcastMessage("[HG] Games Have ended");
				foreach($this->players as $pl)
				{
					$p=$this->getServer()->getPlayer($pl["id"]);
					$p->setLevel($this->signlevel);
					$p->teleport($this->signlevel->getSpawnLocation());
					$p->getInventory()->clearAll();
					$p->setMaxHealth(25);
					$p->setHealth(25);
					unset($p,$pl);
				}
				$this->clearChest();
				//$this->ClearAllInv();
				$this->players=array();
				$this->gameStatus=0;
				$this->lastTime=0;
				break;
			}
		}
		$this->changeStatusSign();
	}

	public function resetChest()
	{
		ResetChest::getInstance()->ResetChest();
	}
	
	public function clearChest()
	{
		ResetChest::getInstance()->ClearChest();
	}
	
	public function changeStatusSign()
	{
		if(!isset($this->sign))
		{
			return;
		}
		$sign=$this->signlevel->getTile($this->sign);
		if($sign instanceof Sign)
		{
			switch($this->gameStatus)
			{
			case 0:
				$sign->setText("HG","Tap to join","player amount :".count($this->players),"");
				break;
			case 1:
				$sign->setText("HG","Tap to join","player amount :".count($this->players),"time left :".$this->lastTime."sec");
				break;
			case 2:
				$sign->setText("HG","starting right now","player amount :".count($this->players),"you are prohibited :".$this->lastTime."sec");
				break;
			case 3:
				$sign->setText("HG","running","alive :".count($this->players)."/{$this->all}","time to the death match :".$this->lastTime."sec");
				break;
			case 4:
				$sign->setText("HG","DM","players left :".count($this->players)."/{$this->all}","time left :".$this->lastTime."sec");
				break;
			}
		}
		unset($sign);
	}
	public function playerBlockTouch(PlayerInteractEvent $event){
		$player=$event->getPlayer();
		$username=$player->getName();
		$block=$event->getBlock();
		$levelname=$player->getLevel()->getFolderName();
		if(isset($this->SetStatus[$username]))
		{
			switch ($this->SetStatus[$username])
			{
			case 0:
				if($event->getBlock()->getID() != 63 && $event->getBlock()->getID() != 68)
				{
					$player->sendMessage(TextFormat::GREEN."[HG] please choose a sign to click on");
					return;
				}
				$this->sign=array(
					"x" =>$block->getX(),
					"y" =>$block->getY(),
					"z" =>$block->getZ(),
					"level" =>$levelname);
				$this->config->set("sign",$this->sign);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."[HG] SIGN for condition has been created");
				$player->sendMessage(TextFormat::GREEN." [HG] please click on the 1st spawnpoint");
				$this->signlevel=$this->getServer()->getLevelByName($this->config->get("sign")["level"]);
				$this->sign=new Vector3($this->sign["x"],$this->sign["y"],$this->sign["z"]);
				$this->changeStatusSign();
				break;
			case 1:
				$this->pos1=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos1",$this->pos1);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN." [HG] Spawnpoint 1 created");
				$player->sendMessage(TextFormat::GREEN."[HG] Please click.on the 2nd spawnpoint");
				$this->pos1=new Vector3($this->pos1["x"]+0.5,$this->pos1["y"],$this->pos1["z"]+0.5);
				break;
			case 2:
				 $this->pos2=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos2",$this->pos2);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN." [HG] spawnpoint 2 created");
				$player->sendMessage(TextFormat::GREEN." [HG] Please click on the 3rd spawnpoint");
				$this->pos2=new Vector3($this->pos2["x"]+0.5,$this->pos2["y"],$this->pos2["z"]+0.5);
				break;	
			case 3:
				$this->pos3=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos3",$this->pos3);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."[HG] spawnpoint 3 created");
				$player->sendMessage(TextFormat::GREEN." [HG] Please click on the 4th spawnpoint");
				$this->pos3=new Vector3($this->pos3["x"]+0.5,$this->pos3["y"],$this->pos3["z"]+0.5);
				break;	
			case 4:
				$this->pos4=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos4",$this->pos4);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."[HG] spawnpoint 4 created");
				$player->sendMessage(TextFormat::GREEN." [HG] please click on the 5th spawnpoint");
				$this->pos4=new Vector3($this->pos4["x"]+0.5,$this->pos4["y"],$this->pos4["z"]+0.5);
				break;
			case 5:
				$this->pos5=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos5",$this->pos5);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN." [HG] spawnpoint 5 created");
				$player->sendMessage(TextFormat::GREEN."[Hunger Game] Please click on the 6th spawnpoint");
				$this->pos5=new Vector3($this->pos5["x"]+0.5,$this->pos5["y"],$this->pos5["z"]+0.5);
				break;
			case 6:
				$this->pos6=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos6",$this->pos6);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."[HG] spawnpoint 6 created");
				$player->sendMessage(TextFormat::GREEN."[HG] Please click on the 7th spawnpoint");
				$this->pos6=new Vector3($this->pos6["x"]+0.5,$this->pos6["y"],$this->pos6["z"]+0.5);
				break;
			case 7:
				$this->pos7=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos7",$this->pos7);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."[HG] spawnpoint 7 created");
				$player->sendMessage(TextFormat::GREEN."[HG] Please click on the 8th spawnpoint");
				$this->pos7=new Vector3($this->pos7["x"]+0.5,$this->pos7["y"],$this->pos7["z"]+0.5);
				break;	
			case 8:
				$this->pos8=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos8",$this->pos8);
				$this->config->save();
				$this->SetStatus[$username]++;
<<<<<<< HEAD
				$player->sendMessage(TextFormat::GREEN."[HG] spawnpoint 8 created");
				$player->sendMessage(TextFormat::GREEN."Please click on the 9th spawnpoint.");
				$this->pos9=new Vector3($this->pos8["x"]+0.5,$this->pos8["y"],$this->pos8["z"]+0.5);
				break;
			case 9:
=======
				$player->sendMessage(TextFormat::GREEN."Spawnpoint 9 created!");
				$player->sendMessage(TextFormat::GREEN."Please click on the 10th spawnpoint.");
				$this->pos9=new Vector3($this->pos9["x"]+0.5,$this->pos9["y"],$this->pos9["z"]+0.5);
				break;
			case 10:
				$this->pos10=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos10",$this->pos10);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."Spawnpoint 10 created!");
				$player->sendMessage(TextFormat::GREEN."Please click on the 11th spawnpoint.");
				$this->pos9=new Vector3($this->pos10["x"]+0.5,$this->pos10["y"],$this->pos10["z"]+0.5);
				break;
			case 11:
				$this->pos11=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("pos11",$this->pos11);
				$this->config->save();
				$this->SetStatus[$username]++;
				$player->sendMessage(TextFormat::GREEN."Spawnpoint 11 created!");
				$player->sendMessage(TextFormat::GREEN."Please click on the spawnpoint for deathmatch.");
				$this->pos10=new Vector3($this->pos10["x"]+0.5,$this->pos10["y"],$this->pos10["z"]+0.5);
				break;	
			case lastpos:
>>>>>>> 4b4f0bbfb82a8ee5163644d848ffa8fc8187da4a
				$this->lastpos=array(
					"x" =>$block->x,
					"y" =>$block->y,
					"z" =>$block->z,
					"level" =>$levelname);
				$this->config->set("lastpos",$this->lastpos);
				$this->config->save();
				$this->lastpos=new Vector3($this->lastpos["x"]+0.5,$this->lastpos["y"],$this->lastpos["z"]+0.5);
				unset($this->SetStatus[$username]);
				$player->sendMessage(TextFormat::GREEN."[HG] death match destination created");
				$player->sendMessage(TextFormat::GREEN."[HG] All settings completed and you can start a game now");
				$this->level=$this->getServer()->getLevelByName($this->config->get("pos1")["level"]);					
			}
		}
		else
		{
			$sign=$event->getPlayer()->getLevel()->getTile($event->getBlock());
			if(isset($this->lastpos) && $this->lastpos!=array() && $sign instanceof Sign && $sign->getX()==$this->sign->x && $sign->getY()==$this->sign->y && $sign->getZ()==$this->sign->z && $event->getPlayer()->getLevel()->getFolderName()==$this->config->get("sign")["level"])
			{
				if(!$this->config->exists("lastpos"))
				{
					$event->getPlayer()->sendMessage("[HG] You can not join the game for the game hasn't been set yet");
					return;
				}
				if(!$event->getPlayer()->hasPermission("FSurvivalGame.touch.startgame"))
				{
					$event->getPlayer()->sendMessage("[HG] You don't have permission to join this game");
					return;
				}
				if(!$event->getPlayer()->isOp())
				{
					$inv=$event->getPlayer()->getInventory();
					for($i=0;$i<$inv->getSize();$i++)
    				{
    					if($inv->getItem($i)->getID()!=0)
    					{
    						$event->getPlayer()->sendMessage("[HG] take the stuff out of ur inv to join match");
    						return;
    					}
    				}
    				foreach($inv->getArmorContents() as $i)
    				{
    					if($i->getID()!=0)
    					{
    						$event->getPlayer()->sendMessage("[HG] Take your armor off please");
    						return;
    					}
    				}
    			}
				if($this->gameStatus==0 || $this->gameStatus==1)
				{
					if(!isset($this->players[$event->getPlayer()->getName()]))
					{
						if(count($this->players)>=6)
						{
							$event->getPlayer()->sendMessage("[HG] the map is full");
							return;
						}
						$this->sendToAll("[HG]Player " .$event->getPlayer()->getName(). " joined the game");
						$this->players[$event->getPlayer()->getName()]=array("id"=>$event->getPlayer()->getName());
						$event->getPlayer()->sendMessage("[HG] joined the game successfully");
						if($this->gameStatus==0 && count($this->players)>=2)
						{
							$this->gameStatus=1;
							$this->lastTime=$this->waitTime;
							$this->sendToAll("[HG] The game will countdown when a low amount of players are in");
						}
						if(count($this->players)==8 && $this->gameStatus==1 && $this->lastTime>5)
						{
							$this->sendToAll("[HG] Match Is Full.....Starting");
							$this->lastTime=5;
						}
						$this->changeStatusSign();
					}
					else
					{
						$event->getPlayer()->sendMessage("[HG] You are already in the game do /lobby to leave");
					}
				}
				else
				{
					$event->getPlayer()->sendMessage("[HG] Can not join the game for it has already started");
				}
			}
		}
	}
	
	public function ClearInv($player)
	{
		if(!$player instanceof Player)
		{
			unset($player);
			return;
		}
		$inv=$player->getInventory();
		if(!$inv instanceof Inventory)
		{
			unset($player,$inv);
			return;
		}
		$inv->clearAll();
		unset($player,$inv);
	}
	
	public function ClearAllInv()
	{
		foreach($this->players as $pl)
		{
			$player=$this->getServer()->getPlayer($pl["id"]);
			if(!$player instanceof Player)
			{
				continue;
			}
			$this->ClearInv($player);
		}
		unset($pl,$player);
	}
	
	public function PlayerQuit(PlayerQuitEvent $event){
		if(isset($this->players[$event->getPlayer()->getName()]))
		{	
			unset($this->players[$event->getPlayer()->getName()]);
			$this->ClearInv($event->getPlayer());
			$this->sendToAll("[HG] Player " .$event->getPlayer()->getName(). " has left the game");
			$this->changeStatusSign();
			if($this->gameStatus==1 && count($this->players)<2)
			{
				$this->gameStatus=0;
				$this->lastTime=0;
				$this->sendToAll("[HG] not enough players countdown stopped");
				/*foreach($this->players as $pl)
				{
					$p=$this->getServer()->getPlayer($pl["id"]);
					$p->setLevel($this->signlevel);
					$p->teleport($this->signlevel->getSpawnLocation());
					unset($p,$pl);
				}*/
			}
		}
	}
	
	public function onDisable(){
		
		
	}
}
?>
