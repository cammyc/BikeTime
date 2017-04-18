function addFriend(args){
		 var addFriendRequest = ajaxRequest(args);

	 		addFriendRequest.done(function(result) {
			    if(result != 2){//comparing to 1 and not using true/false boolean because php doesn't play nice with them
			    	$("#buttonText").text("Request Pending");
			    	friendshipStatus = 0;//need to update global variable, request is pending
			    }else{
			    	alert("Unable to add friend. Please check your internet connection.");
			    }

			}).fail(function() {
			    alert("Unable to add friend. Please check your internet connection.");
			});
	}

function getFriendshipStatus(args){
		 var getFriendshipStatusRequest = ajaxRequest(args);

	 		getFriendshipStatusRequest.done(function(result) {
	 			friendshipStatus = result;//this is a global variable, its being updated, it is set in the profile/index.php page
	 			$("#addFriend").show().css("display","inline-block");//need to add css part or wont display properly
			    if(result == 2){//no request sent, show add friend button
			    	$("#buttonText").text("Add Friend");
			    }else if (result == 1){
			    	$("#buttonText").text("Remove Friend");
			    }else if (result == 0){
			    	$("#buttonText").text("Request Pending");
			    }else{
			    	$("#addFriend").hide();
			    }
			}).fail(function() {
				$("#addFriend").hide();
			});
		return status;
	}

function removeFriend(args){
	 var removeFriendRequest = ajaxRequest(args);

 		removeFriendRequest.done(function(result) {
		    if(result == 1){
		    	friendshipStatus = 2; //no longer friends
		    	$("#buttonText").text("Add Friend");
		    }else{
		    	alert("Unable to remove friend. Please check your internet connection.");
		    }

		}).fail(function() {
		    alert("Unable to remove friend. Please check your internet connection.");
		});
	}

function respondToFriendRequest(_fID, _acceptDecline, _ajaxURL, list, userID){
	args = {
		friendshipID: _fID,
		acceptDecline: _acceptDecline,
		ajaxURL: _ajaxURL
	}
	var acceptFriendshipRequest = ajaxRequest(args);

			acceptFriendshipRequest.done(function(result) {

		    	getFriendRequests(userID,'../ajax/ajax_get_friend_requests.php',list)

		}).fail(function() {
		    alert("Unable to respond to friend request. Please check your internet connection.");
		});
	}

function getFriendRequests(_userID, _ajaxURL, list){
	args = {
		userID: _userID,
		ajaxURL: _ajaxURL
	}
	var getFriendRequestsRequest = ajaxRequest(args);

			getFriendRequestsRequest.done(function(result) {
		    
		    var friendRequests = JSON.parse(result);

		 	updateFriendRequests(friendRequests,list);

		}).fail(function() {
		    alert("Unable to accept friend request. Please check your internet connection.");
		});
	}

function getFriends(_userID, _ajaxURL, list){
	args = {
		userID: _userID,
		ajaxURL: _ajaxURL
	}
	var getFriendsRequest = ajaxRequest(args);

			getFriendsRequest.done(function(result) {
		    
		    var friends = JSON.parse(result);

		 	updateFriendsList(friends,list);

		}).fail(function() {
		    alert("Unable to accept friend request. Please check your internet connection.");
		});
	}

function getGroups(_userID, _ajaxURL, list){
	args = {
		userID: _userID,
		ajaxURL: _ajaxURL
	}
	var getGroupsRequest = ajaxRequest(args);

			getGroupsRequest.done(function(result) {
		    
		    var groups = JSON.parse(result);

		 	updateGroupsList(groups,list);

		}).fail(function() {
		    alert("Unable to accept friend request. Please check your internet connection.");
		});
	}

function updateFriendRequests(friendRequests, list){
	list.empty();
	for(var i = 0; i<friendRequests.length; i++){
		var r = friendRequests[i];
		var imgURL = (r['profPicURL'] == "") ? '../images/noProfPic.png' : r['profPicURL'];
		var image = "<a href='../profile/?id="+r['userID']+"'><img class='alertImage' src='../profilepics/"+imgURL+"'/></a>";
		var text = "<p class='alertText'>"+r['firstName']+" "+r['lastName']+" sent you a friend request.</p>";
		var acceptRequest = '<a class="acceptRequest waves-effect waves-dark btn-flat grey lighten-4 light-blue-text"><i class="mdi-navigation-check"></i></a>';
		var declineRequest =  ' <a class="declineRequest waves-effect waves-dark btn-flat grey lighten-4 light-blue-text"><i class="mdi-navigation-close"></i></a>';
		var hiddenInput = '<input id="fID" type="hidden" value="'+r['tempVar']+'"'
		list.append("<li>"+image+text+"<div class='acceptDecline'>"+acceptRequest+declineRequest+hiddenInput+"</div></li>");
	}

	if(friendRequests.length == 0){
		list.append("No Alerts");
	}
}

function updateFriendsList(friends, list){
	list.empty();
	for(var i = 0; i<friends.length; i++){
		var r = friends[i];
		var imgURL = (r['profPicURL'] == "") ? '../images/noProfPic.png' : '../profilepics/'+r['profPicURL'];
		var listItem = "<li><a title='"+r['firstName']+" "+r['lastName']+"' href='../profile/?id="+r['userID']+"'><div class='initialContainer'><p class='friendsInitial'>"+r['firstName'].charAt(0).toUpperCase()+"."+r['lastName'].charAt(0).toUpperCase()+"</p></div><div class='userImageContainer'><img class='riderListImg' src='"+imgURL+"'/></div></a></li>"
		list.append(listItem);
	}

	if(friends.length == 0){
		list.append("User has no friends... Yet!");
	}
}

function updateGroupsList(groups, list){
	list.empty();
	for(var i = 0; i<groups.length; i++){
		var g = groups[i];
		var logoURL = '../grouplogos/' + g['logoURL'];
		var listItem = "<li><a title='"+g['name']+"' href='../group/?id="+g['groupID']+"'><img class='groupLogo' src='"+logoURL+"'/> <p class='groupName'>"+g['name']+"<br>Sport: "+g['sport']+"</p></a></li>"
		list.append(listItem);
	}

	if(groups.length == 0){
		list.append("User is not in any groups.");
	}
}


function ajaxRequest(args){
	return $.ajax({
        url: args['ajaxURL'],
        data: args,
        success: function(response) {
            result = response;
        }
    });
}

function getArgs(_userID1, _userID2, _ajaxURL){
	//userID1 will always be less then userID2 to make query's easier
	args = {
		userID1: (_userID1 < _userID2) ? _userID1 : _userID2,
		userID2: (_userID1 < _userID2) ? _userID2 : _userID1, 
		ajaxURL: _ajaxURL
	}
	return args;
}

function getArgsAddFriend(_userID1, _userID2, _requesterID, _ajaxURL){
	//userID1 will always be less then userID2 to make query's easier
	args = {
		userID1: (_userID1 < _userID2) ? _userID1 : _userID2,
		userID2: (_userID1 < _userID2) ? _userID2 : _userID1, 
		requesterID: _requesterID,
		ajaxURL: _ajaxURL
	}
	return args;
}