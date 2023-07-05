<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Drive;

use App\Models\User;

class RegistController extends Controller
{
    public function registGet(Request $request)
        {
                // 現在の年と月と日を取得
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;
            $currentdate = Carbon::now()->day;
            $currenthour = Carbon::now()->hour;
            $currentminute = Carbon::now()->minute;

            $current = [
                'year'=>$currentYear,
                'month' =>$currentMonth,
                'date' =>$currentdate,
                'hour' =>$currenthour,
                'minute' =>$currentminute
            ];


            return view('information.resistpage',$current);
        }
    public function registPost(Request $request)
        {
            $year = $request->input('year');
            $month = $request->input('month');
            $date = $request->input('date');
            $starthour = $request->input('starthour');
            $startminute = $request->input('startminute');
            $arrivehour = $request->input('arrivehour');
            $arriveminute = $request->input('arriveminute');
            

            $master = new Drive();
            $master->着メーター = $request->input('mater');
            $master->訪問先 = $request->input('visit');
            $master->高速 = $request->input('highway');
            $master->給油 = $request->input('oil');
            $master->SS = $request->input('ss');
            $master->出発時刻 = $year . "-". $month . "-". $date . " ".$starthour. ":". $startminute;
            $master->到着時刻 = $year . "-". $month . "-". $date . " ".$arrivehour. ":". $arriveminute;
            $master->運転者コード = 2;

            $master->save();

            return redirect()->route('topGet');


        }


        //ランダムな５桁のstring型の数値を出力
        private function generateRandomCode()
        {
            $code = mt_rand(10000, 99999);
            
            while($this->isCompanyCodeExists($code)){
                $code = mt_rand(10000, 99999);
            }
            return $code;

        }
        private function isCompanyCodeExists($code)
        {
            return company::where('会社コード', $code)->exists();
        }

        private function CompanyRecordCreate($companyinformation,$request)
        {
            $companyinformation->会社名 = $request->input('company_name');
            $companyinformation->電話番号 = $request->input('tel');
            $companyinformation->会社名カナ = $request->input('company_kana');
            $companyinformation->郵便番号 = $request->input('postal_code');
            $companyinformation->住所 = $request->input('address');
            $companyinformation->FAX番号 = $request->input('fax');
            $companyinformation->URL = $request->input('URL');
            $companyinformation->会社備考 = $request->input('CompanyETC');

            return $companyinformation;
        }

        private function MeishimasterRecordCreate($information,$request)
        {
            $information->名前 = $request->input('name');
            $information->カナ名 = $request->input('name_kana');
            $information->部署名 = $request->input('department');
            $information->役職 = $request->input('position');
            $information->携帯電話番号 = $request->input('phone-number');
            $information->電話番号 = $request->input('number');
            $information->メールアドレス = $request->input('email');
            $information->担当者 = $request->input('person_in_charge');
            $information->備考 = $request->input('etc');
            $information->入力者コード = Auth::user()->id;

            // inputされた名刺ファイルを変数imageに格納
            $image = $request->file('business_card');
            //名刺ファイルの登録があった場合
            if (!empty($request ->file('business_card'))){
            //imageをpublicディスクのitemsフォルダ内に保存しそのパスを変数pathに格納
            $path = $image->store('items' , 'public');
            //画像の保存先パスをＤＢに登録
            $information->名刺ファイル = $path;
            }

            return $information;
        }
}
