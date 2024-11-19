<?php

namespace App\Http\Controllers;

use App\Imports\ImportOrder;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExelController extends Controller
{
    public function importView(Request $request){
        return view('importFile');
    }
    public function import(Request $request){
        Excel::import(new ImportOrder, $request->file('file')->store('files'));
        return redirect()->back();
    }
    
}
