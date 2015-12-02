/**
 * ==============================
 *          更改密码js入口
 * ==============================
 */
define(function(require, exports){

    var Validator = require('validator');
    var validator = new Validator();

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    var showStrength = function(strengthBk){
        $("#pwd-weak").attr("class", "pass-low");
        $("#pwd-strong").attr("class", "pass-low");
        $("#pwd-mid").attr("class", "pass-low");
        $('#pwd-' + strengthBk).attr('class', 'pass-high');
    };

    var showErrTip = function(field, msg){
        $('#tips-suc-' + field).hide();
        $('#tips-error-' + field).show().html(msg);
    };

    $('#mobile').blur(function(){
        var mobile = $.trim($(this).val());
        if(mobile){
            if(validator.isPhone(mobile)){
                $('#tips-error-mobile, #tips-suc-mobile').hide();
                $.ajax({
                    type: "POST",
                    url: "/index/existtel",
                    data: "mobile="+mobile,
                    dataType: 'json',
                    success: function(msg) {
                        if(msg.ret == 1){
                            $("#mobile").attr('data', '1');
                            $('#tips-error-mobile').hide();
                        }
                        else{
                            $("#mobile").attr('data', '0');
                            showErrTip('mobile', '手机号尚未注册');
                        }
                    },
                    error:function(){
                        showFlashTip('服务器出现异常，请稍候重试');
                    }
                });
            }
            else{
                $("#mobile").attr('data','false');
                showErrTip('mobile', '手机号码格式错误');
            }
        }
        else{
            $("#mobile").attr('data','false');
            showErrTip('mobile', '请输入手机号码');
        }
    });

    $("#captcha, #mobile").keyup(function(){
        var captcha = $("#captcha").val();
        var mobile  = $("#mobile").val();
        if(validator.isCode(captcha) && validator.isPhone(mobile)){
            $('#btn-next').addClass('btn-blue');
        }
        else{
            $('#btn-next').removeClass('btn-blue');
        }
    });

    $("#password, #re-password").keyup( function () {
        var pwd = $("#password").val();
        var rePwd = $("#re-password").val();

        if(rePwd != '' && pwd != ''){
            if(rePwd == pwd){
                $('#tips-error-repwd').hide();
                $('#confirm').addClass('btn-blue').removeClass('btn-grey').removeAttr('disabled');
            }
            else{
                $('#confirm').removeClass('btn-blue').addClass('btn-grey');
                return false;
            }
        }
    });

    $('#confirm').click(function(){
        var pwd = $("#password").val();
        var rePwd = $("#re-password").val();
        var codeImg = $("#codeImg").val();

        if(! validator.isPassword(pwd)){
            $('#tips-error-pwd').show().html("请输入6-20位数字、字母或常用符号");
            return false;
        }
        if(rePwd === ''){
            $('#tips-error-repwd').show().html('请重复输入密码');
            return false;
        }
        if(rePwd != pwd){
            $('#tips-error-repwd').show().html('两次密码输入不一致');
            return false;
        }
        if(pwd != '' && rePwd != '' && pwd == rePwd){
            $.ajax({
                type: "POST",
                url: "/reset/setPwd",
                dataType: 'json',
                data: 'passwd='+pwd+'&repwd='+pwd+"&codeImg="+codeImg,
                success: function(msg) {
                    if(msg.ret == 1){
                        location.href="/reset/success";
                    }
                    else{
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

    $('#captcha').blur(function(){
        var captcha = $.trim($(this).val());
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

    $('#btn-next').click(function(){
        $('#tips-error-codeImg').hide();
        var mobile = $.trim($("#mobile").val());
        var captcha = $.trim($("#captcha").val());
        var regType = $("#regType").val();
        var codeImg = $("#codeImg").val();

        if(mobile === ''){
            $('#tips-error-mobile').show().html('请输入手机号码');
        }
        if(captcha === ''){
            $('#tips-error-captcha').show().html('请输入验证码');
        }
        if(validator.isPhone(mobile) && captcha !='' && validator.isCode(captcha)){
            $.ajax({
                type: "POST",
                url: "/reset/checkcap",
                dataType: 'json',
                data: "mobile="+mobile+"&captcha="+captcha+'&regtype='+regType+"&codeImg="+codeImg,
                success: function(msg) {
                    if(msg.ret == 1){
                        location.href="/reset/pass";
                    }
                    else{
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

    $('#password').keyup(function(){
        var pwd = $(this).val();
        var strength = validator.pwdStrength(pwd);

        if(strength == 0){
            showStrength('weak');
        }
        else if(strength == 1){
            showStrength('strong');
        }
        else if(strength == 2 ){
            showStrength('mid');
        }
        else{
            showStrength('weak');
        }

        if(validator.isPassword(pwd)){
            $('#tips-error-pwd').hide();
        }
    });

    $('#password').blur(function(){
        var pwd = $(this).val();
        if(validator.isPassword(pwd)){
            $('#tips-error-pwd').hide();
        }
        else{
            $('#tips-error-pwd').show().html("请输入6-20位数字、字母或常用符号");
        }
    });

    $('#re-password').blur(function(){
        var rePwd = $(this).val();
        var pwd   = $("#password").val();

        if(rePwd !=''){
            if(rePwd == pwd){
                $('#tips-error-re-pwd').hide();
                $('#tips-suc-re-pwd').show();
            }
            else{
                showErrTip('re-pwd', '两次密码输入不一致');
            }
        }
        else{
            showErrTip('re-pwd', '请重复输入密码');
        }
    });

    $('#re-password').keyup(function(){
        $('#tips-error-re-pwd, #tips-suc-re-pwd').hide();
    });

    var interValObj;   //timer变量，控制时间
    var count = 60;    //间隔函数，1秒执行
    var curCount;      //当前剩余秒数

    var setRemainTime = function(){
        var btnSendCaptcha = $('#btn-send-captcha');
        if(curCount == 0){
            btnSendCaptcha.val("重新发送").removeClass("disabled").removeAttr("disabled");
            window.clearInterval(interValObj);
        }
        else{
            curCount--;
            btnSendCaptcha.val("重新发送(" + curCount + ")");
        }
    };

    $('#btn-send-captcha').click(function(){
        var mobileInput    = $("#mobile");
        var regType        = $('#regType').val();
        var mobile         = mobileInput.val();
        var mobileExists   = mobileInput.attr('data');
        var btnSendCaptcha = $('#btn-send-captcha');

        if(mobileExists != '1') return false;

        if(! validator.isPhone(mobile)){
            $('#tips-error-mobile').show().html('手机号格式错误');
            return false;
        }
        else{
            $('#tips-error-mobile').hide();
        }

        curCount = count;

        btnSendCaptcha.attr("disabled", "true").val("重新发送(" + curCount + ")").addClass("disabled");
        interValObj = window.setInterval(setRemainTime, 1000); //启动计时器，1秒执行一次

        if(validator.isPhone(mobile)){
            $.ajax({
                type: "POST",
                url: "/service/sendMsg",
                data: "mobile="+mobile+"&type="+regType,
                success: function(msg){ },
                error:function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    });

    exports.successReturn = function(){
        var sucInterValObj;
        var sucCount = 5;
        var sucCurCount;
        var sucTimer = $('#seconds-remain');

        var sucRemainTime = function(){
            if(sucCurCount == 0){
                window.clearInterval(sucInterValObj);
                location.href = '/user/index';
            }
            else{
                sucCurCount--;
                sucTimer.html(sucCurCount);
            }
        }

        sucCurCount = sucCount;
        sucInterValObj = window.setInterval(sucRemainTime, 1000);
    }
});
