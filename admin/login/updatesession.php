<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/7
 * Time: 16:24
 */
$return = updatesession();
exit(json_encode(array('status'=>$return)));