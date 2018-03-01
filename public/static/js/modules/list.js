layui.define(['layer', 'element', 'laypage'], function (exports) {
    var $ = layui.jquery;
    $page = $('#page');
    layui.laypage.render({
        elem: 'page'
        ,count: $page.attr('count')
    });


    var mod = {};

    exports('list', mod);
});


