<?php
	include_once("../databasehelper/databasehelper.php");


	header('Content-Type: text/plain; charset=utf-8');
	$isGroup = mysql_escape_string($_POST['GROUP_OR_USER']) == "0";

	$directory = ($isGroup) ? "grouplogos" : "profilepics";

	$ID = mysql_escape_string($_POST['ID']); //if a group this is the groupID if a user this is the userID;
	$name = ($isGroup) ? "group".$ID."" : "user".$ID."";


	try {
	    
	    // Undefined | Multiple Files | $_FILES Corruption Attack
	    // If this request falls under any of them, treat it invalid.
	    if (
	        !isset($_FILES['upfile']['error']) ||
	        is_array($_FILES['upfile']['error'])
	    ) {
	        throw new RuntimeException('Invalid parameters.');
	    }

	    // Check $_FILES['upfile']['error'] value.
	    switch ($_FILES['upfile']['error']) {
	        case UPLOAD_ERR_OK:
	            break;
	        case UPLOAD_ERR_NO_FILE:
	            throw new RuntimeException('No file sent.');
	        case UPLOAD_ERR_INI_SIZE:
	        case UPLOAD_ERR_FORM_SIZE:
	            throw new RuntimeException('Group photo exceeded filesize limit.');
	        default:
	            throw new RuntimeException('Unknown errors.');
	    }

	    // You should also check filesize here. Max is 5MB but size is reduced 
	    if ($_FILES['upfile']['size'] > 5120000) {
	        throw new RuntimeException('Group photo exceeded filesize limit.');
	    }

	    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
	    // Check MIME Type by yourself.
	    $allowedTypes = array(IMAGETYPE_PNG, IMAGETYPE_JPEG);
		$detectedType = exif_imagetype($_FILES['upfile']['tmp_name']);
		$error = !in_array($detectedType, $allowedTypes);
	    if ($error) {
	        throw new RuntimeException('Invalid file format.');
	    }

	    // You should name it uniquely.
	    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
	    // On this example, obtain safe unique name from its binary data.
	    $ext = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
	    $fileName = $name.".".$ext."";
	    $fileURL = "../".$directory."/".$fileName."";

	    if($ext == "png"){ //if current image is a jpg and new image is png it wont overwrite because technically different file name so delete old version jpg
	    	if(file_exists("../".$directory."/".$name.".jpg")){
	    		unlink("../".$directory."/".$name.".jpg");
	    	}
	    }else{
	    	if(file_exists("../".$directory."/".$name.".png")){
	    		unlink("../".$directory."/".$name.".png");
	    	}
	    }

	    if (!compress_image(
	        $_FILES['upfile']['tmp_name'],$fileURL,50
	    )) {
	        throw new RuntimeException('Failed to move uploaded file.');
	    }

	    $mysqli = getDB();

		    if($isGroup){
		    	updateGroupLogo($mysqli,$ID,$fileName);
		    }else{
		    	$profile = getUserProfileFromDB($mysqli,$ID);
		    	$profile->profPicURL = $fileName;
		    	updateProfile($mysqli,$profile);
		    }

	    $mysqli->close();

	    echo "1".$fileURL."";

	} catch (RuntimeException $e) {

	    echo "0".$e->getMessage()."";

	}

	function compress_image($source_url, $destination_url, $quality) {

		$info = getimagesize($source_url); 

		if ($info['mime'] == 'image/jpeg') 
			$image = imagecreatefromjpeg($source_url); 

		elseif ($info['mime'] == 'image/gif') 
			$image = imagecreatefromgif($source_url); 

		elseif ($info['mime'] == 'image/png') 
			$image = imagecreatefrompng($source_url); 
		
		return imagejpeg($image, $destination_url, $quality); 

	}


?>