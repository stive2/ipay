<?php

namespace App\Exports;

use App\Models\AgentProfit;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CommissionExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'REFERENCE','COLLECTE','COMMISSION','STATUT','DATE'],
        ];
    }

    public function array(): array
    {
        return AgentProfit::agentAuth()->get()->map(function($item,$key){
            if($item->paid == 0) $statut = 'Non perçu';
            else $statut = 'Perçu';

            return [
                'id'    => $key + 1,
                'reference'  => $item->transactions->trx_id,
                'collecte'  => $item->transactions->details->charges->conversion_amount??$item->transactions->details->charges->sender_amount,
                'commission'  => $item->total_charge,
                'statut'  => $statut,
                'time'  =>   $item->created_at->format('d-m-y h:i:s'),
            ];
         })->toArray();

    }
}

