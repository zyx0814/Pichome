// 临时密钥服务例子
var bodyParser = require('body-parser');
var STS = require('qcloud-cos-sts');
var express = require('express');
var crypto = require('crypto');
var pathLib = require('path');
var fs = require('fs');

// 配置参数
var config = {
    secretId: process.env.SecretId,
    secretKey: process.env.SecretKey,
    proxy: process.env.Proxy,
    durationSeconds: 1800,
    bucket: process.env.Bucket,
    region: process.env.Region,
    // 允许操作（上传）的对象前缀，可以根据自己网站的用户登录态判断允许上传的目录，例子： user1/* 或者 * 或者a.jpg
    // 请注意当使用 * 时，可能存在安全风险，详情请参阅：https://cloud.tencent.com/document/product/436/40265
    allowPrefix: '_ALLOW_DIR_/*',
    // 密钥的权限列表
    allowActions: [
        // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
        // 简单上传
        'name/cos:PutObject',
        'name/cos:PostObject',
        // 分片上传
        'name/cos:InitiateMultipartUpload',
        'name/cos:ListMultipartUploads',
        'name/cos:ListParts',
        'name/cos:UploadPart',
        'name/cos:CompleteMultipartUpload'
    ],
};

// 创建临时密钥服务和用于调试的静态服务
var app = express();

var replaceBucketRegion = (filePath) => {
    return (req, res, next) => {
        var content = fs.readFileSync(filePath).toString()
            .replace(/(var config = {\r?\n *Bucket: ')test-1250000000(',\r?\n *Region: ')ap-guangzhou(',?\r?\n};?)/,
            '$1' + config.bucket + '$2' + config.region +'$3');
        res.header('Content-Type', 'application/javascript');
        res.send(content);
    };
};
app.use('/demo/demo.js', replaceBucketRegion(pathLib.resolve(__dirname, '../demo/demo.js')));
app.use('/test/test.js', replaceBucketRegion(pathLib.resolve(__dirname, '../test/test.js')));
app.use('/dist/', express.static(pathLib.resolve(__dirname, '../dist')));
app.use('/demo/', express.static(pathLib.resolve(__dirname, '../demo')));
app.use('/test/', express.static(pathLib.resolve(__dirname, '../test')));
app.all('/', (req, res, next) => res.redirect('/demo/'));
app.use(bodyParser.json());

// 格式一：临时密钥接口
app.all('/sts', function (req, res, next) {

    // TODO 这里根据自己业务需要做好放行判断
    if (config.allowPrefix === '_ALLOW_DIR_/*') {
        res.send({error: '请修改 allowPrefix 配置项，指定允许上传的路径前缀'});
        return;
    }

    // 获取临时密钥
    var LongBucketName = config.bucket;
    var ShortBucketName = LongBucketName.substr(0, LongBucketName.lastIndexOf('-'));
    var AppId = LongBucketName.substr(LongBucketName.lastIndexOf('-') + 1);
    var policy = {
        'version': '2.0',
        'statement': [{
            'action': config.allowActions,
            'effect': 'allow',
            'resource': [
                'qcs::cos:' + config.region + ':uid/' + AppId + ':prefix//' + AppId + '/' + ShortBucketName + '/' + config.allowPrefix,
            ],
        }],
    };
    var startTime = Math.round(Date.now() / 1000);
    STS.getCredential({
        secretId: config.secretId,
        secretKey: config.secretKey,
        proxy: config.proxy,
        region: config.region,
        durationSeconds: config.durationSeconds,
        policy: policy,
    }, function (err, tempKeys) {
        if (tempKeys) tempKeys.startTime = startTime;
        res.send(err || tempKeys);
    });
});

// // 格式二：临时密钥接口，支持细粒度权限控制
// // 判断是否允许获取密钥
// var allowScope = function (scope) {
//     var allow = (scope || []).every(function (item) {
//         return config.allowActions.includes(item.action) &&
//             item.bucket === config.bucket &&
//             item.region === config.region &&
//             (item.prefix || '').startsWith(config.allowPrefix);
//     });
//     return allow;
// };
// app.all('/sts-scope', function (req, res, next) {
//     var scope = req.body;
//
//     // TODO 这里根据自己业务需要做好放行判断
//     if (config.allowPrefix === '_ALLOW_DIR_/*') {
//         res.send({error: '请修改 allowPrefix 配置项，指定允许上传的路径前缀'});
//         return;
//     }
//     // TODO 这里可以判断 scope 细粒度控制权限
//     if (!scope || !scope.length || !allowScope(scope)) return res.send({error: 'deny'});
//
//     // 获取临时密钥
//     var policy = STS.getPolicy(scope);
//     var startTime = Math.round(Date.now() / 1000);
//     STS.getCredential({
//         secretId: config.secretId,
//         secretKey: config.secretKey,
//         proxy: config.proxy,
//         durationSeconds: config.durationSeconds,
//         policy: policy,
//     }, function (err, tempKeys) {
//         if (tempKeys) tempKeys.startTime = startTime;
//         res.send(err || tempKeys);
//     });
// });
//
// // 用于 PostObject 签名保护
// app.all('/post-policy', function (req, res, next) {
//     var query = req.query;
//     var now = Math.round(Date.now() / 1000);
//     var exp = now + 900;
//     var qKeyTime = now + ';' + exp;
//     var qSignAlgorithm = 'sha1';
//     var policy = JSON.stringify({
//         'expiration': new Date(exp * 1000).toISOString(),
//         'conditions': [
//             // {'acl': query.ACL},
//             // ['starts-with', '$Content-Type', 'image/'],
//             // ['starts-with', '$success_action_redirect', redirectUrl],
//             // ['eq', '$x-cos-server-side-encryption', 'AES256'],
//             {'q-sign-algorithm': qSignAlgorithm},
//             {'q-ak': config.secretId},
//             {'q-sign-time': qKeyTime},
//             {'bucket': config.bucket},
//             {'key': query.key},
//         ]
//     });
//
//     // 签名算法说明文档：https://www.qcloud.com/document/product/436/7778
//     // 步骤一：生成 SignKey
//     var signKey = crypto.createHmac('sha1', config.secretKey).update(qKeyTime).digest('hex');
//
//     // 步骤二：生成 StringToSign
//     var stringToSign = crypto.createHash('sha1').update(policy).digest('hex');
//
//     // 步骤三：生成 Signature
//     var qSignature = crypto.createHmac('sha1', signKey).update(stringToSign).digest('hex');
//
//     console.log(policy);
//     res.send({
//         policyObj: JSON.parse(policy),
//         policy: Buffer.from(policy).toString('base64'),
//         qSignAlgorithm: qSignAlgorithm,
//         qAk: config.secretId,
//         qKeyTime: qKeyTime,
//         qSignature: qSignature,
//         // securityToken: securityToken, // 如果使用临时密钥，要返回在这个资源 sessionToken 的值
//     });
// });
//

app.all('*', function (req, res, next) {
    res.send({code: -1, message: '404 Not Found'});
});

// 启动签名服务
app.listen(3000);
console.log('app is listening at http://127.0.0.1:3000');
