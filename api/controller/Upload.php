<?php

namespace app\api\controller;

use think\Controller;
use think\Request;

class Upload extends Common
{

    /**
     * 上传图片
     * @param Request $request
     * @return \think\response\Json
     */
    public function upload(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('file');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move('./uploads/home/images');
        if ($info) {
            // 成功上传后 获取上传信息
            // 输出 jpg
            $save_name = $info->getSaveName();
            $save_name1 = str_replace("\\", "/", $save_name);
            $path = '/uploads/home/images/' . $save_name1;
            return json(['code' => 1, 'msg' => '上传成功', 'url' => $path]);
        } else {
            // 上传失败获取错误信息
            return json(['code' => 0, 'msg' => $file->getError()]);
        }
    }

    /**
     * @return \think\response\Json
     */
    public function uploadFile(Request $request)
    {

        // 获取表单上传文件 例如上传了001.jpg
        // $file = $request->file('file');
        // // 移动到框架应用根目录/uploads/ 目录下
        // $info = $file->move('./uploads/home/videos/');
        // if ($info) {
        //     // 成功上传后 获取上传信息
        //     // 输出 jpg
        //     $path = '/uploads/home/videos/' . $info->getSaveName();
        //     return json(['code' => 1, 'msg' => '上传成功', 'url' => $path]);
        // } else {
        //     // 上传失败获取错误信息
        //     return json(['code' => 0, 'msg' => $file->getError()]);
        // }

        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Content-type: text/html; charset=gbk32");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        $folder = input('folder');
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit; // finish preflight CORS requests here
        }
        if (!empty($_REQUEST['debug'])) {
            $random = rand(0, intval($_REQUEST['debug']));
            if ($random === 0) {
                header("HTTP/1.0 500 Internal Server Error");
                exit;
            }
        }
        // header("HTTP/1.0 500 Internal Server Error");
        // exit;
        // 5 minutes execution time
        set_time_limit(0);
        // Uncomment this one to fake upload time
//        usleep(5000);
        // Settings
        $targetDir = './uploads/videos/home' . DIRECTORY_SEPARATOR . 'file_material_tmp';            //存放分片临时目录
        if ($folder) {
            $uploadDir = './uploads/videos/home' . DIRECTORY_SEPARATOR . 'file_material' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . date('Ymd');
        } else {
            $uploadDir = './uploads/videos/home' . DIRECTORY_SEPARATOR . 'file_material' . DIRECTORY_SEPARATOR . date('Ymd');    //分片合并存放目录
        }
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // Create target dir
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        // Create target dir
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        $oldName = $fileName;
        $fileName = iconv('UTF-8', 'gb2312', $fileName);
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        // $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory111."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream222."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file333."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream444."}, "id" : "id"}');
            }
        } else {
            if (!$in = fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream555."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        fclose($out);
        fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
        $index = 0;
        $done = true;
        for ($index = 0; $index < $chunks; $index++) {
            if (!file_exists("{$filePath}_{$index}.part")) {
                $done = false;
                break;
            }
        }
        if ($done) {
            $pathInfo = pathinfo($fileName);
            $hashStr = substr(md5($pathInfo['basename']), 8, 16);
            $hashName = time() . $hashStr . '.' . $pathInfo['extension'];
            $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $hashName;
            if (!$out = fopen($uploadPath, "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream666."}, "id" : "id"}');
            }
            //flock($hander,LOCK_EX)文件锁
            if (flock($out, LOCK_EX)) {
                for ($index = 0; $index < $chunks; $index++) {
                    if (!$in = fopen("{$filePath}_{$index}.part", "rb")) {
                        break;
                    }
                    while ($buff = fread($in, 4096)) {
                        fwrite($out, $buff);
                    }
                    fclose($in);
                    unlink("{$filePath}_{$index}.part");
                }
                flock($out, LOCK_UN);
            }
            fclose($out);
            $response = [
                'code' => 1,
                'success' => true,
                'oldName' => $oldName,
                'filePath' => substr($uploadPath, 1),
//                'fileSize'=>$data['size'],
                'fileSuffixes' => $pathInfo['extension'],          //文件后缀名
//                'file_id'=>$data['id'],
            ];
            return json($response);
        }

        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

}
