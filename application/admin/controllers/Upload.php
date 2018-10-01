<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends Common
{
    function index()
    {
        $res['code'] = 0;
        $res['msg'] = '';

        $base = './uploads/';
        if (@$_FILES['file']['size'] < 5) {
            $res['code'] = -1;
            $res['msg'] = '请选择文件';
        }
        $ext = strtolower(end(explode('.', $_FILES['file']['name'])));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
            $res['code'] = -1;
            $res['msg'] = '不允许上传的文件格式';
        }

        if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
            $res['code'] = -1;
            $res['msg'] = '文件大小不能超过10M';
        }

        //创建日期文件夹
        $date = date("Y-m-d");
        @mkdir($base . $date, 0777);

        $filePath = $base . $date . "/" . date('His') . "." . $ext;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            $res['src'] = str_replace($base, '', $filePath);
        } else {
            $res['code'] = -1;
            $res['msg'] = '文件上传失败！';
        };

        echo json_encode($res);
    }

}
