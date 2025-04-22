<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class CustumerImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalizedRow = array_map('trim', $row);
        $normalizedRow = array_change_key_case($normalizedRow, CASE_UPPER);

        return new User([
            'firstname'      => $normalizedRow['PRENOM'],
            'lastname'       => $normalizedRow['NOM'] ?? null,
            'email'          => $normalizedRow['EMAIL'] ?? '',
            'matricule'      => $normalizedRow['MATRICULE'],
            'mobile'         => $normalizedRow['TEL'] ?? '',
            'rib'            => $normalizedRow['COMPTE'],
            'username'       => $this->makeUserName($normalizedRow['PRENOM'], $normalizedRow['NOM'] ?? ''),
            'full_mobile'    => isset($normalizedRow['TEL']) ? '237' . $normalizedRow['TEL'] : null,
            'email_verified' => 1,
            'sms_verified'   => 1,
            'kyc_verified'   => 1,
            'created_at'     => now(),
            'updated_at'     => now(),
            'password'       => Hash::make('123456'),
        ]);

    }

    public function makeUserName($firstname, $lastname) {
        $userName = make_username($firstname, $lastname);
        $check_user_name = User::where('username', $userName)->first();
        if ($check_user_name) {
            $userName = $userName . '-' . rand(123, 456);
        }

        return $userName;
    }
}
