<?php

namespace App\Imports;

use App\Models\Admin\Agence;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class AgenceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalizedRow = array_map('trim', $row);
        $normalizedRow = array_change_key_case($normalizedRow, CASE_UPPER);

        return new Agence([
            'country' => $normalizedRow['PAYS'],
            'name' => $normalizedRow['NOM'],
            'code' => $normalizedRow['CODE'],
            'city' => $normalizedRow['VILLE'],
            'admin_id' => 1,
            'default' => 0,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
