<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'role_id',
    ];

   
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
