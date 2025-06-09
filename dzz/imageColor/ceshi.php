<?php
$img = 'test.jpg';
$palettes = array(
    0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
);
$lib=extension_loaded('imagick')?'imagick':'gd';
$palette=new ImagePalette( $img,1,5,$lib,$palettes);
$palettes=$palette->palette;
arsort($palettes);
$colordatas = [];
/*foreach ($palettes as $k => $v) {
    $hexColor = hexToRgbs($k);
    $colordatas[$hexColor] = $v;
}*/


foreach ($palettes as $k => $v) {
    $c = new Color($k);
    $co = $c->toHexString();
    echo "<div style='background-color: $co; width: 50px; height: 50px; display: inline-block;'>$v</div>";
}
