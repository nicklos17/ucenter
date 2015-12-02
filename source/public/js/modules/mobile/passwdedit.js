/**
 * ====================================
 *          手机端修改密码js入口
 * ====================================
 */
define(function(require){

    var Validator = require('validator');
    var validator = new Validator();

    var showStrength = function(strengthBk){
        $("#pwd-weak, #pwd-strong, #pwd-mid").attr("class", "pass-low");
        $('#pwd-' + strengthBk).attr('class', 'pass-high');
    };

    var showErrTip = function(field, msg){
        $('#tips-suc-' + field).hide();
        $('#tips-error-' + field).show().html(msg);
    };

    var showSucTip = function(field){
        $('#tips-error-' + field).hide();
        $('#tips-suc-' + field).show();
    };

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    $('#password').keyup(function(){
        var pwd = $(this).val();
        var strength = validator.pwdStrength(pwd);

        if(strength == 0 ){
            showStrength('weak');
        }
        else if(strength == 1){
            showStrength('strong');
        }
        else if(strength == 2){
            showStrength('mid');
        }
        else{
            showStrength('weak');
        }

        if(validator.isPassword(pwd)){
            $('#tips-error-passwd').hide();
        }
    });

    $('#password').blur(function(){
        var pwd = $(this).val();
        if( validator.isPassword(pwd)){
            $('#tips-error-passwd').hide();
        }
        else{
            $('#tips-error-passwd').show().html("请输入6-20位数字、字母或常用符号");
        }
    });

    $('#repwd').blur(function(){
        var rePwd = $(this).val();
        var pwd   = $("#password").val();

        if(rePwd != ''){
            if(rePwd == pwd){
                showSucTip('repwd');
            }
            else{
                showErrTip('repwd', '两次密码不一致');
            }
        }
        else{
            showErrTip('repwd', '请重复输入密码');
        }
    });

    $('#repwd').keyup(function(){
        $('#tips-error-repwd, #tips-suc-repwd').hide();
    });

    $('#captcha').keyup(function(){
        $('#tips-error-captcha').hide();
    });

    $('#captcha').blur(function(){
        var captcha = $.trim($('#captcha').val());
        if(captcha){
            if(validator.isCode(captcha)){
                $('#tips-error-captcha').hide();
            }
            else{
                $('#tips-error-captcha').show().html('验证码错误');
            }
        }
        else{
            $('#tips-error-captcha').show().html('请输入验证码');
        }
    });

    $('#password, #captcha, #repwd').keyup(function(){
        var pwd = $("#password").val();
        var rePwd = $("#repwd").val();
        var captcha = $("#captcha").val();
        var btnChangePwd = $('#btn-change-pwd');

        if(validator.isCode(captcha) && validator.isPassword(pwd) && pwd == rePwd){
            btnChangePwd.addClass('btn-blue').removeAttr('disabled');
        }
        else{
            btnChangePwd.removeClass('btn-blue');
            $('#pass-confirm').attr('disabled', 'true');
            return false;
        }
    });

    $('#btn-change-pwd').click(function(){
        var regType = $('#regtype').val(),
            pwd = $("#password").val(),
            rePwd = $("#repwd").val(),
            captcha = $("#captcha").val(),
            codeImg = $('#codeImg').val();

        if(captcha === ''){
            $('#tips-error-captcha').show().html('请输入验证码');
        }
        else if(pwd === ''){
            $('#tips-error-passwd').show().html('请输入6-20位数字、字母或常用符号');
        }
        else if(rePwd === ''){
            showErrTip('repwd', '请重复输入密码');
        }
        else if(rePwd != pwd){
            showErrTip('repwd', '两次密码不一致');
        }
        else{
            $.ajax({
                type: "POST",
                url: "/user/editPasswd",
                dataType: 'json',
                data: "regtype="+regType+"&passwd="+pwd+"&repwd="+rePwd+"&captcha="+captcha+"&codeImg="+codeImg,
                success: function(msg){
                    if(msg.ret == 1){
                        location.href = '/user/editsuccess'
                    }
                    else{
                        $('#tips-error-codeImg').hide();
                        for(var key in msg.msg){
                            if(msg.msg.codeImg && msg.msg.codeImg.msg == '10030'){
                                $('#bk-pic-captcha').show();
                                continue;
                            }
                            $('#tips-error-'+key).show().html(msg.msg[key].msg);
                        }
                    }
                },
                error:function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    });

    var interValObj; //timer变量，控制时间
    var count = 60;  //间隔函数，1秒执行
    var curCount;    //当前剩余秒数

    function setRemainTime() {
        var btnSendCaptcha = $('#btn-send-captcha');
        if(curCount == 0){
            window.clearInterval(interValObj);
            btnSendCaptcha.removeAttr("disabled").val("重新发送");
        }
        else{
            curCount--;
            btnSendCaptcha.attr('disabled', true).val("重新发送(" + curCount + ")");
        }
    }

    $('#btn-send-captcha').click(function(){
        var regType = $("#regtype").val();
        $('#tips-error-captcha').hide();
        curCount = count;
        $(this).attr("disabled", "true").val("重新发送(" + curCount + ")");
        interValObj = window.setInterval(setRemainTime, 1000);

        $.ajax({
            type: "POST",
            url: "/service/sendMsg",
            data: "type="+regType,
            success: function(msg){},
            error:function(){
                showFlashTip('服务器出现异常，请稍候重试');
            }
        });
    });

});