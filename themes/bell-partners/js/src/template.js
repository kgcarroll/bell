(function(){
  "use strict";

  var ariaExpanded = '',
  		ariaExpanded_slideout = '',
   		ariaPressed = '',
   		ariaLabel = '',
   		isMobileWidth = false,
      slideout = $("#slideout"),
      slideoutContent = $("#slideout-content"),
   		trigger = $("#slideout-button-container"),
      navTrigger = $("#nav-trigger"),
      slideoutHeight = 0;

  if(slideout.length) {
    slideoutHeight = slideout.height();
  }

  var topPosMobile = 47 + slideoutHeight, // Default navTrigger's top position for mobile
      topPosDesktop = 73 + slideoutHeight, // Default navTrigger's top position for desktop
      topPosDesktopScroll = 18 + slideoutHeight, // Default navTrigger's top position for desktop while scrolling
      slideoutContentHeight;

  function getSlideoutHeight(){
    slideoutContentHeight = 0;
    if(slideout.length) {
      // Get the total slideout height, but we have to make it visible first
      slideoutContent.css({'position':'absolute','visibility':'hidden','display':'block'});
      // Assign the value to this variable
      slideoutContentHeight = slideoutContent.outerHeight();
      // And then remove the trick we just did
      slideoutContent.css({'position':'relative','visibility':'visible','display':'none'});
    }   
  }

	function checkMobileWidth(){ isMobileWidth = ($(window).width() < 768); }	

  // Menu actions
  function menu() {
  	var navigation = $("#navigation-container"),
  			//navTrigger = $("#nav-trigger"),
  			navItems = $(".inner-container").find("a");

		navItems.prop('tabindex', -1); // Default

    navTrigger.on('click touch keyup',function(event){
			if (typeof event.keyCode === 'undefined' || event.keyCode === 13) { // Click or Enter key.
        toggle_slideoutContent(true); // Force closing the slideout if it's open.
        navigation.toggleClass('active');
        $(this).toggleClass('active');

	      // 508
	      ariaExpanded = navigation.hasClass('active') ? 'true' : 'false'; // Determine if the content is hidden or shown
	      ariaPressed = navigation.hasClass('active') ? 'true' : 'false'; // Determine if the content is hidden or shown
	      ariaLabel = navigation.hasClass('active') ? 'Close Navigation' : 'Open Navigation'; // Determine if the content is hidden or shown

	      navigation.attr('aria-expanded', ariaExpanded); // Set the aria-expanded attribute accordingly
	      navTrigger.attr('aria-pressed', ariaPressed); // Set the aria-pressed attribute accordingly
	      navTrigger.attr('aria-label', ariaLabel); // Set the aria-label attribute accordingly

        if(ariaExpanded == 'true') {
        	$('a, button').prop('tabindex', -1); // Remove tabindex on everything...
        	navItems.prop('tabindex', 0); // ...except for the inner container
        } else {
        	$('a, button').prop('tabindex', 0);
        	navItems.prop('tabindex', -1);
        }
			}
	  });
  }


  function toggle_slideoutContent(forceClose) {
    if(slideout.length){
      var topPos = 0;
      if(typeof forceClose !== 'undefined' && forceClose && !slideout.hasClass("active")) {
        return;
      }
      slideout.toggleClass("active");
      slideoutContent.toggleClass("active");
      slideoutContent.slideToggle();

      // 508
      ariaExpanded_slideout = slideout.hasClass('active') ? 'true' : 'false'; // Determine if the content is hidden or shown
      ariaExpanded = slideoutContent.hasClass('active') ? 'true' : 'false'; // Determine if the content is hidden or shown
      ariaPressed = slideoutContent.hasClass('active') ? 'true' : 'false'; // Determine if the content is hidden or shown
      ariaLabel = slideoutContent.hasClass('active') ? 'Close Special Content' : 'Open Special Content'; // Determine if the content is hidden or shown

      slideout.attr('aria-expanded', ariaExpanded_slideout); // Set the aria-expanded attribute accordingly
      slideoutContent.attr('aria-expanded', ariaExpanded); // Set the aria-expanded attribute accordingly
      trigger.attr('aria-label', ariaLabel); // Set the aria-expanded attribute accordingly
  		trigger.attr('aria-pressed', ariaPressed); // Set the aria-expanded attribute accordingly
      if(isMobileWidth) {
          topPos = topPosMobile;
      } else {
          topPos = topPosDesktop;
      }

      if(ariaExpanded == 'true'){
        navTrigger.css({'top':slideoutContentHeight + topPos});
      } else {
        navTrigger.css({'top':topPos});
      }
    }
  }

  // Slide Out functions
	function initslideoutContent(){
	  trigger.on('click touch keyup', function(event){
			if (typeof event.keyCode === 'undefined' || event.keyCode === 13) {
				toggle_slideoutContent();
        $("#navigation-container").removeClass('active');
        $("#nav-trigger").removeClass('active');
	      event.preventDefault();
			}
    });
	}

  function initReviewCarousel(){
    $('.review-wrapper').slick({
      infinite: true,
      arrows: true,
      prevArrow:'<div aria-label="Previous" class="prev"><span class="icon-nav-arrow"></span><span class="label">Prev Review</span></div>',
      nextArrow:'<div aria-label="Next" class="next"><span class="icon-nav-arrow"></span><span class="label">Next Review</span></div>',
      slidesToShow: 1,
      initialSlide: 0,
      slidesToScroll: 1,
      cssEase:"ease-in-out"
    });
  }

  // Makes the header fixed when the screen is scrolled
  function animateHeader() {
    var header = $('#header'),
        scroll = $(document).scrollTop(),
        body = $('body'),
        logo = $('.logo'),
        logoScroll = $('.logo-scroll'),
        h = header.height(),
        navTrigger = $("#nav-trigger");
    if(scroll > 0) {
        header.addClass('scroll');
        navTrigger.addClass('scroll');
        body.css('margin-top',h);
      if(!isMobileWidth) {
        logo.removeClass('fadeIn');
        logo.addClass('fadeOut');
      }
     

    } else if(scroll < 25) {
      header.removeClass('scroll');
      navTrigger.removeClass('scroll');
      body.css('margin-top',0);
      if(!isMobileWidth){
        logo.removeClass('fadeOut');
        logo.addClass('fadeIn');
      }     

    }
  }

  function relocateNavTrigger(){
    if (isMobileWidth){
      navTrigger.css({'top':topPosMobile+'px'});
    } else {
      if(navTrigger.hasClass('scroll')){
        navTrigger.css({'top': topPosDesktopScroll + 'px'});
      } else {
        navTrigger.css({'top':topPosDesktop+'px'});
      }
    }
  }

  function resetSlideout(){
    // The navTrigger must be relocated first
    relocateNavTrigger();

    slideout.removeClass("active");
    slideoutContent.removeClass("active");

    slideoutContent.slideUp();

    slideout.attr('aria-expanded', 'false'); // Set the aria-expanded attribute accordingly
    slideoutContent.attr('aria-expanded', 'false'); // Set the aria-expanded attribute accordingly
    trigger.attr('aria-label', 'Open Special Content'); // Set the aria-expanded attribute accordingly
    trigger.attr('aria-pressed', 'false'); // Set the aria-expanded attribute accordingly
  }

  function detectIE() {
    var ua = window.navigator.userAgent;
    var trident = ua.indexOf('Trident/');
    if (trident > 0) {
      // IE 11 => return version number
      var rv = ua.indexOf('rv:');
      return ua.substring(rv + 3, ua.indexOf('.', rv));
    }
    var edge = ua.indexOf('Edge/');
    if (edge > 0) {
      // Edge (IE 12+) => return version number
      return ua.substring(edge + 5, ua.indexOf('.', edge));
    }
    // Other browser
    return '';
  }

  // Set height for .sylvan-home-header-background
  function setHeaderHeight() {
    if($('body').hasClass('sylvan')) {
      setTimeout(function(){
        var captionHeight = $('.sylvan #hero-container #hero-image.style-1 .caption').outerHeight(),
          heroContentHeight = $('.sylvan #hero-container #hero-content').outerHeight(),
          blogHeroHeight = $('.sylvan #hero-container #hero-image').outerHeight(),
          combinedHeight = (captionHeight + heroContentHeight - 55);
        $('.sylvan-home-header-background').css('height', combinedHeight+'px' );  // Home
        $('.sylvan-header-background').css('height', (heroContentHeight - 50)+'px' );  // Features, Amenities, Pet Policies
        if($('body').hasClass('single-post')) {
          $('.sylvan-header-background').css('height', (blogHeroHeight - 100)+'px' );  // Blog Posts
        }
      }, 350);
    }
  }

  // Do stuff on document ready
	$(document).ready(function(){
    getSlideoutHeight();
		menu();
		initslideoutContent();
    checkMobileWidth();
    relocateNavTrigger();
    // initReviewCarousel();

    // Sylvan Helpers
    setHeaderHeight();

    var ie = 'ie-'+detectIE();
    $('body').addClass(ie);
	});

	$(window).on({
    scroll:function(){
      checkMobileWidth();
      animateHeader();
      resetSlideout();
    },
    resize:function(){
      checkMobileWidth();
      getSlideoutHeight();
      resetSlideout();
      
      // Sylvan Helpers
      setHeaderHeight();
      $('.logo').removeClass('fadeOut');
    }
	});
}());
