<?php

namespace App\Http\Controllers\Agent;

use App\Constants\GlobalConst;
use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GlobalController;
use App\Http\Helpers\NotificationHelper;
use App\Http\Helpers\PushNotificationHelper;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\PaymentGateway;
use App\Models\Admin\PaymentGatewayCurrency;
use App\Models\Admin\TransactionSetting;
use Jenssegers\Agent\Agent;
use App\Models\Transaction;
use App\Models\SmsNotification;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserWallet;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AgentNotification;
use App\Models\AgentWallet;
use App\Notifications\Admin\ActivityNotification;
use App\Notifications\Agent\MoneyIn\ReceiverMail;
use App\Notifications\Agent\MoneyIn\SenderMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class MoneyInController extends Controller
{
    protected  $trx_id;
    public function __construct()
    {
        $this->trx_id = 'MI' . getTrxNum();
    }

    public function index()
    {
        $page_title = __("Money In");
        $moneyInCharge = TransactionSetting::where('slug', 'money-in')->where('status', 1)->first();
        $transactions = Transaction::agentAuth()->moneyIn()->latest()->take(10)->get();
        return view('agent.sections.money-in.index', compact("page_title", 'moneyInCharge', 'transactions'));
    }
    public function checkUser(Request $request)
    {
        $email = $request->email;
        $exist['data'] = User::where('email', $email)->active()->first();

        $user = auth()->user();
        if (@$exist['data'] && $user->email == @$exist['data']->email) {
            return response()->json(['own' => __("Can't Money-In to your own")]);
        }
        return response($exist);
    }
    public function confirmedMoneyIn(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'amount' => 'required|numeric|gt:0',
            'email' => 'required',
            'password' => 'required'
        ])->validate();

        $basic_setting = BasicSettings::first();

        $sender_wallet = AgentWallet::auth()->active()->first();
        if (!$sender_wallet) {
            return back()->with(['error' => [__('Agent wallet not found')]]);
        }
        if ($sender_wallet->agent->email == $validated['email']) {
            return back()->with(['error' => [__("Can't Money-In to your own")]]);
        }

        // 27/08/2024
        // Exemple de mot de passe fourni par l'utilisateur
        $plainPassword = $validated['password'];

        $adminID = null;
        if (session()->get('adminlog')) {
            $admin = (object)session()->get('adminlog');
            $adminID = $admin->id;

            $hashedPassword = $admin->password;
        } else {
            // Exemple de mot de passe haché stocké en base de données
            $hashedPassword = auth()->user()->password;
        }

        // 02/09/2024
        if (Hash::check($plainPassword, $hashedPassword)) {
            // Le mot de passe est correct
        } else {
            // Le mot de passe est incorrect
            return back()->with(['error' => [__("Password is incorrect")]]);
        }

        $field_name = "username";
        // 21/08/2022
        if (check_email($validated['email']) || check_phone($validated['email'])) {
            $field_name = "email";
        }
        $receiver = User::where($field_name, $validated['email'])->active()->first();
        if (!$receiver) {
            return back()->with(['error' => [__("Receiver doesn't exists or Receiver is temporary banned")]]);
        }
        $receiver_wallet = UserWallet::where("user_id", $receiver->id)->first();

        if (!$receiver_wallet) {
            return back()->with(['error' => [__("Receiver wallet not found")]]);
        }

        $trx_charges =  TransactionSetting::where('slug', 'money-in')->where('status', 1)->first();
        $charges = $this->moneyInCharge($validated['amount'], $trx_charges, $sender_wallet, $receiver->wallet->currency);

        $sender_currency_rate = $sender_wallet->currency->rate;
        $min_amount = $trx_charges->min_limit * $sender_currency_rate;
        $max_amount = $trx_charges->max_limit * $sender_currency_rate;

        if ($charges['sender_amount'] < $min_amount || $charges['sender_amount'] > $max_amount) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }
        if ($charges['payable'] > $sender_wallet->balance) {
            return back()->with(['error' => [__("Sorry, insufficient balance")]]);
        }
        try {
            $trx_id = $this->trx_id;
            $sender = $this->insertSender($trx_id, $sender_wallet, $charges, $receiver_wallet, $adminID);
            if ($sender) {
                $this->insertSenderCharges($sender, $charges, $sender_wallet, $receiver_wallet);

                try {
                    if ($basic_setting->agent_email_notification == true) {
                        $notifyDataSender = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money In To") . " @" . @$receiver_wallet->user->username . " (" . @$receiver_wallet->user->email . ")",
                            'request_amount'  => getAmount($charges['sender_amount'], 4) . ' ' . $charges['sender_currency'],
                            'payable'   =>  getAmount($charges['payable'], 4) . ' ' . $charges['sender_currency'],
                            'charges'   => getAmount($charges['total_charge'], 2) . ' ' . $charges['sender_currency'],
                            'received_amount'  => getAmount($charges['receiver_amount'], 2) . ' ' . $charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //sender notifications
                        $sender_wallet->agent->notify(new SenderMail($sender_wallet->agent, (object)$notifyDataSender));
                    }
                } catch (Exception $e) {
                }
            }

            $receiverTrans = $this->insertReceiver($trx_id, $sender_wallet, $charges, $receiver_wallet);
            if ($receiverTrans) {
                $this->insertReceiverCharges($receiverTrans, $charges, $sender_wallet, $receiver_wallet);
                //Receiver notifications

                try {
                    $dataSend = [
                        'recipient' => $receiver->full_mobile,
                        'message'   => "Votre collecte journalière du montant de " . getAmount($charges['receiver_amount'], 2) . " "
                            . $charges['receiver_currency'] . " a été enregistré avec succès le " . Carbon::now()->toDateTimeString() . " par l'agent  @" .
                            $sender_wallet->agent->username . " (" . $sender_wallet->agent->email . "). Reférence de l'opération : " . $trx_id,
                    ];
                    GlobalController::send_sms($dataSend);

                    if ($basic_setting->agent_email_notification == true) {
                        $notifyDataReceiver = [
                            'trx_id'  => $trx_id,
                            'title'  => __("Money In From") . " @" . @$sender_wallet->agent->username . " (" . @$sender_wallet->agent->email . ")",
                            'received_amount'  => getAmount($charges['receiver_amount'], 2) . ' ' . $charges['receiver_currency'],
                            'status'  => __("success"),
                        ];
                        //send notifications
                        if ($receiver->type == 'email') {
                            $receiver->notify(new ReceiverMail($receiver, (object)$notifyDataReceiver));
                        }
                    }
                } catch (Exception $e) {
                }
            }
            //admin notification
            try {
                $this->adminNotification($trx_id, $charges, $sender_wallet->agent, $receiver);
            } catch (Exception $e) {
            }
            try {
                $gate = PaymentGatewayCurrency::whereHas('gateway', function ($gateway) {
                    $gateway->where('slug', PaymentGatewayConst::money_out_slug());
                    $gateway->where('status', 1);
                })->where('alias', 'cbs-xaf-money-out')->first();
                $baseCurrency = Currency::default();
                //gateway charge
                $fixedCharge = $gate->fixed_charge;
                $percent_charge =  (((($charges['receiver_amount'] * $gate->rate) / 100) * $gate->percent_charge));
                $charge = $fixedCharge + $percent_charge;
                $conversion_amount = $charges['receiver_amount'] * $gate->rate;
                $will_get = $conversion_amount -  $charge;

                //base_cur_charge
                $baseFixedCharge = $gate->fixed_charge *  $baseCurrency->rate;
                $basePercent_charge = ($charges['receiver_amount'] / 100) * $gate->percent_charge;
                $base_total_charge = $baseFixedCharge + $basePercent_charge;
                $reduceAbleTotal = $charges['receiver_amount'];

                $data['user_id'] = $receiver_wallet->user->id;
                $data['gateway_name'] = $gate->gateway->name;
                $data['gateway_type'] = $gate->gateway->type;
                $data['wallet_id'] = $receiver_wallet->id;
                $data['trx_id'] = $trx_id; // 'CBS' . getTrxNum();
                $data['amount'] =  $charges['receiver_amount'];
                $data['base_cur_charge'] = $base_total_charge;
                $data['base_cur_rate'] = $baseCurrency->rate;
                $data['gateway_id'] = $gate->gateway->id;
                $data['gateway_currency_id'] = $gate->id;
                $data['gateway_currency'] = strtoupper($gate->currency_code);
                $data['gateway_percent_charge'] = $percent_charge;
                $data['gateway_fixed_charge'] = $fixedCharge;
                $data['gateway_charge'] = $charge;
                $data['gateway_rate'] = $gate->rate;
                $data['conversion_amount'] = $conversion_amount;
                $data['will_get'] = $will_get;
                $data['payable'] = $reduceAbleTotal;
                session()->put('moneyoutData', $data);
                $this->cbsMoneyOut($trx_id);
            } catch (Exception $e) {
                //
            }
            return back()->with(['success' => [__("Money In Request Successful")]]);
        } catch (Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    //admin notification
    public function adminNotification($trx_id, $charges, $sender, $receiver)
    {
        $notification_content = [
            //email notification
            'subject' => __("Money In") . " (" . authGuardApi()['type'] . ")",
            'greeting' => __("Money In Information"),
            'email_content' => __("web_trx_id") . " : " . $trx_id . "<br>" . __("sender") . ": @" . $sender->email . "<br>" . __("Receiver") . ": @" . $receiver->email . "<br>" . __("request Amount") . " : " . get_amount($charges['sender_amount'], get_default_currency_code()) . "<br>" . __("Fees & Charges") . " : " . get_amount($charges['total_charge'], get_default_currency_code()) . "<br>" . __("Total Payable Amount") . " : " . get_amount($charges['payable'], get_default_currency_code()) . "<br>" . __("Recipient Received") . " : " . get_amount($charges['sender_amount'], get_default_currency_code()) . "<br>" . __("Status") . " : " . __("success"),

            //push notification
            'push_title' => __("Money In") . " " . __('Successful') . " (" . authGuardApi()['type'] . ")",
            'push_content' => __('web_trx_id') . " " . $trx_id . " " . __("sender") . ": @" . $sender->email . " " . __("Receiver") . ": @" . $receiver->email . " " . __("Sender Amount") . " : " . get_amount($charges['sender_amount'], get_default_currency_code()) . " " . __("Receiver Amount") . " : " . get_amount($charges['sender_amount'], get_default_currency_code()),

            //admin db notification
            'notification_type' =>  NotificationConst::MONEYIN,
            'trx_id' =>  $trx_id,
            'admin_db_title' => "Money In" . " (" . $trx_id . ")" . " (" . authGuardApi()['type'] . ")",
            'admin_db_message' => "Sender" . ": @" . $sender->email . "," . "Receiver" . ": @" . $receiver->email . "," . "Sender Amount" . " : " . get_amount($charges['sender_amount'], get_default_currency_code()) . "," . "Receiver Amount" . " : " . get_amount($charges['sender_amount'], get_default_currency_code())
        ];

        try {
            //notification
            (new NotificationHelper())->admin(['admin.money.in.index', 'admin.money.in.export.data'])
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
    //sender transaction
    public function insertSender($trx_id, $sender_wallet, $charges, $receiver_wallet, $adminID)
    {
        $trx_id = $trx_id;
        $authWallet = $sender_wallet;
        $afterCharge = ($authWallet->balance - $charges['payable']); // + $charges['agent_total_commission'];

        DB::beginTransaction();
        try {
            $id = DB::table("transactions")->insertGetId([
                'agent_id'                      => $sender_wallet->agent->id,
                'admin_id'                      => $adminID,
                'agent_wallet_id'               => $sender_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MONEYIN,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['sender_amount'],
                'payable'                       => $charges['payable'],
                'available_balance'             => $afterCharge,
                'cbsTransfert'                  => 'ND',
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MONEYIN, " ")) . " To " . $receiver_wallet->user->fullname,
                'details'                       => json_encode([
                    'receiver_username' => $receiver_wallet->user->username,
                    'receiver_email' => $receiver_wallet->user->email,
                    'sender_username' => $sender_wallet->agent->username,
                    'sender_email' => $sender_wallet->agent->email,
                    'charges' => $charges
                ]),
                'attribute'                      => PaymentGatewayConst::SEND,
                'status'                        => GlobalConst::SUCCESS,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet, $afterCharge);
            $this->agentProfitInsert($id, $authWallet, $charges);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return $id;
    }
    public function agentProfitInsert($id, $authWallet, $charges)
    {
        DB::beginTransaction();
        try {
            DB::table('agent_profits')->insert([
                'agent_id'          => $authWallet->agent->id,
                'transaction_id'    => $id,
                'paid'    => '0',
                'percent_charge'    => $charges['agent_percent_commission'],
                'fixed_charge'      => $charges['agent_fixed_commission'],
                'total_charge'      => $charges['agent_total_commission'],
                'created_at'        => now(),
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function updateSenderWalletBalance($authWallet, $afterCharge)
    {
        $authWallet->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($id, $charges, $sender_wallet, $receiver_wallet)
    {
        DB::beginTransaction();
        try {
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $charges['percent_charge'],
                'fixed_charge'      => $charges['fixed_charge'],
                'total_charge'      => $charges['total_charge'],
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         => __("Money IN"),
                'message'       => "Money In To  " . $receiver_wallet->user->fullname . ' ' . $charges['sender_amount'] . ' ' . $charges['sender_currency'] . " Successful",
                'image'         =>  get_image($sender_wallet->agent->image, 'agent-profile'),
            ];
            AgentNotification::create([
                'type'      => NotificationConst::MONEYIN,
                'agent_id'  => $sender_wallet->agent->id,
                'message'   => $notification_content,
            ]);
            //Push Notifications
            (new PushNotificationHelper())->prepare([$sender_wallet->agent->id], [
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'agent',
            ])->send();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    //Receiver Transaction
    public function insertReceiver($trx_id, $sender_wallet, $charges, $receiver_wallet)
    {
        $trx_id = $trx_id;
        $receiverWallet = $receiver_wallet;
        $recipient_amount = ($receiverWallet->balance + $charges['receiver_amount']);

        DB::beginTransaction();
        try {
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $receiver_wallet->user->id,
                'user_wallet_id'                => $receiver_wallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MONEYIN,
                'trx_id'                        => $trx_id,
                'request_amount'                => $charges['receiver_amount'],
                'payable'                       => $charges['receiver_amount'],
                'available_balance'             => $receiver_wallet->balance + $charges['receiver_amount'],
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MONEYIN, " ")) . " From " . $sender_wallet->agent->fullname,
                'details'                       => json_encode([
                    'receiver_username' => $receiver_wallet->user->username,
                    'receiver_email' => $receiver_wallet->user->email,
                    'sender_username' => $sender_wallet->agent->username,
                    'sender_email' => $sender_wallet->agent->email,
                    'charges' => $charges
                ]),
                'attribute'                     => PaymentGatewayConst::RECEIVED,
                'status'                        => GlobalConst::SUCCESS,
                'cbsTransfert'                  => 'ND',
                'created_at'                    => now(),
            ]);
            $this->updateReceiverWalletBalance($receiverWallet, $recipient_amount);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
        return $id;
    }
    public function updateReceiverWalletBalance($receiverWallet, $recipient_amount)
    {
        $receiverWallet->update([
            'balance'   => $recipient_amount,
        ]);
    }
    public function insertReceiverCharges($id, $charges, $sender_wallet, $receiver_wallet)
    {
        DB::beginTransaction();
        try {
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => 0,
                'fixed_charge'      => 0,
                'total_charge'      => 0,
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         => __("Money In"),
                'message'       => "Money In From  " . $sender_wallet->agent->fullname . ' ' . $charges['receiver_amount'] . ' ' . $charges['receiver_currency'] . " Successful",
                'image'         => get_image($receiver_wallet->user->image, 'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::MONEYIN,
                'user_id'   => $receiver_wallet->user->id,
                'message'   => $notification_content,
            ]);
            DB::commit();
            //Push Notifications
            (new PushNotificationHelper())->prepare([$receiver_wallet->user->id], [
                'title' => $notification_content['title'],
                'desc'  => $notification_content['message'],
                'user_type' => 'user',
            ])->send();
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }
    }
    public function moneyInCharge($sender_amount, $charges, $sender_wallet, $receiver_currency)
    {
        $exchange_rate = $receiver_currency->rate / $sender_wallet->currency->rate;

        $data['exchange_rate']                      = $exchange_rate;
        $data['sender_amount']                      = $sender_amount;
        $data['sender_currency']                    = $sender_wallet->currency->code;
        $data['receiver_currency']                  = $receiver_currency->code;
        $data['percent_charge']                     = ($sender_amount / 100) * $charges->percent_charge ?? 0;
        $data['fixed_charge']                       = $sender_wallet->currency->rate * $charges->fixed_charge ?? 0;
        $data['total_charge']                       = $data['percent_charge'] + $data['fixed_charge'];
        $data['sender_wallet_balance']              = $sender_wallet->balance;
        $data['receiver_amount']                    = ($sender_amount - $data['total_charge']) * $exchange_rate;
        $data['payable']                            = $sender_amount; //+ $data['total_charge'];
        $data['agent_percent_commission']           = ($sender_amount / 100) * $charges->agent_percent_commissions ?? 0;
        $data['agent_fixed_commission']             = $sender_wallet->currency->rate * $charges->agent_fixed_commissions ?? 0;
        $data['agent_total_commission']             = $data['agent_percent_commission'] + $data['agent_fixed_commission'];
        return $data;
    }

    public function cbsMoneyOut($trx_id)
    {
        $moneyOutData = (object)session()->get('moneyoutData');
        $gateway = PaymentGateway::where('id', $moneyOutData->gateway_id)->first();

        $get_values = [];
        // DB::beginTransaction();
        try {
            $inserted_id = $this->insertRecordManual($moneyOutData, $gateway, $get_values, $reference = null, PaymentGatewayConst::STATUSPENDING);
            /* DB::table("transactions")->where('trx_id', $trx_id)->update([
                'cbsTransfert' => 'ND',
            ]);
            DB::commit(); */

            //send notifications
            $this->insertChargesManual($moneyOutData, $inserted_id);
            $this->adminNotificationcbs($moneyOutData, PaymentGatewayConst::STATUSPENDING);
            $this->insertDeviceManual($moneyOutData, $inserted_id);
        } catch (Exception $e) {
            DB::rollBack();
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
                'cbsTransfert'                  => 'ND',
                'created_at'                    => now(),
            ]);
            if ($moneyOutData->gateway_type != "AUTOMATIC") {
                $this->updateWalletBalanceManual($authWallet, $afterCharge);
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

    public function send_isms($dataSend)
    {
        $sms_config = BasicSettings::first()->sms_config;
        $headers = [
            'Authorization: Bearer ' . $sms_config->token,
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $data = [
            'recipient' => trim($dataSend['recipient'], ' '),
            'sender_id' => $sms_config->sender_id,
            'type'      => 'plain',
            'message'   => $dataSend['message'],
        ];

        $ch = curl_init($sms_config->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $smsNotification = new SmsNotification($data);
        if ($response === false) {
            $smsNotification->status = '0';
            $smsNotification->response = 'Error curl : ' . curl_error($ch);
        } else {
            $smsNotification->status = '1';
            $smsNotification->response = $response;
        }
        $smsNotification->save();

        return $response;
    }
}
