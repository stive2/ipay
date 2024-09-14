<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RibVerification extends Model
{
    use HasFactory;

    protected $table = "rib_verification_records";
    protected $guarded = ['id'];

    protected $fillable = [
        'rib',
        'response',
    ];

    protected $casts = [
        'response'  => 'object',
    ];
}
