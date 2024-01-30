var _upload = {};
var attachextensions = '';
var maxfileSize = null;
_upload.total = 0;
_upload.completed = 0;
_upload.succeed = 0;//成功数量
_upload.errored = 0;//错误数量
_upload.ismin = 1;
_upload.tips = $('#upload_file_tips');
_upload.el = $('#uploading_file_list');
_upload.filelist = $('.fileList');
_upload.fid = null;
_upload.maxli = 10;//设置为0时，不缓存添加数据功能
_upload.datas = [];
_upload.speedTimer;
_upload.parameter = function () {
    maxfileSize = parseInt(_explorer.space.maxattachsize) > 0 ? parseInt(_explorer.space.maxattachsize) : null;
}

// 计算签名
var getAuthorization = function (options, callback) {
    var url = SITEURL + '/dzz/qcos/api/cos-js-sdk-v5-master/server/sts.php';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onload = function (e) {
        try {
            var data = JSON.parse(e.target.responseText);
            var credentials = data.credentials;
        } catch (e) {
        }
        callback({
            TmpSecretId: credentials.tmpSecretId,
            TmpSecretKey: credentials.tmpSecretKey,
            XCosSecurityToken: credentials.sessionToken,
            ExpiredTime: data.expiredTime, // SDK 在 ExpiredTime 时间前，不会再次调用 getAuthorization
        });
    };
    xhr.send();
};
var qcosuploadconfig = {
    Bucket: clouddata.bucket,
    Region: clouddata.region,
    uid:uid
};
var cos = {};
var change_attachtype = function (fid) {
    if (_explorer.sourcedata.folder[fid]) {
        attachextensions = _explorer.sourcedata.folder[fid].allow_exts;

        var accept = [];
        if (attachextensions) {
            var spli = attachextensions.split('|');

            for (var n in spli) {
                accept.push('.' + spli[n])
            }
            accept = accept.join(',');
        } else {
            accept = '*';
        }
        jQuery('#wangpan-upload-file').attr('accept', accept)
        if (attachextensions) {
            attachextensions = "(\.|\/)(" + (attachextensions) + ")$";
        } else {
            attachextensions = "\.*$";
        }
    }
};

