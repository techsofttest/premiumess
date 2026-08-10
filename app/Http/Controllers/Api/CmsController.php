<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cms;

class CmsController extends Controller
{
    public function show(string $slug)
    {
        $page = Cms::where('slug', $slug)->first();

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'image' => $page->image,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->description,
        ]);
    }
}
