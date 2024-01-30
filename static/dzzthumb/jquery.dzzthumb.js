/*
 * @copyright   QiaoQiaoShiDai Internet Technology(Shanghai)Co.,Ltd
 * @license     https://www.oaooa.com/licenses/
 * 
 * @link        https://www.oaooa.com
 * @author      zyx(zyx@oaooa.com)
 <img src="" data-original="" data-download="">
 */
(function($,document,window) {
	$.fn.dzzthumb = function(options) {
		var defaults = {
				root: 'body',
				selector: 'img[data-original]',
				container: $('body'),
				allowdownload:0  ,//是否允许下载按钮
				callback:null,
				event:'click'
			},
			opts = $.extend(defaults, options),
			angle = 0,
			scale=1,
			$imgs=[],
			current = 0;
			
		$imgs[opts['root']] = $(this);
		$imgs[opts['root']].off(opts['event']+'.'+opts['root']).on(opts['event']+'.'+opts['root'], function() {
			var self = this;
			$imgs[opts['root']].each(function(index) {
				if(this == self) current = index;
			});
			setupDom();
			showContent();
			return false;
		});
		var setupDom = function() {
			$('html,body').addClass('dzzthumb_body');
			if(!document.getElementById('MsgContainer')) {
				$('<div id="MsgContainer" style=" background: url(dzz/images/b.gif); z-index:99999;width:100%;height:100%;margin:0;padding:0; right: 0px; bottom: 0px;position: fixed; top:0px; left: 0px;"></div>').appendTo(opts['container']);
			} else {
				$('#MsgContainer').empty().show();
			}
			$('#MsgContainer').on('contextmenu',function(){
				return false;
			});
			var html = '';
			html += '<div id="preview_Container" style="position:absolute;width:100%;height:100%;top:0px;left:0px;bottom:0px;right:0px;display:none;z-index:10000">';
			html += '<div id="preview-box" class="preview-box">';
			html += '	<div class="preview-handle" style="z-index: 118;"><b data_title="ESC'+__lang.logout+'" btn="close" class="pr-close">ESC'+__lang.logout+'</b></div>';
			html += '	<div id="btn_hand" class="preview-panel" style="z-index: 117;">';
			html += '		<ul id="contents-panel" style="right:55px;" class="contents-panel">';
			html += '			<li btn="rotate"  ><i class="pr-rotate"></i><b>'+__lang.rotation+'</b></li>';
			//html+='			<li class="hidden-xs"  btn="collect" ><i class="pr-save"></i><b>保存到我的文档</b></li>';
			if(opts.allowdownload){
			html += '			<li class="hidden-xs hide"  btn="download" ><i class="pr-download"></i><b>'+__lang.download+'</b></li>';
			html += '			<li btn="newwindow" class="hide" ><i class="pr-newwindow"></i><b>'+__lang.look_artwork+'</b></li>';
			}
			html += '		</ul>';
			html += '		<div id="file_name" class="previewer-filename hidden-xs"></div>';
			html += '	</div>';
			html += '	<div id="con" class="preview-contents">';
			html += '		<div class="pr-btn-switch">';
			html += '			<b data_title="'+__lang.keyboard+'“←”'+__lang.key_on+'" btn="prev" class="pr-btn-prev" style="z-index: 116;" >'+__lang.on_a+'</b>';
			html += '			<b data_title="'+__lang.keyboard+'“→”'+__lang.key_under+'" btn="next" class="pr-btn-next" style="z-index: 116;">'+__lang.under_a+'</b>';
			html += '		</div>';
			html += '		<div id="pre_loading" style="display: none;" class="previewer-loading">'+__lang.loading_in+'</div>';
			html += '		<div id="previewer-photo" class="previewer-photo" style="overflow: visible; z-index: 114; display: none; left: 0px; top: 40px;"></div>';
			html += '	</div>';
			html += '</div>';
			html += '<div id="prev-tips" class="prev-tips" >'+__lang.keyboard+'“←”'+__lang.key_on+'</div>';
			html += '<div id="next-tips" class="next-tips">'+__lang.keyboard+'“→”'+__lang.key_under+'</div>';
			html += '<div id="close-tips" class="esc-tips">ESC'+__lang.logout+'</div>';
			html += '<div id="popup-hint" style="z-index: 999999999; top: 50%; left:50%;margin-left:-86px; display:none;" class="popup-hint">';
			html += '	<i rel="type" class="hint-icon hint-inf-m"></i>';
			html += '	<em class="sl"><b></b></em>';
			html += '	<span rel="con">'+__lang.has_last_picture1+'</span>';
			html += '	<em class="sr"><b></b></em>';
			html += '</div>';
			html += '</div>';
			$(html).appendTo('#MsgContainer');
			$.getScript('static/js/jquery.mousewheel.min.js');
			if($imgs[opts['root']].length<=1){
				$('.pr-btn-prev').hide();
				$('.pr-btn-next').hide();
			}else{
				if(current==0){
					$('.pr-btn-prev').hide();
					$('.pr-btn-next').show();
				}else if(current==$imgs[opts['root']].length-1){
					$('.pr-btn-prev').show();
					$('.pr-btn-next').hide();
				}else{
					$('.pr-btn-prev').show();
					$('.pr-btn-next').show();
				}
			}
			$('#preview_Container').css({ height: '100%', width: '100%' }).show();
			$('#preview-box b').on('mouseenter', function() {
				var btn = $(this).attr('btn');
				$('#' + btn + '-tips').show();
			});
			$('#preview-box b').on('mouseleave', function() {
				var btn = $(this).attr('btn');
				$('#' + btn + '-tips').hide();
			});
			$(document).on('keyup.'+opts['root'], function(event) {
				var e;
				if(event.which != "") { e = event.which; } else if(event.charCode != "") { e = event.charCode; } else if(event.keyCode != "") { e = event.keyCode; }
				switch(e) {
					case 27: //Ctrl + Alt + ←
						$(document).off('.'+opts['root']);
						$('#MsgContainer').empty().hide();
						break;
					case 37: //Ctrl + Alt + ←
						if(current == 0) {
							$('#popup-hint').find('span').html(__lang.has_last_picture);
							$('#popup-hint').show();
							window.setTimeout(function() { $('#popup-hint').hide(); }, 3000);
						}else if(current == 1) {
							current = current - 1;
							$('.pr-btn-prev').hide();
							if($imgs[opts['root']].length>1) $('.pr-btn-next').show();
							showContent();
						} else {
							current = current - 1;
							showContent();
							if($imgs[opts['root']].length>1){
								$('.pr-btn-next').show();
								$('.pr-btn-prev').show();
							} 
							
						}
						break;
					case 39: //Ctrl + Alt + →
						if(current == $imgs[opts['root']].length - 1) {
							$('#popup-hint').find('span').html(__lang.has_last_picture1);
							$('#popup-hint').show();
							
							window.setTimeout(function() { $('#popup-hint').hide(); }, 3000);
						}else if(current == $imgs[opts['root']].length - 2) {
							current = current + 1;
							$('.pr-btn-next').hide();
							if($imgs[opts['root']].length>1) $('.pr-btn-prev').show();
							showContent();
						} else {
							current = current + 1;
							showContent();
							if($imgs[opts['root']].length>1){
								$('.pr-btn-next').show();
								$('.pr-btn-prev').show();
							} 
						}
						break;
				}
			});
			$('#previewer-photo').on('click.'+opts['root'], function() {
				$(document).off('.'+opts['root']);
				$('#MsgContainer').empty().hide();
				$('html,body').removeClass('dzzthumb_body');
			});
			$('#MsgContainer [btn],#previewer-photo').on('click.'+opts['root'], function() {
				var btn = $(this).attr('btn');
				switch(btn) {
					case "close":
						$(document).off('.'+opts['root']);
						$('#MsgContainer').empty().hide();
						$('html,body').removeClass('dzzthumb_body');
						break;
					case "prev":
						if(current == 0) {
							$('#popup-hint').find('span').html(__lang.has_last_picture);
							$('#popup-hint').show();
							window.setTimeout(function() { $('#popup-hint').hide(); }, 3000);
						}else if(current == 1) {
							current = current - 1;
							$('.pr-btn-prev').hide();
							if($imgs[opts['root']].length>1) $('.pr-btn-next').show();
							showContent();
						} else {
							current = current - 1;
							showContent();
							if($imgs[opts['root']].length>1){
								$('.pr-btn-next').show();
								$('.pr-btn-prev').show();
							} 
						}
						jQuery(document).trigger('showIcos_done');
						break;
					case "next":
						if(current == $imgs[opts['root']].length - 1) {
							$('#popup-hint').find('span').html(__lang.has_last_picture1);
							$('#popup-hint').show();
							window.setTimeout(function() { $('#popup-hint').hide(); }, 3000);
						}else if(current == $imgs[opts['root']].length - 2) {
							current = current + 1;
							$('.pr-btn-next').hide();
							if($imgs[opts['root']].length>1) $('.pr-btn-prev').show();
							showContent();
						} else {
							current = current + 1;
							showContent();
							if($imgs[opts['root']].length>1){
								$('.pr-btn-next').show();
								$('.pr-btn-prev').show();
							} 
						}
						
						break;
					case "download":
						var img = $imgs[opts['root']].get(current);
						//var dpath = $(img).data('dpath');
						var url =  $(img).data('download');//downurl + '&path=' + dpath;
						if(!url) break;
						if(!document.getElementById('hideframe')) {
							$('<iframe id="hideframe" name="hideframe" src="about:blank" frameborder="0" marginheight="0" marginwidth="0" width="0" height="0" allowtransparency="true" style="display:none;z-index:-99999"></iframe>').appendTo('body');
						}
						$('#hideframe').attr('src', url);
						break;
					case "newwindow":
						var $img = $($imgs[opts['root']].get(current));
						if($img.data('original')){
							var original_img=$img.data('original');
							window.open(original_img);
						}
						break;

					case "rotate":
						var el = $('#previewer-photo img');
						angle += 90;
						
						var rotation = ((angle % 360) / 90);
						if(scale!=1){
							el.css('transform','rotate('+angle+'deg) scale('+scale.toFixed(1)+')');
							el.css({ 'transform': 'rotate(' + (angle) + 'deg) scale('+scale.toFixed(1)+')', '-webkit-transform': 'rotate(' + (angle) + 'deg) scale('+scale.toFixed(1)+')', '-moz-transform': 'rotate(' + (angle) + 'deg) scale('+scale.toFixed(1)+')', '-o-transform': 'rotate(' + (angle) + 'deg) scale('+scale.toFixed(1)+')', '-ms-transform': 'rotate(' + (angle) + 'deg) scale('+scale.toFixed(1)+')' });
						}else{
							el.css({ 'transform': 'rotate(' + (angle) + 'deg)', '-webkit-transform': 'rotate(' + (angle) + 'deg)', '-moz-transform': 'rotate(' + (angle) + 'deg)', '-o-transform': 'rotate(' + (angle) + 'deg)', '-ms-transform': 'rotate(' + (angle) + 'deg)' });
						}
						
						break;

				}
				return false;
			});
		}
		var showContent = function() {
			var img = $imgs[opts['root']].get(current);
			angle=0;
			$('#file_name').html(img.title);
			$('#popup-hint').hide();
			$('#previewer-photo').empty().hide();

			if(!$(img).data('download')){
				$('#contents-panel li[btn="newwindow"],#contents-panel li[btn="download"]').addClass('hide');
			}else{
				$('#contents-panel li[btn="newwindow"],#contents-panel li[btn="download"]').removeClass('hide');
			}
			$('#pre_loading').show();
			var el = $('#previewer-photo');
			var screenWidth = opts['container'].width();
			var screenHeight = opts['container'].height();
			imgReady($(img).data('original'), function() {
				var width = 0;
				var height = 0;
				var imgw = this.width * 1;
				var imgh = this.height * 1;
				var bodyWidth = screenWidth - 6;
				var bodyHeight = screenHeight - $('#btn_hand').height() - 6;
				var ratio = bodyWidth / bodyHeight;
				var ratio1 = imgw / imgh;
				if(ratio > ratio1) {
					if(bodyHeight < imgh) {
						height = bodyHeight;
						width = imgw / imgh * bodyHeight;
					} else {
						width = imgw;
						height = imgh;
					}
				} else {
					if(bodyWidth < imgw) {
						width = bodyWidth;
						height = imgh / imgw * bodyWidth;
					} else {
						width = imgw;
						height = imgh;
					}
				}
				var left = (screenWidth - width) / 2;
				var top = (bodyHeight - height) / 2;
				var el1 = $('<img height="' + height + '" width="' + width + '" style="cursor: move; top: ' + top + 'px; transform: rotate(0deg); left: ' + left + 'px;" src="' + $(img).data('original') + '" ws_property="1" onload="$(\'#pre_loading\').fadeOut();$(\'#previewer-photo\').show();" >').appendTo(el);
				el1.get(0).onmousedown = function(event) { try {dragMenu(el1.get(0), event, 1); } catch(e) {} };
				el1.on('click', function() { return false });
				
				try{
					$('#previewer-photo').off('mousewheel.preview').on('mousewheel.preview','',function(e,delta, deltaX, deltaY){
						
						pic_resize(delta,imgw,imgh,$(img).data('original'));
						return false;
					});
				}catch(e){};
			});
			
			if(opts.callback)opts.callback(current,img)
			
		};
		var pic_resize=function(delta,imgw,imgh,src){
			if(delta>=0) delta=1;
		    else delta=-1;
			var el = $('#previewer-photo');
			var step=Math.max(imgw/900,imgh/900);
			var $img=el.find('img');
			var width=parseInt($img.attr('width'));
			var height=parseInt($img.attr('height'));
			
			var el=$('#previewer-photo>img');
			var dx=delta*0.5;
			if(dx>1) dx=1;
			if(delta<0){
				scale+=dx;
				if(scale<0.1) scale=0.1
			}else{
				scale+=dx;
			}

			/*if(width*scale>imgw || height*scale>imgh){
				if($img.attr('ismax')!='1'){
					$img.attr('ismax',1).attr('src',src+'&original=1');
				}	
			}*/
			if(angle){
				el.css('transform','rotate('+angle+'deg) scale('+scale.toFixed(1)+')');
			}else{
				el.css('transform',"scale("+scale.toFixed(1)+")");
			}
		}
		var imgReady = (function() {
			var list = [],
				intervalId = null,

				// 用来执行队列
				tick = function() {
					var i = 0;
					for(; i < list.length; i++) {
						list[i].end ? list.splice(i--, 1) : list[i]();
					}
					!list.length && stop();
				},

				// 停止所有定时器队列
				stop = function() {
					clearInterval(intervalId);
					intervalId = null;
				};

			return function(url, ready, load, error) {
				var onready, width, height, newWidth, newHeight,
					img = new Image();

				img.src = url;

				// 如果图片被缓存，则直接返回缓存数据
				if(img.complete) {
					ready.call(img);
					load && load.call(img);
					return;
				}

				width = img.width;
				height = img.height;

				// 加载错误后的事件
				img.onerror = function() {
					error && error.call(img);
					onready.end = true;
					img = img.onload = img.onerror = null;
				};

				// 图片尺寸就绪
				onready = function() {
					newWidth = img.width;
					newHeight = img.height;
					if(newWidth !== width || newHeight !== height ||
						// 如果图片已经在其他地方加载可使用面积检测
						newWidth * newHeight > 1024
					) {
						ready.call(img);
						onready.end = true;
					}
				};
				onready();

				// 完全加载完毕的事件
				img.onload = function() {
					// onload在定时器时间差范围内可能比onready快
					// 这里进行检查并保证onready优先执行
					!onready.end && onready();

					load && load.call(img);

					// IE gif动画会循环执行onload，置空onload即可
					img = img.onload = img.onerror = null;
				};

				// 加入队列中定期执行
				if(!onready.end) {
					list.push(onready);
					// 无论何时只允许出现一个定时器，减少浏览器性能损耗
					if(intervalId === null) intervalId = setInterval(tick, 40);
				}
			};
		})();
		//var btnClick=

	}
})(jQuery,document,window);