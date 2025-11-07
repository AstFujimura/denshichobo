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

class TranscribeController extends Controller
{
    public function transcribeget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('transcribe.transcribe', compact('prefix', 'server'));
    }
    public function transcribepost(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->store('temp');
        $filePath = storage_path('app/' . $path);

        $imageData = null;

        if ($ext === 'pdf') {
            // --- PDF → 画像変換（Lambda） ---
            $pdf_base64 = base64_encode(file_get_contents($filePath));
            $lambdaUrl = config('lambda.api_url');
            $lambdaKey = config('lambda.api_key');

            $lambdaResponse = Http::withHeaders([
                'x-api-key' => $lambdaKey,
                'Content-Type' => 'application/json',
            ])->post($lambdaUrl, [
                'pdf_base64' => $pdf_base64,
            ]);
            $data = $lambdaResponse->json();
            // Lambdaのレスポンスにjpg_listが含まれている場合
            if (!isset($data['jpg_list']) || empty($data['jpg_list'])) {
                return response()->json(['error' => 'Lambdaから画像データが返りませんでした'], 500);
            }
            // 1ページ目だけを処理（必要に応じて全ページループも可能）
            $jpg_base64 = $data['jpg_list'][0];
            $imageData = base64_decode($jpg_base64);
        } else {
            // --- 画像ファイルの場合 ---
            $imageData = file_get_contents($filePath);
        }

        // --- Gemini API 呼び出し ---
        $apiKey = config('gemini.api_key');
        $imageBase64 = base64_encode($imageData);
        $mimeType = 'image/jpeg';
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

        Storage::delete($path);

        if ($response->failed()) {
            return response()->json(['error' => 'Gemini呼び出し失敗'], 500);
        }

        $result = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? '(結果なし)';

        return response()->json([
            'result' => $result,
        ]);
    }
    private function getJsonPrompt($rawText = null)
    {
        return "
        以下の画像（またはテキスト）をOCR解析し、各項目の内容を抽出してください。

        出力フォーマット:
        {
            \"現在のご契約者名義\": \"\",
            \"現在のご契約者名義カナ\": \"\",
            \"現在のご契約者性別\": \"\",
            \"現在のご契約者生年月日\": \"\",
            \"変更後のご契約者名義\": \"\",
            \"変更後のご契約者名義カナ\": \"\",
            \"変更後のご契約者名義性別\": \"\",
            \"変更後のご契約者名義生年月日\": \"\",
            \"変更後のご契約者名義続柄\": \"\",
            \"変更後のご契約者名義変更理由\": \"\",
            \"変更後のご契約者名義勤務先\": \"\",
            \"変更後のご契約者名義勤務先電話番号\": \"\",
            \"ご契約者住所\": \"\",
            \"ご契約者住所カナ\": \"\",
            \"ご契約者郵便番号\": \"\",
            \"ご契約者【自宅】電話番号\": \"\",
            \"ご契約者【携帯】電話番号\": \"\",
            \"ご利用先住所\": \"\",
            \"ご利用先住所カナ\": \"\",
            \"ご利用先郵便番号\": \"\",
        }

        ";
    }
}
