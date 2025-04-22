<?php

namespace App\Http\Controllers\Agent;

use App\Exports\CommissionExport;
use App\Http\Controllers\Controller;
use App\Models\AgentProfit;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfitsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($slug = null) {

        $profits = AgentProfit::agentAuth()->orderByDesc("id")->paginate(12);
        $percus = AgentProfit::agentAuth()->where("paid", '1')->sum('total_charge');
        $npercus = AgentProfit::agentAuth()->where("paid", '0')->sum('total_charge');
        $total = AgentProfit::agentAuth()->sum('total_charge');
        $page_title = __('Journal des commisions');
        return view('agent.sections.transaction.log',compact("page_title","profits","percus","npercus","total"));
    }

    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_Commissions_".auth()->user()->matricule.'.xlsx';
        return Excel::download(new CommissionExport, $file_name);
    }
}
