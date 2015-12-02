/**
 * ===========================================
 *          第三方登录绑定用户信息js入口
 * ===========================================
 */
define(function(require){

    var Validator = require('validator');
    var validator = new Validator();

    var showStrength = function(strengthBk){
        $("#pwd-weak").attr("class", "pass-low");
        $("#pwd-strong").attr("class", "pass-low");
        $("#pwd-mid").attr("class", "pass-low");
        $('#pwd-' + strengthBk).attr('class', 'pass-high');
    };

    var showTips = function(show, msg, hide){
        $(show).show().html(msg);
        $(hide).hide();
    };

    var showErrTip = function(field, msg){
        $('#tips-suc-' + field).hide();
        $('#tips-error-' + field).show().html(msg);
    };

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    $('#mobile').blur(function(){
        var mobile = $.trim($(this).val());
        if( mobile ){
            if(validator.isPhone(mobile)){
                $('#tips-error-mobile, #tips-suc-mobile').hide();
                $.ajax({
                    type: "POST",
                    url: "/index/existtel",
                    dataType: 'json',
                    data: "mobile="+mobile,
                    success: function(msg){
                        if(msg.ret == 1){
                            $("#tips-error-mobile").show().html("该号码已存在，您可以使用该手机号<a href='/login' class='c-00'>登录</a>，在云朵账户中绑定微博");
                            $("#mobile").attr('data','');
                        }
                        else{
                            showTips('#tips-suc-mobile', '', '#tip-error-mobile');
                            $("#mobile").attr('data','1');
                        }
                    },
                    error:function(){
                        showFlashTip('服务器出现异常，请稍候重试');
                    }
                });
            }
            else{
                showErrTip('mobile', '手机号码格式错误');
            }
        }
        else{
            showErrTip('mobile', '请输入手机号码');
        }
    });

    $('#captcha').blur(function(){
        var captcha = $.trim($(this).val());
        if(captcha){
            if(validator.isCode(captcha))
                $('#tips-error-captcha, #tips-suc-captcha').hide();
            else
                showErrTip('captcha', '验证码错误');
        }
        else{
            showErrTip('captcha', '请输入验证码');
        }
    });

    $('#password').keyup(function(){
        var pwd = $(this).val();
        var strength = validator.pwdStrength(pwd);

        if( strength == 0 ){
            showStrength('weak');
        }
        else if(strength == 1){
            showStrength('strong');
        }
        else if (strength == 2){
            showStrength('mid');
        }
        else{
            showStrength('weak');
        }

        if( validator.isPassword(pwd) ){
            $('#tips-error-pwd').hide();
        }
    });

    $('#password').blur(function(){
        var pwd = $(this).val();
        if(validator.isPassword(pwd)){
            showTips('#tips-suc-pwd', '', '#tips-error-pwd');
        }
        else{
            showErrTip('pwd', '请输入6-20位数字、字母或常用符号');
        }
    });

    $('#confirmpass').blur(function(){
        var rePwd = $(this).val();
        var pwd   = $("#password").val();

        if(rePwd !=''){
            if(rePwd == pwd){
                showTips('#tips-suc-confirmpass', '', '#tips-error-confirmpass');
            }
            else{
                showErrTip('confirmpass', '两次密码输入不一致');
            }
        }
        else{
            showErrTip('confirmpass', '请重复输入密码');
        }
    });

    $('#confirmpass').keyup(function(){
        $('#tips-error-confirmpass, #tips-suc-confirmpass').hide();
    });

    $('#email').blur(function(){
        var email = $(this).val();
        if(email){
            if(validator.isEmail(email)){
                showTips('#tips-suc-email', '', '#tips-error-email');
            }
            else{
                showErrTip('email', '邮箱格式错误');
            }
        }
        else{
            $('#tips-suc-email, #tips-error-email').hide();
        }
    });

    $('#agree').click(function(){
        var isChecked = $(this).is(':checked');
        isChecked ? $(this).attr('checked', true) : $(this).attr('checked', false);
        if ( isChecked ) {
            $('#reg-btn').removeClass('btn-grey').addClass('btn-blue');
        }
        else {
            $('#reg-btn').removeClass('btn-blue').addClass('btn-grey');
        }
    });

    $('#reg-btn').click(function(){
        var mobile     = $.trim($('#mobile').val());
        var captcha    = $.trim($('#captcha').val());
        var password   = $('#password').val();
        var rePassword = $('#confirmpass').val();
        var regType    = $('#regtype').val();
        var email      = $.trim($('#email').val());
        var pic        = $('#avatar').attr('src');
        var openid     = $('#openid').val();
        var wbuid      = $('#wbuid').val();
        var thirdType      = $('#third_type').val();
        var thirdOpenid      = $('#third_openid').val();
        var codeImg      = $('#codeImg').val();
        var agreeChkBox = $('#agree');
        var agree = agreeChkBox.val();
        var isChecked = agreeChkBox.is(':checked');
        var flag = true;
        isChecked ? agreeChkBox.attr('checked',true) : agreeChkBox.attr('checked',false);

        if(! isChecked) return false;

        if(! mobile){
            $('#tips-error-mobile').show().html('请输入手机号'); flag = false;
        }
        else if(! validator.isPhone(mobile)){
            flag = false;
        }
        if(! password){
            $('#tips-error-passwd').show().html('请输入密码'); flag = false;
        }
        else if(! validator.isPassword(password)){
            flag = false;
        }

        if(! rePassword){
            $('#tips-error-confirmpass').show().html('请重复输入密码');flag = false;
        }
        else if(rePassword != password){
            flag = false;
        }

        if(! captcha){
            $('#tips-error-captcha').show().html('请输入验证码');flag = false;
        }
        else if(! validator.isCode(captcha)){
            flag = false;
        }
        if(email && !validator.isEmail(email)){
            flag = false;
        }

        if(flag && agree == 'on'){
            $.ajax({
                type: "POST",
                url: "/index/adduserinfo",
                dataType: 'json',
                data: "mobile="+mobile+"&captcha="+captcha+"&regtype="+regType+"&passwd="+password+"&confirmpass="+rePassword+"&agree="+agree+"&email="+email+"&pic="+pic+"&openid="+openid+"&wbuid="+wbuid+"&codeImg="+codeImg+"&thirdType="+thirdType+"&thirdOpenid="+thirdOpenid,
                success: function(msg) {
                    if(msg.ret == 1){
                        location.href = '/registersuccess';
                    }
                    else{
                        $('#tips-error-codeImg').hide();
                        for(var key in msg.msg){
                            if(msg.msg.codeImg && msg.msg.codeImg.msg == '10030'){
                                $('#bk-pic-captcha').show();
                                continue;
                            }
                            showErrTip(key, msg.msg[key].msg);
                        }
                    }
                },
                error:function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    });

    var interValObj;   //timer变量，控制时间
    var count = 60;    //间隔函数，1秒执行
    var curCount;      //当前剩余秒数

    var setRemainTime = function(){
        var btnSendCaptcha = $('#btn-send-captcha');
        if (curCount == 0) {
            window.clearInterval(interValObj);
            btnSendCaptcha.removeAttr("disabled").val("重新发送");
        }
        else{
            curCount--;
            btnSendCaptcha.val("重新发送(" + curCount + ")");
        }
    }

    $('#btn-send-captcha').click(function(){
        var mobileInput    = $("#mobile");
        var mobile         = mobileInput.val();
        var mobileExists   = mobileInput.attr('data');
        var regType        = $("#regtype").val();
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
        var data = btnSendCaptcha.attr("disabled");

        if(validator.isPhone(mobile)){
            $.ajax({
                type: "POST",
                url: "/service/sendMsg",
                data: "mobile="+mobile+"&type="+regType,
                success: function(msg){},
                error: function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    });
});