<?php

namespace App\Exports;

use App\Constants\PaymentGatewayConst;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;

class AgentCollecte implements FromArray, WithHeadings
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return [
            ['SL', 'MATRICULE', 'AGENT', 'TELEPHONE', 'EMAIL', 'MONTANT', 'CURRENCY'],
        ];
    }

    public function array(): array
    {
        $query = Transaction::join('agents', 'agents.id', '=', 'transactions.agent_id')
        ->where('transactions.type', PaymentGatewayConst::MONEYIN)
        ->where('transactions.attribute', PaymentGatewayConst::SEND);

        // Filtrage par date unique (filter_date)
        if (!empty($this->filters['filter_date'])) {
            $query->whereDate('transactions.created_at', $this->filters['filter_date']);
        }

        $query->groupBy('agents.matricule','agents.username','agents.full_mobile', 'agents.id', 'agents.firstname', 'agents.lastname', 'agents.email') // Important pour certains DB stricts
        ->select('agents.matricule','agents.username','agents.full_mobile', 'agents.id', 'agents.firstname', 'agents.lastname', 'agents.email', DB::raw('SUM(transactions.request_amount) as montant'));

        return $query->get()->map(function ($item, $key) {

            return [
                'id'    => $key + 1,
                'matricule'  => $item->matricule,
                'agent'  => $item->firstname.' '.$item->lastname,
                'telephone'  => '+ '.$item->full_mobile,
                'email'  => $item->email,
                'amount'  =>  $item->montant,
                'currency' => get_default_currency_code(),
            ];
        })->toArray();
    }
}
