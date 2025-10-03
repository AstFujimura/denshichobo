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
// use Intervention\Image\Facades\Image;
use claviska\SimpleImage;
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
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class CardController extends Controller
{
    public function cardviewget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $userId = Auth::id();

        $perPage = 100;
        $page = $request->input('page', 1);

        // サブクエリ
        $sub = DB::table('cards')
            ->select(
                'cards.*',
                DB::raw("
                    ROW_NUMBER() OVER (
                        PARTITION BY 名刺ユーザーID
                        ORDER BY
                            CASE WHEN ユーザーID = {$userId} THEN 1 ELSE 0 END DESC,
                            最新フラグ DESC,
                            id ASC
                    ) as row_num
                ")
            );
        $sort = $request->input('sort', 1);
        $search = $request->input('search', '');
        $start_date = $request->input('start_date') ?: '1900-01-01';
        $end_date   = $request->input('end_date')   ?: '2100-12-31';

        $query = DB::table('cardusers')
            ->select(
                'cardusers.id as carduser_id',
                'cardusers.表示名',
                'latest_cards.id as card_id',
                'latest_cards.*',
                'latest_cards.created_at as 登録年月日',
                'latest_cards.updated_at as 更新年月日',
                'companies.*'
            )
            ->joinSub($sub, 'latest_cards', function ($join) {
                $join->on('cardusers.id', '=', 'latest_cards.名刺ユーザーID')
                    ->where('latest_cards.row_num', '=', 1);
            })
            ->leftJoin('companies', 'latest_cards.会社ID', '=', 'companies.id');

        // 検索条件
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cardusers.表示名', 'like', "%{$search}%")
                    ->orWhere('cardusers.表示名カナ', 'like', "%{$search}%")
                    ->orWhere('companies.会社名', 'like', "%{$search}%")
                    ->orWhere('companies.会社名カナ', 'like', "%{$search}%");
            });
        }

        $query->whereBetween('latest_cards.created_at', [$start_date, $end_date]);

        // ソート条件
        if ($sort == 1) {
            $query->orderBy('cardusers.表示名カナ', 'asc');
        } elseif ($sort == 2) {
            $query->orderBy('companies.会社名カナ', 'asc');
        } elseif ($sort == 3) {
            $query->orderBy('latest_cards.created_at', 'desc');
        } elseif ($sort == 4) {
            $query->orderBy('latest_cards.updated_at', 'desc');
        }
        $totalCount = $query->count(); // 検索条件に合致する総件数

        // マイ名刺件数（ユーザーIDが自分のもの）
        $myCount = (clone $query)->where('latest_cards.ユーザーID', $userId)->count();

        $cardusers = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();


        // 部署をまとめて取得（N+1防止）
        $deptMap = DB::table('card_department')
            ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
            ->get()
            ->groupBy('名刺ID');

        foreach ($cardusers as $carduser) {
            $carduser->departments = $deptMap->get($carduser->card_id) ?? collect();

            $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->carduser_id)
                ->where('ユーザーID', $userId)
                ->first();

            $carduser->マイ名刺ユーザー = ($carduser->ユーザーID == $userId) ? "true" : "false";
            $carduser->お気に入りユーザー = ($carduser_user && $carduser_user->お気に入りユーザー == 1) ? "true" : "false";
        }

        // Ajaxなら部分ビューだけ返す
        if ($request->ajax()) {
            // return view('card.partials.cardlist', compact('cardusers'));
            return response()->json([
                'html' => view('card.partials.cardlist', compact('cardusers'))->render(),
                'total' => $totalCount,
                'myCount' => $myCount,
            ]);
        }

        // 初回ロードはフルビュー
        return view('card.cardview', compact("prefix", "server", "cardusers", "totalCount", "myCount"));
    }

    public function cardviewsizeget($size)
    {
        $user = User::find(Auth::user()->id);
        if ($size == "large_view") {
            $user->名刺表示サイズ = true;
        } else if ($size == "small_view") {
            $user->名刺表示サイズ = false;
        }
        $user->save();
        return response()->json(['success' => $user->名刺表示サイズ]);
    }

    // 他のユーザーの名刺があるかどうかをチェック
    public function otherusercardcheckget(Request $request, $user_id)
    {
        $cards = DB::table('cards')
            ->leftJoin('users', 'cards.ユーザーID', '=', 'users.id')
            ->select(
                'cards.id as card_id',
                'cards.名刺ユーザーID',
                'cards.ユーザーID',
                'cards.最新フラグ',
                'users.表示名',
                DB::raw('ROW_NUMBER() OVER (
                PARTITION BY 名刺ユーザーID
                ORDER BY 最新フラグ DESC, cards.id ASC
            ) as row_num')
            )
            ->where('削除', '!=', '削除')
            ->where('cards.ユーザーID', '!=', $user_id)
            ->orderBy('名刺ユーザーID', 'asc')
            ->orderBy('最新フラグ', 'desc')
            ->get()
            ->filter(fn($row) => $row->row_num == 1) // Laravelコレクションで1位だけ残す
            ->values();
            
        return response()->json($cards);
    }

    public function cardcompanyeditget(Request $request, $company_id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $company = Company::find($company_id);
        $departments = Department::where('会社ID', $company_id)->get();
        $branches = Branch::where('会社ID', $company_id)->get();
        return view('card.cardcompanyedit', compact("prefix", "server", "company", "departments", "branches"));
    }
    public function cardcompanyeditpost(Request $request)
    {
        $company_id = $request->company_id;


        $company = Company::find($company_id);
        $company->会社名 = $request->company_name;
        $company->会社名カナ = $request->company_name_kana;
        $company->save();

        $department_array = $request->department_name ?? [];
        $branch_array = $request->branch ?? [];

        $department_keys = array_keys($department_array);
        $branch_keys = array_keys($branch_array);

        // 部署削除（イベント無効化で安全に）
        Department::withoutEvents(function () use ($department_keys, $company_id) {
            Department::whereNotIn('id', $department_keys)
                ->where('会社ID', $company_id)
                ->delete();
        });

        // 拠点削除とカード移動
        $delete_branches = Branch::whereNotIn('id', $branch_keys)
            ->where('会社ID', $company_id)
            ->where('拠点指定', 1)
            ->get();

        $notdesignate_branch = Branch::where('会社ID', $company_id)
            ->where('拠点指定', 0)
            ->first();
        if (!$notdesignate_branch) {
            $notdesignate_branch = new Branch();
            $notdesignate_branch->会社ID = $company_id;
            $notdesignate_branch->拠点名 = $company->会社名;
            $notdesignate_branch->拠点所在地 = "";
            $notdesignate_branch->電話番号 = "";
            $notdesignate_branch->FAX番号 = "";
            $notdesignate_branch->拠点指定 = 0;
            $notdesignate_branch->save();
        }

        foreach ($delete_branches as $delete_branch) {
            Card::where('拠点ID', $delete_branch->id)
                ->update(['拠点ID' => $notdesignate_branch->id]);
            $delete_branch->delete();
        }

        foreach ($department_keys as $department_key) {
            $department = Department::find($department_key);
            $department->部署名 = $department_array[$department_key];
            $department->save();
        }

        foreach ($branch_keys as $branch_key) {
            $branch = Branch::find($branch_key);
            $branch->拠点名 = $branch_array[$branch_key]['branch_name'];
            $branch->拠点所在地 = $branch_array[$branch_key]['branch_address'];
            $branch->電話番号 = $branch_array[$branch_key]['branch_tel'];
            $branch->FAX番号 = $branch_array[$branch_key]['branch_fax'];
            $branch->save();
        }

        // 新規部署追加
        if ($request->new_department_name) {
            foreach ($request->new_department_name as $deptName) {
                $dept = new Department();
                $dept->会社ID = $company_id;
                $dept->部署名 = $deptName;
                $dept->save();
            }
        }

        // 新規拠点追加
        if ($request->new_branch) {
            foreach ($request->new_branch as $branchData) {
                $branch = new Branch();
                $branch->会社ID = $company_id;
                $branch->拠点名 = $branchData['branch_name'];
                $branch->拠点所在地 = $branchData['branch_address'];
                $branch->電話番号 = $branchData['branch_tel'];
                $branch->FAX番号 = $branchData['branch_fax'];
                $branch->拠点指定 = 1;
                $branch->save();
            }
        }

        return redirect()->route('cardcompanyeditget', ['company_id' => $company_id])->with('success', '会社情報を更新しました。');
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
        // マイ名刺の最新フラグを取得
        $myLatest = Card::where('ユーザーID', Auth::id())
            ->where('名刺ユーザーID', $carduser->id)
            ->orderBy('最新フラグ', 'desc')
            ->first();

        if ($myLatest) {
            // 自分の名刺は全部取得
            $cards = DB::table('cards')
                ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
                ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
                ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
                ->where('cards.名刺ユーザーID', $carduser->id)
                ->where('cards.ユーザーID', Auth::id())
                ->orderBy('cards.最新フラグ', 'desc')
                ->get();

            // 自分の最新フラグより新しい他人の名刺を最新1件だけ取得
            $newerCard = DB::table('cards')
                ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
                ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
                ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
                ->where('cards.名刺ユーザーID', $carduser->id)
                ->where('cards.最新フラグ', '>', $myLatest->最新フラグ)
                ->where('cards.ユーザーID', '!=', Auth::id())
                ->orderBy('cards.最新フラグ', 'desc')
                ->limit(1)
                ->get();

            // マージ
            $cards = $cards->merge($newerCard);
        } else {
            // マイ名刺がない場合は他人の最新1件
            $cards = DB::table('cards')
                ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
                ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
                ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
                ->where('cards.名刺ユーザーID', $carduser->id)
                ->orderBy('cards.最新フラグ', 'desc')
                ->limit(1)
                ->get();
        }


        $now_card = null;
        $other_card = null;
        $now_card_flag = false;
        foreach ($cards as $card) {
            $departments = DB::table('card_department')
                ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
                ->where('card_department.名刺ID', $card->card_id)
                ->orderBy('card_department.id', 'asc')
                ->get();

            $card->departments = $departments;
            if ($card->拠点指定 == 0) {
                $card->拠点名 = "";
            }
            if ($card->ユーザーID == Auth::id() && !$now_card_flag) {
                $now_card = $card;
                $card->表示最新フラグ = 1;
                $now_card_flag = true;
            } else if ($card->ユーザーID != Auth::id()) {
                // 他人の名刺の場合
                $card->表示最新フラグ = 2;
                $other_card = $card;
                if (!$now_card_flag) {
                    $now_card = $other_card;
                    $now_card_flag = true;
                }
            } else {
                $card->表示最新フラグ = 0;
            }
        }
        if ($cards->count() == 0) {
            return redirect()->route('cardviewget')->with('error', '名刺が見つかりませんでした。');
        }
        return view('card.carddetail', compact("prefix", "server", "carduser", "cards", "now_card", "other_card", "carduser_user"));
    }
    // 名刺最新API
    public function cardlatestpost(Request $request)
    {
        $card_id = $request->card_id;
        $latest_card = Card::find($card_id);
        if (!$latest_card) {
            return redirect()->route('cardviewget')->with('error', '名刺が見つかりませんでした。');
        }
        $latest_number = Card::where('名刺ユーザーID', $latest_card->名刺ユーザーID)
            ->max('最新フラグ');
        $latest_number++;
        $latest_card->最新フラグ = $latest_number;
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
        $branch = Branch::where('id', $card->拠点ID)->first();
        $card->branch = $branch;
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
        $my_card_check = "checked";
        $favorite_check = "";
        return view('card.cardregist', compact("prefix", "server", "edit", "card_id", "carduser_id", "card", "designate_branch", "my_card_check", "favorite_check"));
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
        $branches = Branch::where('会社ID', $card->会社ID)->where('拠点指定', 1)->get();

        $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->id)
            ->where('ユーザーID', Auth::user()->id)
            ->first();
        if ($carduser_user) {
            $my_card_check = $carduser_user->マイ名刺ユーザー == 1 ? "checked" : "";
            $favorite_check = $carduser_user->お気に入りユーザー == 1 ? "checked" : "";
        } else {
            $my_card_check = "";
            $favorite_check = "";
        }
        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id", "designate_branch", "branches", "my_card_check", "favorite_check"));
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

        $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->id)
            ->where('ユーザーID', Auth::user()->id)
            ->first();
        if ($carduser_user) {
            $my_card_check = $carduser_user->マイ名刺ユーザー == 1 ? "checked" : "";
            $favorite_check = $carduser_user->お気に入りユーザー == 1 ? "checked" : "";
        } else {
            $my_card_check = "";
            $favorite_check = "";
        }
        return view('card.cardregist', compact("prefix", "server", "edit", "carduser", "card_id", "card", "carduser_id", "designate_branch", "my_card_check", "favorite_check"));
    }

    // マイ名刺登録
    public function cardmycardget(Request $request, $id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $exist_card = Card::find($id);
        $exist_mycard = Card::where('id', $id)
            ->where('ユーザーID', Auth::user()->id)
            ->first();
        if (!$exist_card) {
            return redirect()->route('cardviewget')->with('error', '名刺が見つかりませんでした。');
        } else if ($exist_mycard) {
            return redirect()->route('carddetailget', ['id' => $exist_mycard->名刺ユーザーID])->with('error', 'すでにマイ名刺登録しています。');
        }
        $latest_number = Card::where('名刺ユーザーID', $exist_card->名刺ユーザーID)
            ->max('最新フラグ');
        $latest_number++;

        $latest_mycard = Card::where('名刺ユーザーID', $exist_card->名刺ユーザーID)
            ->where('ユーザーID', Auth::user()->id)
            ->orderBy('最新フラグ', 'desc')
            ->first();
        if ($latest_mycard) {
            $note = $latest_mycard->備考;
        } else {
            $note = "";
        }

        $new_card = new Card();
        $new_card = $exist_card->replicate();
        $new_card->ユーザーID = Auth::user()->id;
        $new_card->最新フラグ = $latest_number;
        $new_card->備考 = $note;
        $new_card->save();

        $exist_card_department = Card_Department::where('名刺ID', $exist_card->id)->get();
        foreach ($exist_card_department as $department) {
            $new_card_department = new Card_Department();
            $new_card_department->名刺ID = $new_card->id;
            $new_card_department->部署ID = $department->部署ID;
            $new_card_department->save();
        }


        return redirect()->route('carddetailget', ['id' => $new_card->名刺ユーザーID])->with('success', 'マイ名刺登録しました。');
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

            $other_reference = Card::where('名刺ファイル表', $fileName)
                ->where('id', '!=', $card_id)
                ->first();

            if (Filesystem::exists($filePath) && !$other_reference) {
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
                $company = new Company();
                $company->会社名 = $request->company_name;
                $company->会社名カナ = $request->company_name_kana;
                $company->save();
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
            }
            $branch->拠点名 = $request->company_name;
            $branch->会社ID = $company->id;
            $branch->拠点所在地 = $request->branch_address;
            $branch->電話番号 = $request->branch_phone_number;
            $branch->FAX番号 = $request->branch_fax_number;
            $branch->拠点指定 = false;
            $branch->save();
        }
        // その名刺の変更
        if ($edit == 'edit') {
            $carduser = Carduser::find($request->carduser);
            $card = Card::find($request->card_id);
            $latest_number = Card::where('名刺ユーザーID', $carduser->id)
                ->max('最新フラグ');
            if ($card->最新フラグ == $latest_number) {
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
            $latest_number = Card::where('名刺ユーザーID', $carduser->id)
                ->max('最新フラグ');
            $latest_number++;
            $card->最新フラグ = $latest_number;
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
        // if ($request->my_card_check == 'on' || $request->favorite_check == 'on') {
        //     $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->id)
        //         ->where('ユーザーID', Auth::user()->id)
        //         ->first();
        //     if (!$carduser_user) {
        //         $carduser_user = new Carduser_User();
        //         $carduser_user->名刺ユーザーID = $carduser->id;
        //         $carduser_user->ユーザーID = Auth::user()->id;
        //     }
        //     $carduser_user->お気に入りユーザー = $request->favorite_check == 'on' ? true : false;
        //     $carduser_user->save();
        // }

        $card->名前 = $request->name;
        $card->名前カナ = $request->name_kana;
        $card->携帯電話番号 = $request->phone_number;
        $card->メールアドレス = $request->email;
        $card->備考 = $request->note;
        $card->役職 = $request->position;
        $card->拠点ID = $branch->id;
        $card->会社ID = $company->id;
        $card->ユーザーID = Auth::user()->id;
        if ($request->hasFile('front_blob-image')) {
            $file = $request->file('front_blob-image');
        } else if ($request->hasFile('card_file_front')) {
            $file = $request->file('card_file_front');
        } else {
            $file = null;
        }
        // 名刺表面の切り取ったblob画像がある場合
        if ($file) {

            $maxSizeBytes = 1000000; // 1MB
            if ($file->getSize() > $maxSizeBytes) {
                $image = new SimpleImage();
                try {
                    $image->fromFile($file->getPathname())
                        ->resize(1200, null);

                    // JPEG形式でメモリ上に画像生成
                    $imageData = $image->toString('image/jpeg', 80);

                    // 拡張子はJPEGに固定
                    $extension = 'jpg';
                    $filename = $this->generateRandomCode() . "." . $extension;

                    if ($server == 'onpre') {
                        $filepath = Config::get('custom.file_upload_path') . DIRECTORY_SEPARATOR . $filename;
                        // 物理ファイルとして保存
                        file_put_contents($filepath, $imageData);

                        $card->名刺ファイル表 = $filename;
                    } else if ($server == 'cloud') {
                        // S3に直接バイナリをアップロード
                        Storage::disk('s3')->put(
                            $prefix . '/' . $filename,
                            $imageData,
                            'private'
                        );

                        $card->名刺ファイル表 = $filename;
                    }
                } catch (\Exception $e) {
                    return back()->withErrors('画像処理に失敗しました: ' . $e->getMessage());
                }
            } else {
                // サイズが小さい場合は元のファイルをそのまま保存
                $extension = $file->getClientOriginalExtension();
                $filename = $this->generateRandomCode() . "." . $extension;

                if ($server == 'onpre') {
                    $filepath = Config::get('custom.file_upload_path');
                    $file->move($filepath, $filename);
                    $card->名刺ファイル表 = $filename;
                } else if ($server == 'cloud') {
                    Storage::disk('s3')->putFileAs(
                        $prefix,
                        $file,
                        $filename,
                        'private'
                    );
                    $card->名刺ファイル表 = $filename;
                }
            }
        } else {
            if ($edit == 'add') {
                return redirect()->back()->with('error', '名刺表面の画像がアップロードされていません。');
            }
        }
        // // 名刺表面の切り取られていない画像がある場合
        // else if ($request->hasFile('card_file_front')) {
        //     if ($server == 'onpre') {
        //         $extension = $request->file('card_file_front')->getClientOriginalExtension();
        //         $filename = $this->generateRandomCode() . "." . $extension;
        //         $filepath = Config::get('custom.file_upload_path');
        //         // ファイルを保存
        //         $request->file('card_file_front')->move($filepath, $filename);

        //         $card->名刺ファイル表 = $filename;
        //     } else if ($server == 'cloud') {
        //         $extension = $request->file('card_file_front')->getClientOriginalExtension();
        //         $filename = $this->generateRandomCode() . "." . $extension;

        //         // S3にファイルを保存
        //         Storage::disk('s3')->putFileAs(
        //             $prefix,
        //             $request->file('card_file_front'),
        //             $filename,
        //             'private'
        //         );
        //         $card->名刺ファイル表 = $filename;
        //     }
        // }

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
            if ($edit == 'add') {
                $card->名刺ファイル裏 = null;
            }
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
                }

                // 部署データを保存
                $department->save();

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
    // 名刺のOCR処理
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

                // Gemini APIを使用
                $imageBase64 = base64_encode(file_get_contents($imageFile->getRealPath()));
                $mimeType = $imageFile->getMimeType(); // e.g., image/jpeg
                $apiKey = config('gemini.api_key');
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";

                $prompt = $this->getJsonPrompt();

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
                $geminiReply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if (!$geminiReply) {
                    return response()->json(['error' => 'Geminiの返答にテキストがありません'], 422);
                }

                // Markdownのコードブロックを取り除く（```json ～ ``` を消す）
                $geminiReply = trim($geminiReply);
                $geminiReply = preg_replace('/^```json\s*/', '', $geminiReply);  // 先頭の ```json を削除
                $geminiReply = preg_replace('/```$/', '', $geminiReply);         // 末尾の ``` を削除
                $geminiReply = trim($geminiReply);
                $structuredData = json_decode($geminiReply, true);

                if ($request->existing_search == 'true') {
                    $mycard = Card::where('名前', $structuredData['名前'])
                        ->where('ユーザーID', Auth::user()->id)
                        ->orderByDesc('最新フラグ')
                        ->first();

                    $othercard = Card::where('名前', $structuredData['名前'])
                        ->orderByDesc('最新フラグ')
                        ->first();

                    if ($mycard) {
                        return response()->json([
                            'status' => 'success',
                            'data' => $structuredData,
                            'existing_card' => $mycard,
                            'mycard' => true,
                        ]);
                    } else if ($othercard) {
                        $otheruser = User::where('id', $othercard->ユーザーID)->first();
                        return response()->json([
                            'status' => 'success',
                            'data' => $structuredData,
                            'existing_card' => $othercard,
                            'mycard' => false,
                            'otheruser' => $otheruser->表示名,
                        ]);
                    }
                }

                return response()->json([
                    'status' => 'success',
                    'data' => $structuredData,
                ]);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
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
            \"備考\": \"\",
            \"携帯電話番号\": \"\",
            \"電話番号\": \"\",
            \"FAX番号\": \"\",
            \"住所\": \"\",
            \"郵便番号\": \"\",
            \"拠点名\": \"\"
        }

        ";
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
        }

        if (!$filepath) {
            return response()->json(['status' => 'error', 'message' => '画像がアップロードされていません。']);
        }
        // pathinfo関数を使用して拡張子を取得
        $extension = pathinfo($filepath, PATHINFO_EXTENSION);

        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = config('filesystems.disks.s3.bucket');
            $key = config('prefix.prefix') . '/' . $filepath;
            $expiration = '+1 hour'; // 有効期限

            // credentials を条件で分岐
            $s3Config = [
                'region'  => config('filesystems.disks.s3.region'),
                'version' => 'latest',
            ];

            if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.secret')) {
                // Xserver用 (キーが設定されている場合のみ credentials を渡す)
                $s3Config['credentials'] = [
                    'key'    => config('filesystems.disks.s3.key'),
                    'secret' => config('filesystems.disks.s3.secret'),
                ];
            }
            // AWS環境では credentials を省略 → IAM Role が自動で使われる

            $s3Client = new S3Client($s3Config);

            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => $bucket,
                'Key'    => $key
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
        $branches = Branch::where('会社ID', $id)->where('拠点指定', 1)->get();
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
    public function cardmultiplepastpost(Request $request)
    {

        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }

        $files = $request->input('files');
        $results = []; // ← ここで結果をまとめる配列

        foreach ($files as $file) {
            $core_id = $file['core_id']; // JS 側で付与した id を取得
            $filename = $file['filename'];
            $front_back = $file['front_back'] ?? 'front'; // JS で判断済みの裏表

            // ユーザー一致の最新データ取得
            if ($front_back === 'front') {
                $uploaded_file = UploadedCard::where('ファイル名', $filename)
                    ->where('ユーザーID', Auth::id())
                    ->where('名刺ID', '!=', null)
                    ->orderByDesc('updated_at')
                    ->first();
            } elseif ($front_back === 'back') {
                $uploaded_file = UploadedCard::where('ファイル名', $filename)
                    ->where('ユーザーID', Auth::id())
                    ->orderByDesc('updated_at')
                    ->first();
            }

            if (!$uploaded_file) {
                // 他ユーザーから取得
                $uploaded_file = UploadedCard::where('ファイル名', $filename)
                    ->orderByDesc('updated_at')
                    ->where('名刺ID', '!=', null)
                    ->first();
            }

            $status = 'newcard';
            $card_id = null;

            $new_uploaded_card = null;
            if ($uploaded_file) {
                if ($uploaded_file->ユーザーID == Auth::id()) {
                    $status = 'mycard';
                    if ($front_back === 'back' && $uploaded_file->upload_id == $request->upload_id) {
                        $status = 'newcard';
                    }
                    $card_id = $uploaded_file->名刺ID;
                    $new_uploaded_card = $uploaded_file;
                } else {
                    $status = 'othercard';
                    $card_id = $uploaded_file->名刺ID;
                }
            }

            if (!$new_uploaded_card) {
                $new_uploaded_card = new UploadedCard();
                $new_uploaded_card->upload_id = $request->upload_id;
                $new_uploaded_card->ファイル名 = $filename;
                $new_uploaded_card->ユーザーID = Auth::id();
            }

            if ($front_back === 'front') {
                $new_uploaded_card->表 = 1;
            } elseif ($front_back === 'back') {
                $new_uploaded_card->裏 = 1;
            }

            $new_uploaded_card->save();

            // 結果をまとめる
            $results[] = [
                'core_id' => $core_id, // JS 側のファイル識別ID
                'status' => $status,
                'uploaded_card_id' => $new_uploaded_card->id,
                'front_back' => $front_back,
                'filename' => $filename,
                'card_id' => $card_id
            ];
        }

        return response()->json($results);
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
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $file = $request->file('file');
        if (!$file) {
            if ($request->status == 'newcard') {
                return response()->json(['status' => 'error', 'message' => 'No file uploaded.']);
            }
        } else {
            $extension = $file->getClientOriginalExtension();
        }

        if ($request->front_back == 'front') {
            $front_back = 'front';
        } else if ($request->front_back == 'back') {
            $front_back = 'back';
        }
        if ($request->status == 'newcard') {
            $uploaded_card = UploadedCard::find($request->uploaded_card_id);
            if ($front_back == 'front') {
                $uploaded_card->表 = 1;
                $uploaded_card->save();


                // Gemini APIを使用
                $imageBase64 = base64_encode(file_get_contents($file->getRealPath()));
                $mimeType = $file->getMimeType(); // e.g., image/jpeg
                $apiKey = config('gemini.api_key');
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}";
                $prompt = $this->getJsonPrompt();
                // リトライ回数の上限を設定
                $maxRetries = 1;
                $retryCount = 0;
                $structuredData = null;

                while ($retryCount < $maxRetries) {
                    try {
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
                        } else {
                            $geminiReply = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? null;
                        }


                        if (!$geminiReply) {
                        } else {
                            // Markdownのコードブロックを取り除く（```json ～ ``` を消す）
                            $geminiReply = trim($geminiReply);
                            $geminiReply = preg_replace('/^```json\s*/', '', $geminiReply);  // 先頭の ```json を削除
                            $geminiReply = preg_replace('/```$/', '', $geminiReply);         // 末尾の ``` を削除
                            $geminiReply = trim($geminiReply);
                            $structuredData = json_decode($geminiReply, true);
                        }
                        if (
                            isset($structuredData['名前']) && $structuredData['名前'] !== '' &&
                            isset($structuredData['会社名']) && $structuredData['会社名'] !== ''
                        ) {
                            break; // 成功時はループを抜ける
                        }
                    } catch (\Exception $e) {
                        // $queue = OpenaiQueue::find($queue_id);
                        // $queue->トークン = 2000;
                        // $queue->save();
                        // ログ出力する場合：
                        \Log::warning("OpenAI retry {$retryCount}: updated_card_id: " . $uploaded_card->id . " " . $e->getMessage());
                    }
                    $retryCount++;
                }
                if ($retryCount >= $maxRetries) {
                    $uploaded_card->ステータス = 3;
                    $uploaded_card->save();
                    return response()->json([
                        'status' => 'error',
                        'front_back' => 'front',
                        'message' => 'Geminiの処理に失敗しました。',
                        'route' => '1'
                    ]);
                }
                $frontFilename = $this->generateRandomCode() . "." . $extension;


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
                    $branch->拠点所在地 = $structuredData['住所'] ?? '';
                    $branch->電話番号 = $structuredData['電話番号'] ?? '';
                    $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
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
                        if (!$branch) {
                            $branch = new Branch();
                            $branch->会社ID = $company->id;
                            $branch->拠点名 = $structuredData['会社名'] ?? '';
                            $branch->拠点指定 = false;
                            $branch->save();
                        }
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
                $newcard->名刺ファイル裏 = $uploaded_card->back_url;
                $newcard->ユーザーID = Auth::user()->id;
                $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
                $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
                $newcard->save();

                $uploaded_card->名刺ID = $newcard->id;
                $uploaded_card->表 = 2;
                $uploaded_card->save();
                // もし先に裏面が入力されていた場合
                // wasabiからダウンロード
                // 裏面をもう一度確認
                $uploaded_card = UploadedCard::find($request->uploaded_card_id);
                if ($uploaded_card->裏 == 2) {
                    $wasabiBackUrl = $uploaded_card->back_url;
                    $parsedUrl = parse_url($wasabiBackUrl);
                    $path = $parsedUrl['path'] ?? '';
                    // pathinfoで拡張子を取得
                    $extension = pathinfo($path, PATHINFO_EXTENSION);
                    $backFilename = $this->generateRandomCode() . "." . $extension;

                    // 画像をダウンロード
                    $imageData = file_get_contents($wasabiBackUrl);
                    // ダウンロードに失敗した場合
                    if ($imageData === false) {
                        $uploaded_card->裏 = 0;
                        $uploaded_card->save();
                    }
                    // ダウンロードに成功した場合
                    else if ($server == 'onpre') {
                        $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                        if (!is_dir($filepath)) {
                            mkdir($filepath, 0755, true); // フォルダがなければ作る
                        }
                        $fullPath = rtrim($filepath, '/') . '/' . $backFilename;

                        // ファイルを保存
                        file_put_contents($fullPath, $imageData);
                    } else if ($server == 'cloud') {
                        // クラウド
                        // S3にファイルを保存
                        Storage::disk('s3')->put(
                            $prefix . '/' . $backFilename,
                            $imageData,
                            'private'
                        );
                    }
                    $newcard->名刺ファイル裏 = $backFilename;
                    $newcard->save();


                    $uploaded_card->ステータス = 2;
                } else if ($uploaded_card->裏 == 0) {
                    $uploaded_card->ステータス = 2;
                }
                $uploaded_card->save();

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
                            }

                            // 部署データを保存
                            $department->save();

                            $card_department = new Card_Department();
                            $card_department->名刺ID = $newcard->id;
                            $card_department->部署ID = $department->id;
                            $card_department->save();
                        }
                        // 次の部署番号に進む
                        $department_number++;
                    }
                }

                if ($server == 'onpre') {
                    $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                    $file->move($filepath, $frontFilename);
                } else if ($server == 'cloud') {
                    // クラウド
                    // S3にファイルを保存
                    Storage::disk('s3')->putFileAs(
                        $prefix,
                        $file,
                        $frontFilename,
                        'private'
                    );
                }
            } else if ($front_back == 'back') {
                $uploaded_card->裏 = 1;
                // まだ表面が入力されていない場合
                // wasabiに保存
                if ($uploaded_card->表 == 1) {
                    $filename = 'temp/' . uniqid() . '.' . $file->getClientOriginalExtension();
                    // Wasabiにファイルを保存
                    $path = Storage::disk('wasabi')->putFileAs('', $file, $filename, 'public');

                    // プリサインドURLを生成（5分間アクセス可能）
                    $imageUrl = Storage::disk('wasabi')->temporaryUrl(
                        $path,
                        now()->addMinutes(5)
                    );
                    $uploaded_card->back_url = $imageUrl;
                    $uploaded_card->裏 = 2;
                    $uploaded_card->save();
                } else {
                    $card = Card::find($uploaded_card->名刺ID);
                    if ($card) {
                        $filename = $this->generateRandomCode() . "." . $extension;
                        if ($server == 'onpre') {
                            $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                            $file->move($filepath, $filename);
                        } else if ($server == 'cloud') {
                            // クラウド
                            // S3にファイルを保存
                            Storage::disk('s3')->putFileAs(
                                $prefix,
                                $file,
                                $filename,
                                'private'
                            );
                        }
                        $card->名刺ファイル裏 = $filename;
                        $card->save();
                        $uploaded_card->裏 = 2;
                        $uploaded_card->ステータス = 2;
                        $uploaded_card->save();
                    }
                }
            }


            return response()->json([
                'status' => 'success',
                'front_back' => $front_back,
                'route' => '2'
            ]);
        } else if ($request->status == 'mycard' || $request->status == 'othercard') {
            if ($front_back == 'front') {
                $uploaded_card = UploadedCard::find($request->uploaded_card_id);
                $exist_card = Card::find($request->card_id);
                $latest_number = Card::where('名刺ユーザーID', $exist_card->名刺ユーザーID)
                    ->max('最新フラグ');
                $latest_number++;
                $new_card = new Card();
                $new_card = $exist_card->replicate();
                $new_card->ユーザーID = Auth::user()->id;
                $new_card->最新フラグ = $latest_number;
                $new_card->save();

                $exist_card_department = Card_Department::where('名刺ID', $exist_card->id)->get();
                foreach ($exist_card_department as $department) {
                    $new_card_department = new Card_Department();
                    $new_card_department->名刺ID = $new_card->id;
                    $new_card_department->部署ID = $department->部署ID;
                    $new_card_department->save();
                }
            }
        }
        return response()->json([
            'status' => 'success',
            'front_back' => $front_back,
            'route' => '3'
        ]);
    }

    public function cardmultipleuploaddelete(Request $request)
    {
        $uploaded_cards = UploadedCard::where('ユーザーID', Auth::user()->id)->get();
        foreach ($uploaded_cards as $uploaded_card) {
            // 登録済み以外のデータを削除
            if ($uploaded_card->ステータス != 2 && $uploaded_card->名刺ID == null) {
                $uploaded_card->delete();
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => '未登録データを削除しました。',
        ]);
    }
    // public function cardopenai(Request $request)
    // {
    //     $server = config('prefix.server');
    //     $prefix = config('prefix.prefix');
    //     if ($prefix !== "") {
    //         $prefix = "/" . $prefix;
    //     }

    //     $card = UploadedCard::find($request->uploaded_card_id);
    //     if (!$card || $card->status !== 'pending') {
    //         return;
    //     }
    //     $card->status = 'processing';
    //     $card->save();


    //     $imageUrl = $card->front_url;


    //     // リトライ回数の上限を設定
    //     $maxRetries = 3;
    //     $retryCount = 0;
    //     $structuredData = null;

    //     while ($retryCount < $maxRetries) {
    //         $queue = $this->getStartTimeBasedOnTokenLimit();
    //         $queue_id = $queue[1];
    //         sleep($queue[0]);
    //         try {
    //             $aiResponse = OpenAI::chat()->create([
    //                 'model' => 'gpt-4o-mini',
    //                 'messages' => [
    //                     ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
    //                     [
    //                         'role' => 'user',
    //                         'content' => [
    //                             [
    //                                 "type" => "text",
    //                                 "text" => $this->getJsonPrompt() // プロンプトの内容
    //                             ],
    //                             [
    //                                 "type" => "image_url",
    //                                 "image_url" => [
    //                                     "url" => $imageUrl,
    //                                 ]
    //                             ]
    //                         ]
    //                     ],
    //                 ]
    //             ]);
    //             $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
    //             $structuredData = json_decode($jsonString, true);
    //             if (
    //                 isset($structuredData['名前']) && $structuredData['名前'] !== '' &&
    //                 isset($structuredData['会社名']) && $structuredData['会社名'] !== ''
    //             ) {
    //                 break; // 成功時はループを抜ける
    //             }
    //         } catch (\Exception $e) {
    //             $queue = OpenaiQueue::find($queue_id);
    //             $queue->トークン = 2000;
    //             $queue->save();
    //             // ログ出力する場合：
    //             \Log::warning("OpenAI retry {$retryCount}: updated_card_id: " . $card->id . " " . $e->getMessage());
    //         }
    //         $retryCount++;
    //         sleep(10); // API連続呼び出し防止のため1秒待機
    //     }
    //     if ($retryCount >= $maxRetries) {
    //         $card->status = 'failed';
    //         $card->save();
    //         return response()->json([
    //             'status' => 'error',
    //             'front_back' => 'front',
    //             'message' => 'OpenAIの処理に失敗しました。',
    //         ]);
    //     }



    //     if ($server == 'onpre') {
    //         $wasabiFrontUrl = $card->front_url;
    //         // クエリ部分（?以降）を除外
    //         $parsedUrl = parse_url($wasabiFrontUrl);
    //         $path = $parsedUrl['path'] ?? '';

    //         // pathinfoで拡張子を取得
    //         $extension = pathinfo($path, PATHINFO_EXTENSION);
    //         $frontFilename = $this->generateRandomCode() . "." . $extension;

    //         $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
    //         if (!is_dir($filepath)) {
    //             mkdir($filepath, 0755, true); // フォルダがなければ作る
    //         }
    //         // 画像をダウンロード
    //         $imageData = file_get_contents($wasabiFrontUrl);
    //         if ($imageData === false) {
    //             throw new \Exception('ファイルのダウンロードに失敗しました。');
    //         }

    //         // フルパス組み立て
    //         $fullPath = rtrim($filepath, '/') . '/' . $frontFilename;

    //         // ファイルを保存
    //         file_put_contents($fullPath, $imageData);


    //         $backFilename = null;
    //         if ($card->back_url != 'not_uploaded') {
    //             $wasabiBackUrl = $card->back_url;
    //             $parsedUrl = parse_url($wasabiBackUrl);
    //             $path = $parsedUrl['path'] ?? '';

    //             // pathinfoで拡張子を取得
    //             $extension = pathinfo($path, PATHINFO_EXTENSION);
    //             $backFilename = $this->generateRandomCode() . "." . $extension;

    //             $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
    //             if (!is_dir($filepath)) {
    //                 mkdir($filepath, 0755, true); // フォルダがなければ作る
    //             }
    //             // 画像をダウンロード
    //             $imageData = file_get_contents($wasabiBackUrl);
    //             if ($imageData === false) {
    //                 throw new \Exception('ファイルのダウンロードに失敗しました。');
    //             }

    //             $fullPath = rtrim($filepath, '/') . '/' . $backFilename;

    //             // ファイルを保存
    //             file_put_contents($fullPath, $imageData);
    //         }
    //     }
    //     // クラウド



    //     $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
    //     if (!$carduser) {
    //         $carduser = new CardUser();
    //         $carduser->表示名 = $structuredData['名前'] ?? '';
    //         $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
    //         $carduser->save();

    //         $exist_cards = Card::where('名刺ユーザーID', $carduser->id)->get();
    //         foreach ($exist_cards as $exist_card) {
    //             $exist_card->最新フラグ = 0;
    //             $exist_card->save();
    //         }
    //     }
    //     $company = Company::where('会社名', $structuredData['会社名'])->first();
    //     if (!$company) {
    //         $company = new Company();
    //         $company->会社名 = $structuredData['会社名'] ?? '';
    //         $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
    //         $company->save();


    //         $branch = new Branch();
    //         $branch->会社ID = $company->id;
    //         if ($structuredData['拠点名']) {
    //             $branch->拠点名 = $structuredData['拠点名'];
    //             $branch->拠点指定 = true;
    //         } else {
    //             $branch->拠点名 = $structuredData['会社名'] ?? '';
    //             $branch->拠点指定 = false;
    //         }
    //         $branch->save();
    //     } else {
    //         // 拠点名があるかどうかで分岐
    //         if ($structuredData['拠点名']) {
    //             // すでに拠点が登録されているかを確認
    //             $branch = Branch::where('会社ID', $company->id)
    //                 ->where('拠点名', $structuredData['拠点名'])
    //                 ->first();

    //             // ない場合は新規登録
    //             if (!$branch) {
    //                 $branch = new Branch();
    //                 $branch->会社ID = $company->id;
    //                 $branch->拠点名 = $structuredData['拠点名'];
    //                 $branch->拠点指定 = true;
    //                 $branch->拠点所在地 = $structuredData['住所'] ?? '';
    //                 $branch->電話番号 = $structuredData['電話番号'] ?? '';
    //                 $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
    //                 $branch->save();
    //             }
    //         } else {
    //             // 拠点名がない場合は、拠点指定がfalseのものを取得
    //             $branch = Branch::where('会社ID', $company->id)
    //                 ->where('拠点指定', false)
    //                 ->first();
    //         }
    //     }
    //     $newcard = new Card();
    //     $newcard->名刺ユーザーID = $carduser->id;
    //     $newcard->会社ID = $company->id;
    //     $newcard->拠点ID = $branch->id;
    //     $newcard->名前 = $structuredData['名前'] ?? '';
    //     $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
    //     $newcard->役職 = $structuredData['役職'] ?? '';
    //     $newcard->名刺ファイル表 = $frontFilename;
    //     $newcard->名刺ファイル裏 = $backFilename;
    //     $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
    //     $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
    //     $newcard->save();


    //     if (isset($structuredData['部署1']) && $structuredData['部署1'] !== '') {
    //         // 部署登録
    //         $department_number = 1;
    //         $upper_department_id = null;
    //         while (isset($structuredData['部署' . $department_number]) && $structuredData['部署' . $department_number] !== '') {
    //             // 部署名が入力されている場合
    //             if ($structuredData['部署' . $department_number] != '') {
    //                 // 部署名を取得
    //                 $department_name = $structuredData['部署' . $department_number];

    //                 $existing_department = Department::where('部署名', $department_name)
    //                     ->where('会社ID', $company->id)
    //                     ->first();
    //                 if ($existing_department) {
    //                     $department = $existing_department;
    //                 } else {
    //                     $department = new Department();
    //                     $department->会社ID = $company->id;
    //                     $department->部署名 = $department_name;
    //                 }

    //                 // 部署データを保存
    //                 $department->save();

    //                 $card_department = new Card_Department();
    //                 $card_department->名刺ID = $newcard->id;
    //                 $card_department->部署ID = $department->id;
    //                 $card_department->save();
    //             }
    //             // 次の部署番号に進む
    //             $department_number++;
    //         }
    //     }


    //     $card->status = 'done';
    //     $card->名刺ID = $newcard->id;
    //     $card->save();
    //     return response()->json([
    //         'status' => 'success',
    //     ]);
    // }
    // public function cardopenaieachprocess(Request $request)
    // {
    //     \Log::info('API called: ' . now()->format('H:i:s.u'));
    //     $server = config('prefix.server');
    //     $card = UploadedCard::find($request->card_id);
    //     if (!$card || $card->status !== 'pending') {
    //         return;
    //     }
    //     $card->status = 'processing';
    //     $card->save();


    //     $imageUrl = $card->front_url;
    //     $aiResponse = OpenAI::chat()->create([
    //         'model' => 'gpt-4o-mini',
    //         'messages' => [
    //             ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
    //             [
    //                 'role' => 'user',
    //                 'content' => [
    //                     [
    //                         "type" => "text",
    //                         "text" => $this->getJsonPrompt() // プロンプトの内容
    //                     ],
    //                     [
    //                         "type" => "image_url",
    //                         "image_url" => [
    //                             "url" => $imageUrl,
    //                         ]
    //                     ]
    //                 ]
    //             ],
    //         ]
    //     ]);
    //     $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
    //     $structuredData = json_decode($jsonString, true);
    //     if ($structuredData['名前'] == '' || $structuredData['会社名'] == '') {
    //         $card->status = 'done';
    //         $card->save();
    //         return;
    //     }
    //     if ($server == 'onpre') {
    //         $wasabiUrl = $card->front_url;
    //         // クエリ部分（?以降）を除外
    //         $parsedUrl = parse_url($wasabiUrl);
    //         $path = $parsedUrl['path'] ?? '';

    //         // pathinfoで拡張子を取得
    //         $extension = pathinfo($path, PATHINFO_EXTENSION);
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
    //     $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
    //     if (!$carduser) {
    //         $carduser = new CardUser();
    //         $carduser->表示名 = $structuredData['名前'] ?? '';
    //         $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
    //         $carduser->save();

    //         $exist_cards = Card::where('名刺ユーザーID', $carduser->id)->get();
    //         foreach ($exist_cards as $exist_card) {
    //             $exist_card->最新フラグ = 0;
    //             $exist_card->save();
    //         }
    //     }
    //     $company = Company::where('会社名', $structuredData['会社名'])->first();
    //     if (!$company) {
    //         $company = new Company();
    //         $company->会社名 = $structuredData['会社名'] ?? '';
    //         $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
    //         $company->save();


    //         $branch = new Branch();
    //         $branch->会社ID = $company->id;
    //         if ($structuredData['拠点名']) {
    //             $branch->拠点名 = $structuredData['拠点名'];
    //             $branch->拠点指定 = true;
    //         } else {
    //             $branch->拠点名 = $structuredData['会社名'] ?? '';
    //             $branch->拠点指定 = false;
    //         }
    //         $branch->save();
    //     } else {
    //         // 拠点名があるかどうかで分岐
    //         if ($structuredData['拠点名']) {
    //             // すでに拠点が登録されているかを確認
    //             $branch = Branch::where('会社ID', $company->id)
    //                 ->where('拠点名', $structuredData['拠点名'])
    //                 ->first();

    //             // ない場合は新規登録
    //             if (!$branch) {
    //                 $branch = new Branch();
    //                 $branch->会社ID = $company->id;
    //                 $branch->拠点名 = $structuredData['拠点名'];
    //                 $branch->拠点指定 = true;
    //                 $branch->拠点所在地 = $structuredData['住所'] ?? '';
    //                 $branch->電話番号 = $structuredData['電話番号'] ?? '';
    //                 $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
    //                 $branch->save();
    //             }
    //         } else {
    //             // 拠点名がない場合は、拠点指定がfalseのものを取得
    //             $branch = Branch::where('会社ID', $company->id)
    //                 ->where('拠点指定', false)
    //                 ->first();
    //         }
    //     }
    //     $newcard = new Card();
    //     $newcard->名刺ユーザーID = $carduser->id;
    //     $newcard->会社ID = $company->id;
    //     $newcard->拠点ID = $branch->id;
    //     $newcard->名前 = $structuredData['名前'] ?? '';
    //     $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
    //     $newcard->役職 = $structuredData['役職'] ?? '';
    //     $newcard->名刺ファイル表 = $filename;
    //     $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
    //     $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
    //     $newcard->save();

    //     // 部署登録
    //     // $department_number = 1;
    //     // $upper_department_id = null;
    //     // while ($structuredData['部署' . $department_number]) {
    //     //     // 部署名が入力されている場合
    //     //     if ($structuredData['部署' . $department_number] != '') {
    //     //         // 部署名を取得
    //     //         $department_name = $structuredData['部署' . $department_number];

    //     //         $existing_department = Department::where('部署名', $department_name)
    //     //             ->where('会社ID', $company->id)
    //     //             ->first();
    //     //         if ($existing_department) {
    //     //             $department = $existing_department;
    //     //         } else {
    //     //             $department = new Department();
    //     //             $department->会社ID = $company->id;
    //     //             $department->部署名 = $department_name;

    //     //             // 上位部署IDを設定（最初の部署以外）
    //     //             if ($department_number != 1) {
    //     //                 $department->上位部署ID = $upper_department_id;
    //     //             }
    //     //         }

    //     //         // 部署データを保存
    //     //         $department->save();
    //     //         // 上位部署IDを取得
    //     //         $upper_department_id = $department->id;


    //     //         $card_department = new Card_Department();
    //     //         $card_department->名刺ID = $newcard->id;
    //     //         $card_department->部署ID = $department->id;
    //     //         $card_department->save();
    //     //     }
    //     //     // 次の部署番号に進む
    //     //     $department_number++;
    //     // }




    //     $card->status = 'done';
    //     $card->save();
    // }

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

    public function cardmultipletestdestroy(Request $request)
    {
        $allcards = Card::all();
        foreach ($allcards as $card) {
            $card->delete();
        }
        $allbranch = Branch::all();
        foreach ($allbranch as $branch) {
            $branch->delete();
        }
        $alldepartment = Department::all();
        foreach ($alldepartment as $department) {
            $department->delete();
        }
        $allcompany = Company::all();
        foreach ($allcompany as $company) {
            $company->delete();
        }


        $allcarduser = CardUser::all();
        foreach ($allcarduser as $carduser) {
            $carduser->delete();
        }
        $allcarddepartment = Card_Department::all();
        foreach ($allcarddepartment as $carddepartment) {
            $carddepartment->delete();
        }
        $allcarduser_user = CardUser_User::all();
        foreach ($allcarduser_user as $carduser_user) {
            $carduser_user->delete();
        }
        $alluploadedcards = UploadedCard::all();
        foreach ($alluploadedcards as $uploadedcard) {
            $uploadedcard->delete();
        }
        return response()->json(['message' => '名刺複数アップロードテスト削除完了']);
    }

    public function cardviewexcelpost(Request $request)
    {

        // ① 配列を受け取ってデコード
        $cardIds = json_decode($request->input('card_view_card_array', '[]'), true);
        // エクセルテンプレートを読み込む
        $templatePath = public_path("xlsx/cardviewtemplate.xlsx"); // テンプレートのパスを指定
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($templatePath);

        // データベースから取得した値をエクセルに埋め込む
        $worksheet = $spreadsheet->getActiveSheet();

        $row = 3;
        // $cards = Card::whereIn('id', $cardIds)->get();
        $search     = $request->input('search', '');
        $start_date = $request->input('start_date') ?: '1900-01-01';
        $end_date   = $request->input('end_date')   ?: '2100-12-31';
        $sort       = $request->input('sort', '1'); // デフォルト: 名前順
        // サブクエリ: 名刺ユーザーごとに最新カード1件をROW_NUMBERで抽出
        $sub = DB::table('cards')
            ->select(
                'cards.*',
                DB::raw("ROW_NUMBER() OVER (
        PARTITION BY 名刺ユーザーID
        ORDER BY 最新フラグ DESC, id ASC
    ) as row_num")
            );

        // メインクエリ
        $query = DB::table('cardusers')
            ->select(
                'cardusers.id as carduser_id',
                'cardusers.表示名',
                'latest_cards.*',
                'companies.*'
            )
            ->joinSub($sub, 'latest_cards', function ($join) {
                $join->on('cardusers.id', '=', 'latest_cards.名刺ユーザーID')
                    ->where('latest_cards.row_num', 1);
            })
            ->leftJoin('companies', 'latest_cards.会社ID', '=', 'companies.id')
            ->whereBetween('latest_cards.created_at', [$start_date, $end_date]);

        // 検索条件
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('cardusers.表示名', 'like', "%$search%")
                    ->orWhere('companies.会社名', 'like', "%$search%");
            });
        }

        // ソート
        if ($sort === '1') {
            $query->orderBy('cardusers.表示名カナ');
        } elseif ($sort === '2') {
            $query->orderBy('companies.会社名カナ');
        } elseif ($sort === '3') {
            $query->orderBy('latest_cards.created_at');
        } elseif ($sort === '4') {
            $query->orderBy('latest_cards.updated_at');
        }

        $cards = $query->get();

        foreach ($cards as $card) {
            $card_department = Card_Department::where('名刺ID', $card->id)->pluck('部署ID')->toArray();
            $departments = Department::whereIn('id', $card_department)->pluck('部署名');
            $company = Company::where('id', $card->会社ID)->first();
            $company_name = $company->会社名;
            $company_name_kana = $company->会社名カナ;
            $branch = Branch::where('id', $card->拠点ID)->first();
            if ($branch->拠点指定 == 0) {
                $branch_name = "";
            } else {
                $branch_name = $branch->拠点名;
            }
            $worksheet->setCellValue("A{$row}", $card->名前);
            $worksheet->setCellValue("B{$row}", $card->名前カナ);
            $worksheet->setCellValue("C{$row}", $company_name);
            $worksheet->setCellValue("D{$row}", $company_name_kana);
            $worksheet->setCellValue("E{$row}", $departments->implode(' '));
            $worksheet->setCellValue("F{$row}", $card->役職);
            $worksheet->setCellValue("G{$row}", $branch_name);
            $worksheet->setCellValue("H{$row}", $branch->拠点所在地);
            $worksheet->setCellValue("I{$row}", $card->携帯電話番号);
            $worksheet->setCellValue("J{$row}", $card->メールアドレス);
            $worksheet->setCellValue("K{$row}", $branch->電話番号);
            $worksheet->setCellValue("L{$row}", $branch->FAX番号);
            $worksheet->setCellValue("M{$row}", $card->created_at);
            $worksheet->setCellValue("N{$row}", $card->updated_at);

            // 必要に応じて列を追加
            $row++;
        }

        $fileName = '名刺一覧_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
