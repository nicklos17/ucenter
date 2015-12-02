/**
 * ==================================
 *          个人中心首页js入口
 * ==================================
 */
define(function(require, exports){

    var fileUpload = require('ajaxfileupload');
    var Validator = require('validator');
    var validator = new Validator();

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };

    var switchBk = function(hideBk, showBk){
        $(hideBk).hide();
        $(showBk).show();
    };

    exports.showFlashTip = showFlashTip;

    $('#btn-set-nick').click(function(){
        switchBk('#tips-error-name, #nick-block', '#bk-set-nick');
    });

    $('#nickname').keyup(function(){
        if(! validator.checkName($(this).val())){
            $('#tips-error-name').show().html('昵称最长为6个汉字或12个字符');
        }
        else{
            $('#tips-error-name').hide();
        }
    });

    $('#confirm-set').click(function(){
        var oldName = $('#old-name').val();
        var nickName = $('#nickname').val();

        if(oldName == '' && nickName == ''){
            switchBk('#bk-set-nick', '#nick-block');
            return false;
        }
        if(nickName == '' || nickName == oldName){
            switchBk('#bk-set-nick', '#nick-block');
            return false;
        }
        if(! validator.checkName(nickName)){
            $('#tips-error-name').show().html('昵称最长为6个汉字或12个字符');
            return false;
        }

        $.ajax({
            type: 'POST',
            url: '/user/editName',
            dataType: 'json',
            data: "name="+nickName,
            success: function(msg) {
                if(msg.ret == 1){
                    switchBk('#bk-set-nick, #bk-no-nick', '#nick-block, #bk-update-nick');
                    $('#show-nick, #top-nick').html(nickName);
                    $('#old-name').val(nickName);
                    showFlashTip('修改昵称成功');
                    //setTimeout(function(){window.location.href = '';}, 5000);
                }
                else{
                    $('#tips-error-name').show().html(msg.msg.name.msg);
                }
            },
            error:function(){
                showFlashTip('服务器出现异常，请稍候重试');
            }
        });
    });

    $('#btn-update-nick').click(function(){
        var nickname = $('#show-nick').text();
        $('#old-name, #nickname').val(nickname);
        switchBk('#nick-block, #tips-error-name', '#bk-set-nick');
    });

    $('#cancel-set').click(function(){
        switchBk('#bk-set-nick, #tips-error-name', '#nick-block');
        $('#nickname').val('');
    });

    $('body').on('click', '#update-email', function(){
        switchBk('#email-block', '#bk-set-email');
        $('#email').val($('#cur-email').html())
    });

    $('#set-email').on('click', function(){
        switchBk('#email-block', '#bk-set-email');
        $('#email').val($('#cur-email').html())
    });

    $('#nickname').blur(function(){
        if(! validator.checkName($(this).val())){

        }
    });

    $('#email-block').on('click', '#resend-email', function(){
        var email = $('#email-hid').val();
        $.ajax({
            type: 'POST',
            url: '/service/sendMail',
            dataType: 'json',
            data: 'email='+email,
            success: function(msg) {
                if(msg.ret == 1){
                    showFlashTip('系统已向您的邮箱发送了验证邮件，请您及时验证。');
                }
            },
            error:function(){
                showFlashTip('服务器出现异常，请稍候重试');
            }
        });
    });

    $('#confirm-set-email').click(function(){
        var email = $('#email').val();
        var $oldEmail = $('#email-hid')

        if(email == $oldEmail.val()){
            switchBk('#bk-set-email', '#email-block');
            return;
        }

        if(validator.isEmail(email)){
            $('#tips-error-email').hide();
            $.ajax({
                type: 'POST',
                url: '/user/editEmail',
                dataType: 'json',
                data: "email="+email,
                success: function(msg){
                    if(msg.ret == '1'){
                        showFlashTip('系统已向您的邮箱发送了验证邮件，请您及时验证。');
                        $oldEmail.val(email);
                        switchBk('#bk-set-email', '#email-block');
                        $('.bk-show-email').show().html( '<span id="cur-email">'+email+'</span><em class="tips-un unset">未验证</em><a id="update-email" class="color" href="javascript:void(0);">更改</a><em class="v-line">|</em><em>没收到邮件？<a class="color tdn" id="resend-email" href="javascript:void(0);">重新发送验证邮件</a></em>');
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
        else{
            $('#tips-error-email').show().html('邮箱格式错误');
        }
    });

    $('#cancel-set-email').click(function(){
        switchBk('#bk-set-email, #tips-error-email', '#email-block');
    });

    $('#ydrank').hover(
        function(){
            $('.ydrank').show();
        },
        function(){
            $('.ydrank').hide();
        }
    );

    $('#yunbi').hover(
        function(){
            $('.yunbi').show();
        },
        function(){
            $('.yunbi').hide();
        }
    );
});