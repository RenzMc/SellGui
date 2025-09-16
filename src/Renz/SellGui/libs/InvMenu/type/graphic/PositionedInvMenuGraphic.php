<?php

declare(strict_types=1);

namespace Renz\SellGui\libs\InvMenu\type\graphic;

use pocketmine\math\Vector3;

interface PositionedInvMenuGraphic extends InvMenuGraphic{

	public function getPosition() : Vector3;
}
