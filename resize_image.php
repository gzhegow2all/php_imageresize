<?
function resize_image($_path = null, $_w = null, $_h = null, $_mode = null, $_oversize = null) {
  // INIT
  defined('MAIN_DIR') || define('MAIN_DIR', dirname(__FILE__));
  defined('DS') || define('DS', DIRECTORY_SEPARATOR);

  // string
  $path = isset($_path) ? (string) $_path : null;
  $mode = isset($_mode) ? (string) $_mode : 'fit';

  // int
  $w = isset($_w) ? (int) $_w : 0;
  $h = isset($_h) ? (int) $_h : 0;

  // bool
  $oversize = isset($_oversize) ? (int) (boolean) $_oversize : 0;

  // defaults
  $noimage_path = MAIN_DIR . '/files/img/no-image.jpg';


  // PROCESS
  // return if there's no default image
  is_file($noimage_path) or exit('[resize_image] File not found - ' . $noimage_path);

  // check mode is allowed
  in_array($mode, array(
    'cut',
    'contain',
    'cover',
    'hard',
    'oneside'
  )) || exit('[resize_image] Unknown $mode - ' . $mode);

  // return if user haven't brain
  if (($w < 0) || ($h < 0))
    exit("[resize_image] Illegal size \$w: {$w} or \$h: {$h}");

  // if image's not exists - return default image
  $path = realpath($path);
  (!empty($path) && is_file($path)) or $path = $noimage_path;

  // get file info
  $matrix = array(
    'dirname',
    'basename',
    'filename',
    'extension'
  );
  $tmp = pathinfo($path);
  $arr = array_merge($matrix, $tmp);
  $old_dirname = $arr['dirname'];
  $old_basename = $arr['basename'];
  $old_filename = $arr['filename'];
  $old_extension = $arr['extension'];
  $old_mime = image_type_to_mime_type(exif_imagetype($path));

  // check is there an image
  $function_imagecreate = '';
  $function_imagecopy = '';
  $function_imagesave = '';
  switch($old_mime) {
    case 'image/jpg':
    case 'image/jpeg':
      $function_imagecreate = 'imagecreatetruecolor';
      $function_imagecopy = 'imagecreatefromjpeg';
      $function_imagesave = 'imagejpeg';
      break;
    case 'image/gif':
      $function_imagecreate = 'imagecreate';
      $function_imagecopy = 'imagecreatefromgif';
      $function_imagesave = 'imagegif';
      break;
    case 'image/png':
      $function_imagecreate = 'imagecreate';
      $function_imagecopy = 'imagecreatefrompng';
      $function_imagesave = 'imagepng';
      break;
    case 'image/bmp':
      $function_imagecreate = 'imagecreate';
      $function_imagecopy = 'imagecreatefrombmp';
      $function_imagesave = 'imagebmp';
      break;
    default:
      exit('[resize_image] Unknown mime-type - ' . $old_mime);
  }

  !is_dir($var = MAIN_DIR . "/temp") && mkdir($var, 0755);
  !is_dir($var = MAIN_DIR . "/temp/img") && mkdir($var, 0755);
  !is_dir($var = MAIN_DIR . "/temp/img/{$w}-{$h}") && mkdir($var, 0755);

  $hash = md5($path . filesize($path) . filemtime($path) . $old_mime . $w . $h);
  $new_path = $var . '/image_' . $hash . '.' . $old_extension;

  // return path if exists
  if (is_file($new_path))
    return realpath($new_path);

  // get old image data
  $old_width = 0;
  $old_height = 0;
  list($old_width, $old_height) = getimagesize($path);

  // init buffer
  $dst_x = 0;
  $dst_y = 0;
  $dst_height = 0;
  $dst_width = 0;
  $src_x = 0;
  $src_y = 0;
  $src_width = 0;
  $src_height = 0;

  // process new image size
  if (in_array($mode, array('contain'))) {
    $src_width = $old_width;
    $src_height = $old_height;
    if ($w <= $h) {
      $dst_width = $w;
      $dst_height = ceil($old_height * ($w / $old_width));
    } else {
      $dst_height = $h;
      $dst_width = ceil($old_width * ($h / $old_height));
    }

    $dst = $function_imagecreate($dst_width, $dst_height);
    $bg_color = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $dst_width, $dst_height, $bg_color);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $dst,
      $src,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $src_width,
      $src_height
    );
    $function_imagesave($dst, $new_path, 100);
    imagedestroy($src);
    imagedestroy($dst);
  }

  elseif (in_array($mode, array('oneside'))) {
    // if not oversize - return default image
    if ((!$oversize && $w > $old_width) && (!$oversize && $h > $old_height))
      return realpath($path);
    $src_width = $old_width;
    $src_height = $old_height;
    if ($w) {
      $dst_width = $w;
      $dst_height = ceil($old_height * ($w / $old_width));
    } else {
      $dst_height = $h;
      $dst_width = ceil($old_width * ($h / $old_height));
    }

    $dst = $function_imagecreate($dst_width, $dst_height);
    $bg_color = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $dst_width, $dst_height, $bg_color);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $dst,
      $src,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $src_width,
      $src_height
    );
    $function_imagesave($dst, $new_path, 100);
    imagedestroy($src);
    imagedestroy($dst);
  }


  elseif (in_array($mode, array('cut'))) {
    $src_width = $old_width;
    $src_height = $old_height;
    if ($w >= $h) {
      $dst_width = $w;
      $dst_height = ceil($old_height * ($w / $old_width));
    } else {
      $dst_height = $h;
      $dst_width = ceil($old_width * ($h / $old_height));
    }

    // make predst
    $predst = $function_imagecreate($dst_width, $dst_height);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $predst,
      $src,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $src_width,
      $src_height
    );

    // make dst
    $dst_x = 0;
    $dst_y = ceil(($h / 2) * (-1));
    $dst = $function_imagecreate($w, $h);
    $bg_color = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $w, $h, $bg_color);
    @imagecopyresampled(
      $dst,
      $predst,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $dst_width,
      $dst_height
    );
    $function_imagesave($dst, $new_path, 100);
    imagedestroy($src);
    imagedestroy($predst);
    imagedestroy($dst);
  }


  elseif (in_array($mode, array('cover'))) {
    $src_width = $old_width;
    $src_height = $old_height;
    if ($w <= $h) {
      $dst_width = $w;
      $dst_height = ceil($old_height * ($w / $old_width));
    } else {
      $dst_height = $h;
      $dst_width = ceil($old_width * ($h / $old_height));
    }

    // make predst
    $predst = $function_imagecreate($dst_width, $dst_height);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $predst,
      $src,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $src_width,
      $src_height
    );

    // make dst
    $dst_x = ceil(($w / 2) - ($dst_width / 2));
    $dst_y = 0;
    $dst = $function_imagecreate($w, $h);
    $bg_color = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $w, $h, $bg_color);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $dst,
      $predst,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $dst_width,
      $dst_height,
      $dst_width,
      $dst_height
    );
    $function_imagesave($dst, $new_path, 100);
    imagedestroy($src);
    imagedestroy($predst);
    imagedestroy($dst);
  }


  elseif (in_array($mode, array('hard'))) {
    // if not oversize - use old image sizes
    if (!$oversize) {
      if (($w > $old_width) || ($h > $old_height)) {
        $w = $old_width;
        $h = $old_height;
      }
    }
    $src_width = $old_width;
    $src_height = $old_height;

    $dst = $function_imagecreate($w, $h);
    $bg_color = imagecolorallocate($dst, 255, 255, 255);
    imagefilledrectangle($dst, 0, 0, $w, $h, $bg_color);
    $src = $function_imagecopy($path);
    @imagecopyresampled(
      $dst,
      $src,
      $dst_x,
      $dst_y,
      $src_x,
      $src_y,
      $w,
      $h,
      $src_width,
      $src_height
    );
    $function_imagesave($dst, $new_path, 100);
    imagedestroy($src);
    imagedestroy($dst);
  }
  else
    exit('[resize_image] Unknown $mode - ' . $mode);

  // remove exif data
  if (class_exists('Imagick')) {
    $imagick = new Imagick($new_path);
    $imagick->setCompression(imagick::COMPRESSION_JPEG);
    $imagick->setCompressionQuality(100);
    $imagick->stripImage();
    $imagick->writeImage($new_path);
  }

  return realpath($new_path);
}
