(function(){
  "use strict";
  var activeClass="active",
      activeCategory,
      galleryType,
      galleryData,
      categoriesContainer = $("#categories"),
      categoriesElement = "li",
      contentContainer = $("ul#image-gallery"),
      currentCategory,
      currentImageContainer,
      currentLightboxIndex,
      isMobileWidth = false,
      lightboxElement,
      lightboxContentElement = $(document.createElement("div")),
      lightboxMaxHeight = 0,
      thumbs,
      thumbsContainer = $("ul#slick-thumbs"),
      toursData,
      videoData;


  function makeThumbs(content, category) {
    var thumbElement = $(document.createElement('li')),
        thumbData = $(document.createElement('img'));

    thumbData.attr({
      alt: content.alt,
      src: content.slider_thumbnail
    })

    thumbElement.addClass(category.toLowerCase().replace(" ","-"));

    thumbElement.append(thumbData);
    return thumbElement;
  }

  function populateThumbs(content, category) {
    for(var i = 0; i < content.length; i++) {
      var thumbs = makeThumbs(content[i], category);
      thumbsContainer.append(thumbs); 
    }     
  }

  function getYouTubeID(content) {
    var contentData = $(document.createElement('iframe')),
        youTubeRegex = "^(?:https?:)?//[^/]*(?:youtube(?:-nocookie)?\.com|youtu\.be).*[=/]([-\\w]{11})(?:\\?|=|&|$)",
        videoID = content.content.match(youTubeRegex);

    return videoID[1];
  }


  function incrementLightbox(inc){
    var nextIndex = currentLightboxIndex + inc,
        finalIndex = currentCategory.length - 1;

    if (nextIndex < 0){
      nextIndex = finalIndex;
    } else if(nextIndex > finalIndex){
      nextIndex = 0;
    }

    currentLightboxIndex = nextIndex;
    openLightbox(currentCategory[nextIndex]);
  }

  function closeLightbox(){ 
    lightboxElement.fadeOut();
  }

  function createLightbox(){
    var lightboxWrapper = $(document.createElement("div")),
        lightboxBackground = $(document.createElement("div")),
        lightboxContainer = $(document.createElement("div")),
        lightbox = $(document.createElement("div")),
        lightboxCaption = $(document.createElement("div")),
        lightboxClose = $(document.createElement("span")),
        lightboxCloseIcon = $(document.createElement("i")),
        lightboxNext = $(document.createElement("span")),
        nextIcon = $(document.createElement("i")),
        prevIcon = $(document.createElement("i")),
        lightboxPrev = $(document.createElement("span"));

    lightboxWrapper.attr({id:"lightbox-wrapper"});
    lightboxContainer.attr({id:"lightbox-container"});
    lightboxBackground.attr({id:"lightbox-background"});
    lightbox.attr({id:"lightbox"});
    lightboxCaption.attr({id:"lightbox-caption"});
    lightboxClose.addClass('close');
    lightboxCloseIcon.addClass('fas fa-times');
    lightboxContentElement.addClass('element-container');

    // Build Next/Previous buttons
    nextIcon.addClass("fa fa-angle-right arrow");
    prevIcon.addClass("fa fa-angle-left arrow");
    lightboxNext.attr({id:"lightbox-next"});
    lightboxPrev.attr({id:"lightbox-prev"});
    lightboxNext.append(nextIcon);
    lightboxPrev.append(prevIcon);

    lightboxContentElement.css("max-height",lightboxMaxHeight);
    lightboxClose.append(lightboxCloseIcon);
    lightbox.append(lightboxContentElement,lightboxPrev,lightboxNext,lightboxCaption,lightboxClose);
    lightboxContainer.append(lightbox);
    lightboxWrapper.append(lightboxContainer,lightboxBackground);

    lightboxWrapper.fadeIn();

    lightboxBackground.on({
      "click":function(){
        $('iframe').attr('src', '');
        closeLightbox();
      }
    });

    lightboxClose.on({
      "click":function(){
        $('iframe').attr('src', '');
        closeLightbox();
      }
    });

    lightboxNext.on({
      "click":function(){
        incrementLightbox(1);
      }
    });

    lightboxPrev.on({
      "click":function(){
        incrementLightbox(-1);
      }
    });

    lightboxElement = lightboxWrapper;

    $("body").append(lightboxWrapper);
  }

  function makeLightboxImage(content) {
    var contentData = $(document.createElement('img')),
        imgSrc = content.full,
        imgWidth = content.full_width;

    contentData.addClass('responsive-img full');
    contentData.attr({
      alt: content.alt,
      src: imgSrc,
      srcset: imgSrc,
      sizes: "(max-width: " + imgWidth + "px) 100vw, "+ imgWidth + "px"
    });

    return contentData;
  }

  function makeLightboxVideo(content) {
    var contentData = $(document.createElement('iframe'));
    contentData.attr({
      width: '838',
      height: '471',
      src: 'https://www.youtube.com/embed/'+getYouTubeID(content)+'?controls=0',
      frameborder: '0',
      allow: 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture'
    });
    return contentData;
  } 

  function openLightbox(content){
    if (typeof lightboxElement == "undefined"){
      createLightbox();
    } else if(lightboxElement.not(":visible")){
      lightboxElement.fadeIn();
    }

    // Build gallery element (Images, Videos, or 360 Tours)
    if(content.type === "image"){
      var contentData = makeLightboxImage(content);
    } else if(content.type === "video"){
      var contentData = makeLightboxVideo(content);
    }

    // Set ELement
    lightboxElement.find(lightboxContentElement).html(contentData);

    // Set caption 
    if (content.caption != "") {
      lightboxElement.find("#lightbox-caption").html('');
      lightboxElement.find("#lightbox-caption").html(content.caption).show();
    } else {
      lightboxElement.find("#lightbox-caption").hide();
    }

    // Vertically center lightbox
    var lightboxContainer = lightboxElement.find("#lightbox-container");
    if(content.type === "image") {
      var lightboxTop = ($(window).height() - content.calculatedHeight) / 2;
    } else {
      var lightboxTop = ($(window).height() - 471) / 2;
    }
          
    lightboxContainer.css("top",lightboxTop);
    
  }

  function makeContent(content, catObject, category, index) {
    var contentElement = $(document.createElement('li')),
        contentHover = $(document.createElement('div')),
        contentData = $(document.createElement('img'));
    
    contentHover.addClass('hover');

    // Get images based on Gallery Type
    if(galleryType === "0") {
      var imgSrc = content.slider,
          imgWidth = content.slider_width;
    }
    if(galleryType === "1") {
      // Featured Images
      if(content.columns == true) {
        var imgSrc = content.featured,
            imgWidth = content.featured_width;
      }
      // Uncropped (resized) Thumbnails
      else if(content.uncropped == true) {
        var imgSrc = content.thumbnail_soft,
            imgWidth = content.thumbnail_soft_width;
      }
      // Normal, cropped thumbnail.
      else {
        var imgSrc = content.thumbnail_hard,
            imgWidth = content.thumbnail_hard_width;
      }
    }

    contentData.addClass('responsive-img');
    contentData.attr({
      alt: content.alt,
      src: imgSrc,
      srcset: imgSrc,
      sizes: "(max-width: " + imgWidth + "px) 100vw, "+ imgWidth + "px"
    });


    var gridSize = (content.columns == true) ? 'grid-item grid-item-2x' : 'grid-item',
        crop =  (content.uncropped == true) ? ' uncropped' : ' cropped';
    contentElement.addClass('element all ' + ((galleryType == "0") ? 'slide' : gridSize + crop) + ' ' + category.toLowerCase().replace(" ","-"));

    // Build hover effect
    if(galleryType === "1") {
      if(content.type === "video") {
        var playButton = $(document.createElement('span'));

        playButton.addClass('icon-play');
        contentHover.append(contentData, playButton);
      } else {
        contentHover.append(contentData);
      }

      // Build hover effect
      contentHover.append(contentData);
      // Add contentHover to contentElement.
      contentElement.append(contentHover);
    }
    else {
      contentElement.append(contentData);
    }

    // Add caption for Slider Gallery, if it exists. (Masonry captions will be built in lightbox.)
    if(galleryType === "0") {
      if(content.caption) {
        var captionContainer = $(document.createElement('div'));
        captionContainer.addClass('caption');
        captionContainer.html(content.caption);
        contentElement.append(captionContainer);
      }
    }

    // Make Lightbox if Masonry Gallery
    if(galleryType === "1") {
       contentElement.data("index",index);
       contentElement.on('click', function() {
         // Don't open lightbox on mobile
         if (!isMobileWidth){
           currentLightboxIndex = $(this).data("index");
           
           //the first child is the ALL category
           if(categoriesContainer.find('li:first-child').hasClass('active')){
             currentCategory = galleryData[0].content;
           }else{
             currentCategory = catObject; //a category selected
           }
           openLightbox(content, catObject);
         }
      });      
    }

    return contentElement;
  }

  function populateContent(content, category) {
    for(var i = 0; i < content.length; i++) {
      var contentElements = makeContent(content[i], content, category, i);
      contentContainer.append(contentElements); 
    }    
  }

  function showCategoryContent(catElement, catObject) {
    if(galleryType === "0") { currentCategory = catObject; }
    populateContent(catObject.content, catObject.category_name);
    // Add Slider Gallery if activated.
    if(thumbs && galleryType === "0") { populateThumbs(currentCategory.content, currentCategory.category_name); }
  }

  function makeCategory(gallery) {
    var categoryElement = $(document.createElement(categoriesElement));
    categoryElement.addClass(gallery.category_name.replace(' ','-').toLowerCase());
    categoryElement.attr('data-filter','.'+gallery.category_name.toLowerCase().replace(" ","-"));

    if(gallery.category_name.toLowerCase() != 'all'){
      showCategoryContent(categoryElement, gallery);
    } 

    categoryElement.html(gallery.category_name);
    return categoryElement;
  }

  function populateCategories(categories) {
    for (var i = 0; i<categories.length; i++) {   
      var category = makeCategory(categories[i]);
      categoriesContainer.attr('data-filter-group','categories');
      if(i === 0) {
        category.addClass(activeClass); //the first one is active by default
      }
      categoriesContainer.append(category);
    }
  }
   
   // MASONRY GALLERY
  function startMasonryGallery() {
    var filters = {};
    // Initialize Masonry Gallery
    var $grid = $('.masonry').imagesLoaded( function() {
      // Initialize Isotope after all images have loaded
      $grid.isotope({
        layoutMode: 'packery',
        itemSelector: '.grid-item',
        packery: {
          gutter: 20
        },
        filter: '.all'
      });
    });


    categoriesContainer.on('click', 'li', function() {
      var categories = $(this).parents('#categories'),
          filterGroup = categories.attr('data-filter-group');
      
      filters[filterGroup] = $(this).attr('data-filter'); // Set filter for group
      categories.find('.active').removeClass('active'); // Find current class and remove.
      $(this).addClass('active'); // Add new 'active' class.
      $grid.isotope({ filter: filters[filterGroup] }); // Arrange, and use filter function
    });
  }

  function buildMasonryGallery(){

    if(videoData.length > 0){
      // Make Video Category
      var videoCat = {
        category_name: "Videos",
        content:[]
      };

      // Loop through video data and get existing category content, and push them to into "videoCat" array.
      for (var i=0; i<videoData.length; i++){
        videoCat.content.push(videoData[i]);
        videoCat.content[i].type = 'video';  // Add video type
      }
      galleryData.push(videoCat);
    }

    // Make "All" Category
    var allCategories = {
      category_name: "All",
      content:[]
    };

    // Loop through data and get existing category content, and push them to into "allCategories" array.
    for (var i=0; i<galleryData.length; i++){
      for (var j=0; j<galleryData[i].content.length; j++){
        allCategories.content.push(galleryData[i].content[j]);
      }
    }

    galleryData.unshift(allCategories);
     populateCategories(galleryData);
     startMasonryGallery();
  }
  // END MASONRY GALLERY


  // SLIDER GALLERY
  function startSliderGallery(){

    if(thumbs) { var navThumb = '#slick-thumbs' ; }
    else { var navThumb = '' ; }

    // Initialize Slider.
    $('.slider').slick({
      infinite: true,
      arrows: true,
      prevArrow:'<button aria-label="Previous" class="prev"><span class="icon-nav-arrow"></span></button>',
      nextArrow:'<button aria-label="Next" class="next"><span class="icon-nav-arrow"></span></button>',
      // dots: true,
      // customPaging: function(e, t) {
      //   return (t + 1) + " / " + e.slideCount
      // },
      centerMode: true,
      centerPadding: '60px',
      variableWidth: true,
      slidesToShow: 1,
      initialSlide: 0,
      slidesToScroll: 1,
      asNavFor: navThumb,
      cssEase:"ease-in-out",
      responsive: [
        {
          breakpoint: 768,
          settings: {
            arrows:false
          }
        },

      ]
    });

    // Click next/previous image to advance.
    $('.slider').on('click', '.slick-slide', function (e) {
       e.stopPropagation();
       var index = $(this).data("slick-index");
       if ($('.slider').slick('slickCurrentSlide') !== index) {
         $('.slider').slick('slickGoTo', index);
       }
     });

    // Filter
    $('#categories li').on('click', function(e){

      $(this).parent().children().removeClass(activeClass);
      $(this).addClass().addClass(activeClass);

      var filter = ($(this).html() === "All") ? '*' : $(this).attr('data-filter');
 
      $('.slider').slick('slickUnfilter');
      $('.slider').slick('slickFilter', filter).slick('refresh');
      $('.slider').slick('slickGoTo', 0);

      if(thumbs){
        $('#slick-thumbs').slick('slickUnfilter');
        $('#slick-thumbs').slick('slickFilter', filter).slick('refresh');
        $('#slick-thumbs').slick('slickGoTo', 0);
      }

    });

    // Thumbnail Controller (if activated.)
    if(thumbs) {
      $('#slick-thumbs').slick({
          infinite: true,
          dots: false,
          arrows: true,
          prevArrow:'<button aria-label="Previous" class="prev"><span class="icon-nav-arrow"></span></button>',
          nextArrow:'<button aria-label="Next" class="next"><span class="icon-nav-arrow"></span></button>',
          autoplay: false,
          slidesToShow: 6,
          slidesToScroll: 1,
          centerMode: true,
          mobileFirst: false,
          variableWidth: true,
          adaptiveHeight: false,
          asNavFor: '.slider',
          focusOnSelect: true,
          responsive: [
              {
                  breakpoint: 1280,
                  settings: {
                      slidesToShow: 6,
                      slidesToScroll: 1,

                  }
              },
              {
                  breakpoint: 1024,
                  settings: {
                      slidesToShow: 3,
                      slidesToScroll: 1,

                  }
              },
              {
                  breakpoint: 600,
                  settings: {
                      slidesToShow: 2,
                      slidesToScroll: 1
                  }
              },

          ]
      });
    }
  }

  function buildSliderGallery(){
    // Make "All" Category
    var allCategories = {
      category_name: "All",
      content:[]
    };

    // Loop through data and get existing category content, and push them to into "allCategories" array.
    for (var i=0; i<galleryData.length; i++){
      for (var j=0; j<galleryData[i].content.length; j++){
        allCategories.content.push(galleryData[i].content[j]);
      }
    }
    galleryData.unshift(allCategories);

    populateCategories(galleryData);
    startSliderGallery();
    makeDropdown();
  }
  // END SLIDER GALLERY

  function determineGalleryType(galleryType) {
    if(galleryType === "0"){
      buildSliderGallery();
    } else if(galleryType === "1"){
      buildMasonryGallery();
    }
  }
  
  function preloadImages(content,callback){
    for (var i = 0; i < content.length; i++){
      for (var j = 0; j < content[i].content.length; j++){
        // Preload Full Image
        var img = new Image();
        img.src = content[i].content[j].full;

        //The height of every image is only available once they are loaded, so we save it into galleryData
        img.onload = function(){
          for(var k = 0; k < galleryData[0].content.length; k++){
            if(galleryData[0].content[k].full === this.src){
              galleryData[0].content[k].calculatedHeight = this.height;
            }
          }
        }

        // Preload Featured Thumb
        var img2 = new Image();
        img2.src = content[i].content[j].featured;
        
        // Preload Cropped Thumb
        var img3 = new Image();
        img3.src = content[i].content[j].thumbnail_hard;
        
        // Preload Uncropped Thumb
        var img4 = new Image();
        img4.src = content[i].content[j].thumbnail_soft;
      }
    }
    if (typeof callback != "undefined"){
      callback();
    }
  }


  function getGalleryJSON(){
    $('#loader').show();
    $.ajax({
        cache:false,
        url: themeData.templateURL + "-child/JSON/gallery.json",
        dataType:"json",
        success:function(data){
          thumbs = data.slider_thumbnails;
          videoData = data.videos;
          toursData = data.tours;
          galleryType = data.gallery_type;
          galleryData = data.image_gallery;  
        },
        complete: function(){
          $('#loader').fadeOut();

          var callback = function(){
            determineGalleryType(galleryType);
          }
          preloadImages(galleryData,callback);
        },
        error:function(error){
          console.log('Whoops, the API call failed.');
        }
    });
  }

  function determineMobileWidth(){
    isMobileWidth = ($(window).width() < 768);  // Not tablet size!
  }

  function determineLightboxMaxHeight(){
    lightboxMaxHeight = $(window).height() * 0.8;
    if (typeof lightboxElement != "undefined"){
      lightboxElement.find(lightboxContentElement).css("max-height", lightboxMaxHeight);
    }
  }

  function makeDropdown(){
    $('<select class="mobile-select" />').insertAfter('#categories');
    $('#categories li').each(function() {
      var element = $(this);
      $('<option />', {
          'value' : element.attr('data-filter'),
          'data-filter' : element.attr('data-filter'),
          'text' : element.text()
      }).appendTo('#slider-gallery select');
    });

    $('#slider-gallery select').change(function() {
      var selected = $(this).find(":selected");
      var filter = (selected.html() === "All") ? '*' : selected.data("filter");

      $('#image-gallery').slick('slickUnfilter');
      $('#image-gallery').slick('slickFilter', filter).slick('refresh');
      $('#image-gallery').slick('slickGoTo', 0);

    });
  }

  // Do stuff on document ready
  $(document).ready(function(){
    determineMobileWidth();
    determineLightboxMaxHeight();
    getGalleryJSON();
  });

  $(window).on({
    load:function(){
      $('#image-gallery').slick("slickGoTo",1,true);
      $('#image-gallery').slick("slickGoTo",0,true);
    },
    resize:function(){
      determineMobileWidth();
      determineLightboxMaxHeight();
    }
  });

  $(document).keydown(function(e) {
    switch(e.which) {
      case 37: // Left
        incrementLightbox(-1);
        break;
      case 39: // Right
        incrementLightbox(1);
        break;
      default: return; // Exit this handler for other keys
    }
    e.preventDefault(); // Prevent the default action (scroll / move caret)
  });


}());
