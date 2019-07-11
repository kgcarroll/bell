(function() {
  "use strict";

  var bounds = null, // Google maps bounds for fitting all POIs on map
      categoriesData = null, // Categories data from JSON
      poiData = [], // Empty array to restructure categoriesData
      categories_container = $("#categories"),
      map_marker = null, // Default map marker info from JSON
      infoWindow = null, // Opened infoWindow
      map = null, // Google maps element
      mobile_category_label = $("#mobile-label"),
      mobile_category_overlay = $("#mobile-category-overlay"),
      catFilter = $('.category-filter'),
      mapCategories = $('#map-categories'),
      property = null, // Property info from JSON
      property_coordinates = null, // Property LatLng
      isMobileWidth = false,
      markersArray = [],
      infowindowsArray = [],
      infoWindow = null,
      marker_cntr = 0;

  // Clears any overlays currently in the array
  function clearOverlays() {
    if (markersArray) {
      for (var i = 0; i < markersArray.length; i++ ) {
        markersArray[i].setMap(null);
      }
    }
  }

  // Closes any infowindows currently in the Array
  function closeInfowindows() {
    if (infowindowsArray) {
      for (var i = 0; i < infowindowsArray.length; i++ ) {
        infowindowsArray[i].close();
      }
    }
  }

  function checkMobileWidth(){
    isMobileWidth = ($(window).width() <= 768);
  }

  function fit_map_to_bounds(){
    map.fitBounds(bounds); // Fit map to show all visible POIs
    // Zoom out if map has just single POI.
    var listener = google.maps.event.addListener(map, "idle", function() {
      // if (map.getZoom() > 14) map.setZoom(14);
      google.maps.event.removeListener(listener);
    });
  }

  function makeDropdown(){
    $('<select class="mobile-select" />').appendTo(catFilter);
    $("#map-categories a").each(function() {
      var element = $(this);
      $('<option />', {
          'value'   : element.attr('href'),
          'text'    : element.text()
      }).appendTo('.category-filter select');
    });

    $('.category-filter select').change(function() {
      var selected = $(this).find(":selected").html();
      //All Categories
      if(selected.toLowerCase().replace(" ","-") === 'all'){
        init_map();
      }
      else{
        var curr_cat = poiData.filter(function(e){
          return e.name == selected;
        });
        show_category(curr_cat[0]);
      }

      // Change the category also for the ul list
      catFilter.find('ul').children('li').removeClass('active');
      catFilter.find('ul').children('li.'+selected.toLowerCase().replace(" ","-")).addClass('active');
    });
  }

  function init_categories(){
    var isActive = false;

    categories_container.empty();
    catFilter.empty();
    mapCategories.empty();

    for (var i in poiData){
      isActive = (i === "0") ? true : false; // The first one is active by default
      map_marker = poiData[i].marker;  // Assign categoty marker(s);
      var category = build_category_elements(poiData[i], isActive),
          filterCat = build_category_filter_elements(poiData[i], isActive);
      poiData[i].element = category;
      build_category_pois(poiData[i]);
      categories_container.append(category);
      mapCategories.append(filterCat);
    }
    
    // Refresh map for "All" Category
    var allCat = mapCategories.find('li.all');
    allCat.on({
      click:function(){
        init_map();
      }
    });

    catFilter.append(mapCategories);
    makeDropdown();
    fit_map_to_bounds();
  }

  function build_cat_tiny_nav(cat){
    var mapCategoriesSelect = catFilter.find('select');
    mapCategoriesSelect.children('option').map(function(i,e){
       $(e).addClass(cat.name.toLowerCase().replace(" ","-") );
    });
  }

  function build_cat_children(cat) {
    var pois = cat.pois,
        child_ul = $(document.createElement("ul"));

    child_ul.addClass('children');

    for (var i in pois){
      var cat_child_li = $(document.createElement("li")),
          cat_child_span = $(document.createElement("span"));

      cat_child_li.attr('id','marker-'+ marker_cntr);
      cat_child_li.addClass('poi');

      cat_child_span.html(pois[i].name);
      cat_child_li.append(cat_child_span);
      child_ul.append(cat_child_li);

      cat_child_li.on({
          click:function(e){
            e.stopPropagation();
            $('.children li.active').removeClass('active');
            $(this).addClass('active');
            var id = $(this).attr('id'),
                marker_key = id.replace(/marker-/,'');
            marker_key = parseInt(marker_key);
            var current_marker = markersArray[marker_key];
            
            closeInfowindows();

            infowindowsArray[marker_key].open(map, current_marker);
          }
      });

      marker_cntr++;
    }
    return child_ul;
  }


  function build_category_elements(cat, isActive){
    var cat_li = $(document.createElement("li")),
        cat_span = $(document.createElement("span")),
        activeClass;
        //activeClass = (isActive) ? 'active' : '';
    
    if(cat.name.toLowerCase().replace(" ","-") !== 'all'){
      activeClass = 'active'; //hardcoded all to active to show the "all" category on page load
    }

    cat_li.addClass('parent ' + cat.name.toLowerCase().replace(" ","-") + ' ' + activeClass);
    cat_span.html(cat.name);
    cat_li.append(cat_span);
    cat_li.append(build_cat_children(cat));

    cat_li.on({
        click:function(){
          if(!$(this).hasClass('active')) {
            cat_li.removeClass('active');
            show_category(cat);
            $(this).addClass('active');  
          }         
        }
    });
    return cat_li;
  }



  function build_category_filter_elements(cat, isActive){
    var cat_li = $(document.createElement("li")),
        cat_a = $(document.createElement("a")),
        cat_span = $(document.createElement("span")),
        activeClass = (isActive) ? 'active' : '';

    cat_li.addClass(cat.name.toLowerCase().replace(" ","-") + ' ' + activeClass);
    cat_span.html(cat.name);
    cat_a.append(cat_span);
    cat_li.append(cat_a);

    cat_li.on({
        click:function(){
          $(this).parent().children().removeClass('active');
          $(this).addClass('active');

          //change the category also for the dropdown list
          catFilter.find('select').children('option').removeAttr('selected');
          catFilter.find('select').children('.'+cat.name.toLowerCase().replace(" ","-")).attr('selected','selected');

          if(cat.name.toLowerCase() === 'all'){
            init();
          } else{
            show_category(cat);
          }

        }
    });
    return cat_li;
  }

  // function showAllCategories(){
  //   // Re-initialize the categories (better than call init())
  //   // CreateCategories(categoriesData);
  //   for (var i in poiData){
  //     // Set active class for all categories
  //     if(i !== "0"){
  //       poiData[i].element.addClass('parent active');        
  //     }
  //   }
  // }

  function build_category_pois(cat){
    for (var i in cat.pois){
      var poi = build_poi(cat.pois[i]);
      bounds.extend(poi.position);
      cat.pois[i].marker = poi;
    }
  }

  function build_poi(poi){
    var marker_icon = null;
    if (map_marker){
      marker_icon = {
            url: map_marker.url,
            size: new google.maps.Size(map_marker.width, map_marker.height),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point((map_marker.width / 2), map_marker.height)
          };
    }    

    var map_link = (typeof(poi.url) != "undefined" && poi.url != "") ? '<div class="website"><a href="' + poi.url + '" target="_blank">Website</a></div>' : '',
        coordinates = new google.maps.LatLng(poi.lat,poi.lng),
        directions_link = '<div class="directions"><a href="http://maps.google.com/?q=' + poi.address.replace(' ','+')+ '" target="_blank">Directions</a></div>',
        description = (poi.description !== "") ? '<div class="description-container">' + poi.description + '</div>' : '',
        cur_info = '<div class="info-window"><div class="name"><strong>' + poi.name + '</strong></div><div class="address">' + poi.address+ '</div>' + map_link + directions_link + '</div>',
        infowindow = new google.maps.InfoWindow({
          content: cur_info
        }),
        marker = new google.maps.Marker({
            icon: marker_icon,
            infoWindow: infowindow,
            map: map,
            position: coordinates,
            title: poi.name
        });
    google.maps.event.addListener(marker, 'click', function(event) {
      if (infoWindow){
        infoWindow.close();
      }
      marker.infoWindow.open(map, marker);
      infoWindow = marker.infoWindow;
    });


    bounds.extend(marker.position);
    markersArray.push(marker);
    infowindowsArray.push(infowindow);

    return marker;
  }

  function show_category(cat){
    // Reset bounds
    bounds = new google.maps.LatLngBounds(property_coordinates);

    // Loop through categories, hiding all POIs except those in the current category
    for (var i in poiData){
      var set_map = (poiData[i].name != cat.name) ? null : map, // check if this is the current category, hide all POIs if not, otherwise show POIs
          category_class = (poiData[i].name != cat.name) ? 'parent' : 'parent active'; // set active class for current category
          
      if (typeof poiData[i].element != "undefined"){
        poiData[i].element.removeClass();
        if (category_class != ''){
          poiData[i].element.addClass(category_class);
        }
      }
      for (var j in poiData[i].pois){
        if (typeof poiData[i].pois[j].marker != "undefined"){
          poiData[i].pois[j].marker.setMap(set_map);
          if (set_map){
            bounds.extend(poiData[i].pois[j].marker.position);
          }
        }
      }
    }
    fit_map_to_bounds();
  }

  function init_property_marker(){
    if (property){
      property_coordinates = new google.maps.LatLng(property.lat,property.lng);
      var property_icon = {
            url: property.property_map_marker.url,
            size: new google.maps.Size(property.property_map_marker.width,property.property_map_marker.height),
            origin: new google.maps.Point(0,0),
            anchor: new google.maps.Point(property.property_map_marker.width / 2 , property.property_map_marker.height / 2)
          },
      map_link = '<a href="http://maps.google.com/?q=' + property.address + ', ' + property.city + ', ' + property.state + ' ' + property.zip + '" target="_blank">Directions</a>',
      property_infowindow = new google.maps.InfoWindow({
        content: '<div class="info-window"><div class="name"><strong>' + property.property_name + '</strong></div><div class="address">' + property.address.replace(", ","<br />") + ',<br />' + property.city + ', ' + property.state + ' ' + property.zip + '</div>' + map_link + '</div>'
      }),
      property_marker = new google.maps.Marker({
        icon: property_icon,
        infoWindow: property_infowindow,
        map: map,
        position: property_coordinates,
        title: property.property_name
      });
      google.maps.event.addListener(property_marker, 'click', function(event) {
        if (infoWindow){
          infoWindow.close();
        }
        property_marker.infoWindow.open(map, property_marker);
        infoWindow = property_marker.infoWindow;
      });
      bounds = new google.maps.LatLngBounds(property_coordinates);
      init_categories();
      return property_marker;
    }
  }

  function init_map(){
    var coordinates = null;
    if (property) {
      coordinates = new google.maps.LatLng(property.lat, property.lng);
    }
    // Style from Snazzy Maps
    var MY_MAPTYPE_ID = 'custom_style',
        map_style = [{featureType:"all",elementType:"labels",stylers:[{visibility:"off"}]},{featureType:"administrative",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"administrative",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"administrative",elementType:"geometry.stroke",stylers:[{visibility:"on"}]},{featureType:"administrative.country",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"administrative.country",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"administrative.province",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"administrative.province",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"administrative.locality",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"administrative.locality",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"administrative.locality",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"administrative.neighborhood",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"administrative.neighborhood",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"landscape",elementType:"all",stylers:[{hue:"#FFBB00"},{saturation:43.400000000000006},{lightness:37.599999999999994},{gamma:1}]},{featureType:"landscape",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"landscape",elementType:"geometry.stroke",stylers:[{visibility:"on"}]},{featureType:"landscape.natural",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"landscape.natural",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"landscape.natural",elementType:"geometry.stroke",stylers:[{visibility:"on"}]},{featureType:"landscape.natural.landcover",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"landscape.natural.landcover",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"landscape.natural.terrain",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"landscape.natural.terrain",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"poi",elementType:"all",stylers:[{hue:"#00ff6a"},{saturation:-1.0989010989011234},{lightness:11.200000000000017},{gamma:1},{visibility:"off"}]},{featureType:"poi.business",elementType:"all",stylers:[{visibility:"off"}]},{featureType:"road",elementType:"all",stylers:[{visibility:"on"}]},{featureType:"road.highway",elementType:"all",stylers:[{hue:"#FFC200"},{saturation:-61.8},{lightness:45.599999999999994},{gamma:1}]},{featureType:"road.highway.controlled_access",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"road.highway.controlled_access",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"road.arterial",elementType:"all",stylers:[{hue:"#FF0300"},{saturation:-100},{lightness:51.19999999999999},{gamma:1}]},{featureType:"road.arterial",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"road.arterial",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"road.local",elementType:"all",stylers:[{hue:"#FF0300"},{saturation:-100},{lightness:52},{gamma:1}]},{featureType:"transit",elementType:"geometry",stylers:[{visibility:"on"}]},{featureType:"transit",elementType:"geometry.fill",stylers:[{visibility:"on"}]},{featureType:"water",elementType:"all",stylers:[{hue:"#0078FF"},{saturation:-13.200000000000003},{lightness:2.4000000000000057},{gamma:1}]}],
        map_options = {
          center: coordinates,
          zoom: 14,
          scrollwheel:false,
          streetViewControl:false,
          zoomControlOptions:{
            style:google.maps.ZoomControlStyle.SMALL,
            position:google.maps.ControlPosition.LEFT_CENTER
          },
          mapTypeId: MY_MAPTYPE_ID,
          draggable: true
        },
        custom_map_type = new google.maps.StyledMapType(map_style, {
          name:MY_MAPTYPE_ID
        });
        map = new google.maps.Map(document.getElementById('map'),map_options);


    google.maps.event.addListener(map, "click", function(event) {
        infoWindow.close();
    });

    
    map.mapTypes.set(MY_MAPTYPE_ID, custom_map_type);
    init_property_marker();
  }

  function init(){
    $.ajax({
      cache:false,
      url: themeData.templateURL + "-child/JSON/neighborhood.json",
      dataType:"json",
      success:function(data){
        poiData = data.categories;

        // Default option: All
        var default_opt = {
              name:"All",
              category_marker: {},
              pois: []
            };    
        poiData.unshift(default_opt);
        property = data.property;
        init_map();
      }
    });

    mobile_category_label.on("click",function(){
      categories_container.slideToggle();
    });

    if(isMobileWidth) {
      categories_container.css('display', 'block');
    }

  }


  $(document).ready(function(){
    init();
    checkMobileWidth();
  });

  // On Window resize
  $(window).on({
    resize:function(){
      checkMobileWidth();
    }
  })

})();