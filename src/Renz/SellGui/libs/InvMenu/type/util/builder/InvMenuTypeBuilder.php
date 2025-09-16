<?php

declare(strict_types=1);

namespace Renz\SellGui\libs\InvMenu\type\util\builder;

use Renz\SellGui\libs\InvMenu\type\InvMenuType;

interface InvMenuTypeBuilder{

	public function build() : InvMenuType;
}
