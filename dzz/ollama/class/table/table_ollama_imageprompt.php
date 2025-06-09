<?php
if (!defined('IN_OAOOA')) {
    exit('Access Denied');
}

class table_ollama_imageprompt extends dzz_table
{
    public function __construct()
    {

        $this->_table = 'ollama_imageprompt';
        $this->_pk = 'id';
        parent::__construct();
    }
    public function fetch($id, $force_from_db = false){
        $data=parent::fetch($id);
        if($data['prompts']) $data['prompts']=json_decode($data['prompts'],true);
        return $data;
    }
    public function insertData($setarr)
    {
        $setarr['name'] = getstr($setarr['name'],60);
        if(DB::result_first("select id from %t where name = %s and cate = %d",[$this->_table,$setarr['name'],$setarr['cate']])){
            return array('error'=>'该名称已存在');
        }else{
            if($setarr['prompts']) $setarr['prompts']=json_encode($setarr['prompts']);
            return parent::insert($setarr,1);
        }

    }

    public function sortByIds($ids){
        if(!is_array($ids)) $ids = array($ids);
        foreach($ids as $k=>$id){
            parent::update($id,array('disp'=>$k),1);
        }
    }

    public function  setStatusById($id,$status){
        return parent::update($id,array('status'=>$status),1);
    }
    public function setDefaultByIdandCate($id,$cate){
        if(DB::update($this->_table,array('isdefault'=>0),DB::field('cate',$cate).' AND '.DB::field('isdefault',1))){
            return parent::update($id,array('isdefault'=>1),1);
        }
    }
    public function editById($id,$setarr){
        $setarr['name'] = getstr($setarr['name'],60);
        if(DB::result_first("select id from %t where name = %s and id != %d",[$this->_table,$setarr['name'],$id])){
            return array('error'=>'该名称已存在');
        }else{
            if(is_array($setarr['prompts'])) $setarr['prompts']=json_encode($setarr['prompts']);
            parent::update($id,$setarr);
            return true;
        }
    }

    public function fetchPromptByStatus(){
        $data=array();
        foreach(DB::fetch_all("select * from %t where status = 1 order by disp asc ",[$this->_table]) as $value){
            if($value['prompts']) $value['prompts']=json_decode($value['prompts'],true);
            else $value['prompts']=array(
                array(
                    'prompt'=>$value['prompt'],
                    'model'=>''
                ),
                array(
                    'prompt'=>'',
                    'model'=>''
                )
            );
            $data[]=$value;
        }
        return $data;
    }
    public function fetchPromptByCate($cate){
        $data=array();
        foreach(DB::fetch_all("select * from %t where cate = %d order by  disp asc ",[$this->_table,$cate]) as $value){
            if($value['prompts']) $value['prompts']=json_decode($value['prompts'],true);
            else $value['prompts']=array(
                array(
                    'prompt'=>$value['prompt'],
                    'model'=>''
                ),
                array(
                    'prompt'=>'',
                    'model'=>''
                )
            );
            $data[]=$value;
        }
        return $data;
    }

    public function deleteById($id){

        return parent::delete($id);
    }


}