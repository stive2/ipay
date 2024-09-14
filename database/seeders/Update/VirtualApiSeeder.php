<?php

namespace Database\Seeders\Update;

use App\Models\VirtualCardApi;
use Illuminate\Database\Seeder;

class VirtualApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $virtual_card_api = VirtualCardApi::first();
        $config = $virtual_card_api->config;
        // Convert JSON object to associative array
        $data = (array)$config;
        // Add strowallet keys if they do not already exist
        if (!isset($data['strowallet_public_key'])) {
            $data['strowallet_public_key'] = 'R67MNEPQV2ABQW9HDD7JQFXQ2AJMMY';
        }
        if (!isset($data['strowallet_secret_key'])) {
            $data['strowallet_secret_key'] = 'AOC963E385FORPRRCXQJ698C1Q953B';
        }
        if (!isset($data['strowallet_url'])) {
            $data['strowallet_url'] = 'https://strowallet.com/api/bitvcard/';
        }
        if (!isset($data['strowallet_mode'])) {
            $data['strowallet_mode'] = 'sandbox';
        }

        $in = array(
            'admin_id'      => $virtual_card_api->admin_id,
            'image'         => $virtual_card_api->image,
            'card_details'  => $virtual_card_api->card_details,
            'config'        => $data,
            'card_limit'    => 3,
            'created_at'    => now(),
            'updated_at'    => now()
        );

        $virtual_card_api->fill($in)->save();
    }
}
