var fs = require('fs');
var path = require('path');
var webpack = require('webpack');
var pkg = require('./package');

// replaceVersion
var replaceVersion = function () {
    var filePath = path.resolve(__dirname, 'src/cos.js');
    var content = fs.readFileSync(filePath).toString();
    if (content) {
        var newContent = content.replace(/(COS\.version) *= *['"]\d+\.\d+\.\d+['"];/, "$1 = '" + pkg.version + "';");
        if (newContent !== content) {
            fs.writeFileSync(filePath, newContent);
            console.log('cos.js version updated.');
        }
    }
};
var replaceDevCode = function (list) {
    list.forEach(function (fileName) {
        var filePath = path.resolve(__dirname, fileName);
        var content = fs.readFileSync(filePath).toString();
        var newContent = content;
        newContent = newContent.replace(/https:\/\/\w+\.com\/[\w\-]+\/server\//, 'https://example.com/');
        newContent = newContent.replace(/test-125\d{7}/, 'test-1250000000');
        newContent = newContent.replace(/'proxy' => 'http:\/\/[^']+',/, "'proxy' => '',");
        newContent = newContent.replace(/proxy: 'http:\/\/[^']+',/, "proxy: '',");
        newContent = newContent.replace(/AKID\w+/, 'AKIDxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');
        newContent = newContent.replace(/'secretKey' => '[^']+',/, "'secretKey' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',");
        newContent = newContent.replace(/secretKey: '[^']+',/, "secretKey: 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',");
        newContent = newContent.replace(/'allowActions' *=> *array\([^)]+\)/, `'allowActions' => array(
        // 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
        // 简单上传
        'name/cos:PutObject',
        // 分片上传
        'name/cos:InitiateMultipartUpload',
        'name/cos:ListMultipartUploads',
        'name/cos:ListParts',
        'name/cos:UploadPart',
        'name/cos:CompleteMultipartUpload'
    )`);
        if (newContent !== content) {
            console.log('replace ' + filePath);
            fs.writeFileSync(filePath, newContent);
        }
    });
};
replaceVersion();

module.exports = {
    entry: path.resolve(__dirname, './index.js'),
    output: {
        path: path.resolve(__dirname, './dist'),
        publicPath: '/dist/',
        filename: 'cos-js-sdk-v5.js',
        libraryTarget: 'umd',
        library: 'COS',
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                loader: 'babel-loader',
                exclude: /node_modules/
            }
        ]
    },
    devServer: {
        historyApiFallback: true,
        noInfo: true
    },
    performance: {
        hints: false
    },
};

if (process.env.NODE_ENV === 'production') {
    replaceDevCode([
        'demo/demo.js',
        'demo/queue/index.js',
        'test/test.js',
        'server/sts.js',
        'server/sts.php',
    ]);
    module.exports.output.filename = 'cos-js-sdk-v5.min.js';
    module.exports.plugins = (module.exports.plugins || []).concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            sourceMap: true,
            output: {
                ascii_only: true,
            },
            compress: {
                warnings: false,
            },
        }),
        new webpack.LoaderOptionsPlugin({
            minimize: true
        }),
    ])
}