<?php
@set_time_limit(0);
@ini_set('memory_limit', -1);
@ini_set('max_execution_time', 0);
$colors = array(
    '#FFEBEE', '#FFCDD2', '#EF9A9A', '#E57373', '#EF5350', '#F44336', '#E53935', '#D32F2F', '#C62828',
    '#B71C1C', '#FF8A80', '#FF5252', '#FF1744', '#D50000', '#FCE4EC', '#F8BBD0', '#F48FB1', '#F06292',
    '#EC407A', '#E91E63', '#D81B60', '#C2185B', '#AD1457', '#880E4F', '#FF80AB', '#FF4081', '#F50057',
    '#C51162', '#F3E5F5', '#E1BEE7', '#CE93D8', '#BA68C8', '#AB47BC', '#9C27B0', '#8E24AA', '#7B1FA2',
    '#6A1B9A', '#4A148C', '#EA80FC', '#E040FB', '#D500F9', '#AA00FF', '#EDE7F6', '#D1C4E9', '#B39DDB',
    '#9575CD', '#7E57C2', '#673AB7', '#5E35B1', '#512DA8', '#4527A0', '#311B92', '#B388FF', '#7C4DFF',
    '#651FFF', '#6200EA', '#E8EAF6', '#C5CAE9', '#9FA8DA', '#7986CB', '#5C6BC0', '#3F51B5', '#3949AB',
    '#303F9F', '#283593', '#1A237E', '#8C9EFF', '#536DFE', '#3D5AFE', '#304FFE', '#E3F2FD', '#BBDEFB',
    '#90CAF9', '#64B5F6', '#42A5F5', '#2196F3', '#1E88E5', '#1976D2', '#1565C0', '#0D47A1', '#82B1FF',
    '#448AFF', '#2979FF', '#2962FF', '#E1F5FE', '#B3E5FC', '#81D4FA', '#4FC3F7', '#29B6F6', '#03A9F4',
    '#039BE5', '#0288D1', '#0277BD', '#01579B', '#80D8FF', '#40C4FF', '#00B0FF', '#0091EA', '#E0F7FA',
    '#B2EBF2', '#80DEEA', '#4DD0E1', '#26C6DA', '#00BCD4', '#00ACC1', '#0097A7', '#00838F', '#006064',
    '#84FFFF', '#18FFFF', '#00E5FF', '#00B8D4', '#E0F2F1', '#B2DFDB', '#80CBC4', '#4DB6AC', '#26A69A',
    '#009688', '#00897B', '#00796B', '#00695C', '#004D40', '#A7FFEB', '#64FFDA', '#1DE9B6', '#00BFA5',
    '#E8F5E9', '#C8E6C9', '#A5D6A7', '#81C784', '#66BB6A', '#4CAF50', '#43A047', '#388E3C', '#2E7D32',
    '#1B5E20', '#B9F6CA', '#69F0AE', '#00E676', '#00C853', '#F1F8E9', '#DCEDC8', '#C5E1A5', '#AED581',
    '#9CCC65', '#8BC34A', '#7CB342', '#689F38', '#558B2F', '#33691E', '#CCFF90', '#B2FF59', '#76FF03',
    '#64DD17', '#F9FBE7', '#F0F4C3', '#E6EE9C', '#DCE775', '#D4E157', '#CDDC39', '#C0CA33', '#AFB42B',
    '#9E9D24', '#827717', '#F4FF81', '#EEFF41', '#C6FF00', '#AEEA00', '#FFFDE7', '#FFF9C4', '#FFF59D',
    '#FFF176', '#FFEE58', '#FFEB3B', '#FDD835', '#FBC02D', '#F9A825', '#F57F17', '#FFFF8D', '#FFFF00',
    '#FFEA00', '#FFD600', '#FFF8E1', '#FFECB3', '#FFE082', '#FFD54F', '#FFCA28', '#FFC107', '#FFB300',
    '#FFA000', '#FF8F00', '#FF6F00', '#FFE57F', '#FFD740', '#FFC400', '#FFAB00', '#FFF3E0', '#FFE0B2',
    '#FFCC80', '#FFB74D', '#FFA726', '#FF9800', '#FB8C00', '#F57C00', '#EF6C00', '#E65100', '#FFD180',
    '#FFAB40', '#FF9100', '#FF6D00', '#FBE9E7', '#FFCCBC', '#FFAB91', '#FF8A65', '#FF7043', '#FF5722',
    '#F4511E', '#E64A19', '#D84315', '#BF360C', '#FF9E80', '#FF6E40', '#FF3D00', '#DD2C00', '#EFEBE9',
    '#D7CCC8', '#BCAAA4', '#A1887F', '#8D6E63', '#795548', '#6D4C41', '#5D4037', '#4E342E', '#3E2723',
    '#FAFAFA', '#F5F5F5', '#EEEEEE', '#E0E0E0', '#BDBDBD', '#9E9E9E', '#757575', '#616161', '#424242',
    '#212121', '#ECEFF1', '#CFD8DC', '#B0BEC5', '#90A4AE', '#78909C', '#607D8B', '#546E7A', '#455A64',
    '#37474F', '#263238','#FFFFFF',
);


