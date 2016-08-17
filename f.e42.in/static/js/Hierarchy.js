<script>
	var harr= new Array();

	<?php 
	// Load hierarchy Array
	$no_hierarchy=count($hierarchy_row); 
	$tid=-1;	// Start with new type
	for ($idx=0; $idx<$no_hierarchy; $idx++) {
		// Type
		if ( $hierarchy_row[$idx]['type_id'] != $tid ) {
			$tid=$hierarchy_row[$idx]['type_id'];
			echo "\n\tharr[".$tid."]=new Array();";
			echo "\n\tharr[".$tid."][0]='".$hierarchy_row[$idx]['type']."';";
			$cid="";	// reset Category
		}

		// Category
		if ($hierarchy_row[$idx]['category_id'] != $cid) {
			$cid=$hierarchy_row[$idx]['category_id'];
			echo "\n\tharr[".$tid."][".$cid."]=new Array();";
			echo "\n\tharr[".$tid."][".$cid."][0]='".$hierarchy_row[$idx]['category']."';";
			$scid="";	// reset Sub Category
		}

		// Sub Category
		if ($hierarchy_row[$idx]['subcategory_id'] != $scid) {
			$scid=$hierarchy_row[$idx]['subcategory_id'];
			echo "\n\tharr[".$tid."][".$cid."][".$scid."]=new Array();";
			echo "\n\tharr[".$tid."][".$cid."][".$scid."][0]='".$hierarchy_row[$idx]['subcategory']."';";
		}
	echo "\n";
	}
	echo "\n";
	?>

	// Generic functin to write new option to the select box
	function addHOption(selectbox,text,value)
	{
		var optn = document.createElement("OPTION");
		optn.text = text;
		optn.value = value;
		selectbox.options.add(optn);
	}


	// Reload Categorys
	function reload_categorys(TheForm,tid) {
		//alert("reload_categorys(): Form=["+TheForm+"] tid=["+tid+"]");

		// Disable+clear the Category & Sub category dropdowns
		$('#category_id').fadeOut('fast');
		$('#subcategory_id').fadeOut('fast');
		TheForm.category_id.length=0;
		TheForm.subcategory_id.length=0;
		TheForm.category_id.disabled=true;
		TheForm.subcategory_id.disabled=true;
		addHOption(TheForm.category_id,"-Select Category-", "");

		// Populate the category dropdown
		if (harr[tid]) { 
			for (var i=1;i<harr[tid].length;i++){
				if (harr[tid][i]) {
					addHOption(TheForm.category_id,harr[tid][i][0],i);
				}
			}
		}

		// Enable the category dropdown
		if (TheForm.category_id.length > 1){
			TheForm.category_id.disabled=false;
			$('#category_id').fadeIn('fast');
		}

	}



	// Reload Sub Categorys
	function reload_subcategorys(TheForm,tid,cid) {
		//alert("reload_subcategorys(): Form=["+TheForm+"] tid=["+tid+"]"+"] cid=["+cid+"]");

		// Disable sub category
		$('#subcategory_id').fadeOut('fast');
		TheForm.subcategory_id.length=0;
		TheForm.subcategory_id.disabled=true;
		addHOption(TheForm.subcategory_id,"-Select Sub Category-", "");

		// Populate with the new values
		if (harr[tid][cid]) { 
			for (var i=1;i<harr[tid][cid].length;i++){
				if (harr[tid][cid][i]) {
					addHOption(TheForm.subcategory_id,harr[tid][cid][i][0],i);
				}
			}
		}

		// Enable the sub category dropdown
		if (TheForm.subcategory_id.length > 1) { 
			TheForm.subcategory_id.disabled=false; 
			$('#subcategory_id').fadeIn('fast');
		}
	}
	
	function populateProdName() {
		// Don't do anything if autogen is off 
		if ( $('#subcategory_id option:selected').val() == "") return;

		var name=$('#subcategory_id option:selected').text();
		//if ($('#brand option:selected').val() !== "") name=$('#brand option:selected').text()+' '+name;
		if ($('#brand').val() !== "") name=$('#brand').val()+' '+name;
		if ($('#quantity').val() !== "") { 
			name=name+' '+$('#quantity').val(); 
			if ($('#quantity_type option:selected').val() !== "") name=name+' '+$('#quantity_type option:selected').val(); 
		}
		$('#name').val($.trim(name));
	}

</script>
