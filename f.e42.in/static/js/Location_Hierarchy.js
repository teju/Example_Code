<script>

var harr= new Array();

	<?php 
	// Load hierarchy Array
	$no_hierarchy=count($hierarchy_row); 
	$tid=-1;	// Start with new type
	for ($idx=0; $idx<$no_hierarchy; $idx++) {
		// Type
		if ( $hierarchy_row[$idx]['state_id'] != $tid ) {
			$tid=$hierarchy_row[$idx]['state_id'];
			echo "\n\tharr[".$tid."]=new Array();";
			echo "\n\tharr[".$tid."][0]='".$hierarchy_row[$idx]['state_name']."';";
			$cid="";	// reset Category
		}

		// Category
		if ($hierarchy_row[$idx]['city_id'] != $cid) {
			$cid=$hierarchy_row[$idx]['city_id'];
			echo "\n\tharr[".$tid."][".$cid."]=new Array();";
			echo "\n\tharr[".$tid."][".$cid."][0]='".$hierarchy_row[$idx]['city_name']."';";
			$scid="";	// reset Sub Category
		}

		// Sub Category
		if ($hierarchy_row[$idx]['area_id'] != $scid) {
			$scid=$hierarchy_row[$idx]['area_id'];
			echo "\n\tharr[".$tid."][".$cid."][".$scid."]=new Array();";
			echo "\n\tharr[".$tid."][".$cid."][".$scid."][0]='".$hierarchy_row[$idx]['area_name']."';";
		}
	echo "\n";
	}
	echo "\n";
	?>
	
	// Reload Citys
	function reload_citys(TheForm,tid) {
		//alert("reload_citys(): Form=["+TheForm+"] tid=["+tid+"]");

		// Disable+clear the City & Area dropdowns
		$('#city_id').fadeOut('fast');
		$('#area_id').fadeOut('fast');
		TheForm.city_id.length=0;
		TheForm.area_id.length=0;
		TheForm.city_id.disabled=true;
		TheForm.area_id.disabled=true;
		addHOption(TheForm.city_id,"-Select City-", "");

		// Populate the City dropdown
		if (harr[tid]) { 
			for (var i=1;i<harr[tid].length;i++){
				if (harr[tid][i]) {
					addHOption(TheForm.city_id,harr[tid][i][0],i);
				}
			}
		}

		// Enable the City dropdown
		if (TheForm.city_id.length > 1){
			TheForm.city_id.disabled=false;
			$('#city_id').fadeIn('fast');
		}

	}


	// Reload Locations
	function reload_areas(TheForm,tid,cid) {
		//alert("reload_area_ids(): Form=["+TheForm+"] tid=["+tid+"]"+"] cid=["+cid+"]");

		// Disable Area
		$('#area_id').fadeOut('fast');
		TheForm.area_id.length=0;
		TheForm.area_id.disabled=true;
		addHOption(TheForm.area_id,"-Select Area-", "");

		// Populate with the new values
		if (harr[tid][cid]) { 
			for (var i=1;i<harr[tid][cid].length;i++){
				if (harr[tid][cid][i]) {
					addHOption(TheForm.area_id,harr[tid][cid][i][0],i);
				}
			}
		}

		// Enable the Area dropdown
		if (TheForm.area_id.length > 1) { 
			TheForm.area_id.disabled=false; 
			$('#area_id').fadeIn('fast');
		}
	}

	// Generic functin to write new option to the select box
	function addHOption(selectbox,text,value)
	{
		var optn = document.createElement("OPTION");
		optn.text = text;
		optn.value = value;
		selectbox.options.add(optn);
	}
</script>