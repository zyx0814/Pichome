<?php
$_config = array();

// ----------------------------  CONFIG DB  ----------------------------- //
// ----------------------------  数据库相关设置---------------------------- //

/**
 * 数据库主服务器设置, 支持多组服务器设置, 当设置多组服务器时, 则会根据分布式策略使用某个服务器
 * @example
 * $_config['db']['1']['dbhost'] = 'localhost'; // 服务器地址
 * $_config['db']['1']['dbuser'] = 'root'; // 用户
 * $_config['db']['1']['dbpw'] = '';// 密码
 * $_config['db']['1']['dbcharset'] = 'gbk';// 字符集
 * $_config['db']['1']['pconnect'] = '0';// 是否持续连接
 * $_config['db']['1']['dbname'] = 'x1';// 数据库
 * $_config['db']['1']['tablepre'] = 'pre_';// 表名前缀
 *
 * $_config['db']['2']['dbhost'] = 'localhost';
 * ...
 */
$_config['db'][1]['dbhost']  		= 'localhost';//支持三种直接加端口如：127.0.0.1:3306或使用UNix socket 如：/tmp/mysql.sock
$_config['db'][1]['dbuser']  		= 'root';
$_config['db'][1]['dbpw'] 	 		= 'root';
$_config['db'][1]['dbcharset'] 		= 'utf8';
$_config['db'][1]['pconnect'] 		= 0;
$_config['db'][1]['dbname']  		= 'pichome';
$_config['db'][1]['tablepre'] 		= 'pichome_';
$_config['db'][1]['port'] = '3306';//mysql端口
$_config['db'][1]['unix_socket'] = '';//使用此方式连接时 dbhost设置为localhost

/**
 * 数据库从服务器设置( slave, 只读 ), 支持多组服务器设置, 当设置多组服务器时, 系统根据每次随机使用
 * @example
 * $_config['db']['1']['slave']['1']['dbhost'] = 'localhost';
 * $_config['db']['1']['slave']['1']['dbuser'] = 'root';
 * $_config['db']['1']['slave']['1']['dbpw'] = 'root';
 * $_config['db']['1']['slave']['1']['dbcharset'] = 'gbk';
 * $_config['db']['1']['slave']['1']['pconnect'] = '0';
 * $_config['db']['1']['slave']['1']['dbname'] = 'x1';
 * $_config['db']['1']['slave']['1']['tablepre'] = 'pre_';
 * $_config['db']['1']['slave']['1']['weight'] = '0'; //权重：数据越大权重越高
 *
 * $_config['db']['1']['slave']['2']['dbhost'] = 'localhost';
 * ...
 *
 */
$_config['db']['1']['slave'] = array();

//启用从服务器的开关
$_config['db']['slave'] = false;
/**
 * 数据库 分布部署策略设置
 *
 * @example 将 user 部署到第二服务器, session 部署在第三服务器, 则设置为
 * $_config['db']['map']['user'] = 2;
 * $_config['db']['map']['session'] = 3;
 *
 * 对于没有明确声明服务器的表, 则一律默认部署在第一服务器上
 *
 */
$_config['db']['map'] = array();

/**
 * 数据库 公共设置, 此类设置通常对针对每个部署的服务器
 */
$_config['db']['common'] = array();

/**
 *  禁用从数据库的数据表, 表名字之间使用逗号分割
 *
 * @example session, user 这两个表仅从主服务器读写, 不使用从服务器
 * $_config['db']['common']['slave_except_table'] = 'session, user';
 *
 */
$_config['db']['common']['slave_except_table'] = '';



/**
 * 内存服务器优化设置
 * 以下设置需要PHP扩展组件支持，其中 memcache 优先于其他设置，
 * 当 memcache 无法启用时，会自动开启另外的两种优化模式
 */

//内存变量前缀, 可更改,避免同服务器中的程序引用错乱
$_config['memory']['prefix'] = 'oaooa_';

/* reids设置, 需要PHP扩展组件支持, timeout参数的作用没有查证 */
$_config['memory']['redis']['server'] = '';
$_config['memory']['redis']['port'] = 6379;
$_config['memory']['redis']['pconnect'] = 1;
$_config['memory']['redis']['timeout'] = 0;
$_config['memory']['redis']['requirepass'] = '';//如果redis需要密码，请填写redis密码
/**
 * 是否使用 Redis::SERIALIZER_IGBINARY选项,需要igbinary支持,windows下测试时请关闭，否则会出>现错误Reading from client: Connection reset by peer
 * 支持以下选项，默认使用PHP的serializer
 * Redis::SERIALIZER_IGBINARY =2
 * Redis::SERIALIZER_PHP =1
 * Redis::SERIALIZER_NONE =0 //则不使用serialize,即无法保存array
 */