// 对更多字符编码的 url encode 格式
var camSafeUrlEncode = function (str) {
    return encodeURIComponent(str)
        .replace(/!/g, '%21')
        .replace(/'/g, '%27')
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29')
        .replace(/\*/g, '%2A');
};
var fileupload = function (el, fid) {
    console.log('aaaa');
    //qcosuploadconfig.fid = fid;
    cos = new COS({
        getAuthorization: getAuthorization,//获取签名
        FileParallelLimit: 3,    // 控制文件上传并发数
        ChunkParallelLimit: 16,   // 控制单个文件下分片上传并发数
        ChunkSize: 1024*1024*2,  // 控制分片大小，单位 B
        ProgressInterval: 1,  // 控制 onProgress 回调的间隔
        ChunkRetryTimes: 3,   // 控制文件切片后单片上传失败后重试次数
        UploadCheckContentMd5: true,   // 上传过程计算 Content-MD5
    });
};
//实例化对象
$(document).on('change', '#wangpan-upload-folder,#wangpan-upload-file', function () {
    fileupload();
    fileuploads(this);

});

window.onload = function () {
    var dropfiles = [];
     function dragHover(e) {
			e.dataTransfer.dropEffect = 'copy';
        	e.preventDefault();
			$('#mask_layer').show();
    }
	function dragleave(e) {
		$('#mask_layer').hide();
    }

   /* document.getElementById('middleconMenu').addEventListener('dragover', dragHover);
    document.getElementById('middleconMenu').addEventListener('dragleave', dragHover);

    document.getElementById('middleconMenu').addEventListener('drop', function (e) {
        //dropfiles=[];
		$('#mask_layer').hide();
		//判断权限
		try{
			if(!(_filemanage.fid>0 && _explorer.Permission_Container('folder', fid))){
				return false;
			}
		}catch(e){}
        e.stopPropagation();
        e.preventDefault();
      
        var dropuploadfile = function (tmpfile){
            //tmpfile.path = path;
            //dropfiles.push(tmpfile);
            fileuploads([tmpfile],'drop');
        }

        var iterateFilesAndDirs = function (filesAndDirs, path) {
            for (var i = 0; i < filesAndDirs.length; i++) {
                if (typeof filesAndDirs[i].getFilesAndDirectories === 'function') {
                    let path = filesAndDirs[i].path;
                    // this recursion enables deep traversal of directories
                    filesAndDirs[i].getFilesAndDirectories().then(function (subFilesAndDirs) {
                        // iterate through files and directories in sub-directory
                      iterateFilesAndDirs(subFilesAndDirs, path);
                    });
                } else {
                    filesAndDirs[i].path = path;

                   dropuploadfile(filesAndDirs[i]);
                }
            }
        };
        // begin by traversing the chosen files and directories
        if ('getFilesAndDirectories' in e.dataTransfer) {
           dropfiles = [];
            e.dataTransfer.getFilesAndDirectories().then(function (filesAndDirs) {
                     iterateFilesAndDirs(filesAndDirs, '/');
            });
        }
    });*/

};
//上传文件唯一索引,用于计数当前上传运行中的个数
var fileuploadindex = 0;
//盛放待上传文件集合
var filedatas = [];
//上传成功文件数
var uploadsuccess = 0;
//上传li唯一id前缀
var currentuploadpre= 'uploadpre_';
//组合li唯一id计数
var currentuploadid = 0;
//上传失败的文件数据对象集合
var uploadfailddata = {};
//上传失败文件li唯一id集合
var uploadfailddataarr = [];
//待上传文件总大小和已上传文件总大小
var repareuploadsize = uploadedsize = 0;
var uploadspeed = 0;
function fileuploads(el, type) {
    //change_attachtype(qcosuploadconfig.fid);
    if (type == 'drop') {
       var files = el;
    } else {
         var files = document.getElementById(el.id).files;
    }
    for (var o in files) {
        if (files[o].name && files[o].name != 'item') {
            if(!files[o].webkitRelativePath && files[o].path){
                files[o].Key = 'tmpupload/' + qcosuploadconfig.uid + files[o].path+'/'+files[o].name;
            }else{
                files[o].Key = 'tmpupload/' + qcosuploadconfig.uid + '/' + ((files[o].webkitRelativePath) ? files[o].webkitRelativePath : files[o].name);
            }
            //files[o].Key = encodeURI(files[o].Key);
            currentuploadid ++;
            var tmpfile = {
                Key:  files[o].Key,
                Body: files[o],
                Bucket: qcosuploadconfig.Bucket,
                Region: qcosuploadconfig.Region,
                Onlyid:currentuploadpre+currentuploadid
            };
            repareuploadsize += tmpfile.Body.size;
			
           /* _upload.tips.show();
            var li = jQuery('<li class="dialog-file-list" data-id="0" id="'+tmpfile.Onlyid+'"></li>');
            _upload.el.append(li);
            li.append(_upload.getItemTpl(tmpfile.Body));
            _upload.uploadadd();
            */
            filedatas.push(tmpfile);
        }
    }
    //_upload.tips.find('.dialog-header-narrow').trigger('click.icon');
    readFiledatas();
   }
function readFiledatas(){
    if (filedatas.length == 0) return false;
    if( fileuploadindex < 10 ){
        for(var i=0;i <(10-fileuploadindex);i++){
            var uploadfiledata = filedatas.shift();
           if(uploadfiledata) douploadfile(uploadfiledata);
        }
}

}
//尝试重新上传
function uploadtryagain(obj){
    var id = $(obj).closest('li').get(0).id;
    douploadfile(uploadfailddata[id]);

}
function douploadfile(uplodfiledata){
    if(!_upload.speedTimer) _upload.speedTimer= setInterval('changeuploadspeed()',1000);
             fileuploadindex += 1;
            var parksize = 1024 * 1024 * 20;
            if (uplodfiledata.Body.size > 1024 * 1024 * 200) {
                parksize = 1024 * 1024 * 100;
            }

            var li = jQuery('#'+uplodfiledata.Onlyid);
            md5_file(uplodfiledata.Body, parksize).then(function (e) {
                var md5 = e;
                $.post('index.php?mod=pichome&op=library&do=upload&operation=chkmd5', {'md5': md5, 'Key': uplodfiledata.Key,'appid':'MOdBL2'}, function (mjson) {
                    if (!mjson.success) {
						var oldTime = 0;
						console.log([uplodfiledata.Bucket,uplodfiledata.Region,uplodfiledata.Key]);
                        cos.sliceUploadFile({
                            Bucket: uplodfiledata.Bucket, /* 必须 */
                            Region: uplodfiledata.Region,     /* 存储桶所在地域，必须字段 */
                            Key: uplodfiledata.Key,              /* 必须 */
                            Body: uplodfiledata.Body,                /* 必须 */
                            onTaskReady: function (taskId) {
                                li.data('id', taskId);
                                li.find('.operate-pause').html(__lang.pause);
                                li.find('.upload-cancel').removeClass('hide');
                                li.find('.operate-pause').off('click').on('click', function () {
                                    pauseTask(taskId);
                                    $(this).html('');
                                    $(this).siblings('.operate-retry').html(__lang.retry);
                                    $(this).siblings('.operate-retry').off('click').on('click', function () {
                                        restartTask(taskId);
                                        $(this).html('');
                                        $(this).siblings('.operate-pause').html(__lang.pause);
                                    });
                                });
                                li.find('.upload-cancel').off('click').on('click', function () {
                                    cancelTask(taskId);
                                    li.find('.upload-file-status').html('已取消');
                                    li.find('.operate-pause').addClass('hide');
                                    li.find('.upload-cancel').addClass('hide');
                                });
                            },
                            onProgress: function (info) {           /* 非必须 */
                                var percent = parseInt(info.percent * 10000) / 100;
                                var speed = parseInt(info.speed / 1024 / 1024 * 100) / 100;
                                li.find('.process').css('width', percent + '%');
                                li.find('.upload-file-status .speed').html(speed + 'Mb/s');
                                li.find('.upload-file-status .precent').html(percent + '%');
								
								var uploadsize = info.loaded - oldTime;
								oldTime = info.loaded;
                                var tmpuploadsize = uploadedsize + uploadsize;
								
								uploadedsize += uploadsize;
                                uploadspeed += uploadsize;
                                var progessval = (Math.round(tmpuploadsize/repareuploadsize* 10000) / 100)+"%";
								
                                changeheaderprogress(progessval);
                            }
                        }, function (err, data) {
                            if (err) {
                                if(jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr) == -1) {
                                    uploadfailddataarr.push(uplodfiledata.Onlyid);
                                    uploadfailddata[uplodfiledata.Onlyid] = uplodfiledata;
                                    fileuploadindex -=1;
                                }
                                li.find('.upload-file-status').html('<span class="danger" title="' + __lang.upload_failure + '">' + __lang.upload_failure + '</span>');
                                li.find('.upload-file-operate>.operate-try').removeClass('hide');
                                li.find('.operate-pause').addClass('hide');
                                li.find('.upload-cancel').addClass('hide');
                                _upload.uploaddone('error');
                                readFiledatas();

                            } else {
                                // 获取到文件的md5,添加到data中
                                data.md5 = md5;
                                data.size = uplodfiledata.Body.size;
                                data.remoteid = clouddata.remoteid;
                                data.appid = 'MOdBL2';
                                data.bz = clouddata.bz;
                                data.did = clouddata.did;
                                if (!data.Key && data.statusCode == 200) {
                                    data.Key = uplodfiledata.Key;
                                    data.Bucket = uplodfiledata.Bucket;
                                }
                                $.post('index.php?mod=pichome&op=library&do=upload&operation=cloudupload', data, function (json) {
                                    if (json.data) {
                                        li.addClass('success').find('.upload-file-status .speed').html('');
                                        li.find('.upload-file-operate').html(__lang.completed);
                                        li.find('.upload-progress-mask').css('width', '0%');
                                        li.find('.upload-cancel').addClass('hide');
                                        var process_bar = li.find('.process').css('width', '100%');
                                        if (process_bar) {
                                            li.find('.process').css('background-color', '#fff');
                                           // li.remove();
                                           if(jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr) != -1) {
                                                var failurekey = jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr);
                                               uploadfailddataarr.splice(failurekey,1);
                                               delete uploadfailddata[uplodfiledata.Onlyid];
                                               li.find('.upload-file-status').html('100%');
                                           }
                                            fileuploadindex -=1;
                                            uploadsuccess +=1;
                                            // uploadedsize += uplodfiledata.Body.size;
                                            // var progessval = (Math.round(uploadedsize/repareuploadsize* 10000) / 100.00)+"%";
                                            // changeheaderprogress(progessval);
                                            if(uploadsuccess == 10){
                                                uploadsuccess = 0;
                                                _upload.el.find('li.success').remove();
                                            }
                                            readFiledatas();
                                        }
                                        _upload.uploaddone();
                                        if (json.data.folderarr) {
                                            for (var i = 0; i < json.data.folderarr.length; i++) {
                                                _explorer.sourcedata.folder[json.data.folderarr[i].fid] = json.data.folderarr[i];
                                            }
                                        }
                                        if (json.data.icoarr) {
                                            for (var i = 0; i < json.data.icoarr.length; i++) {
                                                if (json.data.icoarr[i].pfid == _filemanage.cons['f-' + qcosuploadconfig.fid].fid) {
                                                    _explorer.sourcedata.icos[json.data.icoarr[i].rid] = json.data.icoarr[i];
                                                    _filemanage.cons['f-' + qcosuploadconfig.fid].CreateIcos(json.data.icoarr[i]);
                                                    try {
                                                        var inst = jQuery("#position").jstree(true);
                                                        var node = inst.get_node('#f_' + json.data.icoarr[i].pfid);
                                                        var pid = inst.get_parent(node);

                                                        if (pid != '#') {
                                                            inst.refresh_node('#' + pid);
                                                        } else {
                                                            jQuery("#position").jstree('refresh');
                                                        }

                                                    } catch (e) {
                                                    }
                                                    jQuery(document).trigger('showIcos_done');
                                                }

                                            }
                                        }
                                    } else {
                                        fileuploadindex -=1;
                                        if(jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr) == -1) {
                                            uploadfailddataarr.push(uplodfiledata.Onlyid);
                                            uploadfailddata[uplodfiledata.Onlyid] = uplodfiledata;
                                        }
                                        li.find('.upload-file-status').html('<span class="danger" title="' + __lang.upload_failure + '">' + __lang.upload_failure + '</span>');
                                        li.find('.upload-file-operate>.operate-try').removeClass('hide');
                                        li.find('.operate-pause').addClass('hide');
                                        li.find('.upload-cancel').addClass('hide');
                                        _upload.uploaddone('error');
                                        readFiledatas();
                                    }
                                }, 'json');
                            }
                        });
                    } else if (mjson.errormsg) {

                        if(jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr) == -1) {
                            uploadfailddataarr.push(uplodfiledata.Onlyid);
                            uploadfailddata[uplodfiledata.Onlyid] = uplodfiledata;
                            fileuploadindex -=1;
                        }
                        li.find('.upload-file-status').html('<span class="danger" title="' + __lang.upload_failure + '">' + __lang.upload_failure + '</span>');
                        li.find('.upload-file-operate>.operate-try').removeClass('hide');
                        li.find('.operate-pause').addClass('hide');
                        li.find('.upload-cancel').addClass('hide');
                        _upload.uploaddone('error');
                        readFiledatas();
                    } else {

                        li.addClass('success').find('.upload-file-status .speed').html('');
                        li.find('.upload-file-operate').html(__lang.completed);
                        li.find('.upload-progress-mask').css('width', '0%');
                        li.find('.upload-cancel').addClass('hide');
                        var process_bar = li.find('.process').css('width', '100%');
                        li.find('.upload-file-status .precent').html('100%');
                        if (process_bar) {
                            li.find('.process').css('background-color', '#fff');
                            if(jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr) != -1) {
                                var failurekey = jQuery.inArray(uplodfiledata.Onlyid,uploadfailddataarr);
                                uploadfailddataarr.splice(failurekey,1);
                                delete uploadfailddata[uplodfiledata.Onlyid];
                                li.find('.upload-file-status').html('100%');
                            }
                            fileuploadindex -=1;
                            uploadsuccess+=1;
                            // uploadedsize += uplodfiledata.Body.size;
							uploadedsize += uplodfiledata.Body.size;
                            uploadspeed += uplodfiledata.Body.size;
                            var progessval = (Math.round(uploadedsize/repareuploadsize* 10000) / 100)+"%";
                            changeheaderprogress(progessval);
                            if(uploadsuccess == 10){
                                uploadsuccess = 0;
                                _upload.el.find('li.success').remove();
                            }
                            readFiledatas();
                        }
                        
                        _upload.uploaddone();
                        if (mjson.data.folderarr) {
                            for (var i = 0; i < mjson.data.folderarr.length; i++) {
                                _explorer.sourcedata.folder[mjson.data.folderarr[i].fid] = mjson.data.folderarr[i];
                            }
                        }
                        if (mjson.data.icoarr) {
                            for (var i = 0; i < mjson.data.icoarr.length; i++) {
                                if (mjson.data.icoarr[i].pfid == _filemanage.cons['f-' + qcosuploadconfig.fid].fid) {
                                    _explorer.sourcedata.icos[mjson.data.icoarr[i].rid] = mjson.data.icoarr[i];
                                    _filemanage.cons['f-' + qcosuploadconfig.fid].CreateIcos(mjson.data.icoarr[i]);
                                    try {
                                        var inst = jQuery("#position").jstree(true);
                                        var node = inst.get_node('#f_' + mjson.data.icoarr[i].pfid);
                                        var pid = inst.get_parent(node);

                                        if (pid != '#') {
                                            inst.refresh_node('#' + pid);
                                        } else {
                                            jQuery("#position").jstree('refresh');
                                        }

                                    } catch (e) {
                                    }
                                    jQuery(document).trigger('showIcos_done');
                                }

                            }
                        }
                    }
                }, 'json')

            }).catch(function (e) {
                // 处理异常
                console.error(e);
            })
    //readFiledatas();

}


