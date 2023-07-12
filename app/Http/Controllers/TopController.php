<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $files = DB::table('files')
        ->select('files.*')
        ->leftJoin('files as t2', function ($join) {
            $join->on('files.過去データID', '=', 't2.過去データID')
                 ->whereRaw('files.バージョン < t2.バージョン');
        })
        ->whereNull('t2.バージョン')
        ->orderBy('files.日付', 'desc')
        ->get();

    $count = $files->count();  

        // 取得したデータをビューに渡すなどの処理
    return view('information.toppage',['files' => $files,'count' => $count]);

    }


                     

    public function search(Request $request)
    {

        $allfiles = DB::table('files')
        ->select('files.*')
        ->leftJoin('files as t2', function ($join) {
            $join->on('files.過去データID', '=', 't2.過去データID')
                 ->whereRaw('files.バージョン < t2.バージョン');
        })
        ->whereNull('t2.バージョン')
        ->orderBy('files.日付', 'desc');

    $startDateStr = $request->input('starthiduke');
    $endDateStr = $request->input('endhiduke');


    //値が空の場合は最小値と最大値を格納する。検索後にもう一度空に戻す
    if (empty($startDateStr)){
        $startDateStr = "00000000";        
    }
    
    if (empty($endDateStr)){
        $endDateStr = "99999999";        
    }
    


    //検索クエリ
    $files = $allfiles->where('files.日付', '>=', $startDateStr)
    ->where('files.日付', '<=', $endDateStr)
    ->get();


    $count = $files->count();

    //検索結果に初期値として渡すときに値を空欄にしておくため
    if ($startDateStr == "00000000"){
        $startDateStr = "";
    }
    if ($endDateStr == "99999999"){
        $endDateStr = "";    
    }


    $data = [
    'files' => $files,
    'count' => $count,
    'starthiduke' => $startDateStr,
    'endhiduke' => $endDateStr
    ];

        // 取得したデータをビューに渡すなどの処理
    return view('information.search',$data);
        
    }

    public function download($id)
    {
        $file = File::where('id',$id)->first();
        $filepath = Config::get('custom.file_upload_path') . "\\" . $file->ファイルパス .'.' . $file->ファイル形式;

        // ファイルのダウンロード
        return response()->download($filepath);
        
    }

    public function detail($id)
    {
        $file = File::with('users')
                ->where('過去データID',$id )
                ->orderby('バージョン','desc')
                ->first();
        // ファイルのダウンロード
        return view('information.detailpage',['file' => $file]);
        
    }
    public function history($id)
    {
        $files = File::with('users')
                ->where('過去データID',$id)
                ->orderby('バージョン')->get();
        $file = File::where('過去データID',$id )->first();
        $count = $files->count();  
        // ファイルのダウンロード
        return view('information.historypage',['files' => $files,'file' => $file,'count' => $count]);
        
    }


}
