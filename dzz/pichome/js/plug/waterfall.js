
function waterfall(){
	this.imgWidth = 252;
	this.box = $('#waterfallCenter');
	this.imgArr = [];
	this.total=0;
}
waterfall.prototype.init = function(type){
	if(type == 'append'){
		this.ImgCompute(type);
	}else if(type == 'refresh'){
		this.imgArr = [];
		this.total=0;
		this.ImgCompute();
	}else{
		this.ImgCompute();
		this.handleReasize()
	}
}
waterfall.prototype.ImgCompute = function(type){
	var self = this;
	var boxHeight = {
		index:0,
		top:0
	};
	var columns = self.box.width()/self.imgWidth;
	$('#waterfallCenter .imgmargin').css('width',columns*self.imgWidth+'px');

	
	if(type == 'append'){
		var block = $('.w-block:eq('+this.total+')').nextAll();
	}else{
		var block = $('.w-block');
	}
	
	block.each(function(findex){
		
		$(this).css('width',self.imgWidth);
		var offsetHeight = $(this).find('.w-storage').height()+$(this).find('.bottom-img-message').height()+15;
		
		if (type != 'append' && findex < columns) {
			$(this).css({
				top:0,
				left:self.imgWidth*findex
			});
			boxHeight.index = findex;
			self.imgArr.push(this.offsetHeight);
		}else{
			var minHeight = self.imgArr[0];
			var index = 0;
			for (var j = 0; j < self.imgArr.length; j++) {
				if (minHeight > self.imgArr[j]) {
					minHeight = self.imgArr[j];
					index = j;
				}
			}
			$(this).css({
				top:self.imgArr[index],
				left:$('.w-block:eq('+index+')').css('left')
			});		
			if(self.imgArr[index]>boxHeight.top){
				boxHeight.index = findex;
				boxHeight.top = self.imgArr[index]
			}
			// 最小列的高度 = 当前自己的高度 + 拼接过来的高度 + 间隙的高度
			self.imgArr[index] = self.imgArr[index] + this.offsetHeight;
		}
		
	});
	self.total = $('.w-block').length-1;
	$('#waterfallCenter').css({
		height:$('.w-block:eq('+boxHeight.index+')').height()+boxHeight.top+30
	});
};
waterfall.prototype.handleReasize = function(){
	var self = this;
	var rdate;//用于记录每次触发resize的时间
	var timer = null;
	var delta = 200;//200ms时间差
	$(window).resize(function() {
	    rdate = new Date();
	    if (timer === null) {
	        timer = setTimeout(resizeEnd, delta);
	    }
	});
	function resizeEnd() {
	    //再等等~
	    if (new Date() - rdate < delta) {
	        setTimeout(resizeEnd, delta);
	    } else {
	        timer = null;
	        //trigger 一个resizeend事件
	        $(window).trigger("resizeend");
	    }               
	}
	//监听resizeend 
	// $(window).on("resizeend",function (e) {  
	// 	console.log(7777777)
	// 	self.imgArr = [];
	//     self.ImgCompute();
	// });

};
