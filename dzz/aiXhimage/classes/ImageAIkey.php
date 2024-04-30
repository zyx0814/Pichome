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
            //查询所有开启的模板
            if($appdata['type'] == 1 || $appdata['type'] == 3){
                $this->getAiKey($data,$type,$appdata['type']);
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
            $this->getAiKey($data,$type,$appdata['type']);
            return false;
        }elseif($type == 'vapp'){
            if(empty($data['appid'])) return true;
            $this->getAiKey($data,$type,$appdata['type']);
            return false;
        }
    }

    public function getAiKey(&$data,$type,$apptype){
        $promptdata = C::t('#aiXhimage#ai_xhimageprompt')->fetchPromptByStatus();
        $tplsdata = [];
        foreach ($promptdata as $prompt) {
            $tplsdata[$prompt['cate']][] = ['name' => $prompt['name'], 'tplid' => $prompt['id'], 'prompt' => $prompt['prompt']];
        }
         ksort($tplsdata);
        $tplnamearr = [
            ['flag'=>'name','lable'=>'AI改文件名'],
            ['flag'=>'tag','lable'=>'AI打标签'],
            ['flag'=>'desc','lable'=>'AI写描述']
        ];

        $filedkey = [];
        foreach($tplsdata as $key=>$tpls){
            if($apptype == 1 && $key == 0) continue;
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
                'name' => '星火图片理解',
                'params' => $params[$type],
                'type'=>$type,
                'filedkey' =>$filedkey
            ];
        }
    }
}