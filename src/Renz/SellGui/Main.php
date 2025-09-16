<?php

declare(strict_types=1);

namespace Renz\SellGui;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use Renz\SellGui\commands\SellGUICommand;
use Renz\SellGui\libs\InvMenu\InvMenuHandler;
use Renz\SellGui\utils\SellManager;

class Main extends PluginBase {
    /** @var SellManager */
    private SellManager $sellManager;

    /**
     * Called when the plugin is loaded
     */
    public function onLoad(): void {
        $this->getLogger()->info(TF::WHITE . "SellGUI is loading...");
    }

    /**
     * Called when the plugin is enabled
     */
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

    /**
     * Called when the plugin is disabled
     */
    public function onDisable(): void {
        $this->getLogger()->info(TF::RED . "SellGUI has been disabled!");
    }
    
    /**
     * Get the SellManager instance
     * 
     * @return SellManager The SellManager instance
     */
    public function getSellManager(): SellManager {
        return $this->sellManager;
    }
}