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
use App\Models\Tag;
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
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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

        $perPage = 50;
        $page = $request->input('page', 1);

        $only_my = $request->input('only_my', 1);
        // サブクエリ
        // 非公開タグは「他のユーザーから見えない」設定なので、
        // 自分以外が所有する名刺レコードに非公開タグが付いている場合はここで除外する。
        // （自分のマイ名刺レコードは非公開タグの有無に関わらず残す）
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
            )
            ->where(function ($q) use ($userId) {
                $q->where('cards.ユーザーID', $userId)
                    ->orWhereNotIn('cards.id', function ($sq) {
                        $sq->select('card_tag.名刺ID')
                            ->from('card_tag')
                            ->join('tags', 'card_tag.タグID', '=', 'tags.id')
                            ->where('tags.非公開', true);
                    });
            });
        $sort = $request->input('sort', 1);
        $search = $request->input('search', '');
        $start_date = $request->input('start_date') ?: '1900-01-01';
        $end_date   = $request->input('end_date')   ?: '2100-12-31';
        $validatedTagIds = $this->cardviewValidatedTagIds($request, $userId);

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

      
        // ★ もし「マイ名刺だけ」を取得したい場合だけ join
        if ($only_my) {
            $query->where('latest_cards.ユーザーID', $userId);
        }

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

        if ($validatedTagIds !== []) {
            $query->whereIn('latest_cards.id', function ($q) use ($validatedTagIds) {
                $q->select('名刺ID')
                    ->from('card_tag')
                    ->whereIn('タグID', $validatedTagIds);
            });
        }

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

        $cardusers = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();


        // 部署をまとめて取得（N+1防止）
        $deptMap = DB::table('card_department')
            ->leftJoin('departments', 'card_department.部署ID', '=', 'departments.id')
            ->get()
            ->groupBy('名刺ID');

        $cardIds = $cardusers->pluck('card_id')->filter()->unique()->values();
        $tagColorsByCardId = collect();
        $hiddenTagCardIds = collect();
        if ($cardIds->isNotEmpty()) {
            // 非公開タグは「他のユーザーから見えない」だけで、所有者本人には通常表示する
            $tagRows = DB::table('card_tag')
                ->join('tags', 'card_tag.タグID', '=', 'tags.id')
                ->whereIn('card_tag.名刺ID', $cardIds)
                ->where(function ($q) use ($userId) {
                    $q->where('tags.非公開', false)
                        ->orWhere('tags.ユーザーID', $userId);
                })
                ->orderBy('tags.タグ名')
                ->select('card_tag.名刺ID', 'tags.カラーコード', 'tags.タグ名')
                ->get();
            $tagColorsByCardId = $tagRows->groupBy(function ($row) {
                return (int) $row->名刺ID;
            })->map(function ($group) {
                return $group->map(function ($row) {
                    $c = trim((string) $row->カラーコード);
                    if (!preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{6})$/', $c)) {
                        return null;
                    }

                    return [
                        'name' => (string) $row->タグ名,
                        'hex' => $c,
                    ];
                })->filter()->values()->all();
            });

            $hiddenTagCardIds = DB::table('card_tag')
                ->join('tags', 'card_tag.タグID', '=', 'tags.id')
                ->whereIn('card_tag.名刺ID', $cardIds)
                ->where('tags.ユーザーID', $userId)
                ->where('tags.非公開', true)
                ->pluck('card_tag.名刺ID')
                ->map(fn ($v) => (int) $v)
                ->unique()
                ->values()
                ->flip();
        }

        foreach ($cardusers as $carduser) {
            $carduser->departments = $deptMap->get($carduser->card_id) ?? collect();
            $carduser->tag_colors = $tagColorsByCardId->get((int) $carduser->card_id, []);
            $carduser->has_hidden_tag = $hiddenTagCardIds->has((int) $carduser->card_id);

            $carduser_user = Carduser_User::where('名刺ユーザーID', $carduser->carduser_id)
                ->where('ユーザーID', $userId)
                ->first();

            $carduser->マイ名刺ユーザー = ($carduser->ユーザーID == $userId) ? "true" : "false";
        }

        // Ajaxなら部分ビューだけ返す
        if ($request->ajax()) {
            // return view('card.partials.cardlist', compact('cardusers'));
            return response()->json([
                'html' => view('card.partials.cardlist', compact('cardusers'))->render(),
                'total' => $totalCount,
            ]);
        }

        $filterTags = Tag::where('ユーザーID', $userId)
            ->orderBy('タグ名')
            ->get(['id', 'タグ名', 'カラーコード', '非公開']);

        // 初回ロードはフルビュー
        return view('card.cardview', compact("prefix", "server", "cardusers", "totalCount", "filterTags"));
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
        ->where('users.削除', '!=', '削除')         // 論理削除されていない
        ->where('cards.ユーザーID', '!=', $user_id) // 自分以外のユーザー
        ->select('cards.名刺ユーザーID', 'users.表示名', 'users.id as user_id') // 名刺ユーザーIDだけを抽出
        // ->groupBy('cards.名刺ユーザーID', 'users.id')
        ->get();

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

        // 非公開タグ（他のユーザーには見せない設定）が付いた他人の名刺レコードを除外するためのサブクエリ
        $privateTaggedCardIds = function ($q) {
            $q->select('card_tag.名刺ID')
                ->from('card_tag')
                ->join('tags', 'card_tag.タグID', '=', 'tags.id')
                ->where('tags.非公開', true);
        };

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

            // 自分の最新フラグより新しい他人の名刺を最新1件だけ取得（非公開タグ付きは除外）
            $newerCard = DB::table('cards')
                ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
                ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
                ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
                ->where('cards.名刺ユーザーID', $carduser->id)
                ->where('cards.最新フラグ', '>', $myLatest->最新フラグ)
                ->where('cards.ユーザーID', '!=', Auth::id())
                ->whereNotIn('cards.id', $privateTaggedCardIds)
                ->orderBy('cards.最新フラグ', 'desc')
                ->limit(1)
                ->get();

            // マージ
            $cards = $cards->merge($newerCard);
        } else {
            // マイ名刺がない場合は他人の最新1件（非公開タグ付きは除外）
            $cards = DB::table('cards')
                ->select('cards.id as card_id', 'cards.*',  'companies.*', 'branches.*')
                ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
                ->leftJoin('branches', 'cards.拠点ID', '=', 'branches.id')
                ->where('cards.名刺ユーザーID', $carduser->id)
                ->whereNotIn('cards.id', $privateTaggedCardIds)
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

        $userTags = $this->userTagsAssignable();
        $nowCardTagIds = [];
        if ((int) ($now_card->ユーザーID ?? 0) === (int) Auth::id()) {
            $nowCardTagIds = $this->tagIdsLinkedToCard((int) $now_card->card_id);
        }

        return view('card.carddetail', compact('prefix', 'server', 'carduser', 'cards', 'now_card', 'other_card', 'carduser_user', 'userTags', 'nowCardTagIds'));
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
        $card->can_edit_tags = (int) $card->ユーザーID === (int) Auth::id();
        $card->attached_tag_ids = $card->can_edit_tags
            ? DB::table('card_tag')->where('名刺ID', $id)->pluck('タグID')->map(fn ($v) => (int) $v)->values()->all()
            : [];
        $card->history_card_id = (int) $id;

        return response()->json($card);
    }

    public function cardtagtogglepost(Request $request)
    {
        $data = $request->validate([
            'card_id' => ['required', 'integer', Rule::exists('cards', 'id')],
            'tag_id' => ['required', 'integer', Rule::exists('tags', 'id')],
            'attach' => ['required', 'boolean'],
        ]);

        $userId = Auth::id();
        $card = Card::query()->where('id', $data['card_id'])->firstOrFail();
        if ((int) $card->ユーザーID !== (int) $userId) {
            return response()->json(['success' => false, 'message' => '権限がありません。'], 403);
        }

        $tag = Tag::query()
            ->where('id', $data['tag_id'])
            ->where('ユーザーID', $userId)
            ->first();
        if (! $tag) {
            return response()->json(['success' => false, 'message' => 'タグが見つかりません。'], 422);
        }

        $myCardIds = Card::query()
            ->where('名刺ユーザーID', $card->名刺ユーザーID)
            ->where('ユーザーID', $userId)
            ->pluck('id')
            ->all();

        DB::transaction(function () use ($myCardIds, $data, $request) {
            if ($request->boolean('attach')) {
                $now = now();
                foreach ($myCardIds as $cid) {
                    DB::table('card_tag')->updateOrInsert(
                        ['名刺ID' => $cid, 'タグID' => $data['tag_id']],
                        ['created_at' => $now, 'updated_at' => $now]
                    );
                }
            } else {
                DB::table('card_tag')
                    ->whereIn('名刺ID', $myCardIds)
                    ->where('タグID', $data['tag_id'])
                    ->delete();
            }
        });

        return response()->json(['success' => true]);
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
        $userTags = $this->userTagsAssignable();
        $checkedTagIds = [];

        return view('card.cardregist', compact('prefix', 'server', 'edit', 'card_id', 'carduser_id', 'card', 'designate_branch', 'my_card_check', 'favorite_check', 'userTags', 'checkedTagIds'));
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
        $userTags = $this->userTagsAssignable();
        // タグは「名刺ユーザー(人) × ログインユーザー」単位で扱うため、人単位の集合を初期チェック値とする
        $checkedTagIds = $this->tagIdsLinkedToCarduserForUser((int) $carduser->id, (int) Auth::id());

        return view('card.cardregist', compact('prefix', 'server', 'edit', 'carduser', 'card_id', 'card', 'carduser_id', 'designate_branch', 'branches', 'my_card_check', 'favorite_check', 'userTags', 'checkedTagIds'));
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
        $userTags = $this->userTagsAssignable();
        // 「名刺追加」では同じ人(carduser)に対する自分のカードに既に付いているタグを引き継ぐ
        $checkedTagIds = $this->tagIdsLinkedToCarduserForUser((int) $carduser->id, (int) Auth::id());

        return view('card.cardregist', compact('prefix', 'server', 'edit', 'carduser', 'card_id', 'card', 'carduser_id', 'designate_branch', 'my_card_check', 'favorite_check', 'userTags', 'checkedTagIds'));
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

        // タグは「名刺ユーザー(人) × ログインユーザー」単位で同期する
        // → 当該人物配下の自分のすべてのカードレコードに同じタグセットを反映
        $this->syncCarduserTagsForUser((int) $carduser->id, (int) Auth::id(), $request->input('tag_ids', []));

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
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key={$apiKey}";

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
                        // 既存の人物（自分が登録済み）に紐付いているタグを引き継げるよう返す
                        $existingTagIds = $this->tagIdsLinkedToCarduserForUser(
                            (int) $mycard->名刺ユーザーID,
                            (int) Auth::id()
                        );

                        return response()->json([
                            'status' => 'success',
                            'data' => $structuredData,
                            'existing_card' => $mycard,
                            'mycard' => true,
                            'existing_tag_ids' => $existingTagIds,
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

    public function cardtagsettingsget()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $presetLabels = [
            'red' => '赤',
            'blue' => '青',
            'yellow' => '黄',
            'green' => '緑',
            'purple' => '紫',
            'custom' => 'カスタム',
        ];

        $rows = $this->buildCardTagSettingsRows();

        return view('card.cardtagsettings', compact('prefix', 'server', 'rows', 'presetLabels'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    private function buildCardTagSettingsRows()
    {
        if (old('tags') !== null) {
            return collect(old('tags'))->values()->map(function ($r) {
                $r = is_array($r) ? $r : [];
                $preset = $r['color_preset'] ?? 'red';
                $allowed = array_merge(array_keys(Tag::PRESET_COLOR_HEX), ['custom']);
                if (! in_array($preset, $allowed, true)) {
                    $preset = 'red';
                }
                $custom = $r['custom_color'] ?? '#e53935';
                if (! is_string($custom) || ! preg_match('/^#?[0-9A-Fa-f]{6}$/', $custom)) {
                    $custom = '#e53935';
                }
                $custom = str_starts_with($custom, '#') ? $custom : '#' . $custom;

                return [
                    'id' => isset($r['id']) && $r['id'] !== '' && $r['id'] !== null ? (int) $r['id'] : null,
                    'タグ名' => (string) ($r['タグ名'] ?? ''),
                    'color_preset' => $preset,
                    'custom_color' => $custom,
                    '非公開' => isset($r['非公開']) && ($r['非公開'] === true || $r['非公開'] === '1' || $r['非公開'] === 1),
                ];
            });
        }

        $tags = Tag::query()
            ->where('ユーザーID', Auth::id())
            ->orderBy('id')
            ->get();

        if ($tags->isEmpty()) {
            return collect([[
                'id' => null,
                'タグ名' => '',
                'color_preset' => 'red',
                'custom_color' => '#e53935',
                '非公開' => false,
            ]]);
        }

        return $tags->map(function (Tag $tag) {
            $preset = Tag::presetKeyForHex($tag->カラーコード);
            $rawHex = (string) $tag->カラーコード;
            $customColor = $preset === 'custom'
                ? (str_starts_with($rawHex, '#') ? strtoupper($rawHex) : '#' . strtoupper($rawHex))
                : '#e53935';

            return [
                'id' => $tag->id,
                'タグ名' => $tag->タグ名,
                'color_preset' => $preset,
                'custom_color' => $customColor,
                '非公開' => (bool) $tag->非公開,
            ];
        });
    }

    public function cardtagsettingspost(Request $request)
    {
        $userId = Auth::id();

        $tagsRows = collect($request->input('tags', []))
            ->map(fn ($r) => is_array($r) ? $r : [])
            ->filter(fn ($r) => filled(trim((string) ($r['タグ名'] ?? ''))))
            ->values();

        if ($tagsRows->isEmpty()) {
            return redirect()->route('cardtagsettingsget')
                ->withErrors(['tags' => '保存するタグを1行以上入力してください。'])
                ->withInput();
        }

        $deletedIds = collect($request->input('deleted_ids', []))
            ->filter(fn ($v) => $v !== null && $v !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $rules = [
            'deleted_ids' => ['nullable', 'array'],
            'deleted_ids.*' => ['integer', Rule::exists('tags', 'id')->where(fn ($q) => $q->where('ユーザーID', $userId))],
        ];

        foreach ($tagsRows as $i => $_) {
            $rules["tags.$i.id"] = ['nullable', 'integer', Rule::exists('tags', 'id')->where(fn ($q) => $q->where('ユーザーID', $userId))];
            $rules["tags.$i.タグ名"] = ['required', 'string', 'max:255'];
            $rules["tags.$i.color_preset"] = ['required', 'string', Rule::in(array_merge(array_keys(Tag::PRESET_COLOR_HEX), ['custom']))];
            $rules["tags.$i.custom_color"] = ['required_if:tags.'.$i.'.color_preset,custom', 'nullable', 'string', 'max:32', 'regex:/^#?[0-9A-Fa-f]{6}$/'];
        }

        $validator = Validator::make(
            [
                'tags' => $tagsRows->all(),
                'deleted_ids' => $deletedIds,
            ],
            $rules
        );

        $validator->after(function ($v) use ($tagsRows, $userId) {
            $names = $tagsRows->map(fn ($r) => trim((string) ($r['タグ名'] ?? '')));
            if ($names->count() !== $names->unique()->count()) {
                $v->errors()->add('tags', '同じタグ名の行が複数あります。');
            }

            foreach ($tagsRows as $i => $row) {
                $name = trim((string) ($row['タグ名'] ?? ''));
                $id = isset($row['id']) && $row['id'] !== '' && $row['id'] !== null ? (int) $row['id'] : null;
                $dup = Tag::query()
                    ->where('ユーザーID', $userId)
                    ->where('タグ名', $name)
                    ->when($id, fn ($q) => $q->where('id', '!=', $id))
                    ->exists();
                if ($dup) {
                    $v->errors()->add("tags.$i.タグ名", 'このタグ名はすでに登録されています。');
                }
            }
        });

        $validator->validate();

        DB::transaction(function () use ($tagsRows, $deletedIds, $userId) {
            foreach ($deletedIds as $delId) {
                Tag::query()->where('id', $delId)->where('ユーザーID', $userId)->delete();
            }

            foreach ($tagsRows as $row) {
                $hex = $this->resolveTagColorCode((string) $row['color_preset'], $row['custom_color'] ?? null);
                $name = trim((string) $row['タグ名']);
                $hidden = isset($row['非公開']) && ($row['非公開'] === true || $row['非公開'] === '1' || $row['非公開'] === 1);
                $payload = [
                    'ユーザーID' => $userId,
                    'タグ名' => $name,
                    'カラーコード' => $hex,
                    '非公開' => $hidden,
                ];
                $id = isset($row['id']) && $row['id'] !== '' && $row['id'] !== null ? (int) $row['id'] : null;
                if ($id) {
                    Tag::query()->where('id', $id)->where('ユーザーID', $userId)->update($payload);
                } else {
                    Tag::create($payload);
                }
            }
        });

        return redirect()->route('cardtagsettingsget')->with('success', 'タグを保存しました。');
    }

    /**
     * タグごとの名刺紐付けモーダル：左側（紐付け済み名刺）と右側（候補マイ名刺）を返す。
     * GET /card/tag/{tag}/cards
     */
    public function cardtagcardsget(Request $request, $tag)
    {
        $userId = (int) Auth::id();
        $tagId = (int) $tag;

        $tagModel = Tag::where('id', $tagId)->where('ユーザーID', $userId)->first();
        if (!$tagModel) {
            return response()->json(['error' => 'タグが見つかりません。'], 404);
        }

        // 自分のマイ名刺について、cardusers ごとに 最新フラグ が最大のレコードを 1 件抽出
        $rawCards = DB::table('cards')
            ->select(
                'cards.id as card_id',
                'cards.名刺ユーザーID as carduser_id',
                'cards.最新フラグ',
                'cardusers.表示名 as name',
                'cardusers.表示名カナ as name_kana',
                'companies.会社名 as company_name',
                'companies.会社名カナ as company_name_kana'
            )
            ->leftJoin('cardusers', 'cards.名刺ユーザーID', '=', 'cardusers.id')
            ->leftJoin('companies', 'cards.会社ID', '=', 'companies.id')
            ->where('cards.ユーザーID', $userId)
            ->orderBy('cards.最新フラグ', 'desc')
            ->orderBy('cards.id', 'asc')
            ->get();

        // cardusers ごとに最新の 1 件を保持
        $latestPerPerson = [];
        foreach ($rawCards as $row) {
            $key = (int) $row->carduser_id;
            if (!isset($latestPerPerson[$key])) {
                $latestPerPerson[$key] = $row;
            }
        }

        // タグに紐付いている cardusers の集合（このユーザーの名刺レコード経由）
        $linkedPersonIds = DB::table('card_tag')
            ->join('cards', 'card_tag.名刺ID', '=', 'cards.id')
            ->where('card_tag.タグID', $tagId)
            ->where('cards.ユーザーID', $userId)
            ->pluck('cards.名刺ユーザーID')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
        $linkedSet = array_flip($linkedPersonIds);

        $linked = [];
        $unlinked = [];
        foreach ($latestPerPerson as $personId => $row) {
            $item = [
                'card_id' => (int) $row->card_id,
                'carduser_id' => (int) $row->carduser_id,
                'name' => (string) ($row->name ?? ''),
                'name_kana' => (string) ($row->name_kana ?? ''),
                'company_name' => (string) ($row->company_name ?? ''),
            ];
            if (isset($linkedSet[$personId])) {
                $linked[] = $item;
            } else {
                $unlinked[] = $item;
            }
        }

        // 並び替え（表示名カナ順）
        $sortByKana = function (&$arr) {
            usort($arr, function ($a, $b) {
                return strcmp((string) ($a['name_kana'] ?? ''), (string) ($b['name_kana'] ?? ''));
            });
        };
        $sortByKana($linked);
        $sortByKana($unlinked);

        // 検索（右側のみ。会社名・名前の部分一致）
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $needle = $search;
            $unlinked = array_values(array_filter($unlinked, function ($item) use ($needle) {
                $name = (string) ($item['name'] ?? '');
                $company = (string) ($item['company_name'] ?? '');
                return mb_stripos($name, $needle) !== false || mb_stripos($company, $needle) !== false;
            }));
        }

        return response()->json([
            'tag' => [
                'id' => (int) $tagModel->id,
                'name' => (string) $tagModel->タグ名,
                'color' => (string) $tagModel->カラーコード,
                'private' => (bool) $tagModel->非公開,
            ],
            'linked' => $linked,
            'unlinked' => $unlinked,
        ]);
    }

    /**
     * タグごとの名刺紐付けモーダル：一括保存（送信された card_ids でタグの紐付けを置き換える）。
     * POST /card/tag/{tag}/cards
     */
    public function cardtagcardspost(Request $request, $tag)
    {
        $userId = (int) Auth::id();
        $tagId = (int) $tag;

        $tagModel = Tag::where('id', $tagId)->where('ユーザーID', $userId)->first();
        if (!$tagModel) {
            return response()->json(['error' => 'タグが見つかりません。'], 404);
        }

        $rawIds = $request->input('card_ids', []);
        if (!is_array($rawIds)) {
            $rawIds = [$rawIds];
        }
        $cardIds = collect($rawIds)
            ->map(fn ($v) => (int) $v)
            ->filter(fn ($v) => $v > 0)
            ->unique()
            ->values()
            ->all();

        // 自分の名刺だけに絞る
        $validCardIds = $cardIds === [] ? [] : DB::table('cards')
            ->where('ユーザーID', $userId)
            ->whereIn('id', $cardIds)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        DB::transaction(function () use ($tagId, $userId, $validCardIds) {
            // このユーザーの名刺レコードに対する既存 pivot をすべて削除し、改めて挿入
            DB::table('card_tag')
                ->where('タグID', $tagId)
                ->whereIn('名刺ID', function ($q) use ($userId) {
                    $q->select('id')->from('cards')->where('ユーザーID', $userId);
                })
                ->delete();

            $now = now();
            foreach ($validCardIds as $cardId) {
                DB::table('card_tag')->insert([
                    'タグID' => $tagId,
                    '名刺ID' => $cardId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'count' => count($validCardIds),
        ]);
    }

    /**
     * ログインユーザーのタグマスタを名刺フォーム用に取得（非公開タグは所有者本人には表示）
     */
    private function userTagsAssignable(): \Illuminate\Support\Collection
    {
        return Tag::query()
            ->where('ユーザーID', Auth::id())
            ->orderBy('タグ名')
            ->get();
    }

    /**
     * @return array<int>
     */
    private function tagIdsLinkedToCard(int $cardId): array
    {
        return DB::table('card_tag')->where('名刺ID', $cardId)->pluck('タグID')->map(fn ($v) => (int) $v)->values()->all();
    }

    /**
     * 指定ユーザーが所有する、ある名刺ユーザー(人)に対するすべてのカードレコードに付いている
     * タグID の集合（重複除外）を返す。タグは人単位で管理される想定。
     *
     * @return array<int>
     */
    private function tagIdsLinkedToCarduserForUser(int $cardUserId, int $userId): array
    {
        return DB::table('card_tag')
            ->join('cards', 'card_tag.名刺ID', '=', 'cards.id')
            ->where('cards.名刺ユーザーID', $cardUserId)
            ->where('cards.ユーザーID', $userId)
            ->pluck('card_tag.タグID')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    private function syncCardTagPivot(int $cardId, int $userId, ?array $tagIds): void
    {
        $ids = collect($tagIds ?? [])->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
        $allowed = Tag::query()
            ->where('ユーザーID', $userId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        DB::table('card_tag')->where('名刺ID', $cardId)->delete();
        foreach ($allowed as $tagId) {
            DB::table('card_tag')->insert([
                '名刺ID' => $cardId,
                'タグID' => $tagId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * タグは「名刺ユーザー(人) × ログインユーザー」単位で管理する。
     * 指定ユーザーが所有する当該名刺ユーザー配下のすべてのカードレコードに、
     * 同一の tagIds を再同期する（古いカードレコードの紐付けも含めて統一）。
     */
    private function syncCarduserTagsForUser(int $cardUserId, int $userId, ?array $tagIds): void
    {
        $ids = collect($tagIds ?? [])->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
        $allowedTagIds = $ids === [] ? [] : Tag::query()
            ->where('ユーザーID', $userId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $cardIds = DB::table('cards')
            ->where('名刺ユーザーID', $cardUserId)
            ->where('ユーザーID', $userId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        if ($cardIds === []) {
            return;
        }

        DB::transaction(function () use ($allowedTagIds, $cardIds) {
            DB::table('card_tag')->whereIn('名刺ID', $cardIds)->delete();
            $now = now();
            foreach ($cardIds as $cardId) {
                foreach ($allowedTagIds as $tagId) {
                    DB::table('card_tag')->insert([
                        '名刺ID' => $cardId,
                        'タグID' => $tagId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    private function resolveTagColorCode(string $preset, ?string $custom): string
    {
        if ($preset === 'custom') {
            $raw = (string) $custom;

            return strtoupper(str_starts_with($raw, '#') ? $raw : '#' . $raw);
        }

        return Tag::PRESET_COLOR_HEX[$preset] ?? Tag::PRESET_COLOR_HEX['red'];
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
                $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key={$apiKey}";
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
        $only_my    = $request->input('only_my', 0);
        $userId     = Auth::id();
        $validatedTagIds = $this->cardviewValidatedTagIds($request, $userId);
        // サブクエリ: 名刺ユーザーごとに最新カード1件をROW_NUMBERで抽出
        // 非公開タグが付いている他人の名刺レコードはあらかじめ除外する
        $sub = DB::table('cards')
            ->select(
                'cards.*',
                DB::raw("ROW_NUMBER() OVER (
        PARTITION BY 名刺ユーザーID
        ORDER BY
            CASE WHEN ユーザーID = {$userId} THEN 1 ELSE 0 END DESC,
            最新フラグ DESC,
            id ASC
    ) as row_num")
            )
            ->where(function ($q) use ($userId) {
                $q->where('cards.ユーザーID', $userId)
                    ->orWhereNotIn('cards.id', function ($sq) {
                        $sq->select('card_tag.名刺ID')
                            ->from('card_tag')
                            ->join('tags', 'card_tag.タグID', '=', 'tags.id')
                            ->where('tags.非公開', true);
                    });
            });

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

        if ($only_my) {
            $query->where('latest_cards.ユーザーID', $userId);
        }

        if ($validatedTagIds !== []) {
            $query->whereIn('latest_cards.id', function ($q) use ($validatedTagIds) {
                $q->select('名刺ID')
                    ->from('card_tag')
                    ->whereIn('タグID', $validatedTagIds);
            });
        }

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

    /**
     * 名刺一覧のタグ絞り込み用。ログインユーザーの有効タグIDのみ残す。
     *
     * @return int[]
     */
    protected function cardviewValidatedTagIds(Request $request, int $userId): array
    {
        $raw = $request->input('tag_ids', []);
        if (!is_array($raw)) {
            if ($raw === null || $raw === '') {
                $raw = [];
            } elseif (is_string($raw)) {
                $raw = array_filter(explode(',', $raw));
            } else {
                $raw = [$raw];
            }
        }
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $raw),
            fn ($v) => $v > 0
        )));
        if ($ids === []) {
            return [];
        }

        return Tag::where('ユーザーID', $userId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();
    }
}
