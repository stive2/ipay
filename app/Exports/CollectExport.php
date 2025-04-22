<?php

namespace App\Exports;

use App\Models\Admin\CollectOnoffLogs;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CollectExport implements FromArray, WithHeadings{

    public function headings(): array
    {
        return [
            ['SL', 'UTILISATEUR','ACTION','TIME'],
        ];
    }

    public function array(): array
    {
        return CollectOnoffLogs::get()->map(function($item,$key){
            return [
                'id'    => $key + 1,
                'utilisateur'  => $item->admin,
                'action'  => $item->action,
                'time'  =>   $item->created_at->format('d-m-y h:i:s'),
            ];
         })->toArray();

    }
}

