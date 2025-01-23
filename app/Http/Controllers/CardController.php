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
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Tcpdf\Fpdi;

class CardController extends Controller
{
    public function cardviewget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('card.cardview', compact("prefix", "server"));
    }
    public function cardcompanyviewget(Request $request)
    {
        return view('card.cardcompanyview');
    }
    public function carddetailget(Request $request, $id)
    {
        return view('card.carddetail');
    }
    public function cardregistget(Request $request)
    {
        $prefix = config('prefix.prefix');
        if ($prefix !== "") {
            $prefix = "/" . $prefix;
        }
        $server = config('prefix.server');
        return view('card.cardregist', compact("prefix", "server"));
    }
    public function cardeditget(Request $request, $id)
    {
        return view('card.cardedit');
    }

    public function cardregistpost(Request $request)
    {
        $card_file_front = $request->file('card_file_front');
        $path = $card_file_front->store('business_cards', 'public');

        $visionClient = new ImageAnnotatorClient();
        $imagePath = storage_path('app/public/' . $path);
        $imageContent = file_get_contents($imagePath);
        $response = $visionClient->textDetection($imageContent);
        $text = $response->getTextAnnotations();

        if (empty($text)) {
            return response()->json(['error' => 'テキストが検出されませんでした。'], 400);
        }

        // バウンディングボックスの取得（最初の結果を使用）
        $bounds = $text[0]->getBoundingPoly()->getVertices();
        $x1 = $bounds[0]->getX();
        $y1 = $bounds[0]->getY();
        $x2 = $bounds[2]->getX();
        $y2 = $bounds[2]->getY();

        // 画像を切り抜く
        $croppedImage = Image::make($imagePath)->crop($x2 - $x1, $y2 - $y1, $x1, $y1);
        $croppedPath = 'business_cards/cropped_' . basename($path);
        $croppedImage->save(storage_path('app/public/' . $croppedPath));


        dd($text);

        return view('card.cardregist');
    }
    public function cardocrpost(Request $request)
    {
        // 画像ファイルを保存
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('business_cards', 'public');

            // Google Cloud Vision APIを初期化
            $imageAnnotator = new ImageAnnotatorClient();
            // 画像データを取得
            $imagePath = storage_path('app/public/' . $path);
            $imageData = file_get_contents($imagePath);

            try {
                // 画像データを取得
                $imagePath = storage_path('app/public/' . $path);
                $imageData = file_get_contents($imagePath);

                // Vision APIでテキストを抽出
                $response = $imageAnnotator->documentTextDetection($imageData);
                $annotation = $response->getFullTextAnnotation();

                if ($annotation) {
                    $rawText = $annotation->getText();

                    // OpenAI APIでJSON形式に整形
                    $prompt = "
                    以下のテキストは名刺から抽出されたデータです。このデータを以下のフォーマットに従って整理してください。

                    - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
                    - 郵便番号と電話番号は数字のみを抽出してください。
                    - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
                    - 「名前カナ」や「会社名カナ」には推測して必ずカタカナで入力してください。
                    - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
                      \"名前\": \"\",
                      \"名前カナ\": \"\",
                      \"会社名\": \"\",
                      \"会社名カナ\": \"\",
                      \"役職\": \"\",
                      \"部署1\": \"\",
                      \"部署2\": \"\",
                      \"メールアドレス\": \"\",
                      \"電話番号\": \"\",
                      \"住所\": \"\",
                      \"郵便番号\": \"\"
                    }

                    テキスト:
                    {$rawText}
                    ";


                    $aiResponse = OpenAI::chat()->create([
                        'model' => 'gpt-3.5-turbo',
                        'messages' => [
                            ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);

                    $structuredData = $aiResponse->choices[0]->message->content;

                    // 保存やレスポンスとして返す処理
                    return response()->json([
                        'status' => 'success',
                        'data' => json_decode($structuredData, true),
                    ]);
                } else {
                    return response()->json(['status' => 'error', 'message' => 'テキストが検出されませんでした。']);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            } finally {
                // 画像ファイルを削除
                Storage::disk('public')->delete($path);
                $imageAnnotator->close();
            }
        }

        return response()->json(['status' => 'error', 'message' => '画像がアップロードされていません。']);
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
}
