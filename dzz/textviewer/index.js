function ScrollBar(opt){
    var me = this;
    me.$wrap = document.getElementById(opt.wrap);
    me.$boxMidle = document.getElementById(opt.boxMidle);
    me.$content =  document.getElementById(opt.content);
    me.$bar = document.getElementById(opt.bar);
    me.init();
    me.$boxMidle.onscroll = function(e){
        //console.log("offsetHeight"+this.offsetHeight); //content + padding + border
        //console.log("clientHeight"+this.clientHeight); // content + padding
        //console.log("scrollHeight"+this.scrollHeight); //内容的高度 + padding
        console.log(this.scrollTop);
        me.scrollToY(this.scrollTop * me.rate)
    }
}
ScrollBar.prototype.init = function(){
    this.$content.style.width = this.$wrap.clientWidth + "px";  //内容的宽度
    this.rate = this.$boxMidle.clientHeight/this.$boxMidle.scrollHeight;  //滚动条高度的比例,也是滚动条top位置的比例
	console.log(this.$boxMidle.clientHeight,this.$boxMidle.scrollHeight)
     this.barHeight = this.rate * this.$boxMidle.clientHeight;  //滚动条的 bar 的高度
    if(this.rate < 1){
        //需要出现滚动条,并计算滚动条的高度
        this.$bar.style.height = this.barHeight + "px";
    }else{
        //不需要出现滚动条
        this.$bar.style.display = "none";
    }
}
ScrollBar.prototype.scrollToY = function(y){
    if(this.rate < 1){
        this.$bar.style.top  = y + 'px';
    }
}
 
var obj = new ScrollBar({"wrap":"wrap","boxMidle":"boxMidle","content":"content","bar":"bar"});