<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use Notifiable; // trait, adds methods to User model so that users can receive notifications (email, database, SMS, etc.).

    // Table
    protected $table = 'user';
    // ID (PK)
    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'password',
        'role',
        'person_id',
        'last_login',
        'is_active',
        'user_image_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login' => 'datetime',
        'is_active' => 'boolean',
    ];
    
    // no timestamps
    public $timestamps = false;

    // Relationships:

    // User hasMany purchase
    public function userhasManypurchase()
    {
        return $this->hasMany(Purchase::class, 'created_by', 'user_id');
    }

    // User hasMany User_branch
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'user_branch', 'user_id', 'branch_id');
    }

    // User hasMany sale
    public function userhasManysale()
    {
        return $this->hasMany(Sale::class, 'created_by', 'user_id');
    }

    // User hasMany payment
    public function userhasManypayment()
    {
        return $this->hasMany(Payment::class, 'created_by', 'user_id');
    }

    // User profile
    public function userhasManyaudit_log()
    {
        return $this->hasMany(AuditLog::class, 'user_id', 'user_id');
    }

    // User hasMany person
    public function UserbelongsToperson()
    {
        return $this->belongsTo(Person::class, 'person_id', 'person_id');
    }

    // User hasMany stock_movement
    public function userhasManystock_movement()
    {
        return $this->hasMany(StockMovement::class, 'created_by', 'user_id');
    }

    public function files()
    {
        return $this->hasMany(File::class, 'user_id', 'user_id');
    }

}
