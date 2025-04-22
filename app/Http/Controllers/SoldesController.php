<?php

namespace App\Http\Controllers;

use App\Exports\SoldesExport;
use App\Imports\SoldeImport;
use App\Models\Soldes;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SoldesController extends Controller
{
    public function importData(Request $request)
    {
        // Validate the incoming request to ensure a file is uploaded
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:8192',
        ]);

        // Get the file from the request
        $file = $request->file('file');

        // Import the Excel file using the import class
        Excel::import(new SoldeImport, $file);

        return redirect()->back()->with('success', 'Soldes importés.');
    }

    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_Soldes".'.xlsx';
        return Excel::download(new SoldesExport, $file_name);
    }

    static function sendSoldes() {
        $soldes = Soldes::join('users', 'users.matricule', 'soldes.matricule')
                        ->distinct()
                        ->where('date', Carbon::today())
                        ->where('notifie', '0')
                        ->get(['soldes.*', 'users.full_mobile'])->toArray();

        foreach($soldes as $row => $solde){
            try {
                $dataSend = [
                    'recipient' => $solde['full_mobile'],
                    'message'   => "Chers ".$solde['fullname'].", le solde de votre compte de collecte (".$solde['compte'].
                            ") a la date du " . Carbon::parse($solde['date'])->toDateString(). " est de ". getAmount($solde['solde'], 2) .
                            " Fcfa. Nous vous remercions pour votre confiance.",
                ];

                $return = GlobalController::send_sms($dataSend);

                if($return['status'] == '1') {
                    Soldes::where('id', $solde['id'])->update([
                        'notifie' => 1
                    ]);
                }
            } catch (Exception $e) {

            }
        }

        return back()->with(['success' => [__("Notifications envoyées")]]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("All Soldes Logs");
        $soldes = Soldes::latest()->where('date', Carbon::today())->paginate(20);

        return view('admin.sections.money-in.soldeslogs', compact(
            'page_title',
            'soldes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Soldes  $soldes
     * @return \Illuminate\Http\Response
     */
    public function show(Soldes $soldes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Soldes  $soldes
     * @return \Illuminate\Http\Response
     */
    public function edit(Soldes $soldes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Soldes  $soldes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Soldes $soldes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Soldes  $soldes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Soldes $soldes)
    {
        //
    }
}
