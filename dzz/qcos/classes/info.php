<?php
    
    namespace dzz\qcos\classes;
    
    use core as C;
    use \IO as IO;
    use \DB as DB;
    
    class info
    {

        public function run($data)
        {
            if(!$data['ext'] || strpos($data['bz'],'QCOS') === false){
                return '';
            }

            $qcosconfig = C::t('connect_storage')->fetch($data['remoteid']);
            $videoexts = getglobal('config/qcosmedia') ? explode(',',getglobal('config/qcosmedia')):array('3gp','avi','flv','mp4','m3u8','mpg','asf','wmv','mkv','mov','ts','webm','mxf');
            $imageexts = getglobal('config/qcosimage') ? explode(',',getglobal('config/qcosimage')):array('jpg','bmp','gif','png','webp');
            $cachepath = is_numeric($data['path']) ? intval($data['path']):($data['rid'] ? $data['rid']:md5($data['path']));
            if(in_array($data['ext'],$videoexts)){
               /* if(!$qcosconfig['mediastatus']){
                    return '';
                }*/

                if($infodata = C::t('ffmpegimage_cache')->fetch_by_path($cachepath)){
                    $info = unserialize($infodata);
                    $attr = array('width'=>$info['width'],'height'=>$info['height']);
                    C::t('pichome_resources')->update($data['rid'], $attr);
                    $attr1 = array('duration'=>$info['duration'],'isget'=>1);
                    C::t('pichome_resources_attr')->update($data['rid'], $attr1);
                    return false;
                }
                $hostarr = explode(':',$qcosconfig['hostname']);
                $config = [
                    'secretId' => trim($qcosconfig['access_id']),
                    'secretKey' => dzzdecode($qcosconfig['access_key'], 'QCOS'),
                    'region' => $hostarr[1],
                    'schema' => $hostarr[0],
                    'bucket'=>trim($qcosconfig['bucket']),
                ];

                include_once DZZ_ROOT.'dzz'.BS.'qcos'.BS.'class'.BS.'class_video.php';
                $this->video = new \video($config);
                try {
                    $fpatharr = explode(':',$data['path']);
                    unset($fpatharr[0]);
                    $ofpath = implode('/',$fpatharr[2]);
                    $object = str_replace(BS,'/',$ofpath);
                    if ($info = $this->video->get_mediainfo($object)) {
                        $cachearr = [
                            'info'=>serialize($info),
                            'path'=>$cachepath,
                            'dateline'=>TIMESTAMP
                        ];
                        C::t('ffmpegimage_cache')->insert($cachearr);
                        $attr = array('width'=>$info['width'],'height'=>$info['height']);
                        C::t('pichome_resources')->update($data['rid'], $attr);
                        $attr1 = array('duration'=>$info['duration'],'isget'=>1);
                        C::t('pichome_resources_attr')->update($data['rid'], $attr1);
                        return false;
                    }else{
                        C::t('pichome_resources_attr')->update($data['rid'], array('isget'=>-1));
                    }

                } catch (\Exception $e) {
                    runlog('qcosvideo', $e->getMessage() . ' file:' . $data['realpath']);
                    C::t('pichome_resources_attr')->update($data['rid'], array('isget'=>-1));
                }
            }
            elseif(in_array($data['ext'],$imageexts)){

                if(!$qcosconfig['imagestatus']){
                    return '';
                }
                if($infodata = C::t('ffmpegimage_cache')->fetch_by_path($cachepath) ) {

                    $palettes = unserialize($infodata);
                    \DB::delete('pichome_palette', array('rid' => $data['rid']));
                    foreach ($palettes as $k => $v) {
                        $color = new \Color($k);
                        $rgbcolor = $color->toRgb();
                        $tdata = [
                            'rid' => $data['rid'],
                            'color' => $k,
                            'r' => $rgbcolor[0],
                            'g' => $rgbcolor[1],
                            'b' => $rgbcolor[2],
                            'weight' => $v
                        ];
                        \C::t('pichome_palette')->insert($tdata);
                        \C::t('pichome_resources_attr')->update($data['rid'], array('isget' => 1));
                        return false;
                    }
                }
                $width = getglobal('config/pichomethumsmallwidth') ? getglobal('config/pichomethumsmallwidth') : 512;
                $height = getglobal('config/pichomethumsmallheight') ? getglobal('config/pichomethumsmallheight') : 512;
                //调用系统获取缩略图
                $returnurl = \IO::getThumb($data['rid'],$width,$height,0,1,1);
                $cachefile = '';
                if(!is_file($returnurl)){
                    $cachefile = getglobal('setting/attachdir') . 'cache/' . md5($data['path']) . '.' . $data['ext'];
                    $handle = fopen($cachefile, 'w+');
                    $fp = fopen($returnurl, 'rb');
                    while (!feof($fp)) {
                        fwrite($handle, fread($fp, 8192));
                    }
                    fclose($handle);
                    fclose($fp);
                    $returnurl = $cachefile;
                }
                if(!$returnurl) {
                    C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
                    return '';
                }
                try{
                    $palette=new \ImagePalette($returnurl,1,5,'gd',$this->palette);
                    $palettes=$palette->palette;
                    if($cachefile) @unlink($cachefile);
                }
                catch(\Exception $e){
                    C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
                    if($cachefile) @unlink($cachefile);
                    return '';
                }

                if (!is_array($palettes)) {
                    DB::delete('pichome_palette', array('rid' => $data['rid']));
                    C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
                }
                else {
                    $cachearr = [
                        'info'=>serialize($palettes),
                        'aid'=>$cachepath,
                        'dateline'=>TIMESTAMP
                    ];
                    C::t('ffmpegimage_cache')->insert($cachearr);
                    \DB::delete('pichome_palette', array('rid' => $data['rid']));
                    foreach ($palettes as $k => $v) {
                        $color = new \Color($k);
                        $rgbcolor = $color->toRgb();
                        $tdata = [
                            'rid' => $data['rid'],
                            'color' => $k,
                            'r' => $rgbcolor[0],
                            'g' => $rgbcolor[1],
                            'b' => $rgbcolor[2],
                            'weight' => $v
                        ];
                        \C::t('pichome_palette')->insert($tdata);
                    }
                    \C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
                    return false;
                }

            }

            return '';
        }
    }