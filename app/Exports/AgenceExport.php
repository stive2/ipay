<?php

namespace App\Exports;

use App\Models\Admin\Agence;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgenceExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'NOM','CODE','PAYS','VILLE','PRINCIPALE'],
        ];
    }

    public function array(): array
    {
        return Agence::get()->map(function($item,$key){
            if($item->default == 1){
                $default =  "OUI";
            }else{
                $default =  "NON";
            }

            return [
                'id'    => $key + 1,
                'nom'  => $item->name,
                'code'  =>  $item->code,
                'pays'  =>  $item->country,
                'ville'  => $item->city,
                'principale'  => $default,
            ];
         })->toArray();

    }
}

