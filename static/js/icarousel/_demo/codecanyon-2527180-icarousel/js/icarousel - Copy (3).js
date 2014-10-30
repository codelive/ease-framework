/**
 * jQuery iCarousel v1.1
 * 
 * @version: 1.1 - June 15, 2012
 * @version: 1.0 - May 25, 2012
 * 
 * @author: Hemn Chawroka
 *		  chavroka@yahoo.com
 *		  http://hemn.soloset.net/
 * 
 */
(function($) {

	var iCarouselStarter = function(el, slides, options) {

		function disableSelection(target) {
			if (typeof target.onselectstart != "undefined") target.onselectstart = function() {
				return false;
			};
			else if (typeof target.style.MozUserSelect != "undefined") target.style.MozUserSelect = "none";
			else target.onmousedown = function() {
				return false;
			};
			target.style.cursor = "default";
		}

		//Image Preloader Function


		function ImagePreload(p_aImages, p_pfnPercent, p_pfnFinished) {
			this.m_pfnPercent = p_pfnPercent;
			this.m_pfnFinished = p_pfnFinished;
			this.m_nLoaded = 0;
			this.m_nProcessed = 0;
			this.m_aImages = new Array;
			this.m_nICount = p_aImages.length;
			for (var i = 0; i < p_aImages.length; i++) this.Preload(p_aImages[i])
		}
		ImagePreload.prototype.Preload = function(p_oImage) {
			var oImage = new Image;
			this.m_aImages.push(oImage);
			oImage.onload = ImagePreload.prototype.OnLoad;
			oImage.onerror = ImagePreload.prototype.OnError;
			oImage.onabort = ImagePreload.prototype.OnAbort;
			oImage.oImagePreload = this;
			oImage.bLoaded = false;
			oImage.source = p_oImage;
			oImage.src = p_oImage
		}
		ImagePreload.prototype.OnComplete = function() {
			this.m_nProcessed++;
			if (this.m_nProcessed == this.m_nICount) this.m_pfnFinished();
			else this.m_pfnPercent(Math.round((this.m_nProcessed / this.m_nICount) * 10))
		}
		ImagePreload.prototype.OnLoad = function() {
			this.bLoaded = true;
			this.oImagePreload.m_nLoaded++;
			this.oImagePreload.OnComplete()
		}
		ImagePreload.prototype.OnError = function() {
			this.bError = true;
			this.oImagePreload.OnComplete()
		}
		ImagePreload.prototype.OnAbort = function() {
			this.bAbort = true;
			this.oImagePreload.OnComplete()
		}

		//Necessary variables
		var defs = {
			degree: 0,
			total: slides.length,
			images: [],
			interval: null,
			timer: options.timer.toLowerCase(),
			dir: options.direction.toLowerCase(),
			pause: false,
			slide: 0,
			currentSlide: null,
			width: el.width(),
			height: el.height(),
			space: options.slidesSpace,
			topSpace: options.slidesTopSpace,
			lock: false,
			easing: 'ease-in-out',
			time: options.pauseTime
		};

		//Disable text selection
		disableSelection(el[0]);

		slides.each(function(i) {
			var slide = $(this);
			slide.attr({
				'data-outerwidth': slide.outerWidth(),
				'data-outerheight': slide.outerHeight(),
				'data-width': slide.width(),
				'data-height': slide.height(),
				'index': i
			}).css({
				visibility: 'hidden'
			});
		});

		//Find images
		var images = $('img', el);
		images.each(function(i) {
			var image = $(this);
			defs.images.push(image.attr("src"));
		});

		//If randomStart
		options.startSlide = (options.randomStart) ? Math.floor(Math.random() * defs.total) : options.startSlide;

		//Set startSlide
		options.startSlide = (options.startSlide < 0 || options.startSlide > defs.total) ? 0 : options.startSlide;
		defs.slide = options.startSlide;

		//Set initial currentSlide
		defs.currentSlide = slides.eq(defs.slide);

		//Set initial pauseTime
		defs.time = (defs.currentSlide.data('pausetime')) ? defs.currentSlide.data('pausetime') : options.pauseTime;

		//Fix slides number
		options.slides = (options.slides > defs.total) ? defs.total : options.slides;
		options.slides = (options.slides % 2) ? options.slides : options.slides - 1;

		//Set Preloader Element
		el.append('<div id="iCarousel-preloader"><div></div></div>');
		var iCarouselPreloader = $('#iCarousel-preloader', el);
		var preloaderBar = $('div', iCarouselPreloader);
		iCarouselPreloader.css({
			top: ((defs.height / 2) - (iCarouselPreloader.height() / 2)) + 'px',
			left: ((defs.width / 2) - (iCarouselPreloader.width() / 2)) + 'px'
		});

		//Set Timer Element
		el.append('<div id="iCarousel-timer"><div></div></div>');
		var iCarouselTimer = $('#iCarousel-timer', el);
		iCarouselTimer.hide();
		var barTimer = $('div', iCarouselTimer);

		var padding = options.timerPadding,
			diameter = options.timerDiameter,
			stroke = options.timerStroke;

		if (options.autoPlay && defs.total > 1 && defs.timer != "bar") {
			//Start the Raphael
			stroke = (defs.timer == "360bar") ? options.timerStroke : 0;
			var width = (diameter + (padding * 2) + (stroke * 2)),
				height = width,
				r = Raphael(iCarouselTimer[0], width, height),
				R = (diameter / 2),
				param = {
					stroke: options.timerBg,
					"stroke-width": (stroke + (padding * 2))
				},
				param2 = {
					stroke: options.timerColor,
					"stroke-width": stroke,
					"stroke-linecap": "round"
				},
				param3 = {
					fill: options.timerColor,
					stroke: 'none',
					"stroke-width": 0
				},
				bgParam = {
					fill: options.timerBg,
					stroke: 'none',
					"stroke-width": 0
				};

			// Custom Arc Attribute
			r.customAttributes.arc = function(value, R) {
				var total = 360,
					alpha = 360 / total * value,
					a = (90 - alpha) * Math.PI / 180,
					cx = ((diameter / 2) + padding + stroke),
					cy = ((diameter / 2) + padding + stroke),
					x = cx + R * Math.cos(a),
					y = cy - R * Math.sin(a),
					path;
				if (total == value) {
					path = [["M", cx, cy - R], ["A", R, R, 0, 1, 1, 299.99, cy - R]];
				} else {
					path = [["M", cx, cy - R], ["A", R, R, 0, +(alpha > 180), 1, x, y]];
				}
				return {
					path: path
				};
			};

			// Custom Segment Attribute
			r.customAttributes.segment = function(angle, R) {
				var a1 = -90;
				R = R - 1;
				angle = (a1 + angle);
				var flag = (angle - a1) > 180,
					x = ((diameter / 2) + padding),
					y = ((diameter / 2) + padding);
				a1 = (a1 % 360) * Math.PI / 180;
				angle = (angle % 360) * Math.PI / 180;
				return {
					path: [["M", x, y], ["l", R * Math.cos(a1), R * Math.sin(a1)], ["A", R, R, 0, +flag, 1, x + R * Math.cos(angle), y + R * Math.sin(angle)], ["z"]]
				};
			};

			if (options.autoPlay && defs.total > 1 && defs.timer == "pie") {
				r.circle(R + padding, R + padding, R + padding - 1).attr(bgParam);
			}
			var timerBgPath = r.path().attr(param),
				timerPath = r.path().attr(param2),
				pieTimer = r.path().attr(param3);
		}

		if (options.autoPlay && defs.total > 1 && defs.timer == "360bar") {
			timerBgPath.attr({
				arc: [359.9, R]
			});
		}

		//Set Timer Styles
		if (defs.timer == "bar") {
			iCarouselTimer.css({
				opacity: options.timerOpacity,
				width: diameter,
				height: stroke,
				border: options.timerBarStroke + 'px ' + options.timerBarStrokeColor + ' ' + options.timerBarStrokeStyle,
				padding: padding,
				background: options.timerBg
			});
			barTimer.css({
				width: 0,
				height: stroke,
				background: options.timerColor,
				'float': 'left'
			});
		} else {
			iCarouselTimer.css({
				opacity: options.timerOpacity,
				width: width,
				height: height
			});
		}

		//Set Timer Position
		var position = options.timerPosition.toLowerCase().split('-');
		for (var i = 0; i < position.length; i++) {
			if (position[i] == 'top') {
				iCarouselTimer.css({
					top: options.timerY + 'px',
					bottom: ''
				});
			} else if (position[i] == 'middle') {
				iCarouselTimer.css({
					top: (options.timerY + (defs.height / 2) - (options.timerDiameter / 2)) + 'px',
					bottom: ''
				});
			} else if (position[i] == 'bottom') {
				iCarouselTimer.css({
					bottom: options.timerY + 'px',
					top: ''
				});
			} else if (position[i] == 'left') {
				iCarouselTimer.css({
					left: options.timerX + 'px',
					right: ''
				});
			} else if (position[i] == 'center') {
				iCarouselTimer.css({
					left: (options.timerX + (defs.width / 2) - (options.timerDiameter / 2)) + 'px',
					right: ''
				});
			} else if (position[i] == 'right') {
				iCarouselTimer.css({
					right: options.timerX + 'px',
					left: ''
				});
			}
		}

		//Browser capabilities checker functions
		var support = {

			//CSS3 Transform3D support
			transform3d: function() {
				var props = ['perspectiveProperty', 'WebkitPerspective', 'MozPerspective', 'OPerspective', 'msPerspective'],
					i = 0,
					support = false,
					form = document.createElement('form');

				while (props[i]) {
					if (props[i] in form.style) {
						support = true;
						break;
					}
					i++;
				}
				return support;
			},

			//CSS3 Transform2D support
			transform2d: function() {
				var props = ['transformProperty', 'WebkitTransform', 'MozTransform', 'OTransform', 'msTransform'],
					i = 0,
					support = false,
					form = document.createElement('form');

				while (props[i]) {
					if (props[i] in form.style) {
						support = true;
						break;
					}
					i++;
				}
				return support;
			},

			//CSS3 Transistion support
			transition: function() {
				var props = ['transitionProperty', 'WebkitTransition', 'MozTransition', 'OTransition', 'msTransition'],
					i = 0,
					support = false,
					form = document.createElement('form');

				while (props[i]) {
					if (props[i] in form.style) {
						support = true;
						break;
					}
					i++;
				}
				return support;
			}
		}

		//Start the iCarousel
		var iCarousel = {
			rightItems: new Array(),
			leftItems: new Array(),
			rightOutItem: null,
			leftOutItem: null,

			//Initial function
			init: function() {

				if (options.directionNav) this.setButtons();
				this.layout();
				this.events();

				//Start the timer
				if (options.autoPlay && defs.total > 1) {
					iCarousel.setTimer();
					iCarouselTimer.attr('title', options.pauseLabel).show();
				}
			},

			//Switch slide function
			goSlide: function(index, motionless, fastchange) {
				//Trigger the onLastSlide callback
				if (defs && (defs.slide == defs.total - 1)) {
					options.onLastSlide.call(this);
				}

				this.clearTimer();

				//Trigger the onBeforeChange callback
				options.onBeforeChange.call(this);

				//Set slide
				defs.slide = (index < 0 || index > defs.total - 1) ? 0 : index;

				//Trigger the onSlideShowEnd callback
				if (defs.slide == defs.total - 1) {
					options.onSlideShowEnd.call(this);
				}

				defs.currentSlide = slides.eq(defs.slide);

				//Custom easing as defined by "data-easing" attribute
				defs.easing = (defs.currentSlide.data('easing')) ? iCarousel.setEasing($.trim(defs.currentSlide.data('easing'))) : iCarousel.setEasing(options.easing);

				//Set the currentSlide pausetime
				defs.time = (defs.currentSlide.data('pausetime')) ? defs.currentSlide.data('pausetime') : options.pauseTime;
				var animSpeed = (fastchange) ? (options.animationSpeed / (fastchange)) : false;

				slides.removeClass('current');

				//Start Transition
				defs.lock = true;

				this.layout(true, animSpeed);

				if (fastchange) return false;

				this.resetTimer();

				//Triger when animations finished
				setTimeout(iCarousel.animationEnd, options.animationSpeed);
			},

			//goFar function
			goFar: function(index) {

				var diff = (index == defs.total - 1 && defs.slide == 0) ? -1 : (index - defs.slide);
				if (defs.slide == defs.total - 1 && index == 0) diff = 1;
				var diff2 = (diff < 0) ? -diff : diff,
					timeBuff = 0;

				for (var i = 0; i < diff2; i++) {
					var timeout = (diff2 == 1) ? 0 : (timeBuff);
					setTimeout(function() {
						(diff < 0) ? iCarousel.goPrev(diff2) : iCarousel.goNext(diff2);
					}, timeout);
					timeBuff += (options.animationSpeed / (diff2));
				}
				setTimeout(iCarousel.animationEnd, options.animationSpeed);

				this.resetTimer();

			},

			//Triger when animation finished
			animationEnd: function() {

				//Trigger the onAfterChange callback
				options.onAfterChange.call(this);

				defs.lock = false;
				defs.degree = 0;

				//Restart the interval
				if (defs.interval == null && !defs.pause && options.autoPlay) iCarousel.setTimer();
			},

			//Timer processor
			processTimer: function() {
				if (defs.timer == "360bar") {
					var degree = (defs.degree == 0) ? 0 : defs.degree - .9;
					timerPath.attr({
						arc: [degree, R]
					});
				} else if (defs.timer == "pie") {
					var degree = (defs.degree == 0) ? 0 : defs.degree - .9;
					pieTimer.attr({
						segment: [degree, R]
					});
				} else {
					barTimer.css({
						width: ((defs.degree / 360) * 100) + '%'
					});
				}
				defs.degree += 4;
			},

			//Reset Timer
			resetTimer: function() {
				if (defs.timer == "360bar") {
					timerPath.animate({
						arc: [0, R]
					}, options.animationSpeed);
				} else if (defs.timer == "pie") {
					pieTimer.animate({
						segment: [0, R]
					}, options.animationSpeed);
				} else {
					barTimer.animate({
						width: 0
					}, options.animationSpeed);
				}
			},

			//Interval timer call function
			timerCall: function() {
				iCarousel.processTimer();
				if (defs.degree > 360) {
					iCarousel.goNext();
				}
			},

			//Set the timer function
			setTimer: function() {
				defs.interval = setInterval(iCarousel.timerCall, (defs.time / 90));
			},

			//Clean the timer function
			clearTimer: function() {
				clearInterval(defs.interval);
				defs.interval = null;
				defs.degree = 0;
			},

			//Items layout shower function
			layout: function(animate, speedTime) {
				//Set sides items
				this.setItems();

				//Set initial slides styles
				var slideTop = (defs.topSpace == "auto") ? ((defs.height / 2) - (defs.currentSlide.data('outerheight') / 2)) : 0,
					slideLeft = ((defs.width / 2) - (defs.currentSlide.data('outerwidth') / 2)),
					center = (defs.width / 4),
					zIndex = 999,
					css = {},
					anim = {},
					speed = (speedTime) ? (speedTime / 1000) : (options.animationSpeed / 1000);

				if (animate && support.transition()) slides.css({
					'-webkit-transition': "all " + speed + "s " + defs.easing,
					'-moz-transition': "all " + speed + "s " + defs.easing,
					'-o-transition': "all " + speed + "s " + defs.easing,
					'-ms-transition': "all " + speed + "s " + defs.easing,
					'transition': "all " + speed + "s " + defs.easing
				});

				slides.css({
					top: slideTop + 'px',
					position: 'absolute',
					opacity: 0,
					visibility: 'hidden'
				});
				defs.currentSlide.addClass('current').css({
					'-webkit-transform': 'none',
					'-moz-transform': 'none',
					'-o-transform': 'none',
					'-ms-transform': 'none',
					'transform': 'none',
					left: slideLeft + 'px',
					top: slideTop + 'px',
					width: defs.currentSlide.data('width') + "px",
					height: defs.currentSlide.data('height') + "px",
					zIndex: zIndex,
					opacity: 1,
					visibility: 'visible'
				});


				for (var i = 0; i < this.rightItems.length; i++) {
					var slide = this.rightItems[i];
					zIndex -= i + 1, css = this.CSS(slide, i, zIndex, true), cssA = this.CSS(slide, i, zIndex, true, true);

					slide.css(css).css({
						opacity: 1,
						visibility: 'visible'
					});
				}

				for (var i = 0; i < this.leftItems.length; i++) {
					var slide = this.leftItems[i];
					zIndex -= i + 1, css = this.CSS(slide, i, zIndex);

					slide.css(css).css({
						opacity: 1,
						visibility: 'visible'
					});
				}

				if (defs.total > options.slides) {
					this.rightOutItem.css(this.CSS(this.rightOutItem, this.leftItems.length - 0.5, this.leftItems.length - 1, true));
					this.leftOutItem.css(this.CSS(this.leftOutItem, this.leftItems.length - 0.5, this.leftItems.length - 1));
				}

			},

			//Set iCarousel items
			setItems: function() {
				var num = Math.floor(options.slides / 2) + 1;
				iCarousel.leftItems = new Array();
				iCarousel.rightItems = new Array();

				for (var i = 1; i < num; i++) {
					var eq = (defs.dir == "ltr") ? (defs.slide + i) % (defs.total) : (defs.slide - i) % (defs.total);
					iCarousel.leftItems.push(slides.eq(eq));
				}

				for (var i = 1; i < num; i++) {
					var eq = (defs.dir == "ltr") ? (defs.slide - i) % (defs.total) : (defs.slide + i) % (defs.total);
					iCarousel.rightItems.push(slides.eq(eq));
				}

				this.leftOutItem = slides.eq(defs.slide - num);
				this.rightOutItem = ((defs.total - defs.slide - num) <= 0) ? slides.eq(-parseInt(defs.total - defs.slide - num)) : slides.eq(defs.slide + num);
				var leftOut = this.leftOutItem,
					rightOut = this.rightOutItem;
				if (defs.dir == "ltr") {
					this.leftOutItem = rightOut;
					this.rightOutItem = leftOut;
				}
			},

			//CSS style generator function
			CSS: function(slide, i, zIndex, positive) {
				var leftRemain = (defs.space == "auto") ? parseInt((i + 1) * (slide.data('width') / 1.5)) : parseInt((i + 1) * (defs.space));
				if (support.transform3d() && options.make3D) {
					var transform = (positive) ? 'translateX(' + (leftRemain) + 'px) translateZ(-' + (250 + ((i + 1) * 110)) + 'px) rotateY(-' + options.perspective + 'deg)' : 'translateX(-' + (leftRemain) + 'px) translateZ(-' + (250 + ((i + 1) * 110)) + 'px) rotateY(' + options.perspective + 'deg)',
						left = "0%",
						top = (defs.topSpace == "auto") ? "none" : parseInt((i + 1) * (defs.space)),
						width = "none",
						height = "none",
						overflow = "visible";
				} else if (support.transform2d()) {
					var transform = (positive) ? 'translateX(' + (leftRemain / 1.5) + 'px) scale(' + (1 - (i / 10) - 0.1) + ')' : 'translateX(-' + (leftRemain / 1.5) + 'px) scale(' + (1 - (i / 10) - 0.1) + ')',
						left = "0%",
						top = (defs.topSpace == "auto") ? "none" : parseInt((i + 1) * (defs.topSpace)),
						width = "none",
						height = "none",
						overflow = "visible";
				} else {
					var transform = '',
						left = (positive) ? ((leftRemain / 1.5) + ((i + 2) * 50)) + "px" : "-" + ((leftRemain / 1.5)) + "px",
						width = (slide.data('width') - ((i + 2) * 50)),
						height = (slide.data('height') - ((i + 2) * 50)),
						top = (defs.topSpace == "auto") ? ((defs.height / 2) - (height / 2)) : parseInt((i + 1) * (defs.topSpace)),
						overflow = "hidden";
				}
				css = {
					'-webkit-transform': transform,
					'-moz-transform': transform,
					'-o-transform': transform,
					'-ms-transform': transform,
					'transform': transform,
					left: left,
					top: top,
					width: width,
					height: height,
					zIndex: zIndex,
					overflow: overflow
				};
				return css;

			},

			//Set easing timing function
			setEasing: function(ease) {
				ease = $.trim(ease);

				switch (ease) {
				case 'linear':
					ease = 'cubic-bezier(0.250, 0.250, 0.750, 0.750)';
					break;
				case 'ease':
					ease = 'cubic-bezier(0.250, 0.100, 0.250, 1.000)';
					break;
				case 'ease-in':
					ease = 'cubic-bezier(0.420, 0.000, 1.000, 1.000)';
					break;
				case 'ease-out':
					ease = 'cubic-bezier(0.000, 0.000, 0.580, 1.000)';
					break;
				case 'ease-in-out':
					ease = 'cubic-bezier(0.420, 0.000, 0.580, 1.000)';
					break;
				case 'ease-out-in':
					ease = 'cubic-bezier(0.000, 0.420, 1.000, 0.580)';
					break;
				case 'easeInQuad':
					ease = 'cubic-bezier(0.550, 0.085, 0.680, 0.530)';
					break;
				case 'easeInCubic':
					ease = 'cubic-bezier(0.550, 0.055, 0.675, 0.190)';
					break;
				case 'easeInQuart':
					ease = 'cubic-bezier(0.895, 0.030, 0.685, 0.220)';
					break;
				case 'easeInQuint':
					ease = 'cubic-bezier(0.755, 0.050, 0.855, 0.060)';
					break;
				case 'easeInSine':
					ease = 'cubic-bezier(0.470, 0.000, 0.745, 0.715)';
					break;
				case 'easeInExpo':
					ease = 'cubic-bezier(0.950, 0.050, 0.795, 0.035)';
					break;
				case 'easeInCirc':
					ease = 'cubic-bezier(0.600, 0.040, 0.980, 0.335)';
					break;
				case 'easeInBack':
					ease = 'cubic-bezier(0.600, -0.280, 0.735, 0.045)';
					break;
				case 'easeOutQuad':
					ease = 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
					break;
				case 'easeOutCubic':
					ease = 'cubic-bezier(0.215, 0.610, 0.355, 1.000)';
					break;
				case 'easeOutQuart':
					ease = 'cubic-bezier(0.165, 0.840, 0.440, 1.000)';
					break;
				case 'easeOutQuint':
					ease = 'cubic-bezier(0.230, 1.000, 0.320, 1.000)';
					break;
				case 'easeOutSine':
					ease = 'cubic-bezier(0.390, 0.575, 0.565, 1.000)';
					break;
				case 'easeOutExpo':
					ease = 'cubic-bezier(0.190, 1.000, 0.220, 1.000)';
					break;
				case 'easeOutCirc':
					ease = 'cubic-bezier(0.075, 0.820, 0.165, 1.000)';
					break;
				case 'easeOutBack':
					ease = 'cubic-bezier(0.175, 0.885, 0.320, 1.275)';
					break;
				case 'easeInOutQuad':
					ease = 'cubic-bezier(0.455, 0.030, 0.515, 0.955)';
					break;
				case 'easeInOutCubic':
					ease = 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
					break;
				case 'easeInOutQuart':
					ease = 'cubic-bezier(0.770, 0.000, 0.175, 1.000)';
					break;
				case 'easeInOutQuint':
					ease = 'cubic-bezier(0.860, 0.000, 0.070, 1.000)';
					break;
				case 'easeInOutSine':
					ease = 'cubic-bezier(0.445, 0.050, 0.550, 0.950)';
					break;
				case 'easeInOutExpo':
					ease = 'cubic-bezier(1.000, 0.000, 0.000, 1.000)';
					break;
				case 'easeInOutCirc':
					ease = 'cubic-bezier(0.785, 0.135, 0.150, 0.860)';
					break;
				case 'easeInOutBack':
					ease = 'cubic-bezier(0.680, 0, 0.265, 1)';
					break;
				};
				return ease;
			},

			//goNext function for go to next slide
			goNext: function(fastchange) {
				fastchange = (fastchange) ? fastchange : false;
				if (!fastchange && defs.lock) return false;
				(defs.slide == defs.total) ? iCarousel.goSlide(0, false, fastchange) : iCarousel.goSlide(defs.slide + 1, false, fastchange);
			},

			//goPrev function for go to previous slide
			goPrev: function(fastchange) {
				fastchange = (fastchange) ? fastchange : false;
				if (!fastchange && defs.lock) return false;
				(defs.slide == 0) ? iCarousel.goSlide(defs.total - 1, false, fastchange) : iCarousel.goSlide(defs.slide - 1, false, fastchange);
			},

			//Events
			events: function() {

				// keyboard navigation handler
				if (options.keyboardNav) $(document).bind('keyup.iCarousel', function(event) {
					switch (event.keyCode) {
					case 33:
						; // pg up
					case 37:
						; // left
					case 38:
						// up
						iCarousel.goPrev();
						break;
					case 34:
						; // pg down
					case 39:
						; // right
					case 40:
						// down
						iCarousel.goNext();
						break;
					}
				});

				// Navigation buttons
				$('a#iCarouselPrev', el).click(function() {
					iCarousel.goPrev();
				});
				$('a#iCarouselNext', el).click(function() {
					iCarousel.goNext();
				});

				//Play/Pause action
				iCarouselTimer.click(function() {

					if (iCarouselTimer.hasClass('paused')) {
						iCarouselTimer.removeClass('paused').attr('title', options.pauseLabel);
						defs.pause = false;

						//Restart the timer
						if (defs.interval == null) {
							iCarousel.setTimer();

							//Trigger the onPlay callback
							options.onPlay.call(this);
						}
					} else {
						iCarouselTimer.addClass('paused').attr('title', options.playLabel);
						defs.pause = true;
						clearInterval(defs.interval);
						defs.interval = null;

						//Trigger the onPause callback
						options.onPause.call(this);
					}
				});

				//For pauseOnHover setting
				if (options.pauseOnHover) {
					el.hover(function() {
						if (!defs.pause) {
							clearInterval(defs.interval);
							defs.interval = null;
						}
					}, function() {
						//Restart the timer
						if (!defs.lock && !defs.pause && defs.interval == null && defs.degree <= 359 && options.autoPlay) {
							iCarousel.setTimer();
						}
					});
				}

				//Touch navigation
				if (options.touchNav && (navigator.userAgent.match(/ipad|iphone|ipod|android/i))) {
					el.swipe({
						swipeLeft: function() {
							(defs.dir == "ltr") ? iCarousel.goPrev() : iCarousel.goNext();
						},
						swipeRight: function() {
							(defs.dir == "ltr") ? iCarousel.goNext() : iCarousel.goPrev();
						}
					});
				}

				//Bind the pause action
				el.bind('iCarousel:pause', function() {
					iCarouselTimer.addClass('paused').attr('title', options.playLabel);
					defs.pause = true;
					clearInterval(defs.interval);
					defs.interval = null;

					//Trigger the onPause callback
					options.onPause.call(this);
				});

				//Bind the play action
				el.bind('iCarousel:play', function() {
					iCarouselTimer.removeClass('paused').attr('title', options.pauseLabel);
					defs.pause = false;

					//Restart the timer
					if (defs.interval == null) {
						iCarousel.setTimer();

						//Trigger the onPlay callback
						options.onPlay.call(this);
					}
				});

				//Bind the goSlide action
				el.bind('iCarousel:goSlide', function(event, slide) {
					if (defs.slide != slide) iCarousel.goFar(slide);
				});

				//Bind the next action
				el.bind('iCarousel:next', function() {
					iCarousel.goNext();
				});

				//Bind the previous action
				el.bind('iCarousel:previous', function() {
					iCarousel.goPrev();
				});

				//Bind the mousewheel on the slides
				if (el.mousewheel && options.mouseWheel) el.mousewheel(function(event, delta) {
					if (delta < 0) iCarousel.goNext();
					else iCarousel.goPrev();
				});

				//Bind the click on the slides
				slides.click(function() {
					var slide = $(this),
						index = slide.attr('index');
					if (defs.slide != index) iCarousel.goFar(index);
				});
			},

			//Direction navigation buttons
			setButtons: function() {
				el.append('<a class="iCarouselNav" id="iCarouselPrev" title="' + options.previousLabel + '">' + options.previousLabel + '</a><a class="iCarouselNav" id="iCarouselNext" title="' + options.nextLabel + '">' + options.nextLabel + '</a>');
			}
		};

		//Set initial easing
		defs.easing = iCarousel.setEasing(options.easing);


		// Run Preloader
		new ImagePreload(defs.images, function(i) {
			var percent = (i * 10);
			preloaderBar.stop().animate({
				width: percent + '%'
			});
		}, function() {
			preloaderBar.stop().animate({
				width: '100%'
			}, function() {
				iCarouselPreloader.remove();
				iCarousel.init();

				//Trigger the onAfterLoad callback
				options.onAfterLoad.call(this);
			});
		});
	};

	// Begin the iCarousel plugin
	$.fn.iCarousel = function(options) {

		// Default options. Play carefully.
		options = jQuery.extend({
			easing: 'ease-in-out',
			slides: 3,
			make3D: true,
			perspective: 35,
			animationSpeed: 500,
			pauseTime: 5000,
			startSlide: 0,
			directionNav: true,
			autoPlay: true,
			keyboardNav: true,
			touchNav: true,
			mouseWheel: true,
			pauseOnHover: false,
			nextLabel: "Next",
			previousLabel: "Previous",
			playLabel: "Play",
			pauseLabel: "Pause",
			randomStart: false,
			slidesSpace: 'auto',
			slidesTopSpace: 'auto',
			direction: 'rtl',
			timer: 'Pie',
			timerBg: '#000',
			timerColor: '#FFF',
			timerOpacity: 0.4,
			timerDiameter: 35,
			timerPadding: 4,
			timerStroke: 3,
			timerBarStroke: 1,
			timerBarStrokeColor: '#FFF',
			timerBarStrokeStyle: 'solid',
			timerBarStrokeRadius: 4,
			timerPosition: 'top-right',
			timerX: 10,
			timerY: 10,
			onBeforeChange: function() {},
			onAfterChange: function() {},
			onAfterLoad: function() {},
			onLastSlide: function() {},
			onSlideShowEnd: function() {},
			onPause: function() {},
			onPlay: function() {}
		}, options);

		$(this).each(function() {
			var el = $(this),
				slides = el.children();

			new iCarouselStarter(el, slides, options);
		});

	};

	// Swipe Function
	$.fn.swipe = function(options) {
		options = jQuery.extend({
			threshold: {
				x: 30,
				y: 100
			},
			swipeLeft: function() {
				alert('swiped left');
			},
			swipeRight: function() {
				alert('swiped right');
			}
		}, options);

		$(this).each(function() {
			var me = $(this);
			var originalCoord = {
				x: 0,
				y: 0
			};
			var finalCoord = {
				x: 0,
				y: 0
			};

			function touchMove(event) {
				event.preventDefault();
				finalCoord.x = event.originalEvent.touches[0].pageX;
				finalCoord.y = event.originalEvent.touches[0].pageY;
			}

			function touchEnd(event) {
				var changeY = originalCoord.y - finalCoord.y;
				if (changeY < options.threshold.y && changeY > (options.threshold.y * -1)) {
					changeX = originalCoord.x - finalCoord.x;
					if (changeX > options.threshold.x) {
						options.swipeLeft.call(this);
					}
					if (changeX < (options.threshold.x * -1)) {
						options.swipeRight.call(this);
					}
				}
			}

			function touchStart(event) {
				originalCoord.x = event.originalEvent.targetTouches[0].pageX;
				originalCoord.y = event.originalEvent.targetTouches[0].pageY;
				finalCoord.x = originalCoord.x;
				finalCoord.y = originalCoord.y;
			}
			me.bind("touchstart MozTouchDown", touchStart);
			me.bind("touchmove MozTouchMove", touchMove);
			me.bind("touchend MozTouchRelease", touchEnd);
		});
	};

})(jQuery);