require_once  'colorget\ml\vendor\autoload.php';


function hexToRgb($hex) {
    // 去除 # 号
    $hex = str_replace('#', '', $hex);

    // 将十六进制颜色值转换为整数
    $color = hexdec($hex);

    // 获取 RGB 分量
    $red = ($color >> 16) & 0xFF;
    $green = ($color >> 8) & 0xFF;
    $blue = $color & 0xFF;
    // 返回 RGB 值
    return array($red, $green, $blue);
}
$colorarr = [];
foreach($colors as $v){
    $crr = hexToRgb($v);

    $colorarr[] =[$crr[0],$crr[1],$crr[2]];

}

use Phpml\Clustering\KMeans;

$kmeans = new KMeans(64); // 选择 10 个簇

$clusters = $kmeans->cluster($colorarr);
$newclusters = array_map(function($subArr) {
    return array_values($subArr);
}, $clusters);
/*$sixcolor = array(
    "红色" => "#FF0000",
    "橙色" => "#FFA500",
    "黄色" => "#FFFF00",
    "绿色" => "#008000",
    "青色" => "#00FFFF",
    "蓝色" => "#0000FF",
    "紫色" => "#800080",
    "粉红色" => "#FFC0CB",
    "棕色" => "#A52A2A",
    "灰色" => "#808080",
    "白色" => "#FFFFFF",
    "黑色" => "#000000",
    "银色" => "#C0C0C0",
    "金色" => "#FFD700",
    "珊瑚色" => "#FF7F50",
    "海军蓝色" => "#000080"
);
foreach($sixcolor as $v){
    echo "<div style='background-color: $v; width: 50px; height: 50px; display: inline-block;'>$v</div>";
    $v = str_replace('#','',$v);
    $colorint[] =hexdec($v);
}*/

$colorint = $colorgb = $colorhex= [];
foreach ($newclusters as $v) {
    $middleIndex = floor(count($v) / 2);
    $rgbarr = $v[$middleIndex];
    $colorgb[] = $rgbarr;
    $chex = sprintf("#%02x%02x%02x", $rgbarr[0], $rgbarr[1], $rgbarr[2]);
    $colorhex[] =$chex;
    echo "<div style='background-color: $chex; width: 50px; height: 50px; display: inline-block;'>$chex</div>";
    $chex = str_replace('#','',$chex);

    $colorint[] =hexdec($chex);
}
print_r($colorgb);die;
echo '<br>';
echo '<br>';
foreach ($colorhex as $k=>$v){
    if($k == 7 || $k == 52){
        print_r($colorgb[$k]);
        echo "<div style='background-color: $v; width: 100px; height: 100px; display: inline-block;'>$v</div>";
    }
}
echo '<br>';
echo '<br>';

