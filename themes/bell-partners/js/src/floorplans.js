(function(){
  "use strict";

  // Global Variables
  var catWrap = $("#cat-wrap"),
  		typeCount = 0,
  		currentFloorPlanType,
  		currentUnitIndex = 0,
  		isMobileWidth = false,
  		resultsContainer = $("#floor-plan-results"),
  		resultsWrap = $(".results-wrap"),
  		floorplanData = [],
  		unitData = [],
      lightboxElement,
      lightboxContentElement = $(document.createElement("div")),
      lightboxMaxHeight = 0,
  		unitResultsContainer = $("#unit-results"),
  		unitTypes = [],
  		backButton = $("#back-button");


  // Turn integer into string.
	function int2Str(int){
    var str=["Zero","One","Two","Three","Four","Five","Six","Seven","Eight","Nine"];
    return str[int];
	}

	// Adds commas to number
	function numberCommas(num){
    if (typeof num != undefined && num != null){
      return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    } else {
      return "";
    }
	}

	function closeLightbox(){
		lightboxElement.fadeOut();
	}


	function createLightbox(){
	  var lightboxWrapper = $(document.createElement("div")),
	      lightboxBackground = $(document.createElement("div")),
	      lightboxContainer = $(document.createElement("div")),
	      lightbox = $(document.createElement("div")),
	      lightboxClose = $(document.createElement("span")),
	      lightboxCloseIcon = $(document.createElement("i")),
	      lightboxNext = $(document.createElement("span")),
	      lightboxPrev = $(document.createElement("span"));

	  lightboxWrapper.attr({id:"lightbox-wrapper"});
	  lightboxContainer.attr({id:"lightbox-container"});
	  lightboxBackground.attr({id:"lightbox-background"});
	  lightbox.attr({id:"lightbox"});
	  lightboxClose.addClass('close');
	  lightboxCloseIcon.addClass('fas fa-times');
	  lightboxContentElement.addClass('element-container');

	  lightboxContentElement.css("max-height",lightboxMaxHeight);
	  lightboxClose.append(lightboxCloseIcon);
	  lightbox.append(lightboxContentElement,lightboxPrev,lightboxNext,lightboxClose);
	  lightboxContainer.append(lightbox);
	  lightboxWrapper.append(lightboxContainer,lightboxBackground);

	  lightboxWrapper.fadeIn();

	  lightboxBackground.on({
	    "click":function(){
	      closeLightbox();
	    }
	  });

	  lightboxClose.on({
	    "click":function(){
	      closeLightbox();
	    }
	  });

	  lightboxElement = lightboxWrapper;

	  $("body").append(lightboxWrapper);
	}

	function buildUnitRow(unit, rowClass){

				var name = 'Apartment #'+unit.ApartmentName,
						price = '$'+numberCommas(parseInt(unit.MinimumRent)),
						available = unit.AvailableDate,
						apply = unit.ApplyOnlineURL,
						unitRow = $("#result-template").clone();

						// console.log(new Date());
						// console.log(available);


				var today = Date.parse(new Date()), // Today
						date = new Date(available),
						// Rebuild Date
						day = date.getDate(), // Day
						month = date.getMonth()+1, // Month starts at 0
						year = date.getFullYear(); // Year

				var availableDate = (date > today) ? 'Available '+month+'/'+day+'/'+year : 'Available Now';

				console.log('today '+today);
				console.log('date '+date);

				unitRow.show();
				unitRow.attr({"id":"unit-type-"+name, "class":"unit-type-tr "+rowClass});
				unitRow.find(".apt-name").html(name);
				unitRow.find(".price").html(price);
				unitRow.find(".available").html(availableDate);
				unitRow.find(".apply .button a").attr({"href":apply,"target":"_blank"});

			return unitRow;

	}

	function openLightbox(unitType){

	  if (typeof lightboxElement == "undefined"){ createLightbox(); }
	  else if(lightboxElement.not(":visible")){ lightboxElement.fadeIn(); }

	  // Build Unit Type Container
    var unitTypeDetails = $(document.createElement("div")),
    		detailsContainer = $(document.createElement("div")),
    		detailsContainerBottom = $(document.createElement("div")),
    		fpImageWrapper = $(document.createElement("div")),
    		unitTypeName = $(document.createElement("div")),
    		bedBath = $(document.createElement("div")),
    		sqft = $(document.createElement("div")),
    		fpImage = $(document.createElement("img")),
				disclaimer = $("#disclaimer");
		
		unitTypeDetails.addClass('unit-type-details');
		detailsContainer.addClass('unit-details');
		detailsContainerBottom.addClass('unit-details-bottom');
		unitTypeName.addClass('name');
		bedBath.addClass('bed-bath');
		sqft.addClass('sqft');
    fpImageWrapper.addClass('fp-image-wrapper');

    var numberOfBeds = parseInt(unitType.Beds),
    		beds = null,
    		numberOfBaths = parseInt(unitType.Baths),
    		baths = null,
    		studioText = 'Studio',
    		bedsText = 'Bedroom',
    		bathText = 'Bathroom';

    // Bedrooms (with or without 's'). 
		if(isMobileWidth){
			bedsText = 'Bed';
    	bathText = 'Bath';
		}

    if(numberOfBeds === 0) { beds = studioText; }
    else if(numberOfBeds === 1) { beds = numberOfBeds + ' ' + bedsText; }
    else { beds = numberOfBeds + ' ' + bedsText + 's'; }

    // Bathrooms (with or without 's').
    baths = (numberOfBaths === 1) ? numberOfBaths+' '+bathText : numberOfBaths+' '+bathText+'s';

    // Assign Elements
    unitTypeName.html(unitType.FloorplanName);
    bedBath.html(beds+' / '+baths);
    fpImage.attr('src', themeData.siteURL+'/wp-content/uploads/'+unitType.FloorplanName.replace(' - ','-').replace(/\s+/g, '-').toUpperCase()+'.jpg');  // Strips out spaces
	  
	  // Append Elements
	  fpImageWrapper.append(fpImage);  // Nest image in container
		
		detailsContainerBottom.append(bedBath);
		
	  if(unitType.AvailableUnitsCount > 0) {
	  	sqft.html(getSquareFootRange(unitType.FloorplanId));
	  	detailsContainerBottom.append(sqft);
	  }

	  detailsContainer.append(unitTypeName, detailsContainerBottom);
	  unitTypeDetails.append(detailsContainer, fpImageWrapper);

	  // Build elements for Available Units //
  	var unitsContainer = $(document.createElement("div")),
				unitCount = $(document.createElement("div")),
				noResults = $("#no-results").clone(),
				resultsTable = $(document.createElement("table")),
				unitResultsTable = $(document.createElement("tbody")),
				head = $(document.createElement("thead")),
				row = $(document.createElement("tr")),
				thApt = $(document.createElement("th")),
				thPrice = $(document.createElement("th")),
				thAvail = $(document.createElement("th")),
				thApply = $(document.createElement("th")),
				availableUnitsResults = null;

		resultsTable.attr({'id':'results'});
		unitResultsTable.attr({'id':'results-table'});
		head.attr({'id':'results-head'});

  	thApt.html("Apartment #");
  	thPrice.html("Starting At");
  	thAvail.html("Availability");

		row.append(thApt, thPrice, thAvail, thApply);
		head.append(row);
		resultsTable.append(head);

		unitResultsTable.empty();

		if(unitType.AvailableUnitsCount > 0) {

			for (var i=0; i<unitData.length; i++) {
				for (var j=0; j<unitData[i].length; j++) {
					var rowClass = (j % 2 == 0) ? 'even' : 'odd',
							unit;

					if(unitType.FloorplanId === unitData[i][j].FloorplanId){
						unit = buildUnitRow(unitData[i][j], rowClass);
						unitResultsTable.append(unit);
					}	
					
				}
			}

			resultsTable.append(unitResultsTable);
			availableUnitsResults = resultsTable;
			
		} else {

			noResults.show();
			availableUnitsResults = noResults;
		}

		unitCount.addClass('count');
  	unitCount.append(unitType.AvailableUnitsCount+' Results');
  	unitsContainer.addClass('available-units-container');
  	unitsContainer.append(unitCount);
  	unitsContainer.append(availableUnitsResults);
  	unitTypeDetails.append(unitsContainer);

		if(unitType.AvailableUnitsCount > 0) {
			var current_disclaimer = disclaimer.clone();
			current_disclaimer.show();
			unitTypeDetails.append(current_disclaimer);
		}else{
			unitCount.empty();
		}

	  // Append all elements into container
	  lightboxElement.find(lightboxContentElement).html(unitTypeDetails);

	  // Vertically center lightbox
	  var lightboxContainer = lightboxElement.find("#lightbox-container"),
	      lightboxTop = ($(window).height() - lightboxContainer.height()) / 2;

	  lightboxContainer.css("top",lightboxTop);
	}

	function determineLightboxMaxHeight(){
	  lightboxMaxHeight = $(window).height() * 0.8;
	  if (typeof lightboxElement != "undefined"){
	    lightboxElement.find(lightboxContentElement).css("max-height", lightboxMaxHeight);
	  }
	}

	function getAvailabilityDate(fpId) {
		var availableDates = [];
		for (var i=0; i<unitData.length; i++) {
			for (var j=0; j<unitData[i].length; j++) {
				if(fpId === unitData[i][j].FloorplanId) {
					availableDates.push(Date.parse(unitData[i][j].AvailableDate));
				}
			}
		}

		var today = Date.parse(new Date()), // Today
				earliestAvailable = Math.min.apply(Math,availableDates),
				date = new Date(earliestAvailable),
				// Rebuild Date
				day = date.getDate(), // Day
				month = date.getMonth()+1, // Month starts at 0
				year = date.getFullYear(); // Year

		var availableDate = (earliestAvailable > today) ? 'Available '+month+'/'+day+'/'+year : 'Available Now';

		return availableDate;
	}

	function getSquareFootRange(fpId) {
		var sfRange = []
		for (var i=0; i<unitData.length; i++) {
			for (var j=0; j<unitData[i].length; j++) {
				if(fpId === unitData[i][j].FloorplanId) {
					sfRange.push(parseInt(unitData[i][j].SQFT));
				}
			}
		}

		// Build Range
		var min = Math.min.apply(Math,sfRange),
				max = Math.max.apply(Math,sfRange),
				sqFootage = (min === max)? numberCommas(min)+' Sq. Ft.' : numberCommas(min)+' - '+numberCommas(max)+' Sq. Ft.';
		
		return sqFootage;
	}

	function createUnitType(unitType) {
		var unitWrapper = $(document.createElement("div")),
				unitDetails = $(document.createElement("div")),
				name = $(document.createElement("div")),
				price = $(document.createElement("div")),
				bedsSpan = $(document.createElement("span")),
				bathsSpan = $(document.createElement("span")),
				bedBathDiv = $(document.createElement("div")),
				availability = $(document.createElement("div")),
				imageWrap = $(document.createElement("div")),
				image = $(document.createElement("img")),
				contactLinkContainer = $(document.createElement("div")),
				contactLink = $(document.createElement("a")),
				magnifyWrap = $(document.createElement("div")),
				magnifySpan = $(document.createElement("span")),
				linksContainer = $(document.createElement("div")),
				bottomContainer = $(document.createElement("div"));

		// Add Classes
		unitWrapper.addClass('unit-wrapper');
		imageWrap.addClass('image-container');	
		image.addClass('floorplan-image');			
		unitDetails.addClass('unit-details');			
		magnifySpan.addClass('icon-magifying');			
		magnifyWrap.addClass('magnify');		
		name.addClass('name');
		price.addClass('price');
		bedBathDiv.addClass('beds-baths');
		price.addClass('price');
		availability.addClass('availability');
		bottomContainer.addClass('bottom-container');
		linksContainer.addClass('links-container');
		contactLinkContainer.addClass('contact');


		// Conditional values (based on availability, type, and other factors).
		var rent = (unitType.MinimumRent === "-1") ? 'Call For Details' : 'Starting at $'+numberCommas(parseInt(unitType.MinimumRent)),
				numberOfBeds = parseInt(unitType.Beds),
				beds = null,
				numberOfBaths = parseInt(unitType.Baths),
				baths = null,
				typeClass = (numberOfBeds === 0) ? 'studio' : int2Str(numberOfBeds).toLowerCase()+'-bedroom',
				imgSrc = themeData.siteURL+'/wp-content/uploads/'+unitType.FloorplanName.replace(' - ','-').replace(/\s+/g, '-').toUpperCase()+'.jpg';  // Strips out spaces

		// Bedrooms (with or without 's'). 
		if(numberOfBeds === 0) { beds = 'Studio'; }
		else if(numberOfBeds === 1) { beds = numberOfBeds+' Bedroom'; }
		else { beds = numberOfBeds+' Bedrooms'; }

		// Bathrooms (with or without 's').
		baths = (numberOfBaths === 1) ? numberOfBaths+' Bathroom' : numberOfBaths+' Bathrooms';

		// Available Units Link (if apartments are available - this triggers lightbox).
		if(unitType.AvailableUnitsCount > 0) {
			var viewUnits = $(document.createElement("div"));
			viewUnits.addClass('view-units');
			viewUnits.html('View Apartments');
			linksContainer.append(viewUnits);
		}

		// Square Footage (if units available)
		if(parseInt(unitType.AvailableUnitsCount) > 0) {
			var sqft = $(document.createElement("div")),
					squareFt = getSquareFootRange(unitType.FloorplanId);

			sqft.addClass('sqft');
			sqft.html(squareFt);
		}

		// Availability Link
		availability.html((parseInt(unitType.AvailableUnitsCount) > 0) ? getAvailabilityDate(unitType.FloorplanId) : 'Contact us for availability');	

		// Contact Link
		contactLink.attr("href", themeData.siteURL+"/contact/");
		// contactLink.attr("href", "mailto:"+fp_email	+"?subject=Unit "+unitType.FloorplanName+" Inquiry");
		contactLink.html('Contact Us');
		contactLinkContainer.append(contactLink);
		linksContainer.append(contactLinkContainer);
		bottomContainer.prepend(availability);
		bottomContainer.append(linksContainer);

		// Assign Elements //
		image.attr('src', imgSrc);
		name.html(unitType.FloorplanName);
		bedsSpan.html(beds);
		bathsSpan.html(baths);
		price.html(rent);

		// Append Elements // 
		unitWrapper.addClass(typeClass);
		magnifyWrap.append(magnifySpan);
		imageWrap.append(image, magnifyWrap);
		bedBathDiv.append(bedsSpan," / ", bathsSpan);
		unitDetails.append(name, bedBathDiv);
		// Add SQ FT div if units available.
		if(parseInt(unitType.AvailableUnitsCount) > 0) { unitDetails.append(sqft); }
		unitDetails.append(price);
		unitWrapper.append(imageWrap, unitDetails, bottomContainer);

		// Add on-click events for lightbox here.
		unitWrapper.on('click', function(e) {
			if($(e.target).hasClass('floorplan-image') || $(e.target).hasClass('view-units')) {
				openLightbox(unitType);
			}
	  });
		
		return unitWrapper;
	}

	function makeDropdown(){
	  $('<select class="mobile-select" />').appendTo('.category-filter');
	  $('.category-filter li').each(function() {
	    var element = $(this);
	    $('<option />', {
	        'value' : element.attr('data-filter'),
	        'data-filter' : element.attr('data-filter'),
	        'text' : element.text()
	    }).appendTo('.category-filter select');
	  });

	  $('.category-filter select').change(function() {
     	var selected = $(this).find(":selected");
    	var filters = selected.data("filter");
	    	if(!selected.hasClass('active')){
	    		$("#floor-plan-results").find(".unit-wrapper").hide();
	    		$("#floor-plan-results").find("div." + filters).show();
	    	}
	  });
	}

	// Below uses UNIT TYPE data (NOT unit data)
	function displayUnitTypes(units){
		var resultsContainer = $("#floor-plan-results"),
				fpCategories = $('#floor-plan-categories');
		for (var i = 0; i < units.length; i++){
	 		var unitType = createUnitType(units[i]);
			resultsContainer.append(unitType);
		}

		// Category Filters
		fpCategories.on('click', 'li', function() {
		  var filters = $(this).data("filter");
	    	if(!$(this).hasClass('active')){
	    		$('#floor-plan-categories').find('li.active').removeClass('active');
	    		$(this).addClass('active');
	    		$("#floor-plan-results").find(".unit-wrapper").hide();
	    		$("#floor-plan-results").find("div." + filters).show();
	    	}
	  });
	  makeDropdown();
	}


  // Build individual category.
  function buildCategory(cat){
		var bedCount = cat[0].Beds,
				category_li = $(document.createElement("li")),
				category_a = $(document.createElement("a")),
				cat_name = null;

		// Determine category name, based off bedroom count.
    switch (bedCount){
      case "0":
        cat_name = "Studio";
        break;
      case "1":
        cat_name = "One Bedroom";
        break;
      case "2":
        cat_name = "Two Bedroom";
        break;
      case "3":
        cat_name = "Three Bedroom";
        break;
      default:
        return false;
        break;
    }

    category_li.attr('data-filter', cat_name.toLowerCase().replace(" ","-"));
    category_li.addClass(cat_name.toLowerCase().replace(" ","-"));
    category_a.append(cat_name);
    category_li.append(category_a);

	  return category_li;
  }

  // Display Categories Sidebar
	function displayCategories() {
		var isActive = true,
				categories_container = $("#floor-plan-categories"); 

		for (var i in unitTypes) {
			var category = buildCategory(unitTypes[i], isActive);
			isActive = false;
			// categories_container.attr('data-filter-group','categories');
			categories_container.append(category);
		}

		// Make "All" category
		var all_category_li = $(document.createElement("li"));
		all_category_li.attr('data-filter','unit-wrapper');
		all_category_li.addClass('active unit-wrapper');
		all_category_li.append('All');
		categories_container.prepend(all_category_li);
	}

	// Creates category index based on number of bedrooms.
	function categoryIndex(numBeds){
		var numBeds = numBeds,
				category = null;

    switch (numBeds){
      case "0":
        category = 0;  // Studio
        break;
      case "1":
        category = 1; // 1 Bed
        break;
      case "2":
        category = 2; // 2 Bed
        break;
      case "3":
        category = 3; // 3 Bed
        break;
      default:
        return false;
        break;
    }

    return category;
	}

  // Filter By Number of Beds
	function filterUnitTypes() {
    for (var i=0; i<floorplanData.length; i++) {
      var typeIndex = categoryIndex(floorplanData[i].Beds);
      if (!(typeIndex in unitTypes)) {
        unitTypes[typeIndex] = [];
      }
      unitTypes[typeIndex].push(floorplanData[i]);
    }
    displayCategories();
	}



	function loopUnitData(floorplanData){
		for (var i=0; i<floorplanData.length; i++) {
			getUnitJSON(floorplanData[i].FloorplanId);
		}
	}

  function determineMobileWidth(){
    isMobileWidth = ($(window).width() < 768);  // Not tablet size!
  }

	// Get Unit Data
	function getUnitJSON(floorPlanID){
		$.ajax({
		    cache:false,
		    url:themeData.siteURL + "/rentCafeAPIConnect.php?requestType=apartmentavailability&floorplanId=" + floorPlanID,
		    dataType:'json',
		    success:function(data){
		        unitData.push(data);
		        typeCount++;
		        if(typeCount == floorplanData.length) {
		        	// Start Search
      		    filterUnitTypes();
      	      displayUnitTypes(floorplanData);
		        }
		    },
		    error:function(error){
		    	console.log('Whoops.  Something is wrong.');
		    }
		});
	}

	// Get Floorplan Data
  function getJSON(){
  	$('#loader').show();
  	$.ajax({
	  	cache:false,
	  	url:themeData.siteURL + "/rentCafeAPIConnect.php",
	  	dataType:'json',
	  	success:function(data){
  	    floorplanData = data;
	  	  loopUnitData(floorplanData);  
	  	},
	  	complete: function(){
	  	  $('#loader').fadeOut();
	  	},
	  	error:function(error){
	  		console.log('Whoops.  Something is wrong.');
	  	}
  	});
  }

  $(document).ready(function(){
    getJSON();
    determineMobileWidth();
    determineLightboxMaxHeight();
  });

  $(window).on({
    resize:function(){
      determineMobileWidth();
      determineLightboxMaxHeight();
    }
  });

}());