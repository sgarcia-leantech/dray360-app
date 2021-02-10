<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class VesselVerified
{
    use Dispatchable;
    use SerializesModels;

    public $orderData;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->orderData = $order->toArray();
    }
}
