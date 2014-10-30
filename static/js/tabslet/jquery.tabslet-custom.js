/**
 * Tabs plugin
 *
 * @copyright	Copyright 2012, Dimitris Krestos
 * @license		Apache License, Version 2.0 (http://www.opensource.org/licenses/apache2.0.php)
 * @link		http://vdw.staytuned.gr
 * @version		v1.4.2
 */

	/* Sample html structure

	<div class='tabs'>
		<div class="tabs-nav">
			<ul>
				<li><a href="#tab-1">Tab 1</a></li>
				<li><a href="#tab-2">Tab 2</a></li>
				<li><a href="#tab-3">Tab 3</a></li>
			</ul>
		</div>
		<div class="tabs-content">
			<div id='tab-1'></div>
			<div id='tab-2'></div>
			<div id='tab-3'></div>
		</div>
	</div>

	*/

;(function($, window, undefined) {
	"use strict";

	$.fn.tabslet = function(options) {

		var defaults = {
			mouseevent:   'click',
			attribute:    'href',
			animation:    false,
			autorotate:   false,
			pauseonhover: true,
			delay:        2000,
			active:       1,
			controls:     {
				prev: '.prev',
				next: '.next'
			}
		};

		var options = $.extend(defaults, options);

		return this.each(function() {

			var $this = $(this);

			// Ungly overwrite
			options.mouseevent    = $this.data('mouseevent') || options.mouseevent;
			options.attribute     = $this.data('attribute') || options.attribute;
			options.animation     = $this.data('animation') || options.animation;
			options.autorotate    = $this.data('autorotate') || options.autorotate;
			options.pauseonhover 	= $this.data('pauseonhover') || options.pauseonhover;
			options.delay 				= $this.data('delay') || options.delay;
			options.active 				= $this.data('active') || options.active;

			$this.find('.tabs-content > div').hide();
			$this.find('.tabs-content > div').eq(options.active - 1).show();
			
			var tabToActivate = $this.find('.tabs-nav > ul li').eq(options.active - 1);
			tabToActivate.addClass('active');
			options.onActivate && options.onActivate.call(tabToActivate, tabToActivate);

			var fn = eval(

				function() {

					$(this).trigger('_before');

					$this.find('.tabs-nav > ul li').removeClass('active');
					$(this).addClass('active');
					$this.find('.tabs-content > div').hide();
					options.onActivate && options.onActivate.call($(this), $(this));

					var currentTab = $(this).find('a').attr(options.attribute);

					if (options.animation) {
						$this.find(currentTab).animate( { opacity: 'show' }, 'slow', function() {
							$(this).trigger('_after');
						});
					} else {
						$this.find(currentTab).show();
						$(this).trigger('_after');
					}
					return false;
				}

			);

			var init = eval("$this.find('.tabs-nav > ul li')." + options.mouseevent + "(fn)");

			init;

			// Autorotate
			var elements = $this.find('.tabs-nav > ul li'), i = options.active - 1; // ungly

			function forward() {

				i = ++i % elements.length; // wrap around

				options.mouseevent == 'hover' ? elements.eq(i).trigger('mouseover') : elements.eq(i).click();

				var t = setTimeout(forward, options.delay);

				$this.mouseover(function () {

					if (options.pauseonhover) clearTimeout(t);

				});

			}

			if (options.autorotate) {

				setTimeout(forward, 0);

				if (options.pauseonhover) $this.on( "mouseleave", function() { setTimeout(forward, 1000); });

			}

			function move(direction) {

				var class_list = elements.eq(i).attr('class');
				var step_num = class_list.replace("item-step item-step-","");
				if (direction == 'forward'){
					step_num = parseInt(step_num.replace("active","")) + 1;
				}else{
					step_num = parseInt(step_num.replace("active","")) - 1;
				}
				hide_prev_next(step_num);
				
				if (direction == 'forward') i = ++i % elements.length; // wrap around

				if (direction == 'backward') i = --i % elements.length; // wrap around

				elements.eq(i).click();

			}

			$this.find(options.controls.next).click(function() {
				move('forward');
			});

			$this.find(options.controls.prev).click(function() {
				move('backward');
			});

			$this.on ('destroy', function() {
				$(this).removeData();
			});

		});

	};

	$(document).ready(function () { $('[data-toggle="tabslet"]').tabslet(); });

})(jQuery);

function hide_prev_next(step_num){
  jQuery(".prev").show();
  step_num = parseInt(step_num);
  
  if (step_num == 1 || step_num == 12) {
	jQuery(".prev").hide();
  }else{
	jQuery(".prev").show();
  }
  
  if (step_num == 17 || step_num == 6) {
	jQuery(".next").hide();
  }else{
	jQuery(".next").show();
  }	
}