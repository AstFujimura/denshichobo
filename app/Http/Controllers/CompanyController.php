<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Meishimaster;

class CompanyController extends Controller
{
    public function index()
    {
        
        $company = Company::orderby('会社名カナ')->get();
        $count = $company->count();

    

        return view('information.companypage', ['company' => $company,'count' => $count]);
    }
    public function detail($id)
    {
        $company = Company::where('会社コード', '=', $id) ->first();
        $count = $company->meishimasters()->count();
        $user = Meishimaster::where('会社コード','=', $id) ->get();
            return view('information.companydetailpage',['company' => $company, 'count' => $count,'user' => $user]);
       
    }
    public function companyeditGet($id)
    {
        $company = Company::where('会社コード', '=', $id) ->first();
            return view('information.companyeditpage',['company' => $company ]);
       
    }
    public function companyeditPut(Request $request)
    {
        $id = $request->input('id');
        $companyrecord = Company::find($id);
        if (!$companyrecord) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }
        $companyrecord->会社名 = $request->input('company_name');
        $companyrecord->会社名カナ = $request->input('company_kana');
        $companyrecord->電話番号 = $request->input('tel');
        $companyrecord->郵便番号 = $request->input('postal_code');
        $companyrecord->住所 = $request->input('address');
        $companyrecord->FAX番号 = $request->input('fax');
        $companyrecord->URL = $request->input('URL');
        $companyrecord->会社備考 = $request->input('CompanyETC');

        $companyrecord->save();
        return redirect()->route('companydetailGet', ['id' => $id]);
       
    }
    // public function companyregist()
    // {
 
    //     return view('information.companyregistpage');
       
    // }
    public function companycreate(Request $request)
    {
        $keyword = $request->input('company_name');

        $existingRecord = Company::where('会社名', '=', $keyword)->first();

        if ($existingRecord) {
            // レコードが存在する場合の処理

            $change = false;

            if(($existingRecord->会社名カナ)&&($existingRecord->会社名カナ != $request->input('company_kana'))){
                $change = true;
            };
            if(($existingRecord->電話番号)&&($existingRecord->電話番号 != $request->input('tel'))){
                $change = true;
            };
            if(($existingRecord->郵便番号)&&($existingRecord->郵便番号 != $request->input('postal_code'))){
                $change = true;
            };
            if(($existingRecord->住所)&&($existingRecord->住所 != $request->input('address'))){
                $change = true;
            };
            if(($existingRecord->FAX番号)&&($existingRecord->FAX番号 != $request->input('fax'))){
                $change = true;
            };
            if(($existingRecord->会社備考)&&($existingRecord->会社備考 != $request->input('CompanyETC'))){
                $change = true;
            };

            // 会社情報に変更があった場合
            if($change == true){

                // 新しい会社レコードを追加する
                //Companyモデルをインスタンス化
                $companyinformation = new Company;
                
                //会社コードを新たに作成
                $companycode = $this->generateRandomCode();


                //モデル->カラム名=値でデータを割り当てる
                $companyinformation->会社コード = $companycode;
                
                // 各項目を入れていく
                $this->CompanyRecordCreate($companyinformation,$request);

                $companyinformation->save();
            }   

            // 会社情報に変更がなかった時、もしくはnullのデータに更新のみがあったとき
            elseif($change == false){
                $this->CompanyRecordCreate($existingRecord,$request);
                $existingRecord->save();
            }

            return redirect()->route('companyGet');

        } else {
            // レコードが存在しない場合の処理
            // 新しいレコードを追加する
            // モデルをインスタンス化
            $companyinformation = new Company;
            
            $companycode = $this->generateRandomCode();


            //モデル->カラム名=値でデータを割り当てる
            $companyinformation->会社コード = $companycode;
            $this->CompanyRecordCreate($companyinformation,$request);




            //データベースに保存
            $companyinformation->save();
            return redirect()->route('companyGet');
        }

        
    }
    public function companydelete(Request $request)
    {
        $id = $request->input('id');
        $record = Company::find($id);

        $condition = Meishimaster::where('会社コード',$id)->exists();


        // データベースの条件をチェックするロジックを記述
        if ($condition) {
            // エラーメッセージをフラッシュデータに設定
            session()->flash('error', '名刺の登録がある場合は削除できません。名刺情報の企業を変更してください。');
            
            // リダイレクト先に戻す
            return redirect()->back();
        }

        if (!$record) {
            return response()->json(['message' => 'ユーザーが見つかりません'], 404);
        }
        Company::where('会社コード' ,'=', $id) ->delete();
        return redirect()->route('companyGet');
       
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



}