<?php

namespace App\Imports;

use App\Models\Admin\Agence;
use App\Models\Agent;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class AgentImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $normalizedRow = array_map('trim', $row);
        $normalizedRow = array_change_key_case($normalizedRow, CASE_UPPER);
        $password = generate_unique_string("users", "remember_token", 8);

        if (!empty($normalizedRow['CODE_AGENCE'])) {
            $agence = Agence::where('code', $normalizedRow['CODE_AGENCE'])->first();
            if ($agence) {
                $normalizedRow['AGENCE'] = $agence->id;
            } else {
                $normalizedRow['AGENCE'] = null;
            }
        }

        return new Agent([
            'firstname' => $normalizedRow['PRENOM'],
            'lastname' => $normalizedRow['NOM'],
            'email' => $normalizedRow['EMAIL'],
            'matricule' => $normalizedRow['MATRICULE'],
            'mobile' => $normalizedRow['TEL'],
            'agence_id' => $normalizedRow['AGENCE'] ?? null,
            'username' => $this->makeUserName($normalizedRow['PRENOM'],$normalizedRow['NOM']),
            'full_mobile' => '237'.$normalizedRow['TEL'],
            'email_verified' => 1,
            'sms_verified' => 1,
            'kyc_verified' => 1,
            'created_at' => now(),
            'updated_at' => now(),
            'password' => Hash::make($password),
            'remember_token' => $password,
        ]);
    }

    public function makeUserName($firstname, $lastname) {
        $userName = make_username($firstname, $lastname);
        $check_user_name = Agent::where('username', $userName)->first();
        if ($check_user_name) {
            $userName = $userName . '-' . rand(123, 456);
        }

        return $userName;
    }
}
