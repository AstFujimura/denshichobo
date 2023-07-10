<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class TopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    $files = File::orderby('日付')->get();     
    $count = $files->count();  

        // 取得したデータをビューに渡すなどの処理
    return view('information.toppage',['files' => $files,'count' => $count]);

}


                     

    public function search()
    {
        return view('information.search');
        
    }

    public function download($id)
    {
        $file = File::where('id',$id)->first();
        $filepath = Config::get('custom.file_upload_path') . "\\" . $file->ファイルパス.'_'.$file->バージョン .'.' . $file->ファイル形式;

        // ファイルのダウンロード
        return response()->download($filepath);
        
    }

    public function detail($id)
    {
        $files = File::where('過去データID',$id)->orderby('バージョン')->get();
        $file = File::where('過去データID',$id )->first();
        $count = $files->count();  
        // ファイルのダウンロード
        return view('information.detailpage',['files' => $files,'file' => $file,'count' => $count]);
        
    }


}
