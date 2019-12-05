<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends Common
{
    function index()
    {
        $type = $this->get('type', 'images');
        $fileName = $this->get('fileName');
        $path = './uploads/';
        $base = $path.$type.'/';
        @mkdir($base, 0777);
        if (@$_FILES['file']['size'] < 5) {
            $this->err('未选择文件');
        }
        $ext = strtolower(end(explode('.', $_FILES['file']['name'])));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'mp3', 'pdf'])) {
            $this->err('不允许上传的文件格式');
        }

        if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
            $this->err('文件大小不能超过10M');
        }

        //创建日期文件夹
        $date = date("Y-m-d");
        @mkdir($base . $date, 0777);

        if (empty($fileName)) {
            $filePath = $base . $date . "/" . date('His') . "." . $ext;
        } elseif ($fileName == 'old') {
            $filePath = $base . $date . "/" . $_FILES['file']['name'];
        } else {
            $filePath = $base . $date . "/" . $fileName;
        }

        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $res['code'] = 1;
            $res['msg'] = '';
            $res['filename'] = $_FILES['file']['name'];
            $res['src'] = str_replace($path, '', $filePath);
            echo json_encode($res);
        } else {
            $this->err('文件上传失败');
        };
    }

    //html5上传
    function xheditor() {
        $base = './uploads/xheditor/';
        preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info);
        $fileName = $info[2];
        $ext = strtolower(end(explode('.', $fileName)));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $this->err('不允许上传的文件格式');
        }

        $date = date("Y-m-d");
        @mkdir($base . $date, 0777);
        $filePath = $base . $date . "/" . date('His') . "." . $ext;
        file_put_contents($filePath,file_get_contents("php://input"));
        $res['err'] = '';
        $res['msg'] = str_replace('./', '/', $filePath);
        echo json_encode($res);
    }

    //附件上传
    function attach() {
        $base = './uploads/xheditor/';
        preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info);
        $fileName = $info[2];
        $ext = strtolower(end(explode('.', $fileName)));
        if (!in_array($ext, ['zip','rar','txt','doc','docx','xls','xlsx'])) {
            $this->err('不允许上传的文件格式');
        }

        $date = date("Y-m-d");
        @mkdir($base . $date, 0777);
        $filePath = $base . $date . "/" . date('His') . "." . $ext;
        file_put_contents($filePath,file_get_contents("php://input"));
        $res['err'] = '';
        $res['msg'] = '!'.str_replace('./', '/', $filePath).'||'.$fileName;
        echo json_encode($res);
    }

    private function err($msg) {
        $res['code'] = -1;
        $res['err'] = $msg;
        $res['msg'] = $msg;
        echo json_encode($res);
        exit;
    }

}
