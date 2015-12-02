/**
 * =======================================
 *          [修改密码-注册]成功js入口
 * =======================================
 */
define(function(require){

    var interValObj;   //timer变量，控制时间
    var count = 5;    //间隔函数，1秒执行
    var curCount;      //当前剩余秒数
    var timer = $('#seconds-remain');

    var setRemainTime = function(){

        if(curCount == 0){
            window.clearInterval(interValObj);
            location.href = 'http://www.yunduo.com/';
        }
        else{
            curCount--;
            timer.html(curCount);
        }
    }

    curCount = count;
    interValObj = window.setInterval(setRemainTime, 1000); //启动计时器，1秒执行一次

});