$_config['memory']['redis']['serializer'] = 1;

$_config['memory']['memcache']['server'] = ''; // memcache 服务器地址
$_config['memory']['memcache']['port'] = 11211;			// memcache 服务器端口
$_config['memory']['memcache']['pconnect'] = 1;			// memcache 是否长久连接
$_config['memory']['memcache']['timeout'] = 1;			// memcache 服务器连接超时

$_config['memory']['memcached']['server'] = '127.0.0.1'; // memcached 服务器地址
$_config['memory']['memcached']['port'] = 11211;		// memcached 服务器端口
$_config['memory']['memcached']['pconnect'] = 1;		// memcached 是否长久连接
$_config['memory']['memcached']['timeout'] = 1;			// memcached 服务器连接超时

$_config['memory']['apc'] = 1;							// 启动对 apc 的支持
$_config['memory']['xcache'] = 1;						// 启动对 xcache 的支持
$_config['memory']['eaccelerator'] = 0;					// 启动对 eaccelerator 的支持
$_config['memory']['wincache'] = 1;						// 启动对 wincache 的支持


// 服务器相关设置
$_config['server']['id']		= 1;			// 服务器编号，多webserver的时候，用于标识当前服务器的ID

//计划任务设置
$_config['remote']['on']=0; //1：设定计划任务由外部触发；0：通过用户访问触发
$_config['remote']['cron']=0; //1：设定计划任务由外部触发；0：通过用户访问触发
//  CONFIG CACHE
$_config['cache']['type'] 			= 'sql';	// 缓存类型 file=文件缓存, sql=数据库缓存

// 页面输出设置
$_config['output']['charset'] 			= 'utf-8';	// 页面字符集
$_config['output']['forceheader']		= 1;		// 强制输出页面字符集，用于避免某些环境乱码
$_config['output']['gzip'] 			    = 0;		// 是否采用 Gzip 压缩输出
$_config['output']['tplrefresh'] 		= 1;		// 模板自动刷新开关 0=关闭, 1=打开


$_config['output']['language'] 			= 'zh-CN';	// 页面语言 zh-cn/zh-tw
$_config['output']['language_list']['zh-CN']='简体中文';	// 页面语言 zh-CN/en-US
$_config['output']['language_list']['en-US']='English';	// 页面语言 zh-CN/en-US

$_config['output']['staticurl'] 		= 'static/';	// 站点静态文件路径，“/”结尾
$_config['output']['ajaxvalidate']		= 0;		// 是否严格验证 Ajax 页面的真实性 0=关闭，1=打开
$_config['output']['iecompatible']		= 0;		// 页面 IE 兼容模式


$_config['cookie']['cookiepre'] 		= 'oaooa_'; 	// COOKIE前缀
$_config['cookie']['cookiedomain'] 		= ''; 		// COOKIE作用域
$_config['cookie']['cookiepath'] 		= '/'; 		// COOKIE作用路径

// 站点安全设置
$_config['security']['authkey']	            = 'oaooa';	// 站点加密密钥
$_config['security']['urlxssdefend']		= true;		// 自身 URL XSS 防御
$_config['security']['attackevasive']		= 0;		// CC 攻击防御 1|2|4|8

$_config['security']['querysafe']['status']	= 1;		// 是否开启SQL安全检测，可自动预防SQL注入攻击
$_config['security']['querysafe']['dfunction']	= array('load_file','hex','substring','if','ord','char');
$_config['security']['querysafe']['daction']	= array('@','intooutfile','intodumpfile','unionselect','(select', 'unionall', 'uniondistinct');
$_config['security']['querysafe']['dnote']	= array('/*','*/','#','--','"');
$_config['security']['querysafe']['dlikehex']	= 1;
$_config['security']['querysafe']['afullnote']	= 0;

