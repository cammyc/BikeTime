
        function joinRide(_userID,_rideID,_intervals, _getRiders,fromHomepage){
            var args = {
              userID: _userID,
              rideID: _rideID,
              intervals: _intervals,
              ajaxURL: fromHomepage + 'ajax/ajax-join-ride.php'
            }

           var JoinRideRequest = ajaxJoinRide(args);

              JoinRideRequest.done(function(result) {
                console.log(result)
                 if(result == 1){
                   $('#bJoinRide').hide();
                   $('#bLeaveRide').show();
                   Materialize.toast('Joined Ride', 4000);

                 }else if (result == 2){
                   $('#bJoinRide').show();
                   $('#bLeaveRide').hide();
                   Materialize.toast('Left Ride', 4000);
                 }

                 if(result != 0 && _getRiders){
                  getRiders(args);
                 }

              }).fail(function() {
                  alert("Unable to join ride. Please check your internet connection.");
              });
        }

        function ajaxJoinRide(args){
          return $.ajax({
                url: args['ajaxURL'],
                data: args,
                success: function(response) {
                    result = response;
                }
            });
        }

        function hasUserJoinedRide(_userID,_rideID,_intervals,fromHomepage){
           var args = {
              userID: _userID,
              rideID: _rideID,
              intervals: _intervals,
              ajaxURL: fromHomepage + 'ajax/ajax_has_user_joined_ride.php'
            }

           var hasUserJoinedRideRequest = ajaxHasUserJoinedRide(args);

              hasUserJoinedRideRequest.done(function(result) {
                console.log(result)
                 if(result == 2){
                   $('#bJoinRide').hide();
                   $('#bLeaveRide').show();
                 }else if (result == 1){
                   $('#bJoinRide').show();
                   $('#bLeaveRide').hide();
                 }else{
                   alert("Unable to join ride. Please check your internet connection.");
                 }

              }).fail(function() {
                  alert("Unable to join ride. Please check your internet connection.");
              });
        }

        function ajaxHasUserJoinedRide(args){
          return $.ajax({
                url: args['ajaxURL'],
                data: args,
                success: function(response) {
                    result = response;
                }
            });
        }

         function getRiders(args){
           var getRidersRequest = ajaxGetRiders(args);

              getRidersRequest.done(function(result) {
                console.log(result)
                var list = $("#ridersList");
                list.empty();

                var riders = JSON.parse(result);

                riders.forEach(function(r) {
                  var imgURL = ($.trim(r['profPicURL']) == '') ? "../images/noProfPic.png" : "../profilepics/" + r['profPicURL'];
                  var string = "<li><a title='" + r['firstName'] + " " + r['lastName'] + "' href='../profile/?id="+ r['userID'] +"'><div class='riderListImageContainer'><img class='riderListImg' src='" + imgURL + "'/></div></a></li>";
                  list.append(string);
                
                });

                if(riders.length == 0){
                  $('#ridersList').append("<li>No riders have joined this ride yet...</li>");
                }

              }).fail(function() {
                  alert("Unable to retrieve riders. Please check your internet connection.");
              });
        }

         function ajaxGetRiders(args){
          return $.ajax({
                url: '../ajax/ajax-update-joined-ride.php',
                data: args,
                success: function(response) {
                    result = response;
                }
            });
        }

        function getRideArgs(){//only use this function on ride/index.php page
          var args = {
            userID: userID,
            rideID: rideID,
            intervals: intervals,
            ajaxURL: '../ajax/ajax-join-ride.php'
          }
          return args;
        }

        function getMessages(args){
            return $.ajax({
                  url: args['ajaxURL'],
                  data: args,
                  success: function(response) {
                      result = response;
                  }
              });
          }

        function updateMessageBoard(_rideID, _intervals, timeNow){//set to refresh every x seconds...

          $("#msgBoardList").empty();

           var args = {
            rideID: _rideID,
            intervals: _intervals,
            ajaxURL: '../ajax/ajax_get_ride_messages.php'
           }

           var getMessagesRequest = getMessages(args);

              getMessagesRequest.done(function(result) {
                //console.log(result)
                  var messages = JSON.parse(result);

                    for(var i = 0; i < messages.length; i++){
                  var m = messages[i];
                  var picURL = (m['user']['profPicURL'] != '') ? m['user']['profPicURL'] : 'images/noProfPic.png';

                  var isAdmin = (m['userCreatedRide'] == 'true') ? "Ride Admin • ": "";

                  var img = "<a href='../profile/?id=" + m['user']['userID'] + "'><div class='msgImageContainer'><img class='msgImage' src='../profilepics/" + picURL + "'/></div></a>";

                  var name = m['user']['firstName'] + ' ' + m['user']['lastName'];

                  var stamp = Number(m['timestamp']); // dont need to convert because both times being compared are in UTC

                  var message = "<p class='message noMargin' > <a href='../profile/?id=" + m['user']['userID'] + "' class='nameLink'>" + name + "</a> " + m['message'] + "</p>"
                  var msgTimestamp = "<p class='msgTimestamp noMargin'>" + isAdmin + timeSincePost(stamp, timeNow) + "</p>";

                  $('#msgBoardList').append("<li class='msgListItem'>" + img + message + msgTimestamp + "</li>");
                }

                if(messages.length == 0){
                  $('#msgBoardList').append("<li class='noMsgSent'>No messages have been sent...</li>");
                }

              }).fail(function() {
                  alert("Unable to retrieve messages. Please check your internet connection.");
              }

              );
        }

        function timeSincePost(timeOfPost,now){
          var delta = Math.abs(timeOfPost - now);


          var minutes = Math.floor(delta / 60);

          if(minutes < 1){
            return 'Just now'
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

        function sendMessage(_rideID,_intervals, _userID, _timeNow){
           var args = {
            rideID: _rideID,
            intervals: _intervals,
            userID: _userID,
            message: $('#inputMessage').val(),
            ajaxURL: '../ajax/ajax_send_message_ride.php'
           }

           var sendMessageRequest = ajaxSendMessage(args);

              sendMessageRequest.done(function(result) {
                //console.log(result)
                 if(result == 1){
                  $('#inputMessage').val('');
                     updateMessageBoard(_rideID,_intervals, _timeNow);
                 }else{
                    
                 }

              }).fail(function() {
                  alert("Unable to send message. Please check your internet connection.");
              });

        }

        function ajaxSendMessage(args){
          return $.ajax({
                url: args['ajaxURL'],
                data: args,
                success: function(response) {
                    result = response;
                }
            });
        }

        function updateRide(_userID, _rideID, _title, _rideType, _date, _rideLevel, _startTime, _endTime, _isAllRides, _dayDifference, _repeatStartDate){
           var args = {
            userID: _userID,
            rideID: _rideID,
            rideTitle: _title,
            rideType: _rideType,
            startDate: _date,
            rideLevel: _rideLevel,
            startTime: _startTime,
            endTime: _endTime,
            isAllRides: _isAllRides,
            dayDifference: _dayDifference,
            originalStartDate: _repeatStartDate,
            ajaxURL: '../ajax/ajax_update_ride.php'
           }

           var updateRideRequest = ajaxRequest(args);

              updateRideRequest.done(function(result) {
                //console.log(result);
                location.reload();
              }).fail(function() {
                  Materialize.toast("Unable to update ride. Please check your internet connection.",4000);
              });
        }

        function cancelRide(_rideID, _allRides, _dateOfRide){
          var args = {
            rideID: _rideID,
            allRides: _allRides,
            dateOfRide: _dateOfRide,
            ajaxURL: '../ajax/ajax_cancel_ride.php'
          }

          var cancelRideRequest = ajaxRequest(args);

          cancelRideRequest.done(function(result){
            console.log(result)
          }).fail(function(result){
            Materialize.toast("Unable to cancel ride. Please check your internet connection.",4000);
          });

        }

        function restoreRide(_rideID, _dateOfRide){
          
        }

        function ajaxRequest(args){
           return $.ajax({
                url: args['ajaxURL'],
                data: args,
                cache: false,
                success: function(response) {
                    result = response;
                }
            });
        }