function intToHexColor($intColor) {
    // 将整型值转换为十六进制字符串
    $hexColor = dechex($intColor);
    // 如果十六进制字符串长度不足 6 位，则在前面补 0
    $hexColor = str_pad($hexColor, 6, '0', STR_PAD_LEFT);
    // 在十六进制字符串前面加上 x0
    $hexColor = '0x' . $hexColor;
    // 返回转换后的十六进制颜色值
    return $hexColor;
}
/*$arr = [];
foreach($colorint as $v){
        $arr[] = intToHexColor($v);
}*/
// 打开文件并写入数组数据
/*$file = fopen('colors.php', 'w');
fwrite($file, '<?php $myArray = array(');
fwrite($file, implode(',', $arr));
fwrite($file, '); ?>');
fclose($file);
print_r($arr);die;*/
$colorint =
    [
        0xfff8e1,0xf57c00,0xffd740,0xb3e5fc,0x607d8b,0xd7ccc8,
        0xff80ab,0x4e342e,0x9e9e9e,0x66bb6a,0xaed581,0x18ffff,
        0xffe0b2,0xc2185b,0x00bfa5,0x00e676,0x0277bd,0x26c6da,
        0x7c4dff,0xea80fc,0x512da8,0x7986cb,0x00e5ff,0x0288d1,
        0x69f0ae,0x3949ab,0x8e24aa,0x40c4ff,0xdd2c00,0x283593,
        0xaeea00,0xffa726,0xd84315,0x82b1ff,0xab47bc,0xd4e157,
        0xb71c1c,0x880e4f,0x00897b,0x689f38,0x212121,0xffff00,
        0x827717,0x8bc34a,0xe0f7fa,0x304ffe,0xd500f9,0xec407a,
        0x6200ea,0xffab00,0xafb42b,0x6a1b9a,0x616161,0x8d6e63,
        0x80cbc4,0x8c9eff,0xffeb3b,0xffe57f,0xfff59d,0xff7043,
        0x1976d2,0x5c6bc0,0x64dd17,0xffd600];

