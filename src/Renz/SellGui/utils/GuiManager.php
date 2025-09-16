<?php

declare(strict_types=1);

namespace Renz\SellGui\utils;

use pocketmine\block\VanillaBlocks;
use pocketmine\block\utils\DyeColor;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use Renz\SellGui\libs\InvMenu\InvMenu;
use Renz\SellGui\libs\InvMenu\transaction\InvMenuTransaction;
use Renz\SellGui\libs\InvMenu\transaction\InvMenuTransactionResult;

class GuiManager {
    /**
     * Open the sell GUI for a player
     * 
     * @param Player $player
     */
    public static function openSellGui(Player $player): void {
        $menu = InvMenu::create(InvMenu::TYPE_CHEST);
        $menu->setName(TF::BOLD . TF::AQUA . "Sell Gui");

        $redShulker = self::createShulker(TF::BOLD . TF::DARK_GREEN . "Sell Hand", VanillaBlocks::SHULKER_BOX());
        $greenShulker = self::createShulker(TF::BOLD . TF::DARK_RED . "Sell All", VanillaBlocks::SHULKER_BOX());
        $yellowShulker = self::createShulker(TF::BOLD . TF::GOLD . "Sell Ores", VanillaBlocks::SHULKER_BOX());
        $blueShulker = self::createShulker(TF::BOLD . TF::AQUA . "Sell Inv", VanillaBlocks::SHULKER_BOX());

        $blackGlass = self::createGlass(TF::RESET, VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::BLACK())->asItem());
        $redGlass = self::createGlass(TF::RED . "EXIT", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::RED())->asItem());
        $greenGlass = self::createGlass(TF::GREEN . "Author", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GREEN())->asItem());
        $yellowGlass = self::createGlass(TF::YELLOW . "Wiki", VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::YELLOW())->asItem());

        // Set up the item actions
        $redShulker->getNamedTag()->setString("action", "sell hand");
        $greenShulker->getNamedTag()->setString("action", "sell all");
        $yellowShulker->getNamedTag()->setString("action", "sell ores");
        $blueShulker->getNamedTag()->setString("action", "sell inv");

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
                    $player->getServer()->dispatchCommand($player, $action);
                    $player->removeCurrentWindow();
                    break;
                default:
                    switch ($item->getCustomName()) {
                        case TF::YELLOW . "Wiki":
                            $player->sendMessage(TF::BOLD . TF::GOLD . "Welcome To Wiki SellGUI\nKalian bisa change harga jual di Plugin sellAll\nPastikan pasang sellAll\n" . TF::BOLD . TF::DARK_RED . "Thanks Don't Forget To\n" . TF::BOLD . TF::AQUA . "Subscribe Renz-mc");
                            $player->removeCurrentWindow();
                            break;
                        case TF::RED . "EXIT":
                            $player->removeCurrentWindow();
                            break;
                        case TF::GREEN . "Author":
                            $player->sendMessage(TF::BOLD . TF::AQUA . "Plugin created by Renz-mc");
                            $player->removeCurrentWindow();
                            break;
                    }
            }

            return $transaction->discard();
        });

        $menu->send($player);
    }

    /**
     * Create a shulker box item with custom name
     * 
     * @param string $name
     * @param Item $item
     * @return Item
     */
    private static function createShulker(string $name, Item $item): Item {
        $shulkerBox = clone $item;
        $shulkerBox->setCustomName($name);
        return $shulkerBox;
    }

    /**
     * Create a glass pane item with custom name
     * 
     * @param string $name
     * @param Item $item
     * @return Item
     */
    private static function createGlass(string $name, Item $item): Item {
        $glass = clone $item;
        $glass->setCustomName($name);
        return $glass;
    }
}