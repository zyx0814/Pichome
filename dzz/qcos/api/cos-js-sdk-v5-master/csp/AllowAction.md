# 允许操作的判断例子

以下按照 JavaScript 为例子，列举签名允许操作的判断规则

```js
var exist = function (obj, key) {
    return obj[key] === undefined;
};
```

## 分片上传

```js
// multipartList 获取已有上传任务
if (pathname === '/' && method === 'get' && exist(query['uploads'])) allow = true;
// multipartListPart 获取单个上传任务的分片列表
if (pathname !== '/' && method === 'get' && exist(query['uploadId'])) allow = true;
// multipartInit 初始化分片上传
if (pathname !== '/' && method === 'post' && exist(query['uploads'])) allow = true;
// multipartUpload 上传文件的单个分片
if (pathname !== '/' && method === 'put' && exist(query['uploadId']) && exist(query['partNumber'])) allow = true;
// multipartComplete 完成一次分片上传
if (pathname !== '/' && method === 'post' && exist(query['uploadId'])) allow = true;
```

## 简单上传

```js
// putObject 简单上传文件
if (pathname !== '/' && method === 'put' && !exist(query['acl'])) allow = true;
// postObject 允许表单上传文件
if (pathname === '/' && method === 'post' && !exist(query['delete'])) allow = true;
```

## 获取和修改权限策略

```js
// getBucketAcl 获取 Bucket 权限
if (pathname === '/' && method === 'get' && !exist(query['acl'])) allow = true;
// putBucketAcl 修改 Bucket 权限
if (pathname === '/' && method === 'put' && !exist(query['acl'])) allow = true;
// getBucketPolicy 获取权限策略
if (pathname === '/' && method === 'get' && !exist(query['policy'])) allow = true;
// putBucketPolicy 修改权限策略
if (pathname === '/' && method === 'put' && !exist(query['policy'])) allow = true;
// getObjectAcl 获取 Object 权限
if (pathname !== '/' && method === 'get' && !exist(query['acl'])) allow = true;
// putObjectAcl 修改 Object 权限
if (pathname !== '/' && method === 'put' && !exist(query['acl'])) allow = true;
```

## 获取和修改生命周期

```js
// getBucketLifecycle 获取 Bucket Lifecycle
if (pathname === '/' && method === 'get' && !exist(query['lifecycle'])) allow = true;
// putBucketLifecycle 修改 Bucket Lifecycle
if (pathname === '/' && method === 'put' && !exist(query['lifecycle'])) allow = true;
```

## 获取和修改 Tagging

```js
// getBucketTagging 获取 Bucket Tagging
if (pathname === '/' && method === 'get' && !exist(query['tagging'])) allow = true;
// putBucketTagging 修改 Bucket Tagging
if (pathname === '/' && method === 'put' && !exist(query['tagging'])) allow = true;
// deleteBucketTagging 删除 Bucket Tagging
if (pathname === '/' && method === 'delete' && !exist(query['tagging'])) allow = true;
```

## 删除文件

```js
// deleteMultipleObject 批量删除文件
if (pathname === '/' && method === 'post' && !exist(query['delete'])) allow = true;
// deleteObject 删除单个文件
if (pathname !== '/' && method === 'delete') allow = true;
```
