<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MoneyOutCBSPendingExport implements FromArray, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            ['Compte', 'Libelle', 'Reference', 'Debit', 'Credit','Ref. Lettrage'],
        ];
    }

    public function array(): array
    {
        $query = Transaction::query()
            ->where('type', PaymentGatewayConst::TYPEMONEYOUT)
            ->where('status', 1)
            ->where('cbsTransfert', 'ND');

        // Filtrage par date unique (filter_date)
        if (!empty($this->filters['filter_date'])) {
            $query->whereDate('created_at', $this->filters['filter_date']);
        }

        // Filtrage par agent (en général l'agent est relié via user_id ou creator_id)
        if (!empty($this->filters['agent_id']) && $this->filters['agent_id'] != 0) {
            $query->where('agent_id', $this->filters['agent_id']);
        }

        return $query->latest()->get()->map(function ($item, $key) {
            return [
                'compte'       => $item->creator->rib . ' ',
                'libelle'      => 'Collecte journalière de ' . $item->creator->fullname,
                'reference'    => $item->trx_id,
                'debit'        => '0',
                'credit'       => $item->request_amount ?? '0',
                'ref_lettrage' => $item->trx_id,
            ];
        })->toArray();
    }

}
