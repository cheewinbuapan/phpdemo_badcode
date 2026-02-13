<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'order_number',
        'user_id',
        'status_id',
        'shipping_address',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    /**
     * Boot the model and register events
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate order_number when creating
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-' . time() . '-' . rand(1000, 9999);
            }
        });
    }

    /**
     * Get the user that owns the order
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the status of this order
     */
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'status_id', 'status_id');
    }

    /**
     * Get order details (line items)
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    /**
     * Scope for pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status_id', ProductStatus::PENDING);
    }

    /**
     * Scope for confirmed orders
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status_id', ProductStatus::CONFIRMED);
    }
}
