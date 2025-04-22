<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class CustumerUpdate implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalizedRow = array_map('trim', $row);
        $normalizedRow = array_change_key_case($normalizedRow, CASE_UPPER);

        if (empty($normalizedRow['MATRICULE'])) {
            return null; // Sécurité : ignorer si pas de matricule
        }

        $user = User::where('matricule', $normalizedRow['MATRICULE'])->first();

        if($user){
            $user->update([
                'email'          => $normalizedRow['EMAIL'] ?? '',
                'mobile'         => $normalizedRow['TEL'] ?? '',
                'full_mobile'    => !empty($normalizedRow['TEL']) ? '237' . $normalizedRow['TEL'] : null,
                'updated_at'     => now(),
                'firstname'      => $normalizedRow['PRENOM'],
                'lastname'       => $normalizedRow['NOM'] ?? null,
                'username'       => $this->makeUserName($normalizedRow['PRENOM'], $normalizedRow['NOM'] ?? ''),
                'rib'            => $normalizedRow['COMPTE'],
            ]);
            return $user;
        } else {
            return null;
        }
    }

    public function makeUserName($firstname, $lastname) {
        $userName = make_username($firstname, $lastname);
        $check_user_name = User::where('username', $userName)->first();
        if ($check_user_name) {
            $userName = $userName . '-' . rand(1000, 9999);
        }

        return $userName;
    }
}
