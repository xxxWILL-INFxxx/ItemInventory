<?php

/*
 *
 *
 *    ___      ___  _______    ____________  __________   ___________   ___      ___
 *   |   \    /   ||       \  |            ||          | /           \ |   \    /   |
 *   |    \  /    ||   __   \ |____    ____||     _____||     ___     ||    \  /    |
 *   |     \/     ||  |  |   |    |    |    |    |__    |    |___|    ||     \/     |
 *   |            ||  |  |   |    |    |    |     __|   |     ___     ||            |
 *   |   |\__/|   ||  |__|   |    |    |    |    |_____ |    |   |    ||   |\__/|   |
 *   |   |    |   ||        /     |    |    |          ||    |   |    ||   |    |   |
 *   |___|    |___||_______/      |____|    |__________||____|   |____||___|    |___|
 *   
 *                                2016 MineDogsTeam
 *                                Plugin by EmreTr1
 *                                  Version: 1.0
 *                               API: 1.13.0, 2.0.0
 *                       Description: Add ChestInventory to items
 *                               All rights reserved.
*/

namespace ItemInventory;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
// New Events
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\EntityInventoryChangeEvent;
//
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\block\Block;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as c;

class Main extends PluginBase implements Listener{
	
	//prefix
	public $prefix="§6§lItem§aInventory§7 >§r ";
	//variables
	public $lastopen=array();
	public $ids=array();
	
