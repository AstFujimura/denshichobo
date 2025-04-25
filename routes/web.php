<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TopController;
use App\Http\Controllers\RegistController;
use App\Http\Controllers\EditController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\FlowController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\ScheduleController;
use App\Http\Middleware\CheckSessionTimeout;
use Illuminate\Support\Facades\Config;

use App\Models\Version;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // ルート名が"topGet"のルートにリダイレクト
    return redirect()->route('topGet');
});
$prefix = config('prefix.prefix');
Route::prefix($prefix)->group(function () {

    // CSSフォルダへのルーティング
    Route::get('/css/{file}', [AssetController::class, 'css'])->where('file', '.*css');

    // JavaScriptフォルダへのルーティング
    Route::get('/js/{file}', [AssetController::class, 'js'])->where('file', '.*js');

    // 画像フォルダへのルーティング
    Route::get('/img/{file}', [AssetController::class, 'img'])->where('file', '.*\.(jpg|jpeg|png|gif|ico|svg)');

    // 画像フォルダへのルーティング
    Route::get('/jquery/{file}', [AssetController::class, 'jquery'])->where('file', '.*js');

    // 画像フォルダへのルーティング
    Route::get('/icon/{file}', [AssetController::class, 'icon'])->where('file', '.*ico');

    // フォントフォルダへのルーティング
    Route::get('/font/{file}', [AssetController::class, 'font'])->where('file', '.*\.(TTC|TTF|ttf|otf|woff|woff2)');
    // ストレージフォルダへのルーティング
    Route::get('/storage/{folder}/{file}', [AssetController::class, 'storage'])->where('file', '.*');


    //  ログインページ
    Route::get('login', [LoginController::class, 'loginGet'])->name('loginGet');
    // ログインページにポストで情報を投げた時
    Route::post('login', [LoginController::class, 'LoginPost'])->name('loginPost');
    // javascriptが無効の場合
    Route::get('/jserror', [ErrorController::class, 'jserrorGet'])->name('jserrorGet');

    Route::group(['middleware' => 'auth'], function () {
        // ログアウト時（すぐにリダイレクトされる）
        Route::get('logout', [LogoutController::class, 'Logout'])->name('logout');




        // トップページ
        Route::get('/', [TopController::class, 'index'])->name('topGet');
        // トップページ
        Route::get('/search', [TopController::class, 'search'])->name('searchPost');

        Route::get('/download/{id}', [TopController::class, 'download'])->name('download');

        Route::get('/detail/{id}', [TopController::class, 'detail'])->name('detail');

        Route::get('/history/{id}', [TopController::class, 'history'])->name('history');

        Route::get('/img/{id}', [TopController::class, 'imgget'])->name('imgget');

        Route::get('/usersetting', [TopController::class, 'usersettingGet'])->name('usersettingGet');

        Route::post('/usersetting', [TopController::class, 'usersettingPost'])->name('usersettingPost');

        Route::get('/torihikisaki', [TopController::class, 'torihikisearch'])->name('torihikisearch');

        // 新規登録ページ
        Route::get('/regist', [RegistController::class, 'registGet'])->name('registGet');
        // 新規登録ページにポストで情報を投げた時
        Route::post('/regist', [RegistController::class, 'registPost'])->name('registPost');
        // 新規登録ページにポストで情報を投げた時
        Route::post('/regist/cloud', [RegistController::class, 'registcloudPost'])->name('registcloudPost');

        Route::get('/objectURL', [RegistController::class, 'registURL'])->name('registURl');


        // 変更ページ
        Route::get('/edit/{path}', [EditController::class, 'editGet'])->name('editGet');
        // 変更ページにポストで情報を投げた時
        Route::post('/edit/{path}', [EditController::class, 'editPost'])->name('editPost');

        // 削除
        Route::get('/delete/{path}', [EditController::class, 'deleteGet'])->name('deleteGet');

        // numの数だけダミーデータを登録するとき
        Route::get('/test/{num}', [TestController::class, 'testGet'])->name('testGet');

        // 管理者ページトップ（ユーザー一覧）
        Route::get('/admin', [AdminController::class, 'adminGet'])->name('adminGet');

        // ユーザー変更画面
        Route::get('/admin/edit/{id}', [AdminController::class, 'admineditGet'])->name('admineditGet');

        // ユーザー登録画面
        Route::get('/admin/regist', [AdminController::class, 'adminregistGet'])->name('adminregistGet');

        // ユーザー登録ページにポストで情報を投げた時
        Route::post('/admin/regist', [AdminController::class, 'adminregistPost'])->name('adminregistPost');

        // ユーザー変更ページにポストで情報を投げた時
        Route::put('/admin/edit/{id}', [AdminController::class, 'admineditPut'])->name('admineditPut');

        // 変更ページにポストで情報を投げた時
        Route::delete('/admin/delete/{id}', [AdminController::class, 'adminDelete'])->name('adminDelete');

        // パスワードリセットをポストで情報を投げた時
        Route::post('/admin/reset/{id}', [AdminController::class, 'adminresetPost'])->name('adminresetPost');

        //管理ユーザーを変更しても大丈夫か確認するとき
        Route::get('/admincheck/{id}', [AdminController::class, 'admincheck'])->name('admincheck');

        //書類管理ページ
        Route::get('/admin/document', [AdminController::class, 'admindocumentGet'])->name('admindocumentGet');

        //書類を削除しても大丈夫か確認するとき
        Route::get('/admin/documentcheck/{id}', [AdminController::class, 'documentcheck'])->name('documentcheck');

        // 書類管理ページに変更をポストするとき
        Route::post('/admin/document', [AdminController::class, 'admindocumentPost'])->name('admindocumentPost');

        // グループ登録・編集画面
        Route::get('/admin/group/regist', [AdminController::class, 'admingroupregistGet'])->name('admingroupregistGet');

        //グループを削除しても大丈夫か確認するとき
        Route::get('/admin/groupcheck/{id}', [AdminController::class, 'groupcheck'])->name('groupcheck');

        // グループ登録のポスト
        Route::post('/admin/group/regist', [AdminController::class, 'admingroupregistPost'])->name('admingroupregistPost');



        Route::get('/error/{code}', [ErrorController::class, 'errorGet'])->name('errorGet');

        Route::get('/usercheck', [TopController::class, 'usercheck'])->name('usercheck');

        Route::post('/userexcel', [TestController::class, 'userexcel'])->name('userexcel');

        Route::get('/question', [TopController::class, 'question'])->name('question');

        // -------------------------------承認機能----------------------------------------------------
        if (Version::where('フロー', true)->first()) {
            // 機能選択画面
            Route::get('/start_choice', [LoginController::class, 'startchoiceGet'])->name('startchoiceGet');
            // ワークフロー登録
            Route::get('/workflowregist', [FlowController::class, 'workflowregistget'])->name('workflowregistget');
            Route::post('/workflowregist', [FlowController::class, 'workflowregistpost'])->name('workflowregistpost');
            // ワークフロー登録時のユーザーの非同期通信先API
            Route::get('/flowuserlist', [FlowController::class, 'flowuserlist'])->name('flowuserlist');
            // ワークフローのフロントエンドオブジェクトを返す非同期通信先API
            Route::get('/flowobject/{id}', [FlowController::class, 'flowobject'])->name('flowobject');
            // ワークフローのポスト前のユーザー名のチェックを行う非同期通信先API
            Route::get('/flowusercheck', [FlowController::class, 'flowusercheck'])->name('flowusercheck');

            // ワークフローの読み取り専用情報のうちメタ情報を返す非同期通信API
            Route::get('/viewonlymetaworkflow/{id}', [FlowController::class, 'viewonlymetaworkflow'])->name('viewonlymetaworkflow');
            // ワークフローの読み取り専用情報を返す非同期通信先API
            Route::get('/viewonlyworkflow/{id}', [FlowController::class, 'viewonlyworkflow'])->name('viewonlyworkflow');
            // ワークフローの読み取り専用フローの時のグループリストを返す非同期通信先API
            Route::get('/flowgrouplist/{groupid}', [FlowController::class, 'flowgrouplist'])->name('flowgrouplist');

            // ワークフローメニュー画面
            Route::get('/workflow', [FlowController::class, 'workflow'])->name('workflow');
            // ワークフローマスタ一覧
            Route::get('/workflow/master', [FlowController::class, 'workflowmaster'])->name('workflowmaster');
            // ワークフローマスタ詳細
            Route::get('/workflow/master/{id}', [FlowController::class, 'workflowmasterdetail'])->name('workflowmasterdetail');
            // ワークフローマスタ編集
            Route::get('/workflowedit/{id}', [FlowController::class, 'workfloweditget'])->name('workfloweditget');
            // ワークフローマスタ削除
            Route::get('/workflowdelete/{id}', [FlowController::class, 'workflowdeleteget'])->name('workflowdeleteget');
            // メール設定
            Route::get('/workflow/mail', [FlowController::class, 'mailsettingget'])->name('mailsettingget');
            // メール送信
            Route::post('/workflow/mail', [FlowController::class, 'mailsettingpost'])->name('mailsettingpost');
            // テストメール送信
            Route::post('/workflow/mail/test', [FlowController::class, 'mailsettingtestsend'])->name('mailsettingtestsend');
            // カテゴリ設定
            Route::get('/workflow/category', [FlowController::class, 'categoryget'])->name('categoryget');
            //カテゴリ名変更の非同期通信API
            Route::get('/workflow/category/change/{id}/{value}', [FlowController::class, 'categorychangeget'])->name('categorychangeget');
            //カテゴリ詳細変更
            Route::get('/workflow/category/detail/{id}', [FlowController::class, 'categorydetailget'])->name('categorydetailget');
            // カテゴリTAMERU設定　idはcategoryのid
            Route::get('/workflow/category/tameru/setting/{id}', [FlowController::class, 'categorytamerusettingget'])->name('categorytamerusettingget');
            // カテゴリTAMERU設定ポスト
            Route::post('/workflow/category/tameru/setting', [FlowController::class, 'categorytamerusettingpost'])->name('categorytamerusettingpost');
            //カテゴリ追加
            Route::get('/workflow/category/regist', [FlowController::class, 'categoryregistget'])->name('categoryregistget');
            //カテゴリ追加ポスト
            Route::post('/workflow/category/regist', [FlowController::class, 'categoryregistpost'])->name('categoryregistpost');
            // カテゴリ削除
            Route::post('/workflow/category/delete', [FlowController::class, 'categorydeletepost'])->name('categorydeletepost');



            //カテゴリ詳細変更ポスト
            Route::post('/workflow/category/detail', [FlowController::class, 'categorydetailpost'])->name('categorydetailpost');
            // カテゴリ情報の非同期通信API
            Route::get('/workflow/category/info/{id}', [FlowController::class, 'categoryinfoget'])->name('categoryinfoget');
            //カテゴリ承認設定
            Route::get('/workflow/category/approval/setting/{id}', [FlowController::class, 'categoryapprovalsettingget'])->name('categoryapprovalsettingget');
            //カテゴリ承認設定ポスト
            Route::post('/workflow/category/approval/setting', [FlowController::class, 'categoryapprovalsettingpost'])->name('categoryapprovalsettingpost');
            //承認用紙のpdfのapi
            Route::get('/workflow/approval/setting/img/{id}', [FlowController::class, 'approvalsettingpdf'])->name('approvalsettingpdf');



            // ワークフロー申請
            Route::get('/workflow/application', [FlowController::class, 'workflowapplicationget'])->name('workflowapplicationget');
            // ワークフロー申請ポスト
            Route::post('/workflow/application', [FlowController::class, 'workflowapplicationpost'])->name('workflowapplicationpost');

            // ワークフロー再申請
            Route::get('/workflow/reapply/{id}', [FlowController::class, 'workflowreapplyget'])->name('workflowreapplyget');
            // ワークフロー再申請ポスト
            Route::post('/workflow/reapply', [FlowController::class, 'workflowreapplypost'])->name('workflowreapplypost');


            // ワークフロー経路選択
            Route::get('/workflow/choice/{id}', [FlowController::class, 'workflowchoiceget'])->name('workflowchoiceget');
            // ワークフロー経路選択ポスト
            Route::post('/workflow/choice', [FlowController::class, 'workflowchoicepost'])->name('workflowchoicepost');

            // ワークフロー申請印
            Route::get('/workflow/application/stamp/{id}', [FlowController::class, 'workflowapplicationstampget'])->name('workflowapplicationstampget');
            // ワークフロー申請印ポスト
            Route::post('/workflow/application/stamp', [FlowController::class, 'workflowapplicationstamppost'])->name('workflowapplicationstamppost');
            // ワークフロー再申請印
            Route::get('/workflow/reapply/stamp/{id}', [FlowController::class, 'workflowreapplicationstampget'])->name('workflowreapplicationstampget');
            // ワークフロー再申請印ポスト
            Route::post('/workflow/reapply/stamp', [FlowController::class, 'workflowreapplicationstamppost'])->name('workflowreapplicationstamppost');


            // ワークフロー確認画面
            Route::get('/workflow/confirm/{id}', [FlowController::class, 'workflowconfirmget'])->name('workflowconfirmget');
            // ワークフロー最終申請
            Route::post('/workflow/confirm', [FlowController::class, 'workflowconfirmpost'])->name('workflowconfirmpost');

            // ワークフロー申請一覧
            Route::get('/workflow/view', [FlowController::class, 'workflowviewget'])->name('workflowviewget');
            // ワークフロー申請情報(idはt_flowsのid)
            Route::get('/workflow/application/detail/{id}', [FlowController::class, 'workflowapplicationdetailget'])->name('workflowapplicationdetailget');

            // ワークフロー申請取消し
            Route::get('/workflow/application/cancel/{id}', [FlowController::class, 'workflowapplicationcancelget'])->name('workflowapplicationcancelget');

            // 印鑑設定
            Route::get('/workflow/stamp', [FlowController::class, 'workflowstampget'])->name('workflowstampget');
            // 印鑑設定ポスト
            Route::post('/workflow/stamp', [FlowController::class, 'workflowstamppost'])->name('workflowstamppost');
            // 印鑑データを返すAPI
            Route::get('/workflow/stamp/img/{id}', [FlowController::class, 'workflowstampimgget'])->name('workflowstampimgget');



            // ワークフローファイル閲覧API
            Route::get('/workflow/img/{id}', [FlowController::class, 'flowimgget'])->name('flowimgget');
            // ワークフローファイルダウンロード
            Route::get('/workflow/download/{id}', [FlowController::class, 'flowdownload'])->name('flowdownload');
            // 承認用紙ダウンロード
            // Route::get('/workflow/approval/download/{id}', [FlowController::class, 'workflowapprovaldownload'])->name('workflowapprovaldownload');
            
            // 承認一覧
            Route::get('/workflow/approvalview', [FlowController::class, 'workflowapprovalview'])->name('workflowapprovalview');
            // 承認(idはt_approvalsのid)
            Route::get('/workflow/approval/{id}', [FlowController::class, 'workflowapprovalget'])->name('workflowapprovalget');
            // 承認ポスト
            Route::post('/workflow/approval', [FlowController::class, 'workflowapprovalpost'])->name('workflowapprovalpost');
            // 承認印 idはt_approvalのid
            Route::get('/workflow/approval/stamp/{id}', [FlowController::class, 'workflowapprovalstampget'])->name('workflowapprovalstampget');
            // 承認印ポスト
            Route::post('/workflow/approval/stamp/', [FlowController::class, 'workflowapprovalstamppost'])->name('workflowapprovalstamppost');



            // 閲覧一覧
            Route::get('/workflow/checkview', [FlowController::class, 'workflowcheckviewget'])->name('workflowcheckviewget');
            // 閲覧詳細
            Route::get('/workflow/checkview/detail/{id}', [FlowController::class, 'workflowcheckdetailget'])->name('workflowcheckdetailget');

            // ファイル管理画面
            Route::get('/workflow/file', [FlowController::class, 'workflowfileget'])->name('workflowfileget');
            // 一括ダウンロード
            Route::get('/workflow/file/download/all', [FlowController::class, 'workflowfilealldownload'])->name('workflowfilealldownload');


            //    グループの役職設定画面
            Route::get('/admin/groupposition/{id}', [AdminController::class, 'admingrouppositionGet'])->name('admingrouppositionGet');
            //    グループの役職設定ポスト
            Route::post('/admin/groupposition/{id}', [AdminController::class, 'admingrouppositionPost'])->name('admingroupdetailPost');

            //    グループのユーザー設定画面
            Route::get('/admin/groupuser/{id}', [AdminController::class, 'admingroupuserGet'])->name('admingroupuserGet');
            //    グループのユーザー設定ポスト
            Route::post('/admin/groupuser/{id}', [AdminController::class, 'admingroupuserPost'])->name('admingroupuserPost');


            //    グループの役職削除API
            Route::get('/admin/grouppositiondelete/{id}', [AdminController::class, 'admingrouppositiondeleteGet'])->name('admingrouppositiondeleteGet');



            //    ワークフローエラーコード
            Route::get('/workflowerror/{code}', [ErrorController::class, 'workflowerrorGet'])->name('workflowerrorGet');
        }


        // -----------------------------名刺管理--------------------------------
        if (Version::where('名刺', true)->first()) {
            // 名刺管理画面
            Route::get('/card/cardview', [CardController::class, 'cardviewget'])->name('cardviewget');
            // 会社一覧画面
            Route::get('/card/companyview', [CardController::class, 'cardcompanyviewget'])->name('cardcompanyviewget');
            // 名刺詳細(idはcardusersのid)
            Route::get('/card/detail/{id}', [CardController::class, 'carddetailget'])->name('carddetailget');
            // 名刺情報API
            Route::get('/card/history/{id}', [CardController::class, 'cardinfoget'])->name('cardinfoget');
            // 名刺登録
            Route::get('/card/regist', [CardController::class, 'cardregistget'])->name('cardregistget');
            // 名刺編集
            Route::get('/card/edit/{id}', [CardController::class, 'cardeditget'])->name('cardeditget');
            // 名刺追加(cardusersのid)
            Route::get('/card/add/{id}', [CardController::class, 'cardaddget'])->name('cardaddget');
            // 名刺削除
            Route::post('/card/delete', [CardController::class, 'carddeletepost'])->name('carddeletepost');
            // 名刺登録・編集ポスト
            Route::post('/card/regist', [CardController::class, 'cardregistpost'])->name('cardregistpost');
            // 名刺OCR
            Route::post('/card/ocr', [CardController::class, 'cardocrpost'])->name('cardocrpost');
            // 名刺画像取得
            Route::get('/card/img/{id}/{front}', [CardController::class, 'cardimgget'])->name('cardimgget');
            // 会社候補取得
            Route::get('/card/company/candidate', [CardController::class, 'companycandidateget'])->name('cardcompanycandidateget');
            // 会社情報API
            Route::get('/card/company/info/{id}', [CardController::class, 'companyinfoget'])->name('companyinfoget');
            // 名刺テスト
            Route::get('/card/test', [CardController::class, 'cardtestget'])->name('cardtestget');
        
            // 名刺複数アップロード
            Route::get('/card/multiple/upload', [CardController::class, 'cardmultipleuploadget'])->name('cardmultipleuploadget');
            // 名刺複数アップロードポスト
            Route::post('/card/multiple/upload', [CardController::class, 'cardmultipleuploadpost'])->name('cardmultipleuploadpost');
            //    OpenAI呼び出し
            Route::post('/card/openai/process', [CardController::class, 'cardopenai'])->name('cardopenai');
           
            // 複数アップロード進捗
            Route::get('/card/multiple/progress', [CardController::class, 'cardmultipleprogressget'])->name('cardmultipleprogressget');


            Route::get('/card/multiple/test', [CardController::class, 'cardmultipletestget'])->name('cardmultipletestget');
        }

        // -----------------------------スケジュール--------------------------------
        if (Version::where('スケジュール', true)->first()) {
            Route::get('/schedule', [ScheduleController::class, 'scheduleget'])->name('scheduleget');
            Route::get('/schedule/week', [ScheduleController::class, 'scheduleweekget'])->name('scheduleweekget');
            Route::get('/schedule/month', [ScheduleController::class, 'schedulemonthget'])->name('schedulemonthget');
            Route::get('/schedule/regist', [ScheduleController::class, 'scheduleregistget'])->name('scheduleregistget');
            Route::post('/schedule/regist', [ScheduleController::class, 'scheduleregistpost'])->name('scheduleregistpost');
            Route::post('/schedule/delete', [ScheduleController::class, 'scheduledeletepost'])->name('scheduledeletepost');
            Route::get('/schedule/holiday', [ScheduleController::class, 'scheduleholidayget'])->name('scheduleholidayget');
            Route::get('/schedule/holiday/regist', [ScheduleController::class, 'scheduleholidayregistget'])->name('scheduleholidayregistget');
            Route::post('/schedule/holiday/regist', [ScheduleController::class, 'scheduleholidayregistpost'])->name('scheduleholidayregistpost');
            Route::get('/schedule/candidate', [ScheduleController::class, 'schedulecandidateget'])->name('schedulecandidateget');
            Route::get('/schedule/group/regist', [ScheduleController::class, 'schedulegroupregistget'])->name('schedulegroupregistget');
            Route::post('/schedule/group/regist', [ScheduleController::class, 'schedulegroupregistpost'])->name('schedulegroupregistpost');
            Route::get('/schedule/regular/regist', [ScheduleController::class, 'scheduleregularregistget'])->name('scheduleregularregistget');
            Route::post('/schedule/regular/regist', [ScheduleController::class, 'scheduleregularregistpost'])->name('scheduleregularregistpost');
            Route::get('/schedule/term/regist', [ScheduleController::class, 'scheduletermregistget'])->name('scheduletermregistget');
            Route::post('/schedule/term/regist', [ScheduleController::class, 'scheduletermregistpost'])->name('scheduletermregistpost');
            Route::get('/schedule/master/regist', [ScheduleController::class, 'schedulemasterregistget'])->name('schedulemasterregistget');
            Route::post('/schedule/master/regist', [ScheduleController::class, 'schedulemasterregistpost'])->name('schedulemasterregistpost');
            // Route::get('/schedule/csv', [ScheduleController::class, 'schedulecsvget'])->name('schedulecsvget');
            // Route::post('/schedule/csv', [ScheduleController::class, 'schedulecsvpost'])->name('schedulecsvpost');
        }
    });
});
