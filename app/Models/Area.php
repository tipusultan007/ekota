<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    /**
     * Get the members for the area.
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    /**
     * Get the field workers for the area.
     */
    public function fieldWorkers()
    {
        return $this->hasMany(User::class);
    }

    public function users() // নাম পরিবর্তন করে users করা হলো
    {
        return $this->belongsToMany(User::class, 'area_user');
    }
}