//暂停任务
var pauseTask = function (taskid) {
    cos.pauseTask(taskid);
}
//重新开始任务
var restartTask = function (taskid) {
    cos.restartTask(taskid);
}
//取消任务
var cancelTask = function (taskid) {
    cos.cancelTask(taskid);
}


_upload.uploadadd = function () {
    _upload.total++;

    $('#upload_header_status').html(__lang.upload_processing);
    $('#upload_header_number_container').show();
    $('#upload_header_total').html(_upload.total);
    // _upload.tips.find('.dialog-body-text').html(_upload.completed + '/' + _upload.total);
}

//计算文件md5值
function md5_file(file, chunkSize) {
    return new Promise(function (resolve, reject) {
        var blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
        var chunks = Math.ceil(file.size / chunkSize);
        var currentChunk = 0;
        var spark = new SparkMD5.ArrayBuffer();
        var fileReader = new FileReader();

        fileReader.onload = function (e) {
            spark.append(e.target.result);
            currentChunk++;

            if (currentChunk < chunks) {
                loadNext();
            } else {
                var _md = spark.end();

                resolve(_md);
            }
        };

        fileReader.onerror = function (e) {
            reject(e);
        };

        function loadNext() {
            var start = currentChunk * chunkSize;
            var end = start + chunkSize;

            if (end > file.size) {
                end = file.size;
            }

            fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
        }

        loadNext();
    });
}

