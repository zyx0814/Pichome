var config = {
    Bucket: 'test-1250000000',
    Region: 'ap-guangzhou'
};

var util = {
    createFile: function (options) {
        var buffer = new ArrayBuffer(options.size || 0);
        var arr = new Uint8Array(buffer);
        [].forEach.call(arr, function (char, i) {
            arr[i] = 0;
        });
        var opt = {};
        options.type && (opt.type = options.type);
        var blob = new Blob([buffer], options);
        return blob;
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

var getAuthorization = function (options, callback) {

    // 格式一、（推荐）后端通过获取临时密钥给到前端，前端计算签名
    // 服务端 JS 和 PHP 例子：https://github.com/tencentyun/cos-js-sdk-v5/blob/master/server/
    // 服务端其他语言参考 COS STS SDK ：https://github.com/tencentyun/qcloud-cos-sts-sdk
    // var url = '../server/sts.php'; // 如果起的是 php server 用这个
    var url = '/sts'; // 如果是 npm run sts.js 起的 nodejs server，使用这个
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.onload = function (e) {
        try {
            var data = JSON.parse(e.target.responseText);
            var credentials = data.credentials;
        } catch (e) {
        }
        if (!data || !credentials) return console.error('credentials invalid');
        callback({
            TmpSecretId: credentials.tmpSecretId,
            TmpSecretKey: credentials.tmpSecretKey,
            XCosSecurityToken: credentials.sessionToken,
            StartTime: data.startTime, // 时间戳，单位秒，如：1580000000，建议返回服务器时间作为签名的开始时间，避免用户浏览器本地时间偏差过大导致签名错误
            ExpiredTime: data.expiredTime, // 时间戳，单位秒，如：1580000900
        });
    };
    xhr.send();


    // // 格式二、（推荐）【细粒度控制权限】后端通过获取临时密钥给到前端，前端只有相同请求才重复使用临时密钥，后端可以通过 Scope 细粒度控制权限
    // // 服务端例子：https://github.com/tencentyun/qcloud-cos-sts-sdk/edit/master/scope.md
    // // var url = '../server/sts.php'; // 如果起的是 php server 用这个
    // var url = '/sts-scope'; // 如果是 npm run sts.js 起的 nodejs server，使用这个
    // var xhr = new XMLHttpRequest();
    // xhr.open('POST', url, true);
    // xhr.setRequestHeader('Content-Type', 'application/json');
    // xhr.onload = function (e) {
    //     try {
    //         var data = JSON.parse(e.target.responseText);
    //         var credentials = data.credentials;
    //     } catch (e) {
    //     }
    //     if (!data || !credentials) return console.error('credentials invalid');
    //     callback({
    //         TmpSecretId: credentials.tmpSecretId,
    //         TmpSecretKey: credentials.tmpSecretKey,
    //         XCosSecurityToken: credentials.sessionToken,
    //         StartTime: data.startTime, // 时间戳，单位秒，如：1580000000，建议返回服务器时间作为签名的开始时间，避免用户浏览器本地时间偏差过大导致签名错误
    //         ExpiredTime: data.expiredTime, // 时间戳，单位秒，如：1580000000
    //         ScopeLimit: true, // 细粒度控制权限需要设为 true，会限制密钥只在相同请求时重复使用
    //     });
    // };
    // xhr.send(JSON.stringify(options.Scope));


    // // 格式三、（不推荐，分片上传权限不好控制）前端每次请求前都需要通过 getAuthorization 获取签名，后端使用固定密钥或临时密钥计算签名返回给前端
    // // 服务端获取签名，请参考对应语言的 COS SDK：https://cloud.tencent.com/document/product/436/6474
    // // 注意：这种有安全风险，后端需要通过 method、pathname 严格控制好权限，比如不允许 put / 等
    // var method = (options.Method || 'get').toLowerCase();
    // var query = options.Query || {};
    // var headers = options.Headers || {};
    // var pathname = options.Pathname || '/';
    // // var url = 'http://127.0.0.1:3000/auth';
    // var url = '../server/auth.php';
    // var xhr = new XMLHttpRequest();
    // var data = {
    //     method: method,
    //     pathname: pathname,
    //     query: query,
    //     headers: headers,
    // };
    // xhr.open('POST', url, true);
    // xhr.setRequestHeader('content-type', 'application/json');
    // xhr.onload = function (e) {
    //     try {
    //         var data = JSON.parse(e.target.responseText);
    //     } catch (e) {
    //     }
    //     if (!data || !data.authorization) return console.error('authorization invalid');
    //     callback({
    //         Authorization: data.authorization,
    //         // XCosSecurityToken: data.sessionToken, // 如果使用临时密钥，需要把 sessionToken 传给 XCosSecurityToken
    //     });
    // };
    // xhr.send(JSON.stringify(data));


    // // 格式四、（不推荐，适用于前端调试，避免泄露密钥）前端使用固定密钥计算签名
    // var authorization = COS.getAuthorization({
    //     SecretId: 'AKIDxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 可传固定密钥或者临时密钥
    //     SecretKey: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx', // 可传固定密钥或者临时密钥
    //     Method: options.Method,
    //     Pathname: options.Pathname,
    //     Query: options.Query,
    //     Headers: options.Headers,
    //     Expires: 900,
    // });
    // callback({
    //     Authorization: authorization,
    //     // XCosSecurityToken: credentials.sessionToken, // 如果使用临时密钥，需要传 XCosSecurityToken
    // });

};

var cos = new COS({
    getAuthorization: getAuthorization,
});

var TaskId;

var pre = document.querySelector('.result');
var showLogText = function (text, color) {
    if (typeof text === 'object') {
        try {
            text = JSON.stringify(text);
        } catch (e) {
        }
    }
    var div = document.createElement('div');
    div.innerText = text;
    color && (div.style.color = color);
    pre.appendChild(div);
    pre.style.display = 'block';
    pre.scrollTop = pre.scrollHeight;
};

var logger = {
    log: function (text) {
        console.log.apply(console, arguments);
        var args = [].map.call(arguments, function (v) {
            return typeof v === 'object' ? JSON.stringify(v) : v;
        });

        var logStr = args.join(' ');

        if(logStr.length > 1000000) {
            logStr = logStr.slice(0, 1000000) + '...content is too long, the first 1000000 characters are intercepted';
        }

        showLogText(logStr);
    },
    error: function (text) {
        console.error(text);
        showLogText(text, 'red');
    },
};

function getObjectUrl() {
    var url = cos.getObjectUrl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip',
        Expires: 60,
        Sign: true,
    }, function (err, data) {
        logger.log(err || data && data.Url);
    });
    logger.log(url);
}

