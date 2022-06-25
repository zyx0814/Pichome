<?php
    namespace dzz\imageColor\classes;
    
    use \core as C;
    use \DB as DB;
    use \IO as IO;
    use \dzz\exif\classes\getInfo as info;
    class getthumb{
        
        public function run($data){

            if(strpos($data['realpath'],':') === false){
                $bz = 'dzz';
            }else{
                $patharr = explode(':', $data['realpath']);
                $bz = $patharr[0];
                $did = $patharr[1];

            }
            if(!is_numeric($did) || $did < 2){
                $bz = 'dzz';
            }
            if(!$data['ext'] || $bz != 'dzz'){
                return '';
            }
            $meta=$data;
            if (!extension_loaded('imagick')) {
                return '';
            } else{
                if(in_array($meta['ext'],array('ico','psd','png','tg+a','tiff','tif','cr2'))) $prefix='png';
				elseif(in_array($meta['ext'],array('ai','eps'))) $prefix='ai';
                else $prefix='';

                if($target=self::getThumb($meta,$prefix)){
                    if($imginfo=getimagesize(IO::getStream($target))){
                      return array($target);
                    }else{
                        return '';
                    }
                }

			}
        }
        public function getThumb($meta,$prefix=''){
            global $_G;
            if(!$prefix) $prefix = 'jpg';
            if($meta['tmpfile']){
                $target = md5($meta['realpath'].$meta['thumbsign']).'.'.(($prefix == 'ai') ? 'png':$prefix);
                //本地路径
                $jpg=$_G['setting']['attachdir'].'./'.'cache/'.$target;
            }else{
                $target = md5($meta['realpath']).'.'.(($prefix == 'ai') ? 'png':$prefix);
                //本地路径
                $jpg=$_G['setting']['attachdir'].'./'.'pichomethumb/'.$meta['appid'].'/'.$target;
            }
            if($recordpath = DB::result_first("select path from %t where rid =%s and thumbsign = 1 and thumbstatus = 1",array('thumb_record',$meta['rid']))){
                return $recordpath;

            }
			$dirpath=dirname($jpg);
			dmkdir($dirpath);
            $file=IO::getStream($meta['realpath']);
            try{
                $im=new \Imagick($file);
                //$icc_rgb = file_get_contents(DZZ_ROOT.'./dzz/imagick/icc/sRGB_v4_ICC_preference.icc');
                //$im->profileImage('icc', $icc_rgb);
               unset($icc_rgb);
                
                $im->setIteratorIndex(0);
				$owidth=$im->getImageWidth();
				$oheight=$im->getImageHeight();
				//$whsize = $this->getImageThumbsize($owidth,$oheight,$meta['thumbwidth'],$meta['thumbheight']);

				if($owidth>getglobal('config/pichomethumlargwidth') && $oheight>getglobal('config/pichomethumlargheight')){
					$width=1920;
					$height=ceil($width*$oheight/$owidth);
				}else{
					$width=$owidth;
					$height=$oheight;
				}
				if($prefix=='ai'){
					$prefix='png';
					if($width<1920){
						$width=1920;
						$height=ceil($width*$oheight/$owidth);
					}
				}
                if($prefix=='ai'){
                    $prefix='png';
                    if($$width<1920){
                        $width=1920;
                        $height=ceil($width*$oheight/$owidth);
                    }
                }
                $a = $im->thumbnailImage($width, $height, false);
                $im->stripImage(); //去除图片信息
                $im->setImageCompressionQuality(100); //图片质量
                
                $im->writeImage(($prefix?$prefix.':':'').$jpg);

                if($imginfo=@getimagesize($jpg)){
                    if(!$meta['tmpfile'])C::t('pichome_resources')->update($meta['rid'],array('width'=>$owidth,'height'=>$oheight));
                    return $jpg;

                }else{

                    runlog('imagick','uneable get size  file:'.$jpg);
                    return '';
                }
            }catch(\Exception $e){
                $message= $e->getMessage();
                $message = diconv($message,'GBK','UTF-8');
                runlog('imagick',$message.' file:'.$file);
            };
            return '';
        }
        public function getPath($dir = 'imagick')
        {
            global $_G;
            $target1 = $dir . '/index.html';
            $target_attach = $_G['setting']['attachdir'] .'./'. $target1;
            $targetpath = dirname($target_attach);
            dmkdir($targetpath);
            return $dir;
        }
        public function getImageThumbsize($owidth, $oheight, $width, $height)
        {
            if ($owidth > $width || $oheight > $height) {
                $or = $owidth / $oheight;
                $r = $width / $height;
                if ($r > $or) {
                    if ($oheight < $height) {
                        $height = $oheight;
                        $width = $owidth;
                    } else {

                        $width = ceil($height * $or);
                        if ($width < 242) {
                            $width = 242;
                            $height = ceil($width / $or);
                        }
                    }

                } else {
                    if ($owidth < $width) {
                        $height = $oheight;
                        $width = $owidth;
                    } else {
                        $height = ceil($width / $or);
                        $width = ceil($height * $or);
                        if ($width < 242) {
                            $width = 242;
                            $height = ceil($width / $or);
                        }
                    }
                }

            } else {
                $width = $owidth;
                $height = $oheight;
            }
            //Return the results
            return array($width, $height);

        }
    }