<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agence extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'editData',
    ];

    protected $casts = [
        'admin_id' => 'integer',
        'country' => 'string',
        'city' => 'string',
        'name' => 'string',
        'code' => 'string',
        'default' => 'integer',
        'status' => 'integer',
    ];

    public function getEditDataAttribute() {
        $data = [
            'name'      => $this->name,
            'code'      => $this->code,
            'city'      => $this->city,
            'option'    => ($this->default == true) ? 1 : 0,
            'country'   => $this->country,
        ];

        return json_encode($data);
    }


    public function scopeDefault() {
        return $this->where('default',true)->first() ?? false;
    }


    public function isDefault() {
        if($this->default == true) return true;
        return false;
    }

    public function scopeSearch($query,$text) {
        $query->where(function($q) use ($text) {
            $q->where("country","like","%".$text."%");
        })->orWhere("name","like","%".$text."%")->orWhere("code","like","%".$text."%");
    }

    public function scopeActive($query) {
        return $query->where("status",true);
    }

}

