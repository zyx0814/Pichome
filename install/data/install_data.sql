--
-- 转存表中的数据 `dzz_app_market`
-- 
INSERT INTO `dzz_app_market` (`appid`, `mid`, `appname`, `appico`, `appdesc`, `appurl`, `appadminurl`, `noticeurl`, `dateline`, `disp`, `vendor`, `haveflash`, `isshow`, `havetask`, `hideInMarket`, `feature`, `fileext`, `group`, `orgid`, `position`, `system`, `notdelete`, `open`, `nodup`, `identifier`, `app_path`, `available`, `version`, `upgrade_version`, `check_upgrade_time`, `extra`, `uids`, `showadmin`) VALUES
(1, 0, 'pichome', 'appico/201712/21/161251dpmgqozr0kdk9rqz.png', '支持将服务器中eagle文件包导入到系统指定目录', '{dzzscript}?mod=pichome&op=index', null, '', '0', '0', '乐云网络', '0', '1', '0', '0', '', 'eaglepack,zip', '1', '0', '1', '0', '1', '0', '0', 'pichome', 'dzz', '1', '2.01', '', '0', 'a:2:{s:11:\"installfile\";s:11:\"install.php\";s:13:\"uninstallfile\";s:13:\"uninstall.php\";}', null, '0'),
(2, 2, '机构用户', 'appico/201712/21/131016is1wjww2uwvljllw.png', 'Dzz机构用户管理', '{adminscript}?mod=orguser', '', '', 1377753015, 2, '欧奥图文档', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'orguser', 'admin', 1, '2.0', '', 20171211, '', '', 0),
(9, 9, '系统工具', 'appico/201712/21/160537cikgw2v6s6z4scuv.png', '系统维护相关工具集合，如：更新缓存、数据库备份，计划任务，在线升级等', '{adminscript}?mod=system', '', '', 1377677136, 9, '欧奥图文档', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'system', 'admin', 1, '2.0', '', 20171115, '', '', 0);

-- 转存表中的数据 `dzz_cron`
--

