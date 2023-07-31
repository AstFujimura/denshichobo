<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\File;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\Paginator;

class TopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userId = Auth::id(); // ログインしているユーザーのIDを取得
        $admin = User::find($userId)->管理;
        $today = Carbon::today(); // 今日の日付を取得

        if ($admin == "一般"){
            $files = DB::table('files')
            ->select('files.*')
            ->leftJoin('files as t2', function ($join) {
                $join->on('files.過去データID', '=', 't2.過去データID')
                     ->whereRaw('files.バージョン < t2.バージョン');
            })
            ->whereNull('t2.バージョン')
            ->whereDate('files.updated_at', $today)
            ->where('files.保存者ID',$userId)
            ->orderBy('files.日付', 'desc')
            ->paginate(1000);
        }
        else if ($admin == "管理"){
            $files = DB::table('files')
            ->select('files.*')
            ->leftJoin('files as t2', function ($join) {
                $join->on('files.過去データID', '=', 't2.過去データID')
                     ->whereRaw('files.バージョン < t2.バージョン');
            })
            ->whereNull('t2.バージョン')
            ->whereDate('files.updated_at', $today)
            ->orderBy('files.日付', 'desc')
            ->paginate(1000);
        }


    $count = $files->total();
    $deletecount = $files->where('削除フラグ','済')->count();
    $notdeletecount = $count - $deletecount;

        // 取得したデータをビューに渡すなどの処理
    return view('information.toppage',compact('files', 'count', 'deletecount', 'notdeletecount'));

    }


                     

    public function search(Request $request)
    {
        $userId = Auth::id(); // ログインしているユーザーのIDを取得
        $admin = User::find($userId)->管理;
        if ($admin == "一般"){
            $allfiles = DB::table('files')
            ->select('files.*')
            ->leftJoin('files as t2', function ($join) {
                $join->on('files.過去データID', '=', 't2.過去データID')
                     ->whereRaw('files.バージョン < t2.バージョン');
            })
            ->whereNull('t2.バージョン')
            ->where('files.保存者ID',$userId)
            ->orderBy('files.日付', 'desc');
        }
        else if ($admin == "管理"){
            $allfiles = DB::table('files')
            ->select('files.*')
            ->leftJoin('files as t2', function ($join) {
                $join->on('files.過去データID', '=', 't2.過去データID')
                     ->whereRaw('files.バージョン < t2.バージョン');
            })
            ->whereNull('t2.バージョン')
            ->orderBy('files.日付', 'desc');
        }


    $startDateStr = $request->input('starthiduke');
    $startDateStr = str_replace('/', '', $startDateStr);

    $endDateStr = $request->input('endhiduke');
    $endDateStr = str_replace('/', '', $endDateStr);

    $startKinngakuStr = $request->input('startkinngaku');
    $startKinngakuStr = str_replace(',', '', $startKinngakuStr);
    // dd($startKinngakuStr);

    $endKinngakuStr = $request->input('endkinngaku');
    $endKinngakuStr = str_replace(',', '', $endKinngakuStr);

    $torihikisaki = $request->input('torihikisaki');
    $syoruikubunn = $request->input('syoruikubunn');
    $kennsakuword = $request->input('kennsakuword');
    $hozonn = $request->input('hozonn');



    //値が空の場合は最小値と最大値を格納する。検索後にもう一度空に戻す
    if ($startDateStr == ""){
        $startDateStr = "00000000";        
    }
    
    
    if ($endDateStr == ""){
        $endDateStr = "99999999";        
    }

    if ($startKinngakuStr == ""){
        //入力した値が入っていない場合
        $startKinngakuStr = "-2100000000";        
    }
    if ($endKinngakuStr == ""){
        $endKinngakuStr = "2100000000";        
    }
    


    //検索クエリ
    $files = $allfiles->where('files.日付', '>=', $startDateStr)
    ->where('files.日付', '<=', $endDateStr)
    ->where('files.金額', '>=', $startKinngakuStr)
    ->where('files.金額', '<=', $endKinngakuStr)
    ->where('files.取引先','like',"%". $torihikisaki ."%")
    ->where('files.書類','like',"%". $syoruikubunn ."%")
    ->where('files.保存','like',"%". $hozonn ."%")
    ->where('files.備考','like',"%". $kennsakuword ."%")
    ->paginate(1000);


    $count = $files->count();
    $deletecount = $files->where('削除フラグ','済')->count();
    $notdeletecount = $count - $deletecount;

    //検索結果に初期値として渡すときに値を空欄にしておくため
    if ($startDateStr == "00000000"){
        $startDateStr = "";
    }
    else{
        $startDateStr = substr_replace($startDateStr,'/',4,0);
        $startDateStr = substr_replace($startDateStr,'/',7,0);
    }
    if ($endDateStr == "99999999"){
        $endDateStr = "";    
    }
    else{
        $endDateStr = substr_replace($endDateStr,'/',4,0);
        $endDateStr = substr_replace($endDateStr,'/',7,0);
    }

    if ($startKinngakuStr == "-2100000000"){
        $startKinngakuStr = "";
    }
    else{
        $startKinngakuStr = number_format(floatval($startKinngakuStr));
    }
    if ($endKinngakuStr == "2100000000"){
        $endKinngakuStr = "";    
    }
    else{
        $endKinngakuStr = number_format(floatval($endKinngakuStr));
    }

    


    $data = [
    'files' => $files,
    'count' => $count,
    'deletecount' => $deletecount,
    'notdeletecount' => $notdeletecount,
    'starthiduke' => $startDateStr,
    'endhiduke' => $endDateStr,
    'startkinngaku' => $startKinngakuStr,
    'endkinngaku' => $endKinngakuStr,
    'torihikisaki' => $torihikisaki,
    'kennsakuword' => $kennsakuword,
    'none' => "",
    'seikyusyo' => "",
    'nohinnsyo' => "",
    'keiyakusyo' => "",
    'mitumorisyo' => "",
    'dennshinone' => "",
    'dennshi' => "",
    'scan' => "",
    ];

    if ($syoruikubunn == ""){
        $data['none'] = "selected";
    }
    else if ($syoruikubunn == "請求書"){
        $data['seikyusyo'] = "selected";
    }
    else if ($syoruikubunn == "納品書"){
        $data['nohinnsyo'] = "selected";
    }
    else if ($syoruikubunn == "契約書"){
        $data['keiyakusyo'] = "selected";
    }
    else if ($syoruikubunn == "見積書"){
        $data['mitumorisyo'] = "selected";
    }
    
    if ($hozonn == ""){
        $data['dennshinone'] = "selected";
    }
    else if ($hozonn == "電子保存"){
        $data['dennshi'] = "selected";
    }
    else if ($hozonn == "スキャナ保存"){
        $data['scan'] = "selected";
    }

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
    
    public function imgget($id)
    {
        $img = File::where('id',$id)->first();


        $filepath = $img->ファイルパス;
        $extension = $img->ファイル形式;
        $path = Config::get('custom.file_upload_path') . "\\" .$filepath. '.' .$extension;

            // 画像形式の場合は画像を表示
        if (in_array($extension, ['jpeg', 'jpg', 'png', 'gif'])) {
            return response()->file($path, ['Content-Type' => 'image/' . $extension]);
        }
            return response()->file($path, ['Content-Type' => 'application/pdf']);

    }

    public function usersettingGet()
    {
        $user = Auth::user();
        return view('information.usersetting',['user' => $user]);


    }
    public function usersettingPost(Request $request)
    {
        $user = Auth::user();
        if(!$request->input('name')||!$request->input('email')){
            return "必須";
        }
        //パスワード設定変更
        else if($request->input('oldpass')){
            if (Hash::check($request->input('oldpass'),$user->password)){
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->password = Hash::make($request->input('newpass'));
                $user->save();
                return "成功";
            }
            else{
                return "パスワードが違います";
            }
        }
        else {
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->save();
            return "成功";
        }



    }


}
