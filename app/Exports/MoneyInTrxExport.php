<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MoneyInTrxExport implements FromArray, WithHeadings
{

    public function headings(): array
    {
        return [
            ['SL', 'REF', 'COLLECTEUR', 'CLIENT', 'MONTANT', 'TOTAL CHARGES', 'COMMISSIONS', 'PROFITS', 'MONTANT TOTAL', 'STATUS',  'DATE'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', PaymentGatewayConst::MONEYIN)->where('attribute', PaymentGatewayConst::SEND)->latest()->get()->map(function ($item, $key) {
            return [
                'id'    => $key + 1,
                'ref'   => $item->trx_id,
                'collecteur'  => $item->creator->fullname,
                'client'  => $item->details->receiver_name,
                'montant'  => get_amount($item->details->charges->receiver_amount, null, 2),
                'total_charges'  =>  get_amount($item->details->charges->total_charge, null, 2),
                'commissions'  =>  get_amount($item->details->charges->agent_total_commission, null, 2),
                'profits'  =>  get_amount(($item->details->charges->total_charge - $item->details->charges->agent_total_commission), null, 2),
                'total'  => get_amount($item->details->charges->payable, null, 2),
                'status'  => __($item->stringStatus->value),
                'date'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
        })->toArray();
    }
}
