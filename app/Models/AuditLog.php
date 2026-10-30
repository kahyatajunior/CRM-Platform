<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'model_type',
        'model_id',
        'event',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
