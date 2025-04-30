<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AgenceExport;
use App\Http\Controllers\Controller;
use App\Models\Admin\Agence;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Http\Helpers\Response;
use App\Imports\AgenceImport;
use Maatwebsite\Excel\Facades\Excel;

class AgenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page_title = __("Setup Agence");
        $agences = Agence::orderByDesc('default')->paginate(10);
        return view('admin.sections.currency.agence',compact(
            'page_title',
            'agences',
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
        $validator = Validator::make($request->all(),[
            'country'   => 'required|string',
            'name'      => 'required|string',
            'code'      => 'required|string|unique:agences,code',
            'city'      => 'required|string',
            'option'    => 'required|string',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','agence_add');
        }
        $validated = $validator->validate();

        $default = [
            'default' => true,
            'optional'  => false,
        ];

        // If Default is already available
        if($default[$validated['option']] == true) {
            $check_default = Agence::where('default',true);
            if($check_default->count() > 0) {
                try{
                    $check_default->update([
                        'default'       => false,
                    ]);
                }catch(Exception $e) {
                    return back()->with(['error' => [__("Default agence make failed! Please try again.")]]);
                }
            }
        }

        $validated['default']       = $default[$validated['option']];
        $validated['created_at']    = now();
        $validated['admin_id']      = Auth::user()->id;

        $validated = Arr::except($validated,['option']);
        // insert_data
        try{
            $agence = Agence::create($validated);
        }catch(Exception $e) {
            return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Agence Saved Successfully!")]]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin\Agence  $agence
     * @return \Illuminate\Http\Response
     */
    public function show(Agence $agence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Admin\Agence  $agence
     * @return \Illuminate\Http\Response
     */
    public function edit(Agence $agence)
    {
        //
    }

    /**
     * Update Currency Status
     */
    public function statusUpdate(Request $request) {
        $validator = Validator::make($request->all(),[
            'status'                    => 'required|boolean',
            'data_target'               => 'required|string',
        ]);
        if ($validator->stopOnFirstFailure()->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }
        $validated = $validator->safe()->all();
        $agence_code = $validated['data_target'];

        $agence = Agence::where('code',$agence_code)->first();
        if(!$agence) {
            $error = ['error' => [__("Agence record not found in our system.")]];
            return Response::error($error,null,404);
        }

        try{
            $agence->update([
                'status' => ($validated['status'] == true) ? false : true,
            ]);
        }catch(Exception $e) {
            $error = ['error' => [__("Something went wrong! Please try again.")]];
            return Response::error($error,null,500);
        }

        $success = ['success' => [__("Agence status updated successfully!")]];
        return Response::success($success,null,200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Agence  $agence
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agence $agence)
    {
        $target = $request->target ?? $request->agence_code;
        $agence = Agence::where('code',$target)->first();
        if(!$agence) {
            return back()->with(['warning' => [__("Agence not found!")]]);
        }
        $request->merge(['old_flag' => $agence->flag]);

        $validator = Validator::make($request->all(),[
            'agence_country'   => 'required|string',
            'agence_name'      => 'required|string',
            'agence_code'      => ['required','string',Rule::unique('agences','code')->ignore($agence->id)],
            'agence_city'      => 'required|string',
            'agence_option'    => 'required|string',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('modal','agence_edit');
        }
        $validated = $validator->validate();

        $default = [
            '1' => true,
            '0'  => false,
        ];

        // If Default is already available
        if($default[$validated['agence_option']] == true) {
            $check_default = Agence::where('default',true);
            if($check_default->count() > 0 && $check_default->first()->code != $agence->code) {
                try{
                    $check_default->update([
                        'default'       => false,
                    ]);
                }catch(Exception $e) {
                    return back()->with(['error' => [__("Default agence make failed! Please try again.")]]);
                }
            }
        }

        $validated['agence_default']       = $default[$validated['agence_option']];

        $validated = replace_array_key($validated,"agence_");
        try{
            $agence->update($validated);
        }catch(Exception $e) {
            return back()->withErrors($validator)->withInput()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Successfully updated the information.")]]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin\Agence  $agence
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:agences,code',
        ]);
        $validated = $validator->validate();
        $agence = Agence::where("code",$validated['target'])->first();

        if($agence->isDefault()) {
            return back()->with(['warning' => [__("Can't deletable default agence.")]]);
        }

        try{
            $agence->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__("Agence deleted successfully!")]]);
    }

    public function search(Request $request) {
        $validator = Validator::make($request->all(),[
            'text'  => 'required|string',
        ]);

        if($validator->fails()) {
            $error = ['error' => $validator->errors()];
            return Response::error($error,null,400);
        }

        $validated = $validator->validate();
        $agences = Agence::search($validated['text'])->select()->limit(10)->get();
        return view('admin.components.search.agence-search',compact(
            'agences',
        ));
    }

    public function exportData(){
        $file_name = now()->format('Y-m-d_H:i:s') . "_Agences".'.xlsx';
        return Excel::download(new AgenceExport, $file_name);
    }

    public function importData(Request $request)
    {
        // Validate the incoming request to ensure a file is uploaded
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:4196',
        ]);

        // Get the file from the request
        $file = $request->file('file');

        // Import the Excel file using the import class
        Excel::import(new AgenceImport, $file);

        // Return a response after the import is complete
        return redirect()->back()->with('success', 'Agences imported successfully.');
    }
}
