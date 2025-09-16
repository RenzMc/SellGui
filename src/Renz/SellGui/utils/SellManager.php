<?php

declare(strict_types=1);

namespace Renz\SellGui\utils;

use cooldogedev\BedrockEconomy\api\BedrockEconomyAPI;
use onebone\economyapi\EconomyAPI;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Renz\SellGui\Main;

class SellManager {
    /** @var Main */
    private $plugin;
    
    /** @var array */
    private $config;
    
    /** @var array */
    private $messages;
    
    /** @var array */
    private $settings;
    
    /** @var Config */
    private $msgFile;
    
    /** @var Config */
    private $settingsFile;

    const CFGVERSION = 1.3;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadResources();
    }

    /**
     * Load all necessary resources
     */
    private function loadResources(): void {
        $this->config = $this->plugin->getConfig()->getAll();
        $this->msgFile = new Config($this->plugin->getDataFolder() . "messages.yml", Config::YAML, []);
        $this->messages = $this->msgFile->getAll();
        $this->settingsFile = new Config($this->plugin->getDataFolder() . "settings.yml", Config::YAML, []);
        $this->settings = $this->settingsFile->getAll();
    }

    /**
     * Reload all resources
     */
    public function reload(): void {
        $this->plugin->reloadConfig();
        $this->config = $this->plugin->getConfig()->getAll();
        $this->msgFile = new Config($this->plugin->getDataFolder() . "messages.yml", Config::YAML, []);
        $this->messages = $this->msgFile->getAll();
        $this->settingsFile = new Config($this->plugin->getDataFolder() . "settings.yml", Config::YAML, []);
        $this->settings = $this->settingsFile->getAll();
        
        $this->validateConfig();
    }

    /**
     * Validate configuration versions
     */
    private function validateConfig(): void {
        if (!isset($this->config["cfgversion"])) {
            $this->plugin->getLogger()->critical("Config version outdated! Please regenerate your config or this plugin might not work correctly.");
        } elseif ($this->config["cfgversion"] != self::CFGVERSION) {
            $this->plugin->getLogger()->critical("Config version outdated! Please regenerate your config or this plugin might not work correctly.");
        }
        
        if (!isset($this->messages["cfgversion"])) {
            $this->plugin->getLogger()->critical("Messages version outdated! Please regenerate your messages file or this plugin might not work correctly.");
        } elseif ($this->messages["cfgversion"] != self::CFGVERSION) {
            $this->plugin->getLogger()->critical("Messages version outdated! Please regenerate messages file config or this plugin might not work correctly.");
        }
        
        if (!isset($this->settings["cfgversion"])) {
            $this->plugin->getLogger()->critical("Settings version outdated! Please regenerate your settings file or this plugin might not work correctly.");
        } elseif ($this->settings["cfgversion"] != self::CFGVERSION) {
            $this->plugin->getLogger()->critical("Settings version outdated! Please regenerate settings file config or this plugin might not work correctly.");
        }
    }

    /**
     * Sell item in hand
     * 
     * @param Player $player
     * @return bool
     */
    public function sellHand(Player $player): bool {
        $item = $player->getInventory()->getItemInHand();
        if ($this->isSellable($item)) {
            $price = $this->getPrice($item);
            $count = $item->getCount();
            $totalPrice = $price * $count;
            $this->addMoney($player->getName(), (int) $totalPrice);
            $item->setCount($item->getCount() - (int) $count);
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage(TextFormat::colorize($this->replaceVars($this->messages["success.sell"], [
                "AMOUNT" => (string) $count,
                "ITEMNAME" => $item->getName(),
                "MONEY" => (string) $totalPrice
            ])));
            return true;
        }
        
        $player->sendMessage(TextFormat::colorize($this->messages["error.not-found"]));
        return false;
    }

    /**
     * Sell all items in inventory
     * 
     * @param Player $player
     * @return bool
     */
    public function sellAll(Player $player): bool {
        $inv = $player->getInventory()->getContents();
        $revenue = 0;
        
        foreach ($inv as $item) {
            if ($this->isSellable($item)) {
                $revenue = $revenue + ($item->getCount() * $this->getPrice($item));
                $player->getInventory()->remove($item);
            }
        }
        
        if ($revenue <= 0) {
            $player->sendMessage(TextFormat::colorize($this->messages["error.no.sellables"]));
            return false;
        }
        
        $this->addMoney($player->getName(), (int) $revenue);
        $player->sendMessage(TextFormat::colorize($this->replaceVars($this->messages["success.sell.inventory"], [
            "MONEY" => (string) $revenue
        ])));
        
        return true;
    }

    /**
     * Sell specific group of items
     * 
     * @param Player $player
     * @param string $group
     * @return bool
     */
    public function sellGroup(Player $player, string $group): bool {
        if (!array_key_exists($group, $this->config["groups"])) {
            return false;
        }
        
        $groupConfig = $this->config["groups"][$group];
        $inv = $player->getInventory()->getContents();
        $revenue = 0;
        
        foreach ($inv as $item) {
            if ($this->getPrice($item)) {
                if (in_array($this->getItemId($item), $groupConfig["items"]) || in_array($item->getName(), $groupConfig["items"])) {
                    $revenue = $revenue + ($item->getCount() * $this->getPrice($item));
                    $player->getInventory()->remove($item);
                }
            }
        }
        
        if ($revenue <= 0) {
            $player->sendMessage(TextFormat::colorize($groupConfig["failed"]));
            return false;
        }
        
        $this->addMoney($player->getName(), (int) $revenue);
        $player->sendMessage(TextFormat::colorize($this->replaceVars($groupConfig["success"], [
            "MONEY" => (string) $revenue
        ])));
        
        return true;
    }

    /**
     * Check if an item is sellable
     * 
     * @param Item $item
     * @return bool
     */
    public function isSellable(Item $item): bool {
        return $this->getPrice($item) !== false;
    }

    /**
     * Get the price of an item
     * 
     * @param Item $item
     * @return mixed
     */
    public function getPrice(Item $item) {
        foreach ($this->config as $id => $meta) {
            if (!is_array($meta)) {
                if (strpos(strval($id), ":") !== false) {
                    $newId = (int) explode(":", $id)[0];
                    $newMeta = (int) explode(":", $id)[1];
                    $newItem = null;
                    try {
                        $newItem = LegacyStringToItemParser::getInstance()->parse($newId . ":" . $newMeta);
                    } catch (LegacyStringToItemParserException $e) {
                        echo $e->getMessage();
                    }
                } else {
                    $newItem = null;
                    try {
                        $newItem = LegacyStringToItemParser::getInstance()->parse($id . ":0");
                    } catch (LegacyStringToItemParserException $e) {
                        echo $e->getMessage();
                    }
                }
                if ($newItem !== null && $newItem->equals($item)) {
                    return (int) $meta;
                }
            }
        }
        return false;
    }

    /**
     * Get item ID for comparison
     * 
     * @param Item $item
     * @return string
     */
    private function getItemId(Item $item): string {
        return $item->getTypeId() . ":" . $item->getStateId();
    }

    /**
     * Add money to player
     * 
     * @param string $player
     * @param int $amount
     */
    public function addMoney(string $player, int $amount): void {
        if ($this->settings["economy.provider"] === "EconomyAPI") {
            EconomyAPI::getInstance()->addMoney($player, $amount);
        } elseif ($this->settings["economy.provider"] === "BedrockEconomy") {
            BedrockEconomyAPI::getInstance()->addToPlayerBalance($player, (int) ceil($amount));
        }
    }

    /**
     * Replace variables in a string
     * 
     * @param string $str
     * @param array $vars
     * @return string
     */
    public function replaceVars(string $str, array $vars): string {
        foreach ($vars as $key => $value) {
            $str = str_replace("{" . $key . "}", $value, $str);
        }
        return $str;
    }

    /**
     * Get list of available arguments
     * 
     * @return string
     */
    public function listArguments(): string {
        $separator = $this->messages["separator"];
        $args = "hand" . $separator . "all" . $separator . "inv";
        foreach ($this->config["groups"] as $name => $group) {
            $args = $args . $separator . $name;
        }
        return $args;
    }

    /**
     * Get the messages array
     * 
     * @return array
     */
    public function getMessages(): array {
        return $this->messages;
    }

    /**
     * Get the config array
     * 
     * @return array
     */
    public function getConfig(): array {
        return $this->config;
    }
}