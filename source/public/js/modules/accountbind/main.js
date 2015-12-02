/**
 * ==============================
 *          账号绑定js入口
 * ==============================
 */
define(function(require){

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    var unbind = function(type){
        $("#"+type+"-popup").hide();
        var bind= $('#unbind-'+type).attr('data');
        $.ajax({
            type: "POST",
            url: "/user/accountunbind",
            dataType: 'json',
            data: "bind="+bind+"&type="+type,
            success: function(msg){
                if(msg.ret == 1){
                    location.href = "";
                }
                else{
                    location.href = "";
                }
            },
            error:function(){
                showFlashTip('服务器出现异常，请稍候重试');
            }
        });
    };

    $('#bind-sina').on('click', function(){
        location.href = "/index/wboauth";
    });

    $('#bind-qq').on('click', function(){
        location.href = "/index/qqoauth";
    });

    $('#bind-wx').on('click', function(){
        location.href = "/index/weixin";
    });

    $('#bind-alipay').on('click', function(){
        location.href = "/index/alipay";
    });

    $('#unbind-sina').on('click', function(){
        $('#sina-popup').removeClass('dn');
    });

    $('#unbind-qq').on('click', function(){
        $('#qq-popup').removeClass('dn');
    });

    $('#unbind-wx').on('click', function(){
        $('#wx-popup').removeClass('dn');
    });

    $('#unbind-alipay').on('click', function(){
        $('#alipay-popup').removeClass('dn');
    });

    $('#unbind-confirm-sina').on('click', function(){
        unbind('sina');
    });

    $('#unbind-confirm-qq').on('click', function(){
        unbind('qq');
    });

    $('#unbind-confirm-wx').on('click', function(){
        unbind('wx');
    });

    $('#unbind-confirm-alipay').on('click', function(){
        unbind('alipay');
    });

    $('#unbind-cancel-sina').on('click', function(){
        $('#sina-popup').addClass('dn');
    });

    $('#unbind-cancel-qq').on('click', function(){
        $('#qq-popup').addClass('dn');
    });

    $('#unbind-cancel-wx').on('click', function(){
        $('#wx-popup').addClass('dn');
    });

    $('#unbind-cancel-alipay').on('click', function(){
        $('#alipay-popup').addClass('dn');
    });
});