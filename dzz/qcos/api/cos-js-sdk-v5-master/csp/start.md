# COS JavaScript SDK CSP 快速入门

### SDK 获取

对象存储服务的 XML JS SDK 资源 github 地址：[tencentyun/cos-js-sdk-v5](https://github.com/tencentyun/cos-js-sdk-v5)。

演示示例 Demo 代码地址：[XML JS SDK CSP Demo](https://github.com/tencentyun/cos-js-sdk-v5/tree/master/csp/csp.html)。

### 开发准备

1. 首先，JS SDk 需要浏览器支持基本的 HTML5 特性，以便支持 ajax 上传文件和计算文件 md5 值。
2. 到COS 对象存储控制台创建存储桶，得到 Bucket（存储桶名称） 和 Region（地域名称）
3. 到控制台密钥管理获取您的项目 SecretId 和 SecretKey。
4. 配置 CORS 规则，配置例子如下图：

![cors](../demo/cors.png)

> 关于文章中出现的 SecretId、SecretKey、Bucket 等名称的含义和获取方式请参考：[COS 术语信息](https://cloud.tencent.com/document/product/436/7751)

## 快速入门
### 计算签名

由于签名计算放在前端会暴露 SecretId 和 SecretKey，我们把签名计算过程放在后端实现，前段通过 ajax 向后端获取签名结果，正式部署时请再后端加一层自己网站本身的权限检验。其他语言，请参照对应的 [XML SDK](https://cloud.tencent.com/document/product/436/6474) 文档。


### 上传例子

1. 创建 test.html，填入下面的代码，修改里面的 Bucket 和 Region。
2. 部署好后端的签名服务，并修改 getAuthorization 里的签名服务地址。
3. 把 test.html 放在 Web 服务器下，然后在浏览器访问页面，测试文件上传。

```html
<input id="file-selector" type="file">
<script src="dist/cos-js-sdk-v5.min.js"></script>
<script>
var Bucket = 'test-1250000000';
var Region = 'ap-guangzhou';

// 初始化实例
var cos = new COS({
    CompatibilityMode: true,
    ServiceDomain: 'http://cos.default.xxx.com', // 这里替换成 getService 域名
    Domain: 'http://{Bucket}.cos.{Region}.xxx.com', 这里替换成 API 域名格式模板
    getAuthorization: function (options, callback) {
        var url = './auth-json.php?method=' + options.Method + '&path=' + encodeURIComponent('/' + options.Key);
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.onload = function (e) {
            callback(e.target.responseText);
        };
        xhr.send();
    },
});

// 监听选文件
document.getElementById('file-selector').onchange = function () {
    
    var file = this.files[0];
    if (!file) return;

    // 分片上传文件
    cos.sliceUploadFile({
        Bucket: Bucket,
        Region: Region,
        Key: file.name,
        Body: file,
    }, function (err, data) {
        console.log(err, data);
    });

};
</script>
```

## webpack 引入方式

支持 webpack 打包的场景，可以用 npm 引入作为模块
```shell
npm i cos-js-sdk-v5 --save
```
