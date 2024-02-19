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
            // ワークフロー登録
            Route::get('/workflowregist', [FlowController::class, 'workflowregistget'])->name('workflowregistget');
            Route::post('/workflowregist', [FlowController::class, 'workflowregistpost'])->name('workflowregistpost');
            // ワークフロー登録時のユーザーの非同期通信先API
            Route::get('/flowuserlist', [FlowController::class, 'flowuserlist'])->name('flowuserlist');
            // ワークフローのフロントエンドオブジェクトを返す非同期通信先API
            Route::get('/flowobject/{id}', [FlowController::class, 'flowobject'])->name('flowobject');
            // ワークフローのポスト前のユーザー名のチェックを行う非同期通信先API
            Route::get('/flowusercheck', [FlowController::class, 'flowusercheck'])->name('flowusercheck');


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
            // ワークフロー申請
            Route::get('/workflow/application', [FlowController::class, 'workflowapplicationget'])->name('workflowapplicationget');
            // ワークフロー申請
            Route::post('/workflow/application', [FlowController::class, 'workflowapplicationpost'])->name('workflowapplicationpost');
            // ワークフロー経路選択
            Route::get('/workflow/choice/{id}', [FlowController::class, 'workflowchoiceget'])->name('workflowchoiceget');

            // 承認一覧
            Route::get('/workflow/approval', [FlowController::class, 'workflowapproval'])->name('workflowapproval');




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
        }
    });
});
