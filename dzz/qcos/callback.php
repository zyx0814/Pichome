<?php
    ignore_user_abort(true);
    @set_time_limit(0);
    $resultdata = file_get_contents("php://input");
    $xmlstring = simplexml_load_string($resultdata, 'SimpleXMLElement', LIBXML_NOCDATA);
    $value_array = json_decode(json_encode($xmlstring), true);
    //获取任务id
    $jobid = $value_array['JobsDetail']['JobId'];
    //如果任务运行成功
    if($value_array['JobsDetail']['Code'] == 'Success'){
            //如果是视频转换任务
        $jobid = $value_array['JobsDetail']['JobId'];
        $path = $value_array['JobsDetail']['Operation']['MediaResult']['OutputFile']['ObjectName'];
        $rid= DB::result_first("select rid from %t where jobid = %s",array('video_record',$jobid));
        $apppath = DB::result_first("select v.path from %t r left join %t v on r.appid=v.appid where r.rid = %s",array('pichome_resources','pichome_vapp',$rid));
        $patharr = explode('/', $apppath);
        $bz = $patharr[0];
        DB::update('video_record',array('path'=>$bz.'/'.$path,'status'=>2),array('jobid'=>$jobid));
    }else{
        DB::update('video_record',array('status'=>-1),array('jobid'=>$jobid));
    }