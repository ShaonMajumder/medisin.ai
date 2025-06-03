<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medicine extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'generic_name',
        'brand_names',
        'uses',
        'dosage',
        'side_effects',
        'precautions',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
