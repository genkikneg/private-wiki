<?php

namespace App\Http\Controllers;

use App\Models\FavoriteTag;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteTagController extends Controller
{
    public function index(): JsonResponse
    {
        $favoriteTags = FavoriteTag::with('tag')->ordered()->get();

        return response()->json([
            'status' => 'success',
            'data' => $favoriteTags
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'tag_id' => [
                'required',
                'integer',
                Rule::exists('tags', 'id'),
                Rule::unique('favorite_tags', 'tag_id')
            ]
        ]);

        try {
            $favoriteTag = FavoriteTag::create([
                'tag_id' => $request->tag_id,
                'display_order' => FavoriteTag::getNextDisplayOrder()
            ]);

            $favoriteTag->load('tag');

            return response()->json([
                'status' => 'success',
                'data' => $favoriteTag
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'このタグは既にお気に入りに登録されています'
            ], 422);
        }
    }

    public function destroy($id): JsonResponse
    {
        $favoriteTag = FavoriteTag::find($id);
        
        if (!$favoriteTag) {
            return response()->json([
                'status' => 'error',
                'message' => 'お気に入りタグが見つかりません'
            ], 404);
        }
        
        $favoriteTag->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'お気に入りタグを削除しました'
        ]);
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'favorite_tag_ids' => 'required|array',
            'favorite_tag_ids.*' => 'integer|exists:favorite_tags,id'
        ]);

        FavoriteTag::reorderTags($request->favorite_tag_ids);

        return response()->json([
            'status' => 'success',
            'message' => 'お気に入りタグの順序を変更しました'
        ]);
    }

    public function manage()
    {
        $favoriteTags = FavoriteTag::with('tag')->ordered()->get();
        
        $favoriteTagIds = $favoriteTags->pluck('tag_id');
        $availableTags = Tag::whereNotIn('id', $favoriteTagIds)->orderBy('name')->get();

        return view('favorite-tags.manage', compact('favoriteTags', 'availableTags'));
    }

    public function add(Request $request)
    {
        // 現在のお気に入りタグ数をチェック
        $currentCount = FavoriteTag::count();
        if ($currentCount >= 5) {
            return redirect('/favorite-tags/manage')
                ->with('error', 'お気に入りタグは最大5個までしか登録できません');
        }

        $request->validate([
            'tag_name' => [
                'required',
                'string',
                'max:255'
            ]
        ]);

        // タグ名からタグを検索または作成
        $tag = Tag::firstOrCreate(['name' => $request->tag_name]);
        
        // 既にお気に入りに追加されているかチェック
        if (FavoriteTag::where('tag_id', $tag->id)->exists()) {
            return redirect('/favorite-tags/manage')
                ->with('error', 'このタグは既にお気に入りに登録されています');
        }

        FavoriteTag::create([
            'tag_id' => $tag->id,
            'display_order' => FavoriteTag::getNextDisplayOrder()
        ]);

        return redirect('/favorite-tags/manage')
            ->with('success', 'お気に入りタグに追加しました');
    }

    public function remove(FavoriteTag $favoriteTag)
    {
        $favoriteTag->delete();

        return redirect('/favorite-tags/manage')
            ->with('success', 'お気に入りタグから削除しました');
    }

}