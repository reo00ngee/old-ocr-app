<?php

namespace App\Http\Controllers;


use App\Models\User;
use Google\Cloud\Vision\V1\Likelihood;
use Illuminate\Http\Request;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Illuminate\Support\Facades\Storage;
use App\Models\Ocr;
use Google\Cloud\Vision\V1\Feature\Type;
use OpenAI\Laravel\Facades\OpenAI;
use App\Http\Requests\ImageRequest;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    // home。画像をアップロードするページを表示する
    public function index()
    {
        $user = \Auth::user();
        return view('home', compact('user'));
    }

    // 画像データを一覧表示
    public function ocrResult()
    {

        $user = \Auth::user();
        $ocrs = Ocr::where('user_id', $user['id'])->where('status', '1')->orderBy('id', 'DESC')->get();
        // dd($ocrs);
        return view('ocrResult', compact('user', 'ocrs'));
    }
// homeから画像をアップロードする
    public function store(ImageRequest $request)
    {

        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' =>'/var/www/html/ocr/config/google-credentials.json',
        ]);

        $data = $request->all();
        // 画像をストレージに格納
        $image = $request->file('image');
        $path = \Storage::put('/public', $image);
        // 画像のpathを作る
        $path = explode('/', $path);
        if (!in_array($image->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
            return redirect()->route('home')->with('error', '画像はJPG、JPEG、PNG形式でアップロードしてください。');
        }

        $image = file_get_contents(Storage::disk('public')->path($path[1]));
        // Vision APIを使用して、テキストを抽出する
        $response = $imageAnnotator->textDetection($image);
        $texts = $response->getTextAnnotations();
        $bounds = [];
        foreach ($texts as $text) {
    
                $bounds[] = $text->getDescription();

        }
        $content = join(', ', $bounds) . PHP_EOL;
        $result = OpenAI::completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => "翻訳をせずに下記文章の誤字・脱字・スペルミスを修正してください。"."\n".$content,
            'temperature' => 0.2,
            'max_tokens' => 2000,
        ]);
        $fixed_content = $result['choices'][0]['text'];

        $result = OpenAI::completions()->create([
            'model' => 'text-davinci-003',
            'prompt' => "meke the following sentences shorter."."\n".$fixed_content,
            'temperature' => 0.2,
            'max_tokens' => 2000,
        ]);
        $point_of_content = $result['choices'][0]['text'];

        // ocrsテーブルに格納
        $ocr_id = Ocr::insertGetId([
            'image' => $path[1],
            'status' => '1',
            'user_id' => $data['user_id'],
            'content' => $content,
            'fixed_content' => $fixed_content,
            'point_of_content' => $point_of_content
        ]);
        $imageAnnotator->close();

        // リダイレクト処理
        return redirect()->route('ocrResult');
    }

    // 選択した画像の情報を閲覧できる
    public function edit($id)
    {
        // 該当するIDのデータをデータベースから取得
        $user = \Auth::user();
        $ocr = Ocr::where('status', 1)->where('id', $id)->where('user_id', $user['id'])
            ->first();
        $ocrs = Ocr::where('user_id', $user['id'])->where('status', '1')->orderBy('id', 'DESC')->get();
        //取得したOCRをViewに渡す
        return view('edit', compact('user', 'ocr', 'ocrs'));
    }

// OCRした情報を編集する
    public function update(Request $request, $id)
    {
        $inputs = $request->all();
        $ocr = Ocr::where('id', $id)->update(['fixed_content' => $inputs['fixed_content'], 'point_of_content' => $inputs['point_of_content']]);

        return redirect()->route('ocrResult');
    }

    // OCRされた情報を削除する
    public function delete(Request $request, $id)
    {
        $inputs = $request->all();
        // statusを２にして削除したことにする
        Ocr::where('id', $id)->update(['status' => 2]);

        return redirect()->route('ocrResult')->with('success', 'OCRの削除が完了しました');
    }

    // 検索機能
    public function search(Request $request)
    {

        $user = \Auth::user();
        $ocrs = Ocr::where('user_id', $user['id'])->where('status', '1')->orderBy('id', 'DESC')->get();
        $search = $request->input('search');
        $query = Ocr::query();


        if ($search) {
            $spaceConversion = mb_convert_kana($search, 's');

            $wordArraySearched = preg_split('/[\s,]+/', $spaceConversion, -1, PREG_SPLIT_NO_EMPTY);

            foreach ($wordArraySearched as $value) {
                $query->where('fixed_content', 'LIKE', '%' . $value . '%');
            }

            $ocrs = $query->get();
        }
        return view('search', compact('user', 'ocrs', 'search'));
    }
}