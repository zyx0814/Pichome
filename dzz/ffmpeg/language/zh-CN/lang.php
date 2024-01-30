<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      qchlian(3580164@qq.com)
 */

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

$lang = array
(
	'appname'=>'视频中心',
	'exts'=>'转码的文件类型',
	'exts_thumb'=>'生成缩略图支持的文件类型',
	'exts_tip'=>'转码的文件类型,使用英文逗号隔开，如：avi,rm,rmvb,mov,mkv',
	'exts_thumb_tip'=>'生成缩略图支持的文件类型,使用英文逗号隔开，如：avi,rm,rmvb,mov,mkv',
	'exts_info'=>'视频信息获取支持的文件类型',
	'exts_info_tip'=>'视频信息获取支持的文件类型,使用英文逗号隔开，如：avi,rm,rmvb,mov,mkv',
	'info_in_form'=>'信息关联字段',
	'info_in_form_select'=>'选择关联字段',
	'info_in_form_tip'=>'信息关联表单,获取到的信息数据会写入关联字段',
	'your_file_extracted'=>'视频转码中，请稍侯...',
	'ffmpeg_setting'=>'ffmpeg设置',
	'ffmpeg.binaries'=>'ffmpeg.binaries地址',
	'ffmpeg.binaries.tips'=>'填写ffmpeg.binaries地址,留空自动获取',
	'ffprobe.binaries'=>'ffprobe.binaries地址',
	'ffprobe.binaries.tips'=>'填写ffprobe.binaries地址,留空自动获取',
	'ffmpeg.timeout'=>'超时时间',
	'ffmpeg.timeout.tips'=>'超时时间，留空默认3600s',
	'ffmpeg.threads'=>'视频转码线程数',
	'ffmpeg.threads.tips'=>'视频转码线程数,建议按服务器线程数设置，以便充分利用cpu，留空默认0不转码',
	'width'=>'宽度',
	'height'=>'高度',
	'avg_frame_rate'=>'平均帧率',
	'bit_rate'=>'比特率',
	'duration'=>'时长',
	'format_name'=>'编码格式',
	'yinpin_bitelv'=>'音频比特率',
	'yinpin_shichang'=>'音频时长',
	'yinpin_caiyanglv'=>'音频采样率',
	'yinpin_dongdao'=>'声道',
	'yinpin_yishujia'=>'艺术家',
	'yinpin_changpian'=>'唱片',
	'yinpin_fxrq'=>'发行日期',
	'yinpin_decription'=>'音频描述',
	'convert_immediately'=>'转码时间',
	'convert_immediately_0'=>'通过计划任务执行转码',
	'convert_immediately_1'=>'用户上传完成立即开始转码',
	'convert_immediately_2'=>'不转码',
	'convert_immediately_tip'=>'根据使用情况选择，上传完立即开始转码，转码比较及时。但是转码时服务器资源消耗较大，可能不利于用户访问体验；通过计划任务转码，可以设定服务器空闲时间执行转码任务；设置为不转码时：不会转码视频，即使用户点击播放时也不会开启转码',
	'your_file_extracted'=>'正在转码中，请稍候...'
	
);
?>
