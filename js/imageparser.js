function handleFileSelection(file, imageType, ID){//imageType: 0 is for group logo, 1 is for profile picture
	var maxFileSize = 5120000; //5 Megabytes
	if(file.size < maxFileSize){
		uploadImage(file, imageType,ID);
	}else{
		Materialize.toast('Image has exceeded maximum file size. Please select a different image.', 4000);
	}
}

function uploadImage(file, imageType, ID){
	var form_data = new FormData();

	form_data.append('upfile', file);
	form_data.append('ID',ID);
	form_data.append('GROUP_OR_USER',imageType);

	var uploadImageRequest = imageAjaxRequest(form_data);

	uploadImageRequest.done(function(result) {
	 	
	 	if(result[0] == 0){
	 		Materialize.toast(result.substring(1), 4000);
	 	}else{
	 		Materialize.toast('Photo succesfully changed.', 4000);
	 		var imgID = (imageType == 0) ? "#groupLogoPhoto" : "#updateProfPic";

	 		$(imgID).prop('src', result.substring(1) + '?' + Math.random())

	 		$(imgID).css('top','auto');
	 		$(imgID).css('margin-top','auto');
	 	}
	 	

	}).fail(function() {
	    	alert("Unable to update photo. Please check your internet connection");
	});
}

function imageAjaxRequest(form_data){
	var request = $.ajax({
        url: '../ajax/ajax_upload_image.php',
        dataType: 'text',  // what to expect back from the PHP script, if anything
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        type: 'post',
        success: function(response) {
            result = response;
        }
    });

    return request;
}
