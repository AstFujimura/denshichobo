<?php

namespace App\Http\Controllers;


use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\IOFactory;

use Carbon\Carbon;
use App\Models\File;
use App\Models\User;
use App\Models\Document;
use App\Models\Group;
use App\Models\Group_User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Pagination\Paginator;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\File as PDF;
use Illuminate\Support\Facades\Response;

class TopController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd($this->pagenatearray(5,3));
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        //デフォルトでは25件表示をする
        $show = 25;
        //現在のページ数を取得する
        $nowpage = $request->input("page");
        if (!$nowpage) {
            $nowpage = 1;
        }
        $userId = Auth::id(); // ログインしているユーザーのIDを取得
        $admin = User::find($userId)->管理;
        $today = Carbon::today(); // 今日の日付を取得
        $oneMonthAgo = Carbon::now()->subMonth()->format('Ymd'); //一か月前の日付を取得
        $users = User::where("id", "not like", 1)
            ->where("削除", "")
            ->get();

        $documents = Document::where("check", "check")
            ->orderBy('order', 'asc')
            ->get();

        // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
        $grouparray = Group_User::where('ユーザーID', $userId) // 条件を指定
            ->pluck('グループID') // グループID のみを取得
            ->toArray(); // コレクションを配列に変換




        if ($admin == "一般") {
            //一般の場合はログインユーザーが含まれるグループIDのリストを表示する(検索ボックス用)
            $groups = Group::whereIn("id", $grouparray)
                ->where('id', ">", 100000)
                ->get();


            $files = DB::table('files')
                ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', "groups.グループ名")
                ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
                ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
                ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
                ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
                ->where('files.最新フラグ', '最新')
                ->where('files.日付', '>=', $oneMonthAgo)
                ->where("files.削除フラグ", "")
                ->whereIn('files.グループID', $grouparray)
                ->orderBy('files.日付', 'desc')
                ->orderBy('files.id','desc');
        } else if ($admin == "管理") {
            //管理の場合はすべてを表示する(検索ボックス用)
            $groups = Group::where('id', ">", 100000)
                ->get();
            $files = DB::table('files')
                ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', "groups.グループ名")
                ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
                ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
                ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
                ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
                ->where('files.最新フラグ', '最新')
                ->where('files.日付', '>=', $oneMonthAgo)
                ->where("files.削除フラグ", "")
                ->orderBy('files.日付', 'desc')
                ->orderBy('files.id','desc');
        }
        $alldata = $files->get()->count();
        $files = $files->paginate($show);

        // foreach ($files as $file) {

        //     $file->書類 = DB::table('documents')->where('id', $file->書類ID)->first()->書類;
        // }
        $count = $files->count();

        //表示件数の最大値と最小値
        $startdata = ($nowpage - 1) * $show + 1;
        $enddata = ($nowpage - 1) * $show + $count;

        //全データ÷最大表示件数(小数切り上げ)でページネーションの数を求める
        //ceilは小数切り上げの関数、intvalでint型に変換
        $max = intval(ceil($alldata / $show));
        if ($max == 0) {
            $max = 1;
            $startdata = 0;
        }


        $Parray = $this->paginatearray($max, $nowpage);


        $paginate = [];

        foreach ($Parray as $page) {

            if ($page == $nowpage) {
                $class = "nowpagebutton";
                $a = '/?page=' . $page;
            } else if ($page == "...") {
                $class = "dotpagebutton";
                $a = "";
            } else {
                $class = "pagebutton";
                $a = '/?page=' . $page;
            }
            $value = [
                "value" => $page,
                "class" => $class,
                "a" => $a,

            ];
            array_push($paginate, $value);
        }
        // dd($paginate);

        if ($request->input("excel") == "true") {
            $this->excel($request, $files);
        }

        // 取得したデータをビューに渡すなどの処理
        return view('information.toppage', compact('files', 'users', 'groups', 'documents', 'paginate', 'startdata', 'enddata', 'alldata', 'prefix', 'server'));
    }

    //表示するページネーションボタンの配列を返す
    public function paginatearray($max, $nowpage)
    {
        $pagearray = [];
        //指定したページの前後2つを配列に格納するただし、1未満やmaxを超えるものは入れない
        for ($page = $nowpage - 2; $page <= $nowpage + 2; $page++) {

            if ($page >= 1 && $page <= $max) {
                array_push($pagearray, $page);
            }
        }

        //格納された配列の初めの値が1でない場合[3,4,5,6,7]等の場合は1と...を初めに挿入する。[1,...,3,4,5,6,7]となる
        if ($pagearray[0] != 1) {
            array_unshift($pagearray, 1, "...");
        }
        //格納された配列の最後の値がmaxでない場合[1...3,4,5,6,7]かつmax=10などの場合は...とmax(例では10)を最後に挿入する。[1,...,3,4,5,6,7,...,10]となる
        if (end($pagearray) != $max) {
            array_push($pagearray, "...", $max);
        }
        return $pagearray;
    }










    //------------ここから検索ページ--------------------


    public function search(Request $request)
    {

        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $userId = Auth::id(); // ログインしているユーザーのIDを取得
        $admin = User::find($userId)->管理;
        $users = User::where("id", "not like", 1)
            ->where("削除", "")
            ->get();
        $documents = Document::where("check", "check")
            ->orderBy('order', 'asc')
            ->get();


        // 中間テーブルからログインユーザーが含まれる グループID のリストを取得
        $grouparray = Group_User::where('ユーザーID', $userId) // 条件を指定
            ->pluck('グループID') // グループID のみを取得
            ->toArray(); // コレクションを配列に変換

        if ($admin == "一般") {

            //一般の場合はログインユーザーが含まれるグループIDのリストを表示する(検索ボックス用)
            $groups = Group::whereIn("id", $grouparray)
                ->where('id', ">", 100000)
                ->get();

            $allfiles = DB::table('files')
                ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', "groups.グループ名")
                ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
                ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
                ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
                ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
                ->where('files.最新フラグ', '最新')
                ->whereIn('files.グループID', $grouparray)
                ->orderBy('files.日付', 'desc');
        } else if ($admin == "管理") {

            //管理の場合はすべてを表示する(検索ボックス用)
            $groups = Group::where('id', ">", 100000)
                ->get();

            $allfiles = DB::table('files')
                ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', "groups.グループ名")
                ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
                ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
                ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
                ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
                ->where('files.最新フラグ', '最新')
                ->orderBy('files.日付', 'desc');
        }




        //全件表示の場合はstring型になるのでintに変換
        $show = intval($request->input("datacount"));

        //現在のページ数を取得する
        $nowpage = $request->input("page");
        if (!$nowpage) {
            $nowpage = 1;
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
        $teisyutu = $request->input('teisyutu');
        $kennsakuword = $request->input('kennsakuword');
        $hozonn = $request->input('hozonn');
        $deleteOrzenken = $request->input('deleteOrzenken');
        $group = $request->input('group');
        $updater = $request->input('updater');
        $creater = $request->input('creater');
        $selectdata = $request->input('selectdata');


        //値が入ってない時は%%を入れる
        if (!$syoruikubunn) {
            $syoruikubunn = "%%";
        }
        //値が入っていないときはすべてのグループにするために%%を入れる
        if (!$group) {
            $group = "%%";
        }
        //値が入っていないときはすべてのユーザーにするために%%を入れる
        if (!$updater) {
            $updater = "%%";
        }
        //値が入っていないときはすべてのユーザーにするために%%を入れる
        if (!$creater) {
            $creater = "%%";
        }
        //値が入っていないときはすべてのユーザーにするために%%を入れる
        if (!$teisyutu) {
            $teisyutu = "%%";
        }

        foreach ($users as $user) {

            //新たにcheckedというカラムを追加する（一時的に）
            //チェックされたユーザーが一致した場合値はcheckedを付与する
            $user->updaterselected = ($user->id == $updater) ? 'selected' : '';
            $user->createrselected = ($user->id == $creater) ? 'selected' : '';
        }
        foreach ($groups as $grouprecord) {

            //新たにcheckedというカラムを追加する（一時的に）
            //チェックされたユーザーが一致した場合値はcheckedを付与する
            $grouprecord->groupselected = ($grouprecord->id == $group) ? 'selected' : '';
        }

        foreach ($documents as $document) {

            //新たにcheckedというカラムを追加する（一時的に）
            //チェックされたユーザーが一致した場合値はcheckedを付与する
            $document->selected = ($document->id == $syoruikubunn) ? 'selected' : '';
        }



        //値が空の場合は最小値と最大値を格納する。検索後にもう一度空に戻す
        if ($startDateStr == "") {
            $startDateStr = "00000000";
        }


        if ($endDateStr == "") {
            $endDateStr = "99999999";
        }

        if ($startKinngakuStr == "") {
            //入力した値が入っていない場合
            $startKinngakuStr = "-2100000000";
        }
        if ($endKinngakuStr == "") {
            $endKinngakuStr = "2100000000";
        }

        if ($selectdata == "有効データ") {
            $selectdata = "";
        } else if ($selectdata == "削除データ") {
            $selectdata = "済";
        } else if ($selectdata == "全件データ") {
            $selectdata = "%%";
        }



        //検索クエリ
        $files = $allfiles->where('files.日付', '>=', $startDateStr)
            ->where('files.日付', '<=', $endDateStr)
            ->where('files.金額', '>=', $startKinngakuStr)
            ->where('files.金額', '<=', $endKinngakuStr)
            ->where('files.取引先', 'like', "%" . $torihikisaki . "%")
            ->where('files.書類ID', 'like', $syoruikubunn)
            ->where('files.提出', 'like', $teisyutu)
            ->where('files.保存', 'like', "%" . $hozonn . "%")
            ->where('files.備考', 'like', "%" . $kennsakuword . "%")
            ->where('files.グループID', 'like', $group)
            ->where('files.更新者ID', 'like', $updater)
            ->where('files.保存者ID', 'like', $creater)
            ->where('files.削除フラグ', 'like', $selectdata);

        $alldata = $files->count();

        $files = $files->paginate($show);

        $count = $files->count();

        //表示件数の最大値と最小値
        $startdata = ($nowpage - 1) * $show + 1;
        $enddata = ($nowpage - 1) * $show + $count;

        //全データ÷最大表示件数(小数切り上げ)でページネーションの数を求める
        //ceilは小数切り上げの関数、intvalでint型に変換
        $max = intval(ceil($alldata / $show));
        if ($max == 0) {
            $max = 1;
            $startdata = 0;
        }


        $Parray = $this->paginatearray($max, $nowpage);


        $paginate = [];

        //現在のクエリを取得
        $currentQuery = $_SERVER['QUERY_STRING'];

        $queryParts = [];
        //連想配列に置き換える
        parse_str($currentQuery, $queryParts);
        // 現在のクエリ文字列から 'page' パラメータを削除する
        unset($queryParts['page']);
        // 新しいクエリ文字列を生成
        $newQuery = http_build_query($queryParts);

        foreach ($Parray as $page) {

            if ($page == $nowpage) {
                $class = "nowpagebutton";
                $a = '/search?page=' . $page . "&" . $newQuery;
            } else if ($page == "...") {
                $class = "dotpagebutton";
                $a = '/search?' . $newQuery;
            } else {
                $class = "pagebutton";
                $a = '/search?page=' . $page . "&" . $newQuery;
            }
            $value = [
                "value" => $page,
                "class" => $class,
                "a" => $a,

            ];
            array_push($paginate, $value);
        }



        //検索結果に初期値として渡すときに値を空欄にしておくため
        if ($startDateStr == "00000000") {
            $startDateStr = "";
        } else {
            $startDateStr = substr_replace($startDateStr, '/', 4, 0);
            $startDateStr = substr_replace($startDateStr, '/', 7, 0);
        }
        if ($endDateStr == "99999999") {
            $endDateStr = "";
        } else {
            $endDateStr = substr_replace($endDateStr, '/', 4, 0);
            $endDateStr = substr_replace($endDateStr, '/', 7, 0);
        }

        if ($startKinngakuStr == "-2100000000") {
            $startKinngakuStr = "";
        } else {
            $startKinngakuStr = number_format(floatval($startKinngakuStr));
        }
        if ($endKinngakuStr == "2100000000") {
            $endKinngakuStr = "";
        } else {
            $endKinngakuStr = number_format(floatval($endKinngakuStr));
        }

        if ($request->input("excel") == "true") {
            $this->excel($request, $files);
        }


        $data = [
            'files' => $files,
            'starthiduke' => $startDateStr,
            'endhiduke' => $endDateStr,
            'startkinngaku' => $startKinngakuStr,
            'endkinngaku' => $endKinngakuStr,
            'torihikisaki' => $torihikisaki,
            'kennsakuword' => $kennsakuword,
            'dennshinone' => "",
            'dennshi' => "",
            'scan' => "",
            'deleteOrzenken' => $deleteOrzenken,
            'yukou' => "",
            'delete' => "",
            'zenken' => "",
            'user' => $user,
            'users' => $users,
            'documents' => $documents,
            'teisyutu' => "",
            'jyuryo' => "",
            'k25' => "",
            'k50' => "",
            'k100' => "",
            'k500' => "",
            'k100000' => "",
            'paginate' => $paginate,
            'startdata' => $startdata,
            'enddata' => $enddata,
            'alldata' => $alldata,
            'prefix' => $prefix,
            'server' => $server,
            'groups' => $groups
        ];

        if ($hozonn == "") {
            $data['dennshinone'] = "selected";
        } else if ($hozonn == "電子保存") {
            $data['dennshi'] = "selected";
        } else if ($hozonn == "スキャナ保存") {
            $data['scan'] = "selected";
        }

        if ($selectdata == "") {
            $data['yukou'] = "selected";
        } else if ($selectdata == "済") {
            $data['delete'] = "selected";
        } else if ($selectdata == "%%") {
            $data['zenken'] = "selected";
        }

        if ($teisyutu == "提出") {
            $data['teisyutu'] = "selected";
        } else if ($teisyutu == "受領") {
            $data['jyuryo'] = "selected";
        }

        if ($show == "25") {
            $data['k25'] = "selected";
        } else if ($show == "50") {
            $data['k50'] = "selected";
        } else if ($show == "100") {
            $data['k100'] = "selected";
        } else if ($show == "500") {
            $data['k500'] = "selected";
        } else if ($show == "100000") {
            $data['k100000'] = "selected";
        }

        // 取得したデータをビューに渡すなどの処理
        return view('information.search', $data);
    }

    public function download($id)

    {
        $file = File::where('id', $id)->first();
        if (config('prefix.server') == "cloud") {

            if ($file->ファイル形式 == "") {
                $key = $file->ファイルパス;
            } else {
                $key = $file->ファイルパス . "." . $file->ファイル形式;
            }
            $parts = explode('/', $key);
            $filename = end($parts); // 最後の要素を取得       


            $headers = [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"'
            ];

            return \Response::make(Storage::disk('s3')->get($key), 200, $headers);
        }



        // $parts = explode('/', $key);
        // $filename = end($parts); // 最後の要素を取得

        // $file = Storage::disk('s3')->get($key);
        // return response()->download(Storage::disk('s3')->url($key));

        // // S3からファイルをダウンロード
        // $filePath = $key;
        // $headers = [
        //     'Content-Type' => 'application/octet-stream',
        //     'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        // ];

        // try {
        //     // ファイルをダウンロード
        //     return response()->download(Storage::disk('s3')->url($filePath), $filename, $headers);
        // } catch (\Exception $e) {
        //     return back()->withErrors(['message' => 'ファイルをダウンロードできませんでした。']);
        // }
        else {
            //拡張子がないファイルの場合分け
            if ($file->ファイル形式 == "") {
                $filepath = Config::get('custom.file_upload_path') . "\\" . $file->ファイルパス;
            } else {
                $filepath = Config::get('custom.file_upload_path') . "\\" . $file->ファイルパス . '.' . $file->ファイル形式;
            }
        }

        // ファイルのダウンロード
        return response()->download($filepath);
    }

    public function detail($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $file = DB::table('files')
            ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', 'groups.グループ名')
            ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
            ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
            ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
            ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
            ->where('過去データID', $id)
            ->orderby('バージョン', 'desc')
            ->first();
        // ファイルのダウンロード
        return view('information.detailpage', compact('file', 'prefix', 'server'));
    }
    public function history($id)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        $files = DB::table('files')
            ->select('files.*', 'documents.書類', 'creators.name as 作成者', 'updaters.name as 更新者', 'groups.グループ名')
            ->leftJoin('documents', 'files.書類ID', '=', 'documents.id') // documentsテーブルの結合
            ->leftJoin('users as creators', 'files.保存者ID', '=', 'creators.id')
            ->leftJoin('users as updaters', 'files.更新者ID', '=', 'updaters.id')
            ->leftJoin('groups', 'files.グループID', '=', 'groups.id')
            ->where('過去データID', $id)
            ->orderby('バージョン', 'asc')
            ->get();


        $count = $files->count();

        return view('information.historypage', compact('files',  'count', 'prefix', 'server'));
    }

    public function imgget($id)
    {
        $img = File::where('id', $id)->first();



        $filepath = $img->ファイルパス;
        $extension = $img->ファイル形式;


        if (config('prefix.server') == "cloud") {
            // S3バケットの情報
            $bucket = 'astdocs.com';
            $key = $img->ファイルパス . "." . $img->ファイル形式;
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
            $path = Config::get('custom.file_upload_path') . "\\" . $filepath . '.' . $extension;
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

    public function usersettingGet()
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');

        $user = Auth::user();
        return view('information.usersetting', compact('user', 'prefix', 'server'));
    }
    public function usersettingPost(Request $request)
    {
        $user = Auth::user();
        if (!$request->input('name') || !$request->input('email')) {
            return "必須";
        }
        //パスワード設定変更
        else if ($request->input('oldpass')) {
            if (Hash::check($request->input('oldpass'), $user->password)) {
                $user->name = $request->input('name');
                $user->email = $request->input('email');
                $user->password = Hash::make($request->input('newpass'));
                $user->save();
                return "成功";
            } else {
                return "パスワードが違います";
            }
        } else {
            $user->name = $request->input('name');
            $user->email = $request->input('email');
            $user->save();
            return "成功";
        }
    }
    public function usercheck(Request $request)
    {
        $username = $request->input("username");
        $change = $request->input("change");
        $id = $request->input("id");

        if ($change == "change") {
            $user = User::whereRaw('BINARY name = ?', $username)
                ->whereNot('id', $id)
                ->first();
        } else {
            $user = User::whereRaw('BINARY name = ?', $username)
                ->first();
        }
        // Mysqlは標準では大文字と小文字の区別がされないため、BINARYをつけることによって区別される
        if ($user) {
            return "重複";
        } else {
            return "重複無し";
        }

        // Mysqlは標準では大文字と小文字の区別がされないため、BINARYをつけることによって区別される
        if (User::whereRaw('BINARY name = ?', $username)->first()) {
            return "重複";
        } else {
            return "重複無し";
        }
    }
    public function torihikisearch(Request $request)
    {
        $searchtext = $request->input("search");

        $clients = File::where('取引先', 'like', '%' . $searchtext . '%')
        ->where("最新フラグ","最新")
            ->where('削除フラグ', "")
            ->groupBy('取引先')
            ->orderBy('取引先', "asc")
            ->select('取引先')
            ->get();
        if ($clients->isEmpty()) {
            // 該当する結果がない場合の処理
            $clients = "該当なし";
            return response()->json($clients);
        } else {
            // 該当する結果がある場合の処理
            return response()->json($clients);
        }
    }

    public function question()
    {
        if (Auth::user()->管理 == "管理") {
            $filePath = public_path("pdf/admin.pdf");
        } else if (Auth::user()->管理 == "一般") {
            $filePath = public_path("pdf/general.pdf");
        }
        $fileContent = PDF::get($filePath);
        return Response::make($fileContent, 200, ['Content-Type' => 'application/pdf']);
    }
    public function excel($request, $files)
    {
        // エクセルテンプレートを読み込む
        $templatePath = public_path("xlsx/template.xlsx"); // テンプレートのパスを指定
        $reader = new XlsxReader();
        $spreadsheet = $reader->load($templatePath);

        // データベースから取得した値をエクセルに埋め込む
        $worksheet = $spreadsheet->getActiveSheet();

        $starthiduke = $request->input('starthiduke');
        if (!$starthiduke) {
            $starthiduke = "指定なし";
        }
        $endhiduke = $request->input('endhiduke');
        if (!$endhiduke) {
            $endhiduke = "指定なし";
        }
        $startkinngaku = $request->input('startkinngaku');
        if (!$startkinngaku) {
            $startkinngaku = "指定なし";
        }
        $endkinngaku = $request->input('endkinngaku');
        if (!$endkinngaku) {
            $endkinngaku = "指定なし";
        }
        $torihikisaki = $request->input('torihikisaki');
        if (!$torihikisaki) {
            $torihikisaki = "指定なし";
        }
        $syoruikubunn = $request->input('syoruikubunn');
        if (!$syoruikubunn) {
            $syoruikubunn = "指定なし";
        } else {
            $syoruikubunn = Document::where("id", $syoruikubunn)->first();
            $syoruikubunn = $syoruikubunn->書類;
        }
        $teisyutu = $request->input('teisyutu');
        if (!$teisyutu) {
            $teisyutu = "指定なし";
        }
        $hozonn = $request->input('hozonn');
        if (!$hozonn) {
            $hozonn = "指定なし";
        }
        $kennsakuword = $request->input('kennsakuword');
        if (!$kennsakuword) {
            $kennsakuword = "指定なし";
        }
        $group = $request->input('group');
        if (!$group) {
            $group = "指定なし";
        } else {
            $group = Group::where("id", $group)->first();
            $group = $group->グループ名;
        }
        $updater = $request->input('updater');
        if (!$updater) {
            $updater = "指定なし";
        } else {
            $updater = User::where("id", $updater)->first();
            $updater = $updater->name;
        }
        $creater = $request->input('creater');
        if (!$creater) {
            $creater = "指定なし";
        } else {
            $creater = User::where("id", $creater)->first();
            $creater = $creater->name;
        }

        $worksheet->setCellValue('B2', $starthiduke);
        $worksheet->setCellValue('D2', $endhiduke);
        $worksheet->setCellValue('B3', $startkinngaku);
        $worksheet->setCellValue('D3', $endkinngaku);
        $worksheet->setCellValue('B4', $torihikisaki);
        $worksheet->setCellValue('F2', $syoruikubunn);
        $worksheet->setCellValue('F3', $teisyutu);
        $worksheet->setCellValue('F4', $hozonn);
        $worksheet->setCellValue('H2', $kennsakuword);
        $worksheet->setCellValue('H3', $updater);
        $worksheet->setCellValue('H4', $creater);
        $worksheet->setCellValue('K2', $group);

        // セルのスタイルを設定
        $style = [
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'AEAAAA'], // 点線の色を設定
                ],
            ],
            'font' => [
                'size' => 10, // フォントサイズを10ポイントに設定
            ],
        ];

        $row = 7;
        foreach ($files as $file) {
            $worksheet->getStyle('A' . $row . ':K' . $row)->applyFromArray($style);
            // E列とJ列のセルだけを中央寄せに設定
            $worksheet->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $worksheet->getStyle('K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $worksheet->setCellValue('A' . $row, substr_replace(substr_replace($file->日付, '/', 6, 0), '/', 4, 0));
            $worksheet->setCellValue('B' . $row, $file->金額);
            $worksheet->setCellValue('C' . $row, $file->取引先);
            $worksheet->setCellValue('D' . $row, $file->書類);
            $worksheet->setCellValue('E' . $row, $file->提出);
            $worksheet->setCellValue('F' . $row, $file->保存);
            $worksheet->setCellValue('G' . $row, $file->ファイル形式);
            //個人グループ名の場合は空欄
            if ($file->グループID > 100000) {
                $worksheet->setCellValue('H' . $row, $file->グループ名);
            }
            $worksheet->setCellValue('I' . $row, str_replace('(削除ユーザー)', '', $file->更新者));
            $worksheet->setCellValue('J' . $row, str_replace('(削除ユーザー)', '', $file->作成者));
            $worksheet->setCellValue('K' . $row, $file->削除フラグ);
            $row++;
        }
        $fileName = '帳簿一覧.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header('Cache-Control: max-age=0');

        $writer = new XlsxWriter($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
