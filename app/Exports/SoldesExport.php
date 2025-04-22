<?php

namespace App\Exports;

use App\Models\Soldes;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SoldesExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'MATRICULE','NOMS','COMPTE','SOLDE',"DATE"],
        ];
    }

    public function array(): array
    {
        return Soldes::get()->map(function($item,$key){
            if($item->status == 1){
                $status =  "Actif";
            }else{
                $status =  "Inactif";
            }

            return [
                'id'    => $key + 1,
                'matricule'  => $item->matricule,
                'noms'  => $item->fullname,
                'compte'  =>  $item->compte.' ',
                'solde'  =>  $item->solde,
                'date'  =>   $item->date->format('d-m-y'),
            ];
         })->toArray();

    }
}

