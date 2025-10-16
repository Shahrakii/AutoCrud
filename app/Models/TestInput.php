<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestInput extends Model
{
    use HasFactory;

    protected $table = 'test_inputs'; // table name

    // Allow mass assignment for all columns except id, timestamps
    protected $guarded = ['id', 'created_at', 'updated_at'];

    // Optional: cast certain columns
    protected $casts = [
        'is_active' => 'boolean',
        'email_verified' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Optional: relationships if you have foreign keys
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Example of accessor for full name
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}
