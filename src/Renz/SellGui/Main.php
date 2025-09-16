<?php

declare(strict_types=1);

namespace Renz\SellGui;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Renz\SellGui\commands\SellGUICommand;
use Renz\SellGui\libs\InvMenu\InvMenuHandler;
use Renz\SellGui\utils\SellManager;

class Main extends PluginBase {
    /** @var SellManager */
    private $sellManager;

    public function onLoad(): void {
        $this->getLogger()->info(TF::WHITE . "SellGUI is loading...");
    }

    public function onEnable(): void {
        // Save default resources
        $this->saveDefaultConfig();
        $this->saveResource("messages.yml");
        $this->saveResource("settings.yml");
        
        // Initialize InvMenu
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        
        // Initialize SellManager
        $this->sellManager = new SellManager($this);
        
        // Register command - now using only the merged SellGUICommand
        $this->getServer()->getCommandMap()->register("sellgui", new SellGUICommand($this));
        
        $this->getLogger()->info(TF::GREEN . "SellGUI has been enabled!");
    }

    public function onDisable(): void {
        $this->getLogger()->info(TF::RED . "SellGUI has been disabled!");
    }
    
    /**
     * Get the SellManager instance
     * 
     * @return SellManager
     */
    public function getSellManager(): SellManager {
        return $this->sellManager;
    }
}