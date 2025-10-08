<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Forecast extends Model
{
    // Table name
    protected $table = 'forecast';
    // ID (PK)
    protected $primaryKey = 'forecast_id';

    // Fillable fields for mass assignment
    protected $fillable = [
        'branch_product_id',
        'period_start',
        'period_end',
        'forecast_qty',
        'method',
        'notes',
    ];

    // no timestamps
    public $timestamps = false;

    // Casts for proper data types
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'forecast_qty' => 'integer',
        'created_at' => 'datetime',
    ];

    // Relationships

    // forecast belongsTo branch_product
    public function forecastbelongsTobranch_product()
    {
        return $this->belongsTo(BranchProduct::class, 'branch_product_id', 'branch_product_id');
    }

}