_upload.getItemTpl = function (file) {
    var relativePath = file.name;
    var filearr = file.name.split('.');
    var ext = filearr.pop();
    var imgicon = '<img src="dzz/images/extimg/' + ext.toLowerCase() + '.png" onerror="replace_img(this)" style="width:24px;height:24px;position:absolute;left:0;"/>';
    var typerule = new RegExp(attachextensions, 'i');
    var uploadtips = (typerule.test(file.name)) ? __lang.checking : __lang.allow_file_type;
    if (maxfileSize && (maxfileSize < file.size)) {
        uploadtips = __lang.file_too_large;
    }
    var html =
        '<div class="process" style="position:absolute;z-index:-1;height:100%;background-color:#e8f5e9;-webkit-transition:width 0.6s ease;-o-transition:width 0.6s ease;transition:width 0.6s ease;width:0%;"></div> <div class="dialog-info"> <div class="upload-file-name">' +
        '<div class="dialog-file-icon" align="center">' + imgicon + '</div> <span class="name-text">' + file.name + '</span> ' +
        '</div> <div class="upload-file-size">' + (file.size ? formatSize(file.size) : '') + '</div> <div class="upload-file-path">' +
        '<a title="" class="" href="javascript:;">' + relativePath + '</a> </div> <div class="upload-file-status"> <span class="uploading"><em class="precent"></em><em class="speed">' + uploadtips + '</em>' +
        '</span> <span class="success"><em></em><i></i></span> </div> <div class="upload-file-operate"> ' +
        '<em class="operate-pause"></em> <em class="operate-continue"></em> <em class="operate-retry"></em> <em class="operate-remove"></em> ' +
        '<em class="operate-try hide" onclick="uploadtryagain(this);" style="cursor:pointer;">重试</em>'+
        '<a class="error-link upload-cancel hide" href="javascript:;">' + __lang.cancel + '</a> </div> </div>';
    return html;
}


