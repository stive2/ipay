<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soldes extends Model
{
    protected $fillable = [
        'solde',
        'fullname',
        'compte',
        'matricule',
        'date',
        'notifie',
    ];

    protected $casts = [
        'date'           => 'datetime',
        'notifie'       => 'boolean',
    ];
}
