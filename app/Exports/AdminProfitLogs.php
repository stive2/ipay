<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminProfitLogs implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'REF','AGENT','MATRICULE','CHARGE TOTALE','COMMISSIONS AGENT', 'PROFIT PLATEFORME', 'DATE'],
        ];
    }

    public function array(): array
    {
        return TransactionCharge::with('transactions')
        ->where('total_charge', '>', 0)
        ->whereHas('transactions', function ($query) {
            $query->whereNotIn('type', [PaymentGatewayConst::TYPEADDMONEY, PaymentGatewayConst::TYPEMONEYOUT,PaymentGatewayConst::TYPEADDSUBTRACTBALANCE]);
        })
        ->latest()->get()->map(function($item,$key){
            return [
                'id'    => $key + 1,
                'ref'  => $item->transactions->trx_id,
                'agent'  =>@$item->transactions->creator->fullname,
                'matricule'  =>@$item->transactions->creator->matricule,
                'charge_totale'  => get_amount($item->total_charge,null,4),
                'commissions_agent'  =>  get_amount(@$item->transactions->details->charges->agent_total_commission,null,4),
                'profit_plateforme'  =>  get_amount((@$item->total_charge - @$item->transactions->details->charges->agent_total_commission),null,4),
                'date'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
         })->toArray();

    }
}

