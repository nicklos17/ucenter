/**
 * ==============================
 *          登录js入口
 * ==============================
 */
define(function(require){

    var Validator = require('validator');
    var validator = new Validator();

    var showTips = function(show, msg, hide){
        $(show).show().html(msg);
        $(hide).hide();
    };

    var showErr = function(field, msg){
        $('#tips-error-' + field).show().html(msg);
        $('#'+field).addClass('input-error');
    };

    var focusErr = function(field, msg){
        $('#'+field).focus().addClass('input-error').attr('placeholder', '');
        $('#tips-error-'+field).show().html(msg);
    };

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    $('#auto-login').click(function(){
        if($(this).attr('checked')){
            $(this).removeAttr('checked');
        }
        else{
            $(this).attr('checked', 'true');
        }
    });

    $('#mobile').blur(function(){
        var mobile = $(this).val();
        if(mobile == '') return;
        if(! validator.isPhone(mobile)){
            showErr('mobile', '请输入正确的手机号');
        }
        else{
            $('#tips-error-mobile').hide();
            $(this).removeClass('input-error');
        }
    });

    $('#mobile').keyup(function(){
        $('#tips-error-mobile').hide();
        $(this).removeClass('input-error');
    });

    $('#passwd').keyup(function(){
        $('#tips-error-passwd').hide();
        $(this).removeClass('input-error');
    });

    var login = function(){
        var mobile    = $.trim($('#mobile').val());
        var pwd       = $('#passwd').val();
        var backUrl   = $("input[name='backurl']").val();
        var siteId   = $("input[name='siteId']").val();
        var autoLogin = $('#auto-login').attr('checked');

        $('#tips-error-mobile, #tips-error-passwd').hide();
        $('#mobile, #passwd').removeClass('input-error');

        if(!$('#mobile').val()){
            focusErr('mobile', '请输入手机号码');
            return false;
        }
        if(! validator.isPhone(mobile)){
            focusErr('mobile', '请输入正确的手机号');
            return false;
        }
        if(! pwd){
            focusErr('passwd', '请输入密码');
            return false;
        }
        else if(validator.strLen(pwd) < 6 || validator.strLen(pwd) > 20){
            focusErr('passwd', '密码错误');
            return false;
        }
        else{
            autoLogin = (autoLogin == 'checked') ? 1 : 0;
            if($.ajaxSetup){
                $.ajaxSetup({
                    beforeSend:function(XMLHttpRequest){
                        $('#login-btn').val('登录中...');
                    },
                    complete:function (XMLHttpRequest, textStatus) {
                        $('#login-btn').val('立即登录');
                    }
                });
            }
            $.ajax({
                type:"POST",
                url: "/login",
                dataType: 'json',
                data: "mobile="+mobile+"&passwd="+pwd+"&autoLogin="+autoLogin+"&backurl="+backUrl+"&siteId="+siteId,
                success: function(msg){
                    if(msg.ret == 1){
                        if(msg.backurl){
                            location.href = msg.backurl;
                        }
                        else {
                            location.href = '/user/index';
                        }
                    }
                    else{
                        for(var key in msg.msg){
                            showErr(key, msg.msg[key].msg);
                        }
                    }
                },
                error:function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    };

    $('#login-btn').click(login);

    $(document).keyup(function(e){
        var ev = document.all ? window.event : e;
        if(ev.keyCode == 13){
            login();
        }
    });

});
