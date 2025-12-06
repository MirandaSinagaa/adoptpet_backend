<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{
    // GET ALL (Untuk List)
    public function index()
    {
        $articles = Article::latest()->get();
        return response()->json(['status' => 'success', 'data' => $articles]);
    }

    // CRAWL & SAVE (Ambil data dari URL)
    public function store(Request $request)
    {
        $request->validate(['url' => 'required|url']);
        $url = $request->url;

        try {
            // 1. Ambil HTML dari website target
            $response = Http::timeout(10)->get($url);
            $html = $response->body();

            // 2. Baca Meta Tags (Title, Image, Desc)
            $doc = new \DOMDocument();
            @$doc->loadHTML($html);
            
            $tags = $doc->getElementsByTagName('meta');
            $title = null; $image = null; $desc = null;

            foreach ($tags as $tag) {
                $prop = $tag->getAttribute('property');
                $name = $tag->getAttribute('name');
                $content = $tag->getAttribute('content');

                if ($prop == 'og:title' || $name == 'title') $title = $content;
                if ($prop == 'og:image' || $name == 'image') $image = $content;
                if ($prop == 'og:description' || $name == 'description') $desc = $content;
            }

            if (!$title) {
                $t = $doc->getElementsByTagName('title');
                if ($t->length > 0) $title = $t->item(0)->nodeValue;
            }

            // 3. Simpan ke DB
            $article = Article::create([
                'title' => $title ?? 'No Title',
                'image_url' => $image ?? 'https://placehold.co/600x400?text=No+Image',
                'description' => $desc ?? 'Klik untuk baca selengkapnya.',
                'original_url' => $url,
                'source_host' => parse_url($url, PHP_URL_HOST)
            ]);

            return response()->json(['status' => 'success', 'message' => 'Artikel disimpan!', 'data' => $article]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Gagal mengambil data URL.'], 500);
        }
    }

    // DELETE
    public function destroy($id)
    {
        Article::destroy($id);
        return response()->json(['status' => 'success', 'message' => 'Artikel dihapus']);
    }
}