<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Foundation\Auth\User as Authenticatable;

class OrderPolicy
{
    public function view(Authenticatable $identity, Order $order): bool
    {
        return $this->canViewAnyOrder($identity)
            || ($identity instanceof Store && $identity->id === $order->store_id && $identity->can('orders.view'))
            || ($identity instanceof Customer && $identity->id === $order->customer_id)
            || ($identity instanceof Rider && $identity->id === $order->rider_id && $identity->can('orders.view'));
    }

    public function updateStatus(Authenticatable $identity, Order $order): bool
    {
        return $this->canUpdateAnyOrderStatus($identity)
            || ($identity instanceof Store && $identity->id === $order->store_id && $identity->can('orders.update-status'))
            || ($identity instanceof Rider && $identity->id === $order->rider_id && $identity->can('orders.update-status'));
    }

    private function canViewAnyOrder(Authenticatable $identity): bool
    {
        return $identity instanceof Admin && $identity->can('orders.view');
    }

    private function canUpdateAnyOrderStatus(Authenticatable $identity): bool
    {
        return $identity instanceof Admin && $identity->can('orders.update-status');
    }
}
