<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UploadedCard;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Config;
use App\Models\CardUser;
use App\Models\Card;
use App\Models\Company;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Card_Department;

class ProcessUploadedCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cardId;

    public function __construct($cardId)
    {
        $this->cardId = $cardId;
    }

    public function handle()
    {
        $server = config('prefix.server');
        $card = UploadedCard::find($this->cardId);
        if (!$card || $card->status !== 'pending') {
            return;
        }

        try {
            $imageUrl = $card->front_url;
            $aiResponse = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => '名刺データを整理するアシスタントです。'],
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                "type" => "text",
                                "text" => $this->getJsonPrompt() // プロンプトの内容
                            ],
                            [
                                "type" => "image_url",
                                "image_url" => [
                                    "url" => $imageUrl,
                                ]
                            ]
                        ]
                    ],
                ]
            ]);
            $jsonString = trim(preg_replace('/.*?(\{.*\}).*/s', '$1', $aiResponse->choices[0]->message->content));
            $structuredData = json_decode($jsonString, true);
            if ($structuredData['名前'] == '' || $structuredData['会社名'] == '') {
                $card->status = 'done';
                $card->save();
                return;
            }
            if ($server == 'onpre') {
                $wasabiUrl = $card->front_url;
                // クエリ部分（?以降）を除外
                $parsedUrl = parse_url($wasabiUrl);
                $path = $parsedUrl['path'] ?? '';

                // pathinfoで拡張子を取得
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $filename = $this->generateRandomCode() . "." . $extension;

                $filepath = Config::get('custom.file_upload_path'); // 保存先パスを取得
                if (!is_dir($filepath)) {
                    mkdir($filepath, 0755, true); // フォルダがなければ作る
                }
                // 画像をダウンロード
                $imageData = file_get_contents($wasabiUrl);
                if ($imageData === false) {
                    throw new \Exception('ファイルのダウンロードに失敗しました。');
                }

                // フルパス組み立て
                $fullPath = rtrim($filepath, '/') . '/' . $filename;

                // ファイルを保存
                file_put_contents($fullPath, $imageData);
            }
            $carduser = CardUser::where('表示名', $structuredData['名前'])->first();
            if (!$carduser) {
                $carduser = new CardUser();
                $carduser->表示名 = $structuredData['名前'] ?? '';
                $carduser->表示名カナ = $structuredData['名前カナ'] ?? '';
                $carduser->save();

                $exist_cards = Card::where('名刺ユーザーID', $carduser->id)->get();
                foreach ($exist_cards as $exist_card) {
                    $exist_card->最新フラグ = 0;
                    $exist_card->save();
                }
            }
            $company = Company::where('会社名', $structuredData['会社名'])->first();
            if (!$company) {
                $company = new Company();
                $company->会社名 = $structuredData['会社名'] ?? '';
                $company->会社名カナ = $structuredData['会社名カナ'] ?? '';
                $company->save();


                $branch = new Branch();
                $branch->会社ID = $company->id;
                if ($structuredData['拠点名']) {
                    $branch->拠点名 = $structuredData['拠点名'];
                    $branch->拠点指定 = true;
                } else {
                    $branch->拠点名 = $structuredData['会社名'] ?? '';
                    $branch->拠点指定 = false;
                }
                $branch->save();
            } else {
                // 拠点名があるかどうかで分岐
                if ($structuredData['拠点名']) {
                    // すでに拠点が登録されているかを確認
                    $branch = Branch::where('会社ID', $company->id)
                        ->where('拠点名', $structuredData['拠点名'])
                        ->first();

                    // ない場合は新規登録
                    if (!$branch) {
                        $branch = new Branch();
                        $branch->会社ID = $company->id;
                        $branch->拠点名 = $structuredData['拠点名'];
                        $branch->拠点指定 = true;
                        $branch->拠点所在地 = $structuredData['住所'] ?? '';
                        $branch->電話番号 = $structuredData['電話番号'] ?? '';
                        $branch->FAX番号 = $structuredData['FAX番号'] ?? '';
                        $branch->save();
                    }
                } else {
                    // 拠点名がない場合は、拠点指定がfalseのものを取得
                    $branch = Branch::where('会社ID', $company->id)
                        ->where('拠点指定', false)
                        ->first();
                }
            }
            $newcard = new Card();
            $newcard->名刺ユーザーID = $carduser->id;
            $newcard->会社ID = $company->id;
            $newcard->拠点ID = $branch->id;
            $newcard->名前 = $structuredData['名前'] ?? '';
            $newcard->名前カナ = $structuredData['名前カナ'] ?? '';
            $newcard->役職 = $structuredData['役職'] ?? '';
            $newcard->名刺ファイル表 = $filename;
            $newcard->携帯電話番号 = $structuredData['携帯電話番号'] ?? '';
            $newcard->メールアドレス = $structuredData['メールアドレス'] ?? '';
            $newcard->save();

            // 部署登録
            $department_number = 1;
            $upper_department_id = null;
            while ($structuredData['部署' . $department_number]) {
                // 部署名が入力されている場合
                if ($structuredData['部署' . $department_number] != '') {
                    // 部署名を取得
                    $department_name = $structuredData['部署' . $department_number];

                    $existing_department = Department::where('部署名', $department_name)
                        ->where('会社ID', $company->id)
                        ->first();
                    if ($existing_department) {
                        $department = $existing_department;
                    } else {
                        $department = new Department();
                        $department->会社ID = $company->id;
                        $department->部署名 = $department_name;

                        // 上位部署IDを設定（最初の部署以外）
                        if ($department_number != 1) {
                            $department->上位部署ID = $upper_department_id;
                        }
                    }

                    // 部署データを保存
                    $department->save();
                    // 上位部署IDを取得
                    $upper_department_id = $department->id;


                    $card_department = new Card_Department();
                    $card_department->名刺ID = $card->id;
                    $card_department->部署ID = $department->id;
                    $card_department->save();
                }
                // 次の部署番号に進む
                $department_number++;
            }




            $card->status = 'done';
            $card->save();
        } catch (\Exception $e) {
            $card->status = 'failed';
            $card->save();
            return; // 次のカードへ
        }
    }
    private function getJsonPrompt($rawText = null)
    {
        return "
        以下の画像（またはテキスト）をOCR解析し、名刺情報を出力フォーマットに従ってJSON形式で返してください。

        - 住所に郵便番号が含まれている場合、その郵便番号を取り除いて、専用の「郵便番号」フィールドに入れてください。
        - 郵便番号、電話番号、FAX番号、携帯電話番号は数字のみを抽出してください。
        - 部署が複数ある場合は、各部署を「部署1」「部署2」といった形式で記入し、3つ以上の部署がある場合は「部署3」「部署4」のように追加してください。
        - 「名前カナ」や「会社名カナ」には推測して必ずカタカナで入力してください。
        - 「名前」や「名前カナ」は苗字と名前のスペースを区切らないで入力してください。
        - 「名前カナ」には名前の漢字、読み仮名やメールアドレスのスペルなどから推測してください。
        - 本社、支社、本店、支店、工場など住所が紐づく拠点などがある場合は「拠点名」に入力してください。

        出力フォーマット:
        {
            \"名前\": \"\",
            \"名前カナ\": \"\",
            \"会社名\": \"\",
            \"会社名カナ\": \"\",
            \"役職\": \"\",
            \"部署1\": \"\",
            \"部署2\": \"\",
            \"メールアドレス\": \"\",
            \"携帯電話番号\": \"\",
            \"電話番号\": \"\",
            \"FAX番号\": \"\",
            \"住所\": \"\",
            \"郵便番号\": \"\",
            \"拠点名\": \"\"
        }

        " . ($rawText ? "名刺テキスト:\n{$rawText}" : "画像を解析してください。");
    }
    //ランダムな8桁のstring型の数値を出力
    private function generateRandomCode()
    {
        $code = mt_rand(10000000, 99999999);
        return $code;
    }
}
