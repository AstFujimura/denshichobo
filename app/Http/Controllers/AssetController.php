<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

class AssetController extends Controller
{
    public function css($file)
    {
        return $this->getResponse('css', $file, 'text/css');
    }

    public function js($file)
    {
        return $this->getResponse('js', $file, 'application/javascript');
    }

    public function jquery($file)
    {
        return $this->getResponse('jquery', $file, 'application/javascript');
    }

    public function img($file)
    {
        return $this->getResponse('img', $file, 'image/svg+xml'); // 画像のコンテンツタイプに合わせて適切なものに変更してください
    }

    private function getResponse($folder, $file, $contentType)
    {
        $filePath = public_path("$folder/$file");

        if (File::exists($filePath)) {
            $fileContent = File::get($filePath);
            return Response::make($fileContent, 200, ['Content-Type' => $contentType]);
        } else {
            abort(404);
        }
    }
}
