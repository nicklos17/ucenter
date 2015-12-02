define(function(require, exports, module){

    function Validator(container) {}

    module.exports = Validator;

    Validator.prototype._init = function() {}

    //手机格式验证
    Validator.prototype.isPhone = function(phone){

        var phonePattern=/^0?(13[0-9]|15[0-9]|18[0-9]|14[0-9])[0-9]{8}$/;

        if(! phonePattern.exec(phone)){
            return false ;
        }
        else{
            return true ;
        }
    }

    //邮箱格式验证
    Validator.prototype.isEmail = function(email){

        var reg = /^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+((.[a-zA-Z0-9_-]{2,3}){1,2})$/;
        return reg.test(email);
    }

    //验证码格式验证
    Validator.prototype.isCode = function(captcha){

        if(captcha.length == 4 && this.checkNum(captcha)){
            return true;
        }
        else{
            return false;
        }
    }

    //验证是否为数字
    Validator.prototype.checkNum = function(num){

        return num.match(/\D/) == null;
    }

    //密码格式验证
    Validator.prototype.isPassword = function(pwd){

        if(pwd.length > 5 && pwd.length <= 20){
            return true;
        }
        else{
            return false;
        }
    }

    //密码强度验证
    Validator.prototype.pwdStrength = function(pwd){

        var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
        var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        var enoughRegex = new RegExp("(?=.{6,}).*", "g");

        if(false == enoughRegex.test(pwd)){
            //密码小于六位的时候，密码强度图片都为灰色
            return 0;
        }
        else if(strongRegex.test(pwd)){
            //强,密码为八位及以上并且字母数字特殊字符三项都包括
            return 1;
        }
        else if(mediumRegex.test(pwd)){
            //中等,密码为七位及以上并且字母、数字、特殊字符三项中有两项，强度是中等
            return 2;
        }
        else{
            //弱,如果密码为6为及以下，就算字母、数字、特殊字符三项都包括，强度也是弱的
            return 3;
        }
    }

    //昵称格式验证
    Validator.prototype.checkName = function(name){

        if(this.strLen(name) >= 1 && this.strLen(name) <= 12){

            return true;
        }
        else{

            return false;
        }
    }

    //获取字符床长度
    Validator.prototype.strLen = function(str){

        var len = 0;
        for(var i = 0; i < str.length; i++){
            var c = str.charCodeAt(i);
            //单字节加1
            if((c >= 0x0001 && c <= 0x007e) || (0xff60 <= c && c <= 0xff9f))
                len++;
            else
                len += 2;
        }
        return len;
    }

});
