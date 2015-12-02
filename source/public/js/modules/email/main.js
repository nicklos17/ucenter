/**
 * ===============================
 *          更改邮箱js入口
 * ===============================
 */
define(function(require){

    var Validator = require('validator');
    var validator = new Validator();

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    $('#email-next').click(function(){
        var captcha = $.trim($('#captcha').val());
        var regType = $("#regtype").val();
        var codeImg = $("#codeImg").val();
        $('#tips-error-captcha').hide();
        if(captcha === ''){
            $('#tips-error-captcha').show().html('请输入验证码');
            return false;
        }
        else if(! validator.isCode(captcha)){
            $('#tips-error-captcha').show().html('验证码错误');
            return false;
        }
        else{
            $.ajax({
                type: "POST",
                url: "/email/checkcap",
                dataType: 'json',
                data: "captcha="+captcha+"&regtype="+regType+"&codeImg="+codeImg,
                success: function(msg){
                    if(msg.ret == 1){
                        location.href = '/email/change';
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

    $('#email-confirm').click(function(){
        var email = $.trim($('#email').val());
        if(email === ''){
            $('#tips-error-email').show().html('请输入邮箱');
        }
        else if(! validator.isEmail(email)){
            $('#tips-error-email').show().html('邮箱格式错误');
        }
        else{
            $.ajax({
                type: "POST",
                url: "/email/resetEmail",
                data: "email="+email,
                dataType: 'json',
                success: function(msg){
                    if(msg.ret == '1'){
                        location.href = '/email/success'
                    }
                    else{
                        $('#tips-error-email').show().html(msg.msg.email.msg);
                    }
                },
                error:function(){
                    showFlashTip('服务器出现异常，请稍候重试');
                }
            });
        }
    });

    var interValObj, //timer变量，控制时间
        count = 60,  //间隔函数，1秒执行
        curCount;    //当前剩余秒数

    function setRemainTime(){
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
            dataType: 'json',
            success: function(msg){
                if( msg.ret != 1){}
            },
            error:function(){
                showFlashTip('服务器出现异常，请稍候重试');
            }
        });
    });

});