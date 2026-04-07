<?php
/**
 * scripts/convert_images.php
 *
 * Usage: run from project root:
 *   php scripts/convert_images.php
 *
 * This script scans common image directories and creates .webp
 * versions for JPG/PNG files when a .webp does not already exist.
 * It prefers the GD extension (imagewebp). If GD is not available
 * but Imagick is, it will use Imagick.
 *
 * Note: run a backup or use under source control before mass conversion.
 */

set_time_limit(0);
ini_set('memory_limit', '512M');

$root = realpath(__DIR__ . '/..');
$dirs = [
    $root . '/assets/images',
    $root . '/uploads/annonces',
    $root . '/uploads/announcements',
    $root . '/uploads/hero-ads',
    $root . '/uploads/ingredients',
    $root . '/uploads/payments',
    $root . '/uploads',
];

$exts = ['jpg', 'jpeg', 'png'];
$quality = 80; // WebP quality

function findImages(array $dirs, array $exts) {
    $files = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
        foreach ($it as $f) {
            if (!$f->isFile()) continue;
            $path = $f->getPathname();
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if (in_array($ext, $exts, true)) $files[] = $path;
        }
    }
    return $files;
}

function convertWithGD($src, $dest, $ext, $quality) {
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            $img = @imagecreatefromjpeg($src);
            break;
        case 'png':
            $img = @imagecreatefrompng($src);
            break;
        default:
            return false;
    }
    if (!$img) return false;

    // Preserve transparency for PNG by enabling alpha blending
    if ($ext === 'png') {
        imagepalettetotruecolor($img);
        imagealphablending($img, true);
        imagesavealpha($img, true);
    }

    $ok = imagewebp($img, $dest, $quality);
    imagedestroy($img);
    return $ok;
}

function convertWithImagick($src, $dest, $quality) {
    if (!class_exists('Imagick')) return false;
    try {
        $im = new Imagick($src);
        // Flatten transparent backgrounds to preserve appearance if needed
        if ($im->getImageAlphaChannel()) {
            $im = $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }
        $im->setImageFormat('webp');
        $im->setImageCompressionQuality($quality);
        $im->writeImage($dest);
        $im->clear();
        $im->destroy();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$images = findImages($dirs, $exts);
if (empty($images)) {
    echo "No images found to convert. Checked paths:\n";
    foreach ($dirs as $d) echo " - $d\n";
    exit(0);
}

$gd = function_exists('imagewebp');
$imagick = class_exists('Imagick');

echo "Found " . count($images) . " images. Using GD: " . ($gd ? 'yes' : 'no') . ", Imagick: " . ($imagick ? 'yes' : 'no') . "\n";

$converted = 0;
$skipped = 0;
$failed = 0;

foreach ($images as $src) {
    $dir = pathinfo($src, PATHINFO_DIRNAME);
    $base = pathinfo($src, PATHINFO_FILENAME);
    $dest = $dir . DIRECTORY_SEPARATOR . $base . '.webp';
    if (file_exists($dest)) {
        $skipped++;
        continue;
    }
    $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION));
    $ok = false;
    if ($gd) {
        $ok = convertWithGD($src, $dest, $ext, $quality);
    }
    if (!$ok && $imagick) {
        $ok = convertWithImagick($src, $dest, $quality);
    }
    if ($ok) {
        $converted++;
        echo "Converted: $src -> $dest\n";
    } else {
        $failed++;
        echo "Failed: $src\n";
    }
}

echo "\nDone. Converted: $converted, Skipped(existing): $skipped, Failed: $failed\n";

// Exit codes: 0 success
exit(0);
