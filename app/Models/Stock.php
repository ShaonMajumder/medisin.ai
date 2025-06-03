<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
     use SoftDeletes;

    protected $fillable = [
        'generic_name',
        'brand_names',
        'uses',
        'dosage',
        'side_effects',
        'precautions',
        'shopkeeper_id',
        'quantity',
    ];

    public function shopkeeper()
    {
        return $this->belongsTo(User::class, 'shopkeeper_id');
    }
}
