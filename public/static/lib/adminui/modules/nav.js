
layui.define(['layer', 'element'], function (exports) {
    var element = layui.element,
        $ = layui.jquery;

    var mod = {
        init: function (url) {
            $.get(url,function (res) {
                var nav = res.data;
                console.log(nav);
                //主导航
                var view = "";
                $(nav).each(function(k,v) {
                    view += '<li class="layui-nav-item" data-id="'+ v.icon + '" key="'+ k + '"><a href="javascript:;"><i class="adminui-icon">' + v.icon + '</i>'+v.name+'</a></li>';
                });
                $('.admin-top-nav').html(view);
                element.init();

                //点击主导航后显示子导航
                $('.admin-top-nav li').click(function() {
                    var index = $(this).index();
                    var sub = nav[index].sub;
                    var view = "";
                    $(sub).each(function(k,v) {
                        view += '<li class="layui-nav-item layui-nav-itemed" key="'+ index + '-' + k + '">';
                        if (v.sub) {
                            view += '<a href="javascript:;"><i class="adminui-icon">' + v.icon + '</i>' + v.name + '</a><dl class="layui-nav-child">';
                            $(v.sub).each(function (ko, vo) {
                                view += '<dd key="'+ index + '-' + k + '-'+ ko +'">';
                                view += '<a href="javascript:;" data-href="' + vo.href + '">';
                                view += '<i class="adminui-icon">' + vo.icon + '</i>' + vo.name + '</a></dd>';
                            });
                            view += '<dl>';
                        } else {
                            view += '<a href="javascript:;" data-href="' + v.href + '">';
                            view += '<i class="adminui-icon">' + v.icon + '</i>' + v.name + '</a>';
                        }
                        view += '</li>';
                        $(".layui-nav-tree").html(view);
                        element.init();
                    });
                });

                //默认打开页
                var key = findKey(nav, location.hash);
                $('.admin-top-nav li').eq(key[0]).click();
                $("dd[key='"+key.join('-')+"']").click();
                function findKey(nav, url) {
                    if(!url) {
                        return [0];
                    }
                    var key = [];
                    $(nav).each(function(i,topNav) {
                        $(topNav.sub).each(function(j,menu) {
                            if(menu.href == url.replace('#','')) {
                                key = [i,j]
                            }
                            $(menu.sub).each(function (k, sub) {
                                if(sub.href == url.replace('#','')) {
                                    key = [i,j,k]
                                }
                            });
                        });
                    });
                    return key;
                }



            },'json');
        }
    };


    exports('adminui-nav', mod);
});


