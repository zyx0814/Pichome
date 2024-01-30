<?php
/**
 * Created by PhpStorm.
 * User: a
 * Date: 2017/12/26
 * Time: 11:38
 */
if (!defined('IN_OAOOA') || !defined('IN_ADMIN')) {
    exit('Access Denied');
}

	
	if(!$_G['setting']['IsWatermarkstatus']) {
		exit(lang('watermark_not_enabled'));
	}
	require_once libfile('class/image');
	@unlink(DZZ_ROOT.'./data/attachment/cache/watermark_temp3.jpg');
	$image = new image;
	if(!($r = $image->Watermark(DZZ_ROOT.'./static/image/common/watermarkpreview.jpg', 'cache/watermark_temp3.jpg'))) {
		$r = $image->error();
	}
	if($r > 0) {

		$sizesource = filesize('static/image/common/watermarkpreview.jpg');
		$sizetarget = $image->imginfo['size'];
		echo '<img src="/data/attachment/cache/watermark_temp3.jpg?'.random(5).'"><br /><br /> '.lang('original_size').number_format($sizesource).' Bytes &nbsp;&nbsp; '.lang('image_processing').number_format($sizetarget).' Bytes ('.
			(sprintf("%2.1f", $sizetarget / $sizesource * 100)).'%)';
		exit();
	} else {
		exit(lang('generation_parameters'));
	}
