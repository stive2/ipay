<?php

namespace App\Http\Controllers\Api;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\PaymentGateway;
use Jenssegers\Agent\Agent;
use App\Models\UserNotification;
use App\Models\UserWallet;
use Illuminate\Support\Facades\DB;
use App\Notifications\Admin\ActivityNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Exception;


class CbsCollectController extends Controller
{
    public static function cbsMoneyOut($trx_id)
    {
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();

        $get_values = [];
        try {
            //send notifications
            $inserted_id = CbsCollectController::insertRecordManual($moneyOutData, $gateway, $get_values, $reference = null, PaymentGatewayConst::STATUSPENDING);
            CbsCollectController::insertChargesManual($moneyOutData, $inserted_id);
            CbsCollectController::adminNotificationcbs($moneyOutData, PaymentGatewayConst::STATUSPENDING);
            CbsCollectController::insertDeviceManual($moneyOutData, $inserted_id);

            DB::table("transactions")->where('trx_id', $trx_id)->update([
                'cbsTransfert' => 'NON',
            ]);
        } catch (Exception $e) {
            return back()->with(['success' => [__("Money In Request Successful but automatic withdraw has fail")]]);
        }
    }

    public function insertRecordManual($moneyOutData, $gateway, $get_values, $reference, $status)
    {

        $trx_id = $moneyOutData->trx_id ?? 'MO' . getTrxNum();
        $authWallet = UserWallet::where('id', $moneyOutData->wallet_id)->where('user_id', $moneyOutData->user_id)->first();
        if ($moneyOutData->gateway_type != "AUTOMATIC") {
            $afterCharge = ($authWallet->balance - ($moneyOutData->amount));
        } else {
            $afterCharge = $authWallet->balance;
        }

        DB::beginTransaction();
        try {
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => auth()->user()->id,
                'user_wallet_id'                => $moneyOutData->wallet_id,
                'payment_gateway_currency_id'   => $moneyOutData->gateway_currency_id,
                'type'                          => PaymentGatewayConst::TYPEMONEYOUT,
                'trx_id'                        => $trx_id,
                'request_amount'                => $moneyOutData->amount,
                'payable'                       => $moneyOutData->will_get,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPEMONEYOUT, " ")) . " by " . $gateway->name,
                'details'                       => json_encode($get_values),
                'status'                        => $status,
                'callback_ref'                  => $reference ?? null,
                'created_at'                    => now(),
            ]);
            if ($moneyOutData->gateway_type != "AUTOMATIC") {
                CbsCollectController::updateWalletBalanceManual($authWallet, $afterCharge);
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }

    public function updateWalletBalanceManual($authWalle, $afterCharge)
    {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }

    public function insertChargesManual($moneyOutData, $id)
    {

        if (Auth::guard(get_auth_guard())->check()) {
            $user = auth()->guard(get_auth_guard())->user();
        }
        DB::beginTransaction();
        try {
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $moneyOutData->gateway_percent_charge,
                'fixed_charge'      => $moneyOutData->gateway_fixed_charge,
                'total_charge'      => $moneyOutData->gateway_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         => __("Withdraw Money"),
                'message'       => __("Your Withdraw Request Send To Admin") . " " . $moneyOutData->amount . ' ' . get_default_currency_code() . " " . __("Successful"),
                'image'         => get_image($user->image, 'user-profile'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::MONEY_OUT,
                'user_id'  =>  auth()->user()->id,
                'message'   => $notification_content,
            ]);
            DB::commit();

            //Push Notifications
            (new PushNotificationHelper())->prepare([$user->id], [
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }

    //admin notification global(Agent & User)
    public function adminNotificationcbs($data, $status)
    {
        $user = auth()->guard(userGuard()['guard'])->user();
        $exchange_rate = " 1 " . get_default_currency_code() . ' = ' . get_amount($data->gateway_rate, $data->gateway_currency);
        if ($status == PaymentGatewayConst::STATUSSUCCESS) {
            $status = "success";
        } elseif ($status == PaymentGatewayConst::STATUSPENDING) {
            $status = "Pending";
        } elseif ($status == PaymentGatewayConst::STATUSHOLD) {
            $status = "Hold";
        } elseif ($status == PaymentGatewayConst::STATUSWAITING) {
            $status = "Waiting";
        } elseif ($status == PaymentGatewayConst::STATUSPROCESSING) {
            $status = "Processing";
        } elseif ($status == PaymentGatewayConst::STATUSFAILD) {
            $status = "Failed";
        }

        $notification_content = [
            //email notification
            'subject' => __("Withdraw Money") . " (" . userGuard()['type'] . ")",
            'greeting' => __("Withdraw Money Via") . " " . $data->gateway_name . ' (' . $data->gateway_type . ' )',
            'email_content' => __("web_trx_id") . " : " . $data->trx_id . "<br>" . __("request Amount") . " : " . get_amount($data->amount, get_default_currency_code()) . "<br>" . __("Exchange Rate") . " : " . $exchange_rate . "<br>" . __("Fees & Charges") . " : " . get_amount($data->gateway_charge, $data->gateway_currency) . "<br>" . __("Total Payable Amount") . " : " . get_amount($data->payable, get_default_currency_code()) . "<br>" . __("Will Get") . " : " . get_amount($data->will_get, $data->gateway_currency, 2) . "<br>" . __("Status") . " : " . __($status),
            //push notification
            'push_title' =>  __("Withdraw Money") . " (" . userGuard()['type'] . ")",
            'push_content' => __('web_trx_id') . " " . $data->trx_id . " " . __("Withdraw Money") . ' ' . get_amount($data->amount, get_default_currency_code()) . ' ' . __('By') . ' ' . $data->gateway_name . ' (' . $user->username . ')',

            //admin db notification
            'notification_type' =>  NotificationConst::MONEY_OUT,
            'trx_id' => $data->trx_id,
            'admin_db_title' =>  "Withdraw Money" . " (" . userGuard()['type'] . ")",
            'admin_db_message' =>  "Withdraw Money" . ' ' . get_amount($data->amount, get_default_currency_code()) . ' ' . 'By' . ' ' . $data->gateway_name . ' (' . $user->username . ')'
        ];

        try {
            //notification
            (new NotificationHelper())->admin(['admin.money.out.index', 'admin.money.out.pending', 'admin.money.out.complete', 'admin.money.out.canceled', 'admin.money.out.details', 'admin.money.out.approved', 'admin.money.out.rejected', 'admin.money.out.export.data'])
                ->mail(ActivityNotification::class, [
                    'subject'   => $notification_content['subject'],
                    'greeting'  => $notification_content['greeting'],
                    'content'   => $notification_content['email_content'],
                ])
                ->push([
                    'user_type' => "admin",
                    'title' => $notification_content['push_title'],
                    'desc'  => $notification_content['push_content'],
                ])
                ->adminDbContent([
                    'type' => $notification_content['notification_type'],
                    'title' => $notification_content['admin_db_title'],
                    'message'  => $notification_content['admin_db_message'],
                ])
                ->send();
        } catch (Exception $e) {
        }
    }

    public function insertDeviceManual($output, $id)
    {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        $mac = "";

        DB::beginTransaction();
        try {
            DB::table("transaction_devices")->insert([
                'transaction_id' => $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
}
