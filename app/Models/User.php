<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia // HasMedia যোগ করুন
{
    use Notifiable, HasRoles, InteractsWithMedia, HasFactory;

    // InteractsWithMedia যোগ করুন

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'address',
        'nid_no',
        'joining_date',
        'status',
        'salary'
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'joining_date' => 'date',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Register media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('user_photo')->singleFile();
        $this->addMediaCollection('user_documents');
    }

    public function areas() // নাম পরিবর্তন করে প্লুরাল করা হলো
    {
        return $this->belongsToMany(Area::class, 'area_user');
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class);
    }
}
