<?php 

include("model.php");
include("db.php");

// All the GO stuff
switch ($DO) {
	// All the do stuff
	case "book":
		do_check_rooms();
		break;
}

?>
