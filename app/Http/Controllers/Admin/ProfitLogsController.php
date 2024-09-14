<?php

namespace App\Http\Controllers\Admin;

use App\Constants\PaymentGatewayConst;
use App\Exports\AdminProfitLogs;
use App\Exports\AdminRechargeLogs;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCharge;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProfitLogsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function profitLogs()
    {
        $page_title = __("All Profits Logs");
        $profits = TransactionCharge::with('transactions')
            ->whereHas('transactions', function ($query) {
                $query->whereNotIn('type', [PaymentGatewayConst::TYPEADDMONEY, PaymentGatewayConst::TYPEMONEYOUT, PaymentGatewayConst::TYPEADDSUBTRACTBALANCE]);
            })
            ->latest()->paginate(20);

        return view('admin.sections.profits.index', compact(
            'page_title',
            'profits'
        ));
    }
    public function exportData()
    {
        $file_name = now()->format('Y-m-d_H:i:s') . "_admin_profit_Logs" . '.xlsx';
        return Excel::download(new AdminProfitLogs, $file_name);
    }
    public function balanceLogs()
    {
        $page_title = __("Add/Substract Balance Logs");
        $profits = Transaction::where('type', PaymentGatewayConst::TYPEADDSUBTRACTBALANCE)
            ->latest()->paginate(20);

        return view('admin.sections.profits.balance', compact(
            'page_title',
            'profits'
        ));
    }
    public function exportRecharge()
    {
        $file_name = now()->format('Y-m-d_H:i:s') . "_admin_recharge_Logs" . '.xlsx';
        return Excel::download(new AdminRechargeLogs, $file_name);
    }
}
