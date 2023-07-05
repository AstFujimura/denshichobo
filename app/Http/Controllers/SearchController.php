<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\meishiinformation;

use App\Models\Meishimaster;
use App\Models\Company;


class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $keyword = $request->input('keyword');

            $name = Meishimaster::join('companies', 'meishimasters.会社コード', '=', 'companies.会社コード')
                ->where(function ($query) use ($keyword) {
                    $query->where('companies.会社名', 'like', '%' . $keyword . '%')
                        ->orWhere('companies.会社名カナ', 'like', '%' . $keyword . '%')
                        ->orWhere('meishimasters.名前', 'like', '%' . $keyword . '%')
                        ->orWhere('meishimasters.カナ名', 'like', '%' . $keyword . '%');
                        
                })
                ->orderBy('meishimasters.カナ名')
                ->get();
            


            $company = Meishimaster::join('companies', 'meishimasters.会社コード', '=', 'companies.会社コード')
                ->where(function ($query) use ($keyword) {
                    $query->where('companies.会社名', 'like', '%' . $keyword . '%')
                    ->orWhere('companies.会社名カナ', 'like', '%' . $keyword . '%')
                    ->orWhere('meishimasters.名前', 'like', '%' . $keyword . '%')
                    ->orWhere('meishimasters.カナ名', 'like', '%' . $keyword . '%');
                })
                ->orderBy('companies.会社名カナ')
                ->get();




            $count = $name->count();

        
        return view('information.search', ['name' => $name,'company' => $company,'count' => $count, 'keyword' =>$keyword]);
    }
}