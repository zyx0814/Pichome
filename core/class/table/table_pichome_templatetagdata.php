<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class  table_pichome_templatetagdata extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'pichome_templatetagdata';
        $this->_pk = 'id';
        $this->_pre_cache_key = 'pichome_templatetagdata';
        //$this->_cache_ttl = 3600;
        parent::__construct();
    }
    //新建或修改单页
    public function insertdata($setarr){
        $olddata = [];
        $id = 0;
        if($setarr['id']) {
            $id = $setarr['id'];
            unset($setarr['id']);
            $olddata = parent::fetch($id);
        }

        $type = $setarr['type'];
        unset($setarr['type']);
        switch ($type){
            case  'contact':
            case  'rectangle_rec':
            case  'link':
            case  'manual_rec':
            case  'banner':
            case 'slide':
                $naids =  [];
                foreach($setarr['tdata'] as $v){
                    $naids[] = $v['aid'];
                }
                if($olddata){
                    $odata = unserialize($olddata['tdata']);
                    $oaids = [];
                    foreach($odata as $idata){
                        $oaids[] = $idata['aid'];
                    }


                    $delaids = array_diff($oaids,$naids);
                    foreach($delaids as $v){
                        C::t('attachment')->delete_by_aid($v['aid']);
                    }
                    $naids = array_diff($naids,$oaids);
                }
                C::t('attachment')->addcopy_by_aid($naids);
                $setarr['tdata'] = serialize($setarr['tdata']);
                break;
            case 'rich_text':

                $setarr['tdata'] = $this->getcontentdata($setarr['tdata'],$olddata['tdata']);
                break;
            default :
                $naids =  [];
                foreach($setarr['tdata'] as $v){
                    $naids[] = $v['aid'];
                }
                if($olddata){
                    $odata = unserialize($olddata['tdata']);
                    $oaids = [];
                    foreach($odata as $idata){
                        $oaids[] = $idata['aid'];
                    }


                    $delaids = array_diff($oaids,$naids);
                    foreach($delaids as $v){
                        C::t('attachment')->delete_by_aid($v['aid']);
                    }
                    $naids = array_diff($naids,$oaids);
                    if($naids)  C::t('attachment')->addcopy_by_aid($naids);
                }
                $setarr['tdata'] = serialize($setarr['tdata']);
                break;

        }

        if($id){
            parent::update($id,$setarr);
            return $id;
        }else{
            return parent::insert($setarr,1);
        }
    }
    public function parserichtextdata($data){
        $pattern = "/(https?:\/\/)?\w+\.\w+\.\w+\.\w+?(:[0-9]+)?\/index\.php\?mod=io&amp;op=getfileStream&amp;path=(.+)/";
        $data= preg_replace_callback($pattern,function($matchs){

            return 'index.php?mod=io&op=getfileStream&path='.$matchs[3];

        },$data);

        $data= preg_replace_callback('/path=(\w+)&amp;aflag=(attach::\d+)/',function($matchs){
            if(isset($matchs[2])){
                return 'path='.dzzencode($matchs[2]);
            }

        },$data);

        return $data;
    }
    public function fetch_data_by_tidandtagtype($tid,$tagtype){
        $tagdata = [];
        foreach(DB::fetch_all("select * from %t where tid = %d order by disp asc",[$this->_table,$tid]) as $v){

            if($tagtype == 'rich_text'){

                $v['tdata'] = $this->parserichtextdata($v['tdata']);
            }else{
                $v['tdata'] = unserialize($v['tdata']);

                foreach($v['tdata'] as $k=>$val){
                    if($val['aid']){
                        $v['tdata'][$k]['imgurl'] =IO::getFileUri('attach::'.$val['aid']);
                    }
                    if(!$val['link']) $val['tdata'][$k]['url'] =  $val['linkval'] ? $val['linkval']:'';
                    else{
                        switch ($val['link']){
                            case 1:
                                $url = 'index.php?mod=pichome&op=fileview#appid='.$val['linkval'];
                                break;
                            case 2:
                                $url =  'index.php?mod=alonepage&op=view#id='.$val['linkval'];
                                break;
                            case 3:
                                $bdata = C::t('pichome_banner')->fetch($val['linkval']);
                                $url = ($bdata['btype'] == 3) ? $bdata['bdata']:'index.php?mod=banner&op=index#id='.$bdata['bdata'];
                                break;
                        }
                        if(getglobal('setting/pathinfo')) $path = C::t('pichome_route')->feth_path_by_url($url);
                        else $path = '';
                        if($path){
                            $v['tdata'][$k]['url'] = getglobal('siteurl').$path;
                        }else{
                            $v['tdata'][$k]['url'] = getglobal('siteurl').$url;
                        }
                    }
                }

            }

            $v['tdid'] = $v['id'];
            unset($v['id']);
            $tagdata[] = $v;
        }
        return $tagdata;
    }
    public function getcontentdata($data,$odata){
        global $naids;
        $data = str_replace(getglobal('siteurl'),'',$data);
        $naids= [];
        $data= preg_replace_callback('/path=(\w+)/',function($matchs){
            global $naids;
            if($aidstr = dzzdecode($matchs[1])){
                $aid = str_replace('attach::','',$aidstr);
                $naids[] = $aid;
                return 'path='.$matchs[1].'&amp;aflag='.$aidstr;
            }

        },$data);
        //旧数据copy数减一
        if($odata){
            preg_match_all('/attach::\d/',$data,$match);
            foreach($match[0] as $v){
                $oaid = str_replace('attach::','',$v);
                C::t('attachment')->delete_by_aid($oaid);
            }
        }
        C::t('attachment')->addcopy_by_aid($naids);
        return $data;

    }

    public function delete_by_tid($tid){
        $ids = [];
        foreach(DB::fetch_all("select id from %t where tid = %d",[$this->_table,$tid]) as $v){
            $ids[] = $v['id'];
        }
        return parent::delete($ids);
    }
    public function delete_by_id($id){
        if(!is_array($id)) $id = (array)$id;
        foreach($id as $v){
            $cachename = 'templatetagdata_'.$v;
            C::t('cache')->delete_cachedata_by_cachename($cachename);
            parent::delete($v);
        }
        return true;
    }

}