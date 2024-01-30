<?php
/**
 * PHP 汉字转拼音
 * @example
 *	echo pinyin::encode('乐云网络'); //编码为拼音首字母
 *	echo pinyin::encode('乐云网络', 'all'); //编码为全拼音
 */
    include_once DZZ_ROOT . './core/api/Pinyin/vendor/autoload.php';
    use Overtrue\Pinyin\Pinyin as p;
class pinyin {

    /**
     * 将中文编码成拼音
     * @param string $utf8Data utf8字符集数据
     * @param string $sRetFormat 返回格式 [head:首字母|all:全拼音]
     * @return string
     */
	public static function encode($utf8Data, $sRetFormat='head')
    {
        $p = new p();
        if ('head' === $sRetFormat) {
            $aBuf = $p->abbr($utf8Data);
        } else {
            $aBuf = $p->sentence($utf8Data);
        }
        return $aBuf;
    }
}
