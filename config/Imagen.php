<?php
/** Solo se conservan imagenes decodificadas y recodificadas, nunca el archivo original. */
class Imagen {
    public static function guardar(array $archivo, string $tipo): ?string {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if (!in_array($tipo, ['perfil', 'logo'], true)) throw new RuntimeException('Tipo de imagen invalido.');
        if (($archivo['error'] ?? -1) !== UPLOAD_ERR_OK || !is_uploaded_file($archivo['tmp_name'] ?? '')) {
            throw new RuntimeException('No se pudo recibir la imagen.');
        }
        if (filesize($archivo['tmp_name']) > 5 * 1024 * 1024) throw new RuntimeException('La imagen debe pesar como maximo 5 MB.');
        $info = @getimagesize($archivo['tmp_name']);
        if (!$info || !in_array($info['mime'], ['image/jpeg', 'image/png', 'image/webp'], true)
            || $info[0] * $info[1] > 16000000 || max($info[0], $info[1]) > 8000) {
            throw new RuntimeException('Selecciona una imagen JPG, PNG o WebP de hasta 16 megapixeles.');
        }
        if (!function_exists('imagewebp')) throw new RuntimeException('Activa la extension GD de PHP para optimizar las imagenes.');
        $source = @imagecreatefromstring(file_get_contents($archivo['tmp_name']));
        if (!$source) throw new RuntimeException('La imagen esta danada.');
        if ($info['mime'] === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($archivo['tmp_name']);
            $orientation = (int)($exif['Orientation'] ?? 1);
            if (in_array($orientation, [2, 4, 5, 7], true)) imageflip($source, IMG_FLIP_HORIZONTAL);
            $angle = [3 => 180, 4 => 180, 5 => -90, 6 => -90, 7 => 90, 8 => 90][$orientation] ?? 0;
            if ($angle) { $rotated = imagerotate($source, $angle, 0); imagedestroy($source); $source = $rotated; }
        }
        $limit = $tipo === 'perfil' ? 256 : 512;
        $ratio = min(1, $limit / max(imagesx($source), imagesy($source)));
        $target = imagecreatetruecolor(max(1, (int)round(imagesx($source)*$ratio)), max(1, (int)round(imagesy($source)*$ratio)));
        imagealphablending($target, false); imagesavealpha($target, true);
        imagefill($target, 0, 0, imagecolorallocatealpha($target, 0, 0, 0, 127));
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), imagesx($source), imagesy($source));
        $relative = 'uploads/identidad/'.bin2hex(random_bytes(16)).'.webp';
        $path = __DIR__.'/../public/'.$relative;
        try {
            if (!is_dir(dirname($path)) && !mkdir(dirname($path), 0755, true)) throw new RuntimeException('No se pudo preparar el almacenamiento.');
            if (!imagewebp($target, $path, 78)) throw new RuntimeException('No se pudo guardar la imagen optimizada.');
            if (filesize($path) > 180 * 1024) {
                imagewebp($target, $path, 55);
                clearstatcache(true, $path);
                if (filesize($path) > 180 * 1024) throw new RuntimeException('La imagen es demasiado compleja. Selecciona otra.');
            }
            return $relative;
        } catch (Throwable $e) { self::eliminar($relative); throw $e; }
        finally { imagedestroy($source); imagedestroy($target); }
    }
    public static function eliminar(?string $path): void {
        if ($path && preg_match('~^uploads/identidad/[a-f0-9]{32}\.webp$~D', $path)) {
            $absolute = __DIR__.'/../public/'.$path;
            if (is_file($absolute)) unlink($absolute);
        }
    }
}
