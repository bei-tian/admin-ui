<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends Common
{
    function index()
    {


        $base = './uploads/';
        if (@$_FILES['file']['size'] < 5) {
            $this->err('未选择文件');
        }
        $ext = strtolower(end(explode('.', $_FILES['file']['name'])));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $this->err('不允许上传的文件格式');
        }

        if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
            $this->err('文件大小不能超过10M');
        }

        //创建日期文件夹
        $date = date("Y-m-d");
        @mkdir($base . $date, 0777);

        $filePath = $base . $date . "/" . date('His') . "." . $ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $res['code'] = 1;
            $res['msg'] = '';
            $res['src'] = str_replace($base, '', $filePath);
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

    private function err($msg) {
        $res['code'] = -1;
        $res['err'] = $msg;
        $res['msg'] = $msg;
        echo json_encode($res);
        exit;
    }

}
