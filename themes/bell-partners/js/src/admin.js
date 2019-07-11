function toggle_flex() {
	console.log('testing');
	if(!$('.acf-flexible-content .values .layout').hasClass('-collapsed')){
		$('.acf-flexible-content .values .layout').addClass('-collapsed');
	}	
	alert('close');
}