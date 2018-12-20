<?php

namespace Manix\Brat\Helpers;

use Exception;

class Image {

  const WATERMARK_REPEAT = 1;
  const WATERMARK_REPEATX = 2;
  const WATERMARK_REPEATY = 4;
  const WATERMARK_SINGLE = 8;
  const WATERMARK_TOPLEFT = 16;
  const WATERMARK_TOPRIGHT = 32;
  const WATERMARK_BOTLEFT = 64;
  const WATERMARK_BOTRIGHT = 128;

  protected $filename;
  protected $info = [];
  protected $resource;

  public function getResource() {
    return $this->resource;
  }

  /**
   * 
   * @param type $str
   * @return Image
   */
  public static function fromString($str) {
    $image = new self;

    $image->resource = imagecreatefromstring($str);
    $image->updateDimensions();

    return $image;
  }

  /**
   * 
   * @param type $res
   * @return Image
   */
  public static function fromResource($res) {
    $image = new self;
    $image->resource = $res;

    $image->updateDimensions();

    return $image;
  }

  /**
   * 
   * @param type $fname
   * @return Image
   * @throws Exception
   */
  public static function fromFile($fname) {
    $image = new self;

    if (!is_file($fname)) {
      throw new Exception('Failed to load image.', 500);
    }

    $image->filename = $fname;
    $image->info = getimagesize($image->filename);

    if ($image->info === false) {
      throw new Exception('Failed to load image.', 500);
    }

    switch ($image->info[2]) {
      case IMAGETYPE_GIF:
        $image->resource = imagecreatefromgif($image->filename);
        break;

      case IMAGETYPE_JPEG:
      case IMAGETYPE_JPEG2000:
      case IMAGETYPE_JPC:
      case IMAGETYPE_JP2:
      case IMAGETYPE_JPX:
        $image->resource = imagecreatefromjpeg($image->filename);
        break;

      case IMAGETYPE_PNG:
        $image->resource = imagecreatefrompng($image->filename);
        break;

      case IMAGETYPE_BMP:
      case IMAGETYPE_WBMP:
        $image->resource = imagecreatefromwbmp($image->filename);
        break;

      case IMAGETYPE_XBM:
        $image->resource = imagecreatefromxbm($image->filename);
        break;

      case IMAGETYPE_TIFF_II:
      case IMAGETYPE_TIFF_MM:
      case IMAGETYPE_IFF:
      case IMAGETYPE_JB2:
      case IMAGETYPE_SWF:
      case IMAGETYPE_PSD:
      case IMAGETYPE_SWC:
      // case IMAGETYPE_ICO:
      default:
        $image->resource = null;
        break;
    }

    return $image;
  }

  public function __get($name) {
    switch ($name) {
      case 'width':
        return $this->info[0];

      case 'height':
        return $this->info[1];

      case 'ratio':
        return $this->info[0] / $this->info[1];

      case 'mime':
        return $this->info['mime'];

      case 'type':
        return $this->info[2];

      case 'resource':
        return $this->resource;

      case 'file':
        return $this->filename;

      case 'info':
        return $this->info;
    }
  }

  public function __clone() {
    $original = $this->resource;

    $this->updateDimensions();

    $copy = imagecreatetruecolor($this->info[0], $this->info[1]);
    imagealphablending($copy, false);
    imagesavealpha($copy, true);

    imagecopy($copy, $original, 0, 0, 0, 0, $this->info[0], $this->info[1]);

    $this->resource = $copy;
    $this->filename = null;
  }

  public function setType($type) {
    $this->info[2] = $type;
    $this->info['mime'] = image_type_to_mime_type($type);

    return $this;
  }

  public function setFile($path, $ext = false) {
    if ($ext) {
      if (!$this->info[2]) {
        throw new Exception('Image object has no type', 500);
      }

      $path .= image_type_to_extension($this->info[2]);
    }


    $this->filename = $path;

    return $this;
  }

  public function save(...$options) {
    if (!$this->filename) {
      throw new Exception('Image object has no filename', 500);
    }

    if (!$this->info[2]) {
      throw new Exception('Image object has no type', 500);
    }

    switch ($this->info[2]) {
      case IMAGETYPE_GIF:
        imagegif($this->resource, $this->filename);
        break;

      case IMAGETYPE_JPEG:
      case IMAGETYPE_JPEG2000:
      case IMAGETYPE_JPC:
      case IMAGETYPE_JP2:
      case IMAGETYPE_JPX:
        imagejpeg($this->resource, $this->filename, ...$options);
        break;

      case IMAGETYPE_PNG:
        imagesavealpha($this->resource, true);
        imagepng($this->resource, $this->filename, ...$options);
        break;

      case IMAGETYPE_BMP:
      case IMAGETYPE_WBMP:
        imagewbmp($this->resource, $this->filename);
        break;

      case IMAGETYPE_XBM:
        imagexbm($this->resource, $this->filename);
        break;

      case IMAGETYPE_TIFF_II:
      case IMAGETYPE_TIFF_MM:
      case IMAGETYPE_IFF:
      case IMAGETYPE_JB2:
      case IMAGETYPE_SWF:
      case IMAGETYPE_PSD:
      case IMAGETYPE_SWC:
      // case IMAGETYPE_ICO:
      default:
        throw new Exception('Image not saved - unsupported type', 500);
    }

    return $this;
  }

  public function updateDimensions() {
    $this->info[0] = imagesx($this->resource);
    $this->info[1] = imagesy($this->resource);

    return $this;
  }

