<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MoneyOutRejected implements FromArray, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            ['SL', 'REF', 'CLIENT', 'MATRICULE', 'COMPTE', 'AMOUNT', 'CURRENCY', 'STATUS', 'TIME'],
        ];
    }

    public function array(): array
    {
        $query = Transaction::query()->where('type', PaymentGatewayConst::TYPEMONEYOUT)
        ->where('status', 4);

        // Filtrage par date unique (filter_date)
        if (!empty($this->filters['filter_date'])) {
            $query->whereDate('created_at', $this->filters['filter_date']);
        }

        // Filtrage par agent (en général l'agent est relié via user_id ou creator_id)
        if (!empty($this->filters['agent_id'])) {
            $query->where('agent_id', $this->filters['agent_id']);
        }

        return $query->latest()->get()->map(function ($item, $key) {

            return [
                'id'    => $key + 1,
                'ref'  => $item->trx_id,
                'client'  => $item->creator->fullname,
                'matricule'  => $item->creator->matricule,
                // 'telephone'  => $item->creator->full_mobile,
                // 'email'  => $item->creator->email,
                'rib' => $item->creator->rib,
                'amount'  =>  $item->request_amount,
                'currency' => get_default_currency_code(), // get_amount(, get_default_currency_code(), 4),
                'status'  => __($item->stringStatus->value),
                'time'  =>   $item->created_at->format('d-m-y h:i:s A'),
            ];
        })->toArray();
    }
}