/*$webSafeColors = array(


    0xFF0000 => "红色",
    0xFF1A1A => "深红色",
    0xFF3333 => "深红色",
    0xFF4D4D => "深红色",
    0xFF6666 => "深红色",
    0xFF8080 => "深红色",
    0xFF9999 => "深红色",
    0xFFB3B3 => "浅红色",
    0xFFCCCC => "浅红色",
    0xFFE6E6 => "浅红色",
    0xFFF0F0 => "浅红色",
    0xFFF5F5 => "浅红色",
    0xFFFAFA => "浅红色",
    0xFFE0E0 => "浅红色",
    0xFFC8C8 => "浅红色",
    0xFFB0B0 => "浅红色",

    0xFF00FF => "洋红色",
    0xFF1AFF => "深洋红色",
    0xFF33FF => "深洋红色",
    0xFF4DFF => "深洋红色",
    0xFF66FF => "深洋红色",
    0xFF80FF => "深洋红色",
    0xFF99FF => "深洋红色",
    0xFFB3FF => "浅洋红色",
    0xFFCCFF => "浅洋红色",
    0xFFE6FF => "浅洋红色",
    0xFFF0FF => "浅洋红色",
    0xFFF5FF => "浅洋红色",
    0xFFFAFF => "浅洋红色",
    0xFFE0FF => "浅洋红色",
    0xFFC8FF => "浅洋红色",
    0xFFB0FF => "浅洋红色",

    0xFFFF00 => "黄色",
    0xFFFF1A => "深黄色",
    0xFFFF33 => "深黄色",
    0xFFFF4D => "深黄色",
    0xFFFF66 => "深黄色",
    0xFFFF80 => "深黄色",
    0xFFFF99 => "深黄色",
    0xFFFFB3 => "浅黄色",
    0xFFFFCC => "浅黄色",
    0xFFFFE6 => "浅黄色",
    0xFFFFF0 => "浅黄色",
    0xFFFFF5 => "浅黄色",
    0xFFFFFF => "浅黄色",
    0xFFF0E0 => "淡橙色",
    0xFFE0C8 => "淡橙色",
    0xFFD1A0 => "淡橙色",

    0xFFFFFF => "白色",
    0xF5F5F5 => "浅灰色",
    0xE0E0E0 => "浅灰色",
    0xC8C8C8 => "浅灰色",
    0xB0B0B0 => "浅灰色",
    0x999999 => "深灰色",
    0x808080 => "深灰色",
    0x666666 => "深灰色",
    0x4D4D4D => "深灰色",
    0x333333 => "深灰色",
    0x1A1A1A => "深灰色",
    0x000000 => "黑色",
    0xFFF5F5 => "浅粉色",
    0xFFE0E0 => "浅粉色",
    0xFFC8C8 => "浅粉色",
    0xFFB0B0 => "浅粉色",

    0xC0C0C0 => "银色",
    0xC8C8C8 => "浅灰色",
    0xD1D1D1 => "浅灰色",
    0xDADADA => "浅灰色",
    0xE3E3E3 => "浅灰色",
    0xECECEC => "浅灰色",
    0xF5F5F5 => "浅灰色",
    0xFFFFFF => "白色",
    0xB3B3B3 => "深灰色",
    0x999999 => "深灰色",
    0x808080 => "深灰色",
    0x666666 => "深灰色",
    0x4D4D4D => "深灰色",
    0x333333 => "深灰色",
    0x1A1A1A => "深灰色",
    0x000000 => "黑色",

    0x808080 => "灰色",
    0x8C8C8C => "浅灰色",
    0x999999 => "浅灰色",
    0xA6A6A6 => "浅灰色",
    0xB3B3B3 => "浅灰色",
    0xC0C0C0 => "浅灰色",
    0xC8C8C8 => "浅灰色",
    0xD1D1D1 => "浅灰色",
    0xDADADA => "浅灰色",
    0xE3E3E3 => "浅灰色",
    0xECECEC => "浅灰色",
    0xF5F5F5 => "浅灰色",
    0xFFFFFF => "白色",
    0x666666 => "深灰色",
    0x4D4D4D => "深灰色",
    0x333333 => "深灰色",

    0x800000 => "深红色",
    0x8B0000 => "深红色",
    0x960000 => "深红色",
    0xA00000 => "深红色",
    0xAA0000 => "深红色",
    0xB40000 => "深红色",
    0xBF0000 => "深红色",
    0xC90000 => "深红色",
    0xD40000 => "深红色",
    0xDF0000 => "深红色",
    0xE90000 => "深红色",
    0xF40000 => "深红色",
    0xFF0000 => "红色",


    0xFFA500 => "橙色",
    0xFFB347 => "浅橙色",
    0xFFC299 => "浅橙色",
    0xFFD1BD => "浅橙色",
    0xFFE0E5 => "浅橙色",
    0xFFE5B4 => "浅橙色",
    0xFFEBCD => "浅橙色",
    0xFFF0F5 => "浅橙色",
    0xFFF5EE => "浅橙色",
    0xFFF8DC => "浅橙色",
    0xFFFACD => "浅橙色",
    0xFFFAF0 => "浅橙色",
    0xFFFF00 => "黄色",
    0xCD8500 => "深橙色",
    0x8B5A00 => "深橙色",
    0x61380B => "深橙色",

    0x800080 => "深紫色",
    0x8B008B => "深紫色",
    0x960096 => "深紫色",
    0xA000A0 => "深紫色",
    0xAA00AA => "深紫色",
    0xB400B4 => "深紫色",
    0xBF00BF => "深紫色",
    0xC900C9 => "深紫色",
    0xD400D4 => "深紫色",
    0xDF00DF => "深紫色",
    0xE900E9 => "深紫色",
    0xF400F4 => "深紫色",
    0xFF00FF => "洋红色",
    0x660066 => "紫色",
    0x4D004D => "紫色",
    0x330033 => "紫色",

    0xFF4500 => "橙红色",
    0xFF5A5A => "浅橙红色",
    0xFF7F50 => "浅橙红色",
    0xFF8C00 => "深橙色",
    0xFFA07A => "浅橙红色",
    0xFFA500 => "橙色",
    0xFFB347 => "浅橙色",
    0xFFC0CB => "粉红色",
    0xFFD700 => "金色",
    0xFFE4B5 => "浅橙色",
    0xFFE4C4 => "浅橙色",
    0xFFE4E1 => "浅粉红色",
    0xFFE5B4 => "浅橙色",
    0xFFEBCD => "浅橙色",
    0xFFEFDB => "浅粉红色",
    0xFFF0F5 => "浅粉红色",

    0xEE82EE => "紫罗兰色",
    0xC71585 => "深洋红色",
    0x8B008B => "深洋红色",
    0x4B0082 => "靛蓝色",
    0x9400D3 => "深紫色",
    0x9932CC => "深兰花紫色",
    0x800080 => "深紫色",
    0x6A5ACD => "板岩蓝色",
    0x483D8B => "深slate蓝色",
    0x663399 => "熏衣草淡紫色",
    0x8A2BE2 => "蓝紫色",
    0x9370DB => "灰蓝色",
    0x7B68EE => "中板岩蓝色",
    0x6B8E23 => "橄榄土绿色",
    0x9400D3 => "深紫色",
    0x8B008B => "深洋红色"
);*/
function hexToRgbs($hexColor)
{
    $hexColor = str_replace('#', '', $hexColor);
    $r = hexdec(substr($hexColor, 0, 2));
    $g = hexdec(substr($hexColor, 2, 2));
    $b = hexdec(substr($hexColor, 4, 2));
    return "rgb({$r}, {$g}, {$b})";
}

