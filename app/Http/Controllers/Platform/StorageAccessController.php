<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorageAccessController extends Controller
{
    public function test(Request $request): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:1024'],
        ]);

        $path = rtrim($data['path'], "\\/");
        if (! is_dir($path)) {
            return response()->json([
                'message' => '指定したフォルダーが存在しないか、サーバーから参照できません。',
            ], 422);
        }

        if (! is_readable($path)) {
            return response()->json([
                'message' => 'フォルダーは存在しますが、読み取り権限がありません。',
            ], 422);
        }

        $testFile = $path . DIRECTORY_SEPARATOR . '.binder_access_test_' . Str::random(16);
        $handle = @fopen($testFile, 'x+b');
        if ($handle === false) {
            return response()->json([
                'message' => 'フォルダーは参照できますが、ファイルを作成できません。',
            ], 422);
        }

        $written = @fwrite($handle, 'binder storage access test');
        @fclose($handle);
        $deleted = @unlink($testFile);

        if ($written === false || ! $deleted) {
            if (is_file($testFile)) {
                @unlink($testFile);
            }

            return response()->json([
                'message' => 'ファイルの書き込みまたは削除を完了できませんでした。',
            ], 422);
        }

        return response()->json([
            'message' => '接続成功: フォルダーの読み取り・ファイル作成・削除が可能です。',
        ]);
    }
}
