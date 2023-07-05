<?php

namespace App\Http\Controllers;
use App\Models\meishiinformation;
use App\Models\Meishimaster;
use App\Models\Company;
use Illuminate\Support\Facades\Storage;

use Illuminate\Http\Request;

class DeleteController extends Controller
{
    public function delete(Request $request)
    {
        $id = $request->input('id');
        $record = Meishimaster::find($id);
        
        //名刺ファイルが存在していた場合
        if ($record->名刺ファイル !== null) {
            //名刺ファイルの画像データを消去する
            Storage::disk('public')->delete($record->名刺ファイル);
        }
        Meishimaster::where('id' ,'=', $id) ->delete();

        return redirect()->route('topGet');
    }
}