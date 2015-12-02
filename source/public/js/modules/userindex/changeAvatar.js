define(function(require, exports){

    var showFlashTip = function(msg){
        $('#flash-msg').show().html(msg).fadeOut(4000);
    };
});