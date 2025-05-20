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
use App\Models\Carduser_User;
use App\Models\Department;
use App\Models\Card_Department;
use App\Models\Branch;
use App\Models\UploadedCard;
use App\Models\OpenaiQueue;
use Illuminate\Support\Facades\File as Filesystem;
use App\Jobs\ProcessUploadedCard;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Http;

use Gemini\Data\Blob;
use Gemini\Enums\MimeType;
use Gemini\Laravel\Facades\Gemini;
use Gemini\Client as GeminiClient;

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
        $sub = DB::table('cards')
            ->select('cards.*', DB::raw('ROW_NUMBER() OVER (PARTITION BY 名刺ユーザーID ORDER BY id ASC) as row_num'))
            ->where('cards.最新フラグ', 1);

        $cardusers = DB::table('cardusers')
            ->select(
                'cardusers.id as carduser_id',
                'cardusers.表示名',
                'latest_cards.id as card_id',
                'latest_cards.*',
                'companies.*'
            )
            ->joinSub($sub, 'latest_cards', function ($join) {
                $join->on('cardusers.id', '=', 'latest_cards.名刺ユーザーID')
                    ->where('latest_cards.row_num', '=', 1);
            })
            ->leftJoin('companies', 'latest_cards.会社ID', '=', 'companies.id')
            ->orderBy('cardusers.表示名カナ', 'asc')
            ->get();
        foreach ($cardusers as $carduser) {
            $departments = DB::table('card_department')
                ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
                ->leftJoin('cards', 'card_department.名刺ID', '=', 'cards.id')
                ->where('cards.id', $carduser->card_id)
                ->orderBy('cards.id', 'desc')
                ->get();
            $carduser->departments = $departments;


            $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->carduser_id)
                ->where('ユーザーID', Auth::user()->id)
                ->first();
            $carduser->マイ名刺ユーザー = $carduser_user->マイ名刺ユーザー ?? null == 1 ? "true" : "false";
            $carduser->お気に入りユーザー = $carduser_user->お気に入りユーザー ?? null == 1 ? "true" : "false";
        }
        return view('card.cardview', compact("prefix", "server", "cardusers"));
    }
    public function cardcompanyviewget(Request $request)
    {
        return view('card.cardcompanyview');
    }
    // 名刺詳細画面
    public function carddetailget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $carduser = Carduser::where('id', $id)->first();
        if (!$carduser) {
            return redirect()->route('cardviewget')->with('error', '名刺が見つかりませんでした。');
        }
        $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->id)
            ->where('ユーザーID', Auth::user()->id)
            ->first();
        $cards = DB::table('cards')
            ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
            ->where('cards.名刺ユーザーID', $carduser->id)
            ->get();
        $now_card = null;
        foreach ($cards as $card) {
            $departments = DB::table('card_department')
                ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
                ->leftJoin('cards', 'card_department.名刺ID', '=', 'cards.id')
                ->where('card_department.名刺ID', $card->card_id)
                ->orderBy('cards.id', 'desc')
                ->get();
            $card->departments = $departments;
            if ($card->拠点指定 == 0) {
                $card->拠点名 = "";
            }
            if ($card->最新フラグ == 1) {
                $now_card = $card;
            }
        }
        if (!$now_card) {
            return redirect()->route('cardviewget')->with('error', '名刺が見つかりませんでした。');
        }
        return view('card.carddetail', compact("prefix", "server", "carduser", "cards", "now_card", "carduser_user"));
    }
    // 名刺最新API
    public function cardlatestpost(Request $request)
    {
        $card_id = $request->card_id;
        $latest_card = Card::find($card_id);

        // 名刺ユーザーIDが同じ名刺の中で最新フラグが1のものを0にする
        Card::where('名刺ユーザーID', $latest_card->名刺ユーザーID)
            ->where('id', '!=', $card_id)
            ->update(['最新フラグ' => 0]);

        $latest_card->最新フラグ = 1;
        $latest_card->save();

        $carduser = Carduser::find($latest_card->名刺ユーザーID);
        $carduser->表示名 = $latest_card->名前;
        $carduser->表示名カナ = $latest_card->名前カナ;
        $carduser->save();

        return response()->json(['success' => 'true']);
    }
    // 名刺お気に入りAPI
    public function cardfavoritepost(Request $request)
    {
        $card_user_id = $request->card_user_id;
        $check = $request->check;
        $type = $request->type;
        $carduser_user = Carduser_User::where('名刺ユーザーID', $card_user_id)
            ->where('ユーザーID', Auth::user()->id)
            ->first();
        if (!$carduser_user) {
            $carduser_user = new Carduser_User();
            $carduser_user->名刺ユーザーID = $card_user_id;
            $carduser_user->ユーザーID = Auth::user()->id;
        }

        if ($check == "true") {
            if ($type == 'my_card_check') {
                $carduser_user->マイ名刺ユーザー = true;
            }
            if ($type == 'favorite_check') {
                $carduser_user->お気に入りユーザー = true;
            }
        } else if ($check == "false") {
            if ($type == 'my_card_check') {
                $carduser_user->マイ名刺ユーザー = false;
            }
            if ($type == 'favorite_check') {
                $carduser_user->お気に入りユーザー = false;
            }
        }
        $carduser_user->save();
        return response()->json(['success' => 'true']);
    }
    public function cardinfoget(Request $request, $id)
    {
        $card = DB::table('cards')
            ->select('cards.*',  'companies.*')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.id', $id)
            ->first();

        $department = DB::table('card_department')
            ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
            ->where('card_department.名刺ID', $id)
            ->get();
        $card->department = $department;
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
        $designate_branch = false;
        return view('card.cardregist', compact("prefix", "server", "edit", "card_id", "carduser_id", "card", "designate_branch"));
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
            ->select('cards.id as card_id', 'cards.*', 'cardusers.*', 'companies.*', 'branches.*')
            ->leftJoin('cardusers', 'cards.名刺ユーザーID', '=', 'cardusers.id')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
            ->where('cards.id', $id)
            ->first();
        $card_id = $id;
        $carduser = Carduser::where('id', $card->名刺ユーザーID)->first();
        $carduser_id = $carduser->id;
        $departments = DB::table('card_department')
            ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
            ->where('card_department.名刺ID', $card->card_id)
            ->orderBy('card_department.id', 'asc')
            ->get();
        $card->departments = $departments;
        $designate_branch = $card->拠点指定 == 1 ? true : false;
        $branches = Branch::where('会社ID', $card->会社ID)->get();
        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id", "designate_branch", "branches"));
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
        $designate_branch = false;

        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id", "designate_branch"));
    }
    public function carddeletepost(Request $request)
    {
        $card_id = $request->card_id;
        $card = Card::find($card_id);
        if (config('prefix.server') == 'cloud') {
            // 権限などがまだなので保留
            // Storage::disk('s3')->delete(config('preix.prefix') .'/'. $card->名刺ファイル表);
        } else if (config('prefix.server') == 'onpre') {
            $uploadPath = config('custom.file_upload_path');
            $fileName = $card->名刺ファイル表; // 削除したいファイル名
            $filePath = $uploadPath . '/' . $fileName;
            if (Filesystem::exists($filePath)) {
                Filesystem::delete($filePath);
            }
        }


        // 名刺のユーザーが他に名刺を登録しているかを取得する
        $user_count = Card::where('名刺ユーザーID', $card->名刺ユーザーID);
        $carduser = Carduser::find($card->名刺ユーザーID);

        // 登録している名刺が該当の一つのみである場合は名刺ユーザーごと消去する
        if ($user_count->count() == 1) {
            $carduser->delete();
        }
        // 他に名刺を登録している場合は名刺のみを消去する
        else {
            $card->delete();
            // 名刺ユーザーIDが同じ名刺の中で最新フラグが0のものを0にする
            $user_count->where('id', '!=', $card_id)
                ->orderBy('id', 'desc')
                ->first()
                ->update(['最新フラグ' => 1]);
            $carduser->表示名 = $user_count->first()->名前;
            $carduser->表示名カナ = $user_count->first()->名前カナ;
            $carduser->save();
        }

        return redirect()->route('cardviewget')->with('success', '名刺を削除しました。');
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
        // 拠点IDが入力されている場合は拠点を取得する
        $branch_id = $request->branch_id;
        // 拠点名が入力されている場合は拠点を取得する
        $branch_name = $request->branch_name;
        if ($branch_name != '') {
            // 過去に登録したことのある拠点名かを調べる
            $branch = Branch::where('拠点名', $branch_name)
                ->where('会社ID', $company->id)
                ->first();
            // その履歴がない場合は新規登録する
            if (!$branch) {
                $branch = new Branch();
                $branch->拠点名 = $branch_name;
                $branch->会社ID = $company->id;
                $branch->拠点所在地 = $request->branch_address;
                $branch->電話番号 = $request->branch_phone_number;
                $branch->FAX番号 = $request->branch_fax_number;
                $branch->拠点指定 = true;
                $branch->save();
            }
        }
        // 拠点IDが入力されている場合は拠点を取得する
        else if ($branch_id != '') {
            $branch = Branch::find($branch_id);
        }
        // 拠点IDも拠点名も入力されていない場合は拠点指定のない拠点を取得する
        else if ($branch_name == '') {
            $branch = Branch::where('拠点指定', false)
                ->where('会社ID', $company->id)
                ->first();
            if (!$branch) {
                $branch = new Branch();
                $branch->拠点名 = $request->company_name;
                $branch->会社ID = $company->id;
                $branch->拠点指定 = false;
                $branch->save();
            }
        }
        // その名刺の変更
        if ($edit == 'edit') {
            $carduser = Carduser::find($request->carduser);
            $card = Card::find($request->card_id);
            if ($card->最新フラグ == 1) {
                $carduser->表示名 = $request->name;
                $carduser->表示名カナ = $request->name_kana;
                $carduser->save();
            }
        }
        // 名刺の追加
        else if ($edit == 'add') {
            $carduser = Carduser::where('id', $request->carduser)->first();
            $card = new Card();
            $card->名刺ユーザーID = $carduser->id;
            $card->最新フラグ = 1;
            $past_card = Card::where('名刺ユーザーID', $carduser->id)->update(['最新フラグ' => 0]);
            $carduser->表示名 = $request->name;
            $carduser->表示名カナ = $request->name_kana;
            $carduser->save();
        } else {
            $carduser = new Carduser();
            $carduser->表示名 = $request->name;
            $carduser->表示名カナ = $request->name_kana;
            $carduser->save();

            $card = new Card();
            $card->名刺ユーザーID = $carduser->id;
            $card->最新フラグ = 1;
        }

        $card->名前 = $request->name;
        $card->名前カナ = $request->name_kana;
        $card->携帯電話番号 = $request->phone_number;
        $card->メールアドレス = $request->email;
        $card->役職 = $request->position;
        $card->拠点ID = $branch->id;
        $card->会社ID = $company->id;


        // 名刺表面の切り取ったblob画像がある場合
        if ($request->hasFile('front_blob-image')) {
            if ($server == 'onpre') {
                $extension = $request->file('front_blob-image')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                $filepath = Config::get('custom.file_upload_path');
                // ファイルを保存
                $request->file('front_blob-image')->move($filepath, $filename);

                $card->名刺ファイル表 = $filename;
            } else if ($server == 'cloud') {
                $extension = $request->file('front_blob-image')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                // S3にファイルを保存
                Storage::disk('s3')->putFileAs(
                    $prefix,
                    $request->file('front_blob-image'),
                    $filename,
                    'private'
                );


                $card->名刺ファイル表 = $filename;
            }
        }
        // 名刺表面の切り取られていない画像がある場合
        else if ($request->hasFile('card_file_front')) {
            if ($server == 'onpre') {
                $extension = $request->file('card_file_front')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                $filepath = Config::get('custom.file_upload_path');
                // ファイルを保存
                $request->file('card_file_front')->move($filepath, $filename);

                $card->名刺ファイル表 = $filename;
            } else if ($server == 'cloud') {
                $extension = $request->file('card_file_front')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;

                // S3にファイルを保存
                Storage::disk('s3')->putFileAs(
                    $prefix,
                    $request->file('card_file_front'),
                    $filename,
                    'private'
                );
                $card->名刺ファイル表 = $filename;
            }
        }

        // 名刺裏面の切り取ったblob画像がある場合
        if ($request->hasFile('back_blob-image')) {
            if ($server == 'onpre') {
                $extension = $request->file('back_blob-image')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                $filepath = Config::get('custom.file_upload_path');
                // ファイルを保存
                $request->file('back_blob-image')->move($filepath, $filename);

                $card->名刺ファイル裏 = $filename;
            } else if ($server == 'cloud') {
                $extension = $request->file('back_blob-image')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                // S3にファイルを保存
                Storage::disk('s3')->putFileAs(
                    $prefix,
                    $request->file('back_blob-image'),
                    $filename,
                    'private'
                );


                $card->名刺ファイル裏 = $filename;
            }
        }
        // 名刺裏面の切り取られていない画像がある場合
        else if ($request->hasFile('card_file_back')) {
            if ($server == 'onpre') {
                $extension = $request->file('card_file_back')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;
                $filepath = Config::get('custom.file_upload_path');
                // ファイルを保存
                $request->file('card_file_back')->move($filepath, $filename);

                $card->名刺ファイル裏 = $filename;
            } else if ($server == 'cloud') {
                $extension = $request->file('card_file_back')->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;

                // S3にファイルを保存
                Storage::disk('s3')->putFileAs(
                    $prefix,
                    $request->file('card_file_back'),
                    $filename,
                    'private'
                );
                $card->名刺ファイル裏 = $filename;
            }
        }
        // 名刺裏面がない場合はnullにする
        if (!$request->hasFile('back_blob-image') && !$request->hasFile('card_file_back')) {
            $card->名刺ファイル裏 = null;
        }



        $card->save();



        $department_number = 1;
        $upper_department_id = null;
        if ($edit == 'edit') {
            $card_department = Card_Department::where('名刺ID', $card->id)->delete();
        }
        while ($request->has('department' . $department_number)) {
            // 部署名が入力されている場合
            if ($request->input('department' . $department_number) != '') {
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


                $card_department = new Card_Department();
                $card_department->名刺ID = $card->id;
                $card_department->部署ID = $department->id;
                $card_department->save();
            }
            // 次の部署番号に進む
            $department_number++;
        }

        if ($edit == 'edit') {
            return redirect()->route('carddetailget', ['id' => $carduser->id])->with('success', '名刺を更新しました。');
        } else {
            return redirect()->route('cardregistget')->with('success', '名刺を登録しました。');
        }
    }
    public function cardocrpost(Request $request)
    {
        $server = config('prefix.server');

        // 画像ファイルを保存
        if ($request->hasFile('image') || $request->hasFile('blob-image')) {


            try {
                // 切り取ったblob画像の場合
                if ($request->hasFile('blob-image')) {
                    $imageFile  = $request->file('blob-image');
                } else {
                    $imageFile = $request->file('image');
                }


                if ($server == 'onpre') {
                    $filename = 'temp/' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                    // Wasabiにファイルを保存
                    $path = Storage::disk('wasabi')->putFileAs('', $imageFile, $filename, 'public');

                    // プリサインドURLを生成（5分間アクセス可能）
                    $imageUrl = Storage::disk('wasabi')->temporaryUrl(
                        $path,
                        now()->addMinutes(5)
                    );


                    // Gemini APIを使用
                    $imageBase64 = base64_encode(file_get_contents($imageFile->getRealPath()));
                    $mimeType = $imageFile->getMimeType(); // e.g., image/jpeg
                    $apiKey = config('gemini.api_key');
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

                    $prompt = <<<PROMPT
                                以下の画像（またはテキスト）をOCR解析し、名刺情報を出力フォーマットに従ってJSON形式で返してください。

                                        - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
                                        - 郵便番号、電話番号、FAX番号、携帯電話番号は数字のみを抽出してください。
                                        - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
                                        - 「名前カナ」や「会社名カナ」には推測して必ずカタカナで入力してください。
                                        - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
                                        - 「名前カナ」には名前の漢字、読み仮名やメールアドレスのスペルなどから推測してください。
                                        - 本社、支社、本店、支店、工場など住所が紐づく拠点などがある場合は「拠点名」に入力してください。

                                        出力フォーマット:
                                        {
                                            \"名前\": \"\",
                                            \"名前カナ\": \"\",
                                            \"会社名\": \"\",
                                            \"会社名カナ\": \"\",
                                            \"役職\": \"\",
                                            \"部署1\": \"\",
                                            \"部署2\": \"\",
                                            \"メールアドレス\": \"\",
                                            \"携帯電話番号\": \"\",
                                            \"電話番号\": \"\",
                                            \"FAX番号\": \"\",
                                            \"住所\": \"\",
                                            \"郵便番号\": \"\",
                                            \"拠点名\": \"\"
                                        }
                                PROMPT;

                    $response = Http::post($url, [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inlineData' => [
                                            'mimeType' => $mimeType,
                                            'data' => $imageBase64,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]);

                    if (!$response->ok()) {
                        return response()->json(['error' => 'Gemini API request failed'], 500);
                    }
                    $geminiReply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    return response()->json([
                        'status' => 'success',
                        'data' => $geminiReply,
                    ]);
                    // // Google Cloud Vision APIを使用
                    // $imageAnnotator = new ImageAnnotatorClient();
                    // $imageData = file_get_contents(storage_path('app/' . $path));
                    // $response = $imageAnnotator->documentTextDetection($imageData);
                    // $annotation = $response->getFullTextAnnotation();
                    // $rawText = $annotation ? $annotation->getText() : '';
                    // $imageAnnotator->close();

                    // // OpenAI に JSON 変換を依頼
                    // $aiResponse = OpenAI::chat()->create([
                    //     'model' => 'gpt-3.5-turbo',
                    //     'messages' => [
                    //         ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                    //         ['role' => 'user', 'content' => $this->getJsonPrompt($rawText)],
                    //     ],
                    // ]);

                    // $structuredData = json_decode($aiResponse->choices[0]->message->content, true);

                    // $aiResponse = OpenAI::chat()->create([
                    //     'model' => 'gpt-4o-mini',
                    //     'messages' => [
                    //         ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                    //         [
                    //             'role' => 'user',
                    //             'content' => [
                    //                 [
                    //                     "type" => "text",
                    //                     "text" => $this->getJsonPrompt() // プロンプトの内容
                    //                 ],
                    //                 [
                    //                     "type" => "image_url",
                    //                     "image_url" => [
                    //                         "url" => $imageUrl,
                    //                     ]
                    //                 ]
                    //             ]
                    //         ],
                    //     ]
                    // ]);
                    // $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
                    // $structuredData = json_decode($jsonString, true);


                    // 画像ファイルを削除
                    if ($request->hasFile('image') || $request->hasFile('blob-image')) {
                        // Wasabiのストレージからファイルを削除
                        Storage::disk('wasabi')->delete($path);
                    }
                } else if ($server == 'cloud') {

                    // 一時的にパブリックストレージに保存
                    $path = $imageFile->store('public/temp_business_cards');
                    $imageUrl = asset(config('prefix.prefix') . '/' . str_replace('public/', 'storage/', $path));

                    // Cloud 環境: OpenAI に画像URLを直接送信し、一発で JSON を返す
                    $aiResponse = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        "type" => "text",
                                        "text" => $this->getJsonPrompt() // プロンプトの内容
                                    ],
                                    [
                                        "type" => "image_url",
                                        "image_url" => [
                                            "url" => $imageUrl,
                                        ]
                                    ]
                                ]
                            ],
                        ]
                    ]);
                    $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
                    $structuredData = json_decode($jsonString, true);

                    // 画像ファイルを削除
                    if ($request->hasFile('image') || $request->hasFile('blob-image')) {
                        Storage::delete($path);
                    }
                } else {
                    return response()->json(['status' => 'error', 'message' => 'サーバー種別が不明です。']);
                }
                $token = $aiResponse->usage;
                // 保存やレスポンスとして返す処理
                return response()->json([
                    'status' => 'success',
                    'data' => $structuredData,
                    'token' => $token
                ]);
                // }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            } finally {
                // 画像ファイルを削除
                if ($request->hasFile('image') || $request->hasFile('blob-image')) {
                    Storage::delete($path);
                }
            }
        }

        return response()->json(['status' => 'error', 'message' => '画像がアップロードされていません。']);
    }

    /**
     * OpenAI に送る JSON 変換用プロンプト
     */
    private function getJsonPrompt($rawText = null)
    {
        return "
        以下の画像（またはテキスト）をOCR解析し、名刺情報を出力フォーマットに従ってJSON形式で返してください。

        - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
        - 郵便番号、電話番号、FAX番号、携帯電話番号は数字のみを抽出してください。
        - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
        - 「名前カナ」や「会社名カナ」には推測して必ずカタカナで入力してください。
        - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
        - 「名前カナ」には名前の漢字、読み仮名やメールアドレスのスペルなどから推測してください。
        - 本社、支社、本店、支店、工場など住所が紐づく拠点などがある場合は「拠点名」に入力してください。

        出力フォーマット:
        {
            \"名前\": \"\",
            \"名前カナ\": \"\",
            \"会社名\": \"\",
            \"会社名カナ\": \"\",
            \"役職\": \"\",
            \"部署1\": \"\",
            \"部署2\": \"\",
            \"メールアドレス\": \"\",
            \"携帯電話番号\": \"\",
            \"電話番号\": \"\",
            \"FAX番号\": \"\",
            \"住所\": \"\",
            \"郵便番号\": \"\",
            \"拠点名\": \"\"
        }

        " . ($rawText ? "名刺テキスト:\n{$rawText}" : "画像を解析してください。");
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
        if ($front == 'front') {
            $filepath = $card->名刺ファイル表;
        } else if ($front == 'back') {
            $filepath = $card->名刺ファイル裏;
        } else {
            $filepath = $card->名刺ファイル表;
        }
        // pathinfo関数を使用して拡張子を取得
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = 'astdocs.com';
            $key = config('prefix.prefix') . '/' . $filepath;
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
            if ($card) {
                $company->card = $card;
            } else {
                $companies = $companies->except($company->id);
            }
        }
        return response()->json($companies);
    }

    public function companyinfoget(Request $request, $id)
    {
        $candidate_branch = $request->candidate_branch;
        $company = Company::where('id', $id)->first();
        $branches = Branch::where('会社ID', $id)->get();
        $designate_branch = Branch::where('会社ID', $id)->where('拠点指定', 1)->first() ? true : false;
        $candidate_branch = Branch::where('会社ID', $id)->where('拠点指定', 1)->where('拠点名', $candidate_branch)->first() ?? null;
        return response()->json([
            'company' => $company,
            'branches' => $branches,
            'designate_branch' => $designate_branch,
            'candidate_branch' => $candidate_branch
        ]);
    }

    public function cardtestget()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('card.test', compact('prefix', 'server'));
    }
    public function cardmultiplepastget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $filename = $request->filename;
        // 拡張子を除いたファイル名
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // フラグの初期化
        $hasTrailingNumber = false;



        // _数字が末尾にあるかをチェックして削除
        if (preg_match('/_\d+$/', $basename)) {
            $hasTrailingNumber = true;
            $cleanedName = preg_replace('/_\d+$/', '', $basename);
        } else {
            $cleanedName = $basename;
        }
        $file = UploadedCard::where('ファイル名', $cleanedName)->first();

        // フラグがfalseの場合(表面候補)
        if (!$hasTrailingNumber) {
            if (!$file) {
                // ファイルが存在しない場合
                // DB登録
                $uploaded_card = UploadedCard::create([
                    'front_url' => 'not_uploaded',
                    'back_url' => 'not_uploaded', // 裏面は無し
                    'status' => 'pending',
                    'openai_response' => null,
                    'upload_id' => $request->upload_id,
                    'ファイル名' => $cleanedName
                ]);
                return response()->json([
                    'status' => 'new',
                    'uploaded_card_id' => $uploaded_card->id,
                    'filename' => $cleanedName
                ]);
            } else {
                // 表面が未登録の場合(裏面のみが登録されているとき)
                if ($file->front_url == 'not_uploaded') {
                    return response()->json([
                        'status' => 'add_front',
                        'uploaded_card_id' => $file->id,
                        'filename' => $cleanedName
                    ]);
                }
                // 表面が登録済みの場合
                else {
                    return response()->json([
                        'status' => 'skip',
                    ]);
                }
            }
        }
        // フラグがtrueの場合(裏面候補)
        else {
            if (!$file) {
                // DB登録
                $uploaded_card = UploadedCard::create([
                    'front_url' => 'not_uploaded',
                    'back_url' => 'not_uploaded',
                    'status' => 'pending',
                    'openai_response' => null,
                    'upload_id' => $request->upload_id,
                    'ファイル名' => $cleanedName
                ]);
                return response()->json([
                    'status' => 'new_back',
                    'uploaded_card_id' => $uploaded_card->id,
                    'filename' => $cleanedName
                ]);
            } else {
                // 裏面が未登録の場合(表面のみが登録されているとき)
                if ($file->back_url == 'not_uploaded') {
                    return response()->json([
                        'status' => 'back',
                        'uploaded_card_id' => $file->id,
                        'filename' => $cleanedName
                    ]);
                }
                // 裏面が登録済みの場合
                else {
                    return response()->json([
                        'status' => 'skip',
                    ]);
                }
            }
        }
    }
    public function cardmultipleuploadget()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('card.cardmultiple_upload', compact('prefix', 'server'));
    }
    public function cardmultipleuploadpost(Request $request)
    {
        $server = config('prefix.server');
        if (!$request->hasFile('cards')) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded.'], 400);
        }

        $file = $request->file('cards');
        $extension = $file->getClientOriginalExtension();
        $filename = $request->filename . '.' . $extension;
        $key = 'cards/' . now()->format('Y_m_d') . '/' . uniqid() . '_' . $filename;
        // Wasabiにファイルを保存
        $path = Storage::disk('wasabi')->putFileAs('', $file, $key, 'public');

        // プリサインドURLを生成（5分間アクセス可能）
        $url = Storage::disk('wasabi')->temporaryUrl(
            $path,
            now()->addMinutes(5)
        );

        if ($request->status == 'new' || $request->status == 'add_front') {
            $uploaded_card = UploadedCard::find($request->uploaded_card_id);
            $uploaded_card->front_url = $url;
            $uploaded_card->status = 'pending';
            $uploaded_card->save();
            return response()->json([
                'status' => 'front_success',
                'uploaded_card_id' => $uploaded_card->id
            ]);
        }
        // 裏面の新規登録の場合
        else if ($request->status == 'new_back' || $request->status == 'back') {
            $uploaded_card = UploadedCard::find($request->uploaded_card_id);
            $uploaded_card->back_url = $url;
            $uploaded_card->save();
            if ($server == 'onpre') {
                $wasabiUrl = $uploaded_card->back_url;

                $filename = $this->generateRandomCode() . "." . $extension;

                $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                if (!is_dir($filepath)) {
                    mkdir($filepath, 0755, true); // フォルダがなければ作る
                }
                // 画像をダウンロード
                $imageData = file_get_contents($wasabiUrl);
                if ($imageData === false) {
                    throw new \Exception('ファイルのダウンロードに失敗しました。');
                }

                // フルパス組み立て
                $fullPath = rtrim($filepath, '/') . '/' . $filename;

                // ファイルを保存
                file_put_contents($fullPath, $imageData);
                if ($request->status == 'back') {
                    $card = Card::find($uploaded_card->名刺ID);
                    if ($card) {
                        $card->名刺ファイル裏 = $filename;
                        $card->save();
                    }
                }
            }

            return response()->json([
                'status' => 'back_success',
                'uploaded_card_id' => $uploaded_card->id
            ]);
        }
        // // 表面のみが登録されている場合
        // else if ($request->status == 'back') {
        //     $uploaded_card = UploadedCard::find($request->uploaded_card_id);
        //     $uploaded_card->back_url = $url;
        //     $uploaded_card->save();

        //     if ($server == 'onpre') {
        //         $wasabiUrl = $uploaded_card->back_url;

        //         $filename = $this->generateRandomCode() . "." . $extension;

        //         $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
        //         if (!is_dir($filepath)) {
        //             mkdir($filepath, 0755, true); // フォルダがなければ作る
        //         }
        //         // 画像をダウンロード
        //         $imageData = file_get_contents($wasabiUrl);
        //         if ($imageData === false) {
        //             throw new \Exception('ファイルのダウンロードに失敗しました。');
        //         }

        //         // フルパス組み立て
        //         $fullPath = rtrim($filepath, '/') . '/' . $filename;

        //         // ファイルを保存
        //         file_put_contents($fullPath, $imageData);

        //     }

        //     return response()->json([
        //         'status' => 'back_success',
        //         'uploaded_card_id' => $uploaded_card->id
        //     ]);
        // }
    }
    public function cardopenai(Request $request)
    {
        $server = config('prefix.server');
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }

        $card = UploadedCard::find($request->uploaded_card_id);
        if (!$card || $card->status !== 'pending') {
            return;
        }
        $card->status = 'processing';
        $card->save();


        $imageUrl = $card->front_url;


        // リトライ回数の上限を設定
        $maxRetries = 3;
        $retryCount = 0;
        $structuredData = null;

        while ($retryCount < $maxRetries) {
            $queue = $this->getStartTimeBasedOnTokenLimit();
            $queue_id = $queue[1];
            sleep($queue[0]);
            try {
                $aiResponse = OpenAI::chat()->create([
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    "type" => "text",
                                    "text" => $this->getJsonPrompt() // プロンプトの内容
                                ],
                                [
                                    "type" => "image_url",
                                    "image_url" => [
                                        "url" => $imageUrl,
                                    ]
                                ]
                            ]
                        ],
                    ]
                ]);
                $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
                $structuredData = json_decode($jsonString, true);
                if (
                    isset($structuredData['名前']) && $structuredData['名前'] !== '' &&
                    isset($structuredData['会社名']) && $structuredData['会社名'] !== ''
                ) {
                    break; // 成功時はループを抜ける
                }
            } catch (\Exception $e) {
                $queue = OpenaiQueue::find($queue_id);
                $queue->トークン = 2000;
                $queue->save();
                // ログ出力する場合：
                \Log::warning("OpenAI retry {$retryCount}: updated_card_id: " . $card->id . " " . $e->getMessage());
            }
            $retryCount++;
            sleep(10); // API連続呼び出し防止のため1秒待機
        }
        if ($retryCount >= $maxRetries) {
            $card->status = 'failed';
            $card->save();
            return response()->json([
                'status' => 'error',
                'message' => 'OpenAIの処理に失敗しました。',
            ]);
        }



        if ($server == 'onpre') {
            $wasabiFrontUrl = $card->front_url;
            // クエリ部分（?以降）を除外
            $parsedUrl = parse_url($wasabiFrontUrl);
            $path = $parsedUrl['path'] ?? '';

            // pathinfoで拡張子を取得
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $frontFilename = $this->generateRandomCode() . "." . $extension;

            $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
            if (!is_dir($filepath)) {
                mkdir($filepath, 0755, true); // フォルダがなければ作る
            }
            // 画像をダウンロード
            $imageData = file_get_contents($wasabiFrontUrl);
            if ($imageData === false) {
                throw new \Exception('ファイルのダウンロードに失敗しました。');
            }

            // フルパス組み立て
            $fullPath = rtrim($filepath, '/') . '/' . $frontFilename;

            // ファイルを保存
            file_put_contents($fullPath, $imageData);


            $backFilename = null;
            if ($card->back_url != 'not_uploaded') {
                $wasabiBackUrl = $card->back_url;
                $parsedUrl = parse_url($wasabiBackUrl);
                $path = $parsedUrl['path'] ?? '';

                // pathinfoで拡張子を取得
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $backFilename = $this->generateRandomCode() . "." . $extension;

                $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                if (!is_dir($filepath)) {
                    mkdir($filepath, 0755, true); // フォルダがなければ作る
                }
                // 画像をダウンロード
                $imageData = file_get_contents($wasabiBackUrl);
                if ($imageData === false) {
                    throw new \Exception('ファイルのダウンロードに失敗しました。');
                }

                $fullPath = rtrim($filepath, '/') . '/' . $backFilename;

                // ファイルを保存
                file_put_contents($fullPath, $imageData);
            }
        }
        // クラウド



        $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
        if (!$carduser) {
            $carduser = new CardUser();
            $carduser->表示名 = $structuredData['名前'] ?? '';
            $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
            $carduser->save();

            $exist_cards = Card::where('名刺ユーザーID', $carduser->id)->get();
            foreach ($exist_cards as $exist_card) {
                $exist_card->最新フラグ = 0;
                $exist_card->save();
            }
        }
        $company = Company::where('会社名', $structuredData['会社名'])->first();
        if (!$company) {
            $company = new Company();
            $company->会社名 = $structuredData['会社名'] ?? '';
            $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
            $company->save();


            $branch = new Branch();
            $branch->会社ID = $company->id;
            if ($structuredData['拠点名']) {
                $branch->拠点名 = $structuredData['拠点名'];
                $branch->拠点指定 = true;
            } else {
                $branch->拠点名 = $structuredData['会社名'] ?? '';
                $branch->拠点指定 = false;
            }
            $branch->save();
        } else {
            // 拠点名があるかどうかで分岐
            if ($structuredData['拠点名']) {
                // すでに拠点が登録されているかを確認
                $branch = Branch::where('会社ID', $company->id)
                    ->where('拠点名', $structuredData['拠点名'])
                    ->first();

                // ない場合は新規登録
                if (!$branch) {
                    $branch = new Branch();
                    $branch->会社ID = $company->id;
                    $branch->拠点名 = $structuredData['拠点名'];
                    $branch->拠点指定 = true;
                    $branch->拠点所在地 = $structuredData['住所'] ?? '';
                    $branch->電話番号 = $structuredData['電話番号'] ?? '';
                    $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
                    $branch->save();
                }
            } else {
                // 拠点名がない場合は、拠点指定がfalseのものを取得
                $branch = Branch::where('会社ID', $company->id)
                    ->where('拠点指定', false)
                    ->first();
            }
        }
        $newcard = new Card();
        $newcard->名刺ユーザーID = $carduser->id;
        $newcard->会社ID = $company->id;
        $newcard->拠点ID = $branch->id;
        $newcard->名前 = $structuredData['名前'] ?? '';
        $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
        $newcard->役職 = $structuredData['役職'] ?? '';
        $newcard->名刺ファイル表 = $frontFilename;
        $newcard->名刺ファイル裏 = $backFilename;
        $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
        $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
        $newcard->save();


        if (isset($structuredData['部署1']) && $structuredData['部署1'] !== '') {
            // 部署登録
            $department_number = 1;
            $upper_department_id = null;
            while (isset($structuredData['部署' . $department_number]) && $structuredData['部署' . $department_number] !== '') {
                // 部署名が入力されている場合
                if ($structuredData['部署' . $department_number] != '') {
                    // 部署名を取得
                    $department_name = $structuredData['部署' . $department_number];

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


                    $card_department = new Card_Department();
                    $card_department->名刺ID = $newcard->id;
                    $card_department->部署ID = $department->id;
                    $card_department->save();
                }
                // 次の部署番号に進む
                $department_number++;
            }
        }


        $card->status = 'done';
        $card->名刺ID = $newcard->id;
        $card->save();
        return response()->json([
            'status' => 'success',
        ]);
    }
    public function cardopenaieachprocess(Request $request)
    {
        \Log::info('API called: ' . now()->format('H:i:s.u'));
        $server = config('prefix.server');
        $card = UploadedCard::find($request->card_id);
        if (!$card || $card->status !== 'pending') {
            return;
        }
        $card->status = 'processing';
        $card->save();


        $imageUrl = $card->front_url;
        $aiResponse = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                [
                    'role' => 'user',
                    'content' => [
                        [
                            "type" => "text",
                            "text" => $this->getJsonPrompt() // プロンプトの内容
                        ],
                        [
                            "type" => "image_url",
                            "image_url" => [
                                "url" => $imageUrl,
                            ]
                        ]
                    ]
                ],
            ]
        ]);
        $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
        $structuredData = json_decode($jsonString, true);
        if ($structuredData['名前'] == '' || $structuredData['会社名'] == '') {
            $card->status = 'done';
            $card->save();
            return;
        }
        if ($server == 'onpre') {
            $wasabiUrl = $card->front_url;
            // クエリ部分（?以降）を除外
            $parsedUrl = parse_url($wasabiUrl);
            $path = $parsedUrl['path'] ?? '';

            // pathinfoで拡張子を取得
            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $filename = $this->generateRandomCode() . "." . $extension;

            $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
            if (!is_dir($filepath)) {
                mkdir($filepath, 0755, true); // フォルダがなければ作る
            }
            // 画像をダウンロード
            $imageData = file_get_contents($wasabiUrl);
            if ($imageData === false) {
                throw new \Exception('ファイルのダウンロードに失敗しました。');
            }

            // フルパス組み立て
            $fullPath = rtrim($filepath, '/') . '/' . $filename;

            // ファイルを保存
            file_put_contents($fullPath, $imageData);
        }
        $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
        if (!$carduser) {
            $carduser = new CardUser();
            $carduser->表示名 = $structuredData['名前'] ?? '';
            $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
            $carduser->save();

            $exist_cards = Card::where('名刺ユーザーID', $carduser->id)->get();
            foreach ($exist_cards as $exist_card) {
                $exist_card->最新フラグ = 0;
                $exist_card->save();
            }
        }
        $company = Company::where('会社名', $structuredData['会社名'])->first();
        if (!$company) {
            $company = new Company();
            $company->会社名 = $structuredData['会社名'] ?? '';
            $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
            $company->save();


            $branch = new Branch();
            $branch->会社ID = $company->id;
            if ($structuredData['拠点名']) {
                $branch->拠点名 = $structuredData['拠点名'];
                $branch->拠点指定 = true;
            } else {
                $branch->拠点名 = $structuredData['会社名'] ?? '';
                $branch->拠点指定 = false;
            }
            $branch->save();
        } else {
            // 拠点名があるかどうかで分岐
            if ($structuredData['拠点名']) {
                // すでに拠点が登録されているかを確認
                $branch = Branch::where('会社ID', $company->id)
                    ->where('拠点名', $structuredData['拠点名'])
                    ->first();

                // ない場合は新規登録
                if (!$branch) {
                    $branch = new Branch();
                    $branch->会社ID = $company->id;
                    $branch->拠点名 = $structuredData['拠点名'];
                    $branch->拠点指定 = true;
                    $branch->拠点所在地 = $structuredData['住所'] ?? '';
                    $branch->電話番号 = $structuredData['電話番号'] ?? '';
                    $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
                    $branch->save();
                }
            } else {
                // 拠点名がない場合は、拠点指定がfalseのものを取得
                $branch = Branch::where('会社ID', $company->id)
                    ->where('拠点指定', false)
                    ->first();
            }
        }
        $newcard = new Card();
        $newcard->名刺ユーザーID = $carduser->id;
        $newcard->会社ID = $company->id;
        $newcard->拠点ID = $branch->id;
        $newcard->名前 = $structuredData['名前'] ?? '';
        $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
        $newcard->役職 = $structuredData['役職'] ?? '';
        $newcard->名刺ファイル表 = $filename;
        $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
        $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
        $newcard->save();

        // 部署登録
        // $department_number = 1;
        // $upper_department_id = null;
        // while ($structuredData['部署' . $department_number]) {
        //     // 部署名が入力されている場合
        //     if ($structuredData['部署' . $department_number] != '') {
        //         // 部署名を取得
        //         $department_name = $structuredData['部署' . $department_number];

        //         $existing_department = Department::where('部署名', $department_name)
        //             ->where('会社ID', $company->id)
        //             ->first();
        //         if ($existing_department) {
        //             $department = $existing_department;
        //         } else {
        //             $department = new Department();
        //             $department->会社ID = $company->id;
        //             $department->部署名 = $department_name;

        //             // 上位部署IDを設定（最初の部署以外）
        //             if ($department_number != 1) {
        //                 $department->上位部署ID = $upper_department_id;
        //             }
        //         }

        //         // 部署データを保存
        //         $department->save();
        //         // 上位部署IDを取得
        //         $upper_department_id = $department->id;


        //         $card_department = new Card_Department();
        //         $card_department->名刺ID = $newcard->id;
        //         $card_department->部署ID = $department->id;
        //         $card_department->save();
        //     }
        //     // 次の部署番号に進む
        //     $department_number++;
        // }




        $card->status = 'done';
        $card->save();
    }

    // public function cardmultipletestget(Request $request)
    // {
    //     $uploadId = $request->input('upload_id');
    //     $server = config('prefix.server');

    //     // このupload_idに紐づくUploadedCardを取ってくる
    //     $cards = UploadedCard::where('upload_id', $uploadId)
    //         ->where('status', 'pending') // まだ未処理のものだけ
    //         ->get();

    //     foreach ($cards as $card) {
    //         $imageUrl = $card->front_url;
    //         $aiResponse = OpenAI::chat()->create([
    //             'model' => 'gpt-4o-mini',
    //             'messages' => [
    //                 ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
    //                 [
    //                     'role' => 'user',
    //                     'content' => [
    //                         [
    //                             "type" => "text",
    //                             "text" => $this->getJsonPrompt() // プロンプトの内容
    //                         ],
    //                         [
    //                             "type" => "image_url",
    //                             "image_url" => [
    //                                 "url" => $imageUrl,
    //                             ]
    //                         ]
    //                     ]
    //                 ],
    //             ]
    //         ]);
    //         $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
    //         $structuredData = json_decode($jsonString, true);

    //         if ($server == 'onpre') {
    //             $wasabiUrl = $card->front_url;
    //             // クエリ部分（?以降）を除外
    //             $parsedUrl = parse_url($wasabiUrl);
    //             $path = $parsedUrl['path'] ?? '';

    //             // pathinfoで拡張子を取得
    //             $extension = pathinfo($path, PATHINFO_EXTENSION);
    //             $filename = $this->generateRandomCode() . "." . $extension;

    //             $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
    //             if (!is_dir($filepath)) {
    //                 mkdir($filepath, 0755, true); // フォルダがなければ作る
    //             }
    //             // 画像をダウンロード
    //             $imageData = file_get_contents($wasabiUrl);
    //             if ($imageData === false) {
    //                 throw new \Exception('ファイルのダウンロードに失敗しました。');
    //             }

    //             // フルパス組み立て
    //             $fullPath = rtrim($filepath, '/') . '/' . $filename;

    //             // ファイルを保存
    //             file_put_contents($fullPath, $imageData);
    //         }
    //         $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
    //         if (!$carduser) {
    //             $carduser = new CardUser();
    //             $carduser->表示名 = $structuredData['名前'] ?? '';
    //             $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
    //             $carduser->save();
    //         }
    //         $company = Company::where('会社名', $structuredData['会社名'])->first();
    //         if (!$company) {
    //             $company = new Company();
    //             $company->会社名 = $structuredData['会社名'] ?? '';
    //             $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
    //             $company->save();


    //             $branch = new Branch();
    //             $branch->会社ID = $company->id;
    //             if ($structuredData['拠点名']) {
    //                 $branch->拠点名 = $structuredData['拠点名'];
    //                 $branch->拠点指定 = true;
    //             } else {
    //                 $branch->拠点名 = $structuredData['会社名'] ?? '';
    //                 $branch->拠点指定 = false;
    //             }
    //             $branch->save();
    //         } else {
    //             $branch = Branch::where('会社ID', $company->id)
    //                 ->where('拠点名', $structuredData['拠点名'])
    //                 ->first();
    //             if (!$branch) {
    //                 $branch = new Branch();
    //                 $branch->会社ID = $company->id;
    //                 if ($structuredData['拠点名']) {
    //                     $branch->拠点名 = $structuredData['拠点名'];
    //                     $branch->拠点指定 = true;
    //                 } else {
    //                     $branch->拠点名 = $structuredData['会社名'];
    //                     $branch->拠点指定 = false;
    //                 }
    //                 $branch->拠点所在地 = $structuredData['住所'] ?? '';
    //                 $branch->電話番号 = $structuredData['電話番号'] ?? '';
    //                 $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
    //                 $branch->save();
    //             }
    //         }
    //         $newcard = new Card();
    //         $newcard->名刺ユーザーID = $carduser->id;
    //         $newcard->会社ID = $company->id;
    //         $newcard->拠点ID = $branch->id;
    //         $newcard->名前 = $structuredData['名前'] ?? '';
    //         $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
    //         $newcard->役職 = $structuredData['役職'] ?? '';
    //         $newcard->名刺ファイル表 = $filename;
    //         $newcard->save();
    //     }
    // }

    public function cardmultipleprogressget(Request $request)
    {
        $uploadId = $request->input('upload_id');
        $cards = UploadedCard::where('upload_id', $uploadId)->get();
        $pendingCount = $cards->where('status', 'pending')->count();
        $processingCount = $cards->where('status', 'processing')->count();
        $notdoneCount = $pendingCount + $processingCount;
        $doneCount = $cards->where('status', 'done')->count();
        return response()->json([
            'pending' => $pendingCount,
            'processing' => $processingCount,
            'notdone' => $notdoneCount,
            'done' => $doneCount,
            'total' => $cards->count(),
        ]);
    }

    public function getStartTimeBasedOnTokenLimit()
    {
        $limitPerMinute = 200000;
        $uploadtoken = 40000;

        $now = Carbon::now();
        $oneMinuteAgo = $now->copy()->subMinute();

        // 直近1分間のトークン合計
        $usedTokens = OpenaiQueue::where('開始時刻', '>=', $oneMinuteAgo)
            ->sum('トークン');

        // 上限未満なら今すぐOK
        if ($usedTokens + $uploadtoken < $limitPerMinute) {
            $start = $now->copy()->addSeconds(5);

            $openaiqueue = new OpenaiQueue();
            $openaiqueue->トークン = $uploadtoken;
            $openaiqueue->開始時刻 = $start;
            $openaiqueue->save();

            return [1, $openaiqueue->id];
        }

        // 上限超え → 使用済みの各記録を取得して、最短で処理できる時刻を算出
        $queue = OpenaiQueue::where('開始時刻', '>=', $oneMinuteAgo)
            ->orderBy('開始時刻', 'asc')
            ->get();

        $total = 0;
        foreach ($queue as $record) {
            $total += $record->トークン;

            // この時点で超えるなら、このレコードの開始時刻 + 65秒が空きタイミング
            if ($total + $uploadtoken >= $limitPerMinute) {
                $start = Carbon::parse($record->開始時刻)->addSeconds(65);

                $openaiqueue = new OpenaiQueue();
                $openaiqueue->トークン = $uploadtoken;
                $openaiqueue->開始時刻 = $start;
                $openaiqueue->save();

                return [$start->diffInSeconds($now), $openaiqueue->id];
            }
        }
        // 通常はここに来ないが念のため
        return $now->addSeconds(5);
    }
}
