<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Drive;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    // 現在の年と月を取得
    $currentYear = Carbon::now()->year;
    $currentMonth = Carbon::now()->month;


    // データベースから指定した年と月のデータを取得
    $data = Drive::with('users')->whereYear('出発時刻', '=', $currentYear)
                    ->whereMonth('出発時刻', '=', $currentMonth)
                    ->get();
                    $dayOfWeek = ['月','火','水','木','金','土','日'];
                    $processedData = [];
                    

                    foreach ($data as $item) {
                        // dd($item->users->name);
                        $year = $item->出発時刻->format('Y');
                        $month = $item->出発時刻->format('m');
                        $day = $item->出発時刻->format('d');
                        //format('N')によって1.月 2.火 3.水となる
                        $dayofweek = $item->出発時刻->format('N');
                        $starthour = $item->出発時刻->format('H');
                        $startminute = $item->出発時刻->format('i');
                        $arrivehour = $item->到着時刻->format('H');
                        $arriveminute = $item->到着時刻->format('i');
                    
                        $processedData[] = [
                            '年' => $year,
                            '月' => $month,
                            '日' => $day,
                            '曜' => $dayOfWeek[$dayofweek-1],
                            '出発時' => $starthour,
                            '出発分' => $startminute,
                            '到着時' => $arrivehour,
                            '到着分' => $arriveminute,
                            '着メーター' => $item->着メーター,
                            '給油' => $item->給油,
                            'SS' => $item->SS,
                            '高速' => $item->高速,
                            '訪問先' => $item->訪問先,
                            '使用者' => $item->users->name ?? null,
                        ];
                    }

                    // dd($processedData);
    // 取得したデータをビューに渡すなどの処理
    return view('information.toppage', ['data' => $processedData,'year' => $currentYear,'month' => $currentMonth]);
                     
    }

    public function detail($id)
    {
        $user = Meishimaster::with('companies')->where('id', '=', $id) ->first();
        $user->名刺ファイル;
        $image_path = $user->名刺ファイル;
        if(file_exists($image_path)){
            return view('information.detail',['user' => $user,'image_path' => $image_path]);
        }else{
            return view('information.detail', ['user' => $user,'image_path' => null]);
        }
        
    }

    public function urlsearch(Request $request)
    {
        $urlkeyword = $request->input('Button');
        if (strpos($urlkeyword, 'http') !== false) {
            return redirect()->away($urlkeyword);
        }
        else{
            $url = "http://" .$urlkeyword;
            return redirect()->away($url);
        }
        
    }


}
