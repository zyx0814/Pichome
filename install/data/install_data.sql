--
-- 转存表中的数据 `dzz_app_market`
-- 
INSERT INTO `dzz_app_market` (`appid`, `mid`, `appname`, `appico`, `appdesc`, `appurl`, `appadminurl`, `noticeurl`, `dateline`, `disp`, `vendor`, `haveflash`, `isshow`, `havetask`, `hideInMarket`, `feature`, `fileext`, `group`, `orgid`, `position`, `system`, `notdelete`, `open`, `nodup`, `identifier`, `app_path`, `available`, `version`, `upgrade_version`, `check_upgrade_time`, `extra`, `uids`, `showadmin`) VALUES
(1, 0, 'pichome', 'appico/201712/21/161251dpmgqozr0kdk9rqz.png', '支持将服务器中eagle文件包导入到系统指定目录', '{dzzscript}?mod=pichome&op=index', NULL, '', 0, 0, '乐云网络', 0, 1, 0, 0, '', 'eaglepack,zip', 1, 0, 1, 0, 1, 0, 0, 'pichome', 'dzz', 1, '2.01', '', 0, 'a:2:{s:11:\"installfile\";s:11:\"install.php\";s:13:\"uninstallfile\";s:13:\"uninstall.php\";}', NULL, 0),
(2, 2, '机构用户', 'appico/201712/21/131016is1wjww2uwvljllw.png', 'Dzz机构用户管理', '{adminscript}?mod=orguser', '', '', 1377753015, 2, '欧奥图文档', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'orguser', 'admin', 1, '2.0', '', 20171211, '', '', 0),
(9, 9, '系统工具', 'appico/201712/21/160537cikgw2v6s6z4scuv.png', '系统维护相关工具集合，如：更新缓存、数据库备份，计划任务，在线升级等', '{adminscript}?mod=system', '', '', 1377677136, 9, '欧奥图文档', 0, 1, 1, 0, '', '', 3, 0, 0, 2, 1, 0, 0, 'system', 'admin', 1, '2.0', '', 20171115, '', '', 0),
(3, 0, 'onlyoffice_view', ' ', 'office文件预览', '{dzzscript}?mod=onlyoffice_view', '{dzzscript}?mod=onlyoffice_view&op=admin', '', 1377677136, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'dzz::pdf,dzz::doc,dzz::docx,dzz::rtf,dzz::odt,dzz::htm,dzz::html,dzz::mht,dzz::txt,dzz::ppt,dzz::pptx,dzz::pps,dzz::ppsx,dzz::odp,dzz::xls,dzz::xlsx,dzz::ods,dzz::csv', 1, 0, 0, 0, 0, 0, 0, 'onlyoffice_view', 'dzz', 1, '2.0', ' ', 0, 'a:1:{s:4:\"exts\";s:76:\"pdf,doc,docx,rtf,odt,htm,html,mht,txt,ppt,pptx,pps,ppsx,odp,xls,xlsx,ods,csv\";}', ' ', 1),
(4, 0, 'ffmpeg', ' ', '音视频信息获取和缩略图转换', '{dzzscript}?mod=ffmpeg', '{dzzscript}?mod=ffmpeg&op=admin', '', 1377677136, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'dzz::avi,dzz::rm,dzz::rmvb,dzz::mkv,dzz::mov,dzz::wmv,dzz::asf,dzz::mpg,dzz::mpe,dzz::mpeg,dzz::mp4,dzz::m4v,dzz::mpeg,dzz::f4v,dzz::vob,dzz::ogv,dzz::mts,dzz::m2ts,dzz::3gp,dzz::webm,dzz::flv,dzz::wav,dzz::mp3,dzz::ogg,dzz::midi,dzz::wma,dzz::vqf,dzz::ra,dzz::aac,dzz::flac,dzz::ape,dzz::amr,dzz::aiff,dzz::au,dzz::m4a', 1, 0, 0, 0, 0, 0, 0, 'ffmpeg', 'dzz', 1, '2.0', ' ', 0, 'a:7:{s:15:\"ffmpeg.binaries\";s:0:\"\";s:16:\"ffprobe.binaries\";s:0:\"\";s:7:\"timeout\";s:4:\"3600\";s:14:\"ffmpeg.threads\";s:1:\"0\";s:4:\"exts\";s:118:\"avi,rm,rmvb,mkv,wmv,asf,mpg,mpeg,f4v,vob,ogv,mts,m2ts,mpe,3gp,flv,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,m4v,mxf\";s:10:\"exts_thumb\";s:148:\"avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,m2ts,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,mxf\";s:9:\"exts_info\";s:148:\"avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,mt2s,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,mxf\";}', ' ', 1),
(6, 0, 'qcos', ' ', '腾讯云音视频转换、缩略图及信息获取，office文件转换及缩略图获取和图片缩略图以及颜色获取', '{dzzscript}?mod=qcos', NULL, '', 1377677136, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'QCOS::jpg,QCOS::bmp,QCOS::gif,QCOS::png,QCOS::webp,,QCOS::3gp,QCOS::avi,QCOS::flv,QCOS::mp4,QCOS::m3u8,QCOS::mpg,QCOS::asf,QCOS::wmv,QCOS::mkv,QCOS::mov,QCOS::ts,QCOS::webm,QCOS::mxf', 1, 0, 0, 0, 0, 0, 0, 'qcos', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0),
(5, 0, 'imageColor', ' ', '图片缩略图转换及颜色获取', '{dzzscript}?mod=imageColor', NULL, '', 1377677136, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'dzz::aai,dzz::art,dzz::arw,dzz::avs,dzz::bpg,dzz::bmp,dzz::bmp2,dzz::bmp3,dzz::brf,dzz::cals,dzz::cals,dzz::cgm,dzz::cin,dzz::cip,dzz::cmyk,dzz::cmyka,dzz::cr2,dzz::crw,dzz::cube,dzz::cur,dzz::cut,dzz::dcm,dzz::dcr,dzz::dcx,dzz::dds,dzz::dib,dzz::djvu,dzz::dng,dzz::dot,dzz::dpx,dzz::emf,dzz::epdf,dzz::epi,dzz::eps,dzz::eps2,dzz::eps3,dzz::epsf,dzz::epsi,dzz::ept,dzz::exr,dzz::fax,dzz::fig,dzz::fits,dzz::fpx,dzz::gplt,dzz::gray,dzz::graya,dzz::hdr,dzz::heic,dzz::hpgl,dzz::hrz,dzz::ico,dzz::info,dzz::isobrl,dzz::isobrl6,dzz::jbig,dzz::jng,dzz::jp2,dzz::jpt,dzz::j2c,dzz::j2k,dzz::jxr,dzz::json,dzz::man,dzz::mat,dzz::miff,dzz::mono,dzz::mng,dzz::m2v,dzz::mpc,dzz::mpr,dzz::mrwmmsl,dzz::mtv,dzz::mvg,dzz::nef,dzz::orf,dzz::otb,dzz::p7,dzz::palm,dzz::pam,dzz::clipboard,dzz::pbm,dzz::pcd,dzz::pcds,dzz::pcl,dzz::pcx,dzz::pdb,dzz::pef,dzz::pes,dzz::pfa,dzz::pfb,dzz::pfm,dzz::pgm,dzz::picon,dzz::pict,dzz::pix,dzz::png8,dzz::png00,dzz::png24,dzz::png32,dzz::png48,dzz::png64,dzz::pnm,dzz::ppm,dzz::ps,dzz::ps2,dzz::ps3,dzz::psb,dzz::psd,dzz::ptif,dzz::pwp,dzz::rad,dzz::raf,dzz::rgb,dzz::rgb565,dzz::rgba,dzz::rgf,dzz::rla,dzz::rle,dzz::sfw,dzz::sgi,dzz::shtml,dzz::sid,dzz::mrsid,dzz::sum,dzz::svg,dzz::text,dzz::tga,dzz::tif,dzz::tiff,dzz::tim,dzz::ttf,dzz::ubrl,dzz::ubrl6,dzz::uil,dzz::uyvy,dzz::vicar,dzz::viff,dzz::wbmp,dzz::wpg,dzz::webp,dzz::wmf,dzz::wpg,dzz::x,dzz::xbm,dzz::xcf,dzz::xpm,dzz::xwd,dzz::x3f,dzz::YCbCr,dzz::YCbCrA,dzz::yuv,dzz::sr2,dzz::srf,dzz::srw,dzz::rw2,dzz::nrw,dzz::mrw,dzz::kdc,dzz::erf,dzz::canvas,dzz::caption,dzz::clip,dzz::clipboard,dzz::fractal,dzz::gradient,dzz::hald,dzz::histogram,dzz::inline,dzz::map,dzz::mask,dzz::matte,dzz::null,dzz::pango,dzz::plasma,dzz::preview,dzz::print,dzz::scan,dzz::radial_gradient,dzz::scanx,dzz::screenshot,dzz::stegano,dzz::tile,dzz::unique,dzz::vid,dzz::win,dzz::xc,dzz::granite,dzz::logo,dzz::netscpe,dzz::rose,dzz::wizard,dzz::bricks,dzz::checkerboard,dzz::circles,dzz::crosshatch,dzz::crosshatch30,dzz::crosshatch45,dzz::fishscales,dzz::gray0,dzz::gray5,dzz::gray10,dzz::gray15,dzz::gray20,dzz::gray25,dzz::gray30,dzz::gray35,dzz::gray40,dzz::gray45,dzz::gray50,dzz::gray55,dzz::gray60,dzz::gray65,dzz::gray70,dzz::gray75,dzz::gray80,dzz::gray85,dzz::gray90,dzz::gray95,dzz::gray100,dzz::hexagons,dzz::horizontal,dzz::horizontal2,dzz::horizontal3,dzz::horizontalsaw,dzz::hs_bdiagonal,dzz::hs_cross,dzz::hs_diagcross,dzz::hs_fdiagonal,dzz::hs_vertical,dzz::left30,dzz::left45,dzz::leftshingle,dzz::octagons,dzz::right30,dzz::right45,dzz::rightshingle,dzz::smallfishcales,dzz::vertical,dzz::vertical2,dzz::vertical3,dzz::verticalfishingle,dzz::vericalrightshingle,dzz::verticalleftshingle,dzz::verticalsaw,dzz::fff,dzz::3fr,dzz::ai,dzz::iiq,dzz::cdr,dzz::jpg,dzz::png,dzz::gif,dzz::jpeg', 1, 0, 0, 0, 0, 0, 0, 'imageColor', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0),
(7, 0, 'xgplayer', ' ', '西瓜视频', '{dzzscript}?mod=xgplayer', NULL, '', 0, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'mp3,mp4,flv,webm,ogv,ogg,wav,m3u8,hls,mpg,avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpeg,f4v,vob,ogv,mts,m2ts,mpe,ogg,3gp,flv,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,m4v', 1, 0, 0, 0, 0, 0, 0, 'xgplayer', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0),
(8, 0, 'textviewer', ' ', 'text预览', '{dzzscript}?mod=textviewer', NULL, '', 0, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'txt,php,js,jsp,htm,html,jsp,asp,aspx', 1, 0, 0, 0, 0, 0, 0, 'textviewer', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0),
(10, 0, 'qcosoffice', ' ', '腾讯云文档预览', '{dzzscript}?mod=qcosoffice', NULL, '', 0, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'QCOS::pptx,QCOS::ppt,QCOS::pot,QCOS::potx,QCOS::pps,QCOS::ppsx,QCOS::dps,QCOS::dpt,QCOS::pptm,QCOS::potm,QCOS::ppsm,QCOS::doc,QCOS::dot,QCOS::wps,QCOS::wpt,QCOS::docx,QCOS::dotx,QCOS::docm,QCOS::dotm,QCOS::xls,QCOS::xlt,QCOS::et,QCOS::ett,QCOS::xlsx,QCOS::xltx,QCOS::csv,QCOS::xlsb,QCOS::xlsm,QCOS::xltm,QCOS::ets,QCOS::pdf,QCOS::lrc,QCOS::c,QCOS::cpp,QCOS::h,QCOS::asm,QCOS::s,QCOS::java,QCOS::asp,QCOS::bat,QCOS::bas,QCOS::prg,QCOS::cmd,QCOS::rtf,QCOS::txt,QCOS::log,QCOS::xml,QCOS::htm,QCOS::html', 1, 0, 0, 0, 0, 0, 0, 'qcosoffice', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0),
(11, 0, 'pdf', ' ', 'pdf预览', '{dzzscript}?mod=pdf', NULL, '', 0, 0, '欧奥图文档', 0, 1, 1, 0, ' ', 'pdf', 1, 0, 0, 0, 0, 0, 0, 'pdf', 'dzz', 1, '2.0', ' ', 0, ' ', ' ', 0);

