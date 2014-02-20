/*
ver: 1.1
*/
//TODO: ludResponsive - prebaciti responsiveClasses i ItemStyle u $(document).ready(...); u jednu varijablu koja se zove u section_head-u
jQuery(window).load(function(){
	var ludLoop = function(selectors, options){
		this.selectors = selectors;
		this.options = options;
	};
	ludLoop.prototype = {
		defaults: {
			enable_animation: false,
			use_link: false,
			defFredWidth: 200,
			fredWidth: 300
		},
		init: function() {
			self = this;
			self.options = jQuery.extend({},self.defaults,self.options);
			//grid
			(self.options.enable_animation === false) ? self.masonryGrid() : self.fredGrid();
			//lightbox
			if(self.options.use_link === 'colorbox') self.colorBox();
		},
		masonryGrid: function(){
			if(jQuery.isFunction(jQuery.fn.masonry)){
				self.itemHeight();
				masonryObj = {
					itemSelector: self.selectors.ludItem.selector,
					isAnimated: true,
					isFitWidth: true,
					columnWidth: self.options.itemWidth
				};
				if(self.options.numslides === 0 ) {
					masonryObj.isFitWidth = false;
				}
				self.selectors.wraper.masonry(masonryObj);
			}
		},
		fredGrid: function(){
			if(jQuery.isFunction(jQuery.fn.carouFredSel)){
				//fred item width
				defItemWidth 	= self.options.defFredWidth;
				fredWidth = (self.options.fredWidth > self.selectors.container.width()) ? self.options.fredWidth : defItemWidth;
				//fredobj for section loop
				defFredObj = {
					responsive: true,
					//height: "variable",
					items:{
						visible: {
							'min'         : 1,
							'max'         : self.options.numslides
						},
						width: fredWidth,
						//height: "variable"
					},
					scroll:{
						items: 1,
						fx: self.options.mode,
						duration: self.options.speed,
						pauseOnHover: self.options.pause_on_hover
					},
					auto:{
						play: self.options.auto,
						timeoutDuration: self.options.pause
					},
					pagination: '',
					prev : {	button  : ''},
					next : { button  : ''}
				}
				//pager
				if(self.options.pager === true) {
					defFredObj.pagination = self.selectors.pager.selector;
					self.selectors.pager.show(0);
				}
				//controls (arrows)
				if(self.options.controls === true) {
					defFredObj.next.button = self.selectors.next;
					defFredObj.prev.button = self.selectors.prev;
					self.selectors.next.css('margin-top', '-=20px');
					self.selectors.prev.css('margin-top', '-=20px');
					self.selectors.next.show(0);
					self.selectors.prev.show(0);
				}
				//initiate fred
				self.selectors.wraper.delay(800, 'carouFredSel').carouFredSel(defFredObj);
				self.itemHeight();
				self.selectors.wraper.trigger('updateSizes');
			}

		},
		colorBox: function(){
			if(jQuery.isFunction(jQuery.fn.colorbox)){
				jQuery('.'+self.selectors.sectionPrefix+'-link',self.selectors.ludItem).click(function(e){
					e.preventDefault();
					var obj = jQuery(e.currentTarget);
					obj.colorbox({inline:true, href: '#'+self.selectors.sectionPrefix+'-inner-'+obj.data(self.selectors.sectionPrefix+'-id'), height: '600px', width: '80%'});
				});
			}
		},
		itemHeight: function(){
			if(self.options.equal_height === true) {
				var itemHeight = Math.max.apply(null, self.selectors.inner.map(function (){
					return jQuery(this).height();
				}).get());
				self.selectors.inner.css({'min-height' : itemHeight});
			}
		}
	}
	//local
	jQuery.fn.ludLoop = function(selectors, options) {
		return new ludLoop(selectors, options).init();
	};
});