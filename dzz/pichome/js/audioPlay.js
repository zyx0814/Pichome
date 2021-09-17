

// (function(){
	var isVoice = false;
	// $(document).on('mouseenter','.bgimage-operation',function(){
	// 	$(this).addClass('show');
	// 	var status = $(this).data('status');
	// 	var type = $(this).data('type');
	// 	if(status){
	// 		$(this).data('status',false);
	// 		if(type == 'video'){
	// 			handleVideo($(this));
	// 		}
	// 		if(type == 'audio'){
	// 			handleMusic($(this));
	// 		}
	// 	}
	// });
	
	// $(document).on('mouseleave','.bgimage-operation',function(){
	// 	var self = $(this);
	// 	self.removeClass('show');
	// });

	function handleMouseenter(tage){
		var find = tage.find('.bgimage-operation');
		find.addClass('show');
		var status = find.data('status');
		var type = find.data('type');
		if(status){
			find.data('status',false);
			if(type == 'video'){
				handleVideo(find);
			}
			if(type == 'audio'){
				handleMusic(find);
			}
		}
	};
	function handleMouseleave(tage){
		tage.find('.bgimage-operation').removeClass('show');
	};
	
	
	
	function handleVideo(box){
		var str = {
			loading:false,
			finish:false,
		};
		if(str.loading){
			return false;
		}
		var realpath = box.data('playurl');
		str.loading = true;
		var html_box = jQuery('<div class="movies-box"><div class="html-loading"><div class="el-loading-mask" style=""><div class="el-loading-spinner"><svg viewBox="25 25 50 50" class="circular"><circle cx="50" cy="50" r="20" fill="none" class="path"></circle></svg></div></div></div></div>');
		box.append(html_box);
		var html_video = jQuery('<video src="'+realpath+'" class="video"></video>');
		var html_progress = jQuery('<div class="progress"><div class="progress-bar" style="width: 0%;"></div></div>');
		var html_time = jQuery('<div class="time"></div>');
		var html_voice = jQuery('<div class="circulars voice no"><i class="ri-volume-up-line yes"></i><i class="ri-volume-mute-line no"></i></div>');
		var video = html_video.get(0);
		video.muted = true;
		
		html_box.append(html_video);
		video.addEventListener('canplay', function () { //加载数据
			if(!str.finish){
				html_box.find('.html-loading').remove();
				html_box.append(html_voice);
				html_box.append(html_progress);
				html_box.append(html_time);
				if(video.paused){
					video.play();
				}
				str.finish = true;
			}
		});
		video.addEventListener('timeupdate',function(){
			var timeDisplay = video.currentTime;
			var progre = parseInt(parseInt(timeDisplay)/parseInt(video.duration) * 100, 10);
			box.find('.progress-bar').css('width',progre+'%');
			var time = secondToDate(timeDisplay,video.duration);
			box.find('.time').text(time);
		});
		html_voice.click(function(){
			if(str.finish){
				if(video.muted){
					jQuery(this).addClass('yes').removeClass('no');
					video.muted = false;
				}else{
					jQuery(this).addClass('no').removeClass('yes');
					video.muted = true;
				}	
			}
			return false;
		});
		html_box.bind('mouseenter',function(e){
			if(str.finish){
				video.currentTime = 0;
				video.play();
			}
		});
		var moveStr = {
			time:true,
			isMove:false
		};
		html_box.bind('mousemove',function(e){
			var item = jQuery(this);
			if(moveStr.time){
				moveStr.time = false;
				setTimeout(function(){
					moveStr.isMove = true;
				},600);
			}
			if(video && moveStr.isMove){
				var parentOffset = item.offset();
				var relX = e.clientX - parentOffset.left;
				if(relX > 0 && relX <= item.width()){
					var progre = parseInt(parseInt(relX)/item.width() * 100, 10);
					item.find('.progress-bar').css('width',progre+'%');
					var videoStime = parseInt(progre*(parseInt(video.duration) / 100));
					video.currentTime = videoStime;
					var time = secondToDate(videoStime,video.duration);
					item.find('.time').text(time);
					if(video.paused){
						video.play();
					}
				}
			}
		});
		html_box.bind('mouseleave',function(){
			var item = jQuery(this);
			video.pause();
			moveStr = {
				time:true,
				isMove:false
			};
			item.find('.progress-bar').css('width','0%');
		});
	};
	function handleMusic(box){
		var str = {
			loading:false,
			finish:false,
			fmousemove:0,
			isplay:false,
		};
		if(str.loading){
			return false;
		}
		str.loading = true;
		var html_box = jQuery('<div class="movies-box"><div class="html-loading"><div class="el-loading-mask" style=""><div class="el-loading-spinner"><svg viewBox="25 25 50 50" class="circular"><circle cx="50" cy="50" r="20" fill="none" class="path"></circle></svg></div></div></div></div>');
		var html_time = jQuery('<div class="time"></div>');
		var html_line = jQuery('<div class="line"></div>');
		var html_voice = jQuery('<div class="circulars voice no"><i class="ri-pause-line yes"></i><i class="ri-play-line no"></i></div>');
		box.append(html_box);
		var wavesurfer = WaveSurfer.create({
			container: html_box.get(0),
			waveColor: '#888888',
			progressColor: 'purple',
			hideScrollbar:true,
			height:box.closest('.each-piece').find('.el-image__inner').height(),
		});
		wavesurfer.load(box.data('playurl'));
		wavesurfer.on("ready", function(){
			if(!str.finish){
				html_box.find('.html-loading').remove();
				html_box.append(html_voice);
				html_box.append(html_time);
				html_box.append(html_line);
				var timeDisplay = parseInt(wavesurfer.getCurrentTime());
				var count = parseInt(wavesurfer.getDuration());
				var time = secondToDate(timeDisplay,count);
				html_box.find('.time').text(time);
				if(str.isplay && isVoice){
					wavesurfer.play();
				}
				str.finish = true;
			}
		});
		wavesurfer.on("audioprocess", function(){
			isVoice = true;
			var timeDisplay = parseInt(wavesurfer.getCurrentTime());
			var count = parseInt(wavesurfer.getDuration());
			var time = secondToDate(timeDisplay,count);
			html_box.find('.time').text(time);
		});
		wavesurfer.on("seek", function(){
			if(str.isplay){
				wavesurfer.play();
			}
		});	
		wavesurfer.on("play", function(){
			html_voice.removeClass('no').addClass('yes');
		});	
		wavesurfer.on("pause", function(){
			html_voice.removeClass('yes').addClass('no');
		});
		html_voice.click(function(){
			if(str.isplay && str.finish){
				wavesurfer.playPause();
			}
			
		});
		html_box.bind('mouseenter',function(e){
			str.isplay = true;
			if(str.finish && isVoice){
				var imgHeight =  $(this).closest('.each-piece').find('.el-image__inner').height()
				wavesurfer.setHeight(imgHeight);
				wavesurfer.play(0);
			}
		});
		html_box.bind('mousemove',function(e){
			if(!str.finish){
				return false;
			}
			var clientX = e.clientX;
			var parentOffset = jQuery(this).offset();
			var relX = clientX - (parentOffset.left+1);
			if(relX<=0){
				relX = 0;
			}
			if(relX>=jQuery(this).width()){
				relX = jQuery(this).width();
			}
			str.fmousemove = relX;
			// if(_filemanage.view == 2){
			// 	jQuery(this).find('.line').css('left',parseInt(relX));
			// }else{
				var WaveHeight = jQuery(this).find('wave').height();
				jQuery(this).find('.line').css({
					left:parseInt(relX),
					height:WaveHeight
				});
			// }
		});
		html_box.bind('mouseleave',function(){
			str.isplay = false;
			if(str.finish){
				wavesurfer.pause();
			}
		});
	};
	function secondToDate(result,count){
		var h = Math.floor(result / 3600) < 10 ? '0'+Math.floor(result / 3600) : Math.floor(result / 3600);
		var m = Math.floor((result / 60 % 60)) < 10 ? '0' + Math.floor((result / 60 % 60)) : Math.floor((result / 60 % 60));
		var s = Math.floor((result % 60)) < 10 ? '0' + Math.floor((result % 60)) : Math.floor((result % 60));
		if(count >= 3600){
			return result = h + ":" + m + ":" + s;
		}else if(count >= 60){
			return result = m + ":" + s;
		}else{
			return result = s;
		}
	};
// })();