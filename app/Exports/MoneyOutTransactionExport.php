<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MoneyOutTransactionExport implements FromArray, WithHeadings
{

    public function headings(): array
    {
        return [
            ['SL', 'TRX', 'FULL NAME', 'RIB', 'USER TYPE', 'USER ID', 'AMOUNT', 'CURRENCY', 'CBS TRANSFERT', 'METHOD', 'STATUS', 'TIME'],
        ];
    }

    public function array(): array
    {
        return Transaction::with(
            'user:id,firstname,lastname,email,username,full_mobile,rib',
            'currency:id,name',
        )->where('type', PaymentGatewayConst::TYPEMONEYOUT)->latest()->get()->map(function ($item, $key) {
            if ($item->user_id != null) {
                $user_type =  "USER" ?? "";
            } elseif ($item->agent_id != null) {
                $user_type =  "AGENT" ?? "";
            } elseif ($item->merchant_id != null) {
                $user_type =  "MERCHANT" ?? "";
            }
            return [
                'id'    => $key + 1,
                'trx'  => $item->trx_id,
                'full_name'  => $item->creator->fullname,
                'rib' => $item->user->rib,
                'user_type'  => $user_type,
                'user_id'  => $item->creator->email,
                'amount'  =>  $item->request_amount,
                'currncy' => get_default_currency_code(), // get_amount(, get_default_currency_code(), 4),
                'cbsTransfert'  => $item->cbsTransfert,
                'method'  =>  @$item->currency->name,
                'status'  => __($item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
        })->toArray();
    }
}
