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

    public function icon($file)
    {
        return $this->getResponse('icon', $file, 'image/x-icon');
    }
    public function font($file)
    {
        return $this->getResponse('font', $file, 'font/ttf');
    }

    public function img($file)
    {
        // ファイルの拡張子を取得
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);
        if ($fileExtension == 'svg'){
            return $this->getResponse('img', $file, 'image/svg+xml');
        }
        else if($fileExtension == 'gif'){
            return $this->getResponse('img', $file, 'image/gif');
        }
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
