<?php

namespace App\Http\Controllers\Admin;

use App\Exports\SMSNotificationExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalController;
use App\Models\Admin\BasicSettings;
use App\Models\SmsNotification;
use App\Notifications\Admin\SendTestMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;

class SetupSMSController extends Controller
{

    /**
     * Displpay The SMS Configuration Page
     *
     * @return view
     */
    public function configuration()
    {
        $page_title = "SMS Gateway";
        $sms_config = BasicSettings::first()->sms_config;
        return view('admin.sections.setup-email.config-sms', compact(
            'page_title',
            'sms_config',
        ));
    }

    public function smsLogs()
    {
        $page_title = __("All SMS Notification Logs");
        $sms = SmsNotification::latest()->paginate(20);

        return view('admin.sections.money-in.smslogs', compact(
            'page_title',
            'sms'
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
            'sender_id'     => 'required|string|max:50',
        ]);

        $validated = $validator->validate();

        $basic_settings = BasicSettings::first();
        if (!$basic_settings) {
            return back()->with(['error' => [__("Basic settings not found!")]]);
        }

        // Make object of sms template
        $data = [
            'url'               => $validated['url'] ?? false,
            'token'             => $validated['token'] ?? false,
            'sender_id'         => $validated['sender_id'] ?? false,
            'app_name'          => $basic_settings['site_name'] ?? env("APP_NAME"),
        ];

        try {
            $basic_settings->update([
                'sms_config'       => $data,
            ]);
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Information updated successfully!")]]);
    }

    public function exportSMS()
    {
        $file_name = now()->format('Y-m-d_H:i:s') . "_SMS_Notification_Logs" . '.xlsx';
        return Excel::download(new SMSNotificationExport, $file_name);
    }

    public function sendTestSMS(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient'         => 'required|alpha_num|max:12',
        ]);

        $validated = $validator->validate();

        $data = [
            'recipient' => $validated['recipient'],
            'message'   => "Ceci est un sms de test provenant de IPAY",
        ];
        try {
            if (GlobalController::send_sms($data) != false) {
                return back()->with(['success' => [__("Email send successfully!")]]);
            } else {
                return back()->with(['error' => [__("Echec de l'envoie.")]]);
            }
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return back()->with(['success' => [__("Email send successfully!")]]);
    }
}