$_config['admincp']['founder']			= '1';		// 站点创始人：拥有站点管理后台的最高权限，每个站点可以设置 1名或多名创始人
// 可以使用uid，也可以使用用户名；多个创始人之间请使用逗号“,”分开;
$_config['admincp']['checkip']			= 1;		// 后台管理操作是否验证管理员的 IP, 1=是[安全], 0=否。仅在管理员无法登录后台时设置 0。
$_config['admincp']['runquery']			= 0;		// 是否允许后台运行 SQL 语句 1=是 0=否[安全]
$_config['admincp']['dbimport']			= 0;		// 是否允许后台恢复网站数据  1=是 0=否[安全]
$_config['admincp']['checksession']     =1800;      //0不判定管理员session超时,xx登录超时时间
$_config['userlogin']['checkip']		= 1; 		//用户登录错误验证ip，对于同一ip同时使用时建议设置为0,否则当有一位用户登录错误次数超过5次，该ip被锁定15分钟，导致其他的同IP用户无法登录;
$_config['userlogin']['checksession']   = 0; 		//用户登录超时，xx秒后自动失去登录状态，0时不会自动失去登录状态;


// --------------------------  CONFIG PICHOME  -------------------------- //

$_config['pichomecloseshare'] = 0;    //关闭分享

$_config['pichomeclosecollect'] = 0;  //关闭收藏

$_config['pichomeclosedownload'] = 0;  //关闭下载

//缩略图默认设置
$_config['pichomethumsmallwidth']= 360;
$_config['pichomethumsmallheight']= 360;
$_config['pichomethumlargewidth'] = 1920;
$_config['pichomethumlargeheight'] = 1080;
$_config['gdgetcolorextlimit'] = 'jpg,png,jpeg,gif'; //gd颜色后缀
$_config['imageickallowextlimit'] = 'aai,art,arw,avs,bpg,bmp,bmp2,bmp3,brf,cals,cals,cgm,cin,cip,cmyk,cmyka,cr2,crw,cube,cur,cut,dcm,dcr,dcx,dds,dib,djvu,dng,dot,dpx,emf,epdf,epi,eps,eps2,eps3,epsf,epsi,ept,exr,fax,fig,fits,fpx,gplt,gray,graya,hdr,heic,hpgl,hrz,ico,info,isobrl,isobrl6,jbig,jng,jp2,jpt,j2c,j2k,jxr,json,man,mat,miff,mono,mng,m2v,mpc,mpr,mrwmmsl,mtv,mvg,nef,orf,otb,p7,palm,pam,clipboard,pbm,pcd,pcds,pcl,pcx,pdb,pef,pes,pfa,pfb,pfm,pgm,picon,pict,pix,png8,png00,png24,png32,png48,png64,pnm,ppm,ps,ps2,ps3,psb,psd,ptif,pwp,rad,raf,rgb,rgb565,rgba,rgf,rla,rle,sfw,sgi,shtml,sid,mrsid,sum,svg,text,tga,tif,tiff,tim,ttf,ubrl,ubrl6,uil,uyvy,vicar,viff,wbmp,wpg,webp,wmf,wpg,x,xbm,xcf,xpm,xwd,x3f,YCbCr,YCbCrA,yuv,sr2,srf,srw,rw2,nrw,mrw,kdc,erf,canvas,caption,clip,clipboard,fractal,gradient,hald,histogram,inline,map,mask,matte,null,pango,plasma,preview,print,scan,radial_gradient,scanx,screenshot,stegano,tile,unique,vid,win,xc,granite,logo,netscpe,rose,wizard,bricks,checkerboard,circles,crosshatch,crosshatch30,crosshatch45,fishscales,gray0,gray5,gray10,gray15,gray20,gray25,gray30,gray35,gray40,gray45,gray50,gray55,gray60,gray65,gray70,gray75,gray80,gray85,gray90,gray95,gray100,hexagons,horizontal,horizontal2,horizontal3,horizontalsaw,hs_bdiagonal,hs_cross,hs_diagcross,hs_fdiagonal,hs_vertical,left30,left45,leftshingle,octagons,right30,right45,rightshingle,smallfishcales,vertical,vertical2,vertical3,verticalfishingle,vericalrightshingle,verticalleftshingle,verticalsaw,fff,3fr,ai,iiq,cdr'; //gd颜色后缀

