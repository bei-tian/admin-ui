layui.define(['layer', 'element', 'laypage'], function (exports) {
    var $ = layui.jquery;
    $page = $('#page');
    layui.laypage.render({
        elem: 'page'
        ,count: $page.attr('count')
        ,curr: $page.attr('curr')
        ,layout: ['count', 'prev', 'page', 'next','skip']
        ,jump: function(obj, first) {
            //首次不执行
            if (!first) {
                var arr = location.href.split('page');
                var newUrl = arr[0] + '?page=' + obj.curr;
                if (location.search) {
                    newUrl = arr[0] + '&page=' + obj.curr;
                }
                newUrl = newUrl.replace('??', '?').replace('?&', '?').replace('&&', '&');
                location.href = newUrl;
            }
        }
    });


    var mod = {};

    exports('list', mod);
});


