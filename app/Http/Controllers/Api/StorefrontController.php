<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Brand;
use App\Models\Advertisement;
use App\Models\Banner;
use App\Models\Category;
use App\Models\FragranceConcentration;
use App\Models\FragranceFamily;
use App\Models\HomePageSection;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class StorefrontController extends Controller
{
    private function assetUrl(?string $path): ?string
    {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $cleanPath = ltrim(preg_replace('/^storage\//', '', $path), '/');
        return asset('storage/' . $cleanPath);
    }

    private function variantPayload($variant): array
    {
        $price = (float) ($variant->selling_price ?? 0);
        $buyingPrice = (float) ($variant->buying_price ?? 0);
        $originalPrice = ($buyingPrice > $price) ? $buyingPrice : null;

        return [
            'id' => $variant->id,
            'sku' => $variant->sku,
            'unit' => $variant->unit,
            'size' => $variant->size,
            'label' => trim(($variant->size ?? '') . ($variant->unit ? ' ' . $variant->unit : '')),
            'price' => $price,
            'buying_price' => $buyingPrice,
            'original_price' => $originalPrice,
            'stock' => (int) ($variant->stock ?? 0),
        ];
    }

    private function productPayload(Product $product): array
    {
        $inStockVariants = $product->variants->filter(fn ($variant) => (int) $variant->stock > 0);
        $variant = $inStockVariants->first() ?: $product->variants->first();
        $price = $variant ? (float) $variant->selling_price : 0.0;
        $buyingPrice = $variant ? (float) ($variant->buying_price ?? 0) : 0.0;
        $originalPrice = ($buyingPrice > $price) ? $buyingPrice : null;
        $approvedReviews = $product->reviews->filter(fn ($r) => (bool) $r->review_status);
        $rating = round((float) ($approvedReviews->avg('review_rating') ?: 0), 1);

        $explodeNotes = function (?string $notes): array {
            if (!$notes) return [];
            return array_values(array_filter(array_map('trim', explode(',', $notes))));
        };

        $families = $product->fragranceFamilies;
        if ($families->isEmpty() && $product->fragranceFamily) {
            $families = collect([$product->fragranceFamily]);
        }
        $familiesPayload = $families->map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'slug' => $f->slug,
        ])->values()->all();

        $gallery = collect([$product->featured_image])
            ->merge($product->images ? $product->images->pluck('image_path') : [])
            ->filter()
            ->unique()
            ->map(fn ($imagePath) => $this->assetUrl($imagePath))
            ->values()
            ->all();

        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $product->name,
            'sku' => $product->sku,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
                'slug' => $product->brand->slug,
                'classification' => $product->brand->classification,
            ] : null,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
                'slug' => $product->category->slug,
            ] : null,
            'featured_image' => $this->assetUrl($product->featured_image),
            'gallery' => $gallery,
            'gender' => $product->gender,
            'fragrance_family' => $product->fragranceFamily ? [
                'id' => $product->fragranceFamily->id,
                'name' => $product->fragranceFamily->name,
                'slug' => $product->fragranceFamily->slug,
            ] : null,
            'fragrance_families' => $familiesPayload,
            'fragrance_concentration' => $product->fragranceConcentration ? [
                'id' => $product->fragranceConcentration->id,
                'name' => $product->fragranceConcentration->name,
                'slug' => $product->fragranceConcentration->slug,
            ] : null,
            'price' => $price,
            'buying_price' => $buyingPrice,
            'original_price' => $originalPrice,
            'min_price' => (float) ($product->min_price ?? $price),
            'max_price' => $originalPrice ?? (float) ($product->max_price ?? $price),
            'is_featured' => (bool) $product->is_featured,
            'is_active' => (bool) $product->is_active,
            'rating' => $rating,
            'review_count' => $approvedReviews->count(),
            'variants' => $product->variants->map(fn ($variant) => $this->variantPayload($variant))->values(),
            'description' => $product->description,
            'key_features' => $product->key_features,
            'top_notes' => $product->top_notes,
            'middle_notes' => $product->middle_notes,
            'base_notes' => $product->base_notes,
            'top_notes_list' => $explodeNotes($product->top_notes),
            'middle_notes_list' => $explodeNotes($product->middle_notes),
            'base_notes_list' => $explodeNotes($product->base_notes),
            'reviews' => $approvedReviews->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->review_name,
                'rating' => (int) $r->review_rating,
                'title' => $r->review_title,
                'content' => $r->review_content,
                'date' => $r->created_at ? $r->created_at->format('M d, Y') : '',
            ])->values()->all(),
            'meta_title' => $product->meta_title,
            'meta_description' => $product->meta_description,
        ];
    }

    private function getCategoryImageUrl(Category $category): ?string
    {
        if ($category->image) {
            return $this->assetUrl($category->image);
        }

        // Try to get a product directly in this category
        $product = Product::query()
            ->where('category_id', $category->id)
            ->whereNotNull('featured_image')
            ->where('is_active', true)
            ->first();

        if ($product) {
            return $this->assetUrl($product->featured_image);
        }

        // Try to get a product from any child categories (subcategories)
        $childIds = Category::query()
            ->where('parent_id', $category->id)
            ->pluck('id');

        if ($childIds->isNotEmpty()) {
            $product = Product::query()
                ->whereIn('category_id', $childIds)
                ->whereNotNull('featured_image')
                ->where('is_active', true)
                ->first();

            if ($product) {
                return $this->assetUrl($product->featured_image);
            }
        }

        return null;
    }

    private function categoryPayload(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'image_url' => $this->getCategoryImageUrl($category),
            'icon_url' => $this->assetUrl($category->icon),
            'href' => '/category/' . $category->slug,
        ];
    }

    private function getCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->children as $child) {
            $ids = array_merge($ids, $this->getCategoryIds($child));
        }

        return array_values(array_unique($ids));
    }

    private function brandPayload(Brand $brand): array
    {
        $firstProduct = $brand->products()->whereNotNull('featured_image')->first();
        $logoUrl = $this->assetUrl($brand->logo);

        return [
            'id' => $brand->id,
            'name' => $brand->name,
            'slug' => $brand->slug,
            'classification' => $brand->classification,
            'logo' => $logoUrl,
            'logo_url' => $logoUrl,
            'product_image_url' => $firstProduct ? $this->assetUrl($firstProduct->featured_image) : null,
            'link' => '/brand/' . $brand->slug,
            'product_count' => $brand->products_count ?? $brand->products()->count(),
        ];
    }

    private function topOfferPayloads(int $limit = 20)
    {
        return Product::query()
            ->with(['brand', 'category', 'variants', 'images', 'reviews'])
            ->where('is_active', true)
            ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
            ->get()
            ->filter(function (Product $product) {
                $minPrice = (float) ($product->min_price ?? 0);
                $maxPrice = (float) ($product->max_price ?? 0);

                return $minPrice > 0 && $maxPrice > $minPrice;
            })
            ->map(function (Product $product) {
                $minPrice = (float) ($product->min_price ?? 0);
                $maxPrice = (float) ($product->max_price ?? 0);
                $discountPercent = (int) round((1 - ($minPrice / $maxPrice)) * 100);

                return [
                    'id' => $product->id,
                    'title' => $product->name,
                    'label' => $discountPercent . '% OFF',
                    'discount_percent' => $discountPercent,
                    'image_url' => $this->assetUrl($product->featured_image),
                    'href' => '/product/' . $product->slug,
                ];
            })
            ->sortByDesc('discount_percent')
            ->take($limit)
            ->values();
    }

    private function homeSectionPayload(HomePageSection $section): array
    {
        $limit = max(1, min((int) $section->item_limit, 48));
        $items = collect();

        if ($section->source === HomePageSection::SOURCE_CUSTOM_PRODUCTS && filled($section->product_ids)) {
            $products = Product::query()
                ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                ->whereIn('id', $section->product_ids)
                ->where('is_active', true)
                ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                ->get()
                ->sortBy(fn (Product $product) => array_search($product->id, $section->product_ids, true));

            $items = $products->map(fn (Product $product) => $this->productPayload($product))->values();
        } elseif ($section->source === HomePageSection::SOURCE_FEATURED_PRODUCTS) {
            $items = Product::query()
                ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                ->where('is_active', true)
                ->where('is_featured', true)
                ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                ->latest()
                ->take($limit)
                ->get()
                ->map(fn (Product $product) => $this->productPayload($product))
                ->values();
        } elseif ($section->source === HomePageSection::SOURCE_LATEST_PRODUCTS) {
            $items = Product::query()
                ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                ->where('is_active', true)
                ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                ->latest()
                ->take($limit)
                ->get()
                ->map(fn (Product $product) => $this->productPayload($product))
                ->values();
        } elseif ($section->source === HomePageSection::SOURCE_CUSTOM_CATEGORIES && filled($section->category_ids)) {
            $categories = Category::query()
                ->whereIn('id', $section->category_ids)
                ->where('is_active', true)
                ->get()
                ->sortBy(fn (Category $category) => array_search($category->id, $section->category_ids, true));

            $items = $categories->map(fn (Category $category) => $this->categoryPayload($category))->values();
        } elseif ($section->source === HomePageSection::SOURCE_CATEGORIES) {
            $items = Category::query()
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->take($limit)
                ->get()
                ->map(fn (Category $category) => $this->categoryPayload($category))
                ->values();
        } elseif ($section->source === HomePageSection::SOURCE_CUSTOM_BRANDS && filled($section->brand_ids)) {
            $brands = Brand::query()
                ->with(['products' => fn ($q) => $q->where('is_active', true)->take(1)])
                ->whereIn('id', $section->brand_ids)
                ->get()
                ->sortBy(fn (Brand $brand) => array_search($brand->id, $section->brand_ids, true));

            $items = $brands->map(fn (Brand $brand) => $this->brandPayload($brand))->values();
        } elseif ($section->source === HomePageSection::SOURCE_BRANDS) {
            $items = Brand::query()
                ->with(['products' => fn ($q) => $q->where('is_active', true)->take(1)])
                ->take($limit)
                ->get()
                ->map(fn (Brand $brand) => $this->brandPayload($brand))
                ->values();
        } elseif ($section->source === HomePageSection::SOURCE_BANNERS) {
            $items = Banner::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->take($limit)
                ->get()
                ->map(fn (Banner $banner) => [
                    'id' => $banner->id,
                    'name' => $banner->name,
                    'image_url' => $this->assetUrl($banner->image),
                    'url' => $banner->url,
                ])->values();
        } elseif ($section->source === HomePageSection::SOURCE_ADVERTISEMENT) {
            $advertisement = Advertisement::query()->first();

            $items = $advertisement ? collect([[
                'id' => $advertisement->id,
                'name' => $advertisement->name,
                'title' => $advertisement->title,
                'banner_url' => $this->assetUrl($advertisement->banner),
                'url' => $advertisement->url,
            ]]) : collect();
        } elseif ($section->source === HomePageSection::SOURCE_TOP_OFFERS) {
            $items = $this->topOfferPayloads($limit);
        }

        return [
            'id' => $section->id,
            'name' => $section->name,
            'type' => $section->type,
            'source' => $section->source,
            'title' => $section->title,
            'subtitle' => $section->subtitle,
            'link_label' => $section->link_label,
            'link_url' => $section->link_url,
            'background_color' => $section->background_color,
            'settings' => $section->settings,
            'items' => $items,
        ];
    }

    public function categories(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->withCount('products')
            ->with(['children' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();

        return response()->json($categories->map(function (Category $category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'image_url' => $this->getCategoryImageUrl($category),
                'icon_url' => $this->assetUrl($category->icon),
                'product_count' => $category->products_count,
                'children' => $category->children->map(fn (Category $child) => [
                    'id' => $child->id,
                    'name' => $child->name,
                    'slug' => $child->slug,
                    'image_url' => $this->getCategoryImageUrl($child),
                    'icon_url' => $this->assetUrl($child->icon),
                ])->values(),
            ];
        }));
    }

    public function header(): JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        $brands = Brand::query()->orderBy('name')->get();

        $brandsByClassification = [
            'Designer Houses' => $brands->where('classification', 'Designer Houses')->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'classification' => $b->classification,
                'logo_url' => $this->assetUrl($b->logo),
                'href' => '/brand/' . $b->slug,
            ])->values(),
            'Prestige & Niche' => $brands->where('classification', 'Prestige & Niche')->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'classification' => $b->classification,
                'logo_url' => $this->assetUrl($b->logo),
                'href' => '/brand/' . $b->slug,
            ])->values(),
            'Classic Elegance' => $brands->where('classification', 'Classic Elegance')->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'classification' => $b->classification,
                'logo_url' => $this->assetUrl($b->logo),
                'href' => '/brand/' . $b->slug,
            ])->values(),
        ];

        $fragranceFamilies = FragranceFamily::query()->orderBy('name')->get()->map(fn ($f) => [
            'id' => $f->id,
            'name' => $f->name,
            'slug' => $f->slug,
            'href' => '/fragrances?family=' . $f->slug,
        ])->values();

        $fragranceConcentrations = FragranceConcentration::query()->orderBy('name')->get()->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'slug' => $c->slug,
            'href' => '/fragrances?concentration=' . $c->slug,
        ])->values();

        return response()->json([
            'brand' => [
                'name' => config('app.name'),
                'logo' => asset('images/logo/brand-logo-nobg.png?v1'),
            ],
            'links' => [
                ['label' => 'Home', 'href' => '/'],
                ['label' => 'Products', 'href' => '/shop'],
                ['label' => 'Categories', 'href' => '/fragrances'],
            ],
            'categories' => $categories->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'href' => '/category/' . $category->slug,
                'image_url' => $this->getCategoryImageUrl($category),
                'icon_url' => $this->assetUrl($category->icon),
            ])->values(),
            'brands_by_classification' => $brandsByClassification,
            'all_brands' => $brands->map(fn ($b) => $this->brandPayload($b))->values(),
            'fragrance_menu' => [
                'for_whom' => [
                    ['name' => 'Men', 'slug' => 'men', 'href' => '/fragrances?gender=Men'],
                    ['name' => 'Women', 'slug' => 'women', 'href' => '/fragrances?gender=Women'],
                    ['name' => 'Unisex', 'slug' => 'unisex', 'href' => '/fragrances?gender=Unisex'],
                ],
                'olfactive_families' => $fragranceFamilies,
                'concentrations' => $fragranceConcentrations,
            ],
        ]);
    }

    public function brands(): JsonResponse
    {
        $brands = Brand::query()->withCount('products')->orderBy('name')->get();

        $all = $brands->map(fn ($b) => $this->brandPayload($b))->values();

        $byClassification = [
            'Designer Houses' => $brands->where('classification', 'Designer Houses')->map(fn ($b) => $this->brandPayload($b))->values(),
            'Prestige & Niche' => $brands->where('classification', 'Prestige & Niche')->map(fn ($b) => $this->brandPayload($b))->values(),
            'Classic Elegance' => $brands->where('classification', 'Classic Elegance')->map(fn ($b) => $this->brandPayload($b))->values(),
        ];

        return response()->json([
            'all' => $all,
            'data' => $all,
            'grouped' => $byClassification,
            'by_classification' => $byClassification,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());
        $perPage = max(1, min((int) $request->integer('per_page', 24), 100));

        $products = collect();
        if ($search !== '') {
            $products = Product::query()
                ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                ->where('is_active', true)
                ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                ->where(function ($query) use ($search) {
                    $keywords = array_filter(explode(' ', $search));
                    foreach ($keywords as $keyword) {
                        $query->where(function ($q) use ($keyword) {
                            $q->where('name', 'like', '%' . $keyword . '%')
                                ->orWhere('sku', 'like', '%' . $keyword . '%')
                                ->orWhereHas('variants', fn ($v) => $v->where('sku', 'like', '%' . $keyword . '%'))
                                ->orWhereHas('brand', fn ($b) => $b->where('name', 'like', '%' . $keyword . '%'))
                                ->orWhereHas('category', fn ($c) => $c->where('name', 'like', '%' . $keyword . '%'));
                        });
                    }
                })
                ->take($perPage)
                ->get()
                ->map(fn (Product $product) => $this->productPayload($product))
                ->values();
        }

        $categories = Category::query()
            ->where('is_active', true)
            ->where(function ($query) use ($search) {
                $keywords = array_filter(explode(' ', $search));
                foreach ($keywords as $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('slug', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderBy('sort_order')
            ->take(12)
            ->get()
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'href' => '/category/' . $category->slug,
            ])
            ->values();

        $brands = Brand::query()
            ->where(function ($query) use ($search) {
                $keywords = array_filter(explode(' ', $search));
                foreach ($keywords as $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('name', 'like', '%' . $keyword . '%')
                            ->orWhere('slug', 'like', '%' . $keyword . '%');
                    });
                }
            })
            ->orderBy('name')
            ->take(12)
            ->get()
            ->map(fn (Brand $brand) => [
                'id' => $brand->id,
                'name' => $brand->name,
                'slug' => $brand->slug,
                'href' => '/brand/' . $brand->slug,
            ])
            ->values();

        return response()->json([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 48);
        $perPage = max(1, min($perPage, 250));

        $baseQuery = Product::query()
            ->with(['brand', 'category', 'variants', 'reviews', 'images'])
            ->where('is_active', true);

        $query = clone $baseQuery;

        if ($categoryParam = $request->string('category')->toString()) {
            $categorySlugs = array_values(array_filter(array_map('trim', explode(',', $categoryParam))));
            $categories = Category::query()->whereIn('slug', $categorySlugs)->get();
            $allCategoryIds = [];
            foreach ($categories as $cat) {
                $allCategoryIds = array_merge($allCategoryIds, $this->getCategoryIds($cat));
            }
            if (!empty($allCategoryIds)) {
                $query->whereIn('category_id', array_unique($allCategoryIds));
            }
        }

        if ($brandParam = $request->string('brand')->toString()) {
            $brandList = array_values(array_filter(array_map('trim', explode(',', $brandParam))));
            $query->whereHas('brand', function ($q) use ($brandList) {
                $q->whereIn('slug', $brandList)
                  ->orWhereIn('id', array_filter($brandList, 'is_numeric'))
                  ->orWhere(function ($sub) use ($brandList) {
                      foreach ($brandList as $b) {
                          $sub->orWhere('name', 'like', '%' . $b . '%');
                      }
                  });
            });
        }

        if ($classificationFilter = $request->string('classification')->toString()) {
            $query->whereHas('brand', fn ($q) => $q->where('classification', $classificationFilter));
        }

        if ($collectionFilter = $request->string('collection')->toString()) {
            $query->whereHas('collections', fn ($q) => $q->where('slug', $collectionFilter)->orWhere('name', 'like', '%' . $collectionFilter . '%'));
        }

        if ($familyFilter = ($request->string('family')->toString() ?: $request->string('family_id')->toString())) {
            $familyList = array_values(array_filter(array_map('trim', explode(',', $familyFilter))));
            $query->where(function ($q) use ($familyList) {
                $q->whereHas('fragranceFamilies', function ($fq) use ($familyList) {
                    $fq->whereIn('slug', $familyList)
                       ->orWhereIn('id', array_filter($familyList, 'is_numeric'))
                       ->orWhere(function ($sub) use ($familyList) {
                           foreach ($familyList as $fam) {
                               $sub->orWhere('name', 'like', '%' . $fam . '%');
                           }
                       });
                })
                ->orWhereHas('fragranceFamily', function ($fq) use ($familyList) {
                    $fq->whereIn('slug', $familyList)
                       ->orWhereIn('id', array_filter($familyList, 'is_numeric'))
                       ->orWhere(function ($sub) use ($familyList) {
                           foreach ($familyList as $fam) {
                               $sub->orWhere('name', 'like', '%' . $fam . '%');
                           }
                       });
                });
            });
        }

        if ($genderParam = $request->string('gender')->toString()) {
            $genderList = array_values(array_filter(array_map('trim', explode(',', $genderParam))));
            $query->where(function ($q) use ($genderList) {
                foreach ($genderList as $idx => $g) {
                    $gLower = strtolower($g);
                    if ($idx === 0) {
                        $q->whereRaw('LOWER(gender) = ?', [$gLower]);
                    } else {
                        $q->orWhereRaw('LOWER(gender) = ?', [$gLower]);
                    }
                }
            });
        }

        if ($concentrationParam = ($request->string('concentration')->toString() ?: $request->string('concentration_id')->toString())) {
            $concList = array_values(array_filter(array_map('trim', explode(',', $concentrationParam))));
            $query->whereHas('fragranceConcentration', function ($cq) use ($concList) {
                $cq->whereIn('slug', $concList)
                   ->orWhereIn('id', array_filter($concList, 'is_numeric'))
                   ->orWhere(function ($sub) use ($concList) {
                       foreach ($concList as $c) {
                           $sub->orWhere('name', 'like', '%' . $c . '%');
                       }
                   });
            });
        }

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%')
                    ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->where('name', 'like', '%' . $search . '%'))
                    ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            });
        }

        $featuredFilter = $request->string('featured')->toString();
        if ($featuredFilter !== '') {
            if (in_array(strtolower($featuredFilter), ['1', 'true', 'yes'], true)) {
                $query->where('is_featured', true);
            } elseif (in_array(strtolower($featuredFilter), ['0', 'false', 'no'], true)) {
                $query->where('is_featured', false);
            }
        }

        $filterParam = strtolower($request->string('filter', $request->string('type')->toString())->toString());
        $bestsellerParam = strtolower($request->string('bestseller', $request->string('is_bestseller')->toString())->toString());
        $newParam = strtolower($request->string('new', $request->string('is_new', $request->string('new_arrivals')->toString())->toString())->toString());

        if (
            in_array($filterParam, ['bestseller', 'bestsellers', 'best-sellers', 'featured'], true) ||
            in_array($bestsellerParam, ['1', 'true', 'yes'], true)
        ) {
            $query->where(function ($q) {
                $q->where('is_featured', true);
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'is_bestseller')) {
                    $q->orWhere('is_bestseller', true);
                }
                $q->orWhereHas('collections', fn ($cq) => $cq->whereIn('slug', ['best-sellers', 'bestsellers', 'best-choice']));
            });
        } elseif (
            in_array($filterParam, ['new', 'new_arrivals', 'new-arrivals', 'latest'], true) ||
            in_array($newParam, ['1', 'true', 'yes'], true)
        ) {
            $query->where(function ($q) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'is_new')) {
                    $q->where('is_new', true);
                }
                $q->orWhereHas('collections', fn ($cq) => $cq->whereIn('slug', ['new-arrivals', 'newarrivals', 'special-deals']));
            });
        }

        $sort = $request->string('sort', 'latest')->toString();

        $query->when($sort === 'price_low', function ($q) {
                return $q->orderBy(
                    \App\Models\ProductVariant::select('selling_price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->orderBy('selling_price', 'asc')
                        ->limit(1),
                    'asc'
                );
            })
            ->when($sort === 'price_high', function ($q) {
                return $q->orderBy(
                    \App\Models\ProductVariant::select('selling_price')
                        ->whereColumn('product_variants.product_id', 'products.id')
                        ->orderBy('selling_price', 'desc')
                        ->limit(1),
                    'desc'
                );
            })
            ->when($sort === 'name_asc', fn ($q) => $q->orderBy('name'))
            ->when($sort === 'name_desc', fn ($q) => $q->orderByDesc('name'))
            ->when($sort === 'featured', fn ($q) => $q->orderByDesc('is_featured'))
            ->when($sort === 'latest' || ! in_array($sort, ['price_low', 'price_high', 'name_asc', 'name_desc', 'featured'], true), fn ($q) => $q->latest());

        $products = $query->get();

        $page = (int) $request->integer('page', 1);
        $paginator = new LengthAwarePaginator(
            $products->forPage($page, $perPage),
            $products->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return response()->json([
            'data' => $paginator->getCollection()->map(fn (Product $product) => $this->productPayload($product))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    public function product(string $identifier): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        $product->load(['brand', 'category', 'variants', 'reviews', 'images']);

        return response()->json($this->productPayload($product));
    }

    public function submitReview(Request $request, string $identifier): JsonResponse
    {
        $product = Product::query()
            ->where('slug', $identifier)
            ->orWhere('id', $identifier)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $product->reviews()->create([
            'review_name' => $validated['name'],
            'review_email' => $validated['email'],
            'review_rating' => $validated['rating'],
            'review_title' => $validated['title'] ?? null,
            'review_content' => $validated['content'],
            'review_status' => false,
        ]);

        return response()->json([
            'message' => 'Thank you for your review! It has been submitted for approval.',
        ], 201);
    }

    public function home(): JsonResponse
    {
        $products = Product::query()
            ->with(['brand', 'category', 'variants', 'reviews', 'images'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
            ->latest()
            ->take(15)
            ->get();

        $homeAdvertisement = Advertisement::query()
            ->find(1);

        $banners = Banner::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('position', 'hero')->orWhereNull('position');
            })
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Banner $banner) => [
                'id' => $banner->id,
                'name' => $banner->name,
                'image_url' => $this->assetUrl($banner->image),
                'url' => $banner->url,
            ])->values();

        $middleBannerObj = Banner::query()
            ->where('is_active', true)
            ->where('position', 'middle')
            ->orderBy('sort_order')
            ->first();

        $middleBanner = $middleBannerObj ? [
            'id' => $middleBannerObj->id,
            'name' => $middleBannerObj->name,
            'image_url' => $this->assetUrl($middleBannerObj->image),
            'url' => $middleBannerObj->url ?: '/shop',
        ] : null;

        $whyChooseUs = \App\Models\WhyChooseUsItem::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'description' => $item->description,
                'icon' => $item->icon,
            ])->values();

        $brands = Brand::query()
            ->with(['products' => fn ($q) => $q->where('is_active', true)->take(1)])
            ->get()
            ->map(function (Brand $brand, int $index) {
                $payload = $this->brandPayload($brand);

                return $payload + ['order' => $index + 1];
            })->values();

        $sections = HomePageSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (HomePageSection $section) => $this->homeSectionPayload($section))
            ->values();

        $featuredCategories = Category::query()
            ->where('home_featured', true)
            ->where('is_active', true)
            ->get()
            ->map(function (Category $category) {
                $categoryIds = Category::query()
                    ->where('id', $category->id)
                    ->orWhere('parent_id', $category->id)
                    ->pluck('id');

                $products = Product::query()
                    ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                    ->whereIn('category_id', $categoryIds)
                    ->where('is_active', true)
                    ->latest()
                    ->take(4)
                    ->get();

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'products' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
                ];
            })
            ->values();

        $announcements = Announcement::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'text' => $announcement->text,
            ])->values();

        $collections = \App\Models\Collection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (\App\Models\Collection $col) {
                $prods = $col->products()
                    ->with(['brand', 'category', 'variants', 'reviews', 'images'])
                    ->where('is_active', true)
                    ->take(12)
                    ->get();

                return [
                    'id' => $col->id,
                    'name' => $col->name,
                    'slug' => $col->slug,
                    'description' => $col->description,
                    'products' => $prods->map(fn (Product $p) => $this->productPayload($p))->values(),
                ];
            })
            ->values();

        $shippingSetting = \App\Models\ShippingSetting::getSettings();
        $shippingSettingsPayload = [
            'default_shipping_fee' => (float) $shippingSetting->default_shipping_fee,
            'free_shipping_threshold' => (float) $shippingSetting->free_shipping_threshold,
            'is_enabled' => (bool) $shippingSetting->is_enabled,
        ];

        return response()->json([
            'home_advertisement' => $homeAdvertisement ? [
                'id' => $homeAdvertisement->id,
                'name' => $homeAdvertisement->name,
                'title' => $homeAdvertisement->title,
                'banner_url' => $this->assetUrl($homeAdvertisement->banner),
                'url' => $homeAdvertisement->url,
            ] : null,
            'banners' => $banners,
            'middle_banner' => $middleBanner,
            'why_choose_us' => $whyChooseUs,
            'shipping_settings' => $shippingSettingsPayload,
            'collections' => $collections,
            'products' => $products->map(fn (Product $product) => $this->productPayload($product))->values(),
            'brands' => $brands,
            'sections' => $sections,
            'featured_categories' => $featuredCategories,
            'announcements' => $announcements,
        ]);
    }

    public function topOffers(): JsonResponse
    {
        return response()->json($this->topOfferPayloads());
    }

    public function faqs(): JsonResponse
    {
        $faqs = \App\Models\Faq::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return response()->json($faqs);
    }

    public function testimonials(): JsonResponse
    {
        $testimonials = \App\Models\Testimonial::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json($testimonials->map(fn (\App\Models\Testimonial $t) => [
            'id' => $t->id,
            'name' => $t->name,
            'role' => $t->role ?: 'Verified Client',
            'quote' => $t->quote,
            'rating' => (int) ($t->rating ?: 5),
            'image' => $t->image ? $this->assetUrl($t->image) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?q=80&w=200&auto=format&fit=crop',
        ])->values());
    }

    public function fragranceFilters(): JsonResponse
    {
        $families = \App\Models\FragranceFamily::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $concentrations = \App\Models\FragranceConcentration::query()->orderBy('name')->get(['id', 'name', 'slug']);
        $genders = ['Men', 'Women', 'Unisex'];

        return response()->json([
            'families' => $families,
            'concentrations' => $concentrations,
            'genders' => $genders,
        ]);
    }

    public function validateCartStock(Request $request): JsonResponse
    {
        $cart = $request->input('cart', []);
        if (!is_array($cart)) {
            return response()->json(['valid' => true, 'stock_issues' => [], 'stock_map' => []]);
        }

        $stockIssues = [];
        $stockMap = [];

        foreach ($cart as $item) {
            $productId = $item['product_id'] ?? $item['productId'] ?? null;
            $variantId = $item['variant_id'] ?? $item['variantId'] ?? null;
            $requestedQty = (int) ($item['quantity'] ?? 1);
            $cartKey = $item['id'] ?? ($productId ? ($productId . '_' . $variantId) : null);
            $dealSlug = $item['dealSlug'] ?? $item['deal_slug'] ?? null;
            $isDeal = !empty($item['isDeal']) || (is_string($cartKey) && str_starts_with($cartKey, 'deal-')) || (is_string($productId) && str_starts_with($productId, 'deal-'));

            if ($isDeal || $dealSlug) {
                $slug = $dealSlug ?: (is_string($cartKey) ? str_replace('deal-', '', $cartKey) : (is_string($productId) ? str_replace('deal-', '', $productId) : ''));
                $deal = \App\Models\CuratedDeal::where('slug', $slug)->first();
                if (!$deal || !$deal->is_active) {
                    $stockIssues[] = [
                        'id' => $cartKey ?? $slug,
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'name' => $item['name'] ?? 'Curated Deal',
                        'requested_quantity' => $requestedQty,
                        'available_stock' => 0,
                        'issue' => 'unavailable',
                        'message' => "'" . ($item['name'] ?? 'Curated Deal') . "' is no longer available.",
                    ];
                } else {
                    $dealStock = (int) ($deal->stock ?? 100);
                    if ($dealStock <= 0) {
                        $stockIssues[] = [
                            'id' => $cartKey ?? $slug,
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'name' => $deal->name,
                            'requested_quantity' => $requestedQty,
                            'available_stock' => 0,
                            'issue' => 'out_of_stock',
                            'message' => "'{$deal->name}' is currently out of stock.",
                        ];
                    } elseif ($requestedQty > $dealStock) {
                        $stockIssues[] = [
                            'id' => $cartKey ?? $slug,
                            'product_id' => $productId,
                            'variant_id' => $variantId,
                            'name' => $deal->name,
                            'requested_quantity' => $requestedQty,
                            'available_stock' => $dealStock,
                            'issue' => 'insufficient_stock',
                            'message' => "Only {$dealStock} unit(s) of '{$deal->name}' are available in stock (you requested {$requestedQty}).",
                        ];
                    }
                    if ($cartKey) {
                        $stockMap[$cartKey] = $dealStock;
                    }
                }
                continue;
            }

            if (!$productId) continue;

            $product = Product::find($productId);
            if (!$product || !$product->is_active) {
                $stockIssues[] = [
                    'id' => $cartKey,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $item['name'] ?? "Product #{$productId}",
                    'requested_quantity' => $requestedQty,
                    'available_stock' => 0,
                    'issue' => 'unavailable',
                    'message' => "'" . ($item['name'] ?? 'Product') . "' is no longer available.",
                ];
                continue;
            }

            $variant = null;
            if ($variantId) {
                $variant = \App\Models\ProductVariant::find($variantId);
            }
            if (!$variant) {
                $product->loadMissing('variants');
                $variant = $product->variants->first(fn($v) => (int) $v->stock > 0) ?? $product->variants->first();
            }

            $availableStock = $variant ? (int) $variant->stock : 0;
            if ($variantId) {
                $stockMap[$variantId] = $availableStock;
            }
            $stockMap['p_' . $productId] = $availableStock;

            $variantName = $variant ? ($variant->name ?: $variant->sku ?: '') : '';
            $displayName = $product->name . ($variantName ? " ({$variantName})" : '');

            if ($availableStock <= 0) {
                $stockIssues[] = [
                    'id' => $cartKey,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $displayName,
                    'requested_quantity' => $requestedQty,
                    'available_stock' => 0,
                    'issue' => 'out_of_stock',
                    'message' => "'{$displayName}' is currently out of stock.",
                ];
            } elseif ($requestedQty > $availableStock) {
                $stockIssues[] = [
                    'id' => $cartKey,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name' => $displayName,
                    'requested_quantity' => $requestedQty,
                    'available_stock' => $availableStock,
                    'issue' => 'insufficient_stock',
                    'message' => "Only {$availableStock} unit(s) of '{$displayName}' are available in stock (you requested {$requestedQty}).",
                ];
            }
        }

        return response()->json([
            'valid' => count($stockIssues) === 0,
            'stock_issues' => $stockIssues,
            'stock_map' => $stockMap,
        ]);
    }

    public function curatedDeals(): JsonResponse
    {
        $deals = \App\Models\CuratedDeal::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (\App\Models\CuratedDeal $d) => [
                'id' => $d->id,
                'slug' => $d->slug,
                'name' => $d->name,
                'subtitle' => $d->subtitle,
                'description' => $d->description,
                'image' => $d->image ? $this->assetUrl($d->image) : null,
                'price' => (float) $d->price,
                'originalPrice' => (float) ($d->original_price ?? $d->price),
                'discountPercent' => (int) ($d->discount_percent ?? 0),
                'badge' => $d->badge,
                'contents' => $d->contents ?: [],
                'features' => $d->features ?: [],
                'link' => "/deals/{$d->slug}",
            ]);

        return response()->json($deals);
    }

    public function curatedDeal(string $slug): JsonResponse
    {
        $d = \App\Models\CuratedDeal::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$d) {
            return response()->json(['message' => 'Deal not found'], 404);
        }

        return response()->json([
            'id' => $d->id,
            'slug' => $d->slug,
            'name' => $d->name,
            'subtitle' => $d->subtitle,
            'description' => $d->description,
            'image' => $d->image ? $this->assetUrl($d->image) : null,
            'price' => (float) $d->price,
            'originalPrice' => (float) ($d->original_price ?? $d->price),
            'discountPercent' => (int) ($d->discount_percent ?? 0),
            'badge' => $d->badge,
            'contents' => $d->contents ?: [],
            'features' => $d->features ?: [],
            'link' => "/deals/{$d->slug}",
        ]);
    }

    public function submitInquiry(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $inquiry = \App\Models\Inquiry::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        try {
            \Illuminate\Support\Facades\Mail::to('sales@premium-perfumes.com')
                ->send(new \App\Mail\InquiryNotificationMail($inquiry));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Inquiry email notification failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Thank you for reaching out. Our fragrance concierge team will contact you shortly.',
            'inquiry_id' => $inquiry->id,
        ]);
    }

    public function announcements(): JsonResponse
    {
        $announcements = Announcement::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'text' => $announcement->text,
                'sort_order' => $announcement->sort_order,
            ])->values();

        return response()->json($announcements);
    }

    public function banners(): JsonResponse
    {
        $banners = Banner::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Banner $banner) => [
                'id' => $banner->id,
                'name' => $banner->name,
                'image_url' => $this->assetUrl($banner->image),
                'url' => $banner->url,
            ])->values();

        return response()->json($banners);
    }

    public function getSeo(Request $request): JsonResponse
    {
        $page = trim((string) $request->query('page', $request->query('slug', 'home')));
        $cleanPage = strtolower(ltrim($page, '/'));

        $seo = \App\Models\Seo::query()
            ->where('page_slug', $cleanPage)
            ->orWhere('page_slug', $page)
            ->orWhere('title', 'like', "%{$page}%")
            ->first();

        if (!$seo) {
            return response()->json([
                'title' => null,
                'meta_title' => null,
                'meta_description' => null,
                'meta_keywords' => null,
            ]);
        }

        return response()->json([
            'id' => $seo->id,
            'title' => $seo->title,
            'page_slug' => $seo->page_slug,
            'meta_title' => $seo->meta_title,
            'meta_description' => $seo->meta_description,
            'meta_keywords' => $seo->meta_keywords,
        ]);
    }
}
