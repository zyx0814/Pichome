<?php

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}
if(function_exists('ini_get')) {
	$memorylimit = @ini_get('memory_limit');
	if($memorylimit && return_bytes($memorylimit) < 536870912 && function_exists('ini_set')) {
		ini_set('memory_limit', '2048m');
	}
}

class image {

	var $source = '';
	var $target = '';
	var $imginfo = array();
	var $imagecreatefromfunc = '';
	var $imagefunc = '';
	var $tmpfile = '';
	var $libmethod = 0;
	var $param = array();
	var $errorcode = 0;

	var $extension = array();

	function image($params=array()) {
		global $_G;

		$this->extension['gd'] = extension_loaded('gd');
		$this->extension['imagick'] = extension_loaded('imagick');
		
		$this->param = array(
				'imagelib'		=> isset($_G['setting']['imagelib'])?$_G['setting']['imagelib']:1,
				'imageimpath'		=> $_G['setting']['imageimpath'],
				'thumbquality'		=> $_G['setting']['thumbquality']?$_G['setting']['thumbquality']:80,
				'watermarkstatus'	=> $_G['setting']['watermarkstatus'],
				'watermarkminwidth'	=> $_G['setting']['watermarkminwidth'],
				'watermarkminheight'=> $_G['setting']['watermarkminheight'],
				'watermarktype'		=> $_G['setting']['watermarktype'],
				'watermarktext'		=> $_G['setting']['watermarktext'],
				'watermarktrans'	=> $_G['setting']['watermarktrans'],
				'watermarkquality'	=> $_G['setting']['watermarkquality'],
		);
		if($params){
			$this->param=array_merge($this->param,$params);
		}
	}


	function Thumb($source, $target, $thumbwidth, $thumbheight, $thumbtype = 1, $nosuffix = 0) {
		$return = $this->init('thumb', $source, $target,$nosuffix);
		if($return <= 0) {
			return $this->returncode($return);
		}

		if($this->imginfo['animated']) {
			return $this->returncode(0);
		}
		$this->param['thumbwidth'] = intval($thumbwidth);
		if(!$thumbheight || $thumbheight > $this->imginfo['height']) {
			$thumbheight = $thumbwidth > $this->imginfo['width'] ? $this->imginfo['height'] : $this->imginfo['height']*($thumbwidth/$this->imginfo['width']);
		}
		$this->param['thumbheight'] = intval($thumbheight);
		$this->param['thumbtype'] = $thumbtype;
		if($thumbwidth < 100 && $thumbheight < 100) {
			$this->param['thumbquality'] = 100;
		}

		$return = !$this->libmethod ? $this->Thumb_GD() : $this->Thumb_IM();
		$return = !$nosuffix ? $return : 0;

		return $this->sleep($return);
	}

	function Cropper($source, $target, $dstwidth, $dstheight, $srcx = 0, $srcy = 0, $srcwidth = 0, $srcheight = 0) {

		$return = $this->init('thumb', $source, $target, '',1);
		if($return <= 0) {
			return $this->returncode($return);
		}
		if($dstwidth < 0 || $dstheight < 0) {
			return $this->returncode(false);
		}
		$this->param['dstwidth'] = intval($dstwidth);
		$this->param['dstheight'] = intval($dstheight);
		$this->param['srcx'] = intval($srcx);
		$this->param['srcy'] = intval($srcy);
		$this->param['srcwidth'] = intval($srcwidth ? $srcwidth : $dstwidth);
		$this->param['srcheight'] = intval($srcheight ? $srcheight : $dstheight);

		$return = !$this->libmethod ? $this->Cropper_GD() : $this->Cropper_IM();
		return $this->sleep($return);
	}

	function Watermark($source,$target='') {
		$return = $this->init('watermask', $source, $target,0);
		if($return <= 0) {
			return $this->returncode($return);
		}

		if(!$this->param['watermarkstatus'] || ($this->param['watermarkminwidth'] && $this->imginfo['width'] <= $this->param['watermarkminwidth'] && $this->param['watermarkminheight'] && $this->imginfo['height'] <= $this->param['watermarkminheight'])) {
			return $this->returncode(0);
		}
        $this->param['watermarkfile'] = getglobal('setting/waterimg');
		if(!is_readable($this->param['watermarkfile']) || ($this->param['watermarktype'] == 'text' && (!file_exists($this->param['watermarktext']['fontpath']) || !is_file($this->param['watermarktext']['fontpath'])))) {
			return $this->returncode(-3);
		}

		$return = !$this->libmethod ? $this->Watermark_GD() : $this->Watermark_IM();

		return $this->sleep($return);
	}

