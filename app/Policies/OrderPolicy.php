<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\ProductStatus;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view the order
     */
    public function view(User $user, Order $order): bool
    {
        // User can view their own orders, admins can view all
        return $user->user_id === $order->user_id || $user->is_admin;
    }

    /**
     * Determine if the user can update the order
     */
    public function update(User $user, Order $order): bool
    {
        // Only owner can update, and only if order is still pending
        return $user->user_id === $order->user_id 
            && $order->status_id === ProductStatus::PENDING;
    }

    /**
     * Determine if the user can confirm the order
     */
    public function confirm(User $user, Order $order): bool
    {
        // Only owner can confirm, and only if order is still pending
        return $user->user_id === $order->user_id 
            && $order->status_id === ProductStatus::PENDING;
    }

    /**
     * Determine if the user can delete the order
     */
    public function delete(User $user, Order $order): bool
    {
        // Only owner can delete, and only if order is still pending
        return $user->user_id === $order->user_id 
            && $order->status_id === ProductStatus::PENDING;
    }
}
