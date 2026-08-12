<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $category = $request->string('category')->toString();
        $search = $request->string('search')->toString();

        $query = Journal::query()
            ->where('is_published', true);

        if ($category !== '') {
            $query->where('category', $category);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('excerpt', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        $journals = $query->orderByDesc('published_at')->get();

        return response()->json($journals->map(fn (Journal $j) => [
            'id' => $j->id,
            'title' => $j->title,
            'slug' => $j->slug,
            'category' => $j->category,
            'author' => $j->author,
            'excerpt' => $j->excerpt,
            'image' => $j->image ? asset('storage/' . $j->image) : null,
            'published_at' => optional($j->published_at)->format('F j, Y'),
        ])->values());
    }

    public function show(string $slug): JsonResponse
    {
        $journal = Journal::where('slug', $slug)->where('is_published', true)->first();

        if (!$journal) {
            return response()->json(['error' => 'Article not found'], 404);
        }

        $related = Journal::where('is_published', true)
            ->where('id', '!=', $journal->id)
            ->take(3)
            ->get();

        return response()->json([
            'id' => $journal->id,
            'title' => $journal->title,
            'slug' => $journal->slug,
            'category' => $journal->category,
            'author' => $journal->author,
            'excerpt' => $journal->excerpt,
            'content' => $journal->content,
            'image' => $journal->image ? asset('storage/' . $journal->image) : null,
            'published_at' => optional($journal->published_at)->format('F j, Y'),
            'related' => $related->map(fn (Journal $j) => [
                'id' => $j->id,
                'title' => $j->title,
                'slug' => $j->slug,
                'category' => $j->category,
                'author' => $j->author,
                'excerpt' => $j->excerpt,
                'image' => $j->image ? asset('storage/' . $j->image) : null,
                'published_at' => optional($j->published_at)->format('F j, Y'),
            ])->values(),
        ]);
    }
}
