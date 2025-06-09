<?php
    if (!defined('IN_OAOOA')) {
        exit('Access Denied');
    }
 
    $ismobile = helper_browser::ismobile();
    if ($ismobile) {
        // include template($themedata['themefolder'].'/mobile/page/filelist');
    } else {
        include template('libraryview/pc/page/index');
    }