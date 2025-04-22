<?php

namespace App\Imports;

use App\Models\Soldes;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class SoldeImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalizedRow = array_map('trim', $row);
        $normalizedRow = array_change_key_case($normalizedRow, CASE_UPPER);

        User::where('matricule', $normalizedRow['MATRICULE'])
            ->where('rib', $normalizedRow['COMPTE'])->update([
            'solde_cbs' => $normalizedRow['SOLDE'],
        ]);

        return new Soldes([
            'matricule' => $normalizedRow['MATRICULE'],
            'fullname' => $normalizedRow['NOMS'],
            'compte' => $normalizedRow['COMPTE'],
            'solde' => $normalizedRow['SOLDE'],
            'date' => now(),//Carbon::parse($normalizedRow['DATE']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
