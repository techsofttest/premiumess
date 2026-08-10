<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageResolver
{
    /** @var array<string, string> Optional lookup map of lowercase filename -> absolute path */
    protected array $lookup = [];

    /** @var string[] Supported image extensions in priority order */
    protected array $extensions = ['.webp', '.png', '.jpg', '.jpeg', '.avif', '.gif'];

    /** @var string The directory to search for source images */
    protected string $searchDirectory;

    /** @var string The storage path to copy images to */
    protected string $storagePath;

    public function __construct()
    {
        $this->searchDirectory = config('import.image_directory', storage_path('app/public/import_images'));
        $this->storagePath = config('import.image_storage_path', 'products');
    }

    /**
     * Set the in-memory lookup map for images.
     *
     * @param array<string, string> $lookup
     */
    public function setLookup(array $lookup): void
    {
        $this->lookup = $lookup;
    }

    /**
     * Resolve an image name (without extension) to a file path.
     * Searches the configured directory for a matching file using the priority extension list.
     *
     * @param string $imageName Image name without extension
     * @return string|null The relative path within the public disk, or null if not found
     */
    public function resolve(string $imageName): ?string
    {
        $imageName = trim($imageName);

        if (empty($imageName)) {
            return null;
        }

        // Try lookup map first (case-insensitive)
        if (!empty($this->lookup)) {
            $key = strtolower($imageName);
            if (isset($this->lookup[$key])) {
                $filePath = $this->lookup[$key];
                if (File::exists($filePath)) {
                    $ext = '.' . pathinfo($filePath, PATHINFO_EXTENSION);
                    $destFilename = $imageName . $ext;
                    $destPath = $this->storagePath . '/' . $destFilename;

                    Storage::disk('public')->put($destPath, File::get($filePath));

                    return $destPath;
                }
            }
        }

        foreach ($this->extensions as $ext) {
            $filePath = $this->searchDirectory . DIRECTORY_SEPARATOR . $imageName . $ext;

            if (File::exists($filePath)) {
                // Copy to public storage
                $destFilename = $imageName . $ext;
                $destPath = $this->storagePath . '/' . $destFilename;

                Storage::disk('public')->put($destPath, File::get($filePath));

                return $destPath;
            }
        }

        return null;
    }

    /**
     * Resolve multiple comma-separated image names.
     *
     * @param string|null $imageNames Comma-separated image names
     * @return array<string> Array of resolved storage paths
     */
    public function resolveMultiple(?string $imageNames): array
    {
        if (empty($imageNames)) {
            return [];
        }

        $resolved = [];
        $names = array_map('trim', explode(',', $imageNames));

        foreach ($names as $name) {
            if (empty($name)) {
                continue;
            }

            $path = $this->resolve($name);
            if ($path !== null) {
                $resolved[] = $path;
            }
        }

        return $resolved;
    }
}
