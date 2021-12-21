<?php

namespace dzz\billfish\classes;

use \core as C;
use \DB as DB;
use \fmpeg as fmpeg;

class getpichomethumb
{

    public function run(&$data)
    {

        $thumbid = DB::result_first("select thumb from %t where appid = %s and rid = %s", array('billfish_record', $data['appid'], $data['rid']));
        if (strlen($thumbid) < 9) {
            $thumbid = str_pad($thumbid,9,0,STR_PAD_LEFT);
        }
        $pathdir = $data['apppath'].BS.'.bf'.BS.'.preview';
        $thumbpatharr = $this->mbStrSplit($thumbid,3);
        array_pop($thumbpatharr);
        $thumbpath = implode(BS,$thumbpatharr);
        return array('icon'=>$pathdir.BS.$thumbpath.BS.$thumbid.'.webp');
    }

    function mbStrSplit ($string, $len=1) {
        $start = 0;
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string,$start,$len,"utf8");
            $string = mb_substr($string, $len, $strlen,"utf8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }


}