INSERT INTO `dzz_cron` (`cronid`, `available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
(1,	1,	'system',	'每月通知清理',	'cron_clean_notification_month.php',	1609448401,	1612126800,	-1,	1,	5,	'0'),
(2,	1,	'system',	'每周清理缓存文件',	'cron_cache_cleanup_week.php',	1609707601,	1610312400,	1,	-1,	5,	'0'),

 (3, 1, 'system', '定时删除删除状态库', 'cron_pichome_delete.php', '1629430582', '1629430800', '-1', '-1', '-1', '0	5	10	15	20	25	30	35	40	45	50	55'),
(4, 1, 'system', '定时更新pichome库内文件数据', 'cron_pichome_updatelibrary_file.php', '1629430723', '1629432000', '-1', '-1', '-1', '0'),
(5, '1', 'system', '定时更新热搜', 'cron_cache_pichome_searchhot.php', '1629874690', '1629925200', '-1', '-1', '5', '0'),
(6, 1, 'system', '定时获取图片颜色', 'cron_pichome_getimagecolor.php', '1629430582', '1629430800', '-1', '-1', '-1', '0	5	10	15	20	25	30	35	40	45	50	55'),
(7, 1, 'system', '定时获取音视频信息', 'cron_pichome_getvideoinfo.php', '1629430582', '1629430800', '-1', '-1', '-1', '0	5	10	15	20	25	30	35	40	45	50	55'),
(8, 1, 'system', '定时获取音视频缩略图', 'cron_pichome_getvideothumb.php', '1629430582', '1629430800', '-1', '-1', '-1', '0	5	10	15	20	25	30	35	40	45	50	55');



--
-- 转存表中的数据 `dzz_hooks`
--
INSERT INTO dzz_hooks (id, app_market_id, `name`, description, `type`, update_time, addons, `status`, priority) VALUES
(1, 0, 'check_login', '', 1, 0, 'user\\classes\\checklogin', 1, 0),
(2, 0, 'safe_chk', '', 1, 0, 'user\\classes\\safechk', 1, 0),
(3, 0, 'config_read', '读取配置钩子', 0, 0, 'core\\dzz\\config', 1, 0),
(4, 0, 'dzz_route', '', 1, 0, 'core\\dzz\\route', 1, 0),
(5, 0, 'dzz_initbefore', '', 0, 0, 'user\\classes\\init|user', 1, 0),
(6, 0, 'dzz_initbefore', '', 0, 0, 'misc\\classes\\init|misc', 1, 0),
(7, 0, 'dzz_initafter', '', 1, 0, 'user\\classes\\route|user', 1, 0),
(8, 0, 'dzz_initafter', ' ', 1, 0, 'core\\dzz\\ulimit', 1, 0),
(9, 0, 'sysreg', ' ', 1, 0, 'core\\dzz\\sysreg', 1, 0),
(10, 0, 'app_run', '', 1, 0, 'core\\dzz\\apprun', 1, 0),
(11, 0, 'mod_run', '', 1, 0, 'core\\dzz\\modrun', 1, 0),
(12, 0, 'adminlogin', '', 1, 0, 'admin\\login\\classes\\adminlogin', 1, 0),
(13, 0, 'mod_start', '', 1, 0, 'core\\dzz\\modroute', 1, 0),
(14, 0, 'login_check', '', 1, 0, 'user\\login\\classes\\logincheck|user', 1, 0),
(15, 0, 'login_valchk', '', 1, 0, 'user\\login\\classes\\loginvalchk|user/login', 1, 0),
(16, 0, 'email_chk', '', 1, 0, 'user\\profile\\classes\\emailchk|user', 1, 0),
(17, 0, 'register_before', '', 1, 0, 'user\\register\\classes\\register|user', 1, 0),
(18, 0, 'check_val', '', 1, 0, 'user\\register\\classes\\checkvalue|user', 1, 0),
(19, 0, 'register_common', '', 1, 0, 'user\\register\\classes\\regcommon', 1, 0),
(20, 8, 'systemlog', '', 1, 0, 'admin\\systemlog\\classes\\systemlog', 1, 0),
(21, 0, 'pichomegetinfo', ' ', 1, 0, 'dzz\\imageColor\\classes\\getcolor', 1, 0),
(22, 0, 'pichomegetinfo', ' ', 1, 0, 'dzz\\ffmpeg\\classes\\info', 1, 0),
(23, 0, 'getpichomethumb', ' ', 1, 0, 'dzz\\billfish\\classes\\getpichomethumb', 1, 0),
(24, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\billfish\\classes\\pichomevappdelete', 1, 0),
(25, 0, 'pichomedatadeleteafter', ' ', 1, 0, 'dzz\\eagle\\classes\\deleteafter', 1, 0),
(26, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\eagle\\classes\\pichomevappdelete', 1, 0),
(27, 0, 'delpichomefolderafter', ' ', 1, 0, 'dzz\\billfish\\classes\\delpichomefolderafter', 1, 0),
(28, 0, 'delpichomefolderafter', ' ', 1, 0, 'dzz\\eagle\\classes\\delpichomefolderafter', 1, 0),
(29, 0, 'pichomedatadeleteafter', ' ', 1, 0, 'dzz\\local\\classes\\deleteafter', 1, 0),
(30, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\local\\classes\\pichomevappdelete', 1, 0),
(31, 0, 'pichomedatadeleteafter', ' ', 1, 0, 'dzz\\billfish\\classes\\deleteafter', 1, 0),
(32, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\local\\classes\\pichomevappdelete', 1, 0),
(33, 0, 'pichomedatadeleteafter', ' ', 1, 0, 'dzz\\billfish\\classes\\deleteafter', 1, 0);



--
-- 转存表中的数据 `dzz_local_storage`
--


--
-- 转存表中的数据 `dzz_usergroup`
--

INSERT INTO `dzz_usergroup` VALUES(1, 1, 'system', 'private', '管理员', 0, 0, 9, '', '', 2, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(2, 2, 'system', 'private', '机构和部门管理员', 0, 0, 8, '', '', 1, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(3, 3, 'system', 'private', '部门管理员', 0, 0, 7, '', '', 1, 1, 1, 1, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(4, 0, 'system', 'private', '禁止发言', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(5, 0, 'system', 'private', '禁止访问', 0, 0, 0, '', '', 0, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(6, 0, 'system', 'private', '禁止 IP', 0, 0, 0, '', '', 0, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(7, 0, 'system', 'private', '游客', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 10);
INSERT INTO `dzz_usergroup` VALUES(8, 0, 'system', 'private', '等待验证成员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(9, 0, 'system', 'private', '普通成员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);
INSERT INTO `dzz_usergroup` VALUES(10, 0, 'system', 'private', '信息录入员', 0, 0, 0, '', '', 1, 1, 0, 0, 0, 0, 0);

--
-- 转存表中的数据 `dzz_usergroup_field`
--

INSERT INTO `dzz_usergroup_field` VALUES(1, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(2, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(3, 0, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(4, -1, '', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(5, -1, '', 0, 1);
INSERT INTO `dzz_usergroup_field` VALUES(6, -1, '', 0, 1);
INSERT INTO `dzz_usergroup_field` VALUES(7, -1, 'gif, jpg, jpeg, png', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(8, -1, '', 0, 7);
INSERT INTO `dzz_usergroup_field` VALUES(9, 10240, '', 0, 524287);
INSERT INTO `dzz_usergroup_field` VALUES(10, 10240, '', 0, 229039);


--
-- 转存表中的数据 `dzz_setting`
--

INSERT INTO `dzz_setting` VALUES('attachdir', './data/attachment');
INSERT INTO `dzz_setting` VALUES('attachurl', 'data/attachment');
INSERT INTO `dzz_setting` VALUES('jspath', 'static/js/');
INSERT INTO `dzz_setting` VALUES('seccodestatus', '5');
INSERT INTO `dzz_setting` VALUES('oltimespan', '15');
INSERT INTO `dzz_setting` VALUES('imgdir', 'static/image/common');
INSERT INTO `dzz_setting` VALUES('avatarmethod', '1');
INSERT INTO `dzz_setting` VALUES('reglinkname', '立即注册');
INSERT INTO `dzz_setting` VALUES('refreshtime', '3');
INSERT INTO `dzz_setting` VALUES('regstatus', '0');
INSERT INTO `dzz_setting` VALUES('regclosemessage', '');
INSERT INTO `dzz_setting` VALUES('regname', 'register');
INSERT INTO `dzz_setting` VALUES('bbrules', '0');
INSERT INTO `dzz_setting` VALUES('bbrulesforce', '0');
INSERT INTO `dzz_setting` VALUES('bbrulestxt', '');
INSERT INTO `dzz_setting` VALUES('seccodedata', 'a:13:{s:4:"type";s:1:"0";s:5:"width";s:3:"150";s:6:"height";s:2:"34";s:7:"scatter";s:1:"0";s:10:"background";s:1:"1";s:10:"adulterate";s:1:"1";s:3:"ttf";s:1:"1";s:5:"angle";s:1:"0";s:7:"warping";s:1:"0";s:5:"color";s:1:"1";s:4:"size";s:1:"0";s:6:"shadow";s:1:"1";s:8:"animator";s:1:"1";}');
INSERT INTO `dzz_setting` VALUES('bbname', 'dzz');
INSERT INTO `dzz_setting` VALUES('pwlength', '0');
INSERT INTO `dzz_setting` VALUES('strongpw', 'a:0:{}');
INSERT INTO `dzz_setting` VALUES('pwdsafety', '0');
INSERT INTO `dzz_setting` VALUES('onlinehold', '60');
INSERT INTO `dzz_setting` VALUES('timeoffset', '8');
INSERT INTO `dzz_setting` VALUES('reginput', 'a:4:{s:8:"username";s:8:"username";s:8:"password";s:8:"password";s:9:"password2";s:9:"password2";s:5:"email";s:5:"email";}');
INSERT INTO `dzz_setting` VALUES('newusergroupid', '9');
INSERT INTO `dzz_setting` VALUES('dateformat', 'Y-n-j');
INSERT INTO `dzz_setting` VALUES('timeformat', 'H:i');
INSERT INTO `dzz_setting` VALUES('userdateformat', '');
INSERT INTO `dzz_setting` VALUES('metakeywords', '');
INSERT INTO `dzz_setting` VALUES('metadescription', '');
INSERT INTO `dzz_setting` VALUES('statcode', '');
INSERT INTO `dzz_setting` VALUES('boardlicensed', '0');
INSERT INTO `dzz_setting` VALUES('leavealert', '0');
INSERT INTO `dzz_setting` VALUES('bbclosed', '0');
INSERT INTO `dzz_setting` VALUES('closedreason', '网站升级中....');
INSERT INTO `dzz_setting` VALUES('sitename', 'dzz');
INSERT INTO `dzz_setting` VALUES('dateconvert', '1');
INSERT INTO `dzz_setting` VALUES('allowshare', '1');


INSERT INTO `dzz_setting` VALUES('smcols', '8');
INSERT INTO `dzz_setting` VALUES('smrows', '5');
INSERT INTO `dzz_setting` VALUES('smthumb', '24');

INSERT INTO `dzz_setting` VALUES('thumb_active', '1');
INSERT INTO `dzz_setting` VALUES('imagelib', '1');

INSERT INTO `dzz_setting` VALUES('waterimg', 'data/attachment/waterimg/water.png');
INSERT INTO `dzz_setting` VALUES('IsWatermarkstatus', '0');
INSERT INTO `dzz_setting` VALUES('watermarkstatus', '9');
INSERT INTO `dzz_setting` VALUES('watermarkminwidth', '512');
INSERT INTO `dzz_setting` VALUES('watermarkminheight', '512');
INSERT INTO `dzz_setting` VALUES('watermarktype', 'png');

INSERT INTO `dzz_setting` VALUES('unRunExts', 'a:16:{i:0;s:3:"exe";i:1;s:3:"bat";i:2;s:2:"sh";i:3;s:3:"dll";i:4;s:3:"php";i:5;s:4:"php4";i:6;s:4:"php5";i:7;s:4:"php3";i:8;s:3:"jsp";i:9;s:3:"asp";i:10;s:4:"aspx";i:11;s:2:"vs";i:12;s:2:"js";i:13;s:3:"htm";i:14;s:4:"html";i:15;s:3:"xml";}');
INSERT INTO `dzz_setting` VALUES('maxChunkSize', '104857600');
INSERT INTO `dzz_setting` VALUES('feed_at_depart_title', '部门');
INSERT INTO `dzz_setting` VALUES('feed_at_user_title', '同事');
INSERT INTO `dzz_setting` VALUES('feed_at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');
INSERT INTO `dzz_setting` VALUES('at_range', 'a:3:{i:9;s:1:"1";i:2;s:1:"2";i:1;s:1:"3";}');

INSERT INTO `dzz_setting` VALUES ('loginset', 'a:5:{s:5:\"title\";s:6:\"欧奥\";s:8:\"subtitle\";s:7:\"Pichome\";s:10:\"background\";s:0:\"\";s:8:\"template\";s:1:\"2\";s:6:\"bcolor\";s:0:\"\";}');
INSERT INTO `dzz_setting` VALUES('privacy', 'a:1:{s:7:"profile";a:17:{s:9:"education";i:1;s:8:"realname";i:-1;s:7:"address";i:0;s:9:"telephone";i:0;s:15:"affectivestatus";i:0;s:10:"department";i:0;s:8:"birthday";i:0;s:13:"constellation";i:0;s:9:"bloodtype";i:0;s:6:"gender";i:0;s:6:"mobile";i:0;s:2:"qq";i:0;s:7:"zipcode";i:0;s:11:"nationality";i:0;s:14:"graduateschool";i:0;s:8:"interest";i:0;s:3:"bio";i:0;}}');
INSERT INTO `dzz_setting`  VALUES ('thumbsize',	'a:3:{s:5:\"small\";a:2:{s:5:\"width\";i:7200;s:6:\"height\";i:360;}s:6:\"middle\";a:2:{s:5:\"width\";i:512;s:6:\"height\";i:512;}s:5:\"large\";a:2:{s:5:\"width\";i:1440;s:6:\"height\";i:900;}}');
INSERT INTO `dzz_setting` VALUES('verify', 'a:8:{i:1;a:9:{s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:5:"field";a:1:{s:8:"realname";s:8:"realname";}s:8:"readonly";i:1;s:5:"title";s:12:"实名认证";s:4:"icon";s:31:"common/verify/1/verify_icon.jpg";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:2;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:3;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:4;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:5;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:6;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:7;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}s:7:"enabled";b:0;}');
INSERT INTO `dzz_setting` VALUES('systemlog_open', '1');
INSERT INTO `dzz_setting` VALUES('systemlog_setting','a:7:{s:8:"errorlog";a:3:{s:5:"title";s:12:"系统错误";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:5:"cplog";a:3:{s:5:"title";s:12:"后台访问";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"deletelog";a:3:{s:5:"title";s:12:"数据删除";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:9:"updatelog";a:3:{s:5:"title";s:12:"数据更新";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"loginlog";a:3:{s:5:"title";s:12:"用户登录";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"sendmail";a:3:{s:5:"title";s:12:"邮件发送";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"otherlog";a:3:{s:5:"title";s:12:"其他信息";s:7:"is_open";i:1;s:8:"issystem";i:1;}}');

INSERT INTO `dzz_setting` VALUES('fileVersion', '1');
INSERT INTO `dzz_setting` VALUES('fileVersionNumber', '50');
INSERT INTO `dzz_setting` VALUES('defaultdepartment', '1');


INSERT INTO `dzz_setting` VALUES ('pichomefilterfileds', 'a:12:{i:0;a:3:{s:3:\"key\";s:3:\"tag\";s:4:\"text\";s:6:\"标签\";s:7:\"checked\";s:1:\"1\";}i:1;a:3:{s:3:\"key\";s:5:\"color\";s:4:\"text\";s:6:\"颜色\";s:7:\"checked\";s:1:\"1\";}i:2;a:3:{s:3:\"key\";s:4:\"link\";s:4:\"text\";s:6:\"链接\";s:7:\"checked\";s:1:\"1\";}i:3;a:3:{s:3:\"key\";s:4:\"desc\";s:4:\"text\";s:6:\"注释\";s:7:\"checked\";s:1:\"1\";}i:4;a:3:{s:3:\"key\";s:8:\"duration\";s:4:\"text\";s:6:\"时长\";s:7:\"checked\";s:1:\"1\";}i:5;a:3:{s:3:\"key\";s:4:\"size\";s:4:\"text\";s:6:\"尺寸\";s:7:\"checked\";s:1:\"1\";}i:6;a:3:{s:3:\"key\";s:3:\"ext\";s:4:\"text\";s:6:\"类型\";s:7:\"checked\";s:1:\"1\";}i:7;a:3:{s:3:\"key\";s:5:\"shape\";s:4:\"text\";s:6:\"形状\";s:7:\"checked\";s:1:\"1\";}i:8;a:3:{s:3:\"key\";s:5:\"grade\";s:4:\"text\";s:6:\"评分\";s:7:\"checked\";s:1:\"1\";}i:9;a:3:{s:3:\"key\";s:5:\"btime\";s:4:\"text\";s:12:\"添加时间\";s:7:\"checked\";s:1:\"1\";}i:10;a:3:{s:3:\"key\";s:8:\"dateline\";s:4:\"text\";s:12:\"修改日期\";s:7:\"checked\";s:1:\"1\";}i:11;a:3:{s:3:\"key\";s:5:\"mtime\";s:4:\"text\";s:12:\"创建日期\";s:7:\"checked\";s:1:\"1\";}}');
INSERT INTO `dzz_setting` VALUES ('overt', 0);
INSERT INTO `dzz_setting` VALUES ('pichomepagesetting', 'a:7:{s:5:\"theme\";s:0:\"\";s:6:\"layout\";s:9:\"waterFall\";s:4:\"show\";s:10:\"name,other\";s:5:\"other\";s:5:\"mtime\";s:4:\"sort\";s:1:\"1\";s:4:\"desc\";s:4:\"desc\";s:8:\"opentype\";s:3:\"new\";}');
INSERT INTO `dzz_setting` VALUES ('pichomeimportallowext', '*.jpg,*.jpeg,*.gif,*.png,*.webp,*.pdf,*.txt,*.mp3,*.mp4,*.webm,*.ogv,*.ogg,*.wav,*.m3u8,*.hls,*.mpg,*.mpeg,*.flv,*.m4v');
INSERT INTO `dzz_setting` VALUES ('pichomeimportnotdir', 'patch,srv,run,lib64,sys,bin,media,boot,etc,sbin,lib,dev,root,usr,proc,tmp,lost+found,lib32,etc.defaults,var.defaults,@*,.*,$*');

-- 转存表中的数据 `dzz_imagetype`
--

INSERT INTO `dzz_imagetype` VALUES(1, 1, '默认', 'smiley', 0, 'dzz');


INSERT INTO `dzz_user_setting` VALUES ('1','1', 'pichomeimageexpanded', '1');

--
-- 转存表中的数据 `dzz_user_profile_setting`
--

INSERT INTO `dzz_user_profile_setting` VALUES('realname', 0, 0, 0, '真实姓名', '', 1, 0, 1, 0, 0, 0, 1, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('gender', 0, 0, 0, '性别', '', 5, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('birthyear', 0, 0, 0, '出生年份', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('birthmonth', 0, 0, 0, '出生月份', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('birthday', 0, 0, 0, '生日', '', 2, 0, 0, 0, 0, 0, 0, 'select', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('constellation', 0, 0, 0, '星座', '星座(根据生日自动计算)', 2, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('zodiac', 0, 0, 0, '生肖', '生肖(根据生日自动计算)', 3, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('telephone', 0, 0, 0, '固定电话', '', 11, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('mobile', 0, 0, 0, '手机', '', 7, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('address', 0, 0, 0, '地址', '', 11, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('zipcode', 0, 0, 0, '邮编', '', 12, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('nationality', 0, 0, 0, '国籍', '', 13, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('graduateschool', 0, 0, 0, '毕业学校', '', 15, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('education', 0, 0, 0, '学历', '', 14, 0, 0, 0, 0, 0, 0, 'select', 0, '博士\n硕士\n本科\n专科\n中学\n小学\n其它', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('affectivestatus', 0, 0, 0, '婚姻状况', '', 6, 0, 0, 0, 0, 0, 0, 'select', 0, '保密\n未婚\n已婚', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('bloodtype', 0, 0, 0, '血型', '', 4, 0, 0, 0, 0, 0, 0, 'select', 0, 'A\nB\nAB\nO\n其它', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('department', 0, 0, 1, '所属部门', '', 0, 0, 1, 0, 0, 0, 0, 'department', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('qq', 0, 0, 0, 'QQ', '', 9, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('skype', 0, 0, 0, 'skype', '', 10, 0, 0, 0, 0, 0, 0, 'text', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('bio', 0, 0, 0, '自我介绍', '', 17, 0, 0, 0, 0, 0, 0, 'textarea', 0, '', 0, '', 0);
INSERT INTO `dzz_user_profile_setting` VALUES('interest', 0, 0, 0, '兴趣爱好', '', 16, 0, 0, 0, 0, 0, 0, 'textarea', 0, '', 0, '', 0);



--
-- 转存表中的数据 `dzz_user_field`
--

INSERT INTO `dzz_user_field` (`uid`, `docklist`, `screenlist`, `applist`, `noticebanlist`, `iconview`, `iconposition`, `direction`, `autolist`, `taskbar`, `dateline`, `updatetime`, `attachextensions`, `maxattachsize`, `usesize`, `addsize`, `buysize`, `wins`, `perm`, `privacy`) VALUES
(1, '', '', '1,10', '', 2, 0, 0, 1, 'bottom', 0, 0, '-1', -1, 0, 0, 0, '', 0, '');











