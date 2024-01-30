var util = require('./util');

// 按照文件特征值，缓存 UploadId
var cacheKey = 'cos_sdk_upload_cache';
var expires = 30 * 24 * 3600;
var cache;
var timer;

var init = function () {
    if (cache) return;
    cache = JSON.parse(localStorage.getItem(cacheKey) || '[]') || [];
    // 清理太老旧的数据
    var changed = false;
    var now = Math.round(Date.now() / 1000);
    for (var i = cache.length - 1; i >= 0; i--) {
        var mtime = cache[i][2];
        if (!mtime || mtime + expires < now) {
            cache.splice(i, 1);
            changed = true;
        }
    }
    changed && localStorage.setItem(cacheKey, JSON.stringify(cache));
};

// 把缓存存到本地
var save = function () {
    if (timer) return;
    timer = setTimeout(function () {
        localStorage.setItem(cacheKey, JSON.stringify(cache));
        timer = null;
    }, 400);
};

var mod = {
    using: {},
    // 标记 UploadId 正在使用
    setUsing: function (uuid) {
        mod.using[uuid] = true;
    },
    // 标记 UploadId 已经没在使用
    removeUsing: function (uuid) {
        delete mod.using[uuid];
    },
    // 用上传参数生成哈希值
    getFileId: function (file, ChunkSize, Bucket, Key) {
        if (file.name && file.size && file.lastModifiedDate && ChunkSize) {
            return util.md5([file.name, file.size, file.lastModifiedDate, ChunkSize, Bucket, Key].join('::'));
        } else {
            return null;
        }
    },
    // 获取文件对应的 UploadId 列表
    getUploadIdList: function (uuid) {
        if (!uuid) return null;
        init();
        var list = [];
        for (var i = 0; i < cache.length; i++) {
            if (cache[i][0] === uuid)
                list.push(cache[i][1]);
        }
        return list.length ? list : null;
    },
    // 缓存 UploadId
    saveUploadId: function (uuid, UploadId, limit) {
        init();
        if (!uuid) return;
        // 清理没用的 UploadId
        for (var i = cache.length - 1; i >= 0; i--) {
            var item = cache[i];
            if (item[0] === uuid && item[1] === UploadId) {
                cache.splice(i, 1);
            }
        }
        cache.unshift([uuid, UploadId, Math.round(Date.now() / 1000)]);
        if (cache.length > limit) cache.splice(limit);
        save();
    },
    // UploadId 已用完，移除掉
    removeUploadId: function (UploadId) {
        init();
        delete mod.using[UploadId];
        for (var i = cache.length - 1; i >= 0; i--) {
            if (cache[i][1] === UploadId) cache.splice(i, 1)
        }
        save();
    },
};

module.exports = mod;