	function error() {
		return $this->errorcode;
	}

	function init($method, $source, $target,$nosuffix = 0) {
		global $_G;
		$this->errorcode = 0;
		if(empty($source)) {
			return -2;
		}
		$parse = parse_url($source);
		if(isset($parse['host'])) {
			if(empty($target)) {
				return -2;
			}
			$data = dfsockopen($source);
			$this->tmpfile = $source = tempnam($_G['setting']['attachdir'].'./cache/', 'tmpimg_');
			if(!$data || $source === FALSE) {
				return -2;
			}
			file_put_contents($source, $data);
		}
		if($method == 'thumb') {
			$target = empty($target) ? (!$nosuffix ? getimgthumbname($source) : $source) : ((strpos($target,DZZ_ROOT)!==false)?$target:$_G['setting']['attachdir'].'./'.$target);
		} elseif($method == 'watermask') {
			$target = empty($target) ?  $source : $_G['setting']['attachdir'].'./'.$target;
		}
		$targetpath = dirname($target);
		dmkdir($targetpath);

		clearstatcache();
		if(!is_readable($source) || !is_writable($targetpath)) {
			return -2;
		}

		$imginfo = @getimagesize($source);
		
		if($imginfo === FALSE) {
			return -1;
		}

		$this->source = $source;
		$this->target = $target;
        $this->imginfo['width'] = $imginfo[0];
		$this->imginfo['height'] = $imginfo[1];
		$this->imginfo['mime'] = $imginfo['mime'];
		$this->imginfo['size'] = @filesize($source);
		$this->libmethod = $this->param['imagelib'];
		if($this->param['imagelib'] && $this->extension['imagick']) {
			$this->libmethod = 1;
		}elseif($this->extension['gd']){
			$this->libmethod = 0;
		} else {
			return -4;
		}

		if(!$this->libmethod) {
			switch($this->imginfo['mime']) {
				case 'image/jpeg':
					$this->imagecreatefromfunc = function_exists('imagecreatefromjpeg') ? 'imagecreatefromjpeg' : '';
					$this->imagefunc = function_exists('imagejpeg') ? 'imagejpeg' : '';
					break;
				case 'image/gif':
					$this->imagecreatefromfunc = function_exists('imagecreatefromgif') ? 'imagecreatefromgif' : '';
					$this->imagefunc = function_exists('imagegif') ? 'imagegif' : '';
					break;
				case 'image/png':
					$this->imagecreatefromfunc = function_exists('imagecreatefrompng') ? 'imagecreatefrompng' : '';
					$this->imagefunc = function_exists('imagepng') ? 'imagepng' : '';
					break;
			}
		} else {
			$this->imagecreatefromfunc = $this->imagefunc = TRUE;
		}

		if(!$this->libmethod && $this->imginfo['mime'] == 'image/gif') {
			if(!$this->imagecreatefromfunc) {
				return -4;
			}
			if(!($fp = @fopen($source, 'rb'))) {
				return -2;
			}
			$content = fread($fp, $this->imginfo['size']);
			fclose($fp);
			$this->imginfo['animated'] = strpos($content, 'NETSCAPE2.0') === FALSE ? 0 : 1;
		}

		return $this->imagecreatefromfunc ? 1 : -4;
	}

	function sleep($return) {
		if($this->tmpfile) {
			@unlink($this->tmpfile);
		}
		$this->imginfo['size'] = @filesize($this->target);
		return $this->returncode($return);
	}

	function returncode($return) {
		if($return > 0 && file_exists($this->target)) {
			return true;
		} else {
			if($this->tmpfile) {
				@unlink($this->tmpfile);
			}
			$this->errorcode = $return;
			return false;
		}
	}