	//Plugin onEnable//
	public function OnEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->getServer()->getLogger()->info($this->prefix.c::GREEN."ItemInventory has been Enabled!");
		@mkdir($this->getDataFolder());
		$this->config = new Config($this->getDataFolder() . "IIS.yml", Config::YAML);
		$this->config->save();
	}
	
	//Plugin onDisable//
 public function OnDisable(){
   $this->getServer()->getLogger()->info($this->prefix.c::RED."ItemInventory has been Disabled!");
 }
 
 //When players process ItemInventory plugin Commands//     
	public function OnCommand(CommandSender $s, Command $cmd, $label, array $args){
		if(empty($args[0])){
			$s->sendMessage($this->prefix.c::RED."You Need help?: /ii help");
		}
		if(!isset($args[0])){unset($s,$cmd,$label,$args);return false;};
  if($s->isOp() and $s instanceof Player){
		switch($args[0]){
			case "help":
		        $s->sendMessage(c::GRAY."<---------- $this->prefix ---------->");
				$s->sendMessage(c::GREEN."/ii add <ItemInventoryitemid>:§d Add a ItemInventory");
				$s->sendMessage(c::GREEN."/ii additem <ItemInventoryitemid> <itemid>:§d Add a item to ItemInventory");
				$s->sendMessage(c::GREEN."/ii delitem <ItemInventoryitemid> <itemid>:§d Remove a item from ItemInventory");
		        $s->sendMessage(c::GREEN."/ii addcommand <ItemInventoryitemid> <itemdid>:§d Add a command to ItemInventory items");
				$s->sendMessage(c::GRAY."<---------- $this->prefix ---------->");
				break;
			case "add":
			    if((!empty($args[1])) and !($this->config->getNested("IIS.$args[1]"))){
					$name=$s->getName();
					if($args[1]==35){
						$s->sendMessage($this->prefix."§cPls, change This id, and try again! Wait next Version...");
						return;
					}
					$level=$s->getLevel()->getFolderName();
					//$damage=0;
					//if(empty($args[2])){
						//$damage=$args[2];      Comin soon in Next Version.
					//}
					//$iis=$args[1].":".$damage;
				  $this->config->setNested("IIS.$args[1]", ["Name"=>$name, "UsedLevels"=>array($level=>true), "WriteCommandsOn"=>"Player", "Items"=>array(), "Commands"=>array()]);
				  $this->config->save();
					$s->sendMessage($this->prefix.c::GREEN."INSTALLED! Please Change used Level on Config!");
				}else{
					$s->sendMessage($this->prefix.c::YELLOW."Usage: /ii add <ItemInventoryID>");
				}
				break;
		    case "additem":
			    if($this->config->getNested("IIS.$args[1]") and (!empty($args[1])) and (!empty($args[2]))){
			  if(empty($args[3])){
			  	 $args[3]=0;
			  }
					$curi=$this->config->getNested("IIS.$args[1].Items");
					$esya=$args[2].":".$args[3].":"."§aItemName";
					array_push($curi, $esya);
					$this->config->setNested("IIS.$args[1].Items", $curi);
					$this->config->save();
					$s->sendMessage($this->prefix.c::GOLD.$args[2]." ID's item added to ".$args[1]);
				}else{
					$s->sendMessage($this->prefix.c::YELLOW."Usage: /ii additem <ItemInventoryID> <Item ID> <damage>");
				}
				break;
			case "updateall":
			    $newChanges=array();
			    for($i=1; $i < 500; $i++){
			    	if($this->config->getNested("IIS.$i")){
			    		$options=$this->config->getNested("IIS.$i");
			    		$newChanges[$i]=array();
			    		foreach($options as $opt=>$val){
			    			$new=null;
			    			$newval=null;
			    			if($opt=="UsedLevel"){
			    				$new="UsedLevels";
			    				$newval=array($val=>true);
			    			}
			    			if($new==null){
			    				$newChanges[$i][$opt]=$val;
			    			}else{
			    				$newChanges[$i][$new]=$newval;
			    			}
			    		}
			    	}
			    }
			    $this->config->set("IIS", $newChanges);
			    $this->config->save();
			    $s->sendMessage($this->prefix."§a All old ItemInventory settings updated.");
			    break;
			case "setname":
			    if(!empty($args[1]) and !empty($args[2]) and $this->config->getNested("IIS.$args[1]")){
			    	$id=$args[1];
			    	$cfg=$this->config->getNested("IIS.$id");
			    	$cfg["Name"]=$args[2];
			    	$this->config->setNested("IIS.$id", $cfg);
			    	$this->config->save();
			    	$s->sendMessage($this->prefix."§a Name Changed.");
			    }else{
			    	$s->sendMessage($this->prefix."§e Kullanim: /ii setname <ItemInventory ID> <New Name>");
			    }
			    break;
			case "setitemname":
			    if(!empty($args[1]) and (!empty($args[2])) and (!empty($args[3])) and $this->config->getNested("IIS.$args[1]")){
			    	$curi=$this->config->getNested("IIS.$args[1].Items");
			    	if(empty($args[4])){
			    		$args[4]=0;
			    	}
			    	foreach($curi as $item){
			    		$pr=explode(":", $item);
			    		$sta=array_search($item, $curi);
			    		if($args[3]==$pr[0] and $args[4]==$pr[1]){
			    			$curi[$sta]=$args[3].":".$args[4].":".$args[2];
			    		}
			    	}
			    	$this->config->setNested("IIS.$args[1].Items", $curi);
			    	$this->config->save();
			    	$s->sendMessage($this->prefix."§aItemName changed.");
			    }else{
			    	 $s->sendMessage($this->prefix."§eKullanim: /ii setitemname <ItemInventoryID> <NewItemName> <ItemID> [ItemDamage]");
			    }
			    break;
		 case "removeitem":
			case "deleteitem":
			case "delitem":
			    if($this->config->getNested("IIS.$args[1]") and (!empty($args[1])) and (!empty($args[2]))){
					$curi=$this->config->getNested("IIS.$args[1].Items");
					$esya=0;
					foreach($curi as $item){
						$hgs=explode(":", $item);
						if($args[2]==$hgs[0] or $args[2]==$hgs[2]){
							$esya=$item;
						}
					}
					unset($curi[$esya]);
					$this->config->setNested("IIS.$args[1].Items", $curi);
					$this->config->save();
					$s->sendMessage($this->prefix.c::RED.$args[2]." ID's item was deleted from the ".$args[1]);
				}else{
					$s->sendMessage($this->prefix.c::YELLOW."Usage: /ii delitem <ItemInventoryID> <Item ID or Name>");
				}
				break;
			case "addcommand":
			    if((!empty($args[1])) and (!empty($args[2])) and ($args[3]>=0) and (!empty($args[4]))){
                                if($s instanceof Player){
                                    if($this->config->getNested("IIS.$args[1]")){
                                        $co=$this->config->getNested("IIS.$args[1]");
									    $cn=$args[1];
                                        $ag=$args[2].":".$args[3];
                                            array_shift($args);
                                            array_shift($args);
                                            array_shift($args);
                                            array_shift($args);
                                            $command = trim(implode(" ", $args));
                                            $this->config->setNested("IIS.$cn.Commands.$ag", $command);
                                            $this->config->save();
                                            $s->sendMessage($this->prefix."$cn :Added command to $ag ID item");
                                    }
                                }
                            }else{
								$s->sendMessage($this->prefix.c::YELLOW."Usage: /ii addcommand <ItemInventoryID> <Item ID> <Item Damage> <Command>");
							}
				break;
				default:
				  $s->sendMessage($this->prefix."§aNeed Help?: /ii help");
				  break;
          }
		}else{
    $s->sendMessage(c::RED."You not have permission to use this command");
  }
	}
	
	//When players quit game, Return to old Inventory Contents//
	public function onQuit(PlayerQuitEvent $event){
		$name=$event->getPlayer()->getName();
		if(!isset($this->lastopen[$name])){
			return;
		}
		$contents=$this->lastopen[$name];
		$event->getPlayer()->getInventory()->setContents($contents);
		unset($this->lastopen[$name]);
		unset($this->ids[$name]);
	}
	
	//When players Hold ItemInventory item on hand, Open ItemInventory//
	public function OnHold(PlayerInteractEvent $event){
		$player=$event->getPlayer();
		$item=$event->getItem();
		$id=$item->getId();
		if($this->config->getNested("IIS.$id")){
			$levels=$this->config->getNested("IIS.$id.UsedLevels");
			$lev=$player->getLevel()->getFolderName();
			if(isset($levels[$lev]) and $levels[$lev]==true){
				if(isset($this->lastopen[$player->getName()])){
					$player->sendMessage("§cMenu already opened!");
					return false;
				}
			$items=$this->config->getNested("IIS.$id.Items");
			$this->lastopen[$player->getName()]=$player->getInventory()->getContents();
			$this->ids[$player->getName()]=$id;
			$inv=$player->getInventory();
			$inv->clearAll();
			for($i=0; $i<count($items); $i++){
				$ported=explode(":", $items[$i]);
				$inv->addItem(Item::get($ported[0], $ported[1]));
			}
			$inv->addItem(Item::get(35, 14, 1));
		  }
		}
		if(isset($this->lastopen[$player->getName()])){
			$event->setCancelled(true);
			if($item->getId()==35 and $item->getDamage()==14){
				$player->getInventory()->clearAll();
				$contents=$this->lastopen[$player->getName()];
				$player->getInventory()->setContents($contents);
				unset($this->ids[$player->getName()]);
				unset($this->lastopen[$player->getName()]);
				return;
			}
			$damage=$item->getDamage();
			$title="$id".":"."$damage";
			$invid=$this->ids[$player->getName()];
			if($this->config->getNested("IIS.$invid.Commands.$title")){
				$cmd=$this->config->getNested("IIS.$invid.Commands.$title");
				$writeon=$this->config->getNested("IIS.$invid")["WriteCommandsOn"];
				$written=new ConsoleCommandSender();
				if($writeon=="Player"){
					$written=$player;
				}
				$this->getServer()->dispatchCommand($written, str_ireplace("{player}", $player->getName(), $cmd));
			}
		}
	} 
	
	// No DROP ItemInventory Items //
	public function onDrop(PlayerDropItemEvent $event){
		$p=$event->getPlayer();
		if(isset($this->lastopen[$p->getName()])){
			$event->setCancelled(true);
			$inv=$p->getInventory();
			$inv->clearAll();
			$id=$this->ids[$p->getName()];
			$items=$this->config->getNested("IIS.$id.Items");
			for($i=0; $i < count($items); $i++){
				$item=explode(":", $items[$i]);
				$inv->addItem(Item::get($item[0], $item[1]));
			}
			$inv->addItem(Item::get(35, 14));
		}
	}
	
	// When players teleport to a position, Close ItemInventory //
	public function onTeleport(EntityTeleportEvent $event){
		$p=$event->getEntity();
		if($p instanceof Player){
			if(isset($this->lastopen[$p->getName()])){
				$contents=$this->lastopen[$p->getName()];
				$p->getInventory()->setContents($contents);
				unset($this->lastopen[$p->getName()]);
			}
		}
	}
	
	//When players Change Gamemode, Close ItemInventory //
	public function onChangeGamemode(PlayerGameModeChangeEvent $event){
		$p=$event->getPlayer();
		if($p instanceof Player){
			if(isset($this->lastopen[$p->getName()])){
				unset($this->lastopen[$p->getName()]);
			}
		}
	}
	
	//When players ItemInventory item held, SendPopup Name //
	public function OnHeld(PlayerItemHeldEvent $event){
		$p=$event->getPlayer();
		$esya=$event->getItem();
		$item=$event->getItem()->getId();
		if($this->config->getNested("IIS.$item")){
			$level=$this->config->getNested("IIS.$item.UsedLevels");
			if(isset($level[$p->getLevel()->getFolderName()]) and $level[$p->getLevel()->getFolderName()]==true){
				$isim=$this->config->getNested("IIS.$item")["Name"];
			 $p->sendPopup($isim);
			}
		}
		if(isset($this->lastopen[$p->getName()])){
			if($item==35 and $esya->getDamage()==14){
				$p->sendPopup("§cClose Menu");
				return;
			}
			$id=$this->ids[$p->getName()];
			$items=$this->config->getNested("IIS.$id.Items");
			foreach($items as $it){
				$hgs=explode(":", $it);
				if($item==$hgs[0] and $esya->getDamage()==$hgs[1]){
					$p->sendPopup("$hgs[2]");
				}
			}
			}
		}
	}
