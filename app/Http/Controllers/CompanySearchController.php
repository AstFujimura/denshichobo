<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class CompanySearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

            $company = Company::where('会社名', 'like', '%' . $keyword . '%')
                        ->orWhere('会社名カナ', 'like', '%' . $keyword . '%')
                        ->orderBy('会社名カナ')
                        ->get();
                
            





            $count = $company->count();

        
        return view('information.companysearchpage', ['company' => $company,'count' => $count, 'keyword' =>$keyword]);
    }
}
