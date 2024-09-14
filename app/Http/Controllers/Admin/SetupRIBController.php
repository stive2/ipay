<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalController;
use App\Models\Admin\BasicSettings;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetupRIBController extends Controller
{

    /**
     * Displpay The SMS Configuration Page
     *
     * @return view
     */
    public function configuration()
    {
        $page_title = "RIB verification Gateway";
        $rib_config = BasicSettings::first()->rib_config;
        return view('admin.sections.setup-email.config-rib', compact(
            'page_title',
            'rib_config',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'url'           => 'required|string|max:255',
            'token'         => 'required|string|max:150',
            'active'        => 'required|string|max:3',
        ]);

        $validated = $validator->validate();

        $basic_settings = BasicSettings::first();
        if (!$basic_settings) {
            return back()->with(['error' => [__("Basic settings not found!")]]);
        }

        // Make object of rib template
        $data = [
            'url'               => $validated['url'] ?? false,
            'active'            => $validated['active'] ?? false,
            'token'             => $validated['token'] ?? false,
            'app_name'          => $basic_settings['site_name'] ?? env("APP_NAME"),
        ];

        try {
            $basic_settings->update([
                'rib_config'       => $data,
            ]);
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }
}
