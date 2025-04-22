<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AdminRechargeLogs implements FromArray, WithHeadings
{

    public function headings(): array
    {
        return [
            ['SL', 'TRX', 'FULL NAME', 'MATRICULE', "EMAIL", 'NATURE', 'AMOUNT', 'ADMIN', 'STATUS', 'DATE'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'agent:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', PaymentGatewayConst::TYPEADDSUBTRACTBALANCE)->latest()->get()->map(function ($item, $key) {
            if ($item->attribute == 'SEND') {
                $montant = (-1) * $item->request_amount;
                $nature = 'RETRAIT';
            } else  {
                $montant = $item->request_amount;
                $nature = 'RECHARGE';
            }
            return [
                'id'    => $key + 1,
                'trx'  => $item->trx_id,
                'full_name'  => $item->creator->fullname,
                'matricule'  =>  $item->creator->matricule,
                'email'  => $item->creator->email,
                'amount'  =>  get_amount($montant, null, 4),
                'nature'    => $nature,
                'admin' => strtoupper($item->admin()->firstname . ' ' . $item->admin()->lastname),
                'status'  => __($item->stringStatus->value),
                'date'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
        })->toArray();
    }
}
