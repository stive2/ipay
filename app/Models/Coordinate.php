<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coordinate extends Model
{
    use HasFactory;
    protected $fillable = ['latitude', 'longitude', 'agent_id'];

    public function agent(){
        return $this->belongsTo(Agent::class);
    }
}
