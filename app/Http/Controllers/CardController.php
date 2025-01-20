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
}
