define(function(require, exports){

    exports.errReturn = function(url){

        var errInterValObj;
        var errCount = 5;
        var errCurCount;
        var errTimer = $('#seconds-remain');

        var errorRemainTime = function(){

            if(errCurCount == 0){
                window.clearInterval(errInterValObj);
                location.href = url;
            }
            else{
                errCurCount--;
                errTimer.html(errCurCount);
            }
        }

        errCurCount = errCount;
        errInterValObj = window.setInterval(errorRemainTime, 1000);
    }
});