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
    private $plugin;

    public function __construct(Main $plugin) {
        parent::__construct("sellgui", "Sell items in your inventory", "/sellgui [hand|all|inv|ores]", ["sell", "sellmenu"]);
        $this->setPermission("sellgui.command");
        $this->plugin = $plugin;
    }

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

        // If command is "sellgui" or "sellmenu" with no args, open the GUI
        if ((strtolower($commandLabel) === "sellgui" || strtolower($commandLabel) === "sellmenu") && empty($args)) {
            GuiManager::openSellGui($sender);
            return true;
        }

        // If command is "sell" with no args, open the GUI
        if (strtolower($commandLabel) === "sell" && empty($args)) {
            GuiManager::openSellGui($sender);
            return true;
        }

        // Handle sell command arguments
        if (!empty($args)) {
            switch (strtolower($args[0])) {
                case "hand":
                    $sellManager->sellHand($sender);
                    return true;

                case "inv":
                case "inventory":
                case "all":
                    // Ensure sellAll works correctly by directly calling the method
                    $sellManager->sellAll($sender);
                    return true;

                case "reload":
                    if ($sender->hasPermission("sellgui.reload")) {
                        $sellManager->reload();
                        $sender->sendMessage(TextFormat::colorize($messages["reload"]));
                    } else {
                        $sender->sendMessage(TextFormat::colorize($sellManager->replaceVars($messages["error.argument"], [
                            "ARGS" => $sellManager->listArguments()
                        ])));
                    }
                    return true;

                default:
                    // Check if it's a group
                    if ($sellManager->sellGroup($sender, $args[0])) {
                        return true;
                    }
                    
                    $sender->sendMessage(TextFormat::colorize($sellManager->replaceVars($messages["error.argument"], [
                        "ARGS" => $sellManager->listArguments()
                    ])));
                    return true;
            }
        }

        // If we reach here, open the GUI as a fallback
        GuiManager::openSellGui($sender);
        return true;
    }

    public function getOwningPlugin(): Plugin {
        return $this->plugin;
    }
}