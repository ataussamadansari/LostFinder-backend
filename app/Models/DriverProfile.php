<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverProfile extends Model
{
    protected $fillable = [
        'user_id', 'vehicle_number', 'vehicle_type', 'vehicle_model'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
