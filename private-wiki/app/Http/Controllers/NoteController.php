<?php

namespace App\Http\Controllers;
use App\Models\Note;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::query();

        // タイトル検索
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // タグ検索
        if ($request->filled('tag')) {
            $tagGroups = array_map('trim', explode(',', html_entity_decode($request->tag)));

            $query->where(function ($mainQuery) use ($tagGroups) {
                foreach ($tagGroups as $tagGroup) {
                    if (str_contains($tagGroup, '::')) {
                        $andTags = explode('::', $tagGroup);
                        $mainQuery->orWhere(function ($subQuery) use ($andTags) {
                            foreach ($andTags as $andTag) {
                                $subQuery->whereHas('tags', function ($q) use ($andTag) {
                                    $q->where('name', $andTag);
                                });
                            }
                        });
                    } else {
                        // OR条件の単体タグ
                        $mainQuery->orWhereHas('tags', function ($q) use ($tagGroup) {
                            $q->where('name', $tagGroup);
                        });
                    }
                }
            });
        }

        $notes = $query->orderBy('updated_at', 'desc')->paginate(10)->appends($request->all());

        return view('home', compact('notes'));
    }

    public function show($id)
    {
        $note = Note::findOrFail($id);

        $converter = new CommonMarkConverter();
        $note->body = $converter->convertToHtml($note->body);

        return view('notes', compact('note'));
    }
    public function create()
    {
        return view('notes.create');
    }
}
