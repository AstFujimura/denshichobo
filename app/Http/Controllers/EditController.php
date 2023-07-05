<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\meishiinformation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Models\Meishimaster;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

class EditController extends Controller
{

    public function editPost(Request $request)
    {
        $validate_rule = [
            'business_card' => 'file|image|mimes:jpeg,png,jpg,gif|max:2048'
            
        ];
        $messages = ['required' => '必須です', 'max' => '100文字以下にしてください'];
        Validator::make($request->all(),$validate_rule,$messages)->validate();
        
        //inputされたidを変数に格納
        $id = $request->input('id');
        //idを主キーとしてデータベースからレコードを取得しrecordに格納
        $record = Meishimaster::with('companies')->findOrFail($id);
        $companyrecord = Company::find($record->会社コード);

        //万が一レコードを取得できなかった場合は404
        if (!$record) {
            abort(404);
        }
        //名刺ファイルの更新があった場合
        if (!empty($request->file('business_card'))) {
            //さらに名刺ファイルがすでに存在していた場合
            if ($record->名刺ファイル !== null) {
                //名刺ファイルの画像データを消去する
                Storage::disk('public')->delete($record->名刺ファイル);
            }
            // inputされた名刺ファイルを変数imageに格納
            $image = $request->file('business_card');
            //imageをpublic/itemsフォルダ内に保存しそのパスを変数pathに格納
            $path = $image->store('items' , 'public'); 
            //画像の保存先パスをＤＢに更新
            $record->名刺ファイル = $path;
        }

        
        //その他の更新データをＤＢに反映させる
        $companyrecord->会社名 = $request->input('company_name');
        $companyrecord->会社名カナ = $request->input('company_kana');
        $companyrecord->電話番号 = $request->input('tel');
        $companyrecord->郵便番号 = $request->input('postal_code');
        $companyrecord->住所 = $request->input('address');
        $companyrecord->FAX番号 = $request->input('fax');
        $companyrecord->URL = $request->input('URL');
        $companyrecord->会社備考 = $request->input('CompanyETC');





        $record->名前 = $request->input('name');
        $record->カナ名 = $request->input('name_kana');
        $record->携帯電話番号 = $request->input('phone-number');
        $record->電話番号 = $request->input('number');
        $record->部署名 = $request->input('department');
        $record->役職 = $request->input('position');
        $record->メールアドレス = $request->input('email');
        $record->担当者 = $request->input('person_in_charge');
        $record->備考 = $request->input('etc');
        $record->入力者コード = Auth::user()->id;


        $companyrecord->save();
        $record->save();
        return redirect()->route('detail', ['id' => $id]);
    }


    public function editGet($id)
    {

        $user = Meishimaster::with('companies')->where('id', '=', $id) ->first();

        return view('information.editpage',['user' => $user]);
    }

}
