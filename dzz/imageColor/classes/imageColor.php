<?php

namespace dzz\imageColor\classes;

use \core as C;
use \DB as DB;
use \IO as IO;
use \ImagePalette as ImagePalette;
use \Color as Color;

class imageColor
{
    //public $palette = array(
    // 0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
    //);
    public $palette = [
        0xfff8e1, 0xf57c00, 0xffd740, 0xb3e5fc, 0x607d8b, 0xd7ccc8,
        0xff80ab, 0x4e342e, 0x9e9e9e, 0x66bb6a, 0xaed581, 0x18ffff,
        0xffe0b2, 0xc2185b, 0x00bfa5, 0x00e676, 0x0277bd, 0x26c6da,
        0x7c4dff, 0xea80fc, 0x512da8, 0x7986cb, 0x00e5ff, 0x0288d1,
        0x69f0ae, 0x3949ab, 0x8e24aa, 0x40c4ff, 0xdd2c00, 0x283593,
        0xaeea00, 0xffa726, 0xd84315, 0x82b1ff, 0xab47bc, 0xd4e157,
        0xb71c1c, 0x880e4f, 0x00897b, 0x689f38, 0x212121, 0xffff00,
        0x827717, 0x8bc34a, 0xe0f7fa, 0x304ffe, 0xd500f9, 0xec407a,
        0x6200ea, 0xffab00, 0xafb42b, 0x6a1b9a, 0x616161, 0x8d6e63,
        0x80cbc4, 0x8c9eff, 0xffeb3b, 0xffe57f, 0xfff59d, 0xff7043,
        0x1976d2, 0x5c6bc0, 0x64dd17, 0xffd600
    ];

    public function run($data)
    {
        global $_G;
        $appid = $data['appid'];
        if (isset($_G['setting'])) $setting = $_G['setting'];
        else  $setting = C::t('setting')->fetch_all();

        if ($setting['imagelib']) $setting['imagelib'] = extension_loaded($setting['imagelib']) ? $lib : 'gd';
        else $setting['imagelib'] = '';
        $lib = isset($setting['imagelib']) ? $setting['imagelib'] : (extension_loaded('imagick') ? 'imagick' : 'gd');
		$lib = extension_loaded($lib) ? $lib : 'gd';
        //if ($lib == 'gd') {
           // $exts = getglobal('config/gdgetcolorextlimit');
           // $extsarr = explode(',', $exts);

       // } else {
        //    $exts = getglobal('config/imageickallowextlimit') . ',' . getglobal('config/gdgetcolorextlimit');
        //    $extsarr = explode(',', $exts);
        //}

        //if (!in_array($data['ext'], $extsarr)) {

         //   runlog('imageColor', ' unablegetcolor img=' . $data['realpath']);
         //   return '';
       // }
        $cachepath = is_numeric($data['path']) ? intval($data['path']) : ($data['rid'] ? $data['rid']:md5($data['realpath']));
        if ($infodata = C::t('ffmpegimage_cache')->fetch_by_path($cachepath)) {
            $palettes = unserialize($infodata['info']);
            DB::delete('pichome_palette', array('rid' => $data['rid']));
            $colors = $palettesnum = [];
            foreach ($palettes as $k => $v) {
                if ($v > 0) {
                    $p = $this->getPaletteNumber($k);
                    $palettesnum[] = $p;
                    $color = new \Color($k);
                    $rgbcolor = $color->toRgb();
                    $colors[] = $rgbcolor;
                    $tdata = [
                        'rid' => $data['rid'],
                        'color' => $k,
                        'r' => $rgbcolor[0],
                        'g' => $rgbcolor[1],
                        'b' => $rgbcolor[2],
                        'weight' => $v,
                        'p' => $p
                    ];
                    C::t('pichome_palette')->insert($tdata);
                }

            }
            $isgray = $this->isgray($colors);
            $setarr = array('isget' => 1, 'gray' => $isgray);
            if ($palettesnum) {
                $setarr['colors'] = implode(',', $palettesnum);
            }
            C::t('pichome_resources_attr')->update_by_rid($appid, $data['rid'], $setarr);
            return false;
        }
        $width = 64;
        $height = 64;
        $img = IO::gettmpThumb($data['rid'], $width, $height, 1, 1);
        $img = IO::getStream($img);
        if (!$img) {
            C::t('pichome_resources_attr')->update($data['rid'], array('isget' => -1));
            return '';
        }
        try {
            $palette = new ImagePalette($img, 1, 12, $lib, $this->palette);
            $palettes = $palette->palette;

        } catch (\Exception $e) {
            C::t('pichome_resources_attr')->update($data['rid'], array('isget' => -1));
            @unlink($img);
            runlog('imageColor', $e->getMessage() . ' img=' . $img);
            return '';
        }
        if (!is_array($palettes)) {
            DB::delete('pichome_palette', array('rid' => $data['rid']));

            C::t('pichome_resources_attr')->update($data['rid'], array('isget' => -1));
            @unlink($img);
        } else {

            $cachearr = [
                'info' => serialize($palettes),
                'path' => $cachepath,
                'dateline' => TIMESTAMP
            ];
            C::t('ffmpegimage_cache')->insert($cachearr);
            DB::delete('pichome_palette', array('rid' => $data['rid']));
            $colors = $palettesnum = [];
            foreach ($palettes as $k => $v) {
                if ($v > 0) {
                    $p = $this->getPaletteNumber($k);
                    $palettesnum[] = $p;
                    $color = new Color($k);
                    $rgbcolor = $color->toRgb();
                    $colors[] = $rgbcolor;
                    $tdata = [
                        'rid' => $data['rid'],
                        'color' => $k,
                        'r' => $rgbcolor[0],
                        'g' => $rgbcolor[1],
                        'b' => $rgbcolor[2],
                        'weight' => $v,
                        'p' => $p
                    ];
                    C::t('pichome_palette')->insert($tdata);
                }

            }
            $isgray = $this->isgray($colors);
            $setarr = array('isget' => 1, 'gray' => $isgray);
            if ($palettesnum) {
                $setarr['colors'] = implode(',', $palettesnum);
            }
            C::t('pichome_resources_attr')->update_by_rid($appid, $data['rid'], $setarr);
            @unlink($img);
            return false;
        }
    }

    public function getPaletteNumber($colors, $palette = array())
    {

        if (empty($palette)) $palette = $this->palette;
        $arr = array();

        if (is_array($colors)) {
            $isarray = 1;
        } else {
            $colors = (array)$colors;
            $isarray = 0;
        }

        foreach ($colors as $color) {
            $bestColor = 0x000000;
            $bestDiff = PHP_INT_MAX;
            $color = new Color($color);
            foreach ($palette as $key => $wlColor) {
                // calculate difference (don't sqrt)
                $diff = $color->getDiff($wlColor);
                // see if we got a new best
                if ($diff < $bestDiff) {
                    $bestDiff = $diff;
                    $bestColor = $wlColor;
                }
            }
            unset($color);
            $arr[] = array_search($bestColor, $palette);
        }
        return $isarray ? $arr : $arr[0];
    }

    public function isgray($colors)
    {
        $i = 0;
        if (count($colors) < 1) return 0;
        foreach ($colors as $color => $value) {
            $color = new Color($color);
            $rgb = $color->toRGB();
            if (abs($rgb[0] - $rgb[1]) < 10 && abs($rgb[2] - $rgb[1]) < 10) {
                $i++;
            }
        }
        if ($i == count($colors)) {
            return 1;
        } else {
            return 0;
        }
    }

}