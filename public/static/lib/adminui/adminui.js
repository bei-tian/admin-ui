layui.extend({
    'adminui': '{/}static/lib/adminui/adminui',
    'adminui-nav': '{/}static/lib/adminui/modules/nav',
    'list': '{/}static/lib/admin-ui/modules/list',
});

// 主入口方法
layui.use(['layer', 'element', 'util'], function () {
    window.$ = layui.jquery;
    window.layer = layui.layer;
    // 操作对象
    var device = layui.device()
        , element = layui.element
        , layer = layui.layer
        , util = layui.util
        , cardIdx = 0
        , cardLayId = 0
        , side = $('.my-side')
        , body = $('.my-body')
        , footer = $('.my-footer');

    //阻止IE7以下访问
    if (device.ie && device.ie < 8) {
        layer.alert('不支持ie8以下的浏览器！');
    }

    // 导航栏收缩
    function navHide(t, st) {
        var time = t ? t : 50;
        st ? localStorage.log = 1 : localStorage.log = 0;
        side.animate({'left': -200}, time);
        body.animate({'left': 0}, time);
        footer.animate({'left': 0}, time);
    }

    // 导航栏展开
    function navShow(t, st) {
        var time = t ? t : 50;
        st ? localStorage.log = 0 : localStorage.log = 1;
        side.animate({'left': 0}, time);
        body.animate({'left': 200}, time);
        footer.animate({'left': 200}, time);
    }

    // 监听导航栏收缩
    $('.btn-nav').on('click', function () {
        if (localStorage.log == 0) {
            navShow(50);
        } else {
            navHide(50);
        }
    });

    // 根据导航栏text获取lay-id
    function getTitleId(card, title) {
        var id = -1;
        $(document).find(".layui-tab[lay-filter=" + card + "] ul li").each(function () {
            if (title === $(this).find('span').html()) {
                id = $(this).attr('lay-id');
            }
        });
        return id;
    }

    // 添加TAB选项卡
    window.addTab = function (elem, title, url) {
        var card = 'card';                                         // 选项卡对象
        title = title ? title : $(elem).find('a').html();              // 导航栏text
        var src = url ? url : $(elem).find('a').data('href');      // 导航栏跳转URL
        var id = new Date().getTime();                                  // ID
        var flag = getTitleId(card, title);                             // 是否有该选项卡存在
        // 大于0就是有该选项卡了
        if (flag > 0) {
            id = flag;
        } else {
            if (src) {
                //新增
                element.tabAdd(card, {
                    title: '<span key="'+$(elem).attr('key')+'">' + title + '</span>'
                    , content: '<iframe src="' + src + '" frameborder="0"></iframe>'
                    , id: id
                });
                // 关闭弹窗
                layer.closeAll();
            }
        }
        // 切换相应的ID tab
        element.tabChange(card, id);

    };


    element.on('nav(side-main)', function () {
        location.hash = window.currHref = $(this).find('a').data('href');
        window.addTab(this);
    });


    //切换选项卡时，同步导航栏状态
    element.on('tab(card)', function(){
        var key = $(this).find('span').attr('key');
        $('.admin-top-nav li').eq(key.split('-')[0]).click();
        $(".layui-nav-child dd").removeClass('layui-this');
        $("dd[key='"+key+"']").addClass('layui-this');
    });


    // 删除选项卡
    window.delTab = function (layId) {
        // 删除
        element.tabDelete('card', layId);
    };

    // 删除所有选项卡
    window.delAllTab = function () {
        // 选项卡对象
        layui.each($('.my-body .layui-tab-title > li'), function (k, v) {
            var layId = $(v).attr('lay-id');
            if (layId > 1) {
                // 删除
                element.tabDelete('card', layId);
            }
        });
    };

    // 获取当前选中选项卡lay-id
    window.getThisTabID = function () {
        // 当前选中的选项卡id
        return $(document).find('body .my-body .layui-tab-card > .layui-tab-title .layui-this').attr('lay-id');
    };

    // 双击关闭相应选项卡
    $(document).on('dblclick', '.my-body .layui-tab-card > .layui-tab-title li', function () {
        // 欢迎页面以外，删除选项卡
        if ($(this).index() > 0) {
            element.tabDelete('card', $(this).attr('lay-id'));
        } else {
            layer.msg('欢迎页面不能关闭')
        }
    });

    // 选项卡右键事件阻止
    $(document).on("contextmenu", '.my-body .layui-tab-card > .layui-tab-title li', function () {
        return false;
    });

    // 选项卡右键事件
    $(document).on("mousedown", '.my-body .layui-tab-card > .layui-tab-title li', function (e) {
        // 判断是右键点击事件并且不是欢迎页面选项卡
        if (3 == e.which && $(this).index() > 0) {
            // 赋值
            cardIdx = $(this).index();
            cardLayId = $(this).attr('lay-id');
            console.log('lay-id:' + cardLayId);
            // 选择框
            layer.tips($('.my-dblclick-box').html(), $(this), {
                skin: 'dblclick-tips-box',
                tips: 3,
                time: false
            });
        }
    });

    // 点击body关闭tips
    $(document).on('click', 'html', function () {
        layer.closeAll('tips');
    });

    // 右键提示框菜单操作-刷新页面
    $(document).on('click', '.card-refresh', function () {
        // 窗体对象
        var ifr = $(document).find('.my-body .layui-tab-content .layui-tab-item iframe').eq(cardIdx);
        // 刷新当前页
        ifr.attr('src', ifr.attr('src'));
        // 切换到当前选项卡
        element.tabChange('card', cardLayId);
    });

    // 右键提示框菜单操作-关闭页面
    $(document).on('click', '.card-close', function () {
        // 删除
        window.delTab(cardLayId);
    });

    // 右键提示框菜单操作-关闭所有页面
    $(document).on('click', '.card-close-all', function () {
        // 删除
        window.delAllTab();
    });

    // 监听顶部右侧导航
    element.on('nav(side-top-right)', function (elem) {
        var theme = $(this).data('theme');
        $.get('/home/setTheme',{theme:theme},function (data) {
            location.reload();
        });
    });


    // 皮肤
    function skin() {
        var skin = localStorage.skin ? localStorage.skin : 0;
        $('#skin').attr('href','static/css/skin-'+skin+'.css');
    }

    // 工具
    function _util() {
        var bar = $('.layui-fixbar');
        // 分辨率小于1023  使用内部工具组件
        if ($(window).width() < 1023) {
            util.fixbar({
                bar1: '&#xe602;'
                , css: {left: 10, bottom: 54}
                , click: function (type) {
                    if (type === 'bar1') {
                        //iframe层
                        layer.open({
                            type: 1,                        // 类型
                            title: false,                   // 标题
                            offset: 'l',                    // 定位 左边
                            closeBtn: 0,                    // 关闭按钮
                            anim: 0,                        // 动画
                            shadeClose: true,               // 点击遮罩关闭
                            shade: 0.8,                     // 半透明
                            area: ['150px', '100%'],        // 区域
                            skin: 'my-mobile',              // 样式
                            content: $('body .my-side').html() // 内容
                        });
                    }
                    element.init();
                }
            });
            bar.removeClass('layui-hide');
            bar.addClass('layui-show');
        } else {
            bar.removeClass('layui-show');
            bar.addClass('layui-hide');
        }
    };

    // 自适应
    $(window).on('resize', function () {
        if ($(window).width() > 1023) {
            navShow(10);
        } else {
            navHide(10);
        }
        _util();
    });

    // 监听控制content高度
    function init(config) {
        // 起始判断-收缩/展开
        if (!localStorage.log) {
            if ($(window).width() > 1023) {
                if (localStorage.log == 0) {
                    navHide(10);
                } else {
                    navShow(10);
                }
            } else {
                navHide(10);
            }
        } else {
            if (localStorage.log == 0) {
                navHide(10);
            } else {
                navShow(10);
            }
        }
        // 工具
        _util();
        // skin
        skin();
        // 选项卡高度
        cardTitleHeight = $(document).find(".layui-tab[lay-filter='card'] ul.layui-tab-title").height();
        // 需要减去的高度
        height = $(window).height() - $('.layui-header').height() - cardTitleHeight - $('.layui-footer').height();
        // 设置高度
        $(document).find(".layui-tab[lay-filter='card'] div.layui-tab-content").height(height - 2);
    }

    //设置导航
    var mod = {
        setNav:function (api) {
            layui.navigation.init(api);
        }
    };

});