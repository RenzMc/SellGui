<?php

declare(strict_types=1);

namespace Renz\SellGui\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;
use Renz\SellGui\Main;
use Renz\SellGui\utils\GuiManager;

class SellGUICommand extends Command implements PluginOwned {
    /** @var Main */
    private Main $plugin;

    /**
     * SellGUICommand constructor.
     * 
     * @param Main $plugin The main plugin instance
     */
    public function __construct(Main $plugin) {
        parent::__construct("sellgui", "Sell items in your inventory", "/sellgui [hand|all|inv|ores]", ["sell", "sellmenu"]);
        $this->setPermission("sellgui.command");
        $this->plugin = $plugin;
    }

    /**
     * Execute the command
     * 
     * @param CommandSender $sender The command sender
     * @param string $commandLabel The command label used
     * @param array $args The command arguments
     * @return bool Whether the command executed successfully
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::colorize("&cPlease execute this command in-game"));
            return true;
        }

        if (!$this->testPermission($sender)) {
            return false;
        }

        $sellManager = $this->plugin->getSellManager();
        $messages = $sellManager->getMessages();
        $commandName = strtolower($commandLabel);

        // Handle commands with no arguments
        if (empty($args)) {
            // Open GUI for any command with no arguments
            GuiManager::openSellGui($sender);
            return true;
        }

        // Handle commands with arguments
        $subCommand = strtolower($args[0]);
        
        switch ($subCommand) {
            case "hand":
                $sellManager->sellHand($sender);
                return true;

            case "inv":
            case "inventory":
            case "all":
                $sellManager->sellAll($sender);
                return true;

            case "reload":
                if ($sender->hasPermission("sellgui.reload")) {
                    $sellManager->reload();
                    $sender->sendMessage(TextFormat::colorize($messages["reload"]));
                } else {
                    $this->sendArgumentError($sender, $sellManager, $messages);
                }
                return true;

            default:
                // Check if it's a group
                if ($sellManager->sellGroup($sender, $args[0])) {
                    return true;
                }
                
                $this->sendArgumentError($sender, $sellManager, $messages);
                return true;
        }
    }

    /**
     * Send argument error message to the player
     * 
     * @param Player $player The player to send the message to
     * @param mixed $sellManager The sell manager instance
     * @param array $messages The messages array
     * @return void
     */
    private function sendArgumentError(Player $player, $sellManager, array $messages): void {
        $player->sendMessage(TextFormat::colorize($sellManager->replaceVars($messages["error.argument"], [
            "ARGS" => $sellManager->listArguments()
        ])));
    }

    /**
     * Get the plugin that owns this command
     * 
     * @return Plugin The owning plugin
     */
    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}