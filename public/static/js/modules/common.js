layui.define(['layer','form'], function (exports) {
    window.$ = layui.jquery;

    window.layerOpen = function(url, width, height, title) {
        width = width || '1000px';
        height = height || '600px';
        parent.layer.open({
            type: 2,
            title: title,
            area: [width, height], //宽高
            content: url
        });
    };

    //弹出一个窗口
    $('.layer-open').click(function(){
        var title = $(this).attr('title');
        if(!title) title = $(this).html();

        layerOpen($(this).data('url'), $(this).attr('w'), $(this).attr('h'), title);
        return false;
    });


    //删除
    $(".delete").click(function () {
        if(confirm('确定要删除该条记录吗？')) {
            $.get($(this).data('url'),function (res) {
                if(res == 'ok') {
                    location.reload();
                } else {
                    parent.layer.msg(res);
                }
            })
        }
    });

    var mod = {};

    exports('common', mod);
});