$_config['pichomespecialimgext'] = 'aai,art,arw,avs,bpg,bmp,bmp2,bmp3,brf,cals,cals,cgm,cin,cip,cmyk,cmyka,cr2,crw,cube,cur,cut,dcm,dcr,dcx,dds,dib,djvu,dng,dot,dpx,emf,epdf,epi,eps,eps2,eps3,epsf,epsi,ept,exr,fax,fig,fits,fpx,gplt,gray,graya,hdr,heic,hpgl,hrz,ico,info,isobrl,isobrl6,jbig,jng,jp2,jpt,j2c,j2k,jxr,json,man,mat,miff,mono,mng,m2v,mpc,mpr,mrwmmsl,mtv,mvg,nef,orf,otb,p7,palm,pam,clipboard,pbm,pcd,pcds,pcl,pcx,pdb,pef,pes,pfa,pfb,pfm,pgm,picon,pict,pix,png8,png00,png24,png32,png48,png64,pnm,ppm,ps,ps2,ps3,psb,psd,ptif,pwp,rad,raf,rgb,rgb565,rgba,rgf,rla,rle,sfw,sgi,shtml,sid,mrsid,sum,text,tga,tif,tiff,tim,ttf,ubrl,ubrl6,uil,uyvy,vicar,viff,wbmp,wpg,wmf,wpg,x,xbm,xcf,xpm,xwd,x3f,YCbCr,YCbCrA,yuv,sr2,srf,srw,rw2,nrw,mrw,kdc,erf,canvas,caption,clip,clipboard,fractal,gradient,hald,histogram,inline,map,mask,matte,null,pango,plasma,preview,print,scan,radial_gradient,scanx,screenshot,stegano,tile,unique,vid,win,xc,granite,logo,netscpe,rose,wizard,bricks,checkerboard,circles,crosshatch,crosshatch30,crosshatch45,fishscales,gray0,gray5,gray10,gray15,gray20,gray25,gray30,gray35,gray40,gray45,gray50,gray55,gray60,gray65,gray70,gray75,gray80,gray85,gray90,gray95,gray100,hexagons,horizontal,horizontal2,horizontal3,horizontalsaw,hs_bdiagonal,hs_cross,hs_diagcross,hs_fdiagonal,hs_vertical,left30,left45,leftshingle,octagons,right30,right45,rightshingle,smallfishcales,vertical,vertical2,vertical3,verticalfishingle,vericalrightshingle,verticalleftshingle,verticalsaw,fff,3fr,ai,iiq,cdr'; //gd颜色后缀
$_config['pichomecommimageext'] = 'jpg,png,jpeg,gif,svg,webp';//pichome直接预览图片后缀
//本地文档支持格式
$_config['onlyofficeviewextlimit'] = 'pdf,doc,docx,rtf,odt,htm,html,mht,txt,ppt,pptx,pps,ppsx,odp,xls,xlsx,ods,csv';

//腾讯云格式支持
$_config['qcosmedia']='3gp,avi,flv,mp4,m3u8,mpg,asf,wmv,mkv,mov,ts,webm,mxf';
$_config['qcosoffice']='pptx,ppt,pot,potx,pps,ppsx,dps,dpt,pptm,potm,ppsm,doc,dot,wps,wpt,docx,dotx,docm,dotm,xls,xlt,et,ett,xlsx,xltx,csv,xlsb,xlsm,xltm,ets,pdf,lrc,c,cpp,h,asm,s,java,asp,bat,bas,prg,cmd,rtf,txt,log,xml,htm,html';
$_config['qcosimage']='jpg,bmp,gif,png,webp';

//ffmpeg默认设置
$_config['pichomeffmpegposition'] = '';
$_config['pichomeffprobeposition'] = '';
$_config['pichomeffmpeggetvieoinfoext']= 'avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,mt2s,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,mxf';  //ffmpeg支持获取音视频信息的后缀
$_config['pichomeffmpeggetthumbext']= 'avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpe,mpeg,mp4,m4v,mpeg,f4v,vob,ogv,mts,m2ts,3gp,webm,flv,wav,mp3,ogg,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,mxf'; //ffmpeg支持获取音视频缩略图后缀
$_config['pichomeffmpegconvertext']= 'avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpeg,f4v,vob,ogv,mts,m2ts,mpe,3gp,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,m4v,mxf'; //ffmpeg支持转码后缀

//支持转码后缀
$_config['pichomeplayermediaext'] = 'mp3,mp4,webm,ogv,ogg,wav,m3u8,hls,mpg,mpeg';
$_config['pichomeconvertext'] = 'webm,ogv,ogg,wav,m3u8,hls,mpg,3gp,avi,flv,mp4,asf,wmv,mkv,mov,ts,mxf';

$_config['pichomexgplayer'] = 'mp3,mp4,flv,webm,ogv,ogg,wav,m3u8,hls,mpg,avi,rm,rmvb,mkv,mov,wmv,asf,mpg,mpeg,f4v,vob,ogv,mts,m2ts,mpe,ogg,3gp,flv,midi,wma,vqf,ra,aac,flac,ape,amr,aiff,au,m4a,m4v';

