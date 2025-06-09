<?php

namespace dzz\aiXhimage\classes;

use \C as C;

class ImageAIkey
{
    public function run(&$data, $params = [])
    {
        global $Types;

        $imageSetting = C::t('setting')->fetch('setting_xhImageDataSetting', true);
        if (!$imageSetting['status']) {
            return true;
        }
        $type = $params['type'];
        $imagetypes = array_merge($Types['commonimage'], $Types['image']);
        $gifKey = array_search('gif', $imagetypes);
        unset($imagetypes[$gifKey]);
        $appdata = C::t('pichome_vapp')->fetch($params['appid']);
        if (($type == 'file' || $type == 'files')&& array_intersect($imagetypes, $data['exts'])) {
            $rids = $data['rid'];
            if (empty($rids)) return true;
            if (!is_array($rids)) $rids = [$rids];
            //判断是否开启eagle同步
            $eagleupdate = false;
            \Hook::listen('checkeaglesync',$eagleupdate);
            $allowvapptype = [1,3];
            if($eagleupdate) $allowvapptype[] = 0;
            //查询所有开启的模板
            if(in_array($appdata['type'],$allowvapptype)){
                $this->getAiKey($data,$type,$appdata);
            }
            if ($type == 'file') {
                $data['Aichat'] = [
                    'url' => getglobal('siteurl') . 'index.php?mod=aiXhimage&op=chat',
                    'recordurl' => getglobal('siteurl') . 'index.php?mod=aiXhimage&op=chat&do=getHistory',
                    'delhistoryurl' => getglobal('siteurl') . 'index.php?mod=aiXhimage&op=chat&do=clearchat',
                    'type' => 'image',
                    'params' => 'rid'
                ];
            }
            return false;
        }
        elseif($type == 'folder'){
            if(empty($data['fid'])) return true;
            $this->getAiKey($data,$type,$appdata);
            return false;
        }elseif($type == 'vapp'){
            if(empty($data['appid'])) return true;
            $this->getAiKey($data,$type,$appdata);
            return false;
        }
    }

    public function getAiKey(&$data,$type,$appdata){
        $apptype = $appdata['type'];
        $apppath = $appdata['path'];
        $writeable = is_writeable($apppath) ? true :false;
        $promptdata = C::t('#aiXhimage#ai_xhimageprompt')->fetchPromptByStatus();
        $tplsdata = [];
        foreach ($promptdata as $prompt) {
            $tplsdata[$prompt['cate']][] = ['name' => $prompt['name'], 'tplid' => $prompt['id'], 'prompt' => $prompt['prompt']];
        }
         ksort($tplsdata);
        $tplnamearr = [
            ['flag'=>'name','lable'=>lang('ai_rename')],
            ['flag'=>'tag','lable'=>lang('ai_tagging')],
            ['flag'=>'desc','lable'=>lang('ai_set_desc')]
        ];

        $filedkey = [];
        foreach($tplsdata as $key=>$tpls){
            if($apptype == 1 && $key == 0 && (!$writeable || getglobal('config/allowDirectoryEditFilename'))) continue;
                $filedkey[$tplnamearr[$key]['flag']] = ['tpls'=>$tpls,'lablename'=>$tplnamearr[$key]['lable'],'flag'=>$tplnamearr[$key]['flag']];

        }
        $params = [
            'file'=>'rid',
            'folder'=>'fid',
            'vapp'=>'appid'
        ];
        if(!empty($filedkey)){
            $data['Aikey'] = [
                'key' => 'aiXh::chatImage',
                'name' => lang('xh_iamge_parse'),
                'params' => $params[$type],
                'type'=>$type,
                'filedkey' =>$filedkey
            ];
        }
    }
}