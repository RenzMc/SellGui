<?php

declare(strict_types=1);

namespace Renz\SellGui\libs\InvMenu\session\network\handler;

use Closure;
use Renz\SellGui\libs\InvMenu\session\network\NetworkStackLatencyEntry;

interface PlayerNetworkHandler{

	public function createNetworkStackLatencyEntry(Closure $then) : NetworkStackLatencyEntry;
}
