<?php
namespace dzz\ollama\classes;

require_once DZZ_ROOT . './dzz/ollama/class/class_ollamaApi.php';

use \C as C;
use \DB as DB;
use \IO as IO;


class ImagetagAnddesc
{

    public $allowExt = [];

    public function run(&$return, $extar = [])
    {
        $rid=$extar['rid'];
        $aiKey = $extar['aiKey'];
        if($aiKey != 'ollama::chatImage'){
            return $return;
        }

        if (!$rid){
            $return=['error' => lang('params_is_not_allow')];
            return $return;
        }
        $force = $extar['isforce'] ? $extar['isforce'] : 0;
        $tplid = $extar['tplid'] ? $extar['tplid'] : 0;
        $promptdata = C::t('#ollama#ollama_imageprompt')->fetch($tplid);

        if (!$promptdata){
            $return=['error' => 'prompt is not exists'];
            return $return;
        }
        $getType = $promptdata['cate'];

        //获取文件数据
        $metadata = IO::getMeta($rid);
        if (!$metadata){
            $return=['error' => 'file is not exists'];
            return $return;
        }
       /* if($promptdata['cate'] == 0){
            $appdata = C::t('pichome_vapp')->fetch($metadata['appid']);
            if($appdata['type'] == 1){
                $suffix =lang('ai_tag_template_end');
            }
        }
        */

        $imgurl = IO::gettmpThumb($rid,1024 ,1024,true, 1,  array('ext'=>'jpg'));

        $this->getAllowExts();
//        $thumbdata = DB::fetch_first("select * from %t where rid =%s", array('thumb_record', $rid));
//        if ($thumbdata['sstatus']) {
//            $imgurl = IO::getFileUri($thumbdata['spath']);
//        } else {
//            $imgurl = IO::getThumb($rid,'small',0,1, 1,1);
//            if (!$imgurl && in_array($metadata['ext'], $this->allowExt) && $metadata['filesize'] <= 10 * 1024 * 1024) {
//                if($metadata['aid'])$imgurl = IO::getStream('attach::' . $metadata['aid']);
//                else $imgurl = IO::getStream($rid);
//            }
//        }

        if ($imgurl) {
            if(!$metadata['aid']) $metadata['aid'] = 0;
            $setarr = ['aid' => $metadata['aid'], 'rid' => $rid, 'gettype' => $getType,'tplid'=>$tplid,'aikey'=>$aiKey];
            if ($data = C::t('ai_imageparse')->insertData($setarr)) {

                if ($data['isget'] && $data['data'] && !$force) {
                    $content = $data['data'];
                } else {
                    $params = ['imgurl' => $imgurl, 'promptdata' => $promptdata];
                    $params['processname'] = $this->waitLock('DZZ_OLLAMA_IAMGEPARSE');

                    $ollama = new \ollamaApi(null);
                    $aireturn = $ollama->getAiData($params);

                    if ($aireturn['error']){
                        $return=['error' => $aireturn['error']];
                        @unlink($imgurl);
                        return $return;
                    }
                    if($aireturn){
                        if($aireturn['prompt_eval_count']){
                            $tokendatas = [
                                'totaltoken'=>$aireturn['totaltoken'],
                                'uid'=>getglobal('uid'),
                                'app'=>'ollama',
                                'gettype'=>$getType,
                                'dateline'=>TIMESTAMP
                            ];
                            \Hook::listen('statsTokenuse',$tokendatas);
                        }
                        C::t('ai_imageparse')->update($data['id'], ['isget'=>1,'data'=>$aireturn['response'],'totaltoken'=>$aireturn['prompt_eval_count']]);
                         $content = $aireturn['response'];
                    }else{
                        C::t('ai_imageparse')->update($data['id'], ['isget'=>1,'data'=>'']);
                        $return=['error' => 'ollama::chatImage error'];
                        @unlink($imgurl);
                        return $return;
                    }

                }

                if ($getType == 1) {
                    runlog('content',$content);
                    $content=strip_tags($content);
                    $content = str_replace('、',',',$content);
                    $content = str_replace('，',',',$content);
                    $content = str_replace("\n",',',$content);
                    $content = str_replace("：",':',$content);
                    $content = preg_replace('/标签\d+:/', ',', $content);
                    $content = str_replace('标签:', ',', $content);
                    $tags = explode(',',$content);
                    $tags=array_unique($tags);

                    $tids = [];
                    foreach ($tags as $v) {

                        $v = trim($v);
                        $v = str_replace(['[',']',',','，','.','。','"',"\n"],'',$v);
                        $v = trim($v);
                        $v = preg_replace("/^\d+\s+/",'',$v);
                        $v = preg_replace("/^\d+/",'',$v);
                        $v = trim($v);
                        if ($v) {
                            if(mb_strlen($v)>6) continue;
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
                    $name = str_replace([',','，','.','。','、'],'',$name);
                    $name = getstr($name);
                    $name = rtrim($name,',');
                    C::t('pichome_resources')->update_by_rids($metadata['appid'], $rid, ['name' => $name.'.'.$metadata['ext']]);
                    $returndata = ['rid' => $rid, 'value' => $name,'flag'=>'name'];
                }
                @unlink($imgurl);
                $return=$returndata;
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
                return ['error'=>lang('system_busy')];
            }
        }
        return $processname;
    }


}