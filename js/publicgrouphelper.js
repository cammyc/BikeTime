function showUpcomingRideList(userID){

	var today = new Date();
	var startDate = today.getFullYear() + "/" + ('0' + (today.getMonth()+1)).slice(-2) + "/" + ('0' + today.getDate()).slice(-2);
	var endDate = (today.getFullYear()+1) + "/" + ('0' + (today.getMonth()+1)).slice(-2) + "/" + ('0' + today.getDate()).slice(-2);

	var args = {
		ajaxURL: "../ajax/ajax-single-group-rides.php",
		startDate: startDate,
		endDate: endDate,
		groupID: groupID,
		userID: userID,
		groupRides: true,//only want group rides
        memberRides: false,
		timezone: jstz.determine().name()
	};

	var getRidesRequest = getRides(args);

		getRidesRequest.done(function(result) { //CLEAN THIS THE FUCK UP, should date range be same as map?
			//console.log(result)
			$('#rideListSpinner').hide();
	    var rides = JSON.parse(result);

	     for(var i = 0; i<rides.length;i++){//this is to help sort the rides by soonest to furthest away
	     	var startDate = Number(rides[i]['startDate']);
        	var repeatInterval = Number(rides[i]['repeatInterval']);
        	var repeatStartDate = getIntervalAdjustedDate(startDate,repeatInterval,args['startDate']);
        	rides[i]['newStartDate'] = repeatStartDate;
	     }

	     rides.sort(function(a,b) { return parseFloat(new Date(a.newStartDate).getTime()) - parseFloat(new Date(b.newStartDate).getTime()) } );

        for(var i = 0; i<rides.length;i++){

        	var startDate = Number(rides[i]['startDate']);
        	var repeatStartDate = rides[i]['newStartDate'];
        	var repeatEndDate = null;


      		var tBody=document.getElementsByTagName("tbody").item(0);
		        var row=document.createElement("tr");
			        var cell1 = document.createElement("td");
			        var cell2 = document.createElement("td");
			        var cell3 = document.createElement("td");
			        var cell4 = document.createElement("td");

				        var textnode1=document.createTextNode(rides[i]['title']);
				        var textnode2=document.createTextNode(rides[i]['level']);
				        var textnode3=document.createTextNode(adjustTimeFormat(rides[i]['startTime']));
				        var textnode4=document.createTextNode(formatDate(repeatStartDate,0,repeatEndDate));//set interval to 0 so that just start date is returned, dont need enddate

			        cell1.appendChild(textnode1);
			        cell2.appendChild(textnode2);
			        cell3.appendChild(textnode3);
			        cell4.appendChild(textnode4);

		        row.appendChild(cell1);
		        row.appendChild(cell2);
		        row.appendChild(cell3);
		        row.appendChild(cell4);
	        tBody.appendChild(row);
      	}

	}).fail(function (jqXHR, textStatus, errorThrown){
           if(!aborted){
           	alert("Unable to retrieve rides. Please check your internet connection.")
           }
		});
}

function updateMessageBoard(groupID, timeNow){//set to refresh every x seconds...

	 var args = {
	 	groupID: groupID,
	 	ajaxURL: '../ajax/ajax_get_group_messages.php'
	 }

	 var getMessagesRequest = ajaxRequest(args);

 		getMessagesRequest.done(function(result) {
 			//console.log(result)
 			$("#msgBoardList").empty();

		    var messages = JSON.parse(result);
		    messageArray =JSON.parse(result);

		    if(messages.length == 0){
		    	$('#msgBoardList').append("<center><h5>No messages have been sent.</h5></center>");
		    }

	        for(var i = 0; i < messages.length; i++){
				var m = messages[i];
				var picURL = (m['user']['profPicURL'] != '') ? m['user']['profPicURL'] : 'images/noProfPic.png';

				var isAdmin = (m['userIsAdmin'] == 1) ? "Admin • ": "";

				var img = "<a href='../profile/?id=" + m['user']['userID'] + "'><img class='msgImage' src='../profilepics/" + picURL + "'/></a>";
				var commentImg = "<a href='../profile/?id=" + m['user']['userID'] + "'><img class='commentMsgImage' src='../profilepics/" + picURL + "'/></a>";


				var name = m['user']['firstName'] + ' ' + m['user']['lastName'];

				var stamp = Number(m['timestamp']); // dont need to convert because both times being compared are in UTC
				var name = "<a href='../profile/?id=" + m['user']['userID'] + "' class='nameLink'>" + name + "</a>"
				var message = "<p class='message noMargin' >" + m['message'] + "</p>"
				var msgTimestamp = "<p class='msgTimestamp noMargin'>" + isAdmin + timeSincePost(stamp, timeNow) + "</p>";
				//var postActionDetails =  "<p class='msgTimestamp noMargin'> 5 Likes • "+ m['numComments'] +" Comments</p>";
				var postActionDetails =  "<p class='msgTimestamp noMargin'>"+ m['numComments'] +" Comments</p>";
				//var postActions = '<div class="postActions"><a class="bLike waves-effect waves-blue btn-flat"><i class="mdi-social-whatshot"></i></a> <a class="bComment waves-effect waves-blue btn-flat"><i class="mdi-communication-messenger"></i></a></div>';
				var postActions = '<div class="postActions"><a class="bComment waves-effect waves-blue btn-flat"><i class="mdi-communication-messenger"></i></a></div>';
				
				var spinner = '<center><div class="spinner"><div class="preloader-wrapper small active"><div class="spinner-layer spinner-blue-only"><div class="circle-clipper left"><div class="circle"></div></div><div class="gap-patch"><div class="circle"></div></div><div class="circle-clipper right"><div class="circle"></div></div></div></div></center>';

				var writeComment = '<div class="writeComment">'+spinner+'<ul class="commentList"></ul>'+commentImg+'<input class="commentInput" placeholder="Write a comment..." type="text" /></div>';

				$('#msgBoardList').append("<li class='msgListItem card'><div class='paddingDiv'>" + img + name + msgTimestamp + message + postActionDetails + postActions + "</div>" + writeComment + "</li>");
			}

		}).fail(function() {
			if(!aborted){
		    	alert("Unable to retrieve messages. Please check your internet connection.");
			}
		});

}

