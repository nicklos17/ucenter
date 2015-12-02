// define(function(require, exports){
//     var oldName = $("#edit-input").val();

//     var myCrop = {
//     //裁剪框为一正方形, arealen为其变长
//     arealen: 100,
//     //四个方向的边界值, 分别是左下右上
//     bound: {
//       1: 0,
//       2: this.arealen,
//       3: this.arealen,
//       4: 0
//     },
//     //初始化裁剪,绑定一些鼠标事件
//   cropInit: function (len, cropEle) {
//     function isNextBou (e, cropper) {
//       var scaleArea = 20;
//       var flag = 0;
//       //alert($("#" + cropEle).css("margin-left"));
//       if (e.pageX - parseInt($("#" + cropEle).css("margin-left")) <= scaleArea && e.pageX - parseInt($("#" + cropEle).css("margin-left")) >= 0) {
//         flag = 1;
//       } 
//       else if (parseInt($("#" + cropEle).css("margin-left")) + cropper.arealen - e.pageX <= scaleArea && parseInt($("#" + cropEle).css("margin-left")) + cropper.arealen - e.pageX >= 0)
//       {
//         flag = 3;
//       }
//       else if (e.pageY - parseInt($("#" + cropEle).css("margin-top")) <= scaleArea && e.pageY - parseInt($("#" + cropEle).css("margin-top")) >= 0)
//       {
//         flag = 4;
//       }
//       else if (parseInt($("#" + cropEle).css("margin-top")) + cropper.arealen - e.pageY <= scaleArea && parseInt($("#" + cropEle).css("margin-top")) + cropper.arealen - e.pageY >= 0)
//       {
//         flag = 2;
//       }
//       return flag;
//   }

//   function reachBou (cropper, chalenX, chalenY) {
//         var len;
//         len = parseInt($("#" + cropEle).css("margin-left")) + chalenX;
//         if (0 > len) {
//           return 1;
//         }
//         len = parseInt($("#cropImg").css("height")) - (parseInt($("#" + cropEle).css("margin-top")) + cropper.arealen + chalenY);
//         if (0 > len) {
//           return 1;
//         }
//         len = parseInt($("#cropImg").css("width")) - (parseInt($("#" + cropEle).css("margin-left")) + cropper.arealen + chalenX);
//         if (0 > len) {
//           return 1;
//         }
//         len = parseInt($("#" + cropEle).css("margin-top")) + chalenY;
//         if (0 > len) {
//           return 1;
//         }
//         return 0;
//   }
//     $("#" + cropEle).css({"margin-left": 0, "margin-top": 0});
//     if (this.arealen > len) {
//       this.arealen=len;
//       $("#" + cropEle).css({width: this.arealen, height: this.arealen});
//       this.bound[2]=this.arealen;
//       this.bound[3]=this.arealen;
//     }
//     var _move=false;//拖拽标记  
//     var pageXOld, pageYOld;
//     var chalen;
//     $("#" + cropEle).bind("touchstart", function(e){
//         //alert("ok");
//         _move=true;
//         pageXOld = e.pageX;
//         pageYOld = e.pageY;
//     });
//     var cropper = this;
//     $(document).bind("touchmove", function(e){
//       var flag;
//       if (_move) {
//         if (flag = isNextBou(e, cropper)) {
//           switch (flag) {
//             case 1:
//               chalen = pageXOld - e.pageX;
//               if (reachBou(cropper, chalen, chalen)) {
//                 return;
//               }
//               cropper.arealen = parseInt($("#" + cropEle).css("width")) + chalen;
//               $("#" + cropEle).css({width: cropper.arealen, height: cropper.arealen});
//               $("#" + cropEle).css({"margin-left": (parseInt($("#" + cropEle).css("margin-left")) - chalen), "margin-top": (parseInt($("#" + cropEle).css("margin-top")) - chalen)});
//               $("#" + cropEle).css({"background-position": -(parseInt($("#" + cropEle).css("margin-left")) - chalen) + "px " + -(parseInt($("#" + cropEle).css("margin-top")) - chalen) + "px"});
//               pageXOld = e.pageX;
//               pageYOld = e.pageY;
//               break;
//             case 2:
//               chalen = e.pageY - pageYOld;
//               if (reachBou(cropper, chalen, chalen)) {
//                 return;
//               }
//               cropper.arealen = parseInt($("#" + cropEle).css("width")) + chalen;
//               $("#" + cropEle).css({width: cropper.arealen, height: cropper.arealen});
//               //$("#" + cropEle).css({"margin-left": (parseInt($("#" + cropEle).css("margin-left")) - chalen), "margin-top": (parseInt($("#" + cropEle).css("margin-top")) - chalen)});
//               pageXOld = e.pageX;
//               pageYOld = e.pageY;
//               break;
//             case 3:
//               chalen = e.pageX - pageXOld;
//               if (reachBou(cropper, chalen, chalen)) {
//                 return;
//               }
//               cropper.arealen = parseInt($("#" + cropEle).css("width")) + chalen;
//               $("#" + cropEle).css({width: cropper.arealen, height: cropper.arealen});
//               //$("#" + cropEle).css({"margin-left": (parseInt($("#" + cropEle).css("margin-left")) - chalen), "margin-top": (parseInt($("#" + cropEle).css("margin-top")) - chalen)});
//               pageXOld = e.pageX;
//               pageYOld = e.pageY;
//               break;
//             case 4:
//               chalen = pageYOld - e.pageY;
//               if (reachBou(cropper, chalen, chalen)) {
//                 return;
//               }
//               cropper.arealen = parseInt($("#" + cropEle).css("width")) + chalen;
//               $("#" + cropEle).css({width: cropper.arealen, height: cropper.arealen});
//               $("#" + cropEle).css({"margin-left": (parseInt($("#" + cropEle).css("margin-left")) - chalen), "margin-top": (parseInt($("#" + cropEle).css("margin-top")) - chalen)});
//               $("#" + cropEle).css({"background-position": -(parseInt($("#" + cropEle).css("margin-left")) - chalen) + "px " + -(parseInt($("#" + cropEle).css("margin-top")) - chalen) + "px"});
//               pageXOld = e.pageX;
//               pageYOld = e.pageY;
//               break;
//           }
//       } else {
//             var x=e.pageX-pageXOld; 
//             var y=e.pageY-pageYOld;
//             if (reachBou(cropper, x, y)) {
//               return;
//             }
//             x += parseInt($("#" + cropEle).css("margin-left"));
//             y += parseInt($("#" + cropEle).css("margin-top"));
//             $("#" + cropEle).css({"margin-top":y,"margin-left":x});
//             //alert(y);
//             $("#" + cropEle).css({"background-position": -x + "px " + -y + "px"});
//             pageXOld = e.pageX;
//             pageYOld = e.pageY;
//       }
//     }
//     }).bind("touchend", function(){
//       _move=false;
//       //dump(cropper)
//       cropper.bound[1] = parseInt($("#" + cropEle).css("margin-left"));
//       cropper.bound[4] = parseInt($("#" + cropEle).css("margin-top"));
//       cropper.bound[3] = cropper.bound[1] + cropper.arealen;
//       cropper.bound[2] = cropper.bound[4] + cropper.arealen;
//     });
//   }
// };

// exports.myCrop = myCrop;

// // function debug () {
// //     alert(myCrop.x1 + " " + myCrop.y1 + " " + myCrop.x2 + " " + myCrop.y2 + " " + myCrop.arealen);
// // }
// });