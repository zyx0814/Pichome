<?php

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

function build_cache_setting() {
	global $_G;

	$skipkeys = array('backupdir','custombackup');
	$serialized = array('verify','unRunExts','iconview','storage','reginput', 'memory',   'secqaa', 'sitemessage','disallowfloat', 
		'seccodedata', 'strongpw','upgrade','desktop_default','loginset','at_range','thumbsize',);

	$data = array();

	foreach(C::t('setting')->fetch_all_not_key($skipkeys) as $setting) {
		if($setting['skey'] == 'attachdir') {
			$setting['svalue'] = preg_replace("/\.asp|\\0/i", '0', $setting['svalue']);
			$setting['svalue'] = str_replace('\\', '/', substr($setting['svalue'], 0, 2) == './' ? DZZ_ROOT.$setting['svalue'] : $setting['svalue']);
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
		} elseif($setting['skey'] == 'attachurl') {
			$setting['svalue'] .= substr($setting['svalue'], -1, 1) != '/' ? '/' : '';
	
		} elseif(in_array($setting['skey'], $serialized) || is_serialized($setting['svalue'])) {
			$setting['svalue'] = @dunserialize($setting['svalue']);
			if($setting['skey'] == 'search') {
				foreach($setting['svalue'] as $key => $val) {
					foreach($val as $k => $v) {
						$setting['svalue'][$key][$k] = max(0, intval($v));
					}
				}
			}
		}
		$_G['setting'][$setting['skey']] = $data[$setting['skey']] = $setting['svalue'];
	}

	include_once DZZ_ROOT.'./core/core_version.php';
	$_G['setting']['version'] = $data['version'] = CORE_VERSION;

	$data['sitemessage']['time'] = !empty($data['sitemessage']['time']) ? $data['sitemessage']['time'] * 1000 : 0;
	
	$data['disallowfloat'] = is_array($data['disallowfloat']) ? implode('|', $data['disallowfloat']) : '';

	if(!$data['imagelib']) unset($data['imageimpath']);
	
	//$data['iconview']=C::t('iconview')->fetch_all();
	

	$data['seccodedata'] = is_array($data['seccodedata']) ? $data['seccodedata'] : array();
	if($data['seccodedata']['type'] == 2) {
		if(extension_loaded('ming')) {
			unset($data['seccodedata']['background'], $data['seccodedata']['adulterate'],
			$data['seccodedata']['ttf'], $data['seccodedata']['angle'],
			$data['seccodedata']['color'], $data['seccodedata']['size'],
			$data['seccodedata']['animator']);
		} else {
			$data['seccodedata']['animator'] = 0;
		}
	} elseif($data['seccodedata']['type'] == 99) {
		$data['seccodedata']['width'] = 50;
		$data['seccodedata']['height'] = 34;
	}

	$data['watermarktext'] = !empty($data['watermarktext']) ? ($data['watermarktext']) : array();
	if($data['watermarktype'] == 'text' && $data['watermarktext']['text']) {
		if($data['watermarktext']['text'] && strtoupper(CHARSET) != 'UTF-8') {
			$data['watermarktext']['text'] = diconv($data['watermarktext']['text'], CHARSET, 'UTF-8', true);
		}
		$data['watermarktext']['textfull'] = $data['watermarktext']['text'];
		$data['watermarktext']['text'] = bin2hex($data['watermarktext']['text']);
		if(file_exists('static/image/seccode/font/en/'.$data['watermarktext']['fontpath'])) {
			$data['watermarktext']['fontpath'] = 'static/image/seccode/font/en/'.$data['watermarktext']['fontpath'];
		} elseif(file_exists('static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'])) {
			$data['watermarktext']['fontpath'] = 'static/image/seccode/font/ch/'.$data['watermarktext']['fontpath'];
		} else {
			$data['watermarktext']['fontpath'] = 'static/image/seccode/font/'.$data['watermarktext']['fontpath'];
		}
		$data['watermarktext']['color'] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', function($matches) { 
			$alpha=hexdec($matches[4]);
			if($alpha<0 || $alpha>127) $alpha=0;
			return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]).','.$alpha; }, $data['watermarktext']['color']);
		$data['watermarktext']['shadowcolor'] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', function($matches) { 
			$alpha=hexdec($matches[4]);
			if($alpha<0 || $alpha>127) $alpha=0;
			return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]).','.$alpha; }, $data['watermarktext']['shadowcolor']);
		
	     $data['watermarktext']['icolor'] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})/', function($matches) { 
			$alpha=hexdec($matches[4]);
			if($alpha<0 || $alpha>100) $alpha = 1;
			 else $alpha = $alpha/100;
			return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]).','.$alpha; }, $data['watermarktext']['icolor']);
		$data['watermarktext']['shadowicolor'] = preg_replace_callback('/#?([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9]{2})([0-9a-fA-F]{2})/', function($matches) { 
			$alpha=hexdec($matches[4]);
			if($alpha<0 || $alpha>100) $alpha=1;
			 else $alpha=$alpha/100;
			return hexdec($matches[1]).','.hexdec($matches[2]).','.hexdec($matches[3]).','.$alpha; }, $data['watermarktext']['shadowicolor']);

	} else {
		$data['watermarktext']['text'] = '';
		$data['watermarktext']['fontpath'] = '';
		$data['watermarktext']['color'] = '';
		$data['watermarktext']['shadowcolor'] = '';
	}
	if(!$data['jspath']) {
		$data['jspath'] = 'static/js/';
	}

	
	$reginputbwords = array('username', 'password', 'password2', 'email');
	if(in_array($data['reginput']['username'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['username'])) {
		$data['reginput']['username'] = random(6);
	}
	if(in_array($data['reginput']['password'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password'])) {
		$data['reginput']['password'] = random(6);
	}
	if(in_array($data['reginput']['password2'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['password2'])) {
		$data['reginput']['password2'] = random(6);
	}
	if(in_array($data['reginput']['email'], $reginputbwords) || !preg_match('/^[A-z]\w+?$/', $data['reginput']['email'])) {
		$data['reginput']['email'] = random(6);
	}

	$data['verhash']=random(3);
	
	$data['output'] = $output;
	
	savecache('setting', $data);
	$_G['setting'] = $data;
}

function parsehighlight($highlight) {
	if($highlight) {
		$colorarray = array('', 'red', 'orange', 'yellow', 'green', 'cyan', 'blue', 'purple', 'gray');
		$string = sprintf('%02d', $highlight);
		$stylestr = sprintf('%03b', $string[0]);

		$style = ' style="';
		$style .= $stylestr[0] ? 'font-weight: bold;' : '';
		$style .= $stylestr[1] ? 'font-style: italic;' : '';
		$style .= $stylestr[2] ? 'text-decoration: underline;' : '';
		$style .= $string[1] ? 'color: '.$colorarray[$string[1]] : '';
		$style .= '"';
	} else {
		$style = '';
	}
	return $style;
}

?>
