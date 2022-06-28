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
       }else{
           //$exts='jpg,png,gif,jpeg,dwg,aai,art,arw,avs,bpg,bmp,bmp2,bmp3,brf,cals,cals,cgm,cin,cip,cmyk,cmyka,cr2,crw,cube,cur,cut,dcm,dcr,dcx,dds,dib,djvu,dng,dot,dpx,emf,epdf,epi,eps,eps2,eps3,epsf,epsi,ept,exr,fax,fig,fits,fpx,gplt,gray,graya,hdr,heic,hpgl,hrz,ico,info,isobrl,isobrl6,jbig,jng,jp2,jpt,j2c,j2k,jxr,json,man,mat,miff,mono,mng,m2v,mpc,mpr,mrwmmsl,mtv,mvg,nef,orf,otb,p7,palm,pam,clipboard,pbm,pcd,pcds,pcl,pcx,pdb,pef,pes,pfa,pfb,pfm,pgm,picon,pict,pix,png8,png00,png24,png32,png48,png64,pnm,ppm,ps,ps2,ps3,psb,psd,ptif,pwp,rad,raf,rgb,rgb565,rgba,rgf,rla,rle,sfw,sgi,shtml,sid,mrsid,sum,svg,text,tga,tif,tiff,tim,ttf,ubrl,ubrl6,uil,uyvy,vicar,viff,wbmp,wpg,webp,wmf,wpg,x,xbm,xcf,xpm,xwd,x3f,YCbCr,YCbCrA,yuv,sr2,srf,srw,rw2,nrw,mrw,kdc,erf,canvas,caption,clip,clipboard,fractal,gradient,hald,histogram,inline,map,mask,matte,null,pango,plasma,preview,print,scan,radial_gradient,scanx,screenshot,stegano,tile,unique,vid,win,xc,granite,logo,netscpe,rose,wizard,bricks,checkerboard,circles,crosshatch,crosshatch30,crosshatch45,fishscales,gray0,gray5,gray10,gray15,gray20,gray25,gray30,gray35,gray40,gray45,gray50,gray55,gray60,gray65,gray70,gray75,gray80,gray85,gray90,gray95,gray100,hexagons,horizontal,horizontal2,horizontal3,horizontalsaw,hs_bdiagonal,hs_cross,hs_diagcross,hs_fdiagonal,hs_vertical,left30,left45,leftshingle,octagons,right30,right45,rightshingle,smallfishcales,vertical,vertical2,vertical3,verticalfishingle,vericalrightshingle,verticalleftshingle,verticalsaw,fff,3fr,ai,iiq,cdr';
           $exts = getglobal('config/imageickallowextlimit').','.getglobal('config/gdgetcolorextlimit');
           $extsarr = explode(',',$exts);
       }
       $extsarr = explode(',',$exts);
        if(!in_array($data['ext'],$extsarr)){
           runlog('imageColor',' unablegetcolor img='.$data['realpath']);
           return '';
        }

       // $width=isset($_G['setting']['thumbsize']['small']['width']) ? $_G['setting']['thumbsize']['small']['width']:64;
        //$height=isset($_G['setting']['thumbsize']['small']['height']) ? $_G['setting']['thumbsize']['small']['width']:64 ;
        $width = 64;
        $height = 64;
        $img = IO::getThumb($data['rid'],$width,$height,0,1,1,1);
        if(!$img) {
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            return '';
        }
        try{
            $palette=new ImagePalette( $img,1,10,$setting['lib'],$this->palette);
            $palettes=$palette->palette;
        }
        catch(\Exception $e){
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
            runlog('imageColor',$e->getMessage().' img='.$img);
            return '';
        }
        if (!is_array($palettes)) {
            DB::delete('pichome_palette', array('rid' => $data['rid']));
            C::t('pichome_resources_attr')->update($data['rid'],array('isget'=>-1));
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