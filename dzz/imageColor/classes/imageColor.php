<?php
namespace dzz\imageColor\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
use \ImagePalette as ImagePalette;
class imageColor{
    public $palette = array(
        0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
    );
	public function run($data){
	    global $_G;
        if (isset($_G['setting'])) $setting = $_G['setting'];
        else  $setting = C::t('setting')->fetch_all();
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

        $lib  = isset($setting['imagelib']) ? $setting['imagelib']:(extension_loaded('imagick')?'imagick':'gd');

        if($lib== 'gd'){
            $exts = getglobal('config/gdgetcolorextlimit');
            $extsarr = explode(',',$exts);

        }else{
            $exts = getglobal('config/imageickallowextlimit').','.getglobal('config/gdgetcolorextlimit');
            $extsarr = explode(',',$exts);
        }

        if(!in_array($data['ext'],$extsarr)){
           runlog('imageColor',' unablegetcolor img='.$data['realpath']);
           return '';
        }

        $width = 64;
        $height = 64;
        $img = IO::getThumb($data['rid'],$width,$height,0,1,1,1);
        if(!$img) {
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            return '';
        }
        try{
            $palette=new ImagePalette( $img,1,10,$lib,$this->palette);
            $palettes=$palette->palette;
        }
        catch(\Exception $e){
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            @unlink($img);
            runlog('imageColor',$e->getMessage().' img='.$img);
            return '';
        }
        if (!is_array($palettes)) {
            DB::delete('pichome_palette', array('rid' => $data['rid']));
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            @unlink($img);
        }
        else {
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

            }
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>1));
            @unlink($img);
            return false;
        }
    }

   
}