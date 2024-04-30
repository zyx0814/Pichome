<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}
ignore_user_abort(true);
@set_time_limit(0);
@ini_set('max_execution_time', 0);
$locked = true;
$processnamepre = 'DZZ_LOCK_DOAITASK';
$processnum = 2;
for($i=0;$i<$processnum;$i++){
    $processname = $processnamepre.$i;
    if (!dzz_process::islocked($processname, 60*5)) {
        $locked=false;
        break;
    }
}
$limit = 100;
//dzz_process::unlock($processname);
if ($locked) {
    exit(json_encode(array('error' => '进程已被锁定请稍后再试')));
}
$limit = 100;
foreach (DB::fetch_all("select * from %t where 1 limit 0,$limit ", array('ai_task')) as $v) {
    if ($v['aikey'] == 'aiXh::chatImage') {
        require_once DZZ_ROOT . './dzz/aiXhimage/class/xhChat.php';
        $tplid = $v['tplid'];
        $promptdata = C::t('#aiXhimage#ai_xhimageprompt')->fetch($tplid);
        if (!$promptdata) continue;
        $getType = $promptdata['cate'];
        if ($promptdata['cate'] == 1) {
            $question = $promptdata['prompt'] . '。返回结果的格式为“标签1,标签2,标签3”，其中标签之间使用逗号分割。';
        } else {
            $question = $promptdata['prompt'];
        }
        $rid = $v['rid'];
        //获取文件数据
        $metadata = IO::getMeta($rid);
        if (!$metadata) {
            C::t('ai_task')->delete($v['id']);
            continue;
        }
        $imgurl = FALSE;
        $thumbdata = DB::fetch_first("select * from %t where rid =%s", array('thumb_record', $rid));
        if ($thumbdata['sstatus']) {
            $imgurl = IO::getFileuri($thumbdata['spath']);
        } else {
            $imgurl = IO::getThumb($rid,'small',0,1, 1,1);
            if (!$imgurl && in_array($metadata['ext'], ['jpg', 'jpeg', 'png', 'webp']) && $metadata['filesize'] <= 10 * 1024 * 1024) {
                if($metadata['aid'])$imgurl = IO::getStream('attach::' . $metadata['aid']);
                else $imgurl = IO::getStream($rid);
            }
        }

        if ($imgurl) {
            if(!$metadata['aid']) $metadata['aid'] = 0;
            $setarr = ['aid' => $metadata['aid'], 'rid' => $rid, 'gettype' => $getType, 'tplid' => $tplid, 'aikey' => $v['aikey']];
            $cachedata = C::t('ai_imageparse')->insertData($setarr);
            if ($cachedata) {
                if ($cachedata['isget'] && $cachedata['data']) {
                    $content = $cachedata['data'];
                }
                else {
                    $params = ['imageurl' => $imgurl, 'question' => $question];
                    $return = waitLock('DZZ_LOCK_XHIAMGEPARSE');
                    if (isset($return['error'])) {
                        continue;
                    } else {
                        $params['processname'] = $return;
                    }
                    $chatclinet = new xhChat();
                    $aireturn = $chatclinet->getApiData('aiXh::chatImage', $params);
                    if ($aireturn['error_msg']) return ['error' => $aireturn['error_msg']];
                    if ($aireturn['result']) {
                        if ($aireturn['totaltoken']) {
                            $tokendatas = [
                                'totaltoken' => $aireturn['totaltoken'],
                                'uid' => getglobal('uid'),
                                'app' => 'aiXhimage',
                                'gettype' => $getType,
                                'dateline' => TIMESTAMP
                            ];
                            \Hook::listen('statsTokenuse', $tokendatas);
                        }
                        C::t('ai_imageparse')->update($cachedata['id'], ['isget' => 1, 'data' => $aireturn['result'], 'totaltoken' => $aireturn['totaltoken']]);
                        $content = $aireturn['result'];
                    } else {
                        C::t('ai_imageparse')->update($cachedata['id'], ['isget' => 1, 'data' => '']);
                        C::t('ai_task')->delete($v['id']);
                        continue;
                    }

                }

                if ($getType == 1) {
                    $tags = explode('，', $content);
                    $tids = [];
                    foreach ($tags as $tagv) {
                        $tagv = preg_replace('/标签\d+[:：]/', '', $tagv);
                        $tagv = trim($tagv);
                        $tagv = str_replace([',','，','.','。'],'',$tagv);
                        $tagv = trim($tagv);
                        if ($tagv) {
                            $tids[] = C::t('pichome_tag')->insert($tagv, 1);
                        }
                    }
                    $attrdata = C::t('pichome_resources_attr')->fetch($rid);
                    $datatags = explode(',', $attrdata['tag']);
                    $addtags = array_diff($tids, $datatags);
                    foreach ($addtags as $vtid) {
                        if (!$vtid) continue;
                        $rtag = ['appid' => $attrdata['appid'], 'rid' => $rid, 'tid' => $vtid];
                        C::t('pichome_resourcestag')->insert($rtag);
                    }
                    $ntags = array_unique(array_merge($datatags, $tids));
                    $attrs = [
                        'tag' => implode(',', $ntags)
                    ];
                    C::t('pichome_resources_attr')->update_by_rid($attrdata['appid'], $rid, $attrs);
                    C::t('ai_task')->delete($v['id']);
                } elseif ($getType == 2) {
                    $desc = getstr($content);
                    C::t('pichome_resources_attr')->update_by_rids($metadata['appid'], $rid, ['desc' => $desc]);
                    C::t('ai_task')->delete($v['id']);
                } elseif ($getType == 0) {
                    $name = trim(name_filter($content));
                    $name = str_replace([',','，','.','。'],'',$name);
                    $name = getstr($name,30);
                    C::t('pichome_resources')->update_by_rids($metadata['appid'], $rid, ['name' => $name.'.'.$metadata['ext']]);
                    C::t('ai_task')->delete($v['id']);
                }

            }
        } else {
            C::t('ai_task')->delete($v['id']);
        }
    }
}
function waitLock($processnameprefix)
{
    $locked = true;
    for ($i = 0; $i < 2; $i++) {
        $processname = $processnameprefix . $i;
        if (!\dzz_process::islocked($processname, 60)) {
            $locked = false;
            break;
        }
    }
    if ($locked) {
        sleep(3);
        for ($i = 0; $i < 2; $i++) {
            $processname = $processnameprefix . $i;
            if (!\dzz_process::islocked($processname, 60)) {
                $locked = false;
                break;
            }
        }
        if ($locked) {
            return ['error' => '系统繁忙，请稍后再试'];
        }
    }
    return $processname;
}

function name_filter($name)
{
    return str_replace(array('/', '\\', ':', '*', '?', '<', '>', '|', '"', "\n"), '', $name);
}

dzz_process::unlock($processname);
if (DB::result_first("select count(id) from %t where 1 ", array('ai_task'))) {
    dfsockopen(getglobal('localurl') . 'misc.php?mod=doaitask', 0, '', '', false, '', 0.1);
} else {
    exit('success'.$i);
}