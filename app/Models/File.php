<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    // Specify the table name
    protected $table = 'files';

    // Primary key
    protected $primaryKey = 'file_id';

    // Allow mass assignment for these fields
    protected $fillable = [
        'user_id',
        'filename',
        'file_url',
        'file_type',
        'file_size',
    ];

    // Enable timestamps
    public $timestamps = true;

    /**
     * Relationship: Each file belongs to one user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