	function sizevalue($method) {
		$x = $y = $w = $h = 0;
		if($method > 0) {
			$imgratio = $this->imginfo['width'] / $this->imginfo['height'];
			$thumbratio = $this->param['thumbwidth'] / $this->param['thumbheight'];
			if($imgratio >= 1 && $imgratio >= $thumbratio || $imgratio < 1 && $imgratio > $thumbratio) {
				$h = $this->imginfo['height'];
				$w = $h * $thumbratio;
				$x = ($this->imginfo['width'] - $thumbratio * $this->imginfo['height']) / 2;
			} elseif($imgratio >= 1 && $imgratio <= $thumbratio || $imgratio < 1 && $imgratio <= $thumbratio) {
				$w = $this->imginfo['width'];
				$h = $w / $thumbratio;
			}
		} else {
			$x_ratio = $this->param['thumbwidth'] / $this->imginfo['width'];
			$y_ratio = $this->param['thumbheight'] / $this->imginfo['height'];
			if(($x_ratio * $this->imginfo['height']) < $this->param['thumbheight']) {
				$h = ceil($x_ratio * $this->imginfo['height']);
				$w = $this->param['thumbwidth'];
			} else {
				$w = ceil($y_ratio * $this->imginfo['width']);
				$h = $this->param['thumbheight'];
			}
			if($w < 242){
			    $w = $this->param['thumbwidth'] = 242;
                $x_ratio = $this->param['thumbwidth'] / $this->imginfo['width'];
			    $h = ceil($x_ratio*$this->imginfo['height']);
            }
		}
		return array($x, $y, $w, $h);
	}

	function loadsource() {
		$imagecreatefromfunc = &$this->imagecreatefromfunc;
		$im = @$imagecreatefromfunc($this->source);
		if(!$im) {
			if(!function_exists('imagecreatefromstring')) {
				return -4;
			}
			$fp = @fopen($this->source, 'rb');
			$contents = @fread($fp, filesize($this->source));
			fclose($fp);
			$im = @imagecreatefromstring($contents);
			if($im == FALSE) {
				return -1;
			}
		}
		return $im;
	}

