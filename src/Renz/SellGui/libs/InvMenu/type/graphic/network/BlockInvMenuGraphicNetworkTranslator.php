<?php

declare(strict_types=1);

namespace Renz\SellGui\libs\InvMenu\type\graphic\network;

use InvalidArgumentException;
use Renz\SellGui\libs\InvMenu\session\InvMenuInfo;
use Renz\SellGui\libs\InvMenu\session\PlayerSession;
use Renz\SellGui\libs\InvMenu\type\graphic\PositionedInvMenuGraphic;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;

final class BlockInvMenuGraphicNetworkTranslator implements InvMenuGraphicNetworkTranslator{

	public static function instance() : self{
		static $instance = null;
		return $instance ??= new self();
	}

	private function __construct(){
	}

	public function translate(PlayerSession $session, InvMenuInfo $current, ContainerOpenPacket $packet) : void{
		$graphic = $current->graphic;
		$graphic instanceof PositionedInvMenuGraphic || throw new InvalidArgumentException("Expected " . PositionedInvMenuGraphic::class . ", got " . $graphic::class);
		$pos = $graphic->getPosition();
		$packet->blockPosition = new BlockPosition((int) $pos->x, (int) $pos->y, (int) $pos->z);
	}
}