function formatSize(bytes) {
    var i = -1;
    do {
        bytes = bytes / 1024;
        i++;
    } while (bytes > 99);

    return Math.max(bytes, 0).toFixed(1) + ['KB', 'MB', 'GB', 'TB', 'PB', 'EB'][i];
}

//文件上传成功
_upload.tips.find('.dialog-close').on('click', function () {
    $(this).parent('.dialog-tips').hide();
});
_upload.tips.find('.dialog-header-close').on('click', function () {
    _upload.close(this);
});
_upload.close = function (obj) {
    _upload.tips.hide();
    $('#upload_header_number_container').hide();
    $('#uploading_file_list').html('');
    _upload.total = 0;
    _upload.completed = 0;
};

_upload.tips.find('.dialog-header-narrow').off('click.icon').on('click.icon', function () {
    if ($(this).hasClass('dzz-min')) {

        $(this).removeClass('dzz-min').addClass('dzz-max');
        $(this).closest('.docunment-dialog').addClass('ismin');

        _upload.ismin = 1;//.css({'max-height': '146px', 'animation': '15s'});
        return false;
    } else {
        $(this).removeClass('dzz-max').addClass('dzz-min');
        $(this).closest('.docunment-dialog').removeClass('ismin');
        _upload.ismin = 0;//css({'max-height': '600px', 'animation': '15s'});
    }
});
function changeheaderprogress(val){
    $('#upload_header_progress').css('width', val);
}
function changeuploadspeed(){
	if(uploadspeed){
	    var speed = formatSize(uploadspeed / 8);
	    $('#upload_header_speed').show().html(speed + '/s');
	    uploadspeed = 0;
	}
}

