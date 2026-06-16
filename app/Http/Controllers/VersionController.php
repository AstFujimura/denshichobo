<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class VersionController extends Controller
{
  private const FEATURE_COLUMNS = [
    'tameru' => 'TAMERU',
    'フロー' => 'フロー',
    'スケジュール' => 'スケジュール',
    '名刺' => '名刺',
    '文書' => '文書',
    'BANBAN' => 'BANBAN',
    'ichifuji' => 'いちふじ',
  ];

  public function versionGet()
  {
    if ($redirect = $this->ensureAstecUser()) {
      return $redirect;
    }

    $prefix = config('prefix.prefix');
    if ($prefix !== '') {
      $prefix = '/' . $prefix;
    }
    $server = config('prefix.server');
    $version = Version::findOrFail(1);
    $featureColumns = self::FEATURE_COLUMNS;

    return view('admin.version', compact('version', 'featureColumns', 'prefix', 'server'));
  }

  public function versionPost(Request $request)
  {
    if ($redirect = $this->ensureAstecUser()) {
      return $redirect;
    }

    $version = Version::findOrFail(1);

    foreach (array_keys(self::FEATURE_COLUMNS) as $column) {
      $version->{$column} = $request->boolean($column);
    }

    $version->save();

    $message = '機能設定を更新しました。';
    $warning = null;

    try {
      $exitCode = Artisan::call('route:cache');
      if ($exitCode === 0) {
        $message .= ' ルートキャッシュを更新しました。';
      } else {
        $warning = 'ルートキャッシュの更新に失敗しました。手動で php artisan route:cache を実行してください。';
      }
    } catch (\Throwable $e) {
      $warning = 'ルートキャッシュの更新に失敗しました。手動で php artisan route:cache を実行してください。';
    }

    $redirect = redirect()->route('versionGet')->with('success', $message);
    if ($warning !== null) {
      return $redirect->with('warning', $warning);
    }

    return $redirect;
  }

  private function ensureAstecUser()
  {
    if (Auth::id() !== 1) {
      return redirect()->route('topGet');
    }

    return null;
  }
}
