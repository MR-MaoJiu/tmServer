<?php

namespace app\admin\controller;

use think\Controller;

class Sts extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        // 临时密钥计算样例

        include './qcloud-sts-sdk.php'; // 这里获取 sts.php https://github.com/tencentyun/qcloud-cos-sts-sdk/blob/master/php/sts/sts.php
        $sts = new \STS();
    // 配置参数
        $config = array(
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => 'AKIDmPK5eEee290cw0rpKYVGnwoOhkW2uSGs', // 固定密钥
            'secretKey' => 'TgdIgrnWy92g392hDUTDgsnV9k58vQgg', // 固定密钥
            'bucket' => 'jytx-1301890194', // 换成你的 bucket
            'region' => 'ap-nanjing', // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*', // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的目录，例子：* 或者 a/* 或者 a.jpg
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions' => array (
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
            )
        );
// 获取临时密钥，计算签名
        $tempKeys = $sts->getTempKeys($config);

// 返回数据给前端
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: http://www.aiyouhui8.com'); // 这里修改允许跨域访问的网站
        header('Access-Control-Allow-Headers: origin,accept,content-type');
        echo json_encode($tempKeys);
    }
}
