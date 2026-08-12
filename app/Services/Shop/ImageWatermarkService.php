<?php

namespace App\Services\Shop;

use App\Settings\GeneralSettings;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;

class ImageWatermarkService
{
    public const MARK_SUFFIX = 'Marked';

    private const WIDTH_RATIO = 0.12;

    private const MIN_LOGO_WIDTH = 60;

    private const MAX_LOGO_WIDTH = 320;

    private const MAX_LOGO_RATIO_OF_IMAGE = 0.4;

    private const PADDING = 12;

    private const OPACITY = 80;

    private ImageManager $manager;

    private ?ImageInterface $logo = null;

    public function __construct(private GeneralSettings $settings)
    {
        $this->manager = new ImageManager(new Driver);
    }

    public function hasLogo(): bool
    {
        $path = $this->settings->logo_path;

        return filled($path) && Storage::disk('public')->exists($path);
    }

    public function isMarked(string $path): bool
    {
        return str_ends_with(
            pathinfo($path, PATHINFO_FILENAME),
            self::MARK_SUFFIX
        );
    }

    public function markedPath(string $path): string
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $name = pathinfo($path, PATHINFO_FILENAME);
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        $newName = $name.self::MARK_SUFFIX.($ext !== '' ? '.'.$ext : '');

        if ($dir === '' || $dir === '.') {
            return $newName;
        }

        return $dir.'/'.$newName;
    }

    /**
     * Apply watermark and write the Marked file. Returns the new relative path.
     *
     * @throws \RuntimeException
     */
    public function apply(string $path, string $disk = 'public'): string
    {
        if (! $this->hasLogo()) {
            throw new \RuntimeException('Site logo is not configured.');
        }

        if (! Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException("Source image missing: {$path}");
        }

        $newPath = $this->markedPath($path);
        $absolute = Storage::disk($disk)->path($path);

        $image = $this->manager->read($absolute);
        $logo = $this->logoScaledFor($image->width());

        $image->place($logo, 'top-left', self::PADDING, self::PADDING, self::OPACITY);

        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'png';
        $encoded = (string) $image->encodeByExtension($extension);

        if (! Storage::disk($disk)->put($newPath, $encoded)) {
            throw new \RuntimeException("Failed writing watermarked image: {$newPath}");
        }

        return $newPath;
    }

    private function loadLogoOnce(): ImageInterface
    {
        if ($this->logo === null) {
            $logoPath = Storage::disk('public')->path($this->settings->logo_path);
            $this->logo = $this->manager->read($logoPath);
        }

        return $this->logo;
    }

    private function logoScaledFor(int $targetWidth): ImageInterface
    {
        $width = (int) round($targetWidth * self::WIDTH_RATIO);
        $width = max(self::MIN_LOGO_WIDTH, min(self::MAX_LOGO_WIDTH, $width));
        $width = min($width, max(1, (int) round($targetWidth * self::MAX_LOGO_RATIO_OF_IMAGE)));

        return (clone $this->loadLogoOnce())->scale(width: $width);
    }
}
