layui.define(['form'], function (exports) {
    var $ = layui.jquery;
    var layer = parent.layui.layer;

    layui.form.on('submit(*)', function(data){

        var url = $('form').attr('action');
        $.ajax({
            url: url,
            type: "post",
            data: data.field,
            success: function(res){
                try {
                    res = JSON.parse(res);
                } catch(error) {
                    layer.alert('接口数据格式出错，请返回json格式数据！');
                    return false;
                }
                if(res.code === 200) {
                    parent.layui.$(".admin-tabsbody .layui-show iframe")[0].contentWindow.location.reload();
                    layer.closeAll();
                    layer.msg('添加成功！');
                } else {
                    layer.alert('提交失败！');
                }

            },
            error: function () {
                layer.alert('接口出错！');
            }
        });

        return false;
    });
    var mod = {};

    exports('add', mod);
});


