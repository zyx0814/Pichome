<?php

namespace dzz\aiXhimage\classes;

require_once DZZ_ROOT . './dzz/aiXhimage/class/xhChat.php';

use \C as C;
use \xhChat as xhChat;
use \DB as DB;
use \IO as IO;


class ImagetagAnddesc
{

    public $allowExt = [];

    public function run($rid, $extar = [])
    {

        $aiKey = $extar['aiKey'];
        if($aiKey != 'aiXh::chatImage'){
            return true;
        }
        if (!$rid) return ['error' => 'params is not allow'];
        $questionType = [
            '请根据图片内容给出3到5个关键词作为图片标签，每个标签词的长度不能超过5个字，且标签词之间不能重复。返回结果的格式为“标签1,标签2,标签3”，其中标签之间使用逗号分割。',
            '请你根据所给出的图片，详细描述其内容，并给出一个不超过120个字符的简短介绍。请确保你的描述中包含主要元素、色彩和可能的主题或情感表达，同时注意保持描述的连贯性和准确性。',
        ];
        $force = $extar['force'] ? $extar['force'] : 0;
        $tplid = $extar['tplid'] ? $extar['tplid'] : 0;
        $promptdata = C::t('#aiXhimage#ai_xhimageprompt')->fetch($tplid);
        if (!$promptdata) return ['error' => 'prompt is not exists'];
        $getType = $promptdata['cate'];
        if($promptdata['cate'] == 1){
            $question = $promptdata['prompt'] .'。返回结果的格式为“标签1,标签2,标签3”，其中标签之间使用逗号分割。';
        }else{
            $question = $promptdata['prompt'];
        }
        //获取文件数据
        $metadata = IO::getMeta($rid);
        if (!$metadata) return ['error' => 'file is not exists'];
        $imgurl = FALSE;
        $this->getAllowExts();
        $thumbdata = DB::fetch_first("select * from %t where rid =%s", array('thumb_record', $rid));
        if ($thumbdata['sstatus']) {
            $imgurl = IO::getFileUri($thumbdata['spath']);
        } else {
            $imgurl = IO::getThumb($rid,'small',0,1, 1,1);
            if (!$imgurl && in_array($metadata['ext'], $this->allowExt) && $metadata['filesize'] <= 10 * 1024 * 1024) {
                if($metadata['aid'])$imgurl = IO::getStream('attach::' . $metadata['aid']);
                else $imgurl = IO::getStream($rid);
            }
        }
        if ($imgurl) {
            if(!$metadata['aid']) $metadata['aid'] = 0;
            $setarr = ['aid' => $metadata['aid'], 'rid' => $rid, 'gettype' => $getType,'tplid'=>$tplid,'aikey'=>$aiKey];
            if ($data = C::t('ai_imageparse')->insertData($setarr)) {
                if ($data['isget'] && $data['data'] && !$force) {
                    $content = $data['data'];
                } else {
                    $params = ['imageurl' => $imgurl, 'question' => $question];
                    $return = $this->waitLock('DZZ_LOCK_XHIAMGEPARSE');
                    if(isset($return['error'])){
                        return ['error' => $return['error']];
                    }else{
                        $params['processname'] = $return;
                    }
                    $chatclinet = new xhChat();
                    $aireturn = $chatclinet->getApiData('aiXh::chatImage', $params);
                    if ($aireturn['error_msg']) return ['error'=>$aireturn['error_msg']];
                    if($aireturn['result']){
                        if($aireturn['totaltoken']){
                            $tokendatas = [
                                'totaltoken'=>$aireturn['totaltoken'],
                                'uid'=>getglobal('uid'),
                                'app'=>'aiXhimage',
                                'gettype'=>$getType,
                                'dateline'=>TIMESTAMP
                            ];
                            \Hook::listen('statsTokenuse',$tokendatas);
                        }
                        C::t('ai_imageparse')->update($data['id'], ['isget'=>1,'data'=>$aireturn['result'],'totaltoken'=>$aireturn['totaltoken']]);
                        $content = $aireturn['result'];
                    }else{
                        C::t('ai_imageparse')->update($data['id'], ['isget'=>1,'data'=>'']);
                        return ['error' => 'aiXh::chatImage error'];
                    }

                }

                if ($getType == 1) {
                    $tags = explode('，',$content);
                    $tids = [];
                    foreach ($tags as $v) {
                        $v = preg_replace('/标签\d+[:：]/', '', $v);
                        $v = trim($v);
                        $v = str_replace([',','，','.','。'],'',$v);
                        $v = trim($v);
                        if ($v) {
                            $tids[] = C::t('pichome_tag')->insert($v, 1);
                        }
                    }
                    $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                    $datatags = explode(',', $attrdata['tag']);
                    $addtags = array_diff($tids, $datatags);
                    foreach ($addtags as $v) {
                        if (!$v) continue;
                        $rtag = ['appid' => $attrdata['appid'], 'rid' => $rid, 'tid' => $v];
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    $ntags = array_unique(array_merge($datatags, $tids));
                    $attrs = [
                        'tag' => implode(',', $ntags)
                    ];
                    C::t('pichome_resources_attr')->update_by_rid($attrdata['appid'], $rid, $attrs);
                    $tagdatas = [];
                    foreach (DB::fetch_all("select tagname,tid from %t where tid in(%n)", array('pichome_tag', $ntags)) as $tv) {
                        $tagdatas[] = ['tid' => $tv['tid'], 'tagname' => $tv['tagname']];
                    }
                    $returndata = ['rid' => $rid, 'value' => $tagdatas,'flag'=>'tag'];
                } elseif ($getType == 2) {
                    $desc = getstr($content);
                    C::t('pichome_resources_attr')->update_by_rids($metadata['appid'], $rid, ['desc' => $desc]);
                    $returndata = ['rid' => $rid, 'value' => $desc,'flag'=>'desc'];
                }elseif($getType == 0){
                    $name = trim($this->name_filter($content));
                    $name = str_replace([',','，','.','。'],'',$name);
                    $name = getstr($name,30);
                    C::t('pichome_resources')->update_by_rids($metadata['appid'], $rid, ['name' => $name.'.'.$metadata['ext']]);
                    $returndata = ['rid' => $rid, 'value' => $name,'flag'=>'name'];
                }
                return $returndata;

            }


        } else {
            return true;
        }

    }
    public function name_filter($name)
    {
        return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
    }
    public function getAllowExts()
    {
        $this->allowExt = ['jpg', 'jpeg', 'png', 'webp'];
    }

    public function waitLock($processnameprefix){
        $locked = true;
        for($i=0;$i<2;$i++){
            $processname =$processnameprefix.$i;
            if (!\dzz_process::islocked($processname, 60)) {
                $locked=false;
                break;
            }
        }
        if($locked){
            sleep(3);
            for($i=0;$i<2;$i++){
                $processname = $processnameprefix.$i;
                if (!\dzz_process::islocked($processname, 60)) {
                    $locked=false;
                    break;
                }
            }
            if($locked){
                return ['error'=>'系统繁忙，请稍后再试'];
            }
        }
        return $processname;
    }


}