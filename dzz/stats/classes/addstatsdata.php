<?php
namespace dzz\stats\classes;

use \DB as DB;
use \core as C;

class addstatsdata{
    private $reperatime = 0;
    public function run(&$data){
        if(!$data['statstype']) return false;
        //浏览和下载统计
        if($data['statstype'] == 1){
            //if(!$data['rid']) return false;
            $setarr = [
                'idtype'=>intval($data['idtype']),
                'idval'=>$data['idval'] ? $data['idval']:'',
                'name'=>$data['name'],
                'uid'=>getglobal('uid'),
                'username'=>getglobal('username'),
                'dateline'=>TIMESTAMP,
                'isadmin'=>$data['isadmin'] ? intval($data['isadmin']):0
            ];
            C::t('#stats#stats_view')->insert_data($setarr,$this->reperatime);
        }elseif($data['statstype'] == 2){//关键词统计
            //关键词限定长度最多二十个中文字符
            $setarr = [
                'idtype'=>intval($data['idtype']),
                'idval'=>$data['idval'] ? $data['idval']:'',
                'keyword'=>$data['keyword'],
                'uid'=>getglobal('uid'),
                'username'=>getglobal('username'),
                'dateline'=>TIMESTAMP,
                'isadmin'=>$data['isadmin'] ? intval($data['isadmin']):0
            ];
            C::t('#stats#stats_keyword')->insert_data($setarr,$this->reperatime);
        }
    }
}