<?php

namespace dzz\imageColor\classes;


use \core as C;
use \DB as DB;
use \ImagePalette as ImagePalette;


class getColor
{
    public $palette = array(
        0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
    );

    public function run(&$data)
    {
        $exts = getglobal('config/getcolorextlimit') ? getglobal('config/getcolorextlimit') : ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        $data['ext'] = strtolower($data['ext']);
        if (!$data['ext'] || !in_array($data['ext'], $exts)) {
            return true;
        } else {
            if (strtolower($data['ext']) == 'pdf' && !extension_loaded('imagick')) {
                 return;
            }

            $setarr = [
                'rid' => $data['rid'],
                'appid' => $data['appid'],
                'ext' => $data['ext']
            ];
            $isforce = (isset($data['isforce'])) ? 1 : 0;
            C::t('pichome_imagickrecord')->insert($setarr, $isforce);
            //dfsockopen(getglobal('localurl') . 'index.php?mod=imageColor&op=index&path=' . dzzencode($data['rid']), 0, '', '', false, '', 0.1);
        }
    }

    public function rundata($data)
    {


        //创建缩略图目录
        if (!is_dir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'])) {
            dmkdir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'], 0777, false);
        }
        $width = getglobal('config/pichomethumbwidth') ? getglobal('config/pichomethumbwidth') : 900;
        $height = getglobal('config/pichomethumbheight') ? getglobal('config/pichomethumbheight') : 900;
        //判断是否生成缩略图
        if (($data['width'] < $width) && ($data['height'] < $height)) {
            $img = $data['realpath'];
            //转换缩略图路径
            $img = str_replace(array('./', '/', '\\'), BS, $img);
            //更改缩略图生成状态为1
            C::t('pichome_imagickrecord')->update($data['rid'], array('thumbstatus' => 1));
        } else {
            $target = 'pichomethumb/' . $data['appid'] . '/' . md5($data['path']) . '.jpg';
            $img = $this->createthumb($data['realpath'], $target, $width, $height, 1);

            if ($img) {
                C::t('pichome_resources')->update($data['rid'], array('hasthumb' => 1));
                //转换缩略图路径
                $img = str_replace(array('./', '/', '\\'), BS, $img);

                C::t('pichome_imagickrecord')->update($data['rid'], array('thumbstatus' => 1,'path'=>$img));
            }
        }



        if (is_file($img)) {

            $lib = extension_loaded('imagick') ? 'imagick' : 'gd';
            try {
                $palette = new ImagePalette($img, 1, 10, $lib, $this->palette);
                $palettes = $palette->palette;
            } catch (\Exception $e) {
                $processname1 = 'PICHOMEGETCOLOR_'.$data['rid'];
                \dzz_process::unlock($processname1);
                runlog('imageColor', $e->getMessage() . ' img=' . $img);
            }

            if (!is_array($palettes)) {
                DB::delete('pichome_palette', array('rid' => $data['rid']));

                // return;
            } else {
                DB::delete('pichome_palette', array('rid' => $data['rid']));
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
                    C::t('pichome_palette')->insert($tdata);
                    C::t('pichome_imagickrecord')->update($data['rid'], array('colorstatus' => 1));
                    if(!DB::result_first("select isget from %t where rid = %s",array('pichome_resources_attr',$data['rid']))) {
                        C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
                        C::t('pichome_vapp')->add_getinfonum_by_appid($data['appid'], 1);
                    }
                }


            }
        }


    }

    public function createthumb($fileuri, $target, $width, $height, $thumbtype = 1)
    {
        global $_G;
        include_once libfile('class/image');
        $target_attach = $_G['setting']['attachdir'] . './' . $target;
        $targetpath = dirname($target_attach);
        dmkdir($targetpath);
        $image = new \image();
        //$fileuri = str_replace(DZZ_ROOT,'',$fileuri);
        try {
            $thumb = $image->Thumb($fileuri, $target, $width, $height, $thumbtype);
        } catch (\Exception $e) {
            $thumb = false;

        }
        return ($thumb) ? $target_attach : false;
    }

    function getpdfthumb($data)
    {

        $width = getglobal('config/pichomethumbwidth') ? getglobal('config/pichomethumbwidth') : 900;
        $height = getglobal('config/pichomethumbheight') ? getglobal('config/pichomethumbheight') : 900;
        if (!extension_loaded('imagick')) {
            return true;
        }
        if (!is_dir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'])) {
            dmkdir(getglobal('setting/attachdir') . './' . 'pichomethumb/' . $data['appid'], 0777, false);
        }
        if (!file_exists($data['realpath'])) {
            return true;
        }
        $target = 'pichomethumb/' . $data['appid'] . '/' . md5($data['path']) . '.jpg';
        $im = new \Imagick();
        $im->setResolution($width, $height);   //设置图像分辨率
        $im->setCompressionQuality(80); //压缩比
        try {

            $im->readImage($data['realpath'] . '[0]'); //设置读取pdf的第一页
        } catch (\Exception $e) {
            runlog('pdfthumb', iconv("gbk", 'utf-8', $e->getMessage()));
        }

        //$im->thumbnailImage(200, 100, true); // 改变图像的大小
        //缩放大小图像
        try {
            $im->scaleImage($width, $height, true);
        }catch (\Exception $e){
            runlog('pdfthumb', iconv("gbk", 'utf-8', $e->getMessage()));
        }
        $filename = getglobal('setting/attachdir') . './' . $target;
        try {
            if ($im->writeImage($filename) == true) {
                $imginfo = @getimagesize($filename);
                $resourcesarr = [
                    'width' => $imginfo[0] ? $imginfo[0]:0,
                    'height' =>$imginfo[1] ? $imginfo[1]:0
                ];
                C::t('pichome_resources')->update($data['rid'],$resourcesarr);
                C::t('pichome_imagickrecord')->update($data['rid'], array('thumbstatus' => 1,'colorstatus'=>1,'path'=>$filename));
                C::t('pichome_resources')->update($data['rid'], array('hasthumb' => 1));
            }

        }catch (\Exception $e){
            runlog('pdfthumb', iconv("gbk", 'utf-8', $e->getMessage()));
        }

    }
}