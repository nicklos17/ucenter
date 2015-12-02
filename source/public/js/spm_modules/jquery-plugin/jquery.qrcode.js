(function( $ ){
	$.fn.qrcode = function(options) {
		var canvas	= document.getElementById("Canvas_" + options.idx);
		var isHtml5 = false;
		if (canvas.getContext) {
			isHtml5 = true;
		}
		// if options is string, 
		if( typeof options === 'string' ){
			options	= { text: options };
		}

		// set default values
		// typeNumber < 1 for automatic calculation
		options	= $.extend( {}, {
			render		: "canvas",
			width		: 256,
			height		: 256,
			typeNumber	: -1,
			correctLevel: QRErrorCorrectLevel.H,
			background : "#ffffff",
			foreground : "#000000"
		}, options);

		var set_logoImg = function(width,height,qrcode){
			var imgElement = $("#logoImg").attr("src");
			if(!imgElement){
				//create the img logo
				var img = $(document.createElement("IMG"))
					   .attr("src", options.src)
					   .css(
					   		{
								"position" : "absolute",
					   			"z-Index" : 1000,
					   			"width" : width * 7 +"px",
					   			"height" : height * 7 + "px",
					   			"left" : ((qrcode.getModuleCount()-1)/2-3)*width+"px",
					   			"top": ((qrcode.getModuleCount()-1)/2-3)*height+"px"
					   		}
					   	).appendTo($('.qr-code'));
			}
		}
		var createCanvas	= function(){
			// create the qrcode itself
			var qrcode	= new QRCode(options.typeNumber, options.correctLevel);
			qrcode.addData(options.text);
			qrcode.make();

			// get canvas element
			//var canvas	= $("Canvas").get(0);
			canvas.width	= options.width;
			canvas.height	= options.height;
			var ctx = canvas.getContext('2d');

			// compute tileW/tileH based on options.width/options.height
			var tileW	= options.width  / qrcode.getModuleCount();
			var tileH	= options.height / qrcode.getModuleCount();

			// draw in the canvas
			for( var row = 0; row < qrcode.getModuleCount(); row++ ){
				for( var col = 0; col < qrcode.getModuleCount(); col++ ){
					ctx.fillStyle = qrcode.isDark(row, col) ? options.foreground : options.background;
					var w = (Math.ceil((col+1)*tileW) - Math.floor(col*tileW));
					var h = (Math.ceil((row+1)*tileW) - Math.floor(row*tileW));
					ctx.fillRect(Math.round(col*tileW),Math.round(row*tileH), w, h);  
				}	
			}
			//set logo
			set_logoImg(tileW,tileH,qrcode);
			// return just built canvas
			return canvas;
		}

		// from Jon-Carlos Rivera (https://github.com/imbcmdth)
		var createTable	= function(){
			// create the qrcode itself
			var qrcode	= new QRCode(options.typeNumber, options.correctLevel);
			qrcode.addData(options.text);
			qrcode.make();
			var $table;
			var tableTemp = $("#contentInfo").html();
			if(!tableTemp){
					// create table element
					$table = $('<table></table>')
					.css("width", options.width+"px")
					.css("height", options.height+"px")
					.css("border", "0px")
					.css("border-collapse", "collapse")
					.css('background-color', options.background)
					.attr('id',"contentInfo");
			}else{
				$("#contentInfo").html("");
				$table = $("#contentInfo");
			}
		  
			// compute tileS percentage
			var tileW	= options.width / qrcode.getModuleCount();
			var tileH	= options.height / qrcode.getModuleCount();

			// draw in the table
			for(var row = 0; row < qrcode.getModuleCount(); row++ ){
				var $row = $('<tr></tr>').css('height', tileH+"px").appendTo($table);
				
				for(var col = 0; col < qrcode.getModuleCount(); col++ ){
					$('<td></td>')
						.css('width', tileW+"px")
						.css('background-color', qrcode.isDark(row, col) ? options.foreground : options.background)
						.appendTo($row);
				}
			}
			//set logo
			set_logoImg(tileW,tileH,qrcode);
			// return just built canvas
			return $table;
		}
  

		return this.each(function(){
			var element	=  isHtml5 ? createCanvas() : createTable();
			$(element).appendTo(this);
		});
	};
})( jQuery );