	function Thumb_GD() {
		if(!function_exists('imagecreatetruecolor') || !function_exists('imagecopyresampled') || !function_exists('imagejpeg') || !function_exists('imagecopymerge')) {
			return -4;
		}

		$imagefunc = &$this->imagefunc;
		$attach_photo = $this->loadsource();
		if($attach_photo < 0) {
			return $attach_photo;
		}
		//@ini_set('memory_limit','512M');
		$copy_photo = imagecreatetruecolor($this->imginfo['width'], $this->imginfo['height']);
		if($this->imginfo['mime']=='image/png'){
			imagealphablending($copy_photo,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
        	imagesavealpha($copy_photo,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
		}
		imagecopy($copy_photo, $attach_photo ,0, 0, 0, 0, $this->imginfo['width'], $this->imginfo['height']);
		$attach_photo = $copy_photo;

		$thumb_photo = null;
		switch($this->param['thumbtype']) {
			case 'fixnone':
			case 1:
				if($this->imginfo['width'] >= $this->param['thumbwidth'] || $this->imginfo['height'] >= $this->param['thumbheight']) {
					$thumb = array();
					list(,,$thumb['width'], $thumb['height']) = $this->sizevalue(0);
					$cx = $this->imginfo['width'];
					$cy = $this->imginfo['height'];
					$thumb_photo = imagecreatetruecolor($thumb['width'], $thumb['height']);
					if($this->imginfo['mime']=='image/png'){
						imagealphablending($thumb_photo,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
						imagesavealpha($thumb_photo,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
					}
					imagecopyresampled($thumb_photo, $attach_photo ,0, 0, 0, 0, $thumb['width'], $thumb['height'], $cx, $cy);
				}
				break;
			case 'fixwr':
			case 2:
				if(!($this->imginfo['width'] <= $this->param['thumbwidth'] || $this->imginfo['height'] <= $this->param['thumbheight'])) {
					list($startx, $starty, $cutw, $cuth) = $this->sizevalue(1);
					$dst_photo = imagecreatetruecolor($cutw, $cuth);
					if($this->imginfo['mime']=='image/png'){
						imagealphablending($$dst_photo,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
						imagesavealpha($$dst_photo,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
					}
					imagecopymerge($dst_photo, $attach_photo, 0, 0, $startx, $starty, $cutw, $cuth, 100);
					$thumb_photo = imagecreatetruecolor($this->param['thumbwidth'], $this->param['thumbheight']);
					if($this->imginfo['mime']=='image/png'){
						imagealphablending($thumb_photo,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
						imagesavealpha($thumb_photo,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
					}
					imagecopyresampled($thumb_photo, $dst_photo ,0, 0, 0, 0, $this->param['thumbwidth'], $this->param['thumbheight'], $cutw, $cuth);
				} else {
					$thumb_photo = imagecreatetruecolor($this->param['thumbwidth'], $this->param['thumbheight']);
					if($this->imginfo['mime']=='image/png'){
						imagealphablending($thumb_photo,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
						imagesavealpha($thumb_photo,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
					}
					$bgcolor = imagecolorallocate($thumb_photo, 255, 255, 255);
					imagefill($thumb_photo, 0, 0, $bgcolor);
					$startx = ($this->param['thumbwidth'] - $this->imginfo['width']) / 2;
					$starty = ($this->param['thumbheight'] - $this->imginfo['height']) / 2;
					imagecopymerge($thumb_photo, $attach_photo, $startx, $starty, 0, 0, $this->imginfo['width'], $this->imginfo['height'], 100);
				}
				break;
		}
		clearstatcache();
		if($thumb_photo) {
			if($this->imginfo['mime'] == 'image/jpeg') {
				@$imagefunc($thumb_photo, $this->target, $this->param['thumbquality']);
			} else {
				@$imagefunc($thumb_photo, $this->target);
			}
			return 1;
		} else {
			return 0;
		}
	}
	function scaleImage($width,$height,$owidth,$oheight) {
		if($owidth>$width || $oheight>$height){
			$or=$owidth/$oheight;
			$r=$width/$height;
			if($r>$or){
				if($oheight<$height){
					$height=$oheight;
					$width=$owidth;
				}else{

					$width=ceil($height*$or);
					if($width < 242){
					    $width = 242;
					    $height = ceil($width/$or);
                    }
				}

			}else{
				if($owidth<$width){
					$height=$oheight;
					$width=$owidth;
				}else{
					$height=ceil($width/$or);
					$width = ceil($height*$or);
					if($width < 242){
                        $width = 242;
                        $height = ceil($width/$or);
                    }
				}
			}

		}else{
			$width=$owidth;
			$height=$oheight;
		}
		//Return the results
		return array($width,$height);
	}
	function Thumb_IM() {

		switch($this->param['thumbtype']) {
			case 'fixnone':
			case 1:
				if($this->imginfo['width'] >= $this->param['thumbwidth'] || $this->imginfo['height'] >= $this->param['thumbheight']) {
				    $im = new Imagick(realpath($this->source));
					$this->autoRotateImage($im);
					$im->stripImage(); //去除图片信息
					$im->setIteratorIndex(0);
					$newsize=$this->scaleImage($this->param['thumbwidth'], $this->param['thumbheight'],$im->getImageWidth(),$im->getImageHeight());
					$im->thumbnailImage($newsize[0], $newsize[1]);
					if($this->imginfo['mime'] == 'image/png') {
						$prefix='png:';
					}elseif($this->imginfo['mime'] == 'image/gif') {
						$prefix='png:';
					}else{
						$prefix='';
						$im->setImageCompressionQuality($this->param['thumbquality']);
					}
					if(!$im->writeImage($prefix.$this->target)) {
						$im->destroy();
						return -3;
					}
					$im->destroy();
				
				}
				break;
			case 'fixwr':
			case 2:
				if(!($this->imginfo['width'] <= $this->param['thumbwidth'] || $this->imginfo['height'] <= $this->param['thumbheight'])) {
					
					list($startx, $starty, $cutw, $cuth) = $this->sizevalue(1);
					
					$im = new Imagick();
					$im->readImage(realpath($this->source));
					$this->autoRotateImage($im);
					$im->stripImage(); //去除图片信息
					$im->setIteratorIndex(0);
					$im->cropImage($cutw, $cuth, $startx, $starty);
					if($this->imginfo['mime'] == 'image/png') {
						$prefix='png:';
					}elseif($this->imginfo['mime'] == 'image/gif') {
						$prefix='png:';
					}else{
						$prefix='';
						$im->setImageCompressionQuality($this->param['thumbquality']);
					}
					
					if(!$im->writeImage($prefix.$this->target)) {
						$im->destroy();
						return -3;
					}

					$im->readImage(realpath($this->target));
				
					$im->thumbnailImage($this->param['thumbwidth'], $this->param['thumbheight']);
					$im->resizeImage($this->param['thumbwidth'], $this->param['thumbheight']);
					$im->setGravity(imagick::GRAVITY_CENTER );
					$im->extentImage($this->param['thumbwidth'], $this->param['thumbheight']);
					if($this->imginfo['mime'] == 'image/png') {
						$prefix='png:';
					}elseif($this->imginfo['mime'] == 'image/gif') {
						$prefix='png:';
					}else{
						$prefix='';
						$im->setImageCompressionQuality($this->param['thumbquality']);
					}
					
					if(!$im->writeImage($prefix.$this->target)) {
						$im->destroy();
						return -3;
					}
					$im->destroy();
				} else {
					$startx = -($this->param['thumbwidth'] - $this->imginfo['width']) / 2;
					$starty = -($this->param['thumbheight'] - $this->imginfo['height']) / 2;

					$im = new Imagick();
					$im->readImage(realpath($this->source));
					$this->autoRotateImage($im);
					$im->stripImage(); //去除图片信息
					$im->setIteratorIndex(0);
					$im->cropImage($this->param['thumbwidth'], $this->param['thumbheight'], $startx, $starty);
					if($this->imginfo['mime'] == 'image/png') {
						$prefix='png:';
					}elseif($this->imginfo['mime'] == 'image/gif') {
						$prefix='png:';
					}else{
						$prefix='';
						$im->setImageCompressionQuality($this->param['thumbquality']);
					}
					if(!$im->writeImage($prefix.$this->target)) {
						$im->destroy();
						return -3;
					}

					$im->readImage(realpath($this->target));
					
					$im->thumbnailImage($this->param['thumbwidth'], $this->param['thumbheight']);
					$im->setGravity(imagick::GRAVITY_CENTER );
					$im->extentImage($this->param['thumbwidth'], $this->param['thumbheight']);
					if($this->imginfo['mime'] == 'image/png') {
						$prefix='png:';
					}elseif($this->imginfo['mime'] == 'image/gif') {
						$prefix='gif:';
					}else{
						$prefix='';
						$im->setImageCompressionQuality($this->param['thumbquality']);
					}
					if(!$im->writeImage($prefix.$this->target)) {
						$im->destroy();
						return -3;
					}
					$im->destroy();
				}
				break;
		}
		return 1;
	}
	function autoRotateImage($image) {
		$orientation = $image->getImageOrientation();
		switch($orientation) {
			case imagick::ORIENTATION_BOTTOMRIGHT: 
				$image->rotateimage("#000", 180); // rotate 180 degrees
				break;

			case imagick::ORIENTATION_RIGHTTOP:
				$image->rotateimage("#000", 90); // rotate 90 degrees CW
				break;

			case imagick::ORIENTATION_LEFTBOTTOM: 
				$image->rotateimage("#000", -90); // rotate 90 degrees CCW
				break;
		}

		// Now that it's auto-rotated, make sure the EXIF data is correct in case the EXIF gets saved with the image!
		$image->setImageOrientation(imagick::ORIENTATION_TOPLEFT);
	}
	function Cropper_GD() {
		$image = $this->loadsource();
		if($image < 0) {
			return $image;
		}
		$newimage = imagecreatetruecolor($this->param['dstwidth'], $this->param['dstheight']);
		if($this->imginfo['mime']=='image/png'){
			imagealphablending($newimage,false);//这里很重要,意思是不合并颜色,直接用$img图像颜色替换,包括透明色;
			imagesavealpha($newimage,true);//这里很重要,意思是不要丢了$thumb图像的透明色;
		}
		imagecopyresampled($newimage, $image, 0, 0, $this->param['srcx'], $this->param['srcy'], $this->param['dstwidth'], $this->param['dstheight'], $this->param['srcwidth'], $this->param['srcheight']);
		ImageJpeg($newimage, $this->target, 100);
		imagedestroy($newimage);
		imagedestroy($image);
		return true;
	}
	function Cropper_IM() {
		$im = new Imagick();
		$im->readImage(realpath($this->source));
		$im->cropImage($this->param['srcwidth'], $this->param['srcheight'], $this->param['srcx'], $this->param['srcy']);
		$im->thumbnailImage($this->param['dstwidth'], $this->param['dstheight']);
		if($this->imginfo['mime'] == 'image/png') {
			$prefix='png:';
		}else{
			$prefix='';
		}
		$result = $im->writeImage($prefix.$this->target);
		$im->destroy();
		if(!$result) {
			return -3;
		}
	}

	function Watermark_GD() {
		if(!function_exists('imagecreatetruecolor')) {
			return -4;
		}
		$imagefunc = &$this->imagefunc;

		if($this->param['watermarktype'] != 'text') {
			if(!function_exists('imagecopy') || !function_exists('imagecreatefrompng') || !function_exists('imagecreatefromgif') || !function_exists('imagealphablending') || !function_exists('imagecopymerge')) {
				return -4;
			}
			$watermarkinfo = @getimagesize($this->param['watermarkfile']);
			if($watermarkinfo === FALSE) {
				return -3;
			}
			$watermark_logo	= $this->param['watermarktype'] == 'png' ? @imageCreateFromPNG($this->param['watermarkfile']) : @imageCreateFromGIF($this->param['watermarkfile']);
			if(!$watermark_logo) {
				return 0;
			}
			list($logo_w, $logo_h) = $watermarkinfo;
		} else {
			if(!function_exists('imagettfbbox') || !function_exists('imagettftext') || !function_exists('imagecolorallocatealpha')) {
				return -4;
			}
			if(!class_exists('Chinese')) {
				include libfile('class/chinese');
			}
			$watermarktextcvt = pack("H*", $this->param['watermarktext']['text']);
			
            $box = imagettfbbox($this->param['watermarktext']['size'], $this->param['watermarktext']['angle'], DZZ_ROOT.$this->param['watermarktext']['fontpath'], $watermarktextcvt);
			$logo_h = max($box[1], $box[3]) - min($box[5], $box[7]);
			$logo_w = max($box[2], $box[4]) - min($box[0], $box[6]);
			$ax = min($box[0], $box[6]) * -1;
			$ay = min($box[5], $box[7]) * -1;
		}
		$wmwidth = $this->imginfo['width'] - $logo_w;
		$wmheight = $this->imginfo['height'] - $logo_h;

		if($wmwidth > 10 && $wmheight > 10 && !$this->imginfo['animated']) {
			switch($this->param['watermarkstatus']) {
				case 1:
					$x = 5;
					$y = 5;
					break;
				case 2:
					$x = ($this->imginfo['width'] - $logo_w) / 2;
					$y = 5;
					break;
				case 3:
					$x = $this->imginfo['width'] - $logo_w - 5;
					$y = 5;
					break;
				case 4:
					$x = 5;
					$y = ($this->imginfo['height'] - $logo_h) / 2;
					break;
				case 5:
					$x = ($this->imginfo['width'] - $logo_w) / 2;
					$y = ($this->imginfo['height'] - $logo_h) / 2;
					break;
				case 6:
					$x = $this->imginfo['width'] - $logo_w;
					$y = ($this->imginfo['height'] - $logo_h) / 2;
					break;
				case 7:
					$x = 5;
					$y = $this->imginfo['height'] - $logo_h - 5;
					break;
				case 8:
					$x = ($this->imginfo['width'] - $logo_w) / 2;
					$y = $this->imginfo['height'] - $logo_h - 5;
					break;
				case 9:
					$x = $this->imginfo['width'] - $logo_w - 5;
					$y = $this->imginfo['height'] - $logo_h - 5;
					break;
			}
			if($this->imginfo['mime'] != 'image/png') {
				$color_photo = imagecreatetruecolor($this->imginfo['width'], $this->imginfo['height']);
				imagealphablending($color_photo, true);
				imagesavealpha($color_photo, true);
			}
			$dst_photo = $this->loadsource();
			if($dst_photo < 0) {
				return $dst_photo;
			}
			
			if($this->imginfo['mime'] != 'image/png') {
				imageCopy($color_photo, $dst_photo, 0, 0, 0, 0, $this->imginfo['width'], $this->imginfo['height']);
				$dst_photo = $color_photo;
			}else{
				imagealphablending($dst_photo, true);
				imagesavealpha($dst_photo, true);
			}
			if($this->param['watermarktype'] == 'png') {
				imageCopy($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_w, $logo_h);
			} elseif($this->param['watermarktype'] == 'text') {
				if(($this->param['watermarktext']['shadowx'] || $this->param['watermarktext']['shadowy']) && $this->param['watermarktext']['shadowcolor']) {
					$shadowcolorrgb = explode(',', $this->param['watermarktext']['shadowcolor']);
					$shadowcolor = imagecolorallocatealpha($dst_photo, $shadowcolorrgb[0], $shadowcolorrgb[1], $shadowcolorrgb[2],$shadowcolorrgb[3]);
					imagettftext($dst_photo, $this->param['watermarktext']['size'], $this->param['watermarktext']['angle'], $x + $ax + $this->param['watermarktext']['shadowx'], $y + $ay + $this->param['watermarktext']['shadowy'], $shadowcolor, DZZ_ROOT.$this->param['watermarktext']['fontpath'], $watermarktextcvt);
				}

				$colorrgb = explode(',', $this->param['watermarktext']['color']);
				$color = imagecolorallocatealpha($dst_photo, $colorrgb[0], $colorrgb[1], $colorrgb[2], $colorrgb[3]);
				imagettftext($dst_photo, $this->param['watermarktext']['size'], $this->param['watermarktext']['angle'], $x + $ax, $y + $ay, $color, DZZ_ROOT.$this->param['watermarktext']['fontpath'], $watermarktextcvt);
			} else {
				imageAlphaBlending($watermark_logo, true);
				imageCopyMerge($dst_photo, $watermark_logo, $x, $y, 0, 0, $logo_w, $logo_h, $this->param['watermarktrans']);
			}

			clearstatcache();
			if($this->imginfo['mime'] == 'image/jpeg') {
				@$imagefunc($dst_photo, $this->target, $this->param['watermarkquality']);
			} else {
				@$imagefunc($dst_photo, $this->target);
			}
		}
		return 1;
	}

	function Watermark_IM() {
		//error_reporting(E_ALL);
		switch($this->param['watermarkstatus']) {
			case 1:
				$gravity = imagick::GRAVITY_NORTHWEST;
				break;
			case 2:
				$gravity = imagick::GRAVITY_NORTH;
				break;
			case 3:
				$gravity = imagick::GRAVITY_NORTHEAST;
				break;
			case 4:
				$gravity = imagick::GRAVITY_WEST;
				break;
			case 5:
				$gravity = imagick::GRAVITY_CENTER;
				break;
			case 6:
				$gravity = imagick::GRAVITY_EAST;
				break;
			case 7:
				$gravity = imagick::GRAVITY_SOUTHWEST;
				break;
			case 8:
				$gravity = imagick::GRAVITY_SOUTH;
				break;
			case 9:
				$gravity = imagick::GRAVITY_SOUTHEAST;
				break;
		}
		if($this->param['watermarktype'] != 'text') {
			
			$watermark = new Imagick(realpath($this->param['watermarkfile']));
			if($this->param['watermarktype'] != 'png' && $this->param['watermarktrans'] != '100') {
				if(method_exists($watermark,'setImageOpacity')) $watermark->setImageOpacity($this->param['watermarktrans']/100);
				elseif(method_exists($watermark,'setImageAlpha')) $watermark->setImageAlpha($this->param['watermarktrans']/100);
			}
			
			$info  = array($watermark->getImageWidth(), $watermark->getImageHeight());
			/* 设定水印位置 */
			switch ($this->param['watermarkstatus']) {
				/* 右下角水印 */
				case 9:
					$x = $this->imginfo['width'] - $info[0];
					$y = $this->imginfo['height'] - $info[1];
					break;
				/* 左下角水印 */
				case 7:
					$x = 0;
					$y = $this->imginfo['height'] - $info[1];
					break;
				/* 左上角水印 */
				case 1:
					$x = $y = 0;
					break;
				/* 右上角水印 */
				case 3:
					$x = $this->imginfo['width'] - $info[0];
					$y = 0;
					break;
				/* 居中水印 */
				case 5:
					$x = ($this->imginfo['width'] - $info[0]) / 2;
					$y = ($this->imginfo['height'] - $info[1]) / 2;
					break;
				/* 下居中水印 */
				case 8:
					$x = ($this->imginfo['width'] - $info[0]) / 2;
					$y = $this->imginfo['height'] - $info[1];
					break;
				/* 右居中水印 */
				case 6:
					$x = $this->imginfo['width'] - $info[0];
					$y = ($this->imginfo['height'] - $info[1]) / 2;
					break;
				/* 上居中水印 */
				case 2:
					$x = ($this->imginfo['width'] - $info[0]) / 2;
					$y = 0;
					break;
				/* 左居中水印 */
				case 4:
					$x = 0;
					$y = ($this->imginfo['height'] - $info[1]) / 2;
					break;
				default:
					return -3;
			}
			
			$canvas = new Imagick(realpath($this->source));
			
			 if ('image/gif' == $this->imginfo['mime']) {
				$img = $canvas->coalesceImages();
				$canvas->destroy(); //销毁原图
				do {
					//添加水印
					$img->compositeImage($watermark,Imagick::COMPOSITE_DEFAULT,$x,$y);
				} while ($img->nextImage());
				//压缩图片
				$canvas = $img->deconstructImages();
				$img->destroy(); //销毁临时图片
        	}else{
				$canvas->compositeImage($watermark,Imagick::COMPOSITE_DEFAULT,$x,$y); 
			}
			if($this->imginfo['mime'] == 'image/png') {
				$prefix='png:';
			}else{
				$prefix='';
			}
			$result = $canvas->writeImage($prefix.$this->target);
			$watermark->destroy();
			$canvas->destroy();
			if(!$result) {
				return -3;
			}
		} else {
			$watermarktextcvt = escapeshellcmd(pack("H*", $this->param['watermarktext']['text']));
			$angle = -$this->param['watermarktext']['angle'];
			$translate = $this->param['watermarktext']['translatex'] || $this->param['watermarktext']['translatey'] ? ' translate '.intval($this->param['watermarktext']['translatex']).','.intval($this->param['watermarktext']['translatey']) : '';
			$skewX = $this->param['watermarktext']['skewx'] ? ' skewX '.intval($this->param['watermarktext']['skewx']) : '';
			$skewY = $this->param['watermarktext']['skewy'] ? ' skewY '.intval($this->param['watermarktext']['skewy']) : '';

			$canvas = new Imagick(realpath($this->source));
			$canvas->setImageCompressionQuality($this->param['watermarkquality']);

			$dw = new ImagickDraw();
			$dw->setFont(DZZ_ROOT.$this->param['watermarktext']['fontpath']);
			$dw->setFontSize($this->param['watermarktext']['size']);
			
			if(($this->param['watermarktext']['shadowx'] || $this->param['watermarktext']['shadowy']) && $this->param['watermarktext']['shadowicolor']) {
				$dw->setFillColor(new ImagickPixel('rgba('.$this->param['watermarktext']['shadowicolor'].')'));
				$dw->setGravity($gravity);
				if($translate) {
					$dw->translate($this->param['watermarktext']['translatex'], $this->param['watermarktext']['translatey']);
				}
				if($skewX) {
					$dw->skewX($this->param['watermarktext']['skewx']);
				}
				if($skewY) {
					$dw->skewY($this->param['watermarktext']['skewy']);
				}
				$dw->annotation($this->param['watermarktext']['shadowx'], $this->param['watermarktext']['shadowy'], escapeshellcmd(pack("H*", $this->param['watermarktext']['text'])));
				$canvas->drawImage($dw);

			}
			$dw->setFillColor(new ImagickPixel('rgba('.$this->param['watermarktext']['icolor'].')'));
			$dw->setGravity($gravity);
			if($translate) {
				$dw->translate($this->param['watermarktext']['translatex'], $this->param['watermarktext']['translatey']);
			}
			if($skewX) {
				$dw->skewX($this->param['watermarktext']['skewx']);
			}
			if($skewY) {
				$dw->skewY($this->param['watermarktext']['skewy']);
			}
			$dw->rotate($angle);
			$dw->annotation(0, 0, escapeshellcmd(pack("H*", $this->param['watermarktext']['text'])));

			$canvas->drawImage($dw);
			//$result=file_put_contents($this->target,$canvas->getImageBlob());
			if($this->imginfo['mime'] == 'image/png') {
				$prefix='png:';
			}else{
				$prefix='';
			}
			$result = $canvas->writeImage($prefix.$this->target);
			$canvas->destroy();
			$dw->destroy();
			if(!$result) {
				return -3;
			}
		}
		return 1;
	}

	function IM_filter($str) {
		return escapeshellarg(str_replace(' ', '', $str));
	}

}
?>