<?php

namespace App\Services\Import;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ZipArchive;

class ImageImportService
{
    protected ?string $tempDirectory = null;
    protected array $supportedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];

    /**
     * Extract the ZIP file and return the filename-to-filepath lookup map.
     *
     * @param string $zipFilePath
     * @return array<string, string> Map of lowercase filename without extension -> absolute filepath
     * @throws \Exception
     */
    public function extractZip(string $zipFilePath): array
    {
        if (!File::exists($zipFilePath)) {
            throw new \InvalidArgumentException("ZIP file does not exist: {$zipFilePath}");
        }

        // Generate unique temporary directory
        $uniqueId = Str::uuid()->toString();
        $this->tempDirectory = storage_path("app/temp/imports/{$uniqueId}");

        if (!File::makeDirectory($this->tempDirectory, 0755, true, true)) {
            throw new \RuntimeException("Failed to create temporary directory: {$this->tempDirectory}");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new \RuntimeException("Failed to open ZIP archive: {$zipFilePath}");
        }

        if (!$zip->extractTo($this->tempDirectory)) {
            $zip->close();
            throw new \RuntimeException("Failed to extract ZIP archive: {$zipFilePath}");
        }
        $zip->close();

        return $this->buildLookupMap();
    }

    /**
     * Recursively scan the temporary directory and build the image lookup map.
     *
     * @return array<string, string>
     */
    protected function buildLookupMap(): array
    {
        if (!$this->tempDirectory || !File::isDirectory($this->tempDirectory)) {
            return [];
        }

        $lookup = [];
        $files = File::allFiles($this->tempDirectory);

        foreach ($files as $file) {
            $extension = strtolower($file->getExtension());
            if (in_array($extension, $this->supportedExtensions)) {
                $filenameWithoutExt = strtolower($file->getFilenameWithoutExtension());
                // Store the first matching file, ignore duplicates or overwrite
                $lookup[$filenameWithoutExt] = $file->getRealPath();
            }
        }

        return $lookup;
    }

    /**
     * Clean up all temporary files and the temporary directory.
     */
    public function cleanup(): void
    {
        if ($this->tempDirectory && File::isDirectory($this->tempDirectory)) {
            File::deleteDirectory($this->tempDirectory);
        }
        $this->tempDirectory = null;
    }
}
