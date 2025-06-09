<?php
    if(!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
    class table_form_filedvals extends dzz_table
    {
        public function __construct()
        {

            $this->_table = 'form_filedvals';
            $this->_pk = 'id';
            $this->_pre_cache_key = 'form_filedvals';
            //$this->_cache_ttl = 3600;
            parent::__construct();
        }

        public function editData($editarr){
            foreach($editarr as $k=>$v){
                $tmparr = ['id'=>$k,'filedval'=>$v];

                Hook::listen('lang_parse',$tmparr,['setSelectOptionLangData']);

                if($tmparr){
                    parent::update($k,$tmparr);
                }
            }
            return true;
        }
        public function insertData($setarr,$filed,$istab = 1){
           $ids = [];
           if(!is_array($setarr)) $setarr = (array)$setarr;
            //获取当前语言
            $lang = getglobal('language');
            foreach($setarr as $v){
                if(!$v) continue;
                 if($data = DB::fetch_first("select id,hots,initial from %t where filedval = %s and filed = %s and lang = %s",array($this->_table,$v,$filed,$lang))){
                if(!$data){
                    $tmparr = [
                        'filedval'=>$v,
                        'lang'=>$lang,
                        'filed'=>$filed
                    ];

                    Hook::listen('lang_parse',$tmparr,['setSelectOptionLangData']);
                   if($tmparr){
                       $tmparr['initial'] = $this->getInitial($v);
                       parent::update($data['id'],$tmparr);
                       Hook::listen('updatefiledvalafter',$data['id']);
                   }

                }
               $ids[] = $data['id'];
            }else{
                $tmparr = [
                        'filedval'=>$v,
                        'lang'=>$lang,
                        'filed'=>$filed
                    ];
                    if($tmparr){
                        $tmparr['initial'] = $this->getInitial($v);
                        $ids[] =  parent::insert($tmparr,1);
                    }


              }
            }
            return $ids;
        }
        //删除选项时处理
        public function delete_by_id($ids){
            if(!is_array($ids)) $ids =(array)$ids;
            if($ids){
                //删除专辑统计数据
                C::t('#tab#tab_filedvalcount')->delete_by_valid($ids);
                //删除专辑关联数据
                C::t('#tab#tab_filedval')->delete_by_valid($ids);
                //删除语言包数据
                Hook::listen('lang_parse',$ids,['delSelectOptionLangData']);
                return parent::delete($ids);
            }
            return true;
        }
        public function fetch_by_id($ids){
            $returndata = [];
            if(!is_array($ids)) {
                $ids = intval($ids);
                $data = parent::fetch($ids);
                 Hook::listen('lang_parse',$data,['getSelectOptionLangData']);
                $returndata = $data;
            }else{

                foreach(DB::fetch_all("select id,filedval from %t where id in(%n)",array($this->_table,$ids))as $v){
                   Hook::listen('lang_parse',$v,['getSelectOptionLangData']);
                   $returndata[]  = $v;
                }
            }

            return $returndata;
        }
        public function fetch_ids_by_filed($filed){
            $ids = [];
            foreach(DB::fetch_all("select id from %t where filed = %s",array($this->_table,$filed)) as $v){
                $ids[] = $v['id'];
            }
            return $ids;
        }
        public function get_options_by_filed($filed,$limit=0){
            if($limit) $limitsql = " limit 0,$limit";
            else $limitsql = '';
            $options = [];                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                      
              foreach(DB::fetch_all("select id,filedval from %t where filed = %s $limitsql",array($this->_table,$filed)) as $v){
               $options[] = ['id'=>$v['id'],'name'=>$v['filedval']];
            }
            Hook::listen('lang_parse',$options,['getSelectOptionLangData',1]);
            return $options;
        }
        public function add_hots_by_id($id){
            $tagdata = parent::fetch($id);
            if(!$tagdata) return false;
            $setarr['hots'] = intval($tagdata['hots']) +1;
            return parent::update($id,$setarr);
        }


        public function getInitial($str){
            $py=pinyin::encode($str);
            $initial=substr($py,0,1);
            if(empty($initial) || !preg_match("/[A-Z]/i",$initial)){
                $initial='#';
            }
            return strtoupper($initial);
        }

    }