_upload.uploaddone = function (flag) {
    if (flag == 'error') _upload.errored++;
    else{
        _upload.completed++;
        _upload.succeed++;
    }
    if (uploadfailddataarr.length > 0) {
        jQuery('.dialog-tips').removeClass('hide');
        _upload.tips.addClass('errortips');
        _upload.tips.find('.dialog-body-text').html(__lang.upload_failure + ' : ' + uploadfailddataarr.length).parent().show();
    } else {
        _upload.tips.removeClass('errortips');
        jQuery('.dialog-tips').addClass('hide');
        //_upload.tips.find('.dialog-body-text').html( __lang.upload_succeed+' : '+_upload.succeed).parent().hide();

    }
    if (_upload.completed >= _upload.total) {
        $('#upload_header_status').html(__lang.upload_finish);
        $('#upload_header_completed').html(_upload.completed);
        $('#upload_header_total').html(_upload.total);
        $('#upload_header_progress').css('width', 0);
        if (_upload.speedTimer) window.clearInterval(_upload.speedTimer);
        _upload.speedTimer = window.setTimeout(function () {
            $('#upload_header_speed').hide();
            //_upload.el.find('li.success').remove();
        }, 3000);
        _upload.el.find('li').remove();
		repareuploadsize = 0;
		uploadedsize = 0;
    } else {
        $('#upload_header_completed').html(_upload.completed);
    }
    var li = _upload.el.find('li.success');
    if (_upload.maxli && li.length >= _upload.maxli) {
        //li.remove();
    }
};