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
            $aliasMap = [
                'terms' => 'terms-and-conditions',
                'terms-of-use' => 'terms-and-conditions',
                'privacy' => 'privacy-policy',
                'security-privacy' => 'privacy-policy',
                'refund-policy' => 'refund-and-return',
                'returns' => 'refund-and-return',
                'cancellation-returns' => 'refund-and-return',
                'shipping' => 'shipping-policy',
                'shipping-dispatch' => 'shipping-policy',
                'about' => 'about-us',
            ];

            if (isset($aliasMap[$slug])) {
                $page = Cms::where('slug', $aliasMap[$slug])->first();
            }
        }

        if (!$page) {
            return response()->json(['error' => 'Page not found'], 404);
        }

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'image' => $page->image ? asset('storage/' . $page->image) : null,
            'meta_title' => $page->meta_title ?: $page->title,
            'meta_description' => $page->description,
        ]);
    }
}
