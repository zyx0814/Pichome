<?php

class ollamaApi {

    private $apikey='';//apikey
    private $apiurl='http://localhost:11434/';//数据接收计数器

    private $params=[];
    private $data_buffer='';
    private $config=null;
    public function __construct($config=null) {
      if(empty($config)){
          $config=C::t('setting')->fetch('setting_ollama',true);
      }

      if($config['apikey']) $this->apikey = $config['apikey'];

      if($config['apiurl']) $this->apiurl = $config['apiurl'];
      if(!preg_match("/\/$/i",$this->apiurl)) $this->apiurl .='/';
      $this->config=$config;
    }
    //列出本地可用的模型。
    public function modelList(){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $url=$this->apiurl.'api/tags';
        $result=$this->request($url,'GET',array(),$headers);
        $data=array();
        if($result['models']){
            foreach ($result['models'] as $key=>$value){
                $data[$value['name']]=$value;
            }
        }else{
            runlog('ollama',$result['error']);
        }
        return $data;
    }
    //列出本地模型详细信息。
    public function modelDetail($model){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model
        );
        $url=$this->apiurl.'/api/show';
        $result=$this->request($url,'POST',json_encode($params),$headers);

        return $result;
    }
    //删除一个模型。
    public function modelDelete($model){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model
        );
        $url=$this->apiurl.'/api/delete';
        $result=$this->request($url,'DELETE',json_encode($params),$headers);
        if($result['error']){
            return false;
        }
        return true;
    }

    //删除一个模型。
    public function modelPull($model){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model,
            'stream'=>false,
            'insecure'=>true
        );
        $url=$this->apiurl.'/api/pull';
        $result=$this->request($url,'POST',json_encode($params),$headers);
        if($result['status']=='success'){
            return true;
        }
        return false;
    }
    //生成模型的嵌入向量
    public function embed($model,$input=array()){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model,
            'input'=>$input,
            'truncate'=>true
        );
        $url=$this->apiurl.'/api/embed';
        $result=$this->request($url,'POST',json_encode($params),$headers);
        if(isset($result['embeddings'])){
            return $result['embeddings'];
        }
        return $result;
    }
    //生成chat completion
    public function chat($model,$messages=array(),$stream=true,$options=array()){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model,
            'messages'=>$messages,
            'stream'=>$stream
        );
        if($options){
            $params['options']=$options;
        }

        $url=$this->apiurl.'api/chat';
        if($stream){
            $result=$this->streamRequest($url,$headers,json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));

        }else{
            $result=$this->request($url,'POST',json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),$headers);
        }

        return $result;
    }
    public function generate($model,$prompt='',$images=[],$stream=false,$options=array()){
        $headers = array(
            'Content-Type: application/json'
        );
        if($this->apikey){
            $headers[]= 'Authorization: Bearer '.$this->apikey;
        }
        $params=array(
            'model'=>$model,
            'prompt'=>$prompt,
            'stream'=>$stream
        );
        if($images){
            $params['images']=$images;
        }
        if($options){
            $params['options']=$options;
        }

        $url=$this->apiurl.'api/generate';
        if($stream){
            $result=$this->streamRequest($url,$headers,json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        }else{
           // echo json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
            $result=$this->request($url,'POST',json_encode($params,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),$headers);
        }
        return $result;
    }
    public function getAiData($params){
        $suffix='';
        $imgurl=$params['imgurl'];
        $promptdata=$params['promptdata'];
        $prompts=$promptdata['prompts'];
        if($promptdata['cate'] == 1){
            $suffix=lang('ai_tag_template_end');
        }
        //获取第一个prompt
        $prompt0=array_shift($prompts);
        $prompt1=$prompts[0];
        if($prompt1['model']){
            $suffix0='';
        }else{
            $suffix0=$suffix;
        }
        $imagebase64=$this->getImageData($imgurl);

        $ret0=$this->generate($prompt0['model'],$prompt0['prompt'].$suffix0,[$imagebase64]);
        unset($ret0['context']);
        $ret0['totaltoken']=($ret0['prompt_eval_count']+$ret0['eval_count']);
        runlog('ollama',json_encode($prompt0,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) .'==='.json_encode($ret0,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
        if($ret0['error']){
            \dzz_process::unlock($params['processname']);
            return $ret0;
        }
        //其他优化代码
        $prefix='"'.$ret0['response'].'" ';
        foreach($prompts as $prompt){

            $ret=$this->generate($prompt['model'],$prefix.$prompt['prompt'].$suffix);
            unset($ret['context']);
            $prompt['prompt']=$prefix.$prompt['prompt'].$suffix;
            runlog('ollama',$prefix.json_encode($prompt,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) .'==='.json_encode($ret,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            if($ret['error']) continue;
            $prefix='"'.$ret['response'].'"';
            $ret0['eval_count']+=$ret['eval_count'];
            $ret0['totaltoken']+=($ret['prompt_eval_count']+$ret['eval_count']);
            $ret0['response']=$ret['response'];
        }
        \dzz_process::unlock($params['processname']);
        return $ret0;
    }
    public function getChatData($params){
        $this->params=$params;

        $messages=$this->getMessages($params);

        $model=$this->config['chatModel'];

        if(empty($model)){
            return array('error'=>'Chat Model missing');
        }
        $ret=$this->chat($model,$messages);
        return $ret;
    }
    public function callback($ch,$data) {

//        if(empty($data)){
//            return strlen($data);
//        }
        $result = json_decode($data, TRUE);

        if(!is_array($result)){

            \dzz_process::unlock($this->params['processname']);
            $this->end($result);
            return strlen($data);
        }
        if($result['done']){//已经全部结束

            $questions = [
                [
                    'content'=>json_encode([
                         "role"=>"assistant",
                         "content"=>$this->data_buffer,
                    ],JSON_UNESCAPED_UNICODE),
                    'totaltoken'=>$result['prompt_eval_count']+$result['eval_count']
                ]
            ];
            $this->insetMessageData($questions);

            \dzz_process::unlock($this->params['processname']);
            $this->end();
            return strlen($data);
        }
        $content = $result['message']['content'];
        $this->data_buffer.=$content;
        $this->write($content);
        return strlen($data);
    }
    public static function write($content = NULL, $flush=TRUE){
        if($content != NULL){
            echo 'data: '.json_encode(['time'=>date('Y-m-d H:i:s'), 'content'=>$content], JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
        }

        if($flush){
            flush();
        }
    }

    public static function end($content = NULL){
        echo "message: close" . PHP_EOL;
        if(!empty($content)){
            echo 'data: '.json_encode(['time'=>date('Y-m-d H:i:s'), 'content'=>$content], JSON_UNESCAPED_UNICODE).PHP_EOL.PHP_EOL;
        }
        echo 'retry: 86400000'.PHP_EOL;
        echo 'event: close'.PHP_EOL;
        echo 'data: Connection closed'.PHP_EOL.PHP_EOL;
        flush();
    }


    public function getImageData($imgUrl)
    {
        $imgedata = file_get_contents($imgUrl);
        return base64_encode($imgedata);
    }
    private function getMessages($params)
    {

        $messages=[];
        $messagedatas = $this->getMessageData($params);
        if ($messagedatas) {
            foreach ($messagedatas as $v) {
                $messagedata = json_decode($v, true);
                $messages[] = $messagedata;
            }
            $newtext = [
                "role" => "user",
                "content" => $params['question'],
            ];
            $messages[] = $newtext;
            $questions = [
                ['content'=> json_encode($newtext)]
            ];
            $this->insetMessageData($questions);
        } else {
            $newtext = [
                            "role" => "user",
                            "content" =>  $params['question'],
                      ];
            if($params['imgurl']){
                if($imagedata =  $this->getImageData($params['imgurl'])){
                    $newtext['images']=[$imagedata];
                }
            }
            $messages[]=$newtext;
            $questions = [
                ['content'=> json_encode($newtext)]
            ];
            $this->insetMessageData($questions);
        }

        return $messages;

    }

    private function getMessageData($params)
    {   $messagedatas = [];
        foreach(C::t('#ollama#ollama_chat')->fetchContentByIdvalue($params['idval'],$params['idtype']) as $v){
            $messagedatas[] = $v['content'];
        }
        return $messagedatas;
    }

    private function insetMessageData($messagedatas){

        foreach($messagedatas as $v){

            $cdata = json_decode($v['content'],true);
            if(!$cdata['role']) continue;
            $setarr = [
                'idval'=>$this->params['idval'],
                'idtype'=>$this->params['idtype'],
                'role'=>$cdata['role'],
                'content'=>$v['content'],
                'totaltoken'=>isset($v['totaltoken']) ? intval($v['totaltoken']):0
            ];
            C::t('#ollama#ollama_chat')->insertData($setarr);
        }

    }
    public  function request($url,$method='GET',$param = array(),$headers=array(),$raw=0)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        if($method=='POST' && $param) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 120);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1200);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
       // curl_setopt($ch, CURLINFO_HEADER_OUT , 1);
      //  curl_setopt($ch, CURLOPT_HEADER  , true);
        $result = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($status == 0) {
            $ret = array('error'=> curl_error($ch));
        } else {
            $ret = $raw?$result:json_decode($result, true);

        }
        curl_close($ch);

        return $ret;
    }

    public function streamRequest(string $url, $headers = [], $postData = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); // 不将响应保存为字符串，直接处理
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 注意：在生产环境中应启用 SSL 验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // 注意：同上
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, is_array($postData) || !empty($postData));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this,'callback']);


        // 执行请求并获取响应
        curl_exec($ch);

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            return array('error'=>curl_error($ch));
        }
        // 关闭 cURL 句柄
        curl_close($ch);
        return true;
    }

}


