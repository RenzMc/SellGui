<?php

declare(strict_types=1);

namespace Renz;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;

class Main extends PluginBase {

    public function onEnable(): void {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
    }

    public function openSimpleMenu(Player $player): void {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName("§l§bSell Gui");

    $redShulker = $this->createShulker("§l§2Sell Hand", VanillaBlocks::SHULKER_BOX());
    $greenShulker = $this->createShulker("§l§4Sell All", VanillaBlocks::SHULKER_BOX());
    $yellowShulker = $this->createShulker("§l§6Sell Ores", VanillaBlocks::SHULKER_BOX());
    $blueShulker = $this->createShulker("§l§bSell Inv", VanillaBlocks::SHULKER_BOX());

        $blackGlass = $this->createGlass(TF::RESET, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK())->asItem());
        $redGlass = $this->createGlass(TF::RED . "EXIT", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem());
        $greenGlass = $this->createGlass(TF::GREEN . "Author", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem());
        $yellowGlass = $this->createGlass(TF::YELLOW . "Wiki", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::YELLOW())->asItem());

        $menu->getInventory()->setContents([
            0 => $blackGlass, 1 => $blackGlass, 2 => $blackGlass,
            3 => $blackGlass, 4 => $blackGlass, 5 => $blackGlass,
            6 => $blackGlass, 7 => $blackGlass, 8 => $blackGlass,
            9 => $redShulker, 10 => $greenShulker, 11 => $yellowShulker, 12 => $blueShulker,
            18 => $greenGlass, 19 => $blackGlass, 20 => $yellowGlass, 21 => $blackGlass,
            22 => $blackGlass, 23 => $blackGlass, 24 => $redGlass, 25 => $blackGlass,
            26 => $blackGlass
        ]);

        $menu->setListener(function (InvMenuTransaction $transaction) use ($player): InvMenuTransactionResult {
            $item = $transaction->getOut();
            $action = $item->getNamedTag()->getString("action", "");

            switch ($action) {
                case "sell hand":
                case "sell all":
                case "sell ores":
                case "sell inv":
                    $this->getServer()->dispatchCommand($player, $action);
                    $player->removeCurrentWindow();
                    break;
                default:
                    switch ($item->getCustomName()) {
                        case TF::YELLOW . "Wiki":
                            $player->sendMessage("§l§6Welcome To Wiki SellGUI\nKalian bisa change harga jual di Plugin sellAll\nPastikan pasang sellAll\n§l§4Thanks Don't Forget To\n§l§bSubscribe Renz-mc");
                            $player->removeCurrentWindow();
                            break;
                        case TF::RED . "EXIT":
                            $player->removeCurrentWindow();
                            break;
                        case TF::GREEN . "Author":
                            $player->sendMessage("§l§bPlugin created by Renz-mc");
                            $player->removeCurrentWindow();
                            break;
                    }
            }

            return $transaction->discard();
        });

        $menu->send($player);
    }

    private function createShulker(string $name, Item $item): Item {
    $shulkerBox = clone $item;
    $shulkerBox->setCustomName($name);

     return $shulkerBox;
}

    private function createGlass(string $name, Item $item): Item {
        $glass = clone $item;
        $glass->setCustomName($name);
        return $glass;
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "sellgui") {
            if (!$sender instanceof Player) {
                $sender->sendMessage("This command can only be used in-game.");
                return false;
            }

            if (!$sender->hasPermission("sellgui.command")) {
                $sender->sendMessage("You do not have permission to use this command.");
                return false;
            }

            $this->openSimpleMenu($sender);
            return true;
        }

        return false;
    }
}
