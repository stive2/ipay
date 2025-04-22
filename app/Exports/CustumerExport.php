<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CustumerExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'PRENOM','NOM','EMAIL','MATRICULE',"TEL",'COMPTE','TYPE','STATUS','TIME'],
        ];
    }

    public function array(): array
    {
        return User::get()->map(function($item,$key){
            if($item->transitional == 1){
                $account_type =  "Compte de collecte";
            }else{
                $account_type =  "Compte classic";
            }

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
                'num_compte'  => $item->rib . ' ',
                'type'  =>  $account_type,
                'status'  => $status,
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

