<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    use HasFactory;

    protected $fillable = [
        'token',
        'secret',
        'views',
        'max_views',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'views' => 'integer',
        'max_views' => 'integer',
    ];


    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }


    public function hasReachedMaxViews(): bool
    {
        return $this->max_views !== null && $this->views >= $this->max_views;
    }


    public function isAccessible(): bool
    {
        return !$this->isExpired() && !$this->hasReachedMaxViews();
    }
}