  public function watermark(Image $watermark, $mode = self::WATERMARK_REPEAT, $opacity = 100, $offsetX = null, $offsetY = null) {

    $derivedW = $this->info[0] * 0.4;
    $derivedH = $this->info[1] * 0.4;

    if ($derivedW < $watermark->info[0] || $derivedH < $watermark->info[1]) {
      $watermark->resize($derivedW, $derivedH);
    }

    $dest = $this->resource;

    if ($offsetX === null) {
      $offsetX = intval(($this->info[0] / 2) - ($watermark->info[0] / 2));
    }
    if ($offsetY === null) {
      $offsetY = intval(($this->info[1] / 2) - ($watermark->info[1] / 2));
    }

    switch ($mode) {
      case null:
      case self::WATERMARK_REPEAT:
        $offsetX = 0;
        $offsetY = 0;

        for ($i = $offsetY; $i < $this->info[1]; $i += $watermark->info[1]) {
          for ($j = $offsetX; $j < $this->info[0]; $j += $watermark->info[0]) {
            if (!imagecopymerge($dest, $watermark->resource, $j, $i, 0, 0, $watermark->info[0], $watermark->info[1], $opacity)) {
              throw new Exception('Image::scale: Error watermarking image.');
            }
          }
        }
        break;

      case self::WATERMARK_SINGLE:
        if (!imagecopymerge($dest, $watermark->resource, $offsetX, $offsetY, 0, 0, $watermark->info[0], $watermark->info[1], $opacity)) {
          throw new Exception('Image::scale: Error watermarking image.');
        }
        break;

      case self::WATERMARK_REPEATX:
        $offsetX = 0;

        for ($i = $offsetX; $i <= $this->info[0]; $i += $watermark->info[0]) {
          if (!imagecopymerge($dest, $watermark->resource, $i, $offsetY, 0, 0, $watermark->info[0], $watermark->info[1], $opacity)) {
            throw new Exception('Image::scale: Error watermarking image.');
          }
        }
        break;

      case self::WATERMARK_REPEATY:
        $offsetY = 0;

        for ($i = $offsetY; $i <= $this->info[1]; $i += $watermark->info[1]) {
          if (!imagecopymerge($dest, $watermark->resource, $offsetX, $i, 0, 0, $watermark->info[0], $watermark->info[1], $opacity)) {
            throw new Exception('Image::scale: Error watermarking image.');
          }
        }
        break;

      case self::WATERMARK_BOTLEFT:
        $this->watermark($watermark, self::WATERMARK_SINGLE, $opacity, 0, $this->info[1] - $watermark->info[1]);
        break;

      case self::WATERMARK_BOTRIGHT:
        $this->watermark($watermark, self::WATERMARK_SINGLE, $opacity, $this->info[0] - $watermark->info[0], $this->info[1] - $watermark->info[1]);
        break;

      case self::WATERMARK_TOPLEFT:
        $this->watermark($watermark, self::WATERMARK_SINGLE, $opacity, 0, 0);
        break;

      case self::WATERMARK_TOPRIGHT:
        $this->watermark($watermark, self::WATERMARK_SINGLE, $opacity, $this->info[0] - $watermark->info[0], 0);
        break;
    }

    return $this;
  }

  public function resize($w, $h) {
    $w_orig = $this->info[0];
    $h_orig = $this->info[1];

    $scale_ratio = $w_orig / $h_orig;

    if (($w / $h) > $scale_ratio) {
      $w = $h * $scale_ratio;
    } else {
      $h = $w / $scale_ratio;
    }

    $tci = imagecreatetruecolor($w, $h);
    imagealphablending($tci, false);
    imagesavealpha($tci, true);

    imagecopyresampled($tci, $this->resource, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
    imagedestroy($this->resource);

    $this->resource = $tci;
    $this->updateDimensions();

    return $this;
  }

  public function thumbnail($path, $w = 126, $h = 126) {
    $thumb = clone $this;

    return $thumb->setFile($path)->resize($w, $h)->save();
  }

  public function writeText($text, $fontfile, $size = 12, $angle = 0, $x = 'right', $y = 'bot', array $color = []) {
    if (!is_file($fontfile)) {
      throw new Exception('Font not found.', 500);
    }

    $dimensions = imagettfbbox($size, $angle, $fontfile, $text);

    switch ($x) {
      case 'left':
        $x = 0;
        break;
      case 'mid':
        $x = ($this->info[0] - abs($dimensions[4] - $dimensions[0])) / 2;
        break;
      case 'right':
        $x = $this->info[0] - abs($dimensions[4] - $dimensions[0]);
        break;
    }

    switch ($y) {
      case 'top':
        $y = abs($dimensions[5] - $dimensions[1]);
        break;
      case 'mid':
        $y = ($this->info[1] + abs($dimensions[5] - $dimensions[1])) / 2;
        break;

      case 'bot':
        $y = $this->info[1];
        break;
    }

    if ($y === null) {
      $y = $this->info[1] - $size;
    }

    if (!is_array($color) || count($color) != 3 || $color[0] < 0 || $color[1] < 0 || $color[2] < 0 || $color[0] > 255 || $color[1] > 255 || $color[2] > 255) {
      $color = [255, 255, 255];
    }

    $colorR = imagecolorallocate($this->resource, $color[0], $color[1], $color[2]);

    imagettftext($this->resource, $size, $angle, $x, $y, $colorR, $fontfile, $text);

    return $this;
  }

}