function getAuth() {
    var key = '1.png';
    // 这里不推荐自己拼接，推荐使用 getObjectUrl 获取 url
    getAuthorization({
        Method: 'get',
        Key: key
    }, function (AuthData) {
        if (typeof AuthData === 'string') {
            AuthData = {Authorization: AuthData.Authorization};
        }
        var url = 'http://' + config.Bucket + '.cos.' + config.Region + '.myqcloud.com' + '/' +
            camSafeUrlEncode(key).replace(/%2F/g, '/') +
            '?' + AuthData +
            (AuthData.XCosSecurityToken ? '&' + AuthData.XCosSecurityToken : '');
        logger.log(url);
    });
}

// getService、putBucket 接口会跨域，不支持浏览器使用，只在场景下可调用，比如改了 ServiceDomain 到代理地址
function getService() {
    cos.getService(function (err, data) {
        logger.log(err || data);
    });
}

// getService、putBucket 接口会跨域，不支持浏览器使用，只在场景下可调用，比如改了 ServiceDomain 到代理地址
function putBucket() {
    cos.putBucket({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        // Prefix: 'dir/'
        // Delimiter: '/'
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucket() {
    cos.getBucket({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        // Prefix: 'dir/'
        // Delimiter: '/'
    }, function (err, data) {
        logger.log(err || data);
    });
}

function headBucket() {
    cos.headBucket({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucket() {
    cos.deleteBucket({
        Bucket: 'testnew-' + config.Bucket.substr(config.Bucket.lastIndexOf('-') + 1),
        Region: 'ap-guangzhou'
    }, function (err, data) {
        console.log(err || data);
    });
}

function putBucketAcl() {
    cos.putBucketAcl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        // GrantFullControl: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantWrite: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantRead: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantReadAcp: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantWriteAcp: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // ACL: 'public-read-write',
        // ACL: 'public-read',
        ACL: 'private',
        // AccessControlPolicy: {
        // "Owner": { // AccessControlPolicy 里必须有 owner
        //     "ID": 'qcs::cam::uin/10001:uin/10001' // 10001 是 Bucket 所属用户的 QQ 号
        // },
        // "Grants": [{
        //     "Grantee": {
        //         "ID": "qcs::cam::uin/1001:uin/1001", // 10002 是 QQ 号
        //         "DisplayName": "qcs::cam::uin/1001:uin/1001" // 10002 是 QQ 号
        //     },
        //     "Permission": "READ"
        // }, {
        //     "Grantee": {
        //         "ID": "qcs::cam::uin/10002:uin/10002", // 10002 是 QQ 号
        //     },
        //     "Permission": "WRITE"
        // }, {
        //     "Grantee": {
        //         "ID": "qcs::cam::uin/10002:uin/10002", // 10002 是 QQ 号
        //     },
        //     "Permission": "READ_ACP"
        // }, {
        //     "Grantee": {
        //         "ID": "qcs::cam::uin/10002:uin/10002", // 10002 是 QQ 号
        //     },
        //     "Permission": "WRITE_ACP"
        // }]
        // }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketAcl() {
    cos.getBucketAcl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketCors() {
    cos.putBucketCors({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        CORSRules: [{
            "AllowedOrigin": ["*"],
            "AllowedMethod": ["GET", "POST", "PUT", "DELETE", "HEAD"],
            "AllowedHeader": ["*"],
            "ExposeHeader": ["ETag", "Date", "Content-Length", "x-cos-acl", "x-cos-version-id", "x-cos-request-id", "x-cos-delete-marker", "x-cos-server-side-encryption"],
            "MaxAgeSeconds": "5"
        }]
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketCors() {
    cos.getBucketCors({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucketCors() {
    cos.deleteBucketCors({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketTagging() {
    cos.putBucketTagging({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Tagging: {
            "Tags": [
                {"Key": "k1", "Value": "v1"},
                {"Key": "k2", "Value": "v2"}
            ]
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketTagging() {
    cos.getBucketTagging({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucketTagging() {
    cos.deleteBucketTagging({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketPolicy() {
    var AppId = config.Bucket.substr(config.Bucket.lastIndexOf('-') + 1);
    cos.putBucketPolicy({
        Policy: {
            "version": "2.0",
            "statement": [{
                "effect": "allow",
                "principal": {"qcs": ["qcs::cam::uin/10001:uin/10001"]}, // 这里的 10001 是 QQ 号
                "action": [
                    // 这里可以从临时密钥的权限上控制前端允许的操作
                    // 'name/cos:*', // 这样写可以包含下面所有权限

                    // // 列出所有允许的操作
                    // // ACL 读写
                    // 'name/cos:GetBucketACL',
                    // 'name/cos:PutBucketACL',
                    // 'name/cos:GetObjectACL',
                    // 'name/cos:PutObjectACL',
                    // // 简单 Bucket 操作
                    // 'name/cos:PutBucket',
                    // 'name/cos:HeadBucket',
                    // 'name/cos:GetBucket',
                    // 'name/cos:DeleteBucket',
                    // 'name/cos:GetBucketLocation',
                    // // Versioning
                    // 'name/cos:PutBucketVersioning',
                    // 'name/cos:GetBucketVersioning',
                    // // CORS
                    // 'name/cos:PutBucketCORS',
                    // 'name/cos:GetBucketCORS',
                    // 'name/cos:DeleteBucketCORS',
                    // // Lifecycle
                    // 'name/cos:PutBucketLifecycle',
                    // 'name/cos:GetBucketLifecycle',
                    // 'name/cos:DeleteBucketLifecycle',
                    // // Replication
                    // 'name/cos:PutBucketReplication',
                    // 'name/cos:GetBucketReplication',
                    // 'name/cos:DeleteBucketReplication',
                    // // 删除文件
                    // 'name/cos:DeleteMultipleObject',
                    // 'name/cos:DeleteObject',
                    // 简单文件操作
                    'name/cos:PutObject',
                    'name/cos:AppendObject',
                    'name/cos:GetObject',
                    'name/cos:HeadObject',
                    'name/cos:OptionsObject',
                    'name/cos:PutObjectCopy',
                    'name/cos:PostObjectRestore',
                    // 分片上传操作
                    'name/cos:InitiateMultipartUpload',
                    'name/cos:ListMultipartUploads',
                    'name/cos:ListParts',
                    'name/cos:UploadPart',
                    'name/cos:CompleteMultipartUpload',
                    'name/cos:AbortMultipartUpload',
                ],
                // "resource": ["qcs::cos:ap-guangzhou:uid/1250000000:test-1250000000/*"] // 1250000000 是 appid
                "resource": ["qcs::cos:" + config.Region + ":uid/" + AppId + ":" + config.Bucket + "/*"] // 1250000000 是 appid
            }]
        },
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketPolicy() {
    cos.getBucketPolicy({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucketPolicy() {
    cos.deleteBucketPolicy({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketLocation() {
    cos.getBucketLocation({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketLifecycle() {
    cos.putBucketLifecycle({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        LifecycleConfiguration: {
            Rules: [{
                "ID": "1",
                "Status": "Enabled",
                "Filter": {},
                "Transition": {
                    "Days": "30",
                    "StorageClass": "STANDARD_IA"
                }
            }, {
                "ID": "2",
                "Status": "Enabled",
                "Filter": {
                    "Prefix": "dir/"
                },
                "Transition": {
                    "Days": "90",
                    "StorageClass": "ARCHIVE"
                }
            }, {
                "ID": "3",
                "Status": "Enabled",
                "Filter": {},
                "Expiration": {
                    "Days": "180"
                }
            }, {
                "ID": "4",
                "Status": "Enabled",
                "Filter": {},
                "AbortIncompleteMultipartUpload": {
                    "DaysAfterInitiation": "30"
                }
            }],
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketLifecycle() {
    cos.getBucketLifecycle({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucketLifecycle() {
    cos.deleteBucketLifecycle({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketVersioning() {
    cos.putBucketVersioning({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        VersioningConfiguration: {
            Status: "Enabled"
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketVersioning() {
    cos.getBucketVersioning({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function listObjectVersions() {
    cos.listObjectVersions({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        // Prefix: "",
        // Delimiter: '/'
    }, function (err, data) {
        logger.log(err || JSON.stringify(data, null, '    '));
    });
}

function putBucketReplication() {
    var AppId = config.Bucket.substr(config.Bucket.lastIndexOf('-') + 1);
    cos.putBucketReplication({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        ReplicationConfiguration: {
            Role: "qcs::cam::uin/10001:uin/10001",
            Rules: [{
                ID: "1",
                Status: "Enabled",
                Prefix: "sync/",
                Destination: {
                    Bucket: "qcs:id/0:cos:ap-chengdu:appid/" + AppId + ":backup",
                    // StorageClass: "Standard",
                }
            }]
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketReplication() {
    cos.getBucketReplication({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteBucketReplication() {
    cos.deleteBucketReplication({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putBucketWebsite() {
    cos.putBucketWebsite({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        WebsiteConfiguration: {
            IndexDocument: {
                Suffix: "index.html" // 必选
            },
            RedirectAllRequestsTo: {
                Protocol: "https"
            },
            // ErrorDocument: {
            //     Key: "error.html"
            // },
            // RoutingRules: [{
            //     Condition: {
            //         HttpErrorCodeReturnedEquals: "404"
            //     },
            //     Redirect: {
            //         Protocol: "https",
            //         ReplaceKeyWith: "404.html"
            //     }
            // }, {
            //     Condition: {
            //         KeyPrefixEquals: "docs/"
            //     },
            //     Redirect: {
            //         Protocol: "https",
            //         ReplaceKeyPrefixWith: "documents/"
            //     }
            // }, {
            //     Condition: {
            //         KeyPrefixEquals: "img/"
            //     },
            //     Redirect: {
            //         Protocol: "https",
            //         ReplaceKeyWith: "picture.jpg"
            //     }
            // }]
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getBucketWebsite() {
    cos.getBucketWebsite({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    },function(err, data){
        logger.log(err || data);
    });
}

function deleteBucketWebsite() {
    cos.deleteBucketWebsite({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region
    },function(err, data){
        logger.log(err || data);
    });
}

function putObject() {
    // 创建测试文件
    var filename = '1mb.zip';
    var blob = util.createFile({size: 1024 * 1024 * 1});
    // 调用方法
    cos.putObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: filename, /* 必须 */
        Body: blob,
        onTaskReady: function (tid) {
            TaskId = tid;
            console.log('onTaskReady', tid);
        },
        onTaskStart: function (info) {
            console.log('onTaskStart', info);
        },
        onProgress: function (progressData) {
            logger.log(JSON.stringify(progressData));
        },
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putObjectCopy() {
    cos.putObjectCopy({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.copy.zip',
        CopySource: config.Bucket + '.cos.' + config.Region + '.myqcloud.com/' + camSafeUrlEncode('1mb.zip').replace(/%2F/g, '/'), // Bucket 格式：test-1250000000
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getObject() {
    cos.getObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip',
    }, function (err, data) {
        logger.log(err || data);
    });
}

function headObject() {
    cos.headObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip'
    }, function (err, data) {
        logger.log(err || data);
    });
}

function putObjectAcl() {
    cos.putObjectAcl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip',
        // GrantFullControl: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantWrite: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // GrantRead: 'id="qcs::cam::uin/1001:uin/1001",id="qcs::cam::uin/1002:uin/1002"',
        // ACL: 'public-read-write',
        // ACL: 'public-read',
        // ACL: 'private',
        ACL: 'default', // 继承上一级目录权限
        // AccessControlPolicy: {
        //     "Owner": { // AccessControlPolicy 里必须有 owner
        //         "ID": 'qcs::cam::uin/10001:uin/10001' // 10001 是 Bucket 所属用户的 QQ 号
        //     },
        //     "Grants": [{
        //         "Grantee": {
        //             "ID": "qcs::cam::uin/10002:uin/10002", // 10002 是 QQ 号
        //         },
        //         "Permission": "READ"
        //     }]
        // }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function getObjectAcl() {
    cos.getObjectAcl({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip'
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteObject() {
    cos.deleteObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1mb.zip'
    }, function (err, data) {
        logger.log(err || data);
    });
}

function deleteMultipleObject() {
    cos.deleteMultipleObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Objects: [
            {Key: '中文/中文.txt'},
            {Key: '中文/中文.zip',VersionId: 'MTg0NDY3NDI1MzM4NzM0ODA2MTI'},
        ]
    }, function (err, data) {
        logger.log(err || data);
    });
}

function restoreObject() {
    cos.restoreObject({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '1.txt',
        RestoreRequest: {
            Days: 1,
            CASJobParameters: {
                Tier: 'Expedited'
            }
        }
    }, function (err, data) {
        logger.log(err || data);
    });
}

function abortUploadTask() {
    cos.abortUploadTask({
        Bucket: config.Bucket, /* 必须 */ // Bucket 格式：test-1250000000
        Region: config.Region, /* 必须 */
        // 格式1，删除单个上传任务
        // Level: 'task',
        // Key: '10mb.zip',
        // UploadId: '14985543913e4e2642e31db217b9a1a3d9b3cd6cf62abfda23372c8d36ffa38585492681e3',
        // 格式2，删除单个文件所有未完成上传任务
        Level: 'file',
        Key: '10mb.zip',
        // 格式3，删除 Bucket 下所有未完成上传任务
        // Level: 'bucket',
    }, function (err, data) {
        logger.log(err || data);
    });
}

function sliceUploadFile() {
    var blob = util.createFile({size: 1024 * 1024 * 3});
    cos.sliceUploadFile({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: '3mb.zip', /* 必须 */
        Body: blob,
        onTaskReady: function (tid) {
            TaskId = tid;
        },
        onHashProgress: function (progressData) {
            logger.log('onHashProgress', JSON.stringify(progressData));
        },
        onProgress: function (progressData) {
            logger.log('onProgress', JSON.stringify(progressData));
        },
    }, function (err, data) {
        logger.log(err || data);
    });
}

function selectFileToUpload() {
    var input = document.getElementById('file_selector') || document.createElement('input');
    input.type = 'file';
    input.onchange = function (e) {
        document.body.removeChild(input);
        var file = this.files[0];
        if (!file) return;
        if (file.size > 1024 * 1024) {
            cos.sliceUploadFile({
                Bucket: config.Bucket, // Bucket 格式：test-1250000000
                Region: config.Region,
                Key: file.name,
                Body: file,
                onTaskReady: function (tid) {
                    TaskId = tid;
                },
                onHashProgress: function (progressData) {
                    logger.log('onHashProgress', JSON.stringify(progressData));
                },
                onProgress: function (progressData) {
                    logger.log('onProgress', JSON.stringify(progressData));
                },
            }, function (err, data) {
                logger.log(err || data);
            });
        } else {
            cos.putObject({
                Bucket: config.Bucket, // Bucket 格式：test-1250000000
                Region: config.Region,
                Key: file.name,
                Body: file,
                onTaskReady: function (tid) {
                    TaskId = tid;
                },
                onHashProgress: function (progressData) {
                    logger.log('onHashProgress', JSON.stringify(progressData));
                },
                onProgress: function (progressData) {
                    logger.log(JSON.stringify(progressData));
                },
            }, function (err, data) {
                logger.log(err || data);
            });
        }
    };
    input.style = 'width:0;height:0;border:0;margin:0;padding:0;';
    input.id = 'file_selector';
    document.body.appendChild(input);
    input.click();
}

function cancelTask() {
    cos.cancelTask(TaskId);
    logger.log('canceled');
}

function pauseTask() {
    cos.pauseTask(TaskId);
    logger.log('paused');
}

function restartTask() {
    cos.restartTask(TaskId);
    logger.log('restart');
}

function uploadFiles() {
    var filename = 'mb.zip';
    var blob = util.createFile({size: 1024 * 1024 * 10});
    cos.uploadFiles({
        files: [{
            Bucket: config.Bucket, // Bucket 格式：test-1250000000
            Region: config.Region,
            Key: '1' + filename,
            Body: blob,
        }, {
            Bucket: config.Bucket, // Bucket 格式：test-1250000000
            Region: config.Region,
            Key: '2' + filename,
            Body: blob,
        }, {
            Bucket: config.Bucket, // Bucket 格式：test-1250000000
            Region: config.Region,
            Key: '3' + filename,
            Body: blob,
        }],
        SliceSize: 1024 * 1024,
        onProgress: function (info) {
            var percent = parseInt(info.percent * 10000) / 100;
            var speed = parseInt(info.speed / 1024 / 1024 * 100) / 100;
            logger.log('进度：' + percent + '%; 速度：' + speed + 'Mb/s;');
        },
        onFileFinish: function (err, data, options) {
            logger.log(options.Key + ' 上传' + (err ? '失败' : '完成'));
        },
    }, function (err, data) {
        logger.log(err || data);
    });
}

function sliceCopyFile() {
    // 创建测试文件
    var sourceName = '3mb.zip';
    var Key = '3mb.copy.zip';

    var sourcePath = config.Bucket + '.cos.' + config.Region + '.myqcloud.com/'+ camSafeUrlEncode(sourceName).replace(/%2F/g, '/');

    cos.sliceCopyFile({
        Bucket: config.Bucket, // Bucket 格式：test-1250000000
        Region: config.Region,
        Key: Key,
        CopySource: sourcePath,
        SliceSize: 2 * 1024 * 1024, // 大于2M的文件用分片复制，小于则用单片复制
        onProgress:function (info) {
            var percent = parseInt(info.percent * 10000) / 100;
            var speed = parseInt(info.speed / 1024 / 1024 * 100) / 100;
            logger.log('进度：' + percent + '%; 速度：' + speed + 'Mb/s;');
        }
    },function (err,data) {
        if(err){
            logger.log(err);
        }else{
            logger.log(data);
        }
    });

}

(function () {
    var list = [
        //'getService', // 不支持，正常场景会跨域
        //'putBucket', // 不支持，正常场景会跨域
        'getObjectUrl',
        'getAuth',
        'getBucket',
        'headBucket',
        'putBucketAcl',
        'getBucketAcl',
        'putBucketCors',
        'getBucketCors',
        // 'deleteBucketCors', // 不建议调用，删除 CORS，浏览器不能正常调用
        'putBucketTagging',
        'getBucketTagging',
        'deleteBucketTagging',
        'putBucketPolicy',
        'getBucketPolicy',
        'deleteBucketPolicy',
        'getBucketLocation',
        'getBucketLifecycle',
        'putBucketLifecycle',
        'deleteBucketLifecycle',
        'putBucketVersioning',
        'getBucketVersioning',
        'listObjectVersions',
        'putBucketReplication',
        'getBucketReplication',
        'deleteBucketReplication',
        'putBucketWebsite',
        'getBucketWebsite',
        'deleteBucketWebsite',
        'deleteBucket',
        'putObject',
        'putObjectCopy',
        'getObject',
        'headObject',
        'putObjectAcl',
        'getObjectAcl',
        'deleteObject',
        'deleteMultipleObject',
        'restoreObject',
        'abortUploadTask',
        'sliceUploadFile',
        'selectFileToUpload',
        'cancelTask',
        'pauseTask',
        'restartTask',
        'uploadFiles',
        'sliceCopyFile',
    ];
    var container = document.querySelector('.main');
    var html = [];
    list.forEach(function (name) {
        html.push('<a href="javascript:void(0)">' + name + '</a>');
    });
    container.innerHTML = html.join('');
    container.onclick = function (e) {
        if (e.target.tagName === 'A') {
            var name = e.target.innerText.trim();
            window[name]();
        }
    };
})();
