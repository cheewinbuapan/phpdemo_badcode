<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductStatus extends Model
{
    protected $table = 'product_status';
    protected $primaryKey = 'status_id';
    public $timestamps = false;

    const PENDING = 1;
    const CONFIRMED = 2;

    protected $fillable = [
        'status_code',
        'status_name',
    ];

    /**
     * Get orders with this status
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'status_id', 'status_id');
    }
}
