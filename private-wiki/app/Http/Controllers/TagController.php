<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // タグ候補API
    public function index(Request $request)
    {
        $query = Tag::query();

        // クエリパラメータ "query" または "search" があれば部分一致で絞り込み
        $searchTerm = $request->input('query') ?? $request->input('search');
        if ($searchTerm) {
            $query->where('name', 'like', '%'.$searchTerm.'%');
        }

        // 最大10件まで返す
        $tags = $query->limit(10)->get(['id', 'name']);

        return response()->json($tags); // 必ずJSONを返す
    }
}
