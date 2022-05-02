<?php
if (!defined('IN_OAOOA')) { //所有的php文件必须加上此句，防止被外部调用
    exit('Access Denied');
}

global $_G;
global $Types, $Opentype, $Defaultallowext, $shapedataarr, $getvideothumb, $getvideoinfo,$Defaultnotallowdir;
//支持获取缩略图视频格式
$getvideothumb = [
    'avi', 'rm', 'rmvb', 'mkv', 'mov', 'wmv', 'asf', 'mpg', 'mpe', 'mpeg', 'mp4', 'm4v', 'mpeg', 'f4v', 'vob', 'ogv', 'mts', 'm2ts',
    '3gp', 'webm', 'flv', 'wav', 'mp3', 'ogg', 'midi', 'wma', 'vqf', 'ra', 'aac', 'flac', 'ape', 'amr', 'aiff', 'au', 'm4a'
];
//支持获取信息视频格式
$getvideoinfo = [
    'avi', 'rm', 'rmvb', 'mkv', 'mov', 'wmv', 'asf', 'mpg', 'mpe', 'mpeg', 'mp4', 'm4v', 'mpeg', 'f4v', 'vob', 'ogv', 'mts', 'm2ts',
    '3gp', 'webm', 'flv', 'wav', 'mp3', 'ogg', 'midi', 'wma', 'vqf', 'ra', 'aac', 'flac', 'ape', 'amr', 'aiff', 'au', 'm4a'
];
//尺寸筛选
$shapedataarr = array(
    1 => array(
        'start' => 'round((4 / 3) * 100)',
        'end' => '',
        'val' => 1,
        'lablename' => '4:3'
    ),
    2 => array(
        'start' => 'round((3 / 4) * 100)',
        'end' => '',
        'val' => 2,
        'lablename' => '3:4'
    ),
    3 => array(
        'start' => 'round((16 / 9) * 100)',
        'end' => '',
        'val' => 3,
        'lablename' => '16:9'
    ),
    4 => array(
        'start' => 'round((9 / 16) * 100)',
        'end' => '',
        'val' => 4,
        'lablename' => '9:16'
    ),
    5 => array(
        'start' => 250,
        'end' => 0,
        'val' => 5,
        'lablename' => '细长横图'
    ),
    6 => array(
        'start' => 0,
        'end' => 40,
        'val' => 6,
        'lablename' => '细长竖图'
    ),
    7 => array(
        'start' => 100,
        'end' => '',
        'val' => 7,
        'lablename' => '方图'
    ),
    8 => array(
        'start' => 100,
        'end' => 250,
        'val' => 8,
        'lablename' => '横图'
    ),
    9 => array(
        'start' => 40,
        'end' => 100,
        'val' => 9,
        'lablename' => '竖图'
    )

);
//格式分类
$Types = array(

    'commonimage' => ['jpg', 'png', 'gif', 'jpeg', 'webp','svg'],
    'image' => ['bmp', 'aai', 'art', 'arw', 'avs', 'bpg', 'bmp',
        'bmp2', 'bmp3', 'brf', 'cals', 'cals', 'cgm', 'cin', 'cip', 'cmyk', 'cmyka', 'cr2', 'crw', 'cube', 'cur', 'cut', 'dcm', 'dcr', 'dcx', 'dds',
        'dib', 'djvu', 'dng', 'dot', 'dpx', 'emf', 'epdf', 'epi', 'eps', 'eps2', 'eps3', 'epsf', 'epsi', 'ept', 'exr', 'fax', 'fig', 'fits', 'fpx', 'gplt',
        'gray', 'graya', 'hdr', 'heic', 'hpgl', 'hrz', 'ico', 'info', 'isobrl', 'isobrl6', 'jbig', 'jng', 'jp2', 'jpt', 'j2c', 'j2k', 'jxr', 'json', 'man',
        'mat', 'miff', 'mono', 'mng', 'm2v', 'mpc', 'mpr', 'mrwmmsl', 'mtv', 'mvg', 'nef', 'orf', 'otb', 'p7', 'palm', 'pam', 'clipboard', 'pbm',
        'pcd', 'pcds', 'pcl', 'pcx', 'pdb', 'pef', 'pes', 'pfa', 'pfb', 'pfm', 'pgm', 'picon', 'pict', 'pix', 'png8', 'png00', 'png24', 'png32', 'png48',
        'png64', 'pnm', 'ppm', 'ps', 'ps2', 'ps3', 'psb', 'psd', 'ptif', 'pwp', 'rad', 'raf', 'rgb', 'rgb565', 'rgba', 'rgf', 'rla', 'rle', 'sfw', 'sgi', 'shtml'
        , 'sid', 'mrsid', 'sum', 'svg', 'text', 'tga', 'tif', 'tiff', 'tim', 'ubrl', 'ubrl6', 'uil', 'uyvy', 'vicar', 'viff', 'wbmp', 'wpg', 'wmf', 'wpg', 'x', 'xbm', 'xcf',
        'xpm', 'xwd', 'x3f', 'YCbCr', 'YCbCrA', 'yuv', 'sr2', 'srf', 'srw', 'rw2', 'nrw', 'mrw', 'kdc', 'erf', 'canvas', 'caption', 'clip', 'clipboard', 'fractal', 'gradient',
        'hald', 'histogram', 'inline', 'map', 'mask', 'matte', 'null', 'pango', 'plasma', 'preview', 'print', 'scan', 'radial_gradient', 'scanx', 'screenshot',
        'stegano', 'tile', 'unique', 'vid', 'win', 'xc', 'granite', 'logo', 'netscpe', 'rose', 'wizard', 'bricks', 'checkerboard', 'circles', 'crosshatch',
        'crosshatch30', 'crosshatch45', 'fishscales', 'gray0', 'gray5', 'gray10', 'gray15', 'gray20', 'gray25', 'gray30', 'gray35', 'gray40', 'gray45',
        'gray50', 'gray55', 'gray60', 'gray65', 'gray70', 'gray75', 'gray80', 'gray85', 'gray90', 'gray95', 'gray100', 'hexagons', 'horizontal', 'horizontal2',
        'horizontal3', 'horizontalsaw', 'hs_bdiagonal', 'hs_cross', 'hs_diagcross', 'hs_fdiagonal', 'hs_vertical', 'left30', 'left45', 'leftshingle', 'octagons', 'right30', 'right45'
        , 'rightshingle', 'smallfishcales', 'vertical', 'vertical2', 'vertical3', 'verticalfishingle', 'vericalrightshingle', 'verticalleftshingle', 'verticalsaw', 'fff', '3fr', 'ai', 'iiq', 'cdr'],

    'document' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'odt', 'ods', 'odg', 'rtf', 'et', 'dpx', 'wps', 'dzzdoc', 'htm', 'html', 'shtm', 'shtml', 'hta', 'htc', 'xhtml', 'stm',
        'ssi', 'js', 'js', 'as', 'asc', 'asr', 'xml', 'xsl', 'xsd', 'dtd', 'xslt', 'rss', 'rdf', 'lbi', 'dwt', 'asp', 'asa', 'aspx', 'ascx', 'asmx', 'config', 'cs', 'css', 'cfm', 'cfml', 'cfc', 'tld', 'txt',
        'php', 'php3', 'php4', 'php5', 'php-dist', 'phtml', 'jsp', 'wml', 'tpl', 'lasso', 'jsf', 'vb', 'vbs', 'vtm', 'vtml', 'inc', 'sql', 'java', 'edml', 'master', 'info', 'install', 'theme',
        'config', 'module', 'profile', 'engine'],

    'video' => ['avi', 'rm', 'rmvb', 'mkv', 'mov', 'wmv', 'asf', 'mpg', 'mpe', 'mpeg', 'mp4', 'm4v', 'mpeg', 'f4v', 'vob', 'ogv', 'mts', 'm2ts', '3gp', 'webm', 'flv'],

    'audio' => ['wav', 'mp3', 'm4a', 'ogg', 'midi', 'wma', 'vqf', 'ra', 'aac', 'flac', 'ape', 'amr', 'aiff', 'au'],
    'font' => ['ttf', 'ttc', 'otf']
);
//支持打开的格式
$Opentype = array(
    'video' => ['mp3', 'mp4', 'webm', 'ogv', 'ogg', 'wav', 'm3u8', 'hls', 'mpg', 'mpeg', 'flv', 'm4v'],
    'text' => ['txt', 'php', 'js', 'jsp', 'htm', 'html', 'jsp', 'asp', 'aspx'],
    'pdf' => ['pdf'],
    'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
);
//默认允许导入格式
$Defaultallowext = '*.jpg,*.jpeg,*.gif,*.png,*.webp,*.pdf,*.txt,*.mp3,*.mp4,*.webm,*.ogv,*.ogg,*.wav,*.m3u8,*.hls,*.mpg,*.mpeg,*.flv,*.m4v';
//默认禁止导入目录
$Defaultnotallowdir=['patch','srv','run','lib64','sys','bin','media','boot','etc','sbin',
    'lib','dev','root','usr','proc','tmp','lost+found','lib32','etc.defaults','var.defaults',
    '@*','.*','$*'
];
function hex2rgb($hexColor)
{
    $color = str_replace('#', '', $hexColor);
    if (strlen($color) > 3) {
        $rgb = array(
            'r' => hexdec(substr($color, 0, 2)),
            'g' => hexdec(substr($color, 2, 2)),
            'b' => hexdec(substr($color, 4, 2))
        );
    } else {
        $r = substr($color, 0, 1) . substr($color, 0, 1);
        $g = substr($color, 1, 1) . substr($color, 1, 1);
        $b = substr($color, 2, 1) . substr($color, 2, 1);
        $rgb = array(
            'r' => hexdec($r),
            'g' => hexdec($g),
            'b' => hexdec($b)
        );
    }
    return $rgb;
}

function GetThemeColor()
{
    global $_G;
    $themecolor = C::t('user_setting')->fetch_by_skey('pichomeusertheme', $_G['uid']);
    if ($themecolor) {
        $theme = $themecolor;
    } elseif ($_G['setting']['pichomepagesetting']['theme']) {
        $theme = $_G['setting']['pichomepagesetting']['theme'];
    } else {
        $theme = 'white';
    }
    return $theme;
}