-- 转存表中的数据 `dzz_cron`
--

INSERT INTO `dzz_cron` (`cronid`, `available`, `type`, `name`, `filename`, `lastrun`, `nextrun`, `weekday`, `day`, `hour`, `minute`) VALUES
(1, 1, 'system', '每月通知清理', 'cron_clean_notification_month.php', 1685692339, 1688158800, -1, 1, 5, '0'),
(2, 1, 'system', '每周清理缓存文件', 'cron_cache_cleanup_week.php', 1687743056, 1688331600, 1, -1, 5, '0'),
(3, 1, 'system', '定时删除删除状态库', 'cron_pichome_delete.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(4, 1, 'system', '定时获取转换状态', 'cron_pichome_getconvertstatus.php', 1688039702, 1688040000, -1, -1, -1, '0 5	10	15	20	25	30	35	40	45	50	55'),
(5, 1, 'system', '定时更新热搜', 'cron_cache_pichome_searchhot.php', 1688039702, 1688040000, -1, -1, -1, '0 5	10	15	20	25	30	35	40	45	50	55'),
(6, 1, 'system', '定时获取缩略图', 'cron_pichome_getthumb.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(7, 1, 'system', '定时获取信息', 'cron_pichome_getinfo.php', 1688039702, 1688040000, -1, -1, -1, '0 5	10	15	20	25	30	35	40	45	50	55'),
(8, 1, 'system', '定时转换音视频', 'cron_pichome_convert.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(10, 1, 'system', '定时获取需要更新的库', 'cron_pichome_vapp_update.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(11, 1, 'system', '定时检查库更新过程任务', 'cron_vapp_vappdoupdate.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(12, 1, 'system', '定时检查缩略图更新任务', 'cron_thumbcheckchange.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(13, 1, 'system', '定时更新缩略图变动任务', 'cron_thumbdochange.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55'),
(14, 1, 'system', '定时检查单页缓存数据更新', 'cron_update_alonepagedata.php', 1688039702, 1688040000, -1, -1, -1, '0	5	10	15	20	25	30	35	40	45	50	55');



--
-- 转存表中的数据 `dzz_hooks`
--
INSERT INTO `dzz_hooks` (`id`, `app_market_id`, `name`, `description`, `type`, `update_time`, `addons`, `status`, `priority`) VALUES
(1, 0, 'check_login', '', 1, 0, 'user\\classes\\checklogin', 1, 0),
(2, 0, 'safe_chk', '', 1, 0, 'user\\classes\\safechk', 1, 0),
(3, 0, 'config_read', '读取配置钩子', 0, 0, 'core\\dzz\\config', 1, 0),
(4, 0, 'dzz_route', '', 1, 0, 'core\\dzz\\route', 1, 0),
(5, 0, 'dzz_initbefore', '', 0, 0, 'user\\classes\\init|user', 1, 0),
(6, 0, 'dzz_initbefore', '', 0, 0, 'misc\\classes\\init|misc', 1, 0),
(7, 0, 'dzz_initafter', '', 1, 0, 'user\\classes\\route|user', 1, 0),
(8, 0, 'app_run', '', 1, 0, 'core\\dzz\\apprun', 1, 0),
(9, 0, 'mod_run', '', 1, 0, 'core\\dzz\\modrun', 1, 0),
(10, 0, 'adminlogin', '', 1, 0, 'admin\\login\\classes\\adminlogin', 1, 0),
(12, 0, 'mod_start', '', 1, 0, 'core\\dzz\\modroute', 1, 0),
(13, 0, 'login_check', '', 1, 0, 'user\\login\\classes\\logincheck|user', 1, 0),
(14, 0, 'login_valchk', '', 1, 0, 'user\\login\\classes\\loginvalchk|user/login', 1, 0),
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
(30, 0, 'dzz_initafter', ' ', 1, 0, 'core\\dzz\\ulimit', 1, 0),
(31, 0, 'sysreg', ' ', 1, 0, 'core\\dzz\\sysreg', 1, 0),
(32, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\local\\classes\\pichomevappdelete', 1, 0),
(33, 0, 'pichomedatadeleteafter', ' ', 1, 0, 'dzz\\billfish\\classes\\deleteafter', 1, 0),
(35, 0, 'pichomegetfileinfo', ' ', 1, 0, 'dzz\\ffmpeg\\classes\\info', 1, 0),
(36, 0, 'pichomegetfileinfo', ' ', 1, 0, 'dzz\\imageColor\\classes\\imageColor', 1, 0),
(37, 0, 'pichomethumb', ' ', 1, 0, 'dzz\\ffmpeg\\classes\\thumb', 1, 0),
(38, 0, 'pichomethumb', ' ', 1, 0, 'dzz\\imageColor\\classes\\getthumb', 1, 0),
(39, 0, 'pichomethumb', ' ', 1, 0, 'dzz\\onlyoffice_view\\classes\\thumb', 1, 0),
(40, 0, 'pichomethumb', ' ', 1, 0, 'dzz\\qcos\\classes\\thumb', 1, 0),
(41, 0, 'pichomegetfileinfo', ' ', 1, 0, 'dzz\\qcos\\classes\\info', 1, 0),
(42, 0, 'addfileafter', ' ', 1, 0, 'dzz\\qcos\\classes\\convert', 0, 0),
(43, 0, 'addfileafter', ' ', 1, 0, 'dzz\\ffmpeg\\classes\\convert', 0, 0),
(45, 0, 'addvappafter', ' ', 1, 0, 'dzz\\pichome\\classes\\addvappafter', 1, 0),
(46, 0, 'pichomevappdelete', ' ', 1, 0, 'dzz\\pichome\\classes\\pichomevappdelete', 1, 0),
(47, 0, 'addfileafter', ' ', 1, 0, 'dzz\\pichome\\classes\\addfileafter', 1, 0),
(53, 0, 'addstatsdata', ' ', 1, 0, 'dzz\\stats\\classes\\addstatsdata', 1, 0),
(54, 0, 'statsviewaddafter', ' ', 1, 0, 'dzz\\banner\\classes\\statsviewaddafter', 1, 0),
(55, 0, 'statskeywordaddafter', ' ', 1, 0, 'dzz\\banner\\classes\\statskeywordaddafter', 1, 0);



--
-- 转存表中的数据 `dzz_connect`
--
INSERT INTO `dzz_connect` (`name`, `key`, `secret`, `type`, `bz`, `root`, `available`, `dname`, `curl`, `disp`) VALUES
('阿里云存储', '', '', 'storage', 'ALIOSS', '', 0, 'connect_storage', '', 0),
('Qcos', '', '', 'storage', 'QCOS', '', 2, 'connect_storage', '', 0),
('本地', '', '', 'local', 'dzz', '', 2, '', '', -2);


--
-- 转存表中的数据 `dzz_connect_storage`
--

INSERT INTO `dzz_connect_storage` (`id`, `uid`, `cloudname`, `dateline`, `perm`, `access_id`, `access_key`, `bucket`, `bz`, `hostname`, `internalhostname`, `host`, `internalhost`, `extra`, `mediastatus`, `docstatus`, `imagestatus`,`disp`,`videoquality`,`specialext`,`isdefault`) VALUES
(1, 0, '本地存储', 0, 29751, ' ', ' ', '', 'dzz', ' ', ' ', ' ', ' ', ' ', 0, 0, 1,-2,0,' ',1);

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
INSERT INTO `dzz_setting` VALUES('imagelib', '0');

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
INSERT INTO `dzz_setting`  VALUES ('thumbsize',	'a:3:{s:5:\"small\";a:2:{s:5:\"width\";i:360;s:6:\"height\";i:360;}s:6:\"middle\";a:2:{s:5:\"width\";i:512;s:6:\"height\";i:512;}s:5:\"large\";a:2:{s:5:\"width\";i:1440;s:6:\"height\";i:900;}}');
INSERT INTO `dzz_setting` VALUES('verify', 'a:8:{i:1;a:9:{s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:5:"field";a:1:{s:8:"realname";s:8:"realname";}s:8:"readonly";i:1;s:5:"title";s:12:"实名认证";s:4:"icon";s:31:"common/verify/1/verify_icon.jpg";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:2;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:3;a:8:{s:5:"title";s:0:"";s:4:"desc";s:0:"";s:9:"available";i:0;s:8:"showicon";s:1:"0";s:8:"readonly";N;s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:7:"groupid";a:0:{}}i:4;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:5;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:6;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}i:7;a:4:{s:4:"icon";s:0:"";s:12:"unverifyicon";s:0:"";s:9:"available";i:0;s:5:"title";s:0:"";}s:7:"enabled";b:0;}');
INSERT INTO `dzz_setting` VALUES('systemlog_open', '1');
INSERT INTO `dzz_setting` VALUES('systemlog_setting','a:2:{s:5:"cplog";a:3:{s:5:"title";s:12:"后台访问";s:7:"is_open";i:1;s:8:"issystem";i:1;}s:8:"loginlog";a:3:{s:5:"title";s:13:" 用户登录";s:7:"is_open";i:1;s:8:"issystem";i:1;}}');
INSERT INTO `dzz_setting` VALUES('fileVersion', '1');
INSERT INTO `dzz_setting` VALUES('fileVersionNumber', '50');
INSERT INTO `dzz_setting` VALUES('defaultdepartment', '1');


INSERT INTO `dzz_setting` VALUES ('pichomefilterfileds', 'a:12:{i:0;a:3:{s:3:\"key\";s:3:\"tag\";s:4:\"text\";s:6:\"标签\";s:7:\"checked\";s:1:\"1\";}i:1;a:3:{s:3:\"key\";s:5:\"color\";s:4:\"text\";s:6:\"颜色\";s:7:\"checked\";s:1:\"1\";}i:2;a:3:{s:3:\"key\";s:4:\"link\";s:4:\"text\";s:6:\"链接\";s:7:\"checked\";s:1:\"1\";}i:3;a:3:{s:3:\"key\";s:4:\"desc\";s:4:\"text\";s:6:\"注释\";s:7:\"checked\";s:1:\"1\";}i:4;a:3:{s:3:\"key\";s:8:\"duration\";s:4:\"text\";s:6:\"时长\";s:7:\"checked\";s:1:\"1\";}i:5;a:3:{s:3:\"key\";s:4:\"size\";s:4:\"text\";s:6:\"尺寸\";s:7:\"checked\";s:1:\"1\";}i:6;a:3:{s:3:\"key\";s:3:\"ext\";s:4:\"text\";s:6:\"类型\";s:7:\"checked\";s:1:\"1\";}i:7;a:3:{s:3:\"key\";s:5:\"shape\";s:4:\"text\";s:6:\"形状\";s:7:\"checked\";s:1:\"1\";}i:8;a:3:{s:3:\"key\";s:5:\"grade\";s:4:\"text\";s:6:\"评分\";s:7:\"checked\";s:1:\"1\";}i:9;a:3:{s:3:\"key\";s:5:\"btime\";s:4:\"text\";s:12:\"添加时间\";s:7:\"checked\";s:1:\"1\";}i:10;a:3:{s:3:\"key\";s:8:\"dateline\";s:4:\"text\";s:12:\"修改日期\";s:7:\"checked\";s:1:\"1\";}i:11;a:3:{s:3:\"key\";s:5:\"mtime\";s:4:\"text\";s:12:\"创建日期\";s:7:\"checked\";s:1:\"1\";}}');
INSERT INTO `dzz_setting` VALUES ('overt', '0');
INSERT INTO `dzz_setting` VALUES ('pichomepagesetting', 'a:7:{s:5:\"theme\";s:0:\"\";s:6:\"layout\";s:9:\"waterFall\";s:4:\"show\";s:10:\"name,other\";s:5:\"other\";s:5:\"mtime\";s:4:\"sort\";s:1:\"1\";s:4:\"desc\";s:4:\"desc\";s:8:\"opentype\";s:3:\"new\";}');
INSERT INTO `dzz_setting` VALUES ('pichomeimportallowext', '*.jpg,*.jpeg,*.gif,*.png,*.webp,*.pdf,*.txt,*.mp3,*.mp4,*.webm,*.ogv,*.ogg,*.wav,*.m3u8,*.hls,*.mpg,*.mpeg,*.flv,*.m4v');
INSERT INTO `dzz_setting` VALUES ('pichomeimportnotdir', 'patch,srv,run,lib64,sys,bin,media,boot,etc,sbin,lib,dev,root,usr,proc,tmp,lost+found,lib32,etc.defaults,var.defaults,@*,.*,$*');
INSERT INTO `dzz_setting` VALUES ('ppathinfo', 0);

INSERT INTO `dzz_pichome_theme` (`id`, `themename`, `colors`, `templates`, `selcolor`, `themestyle`, `themefolder`, `dateline`) VALUES (1, '超酷时尚', 'white,dark', NULL, 'dark', 'a:7:{s:5:\"slide\";a:2:{s:10:\"horizontal\";a:4:{s:5:\"title\";s:6:\"横幅\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:10:\"horizontal\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:9:\"1800×450\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:3:\"25%\";}i:1;a:3:{s:5:\"title\";s:9:\"1800×500\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"28%\";}i:2;a:3:{s:5:\"title\";s:9:\"1800×800\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"44%\";}}}s:4:\"full\";a:4:{s:5:\"title\";s:6:\"满屏\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"full\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:9:\"1800×450\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:3:\"25%\";}i:1;a:3:{s:5:\"title\";s:9:\"1800×500\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"28%\";}i:2;a:3:{s:5:\"title\";s:9:\"1800×800\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"44%\";}}}}s:9:\"rich_text\";a:2:{s:3:\"top\";a:4:{s:5:\"title\";s:12:\"顶部分类\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:3:\"top\";s:4:\"size\";a:2:{i:0;a:3:{s:5:\"title\";s:3:\"宽\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:4:\"full\";}i:1;a:3:{s:5:\"title\";s:3:\"窄\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"limit\";}}}s:4:\"left\";a:4:{s:5:\"title\";s:12:\"左侧分类\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"left\";s:4:\"size\";a:2:{i:0;a:3:{s:5:\"title\";s:3:\"宽\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:4:\"full\";}i:1;a:3:{s:5:\"title\";s:3:\"窄\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"limit\";}}}}s:4:\"link\";a:3:{s:10:\"horizontal\";a:3:{s:5:\"title\";s:6:\"横排\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:10:\"horizontal\";}s:4:\"card\";a:3:{s:5:\"title\";s:6:\"卡片\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"card\";}s:4:\"icon\";a:3:{s:5:\"title\";s:6:\"图标\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"icon\";}}s:8:\"question\";a:2:{s:3:\"top\";a:4:{s:5:\"title\";s:12:\"顶部分类\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:3:\"top\";s:4:\"size\";a:2:{i:0;a:3:{s:5:\"title\";s:3:\"宽\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:4:\"full\";}i:1;a:3:{s:5:\"title\";s:3:\"窄\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"limit\";}}}s:4:\"left\";a:4:{s:5:\"title\";s:12:\"左侧分类\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"left\";s:4:\"size\";a:2:{i:0;a:3:{s:5:\"title\";s:3:\"宽\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:4:\"full\";}i:1;a:3:{s:5:\"title\";s:3:\"窄\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"limit\";}}}}s:8:\"file_rec\";a:5:{s:9:\"imageList\";a:3:{s:5:\"title\";s:6:\"网格\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"imageList\";}s:7:\"rowGrid\";a:3:{s:5:\"title\";s:9:\"自适应\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"rowGrid\";}s:6:\"tabodd\";a:3:{s:5:\"title\";s:12:\"列表单列\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:6:\"tabodd\";}s:7:\"tabeven\";a:3:{s:5:\"title\";s:12:\"列表双列\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"tabeven\";}s:7:\"details\";a:3:{s:5:\"title\";s:6:\"详情\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"details\";}}s:6:\"db_ids\";a:6:{s:9:\"waterFall\";a:3:{s:5:\"title\";s:9:\"瀑布流\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"waterFall\";}s:9:\"imageList\";a:3:{s:5:\"title\";s:6:\"网格\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:9:\"imageList\";}s:7:\"rowGrid\";a:3:{s:5:\"title\";s:9:\"自适应\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"rowGrid\";}s:6:\"tabodd\";a:3:{s:5:\"title\";s:12:\"列表单列\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:6:\"tabodd\";}s:7:\"tabeven\";a:3:{s:5:\"title\";s:12:\"列表双列\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"tabeven\";}s:7:\"details\";a:3:{s:5:\"title\";s:6:\"详情\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:7:\"details\";}}s:10:\"manual_rec\";a:7:{s:3:\"one\";a:4:{s:5:\"title\";s:18:\"单排文字居中\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:3:\"one\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:3:\"two\";a:4:{s:5:\"title\";s:18:\"单排文字居下\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"two\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:5:\"three\";a:4:{s:5:\"title\";s:18:\"单排图外文字\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"three\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:4:\"four\";a:4:{s:5:\"title\";s:18:\"双排文字居中\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"four\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:4:\"five\";a:4:{s:5:\"title\";s:18:\"双排文字居下\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:4:\"five\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:3:\"six\";a:4:{s:5:\"title\";s:18:\"双排图外文字\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:3:\"six\";s:4:\"size\";a:3:{i:0;a:3:{s:5:\"title\";s:8:\"266×182\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:9:\"rectangle\";}i:1;a:3:{s:5:\"title\";s:8:\"266×400\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:8:\"vertical\";}i:2;a:3:{s:5:\"title\";s:8:\"266×266\";s:7:\"default\";s:4:\"true\";s:5:\"value\";s:6:\"square\";}}}s:5:\"seven\";a:3:{s:5:\"title\";s:18:\"大图小图混排\";s:7:\"default\";s:5:\"false\";s:5:\"value\";s:5:\"seven\";}}}', 'fashion', 1706533040);

INSERT INTO `dzz_search_template`(`tid`, `title`, `data`, `screen`, `pagesetting`, `searchRange`, `exts`, `dateline`, `disp`) VALUES
(3, '音频', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"},{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"}]', '{\"layout\":\"imageList\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'wav,ogg,mp3,m4a,flac,aac,ape,aiff,amr', 1709696619, 3),
(1, '综合', '', '[{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"details\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', '', 1709696484, 0),
(2, '图片', '', '[{\"key\":\"color\",\"label\":\"\\u989c\\u8272\"},{\"key\":\"link\",\"label\":\"\\u94fe\\u63a5\"},{\"key\":\"desc\",\"label\":\"\\u6ce8\\u91ca\"},{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"},{\"key\":\"size\",\"label\":\"\\u5c3a\\u5bf8\"},{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"shape\",\"label\":\"\\u5f62\\u72b6\"},{\"key\":\"grade\",\"label\":\"\\u8bc4\\u5206\"},{\"key\":\"btime\",\"label\":\"\\u6dfb\\u52a0\\u65f6\\u95f4\"},{\"key\":\"dateline\",\"label\":\"\\u4fee\\u6539\\u65e5\\u671f\"},{\"key\":\"mtime\",\"label\":\"\\u521b\\u5efa\\u65e5\\u671f\"},{\"key\":\"level\",\"label\":\"\\u5bc6\\u7ea7\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"waterFall\",\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"0\"}', '', 'svg,png,jpg,jpeg,jpe,webp,jfif,ico,heic,gif,eps,bmp,tga,hdr,exr,dds,ppm,pnm,pgm,pdd,pcx,pbm,pam,mpo,mng,miff,jpx,jps,jpf,jpc,jp2,j2k,j2c,dib,cur,cin,tif,wmf,emf,tiff,psd,ai,3fr,arw,cr2,cr3,crw,dng,erf,mrw,nef,nrw,orf,otf,pef,raf,raw,rw2,sr2,srw,x3f', 1709696566, 2),
(4, '视频', '', '[{\"key\":\"duration\",\"label\":\"\\u65f6\\u957f\"},{\"key\":\"shape\",\"label\":\"\\u5f62\\u72b6\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"rowGrid\",\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'wmv,webm,mp4,mov,m4v,avi,ts,swf,rmvb,rm,mkv,flv,vob,trp,sct,ogv,mxf,mpg,m2ts,f4v,dv,dcr,asf,3g2p', 1709696675, 4),
(5, '文档', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"tabodd\",\"display\":[\"name\",\"extension\",\"other\"],\"other\":\"filesize\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'xlsx,xls,pptx,ppt,pdf,docx,doc,pdf,txt,rtf,odt,htm,html,mht,pps,ppsx,odp,ods,csv', 1709696731, 5),
(6, '其它', '', '[{\"key\":\"ext\",\"label\":\"\\u7c7b\\u578b\"},{\"key\":\"tag\",\"label\":\"\\u6807\\u7b7e\",\"group\":\"\",\"auto\":\"0\",\"sort\":\"hot\"}]', '{\"layout\":\"tabodd\",\"display\":[\"name\",\"extension\"],\"other\":\"btime\",\"sort\":\"btime\",\"desc\":\"desc\",\"opentype\":\"current\",\"filterstyle\":\"1\"}', '', 'zip,rar,7z', 1709696795, 6);

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


INSERT INTO `dzz_app_open` (`ext`, `appid`, `disp`, `extid`, `isdefault`) VALUES
('pdf', 3, 0, 1, 0),
('doc', 3, 0, 2, 0),
('docx', 3, 0, 3, 0),
('rtf', 3, 0, 4, 0),
('odt', 3, 0, 5, 0),
('htm', 3, 0, 6, 0),
('html', 3, 0, 7, 0),
('mht', 3, 0, 8, 0),
('txt', 3, 0, 9, 0),
('ppt', 3, 0, 10, 0),
('pptx', 3, 0, 11, 0),
('pps', 3, 0, 12, 0),
('ppsx', 3, 0, 13, 0),
('odp', 3, 0, 14, 0),
('xls', 3, 0, 15, 0),
('xlsx', 3, 0, 16, 0),
('ods', 3, 0, 17, 0),
('csv', 3, 0, 18, 0),
('mp3', 7, 0, 19, 0),
('mp4', 7, 0, 20, 0),
('flv', 7, 0, 21, 0),
('webm', 7, 0, 22, 0),
('ogv', 7, 0, 23, 0),
('ogg', 7, 0, 24, 0),
('wav', 7, 0, 25, 0),
('m3u8', 7, 0, 26, 0),
('hls', 7, 0, 27, 0),
('mpg', 7, 0, 28, 0),
('avi', 7, 0, 29, 0),
('rm', 7, 0, 30, 0),
('rmvb', 7, 0, 31, 0),
('mkv', 7, 0, 32, 0),
('mov', 7, 0, 33, 0),
('wmv', 7, 0, 34, 0),
('asf', 7, 0, 35, 0),
('mpg', 7, 0, 36, 0),
('mpeg', 7, 0, 37, 0),
('f4v', 7, 0, 38, 0),
('vob', 7, 0, 39, 0),
('ogv', 7, 0, 40, 0),
('mts', 7, 0, 41, 0),
('m2ts', 7, 0, 42, 0),
('mpe', 7, 0, 43, 0),
('ogg', 7, 0, 44, 0),
('3gp', 7, 0, 45, 0),
('flv', 7, 0, 46, 0),
('midi', 7, 0, 47, 0),
('wma', 7, 0, 48, 0),
('vqf', 7, 0, 49, 0),
('ra', 7, 0, 50, 0),
('aac', 7, 0, 51, 0),
('flac', 7, 0, 52, 0),
('ape', 7, 0, 53, 0),
('amr', 7, 0, 54, 0),
('aiff', 7, 0, 55, 0),
('au', 7, 0, 56, 0),
('m4a', 7, 0, 57, 0),
('m4v', 7, 0, 58, 0),
('txt', 8, 0, 59, 1),
('php', 8, 0, 60, 1),
('js', 8, 0, 61, 1),
('jsp', 8, 0, 62, 1),
('htm', 8, 0, 63, 1),
('html', 8, 0, 64, 1),
('jsp', 8, 0, 65, 1),
('asp', 8, 0, 66, 1),
('aspx', 8, 0, 67, 1),
('QCOS::pptx', 10, 0, 68, 1),
('QCOS::ppt', 10, 0, 69, 1),
('QCOS::pot', 10, 0, 70, 1),
('QCOS::potx', 10, 0, 71, 1),
('QCOS::pps', 10, 0, 72, 1),
('QCOS::ppsx', 10, 0, 73, 1),
('QCOS::dps', 10, 0, 74, 1),
('QCOS::dpt', 10, 0, 75, 1),
('QCOS::pptm', 10, 0, 76, 1),
('QCOS::potm', 10, 0, 77, 1),
('QCOS::ppsm', 10, 0, 78, 1),
('QCOS::doc', 10, 0, 79, 1),
('QCOS::dot', 10, 0, 80, 1),
('QCOS::wps', 10, 0, 81, 1),
('QCOS::wpt', 10, 0, 82, 1),
('QCOS::docx', 10, 0, 83, 1),
('QCOS::dotx', 10, 0, 84, 1),
('QCOS::docm', 10, 0, 85, 1),
('QCOS::dotm', 10, 0, 86, 1),
('QCOS::xls', 10, 0, 87, 1),
('QCOS::xlt', 10, 0, 88, 1),
('QCOS::et', 10, 0, 89, 1),
('QCOS::ett', 10, 0, 90, 1),
('QCOS::xlsx', 10, 0, 91, 1),
('QCOS::xltx', 10, 0, 92, 1),
('QCOS::csv', 10, 0, 93, 1),
('QCOS::xlsb', 10, 0, 94, 1),
('QCOS::xlsm', 10, 0, 95, 1),
('QCOS::xltm', 10, 0, 96, 1),
('QCOS::ets', 10, 0, 97, 1),
('QCOS::pdf', 10, 0, 98, 1),
('QCOS::lrc', 10, 0, 99, 1),
('QCOS::c', 10, 0, 100, 01),
('QCOS::cpp', 10, 0, 101, 1),
('QCOS::h', 10, 0, 102, 1),
('QCOS::asm', 10, 0, 103, 1),
('QCOS::s', 10, 0, 104, 1),
('QCOS::java', 10, 0, 105, 1),
('QCOS::asp', 10, 0, 106, 1),
('QCOS::bat', 10, 0, 107, 1),
('QCOS::bas', 10, 0, 108, 1),
('QCOS::prg', 10, 0, 109, 1),
('QCOS::cmd', 10, 0, 110, 1),
('QCOS::rtf', 10, 0, 111, 1),
('QCOS::txt', 10, 0, 112, 1),
('QCOS::log', 10, 0, 113, 1),
('QCOS::xml', 10, 0, 114, 1),
('QCOS::htm', 10, 0, 115, 1),
('QCOS::html', 10, 0, 116, 1),
('pdf', 11, 0, 117, 1);
--
-- 转存表中的数据 `dzz_user_field`
--

INSERT INTO `dzz_user_field` (`uid`, `docklist`, `screenlist`, `applist`, `noticebanlist`, `iconview`, `iconposition`, `direction`, `autolist`, `taskbar`, `dateline`, `updatetime`, `attachextensions`, `maxattachsize`, `usesize`, `addsize`, `buysize`, `wins`, `perm`, `privacy`) VALUES
(1, '', '', '1,10', '', 2, 0, 0, 1, 'bottom', 0, 0, '-1', -1, 0, 0, 0, '', 0, '');














