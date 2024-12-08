<?php

namespace App\Http\Controllers\Main;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function list(Request $request)
    {
        $search = $request->get('search');
        $articlesQuery = Article::query();

        if ($search) {
            $articlesQuery->where('name', 'ILIKE', "%{$search}%")
                          ->orWhereHas('hashtags', function ($query) use ($search) {
                              $query->where('hashtag', 'ILIKE', "%{$search}%");
                          })
                          ->orWhereRaw("
                          authors @> ?
                      ", [json_encode([$search])]);
        }

        $articles = $articlesQuery->paginate(20);

        return view('main.article', compact('articles'));
    }

    public function index(int $article_id)
    {
        $article = Article::findOrFail($article_id);

        return view('main.forms.article_page', compact('article'));
    }
}
