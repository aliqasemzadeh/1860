<?php

namespace App\Services\Content;

use App\Settings\GeneralSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BannerImageService
{
    public const WEBP_QUALITY = 82;

    public const DESKTOP_MAX_WIDTH = 1600;

    public const TABLET_MAX_WIDTH = 1024;

    public const MOBILE_CUSTOM_MAX_WIDTH = 768;

    public const MOBILE_AUTO_MAX_WIDTH = 640;

    private ImageManager $manager;

    public function __construct(private GeneralSettings $settings)
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Optimize and store desktop, tablet, and mobile WebP variants from the desktop upload.
     * If a separate mobile upload is provided, use it for the mobile variant.
     *
     * @return array{desktop_image: string, tablet_image: string, mobile_image: string}
     */
    public function storeResponsiveSet(
        string|UploadedFile|TemporaryUploadedFile $desktopSource,
        string $title,
        string|UploadedFile|TemporaryUploadedFile|null $mobileSource = null,
    ): array {
        $baseStem = $this->buildBaseStem($title);

        $desktopBinary = $this->readBinary($desktopSource);

        $desktopPath = $this->uniqueRelativePath('banners', $baseStem.'-desktop');
        $desktopEncoded = (string) $this->manager->read($desktopBinary)
            ->scaleDown(width: self::DESKTOP_MAX_WIDTH)
            ->toWebp(quality: self::WEBP_QUALITY);
        $this->putPublicFile($desktopPath, $desktopEncoded);

        $tabletPath = $this->uniqueRelativePath('banners', $baseStem.'-tablet');
        $tabletEncoded = (string) $this->manager->read($desktopBinary)
            ->scaleDown(width: self::TABLET_MAX_WIDTH)
            ->toWebp(quality: self::WEBP_QUALITY);
        $this->putPublicFile($tabletPath, $tabletEncoded);

        $mobilePath = $this->uniqueRelativePath('banners', $baseStem.'-mobile');

        if ($mobileSource !== null) {
            $mobileBinary = $this->readBinary($mobileSource);
            $mobileEncoded = (string) $this->manager->read($mobileBinary)
                ->scaleDown(width: self::MOBILE_CUSTOM_MAX_WIDTH)
                ->toWebp(quality: self::WEBP_QUALITY);
        } else {
            $mobileEncoded = (string) $this->manager->read($desktopBinary)
                ->scaleDown(width: self::MOBILE_AUTO_MAX_WIDTH)
                ->toWebp(quality: self::WEBP_QUALITY);
        }

        $this->putPublicFile($mobilePath, $mobileEncoded);

        return [
            'desktop_image' => $desktopPath,
            'tablet_image' => $tabletPath,
            'mobile_image' => $mobilePath,
        ];
    }

    /**
     * Optimize and store only a dedicated mobile WebP image.
     */
    public function storeMobileOnly(
        string|UploadedFile|TemporaryUploadedFile $mobileSource,
        string $title,
        ?string $oldMobilePath = null,
    ): string {
        $baseStem = $this->buildBaseStem($title);
        $mobilePath = $this->uniqueRelativePath('banners', $baseStem.'-mobile', $oldMobilePath);

        $mobileBinary = $this->readBinary($mobileSource);
        $mobileEncoded = (string) $this->manager->read($mobileBinary)
            ->scaleDown(width: self::MOBILE_CUSTOM_MAX_WIDTH)
            ->toWebp(quality: self::WEBP_QUALITY);

        $this->putPublicFile($mobilePath, $mobileEncoded);

        if ($oldMobilePath && $oldMobilePath !== $mobilePath) {
            $this->deleteFiles([$oldMobilePath]);
        }

        return $mobilePath;
    }

    /**
     * @param  list<?string>  $paths
     */
    public function deleteFiles(array $paths): void
    {
        $validPaths = array_values(array_unique(array_filter($paths)));

        foreach ($validPaths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function buildBaseStem(string $title): string
    {
        $titlePart = $this->sanitizeSegment(slug_fa($title));

        $sitePart = $this->sanitizeSegment(
            slug_fa((string) ($this->settings->title ?: config('app.name')))
        );

        $stem = collect([$titlePart ?: 'banner', $sitePart])
            ->filter()
            ->implode('-');

        return $stem !== '' ? $stem : 'banner';
    }

    public function sanitizeSegment(string $value): string
    {
        $value = str_replace([' ', '_'], '-', $value);
        $value = preg_replace('/[^\p{L}\p{N}\-]+/u', '-', $value) ?? '';
        $value = preg_replace('/-+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    public function uniqueRelativePath(
        string $directory,
        string $stem,
        ?string $excludePath = null,
    ): array|string {
        $directory = trim($directory, '/');
        $index = 0;

        do {
            $fileName = $index === 0
                ? $stem.'.webp'
                : $stem.'-'.$index.'.webp';
            $filePath = $directory.'/'.$fileName;
            $index++;
        } while (
            Storage::disk('public')->exists($filePath)
            && ($excludePath === null || $filePath !== $excludePath)
        );

        return $filePath;
    }

    private function putPublicFile(string $path, string $contents): void
    {
        if (! Storage::disk('public')->put($path, $contents)) {
            throw new \RuntimeException('Failed to store optimized banner image: '.$path);
        }
    }

    private function readBinary(string|UploadedFile|TemporaryUploadedFile $sourceFile): string
    {
        if ($sourceFile instanceof UploadedFile || $sourceFile instanceof TemporaryUploadedFile) {
            $contents = file_get_contents($sourceFile->getRealPath());

            if ($contents === false) {
                throw new \RuntimeException('Unable to read uploaded banner image.');
            }

            return $contents;
        }

        if (is_file($sourceFile)) {
            $contents = file_get_contents($sourceFile);

            if ($contents === false) {
                throw new \RuntimeException("Unable to read banner image: {$sourceFile}");
            }

            return $contents;
        }

        return $sourceFile;
    }
}
