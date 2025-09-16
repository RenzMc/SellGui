<?php

declare(strict_types=1);

namespace Renz\SellGui\libs\InvMenu\session;

use Renz\SellGui\libs\InvMenu\InvMenu;
use Renz\SellGui\libs\InvMenu\type\graphic\InvMenuGraphic;

final class InvMenuInfo{

	public function __construct(
		readonly public InvMenu $menu,
		readonly public InvMenuGraphic $graphic,
		readonly public ?string $graphic_name
	){}
}