function postComment(_messageID, _userID, _comment, _inputField, _commentList, timeNow){
	var args = {
	 	messageID: _messageID,
	 	userID: _userID,
	 	comment: _comment,
	 	ajaxURL: '../ajax/ajax_post_group_message_comment.php'
	}

	var postCommentRequest = ajaxRequest(args);

	postCommentRequest.done(function(result) {
	 	
	 	if(result == 1){
	 		$(_inputField).val('');
	 		getComments(_messageID, _commentList, timeNow);
	 	}
	   
	}).fail(function() {
		if(!aborted){
	    	alert("Unable to post comment. Please check your internet connection.");
		}
	});

}

function removeMember( _groupID, _userID){
	var args = {
	 	userID: _userID,
	 	groupID: _groupID,
	 	ajaxURL: '../ajax/ajax_remove_group_member.php'
	}

	var removeMemberRequest = ajaxRequest(args);

	removeMemberRequest.done(function(result) {
	 	
	 	if(result == 1){
	 		location.reload();
	 	}
	   
	}).fail(function() {
		if(!aborted){
	    	alert("Unable to leave group. Please check your internet connection.");
		}
	});

}

function getComments(_messageID, uList, timeNow){
	var args = {
	 	messageID: _messageID,
	 	ajaxURL: '../ajax/ajax_get_group_message_comments.php'
	}

	var getCommentsRequest = ajaxRequest(args);

		getCommentsRequest.done(function(result) {

			uList.closest('li').find('.spinner').hide();
		 	
		 	var comments = JSON.parse(result);

		 	uList.empty();

		 	for (var i = 0; i<comments.length; i++) {
		 		var c = comments[i];

		 		var picURL = (c['user']['profPicURL'] != '') ? c['user']['profPicURL'] : 'images/noProfPic.png';
				var commentImg = "<a href='../profile/?id=" + c['user']['userID'] + "'><img class='commentImage' src='../profilepics/" + picURL + "'/></a>";


				var name = c['user']['firstName'] + ' ' + c['user']['lastName'];

				var stamp = Number(c['timestamp']); // dont need to convert because both times being compared are in UTC
				var name = "<a href='../profile/?id=" + c['user']['userID'] + "' class='commentNameLink'>" + name + "</a>"
				var comment = "<p class='commentText noMargin' >"+ name + c['comment'] + "</p>"
				var commentTimestamp = "<p class='commentTimestamp noMargin'>"+timeSincePost(stamp, timeNow) + "</p>";

		 		$(uList).append("<li class='commentListItem'>" + commentImg + comment + commentTimestamp + "</li>");
		 	};
		   
		}).fail(function() {
			if(!aborted){
		    	alert("Unable to post comment. Please check your internet connection.");
			}
		});

}


function sendMessage(timeNow){
	 var args = {
	 	groupID: groupID,
	 	userID: userID,
	 	message: $('#message').val(),
	 	ajaxURL: '../ajax/ajax_send_message_group.php'
	 }

	 var sendMessageRequest = ajaxRequest(args);

 		sendMessageRequest.done(function(result) {
 			//console.log(result)
		   if(result == 1){
		   		updateMessageBoard(groupID,timeNow);
		   }else{
		   		
		   }

		}).fail(function() {
			if(!aborted){
		    	alert("Unable to send message. Please check your internet connection.");
			}
		});

}


function ajaxRequest(args){
	var request = $.ajax({
        url: args['ajaxURL'],
        data: args,
        success: function(response) {
            result = response;
        }
    });

    window.onbeforeunload = confirmExit;
  	function confirmExit(){
  			aborted = true;
		}

    return request;
}


		function timeSincePost(timeOfPost,now){
			var delta = Math.abs(timeOfPost - now);


			var minutes = Math.floor(delta / 60);

			if(minutes < 1){
				return 'Just now.'
			}

			if(minutes < 60){
				var suffix = (minutes == 1) ? '' : 's';
				return minutes + ' min' + suffix + '';
			}

			var hours = Math.floor(delta / 3600);

			if(hours < 24){
				var suffix = (hours == 1) ? '' : 's';
				return hours + ' hour' + suffix + ''
			}

			var days = Math.floor(delta / 86400);
					var suffix = (days == 1) ? '' : 's';
			return days + ' day' + suffix + '';
		}
