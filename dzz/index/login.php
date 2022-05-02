<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2020/9/2
 * Time: 15:53
 */
$result = Hook::listen("check_login");
exit(json_encode($result[0]));