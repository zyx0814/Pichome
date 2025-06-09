<?php
global $idTypeMap;
$idTypeMap = [
    'file' => [
        'Key' => [
            'name' => ['table' => 'pichome_resources', 'id' => 'rid', 'inputtype' => 'input'],
            'desc' => ['table' => 'pichome_resources_attr', 'id' => 'rid', 'inputtype' => 'textarea']],
            'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 0,
    ],
    'vapp' => [
        'Key' => ['appname' => ['table' => 'pichome_vapp', 'id' => 'appid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 1,
    ],
    'tabfiled' => [
        'Key' => ['labelname' => ['table' => 'form_setting', 'id' => 'flag', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 2,
    ],
    'tabfiledval' => [
        'Key' => ['tabfiledval' => ['table' => 'tab_attr', 'id' => 'flag', 'inputtype' => 'type'],],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 3,
    ],
    'tabfiledcat' => [
        'Key' => ['catname' => ['table' => 'form_setting_filedcat', 'id' => 'id', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 4,
    ],
    'tabfiledoptions' => [
        'Key' => ['options' => ['table' => 'form_setting', 'id' => 'flag', 'inputtype' => 'options']],
        'pagestyle' => 'group', 'lablename' => 'lang', 'idtype' => 5,
    ],
    'tabcat' => [
        'Key' => ['catname' => ['table' => 'tab_cat', 'id' => 'cid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 6,
    ],
    'tabgroup' => [
        'Key' =>
            [
            'name' => ['table' => 'tab_group', 'id' => 'gid', 'inputtype' => 'input'],
            'aliasname' => ['table' => 'tab_group', 'id' => 'gid', 'inputtype' => 'input'],
            'aliascat' => ['table' => 'tab_group', 'id' => 'gid', 'inputtype' => 'input'],
            'aliasnumber' => ['table' => 'tab_group', 'id' => 'gid', 'inputtype' => 'input'],
            ],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 7,
    ],
    'tag' => [
        'Key' => [
            'tagname' => ['table' => 'pichome_tag', 'id' => 'tid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 8,
    ],
    'folder' => [
        'Key' => ['fname' => ['table' => 'pichome_folder', 'id' => 'fid', 'inputtype' => 'input'],
            'desc' => ['table' => 'pichome_folder', 'id' => 'fid', 'inputtype' => 'textarea']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 9,
    ],
    'taggroup' => [
        'Key' => ['catname' => ['table' => 'pichome_taggroup', 'id' => 'cid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 10,
    ],
    'banner' =>['Key' => ['bannername' => ['table' => 'pichome_banner', 'id' => 'id', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 11,
    ],
    'tabbanner' =>['Key' => ['name' => ['table' => 'tab_banner', 'id' => 'id', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 12,
    ],
    'alonepage' =>['Key' => ['pagename' => ['table' => 'pichome_templatepage', 'id' => 'id', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 13,
    ],
    'alonepagetag'=>['Key' => ['tagname' => ['table' => 'pichome_templatetag', 'id' => 'tid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 14,
    ],
    'alonepagedata' =>['Key' =>[
        'tdataname' => ['table' => 'pichome_templatetagdata', 'id' => 'id', 'inputtype' => 'input'],
        'tdata'=>['table' => 'pichome_templatetagdata', 'id' => 'id', 'inputtype' => 'type']
        ],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 15,
    ],
    'tab' => ['Key' => ['tabname' => ['table' => 'tab', 'id' => 'tid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 16,
    ],
    'collect' => ['Key' => ['name' => ['table' => 'pichome_collect', 'id' => 'clid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 17,
    ],
    'collectcat' => ['Key' => ['catname' => ['table' => 'pichome_collectcat', 'id' => 'cid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 18,
    ],
    'searchtemplate' => ['Key' => ['title' => ['table' => 'search_template', 'id' => 'tid', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 19,
    ],
    'fileCollect' => ['Key' => [
        'title' => ['table' => 'filecollect', 'id' => 'cid', 'inputtype' => 'input'],
        'description' => ['table' => 'filecollect', 'id' => 'cid', 'inputtype' => 'textarea']
    ],

        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 20,
    ],
    'selectoptions'=>['Key'=>[
        'selectoptions'=>['table'=>'form_filedvals','id'=>'id','inputtype'=>'type']], 'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 21,
    ],
    'selectoption'=>['Key'=>[
        'filedval'=>['table'=>'form_filedvals','id'=>'id','inputtype'=>'type']], 'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 22,
    ],
   /* 'filefiled' => [
        'Key' => ['labelname' => ['table' => 'form_setting', 'id' => 'flag', 'inputtype' => 'input']],
        'pagestyle' => 'single', 'lablename' => 'lang', 'idtype' => 23,
    ],*/
    'filefiledval' => [
        'Key' => ['filefiledval' => ['table' => 'pichome_resourcesatr', 'id' => 'flag', 'inputtype' => 'type'],],
        'pagestyle' => 'idgroup', 'lablename' => 'lang', 'idtype' => 23,
    ],


];
