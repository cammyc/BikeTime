function getMembers(_groupID,_accepted, uList, isAdmin){
	args = {
		groupID: _groupID,
		accepted: _accepted,//1 means accepted, 0 is pending
		ajaxURL: '../ajax/ajax_get_group_members.php'
	}
	var getMembersRequest = ajaxRequest(args);

			getMembersRequest.done(function(result) {
				if(_accepted == 1){
					acceptedGroupMembers = JSON.parse(result);
					$('#numMembers').text(acceptedGroupMembers.length);
				}else{
					pendingGroupMembers = JSON.parse(result);
				}
				uList.empty();
		    	showMembers(result,uList,isAdmin,_accepted);

		}).fail(function() {
		    alert("Unable to get group members. Please check your internet connection.");
		});
	}

function removeMember(_groupID,_userID, uList){
	args = {
		groupID: _groupID,
		userID: _userID,
		ajaxURL: '../ajax/ajax_remove_group_member.php'
	}
	var removeMemberRequest = ajaxRequest(args);

			removeMemberRequest.done(function(result) {
				if(result == 1){
					if(uList == null){
						location.reload();//this is for the main group page, not MEMBERS page
					}else{
						Materialize.toast('Member Removed', 4000) // 4000 is the duration of the toast
		    			getMembers(_groupID,1,uList);//1 means accepted members
					}
					
				}else{
					alert("Unable to remove member. Please check your internet connection.");
				}

		}).fail(function() {
		    alert("Unable to remove member. Please check your internet connection.");
		});
	}

function respondToRequest(_groupID,_userID,_isAccepted){
	args = {
		groupID: _groupID,
		userID: _userID,
		isAccepted: _isAccepted,
		ajaxURL: '../ajax/ajax_respond_to_group_member_request.php'
	}
	var respondToRequestReq = ajaxRequest(args);

			respondToRequestReq.done(function(result) {
				if(result == 1){
					Materialize.toast('Member Accepted', 4000) // 4000 is the duration of the toast
					getMembers(_groupID,0,$('#pendingMemberContainer'),true) //refresh pending members
					getMembers(_groupID,1,$('#memContainer'),true) //refresh accepted members
				}else if (result == 2){
					Materialize.toast('Member Declined', 4000) // 4000 is the duration of the toast
					getMembers(_groupID,0,$('#pendingMemberContainer'),true) //refresh pending members

				}else{
					alert(result);
				}

		}).fail(function() {
		    alert("Unable to respond to request. Please check your internet connection.");
		});
	}

function makeMemberAdmin(_groupID,_userID, uList){
	args = {
		groupID: _groupID,
		userID: _userID,
		ajaxURL: '../ajax/ajax_make_member_admin.php'
	}
	var makeMemberAdminRequest = ajaxRequest(args);

			makeMemberAdminRequest.done(function(result) {
				if(result == 1){
					getMembers(_groupID,1,$('#memContainer'),true) //refresh accepted members
					Materialize.toast('Member is now Admin', 4000) // 4000 is the duration of the toast
				}else{
					alert("Unable to remove member. Please check your internet connection.");
				}

		}).fail(function() {
		    alert("Unable to get remove membsr. Please check your internet connection.");
		});
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

function showMembers(result, uList, isAdmin, accepted){
	var members = JSON.parse(result);
	for (var i = 0; i < members.length; i++) {
		var m = members[i];

		var url = (m['profile']['profPicURL'] != "") ? m['profile']['profPicURL'] : 'images/noProfPic.png';
		var pic = "<a href='../profile/?id="+m['profile']['userID']+"'><img id='profPic' src='../profilepics/"+url+"' /></a>"

		if(accepted == 1){
			var removeMember = (isAdmin) ? '<a id="removeMember" class="removeMember waves-effect waves-light btn" title="Remove Member"><i class="mdi-navigation-close"></i></a>' : '';
			var adminOrRemove = (m['isAdmin'] == true) ? "<p class='truncate' id='memAdmin'>Admin</p>" : removeMember;
			var name = "<p class='truncate' id='memName'>"+m['profile']['firstName']+" "+m['profile']['lastName']+"</p>"
			var makeAdmin =  (isAdmin) ? '<a id="makeAdmin" class="makeAdmin waves-effect waves-light btn" title="Make User Admin"><i class="mdi-social-person"></i></a>' : '';
			uList.append("<li id='memBlock'>"+pic+adminOrRemove+name+makeAdmin+"</li>");
		}else{
			var declineMember = '<a id="declineMember" class="declineMember waves-effect waves-light btn" title="Decline Member"><i class="mdi-navigation-close"></i></a>';
			var acceptMember = '<a id="acceptMember" class="acceptMember waves-effect waves-light btn" title="Accept Member"><i class="mdi-navigation-check"></i></a>';
			var name = "<p class='truncate' id='memName'>"+m['profile']['firstName']+" "+m['profile']['lastName']+"</p>"
			uList.append("<li id='memBlock'>"+pic+declineMember+acceptMember+name+"</li>");
		}
		
	};
}