/*$palettes = [];
foreach ($colors as $v) {
    $v = str_replace('#', '', $v);
    $v = hexdec($v);
    $palettes[] = $v;
}
foreach ($colors as $v) {
    echo "<div style='background-color: $v; width: 50px; height: 50px; display: inline-block;'></div>";
}*/

//echo count($palettes);die;
/*$palettes = array(
    0x111111, 0xFFFFFF, 0x9E9E9E, 0xA48057, 0xFC85B3, 0xFF2727, 0xFFA34B, 0xFFD534, 0x47C595, 0x51C4C4, 0x2B76E7, 0x6D50ED
);*/
$filename = DZZ_ROOT . '7.jpg';

$lib = isset($setting['imagelib']) ? $setting['imagelib'] : (extension_loaded('imagick') ? 'imagick' : 'gd');

if ($lib == 'gd') {
    $exts = getglobal('config/gdgetcolorextlimit');
    $extsarr = explode(',', $exts);

} else {
    $exts = getglobal('config/imageickallowextlimit') . ',' . getglobal('config/gdgetcolorextlimit');
    $extsarr = explode(',', $exts);
}


$palette = new ImagePalette($filename, 1, 64, $lib, $colorint);
$palettes = $palette->palette;

arsort($palettes);

echo '<br>';
echo '<br>';
echo '<br>';
echo '<br>';
$coarr = [];
foreach ($palettes as $k => $v) {
    $c = new Color($k);
    $co = $c->toHexString();
    $ro = $c->toRgb();
    print_r($ro);
    $coarr[] = $k;
    echo "<div style='background-color: $co; width: 50px; height: 50px; display: inline-block;'>$v</div>";
}

$a = getPaletteNumber($coarr,$colorint);
print_r($a);die;






function getPaletteNumber($colors, $palette = array())
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