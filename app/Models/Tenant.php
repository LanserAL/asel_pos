<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo_path', 'description', 'status', 'plan_capacity', 'currencies', 'expires_at',
        'ai_provider', 'ai_api_key', 'ai_model'
    ];

    protected $casts = [
        'plan_capacity' => 'array',
        'currencies' => 'array',
        'expires_at' => 'datetime',
        'ai_api_key' => 'encrypted',
    ];

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function providers()
    {
        return $this->hasMany(Provider::class);
    }
}
