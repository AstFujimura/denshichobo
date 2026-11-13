<?php

namespace App\Http\Controllers;

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

    public function storage($folder, $file)
    {
        $fileExtension = pathinfo($file, PATHINFO_EXTENSION);

        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];

        if (isset($mimeTypes[$fileExtension])) {
            return $this->storagegetResponse($folder, $file, $mimeTypes[$fileExtension]);
        }

        abort(404);
    }

    public function img($file)
    {
        $fileExtension = strtolower((string) pathinfo($file, PATHINFO_EXTENSION));

        $mimeTypes = [
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
        ];

        if (! isset($mimeTypes[$fileExtension])) {
            abort(404);
        }

        return $this->getResponse('img', $file, $mimeTypes[$fileExtension]);
    }

    private function storagegetResponse($folder, $file, $contentType)
    {
        $filePath = storage_path("app/public/$folder/$file");

        if (File::exists($filePath)) {
            $fileContent = File::get($filePath);

            return Response::make($fileContent, 200, ['Content-Type' => $contentType]);
        }

        abort(404);
    }

    private function getResponse($folder, $file, $contentType)
    {
        $filePath = public_path("$folder/$file");

        if (File::exists($filePath)) {
            $fileContent = File::get($filePath);

            return Response::make($fileContent, 200, ['Content-Type' => $contentType]);
        }

        abort(404);
    }
}