//转码质量参数
$_config['videoquality'][0]['name'] = '流畅';
$_config['videoquality'][0]['width'] = 640;
$_config['videoquality'][0]['height'] = 360;
$_config['videoquality'][0]['bitrate'] = 400;

$_config['videoquality'][1]['name'] = '标清';
$_config['videoquality'][1]['width'] = 960;
$_config['videoquality'][1]['height'] = 510;
$_config['videoquality'][1]['bitrate'] = 900;

$_config['videoquality'][2]['name'] = '高清';
$_config['videoquality'][2]['width'] = 1280;
$_config['videoquality'][2]['height'] = 720;
$_config['videoquality'][2]['bitrate'] = 1500;

$_config['videoquality'][3]['name'] = '超清';
$_config['videoquality'][3]['width'] = 1920;
$_config['videoquality'][3]['height'] = 1080;
$_config['videoquality'][3]['bitrate'] = 3000;

$_config['videoquality'][4]['name'] = '2k';
$_config['videoquality'][4]['width'] = 3500;
$_config['videoquality'][4]['height'] = 2560;
$_config['videoquality'][4]['bitrate'] = 1440;

$_config['videoquality'][5]['name'] = '4k';
$_config['videoquality'][5]['width'] = 3840;
$_config['videoquality'][5]['height'] = 2160;
$_config['videoquality'][5]['bitrate'] = 6000;

$_config['defaultvideoquality'] = 1;

//转换缩略图进程数
$_config['thumbprocessnum'] = 1;
//获取信息进程数
$_config['infoprocessnum'] = 1;
//转换缩略图进程数
$_config['convertprocessnum'] = 1;
//imagick支持缩略图格式
$_config['imagickthumext']='aai,art,arw,avs,bpg,bmp,bmp2,bmp3,brf,cals,cals,cgm,cin,cip,cmyk,cmyka,cr2,crw,cube,cur,cut,dcm,dcr,dcx,dds,dib,djvu,dng,dot,dpx,emf,epdf,epi,eps,eps2,eps3,epsf,epsi,ept,exr,fax,fig,fits,fpx,gplt,gray,graya,hdr,heic,hpgl,hrz,ico,info,isobrl,isobrl6,jbig,jng,jp2,jpt,j2c,j2k,jxr,json,man,mat,miff,mono,mng,m2v,mpc,mpr,mrwmmsl,mtv,mvg,nef,orf,otb,p7,palm,pam,clipboard,pbm,pcd,pcds,pcl,pcx,pdb,pef,pes,pfa,pfb,pfm,pgm,picon,pict,pix,png8,png00,png24,png32,png48,png64,pnm,ppm,ps,ps2,ps3,psb,psd,ptif,pwp,rad,raf,rgb,rgb565,rgba,rgf,rla,rle,sfw,sgi,shtml,sid,mrsid,sum,svg,text,tga,tif,tiff,tim,ttf,ubrl,ubrl6,uil,uyvy,vicar,viff,wbmp,wpg,webp,wmf,wpg,x,xbm,xcf,xpm,xwd,x3f,YCbCr,YCbCrA,yuv,sr2,srf,srw,rw2,nrw,mrw,kdc,erf,canvas,caption,clip,clipboard,fractal,gradient,hald,histogram,inline,map,mask,matte,null,pango,plasma,preview,print,scan,radial_gradient,scanx,screenshot,stegano,tile,unique,vid,win,xc,granite,logo,netscpe,rose,wizard,bricks,checkerboard,circles,crosshatch,crosshatch30,crosshatch45,fishscales,gray0,gray5,gray10,gray15,gray20,gray25,gray30,gray35,gray40,gray45,gray50,gray55,gray60,gray65,gray70,gray75,gray80,gray85,gray90,gray95,gray100,hexagons,horizontal,horizontal2,horizontal3,horizontalsaw,hs_bdiagonal,hs_cross,hs_diagcross,hs_fdiagonal,hs_vertical,left30,left45,leftshingle,octagons,right30,right45,rightshingle,smallfishcales,vertical,vertical2,vertical3,verticalfishingle,vericalrightshingle,verticalleftshingle,verticalsaw,fff,3fr,ai,iiq,cdr';
//缩略图地址模式
$_config['thumburlmod'] = 0;//0根据存储位置选择地址，1，一律使用服务器中转
$_config['audiothumetime'] = 5;//视频获取缩略图位置，默认5秒的位置
return $_config;
