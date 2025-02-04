<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use App\Models\Document;
use App\Models\File;
use App\Models\Group;
use App\Models\Group_User;

use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use TCPDF;
use setasign\Fpdi\TcpdfFpdi;
use \TCPDF_FONTS;
use Google\Cloud\Vision\VisionClient;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Company;
use App\Models\Card;
use App\Models\Card_Company;
use App\Models\Carduser;
use App\Models\Department;
use App\Models\Card_Department;






use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Tcpdf\Fpdi;

class CardController extends Controller
{
    public function cardviewget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $cardusers = DB::table('cardusers')
            ->select('cardusers.id as carduser_id', 'cardusers.表示名', 'cards.id as card_id', 'cards.*',  'companies.*')
            ->leftJoin('cards', 'cardusers.id', '=', 'cards.名刺ユーザーID')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.最新フラグ', 1)
            ->get();
        foreach ($cardusers as $carduser) {
            $departments = DB::table('card_department')
                ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
                ->leftJoin('cards', 'card_department.名刺ID', '=', 'cards.id')
                ->where('cards.id', $carduser->card_id)
                ->orderBy('cards.id', 'desc')
                ->get();
            $carduser->departments = $departments;
        }
        return view('card.cardview', compact("prefix", "server", "cardusers"));
    }
    public function cardcompanyviewget(Request $request)
    {
        return view('card.cardcompanyview');
    }
    public function carddetailget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $carduser = Carduser::where('id', $id)->first();
        $cards = DB::table('cards')
            ->select('cards.id as card_id', 'cards.*',  'companies.*')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.名刺ユーザーID', $carduser->id)
            ->get();
        foreach ($cards as $card) {
            $departments = DB::table('card_department')
                ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
                ->leftJoin('cards', 'card_department.名刺ID', '=', 'cards.id')
                ->where('card_department.名刺ID', $card->id)
                ->orderBy('cards.id', 'desc')
                ->get();
            $card->departments = $departments;
            if ($card->最新フラグ == 1) {
                $now_card = $card;
            }
        }
        return view('card.carddetail', compact("prefix", "server", "carduser", "cards", "now_card"));
    }
    public function cardinfoget(Request $request, $id)
    {
        $card = DB::table('cards')
            ->select('cards.*',  'companies.*')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.id', $id)
            ->first();
        return response()->json($card);
    }
    public function cardregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $edit = 'new';
        $card_id = 0;
        $carduser_id = 0;
        $card = 0;
        return view('card.cardregist', compact("prefix", "server", "edit", "card_id", "carduser_id", "card"));
    }
    public function cardeditget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $edit = 'edit';
        $card = DB::table('cards')
            ->select('cards.*', 'cardusers.*', 'companies.*')
            ->leftJoin('cardusers', 'cards.名刺ユーザーID', '=', 'cardusers.id')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.id', $id)
            ->first();
        $card_id = $id;
        $carduser = Carduser::where('id', $card->名刺ユーザーID)->first();
        $carduser_id = $carduser->id;
        $departments = DB::table('card_department')
            ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
            ->where('card_department.名刺ID', $card->id)
            ->orderBy('card_department.id', 'asc')
            ->get();
        $card->departments = $departments;
        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id"));
    }

    public function cardaddget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $edit = 'add';
        $carduser = Carduser::find($id);
        $carduser_id = $carduser->id;
        $card_id = 0;
        $card = 0;

        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id"));
    }

    public function cardregistpost(Request $request)
    {
        $prefix = config('prefix.prefix');
        $server = config('prefix.server');

        $company_id = $request->company_id;
        $edit = $request->edit;
        if ($company_id != 0) {
            $company = Company::find($company_id);
            if (!$company) {
                return redirect()->back()->with('error', '会社が見つかりませんでした。');
            }
        } else {
            $company = new Company();
            $company->会社名 = $request->company_name;
            $company->会社名カナ = $request->company_name_kana;
            $company->save();
        }
        // その名刺の変更
        if ($edit == 'edit') {
            $carduser = Carduser::where('id', $request->carduser)->first();
            $card = Card::where('id', $request->card_id)->first();
        } 
        // 名刺の追加
        else if ($edit == 'add') {
            $carduser = Carduser::where('id', $request->carduser)->first();
            $card = new Card();
            $card->名刺ユーザーID = $carduser->id;
            $card->会社ID = $company->id;
            $card->最新フラグ = 1;
            $past_card = Card::where('名刺ユーザーID', $carduser->id)->update(['最新フラグ' => 0]);
        } 
        else{
            $carduser = new Carduser();
            $carduser->表示名 = $request->name;
            $carduser->表示名カナ = $request->name_kana;
            $carduser->save();

            $card = new Card();
            $card->名刺ユーザーID = $carduser->id;
            $card->会社ID = $company->id;
            $card->最新フラグ = 1;
        }

        $card->名前 = $request->name;
        $card->名前カナ = $request->name_kana;
        $card->携帯電話番号 = $request->phone_number;
        $card->メールアドレス = $request->email;
        $card->役職 = $request->position;



        // 切り取ったblob画像の場合
        if ($request->hasFile('blob-image')) {
            if ($server == 'onpre'){
            $extension = $request->file('blob-image')->getClientOriginalExtension();
            $filename = $this->generateRandomCode() . "." . $extension;
            $filepath = Config::get('custom.file_upload_path');
            // ファイルを保存
            $request->file('blob-image')->move($filepath, $filename);

            $card->名刺ファイル表 = $filename;
            }
            else if ($server == 'cloud'){
                $extension = $request->file('blob-image')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                // S3にファイルを保存
                $path = Storage::disk('s3')->put($prefix . '/' . $filename, $request->file('blob-image'), 'public');
            
                // S3のURLを取得
                $url = $filename;
            
                $card->名刺ファイル表 = $url;
            }
        }
        // 切り取られていない画像の場合
        else if ($request->hasFile('card_file_front')) {
            if ($server == 'onpre'){
            $extension = $request->file('card_file_front')->getClientOriginalExtension();
            $filename = $this->generateRandomCode() . "." . $extension;
            $filepath = Config::get('custom.file_upload_path');
            // ファイルを保存
            $request->file('card_file_front')->move($filepath, $filename);

            $card->名刺ファイル表 = $filename;
            }
            else if ($server == 'cloud'){
                $extension = $request->file('card_file_front')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                
                // S3にファイルを保存
                $path = Storage::disk('s3')->putFileAs($prefix, $request->file('card_file_front'), $filename, 'public');

            }





        }
        // 編集の場合で名刺ファイルを変更しない場合は何もかえない



        $card->save();



        $department_number = 1;
        $upper_department_id = null;
        if ($edit == 'edit') {
            $card_department = Card_Department::where('名刺ID', $card->id)->delete();
        }
        while ($request->has('department' . $department_number)) {
            // 部署名を取得
            $department_name = $request->input('department' . $department_number);

            $existing_department = Department::where('部署名', $department_name)
                ->where('会社ID', $company->id)
                ->first();
            if ($existing_department) {
                $department = $existing_department;
            } else {
                $department = new Department();
                $department->会社ID = $company->id;
                $department->部署名 = $department_name;

                // 上位部署IDを設定（最初の部署以外）
                if ($department_number != 1) {
                    $department->上位部署ID = $upper_department_id;
                }
            }

            // 部署データを保存
            $department->save();
            // 上位部署IDを取得
            $upper_department_id = $department->id;

            // 次の部署番号に進む
            $department_number++;
            $card_department = new Card_Department();
            $card_department->名刺ID = $card->id;
            $card_department->部署ID = $department->id;
            $card_department->save();
        }

        if ($edit == 'edit') {
            return redirect()->route('cardeditget', ['id' => $carduser->id])->with('success', '名刺を更新しました。');
        } else {
            return redirect()->route('cardregistget')->with('success', '名刺を登録しました。');
        }
    }
    public function cardocrpost(Request $request)
    {
        // 画像ファイルを保存
        if ($request->hasFile('image') || $request->hasFile('blob-image')) {

            // Google Cloud Vision APIを初期化
            $imageAnnotator = new ImageAnnotatorClient();
            // 画像データを取得

            // 切り取ったblob画像の場合
            if ($request->hasFile('blob-image')) {
                $imageFile  = $request->file('blob-image');
                $imageData = file_get_contents($imageFile->getRealPath());
            }
            // 切り取られていない画像の場合
            else {
                $path = $request->file('image')->store('business_cards', 'public');
                $imagePath = storage_path('app/public/' . $path);
                $imageData = file_get_contents($imagePath);
            }

            try {
                // // 画像データを取得
                // $imagePath = storage_path('app/public/' . $path);
                // $imageData = file_get_contents($imagePath);

                // Vision APIでテキストを抽出
                $response = $imageAnnotator->documentTextDetection($imageData);
                $annotation = $response->getFullTextAnnotation();

                if ($annotation) {
                    $rawText = $annotation->getText();

                    // OpenAI APIでJSON形式に整形
                    $prompt = "
                    以下のテキストは名刺から抽出されたデータです。このデータを以下のフォーマットに従って整理してください。

                    - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
                    - 郵便番号と電話番号は数字のみを抽出してください。
                    - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
                    - 「名前カナ」や「会社名カナ」には推測して必ずカタカナで入力してください。
                    - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
                      \"名前\": \"\",
                      \"名前カナ\": \"\",
                      \"会社名\": \"\",
                      \"会社名略称\": \"\",
                      \"役職\": \"\",
                      \"部署1\": \"\",
                      \"部署2\": \"\",
                      \"メールアドレス\": \"\",
                      \"電話番号\": \"\",
                      \"住所\": \"\",
                      \"郵便番号\": \"\"
                    }

                    テキスト:
                    {$rawText}
                    ";


                    $aiResponse = OpenAI::chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);

                    $structuredData = $aiResponse->choices[0]->message->content;

                    // 保存やレスポンスとして返す処理
                    return response()->json([
                        'status' => 'success',
                        'data' => json_decode($structuredData, true),
                    ]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'テキストが検出されませんでした。']);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            } finally {
                // 画像ファイルを削除
                if ($request->hasFile('image')) {
                    Storage::disk('public')->delete($path);
                }
                $imageAnnotator->close();
            }
        }

        return response()->json(['status' => 'error', 'message' => '画像がアップロードされていません。']);
    }
    //ランダムな8桁のstring型の数値を出力
    private function generateRandomCode()
    {
        $code = mt_rand(10000000, 99999999);
        return $code;
    }

    //     public function cardocrpost(Request $request)
    //     {
    //         // 画像ファイルを保存
    //         if ($request->hasFile('image')) {
    //             $path = $request->file('image')->store('business_cards', 'public');

    //             // 画像データを取得
    //             $imagePath = storage_path('app/public/' . $path);
    //             $imageData = file_get_contents($imagePath);

    //             try {
    //                 // Base64エンコードされた画像データを作成
    //                 $encodedImage = base64_encode($imageData);

    //                 // OpenAI Vision APIにリクエストを送信
    //                 $prompt = "以下は名刺の画像データです。この画像を解析して、名刺に記載された情報を以下のフォーマットに従ってJSON形式で出力してください。\n\n
    //                     - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
    //                     - 郵便番号と電話番号は数字のみを抽出してください。
    //                     - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
    //                     - 「名前カナ」や「会社名カナ」には必ずカタカナで入力してください。
    //                     - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
    //                     フォーマット:
    //                     {
    //                       \"名前\": \"\",
    //                       \"名前カナ\": \"\",
    //                       \"会社名\": \"\",
    //                       \"会社名カナ\": \"\",
    //                       \"役職\": \"\",
    //                       \"部署1\": \"\",
    //                       \"部署2\": \"\",
    //                       \"メールアドレス\": \"\",
    //                       \"電話番号\": \"\",
    //                       \"住所\": \"\",
    //                       \"郵便番号\": \"\"
    //                     }";

    //                 $response = OpenAI::chat()->create([
    //                     'model' => 'gpt-4o', // Vision API用モデル
    //                     'messages' => [
    //                         ['role' => 'system', 'content' => '名刺データを解析するアシスタントです。'],
    //                         [
    //                             'role' => 'user',
    //                             'content' => [
    //                                 [
    //                                     'type' => 'text',
    //                                     'text' => $prompt
    //                                 ],
    //                                 [
    //                                     'type' => 'image_url',
    //                                     'image_url' => [
    //                                         'url' => $imagePath,
    //                                     ],
    //                                 ]
    //                             ]
    //                         ],
    //                     ],
    //                 ]);

    //                 // Vision APIからの結果を解析
    //                 $aiResponse = $response->choices[0]->message->content;

    //                 // 保存やレスポンスとして返す処理
    //                 return response()->json([
    //                     'status' => 'success',
    //                     'data' => json_decode($aiResponse, true),
    //                 ]);
    //             } catch (\Exception $e) {
    //                 return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    //             } finally {
    //                 // 画像ファイルを削除
    //                 Storage::disk('public')->delete($path);
    //             }
    //         }

    //         return response()->json(['status' => 'error', 'message' => '画像がアップロードされていません。']);
    //     }

    public function cardimgget($id, $front = false)
    {
        $card = Card::where('id', $id)->first();
        if ($front) {
            $filepath = $card->名刺ファイル表;
        } else {
            $filepath = $card->名刺ファイル裏;
        }
        // pathinfo関数を使用して拡張子を取得
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = 'astdocs.com';
            $key = $filepath;
            $expiration = '+1 hour'; // 有効期限

            $s3Client = new S3Client([
                'region' => 'ap-northeast-1',
                'version' => 'latest',
            ]);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key' => $key
            ]);
            // 署名付きURLを生成
            $path = $s3Client->createPresignedRequest($command, $expiration)->getUri();
        } else {
            $path = Config::get('custom.file_upload_path') . "\\" . $filepath;
        }


        // 画像形式の場合は画像を表示
        if (in_array($extension, ['jpeg', 'jpg', 'JPG', 'jpe', 'JPEG', 'png', 'PNG', 'gif', 'bmp', 'svg'])) {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => 'image/' . $extension]);
            } else {
                return response()->file($path, ['Content-Type' => 'image/' . $extension]);
            }
        } else if (in_array($extension, ['PDF', 'pdf'])) {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => 'application/pdf']);
            } else {
                return response()->file($path, ['Content-Type' => 'application/pdf']);
            }
        } else {
            if (config('prefix.server') == "cloud") {
                return response()->json(['path' => $path, 'Type' => '']);
            } else {
                return response()->file($path, ['Content-Type' => '']);
            }
        }
    }

    public function companycandidateget(Request $request)
    {
        $company_name = $request->company_name;
        $company_name = str_replace('株式会社', '', $company_name);
        $company_name = str_replace('有限会社', '', $company_name);
        $company_name = str_replace('合名会社', '', $company_name);
        $company_name = str_replace('合同会社', '', $company_name);
        $company_name = str_replace(' ', '', $company_name);
        $company_name = str_replace('　', '', $company_name);

        $companies = Company::where('会社名', 'like', '%' . $company_name . '%')->get();
        foreach ($companies as $company) {
            $card = Card::where('会社ID', $company->id)->first();
            $company->card = $card;
        }
        return response()->json($companies);
    }

    public function companyinfoget($id)
    {
        $company = Company::where('id', $id)->first();
        return response()->json($company);
    }
}
