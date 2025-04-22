<?php

namespace App\Exports;

use App\Models\Agent;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgentExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'PRENOM','NOM','EMAIL','MATRICULE',"TEL",'STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return Agent::get()->map(function($item,$key){
            if($item->status == 1){
                $status =  "Actif";
            }else{
                $status =  "Inactif";
            }

            return [
                'id'    => $key + 1,
                'prenom'  => $item->firstname,
                'nom'  => $item->lastname,
                'email'  =>  $item->email,
                'matricule'  =>  $item->matricule,
                'tel'  => '+ '.$item->full_mobile,
                'status'  => $status,
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

