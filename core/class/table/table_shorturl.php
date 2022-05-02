<?php
/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 */

if(!defined('IN_OAOOA')) {
	exit('Access Denied');
}

class table_shorturl extends dzz_table
{
	public function __construct() {

		$this->_table = 'shorturl';
		$this->_pk    = 'sid';
		$this->_pre_cache_key = 'shorturl_';
		$this->_cache_ttl =0;
		parent::__construct();
	}
	
	private function code62($x) {
		$show = '';
		while($x > 0) {
		  $s = $x % 62;
		  if ($s > 35) {
			$s = chr($s+61);
		  } elseif ($s > 9 && $s <=35) {
			$s = chr($s + 55);
		  }
		  $show .= $s;
		  $x = floor($x/62);
		}
		return $show;
	}
	public function getSid($url) {
		   $url = crc32($url);
		   $result = sprintf("%u", $url);
		   return self::code62($result);
	}
	
	public function getShortUrl($url){
		$sid=self::getSid($url);
		if(DB::result_first("select COUNT(*) from %t where sid=%s",array($this->_table,$sid))){
			return getglobal('siteurl').'short.php?sid='.$sid;
		}
		$setarr=array('sid'=>$sid,
					  'url'=>$url,
					  );
		if(parent::insert($setarr)){
			return getglobal('siteurl').'short.php?sid='.$sid;
		}
		return '';
	}
	public function getQrcodeUrl($url){
		$sid=self::getSid($url);
		return self::getQRcodeBySid($sid);
	}
	public function addview($sid){
		return DB::query("update %t set count=count+1 where sid=%s",array($this->_table,$sid));
	}
	public function delete_by_url($url){
        $sid=self::getSid($url);
		return parent::delete($sid);
	}
	 public function getQRcodeBySid($sid){
        $target='./qrcode/'.$sid[0].'/'.$sid.'.png';
        $targetpath = dirname(getglobal('setting/attachdir').$target);
        dmkdir($targetpath);
        if(@getimagesize(getglobal('setting/attachdir').$target)){
            return getglobal('setting/attachurl').$target;
        }else{//生成二维码
            QRcode::png((getglobal('siteurl').'short.php?sid='.$sid),getglobal('setting/attachdir').$target,'M',25,2);
            return getglobal('setting/attachurl').$target;
        }
    }
}
?>
