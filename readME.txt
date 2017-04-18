Creator Type
	0 = user
	1 = club
	2 = race organizer

Multi_Rider_Rides: Type
	0 = club/group
	1 = Race/Event

Group Privacy
	0 = public
	1 = private

Ride Visibility
	0 = Friends Only
	1 = Invite Only
	2 = Everyone
	3 = Group Members Only

DisplayRides
	page
		0 = home
		1 = group page

SELECT * FROM  `rides` 
RIGHT JOIN  `rides_repeat` RR ON RR.`ride_id` = rides.`ID` 
WHERE  RR.repeat_start =1408492800 OR (( 1408492800 - RR.repeat_start ) % RR.repeat_interval = 0 )
AND RR.repeat_end >1408492800

//1408... is current day timestamp
//for multiple dates add OR beside timestamp and surround with ()

//FOR ANY NEW DATE SET MONTH TO 1 FOR TIMEZONE OFFEST/DST ISSUES

//ALL repeat_start and end values are UTC time
 