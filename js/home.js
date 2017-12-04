
fetchLog();


$("#fetchLog").click(function(){
	fetchLog();
	Materialize.toast("Log feed updated", 4000, 'rounded');
});

function fetchLog(){

	$.ajax({
		url: 'resources/handler/home.php',
		data: {
			event: "updateLog"
		},
		type: 'POST'
		,dataType:'json'
	}).done(function(response){

		if (response.hasOwnProperty('error')) {
			// Error loading.
		} else {

			$("#userLog").html("");

			for (var i = 0; i < response.userLog.length; i++) {

				var timestamp = response.userLog[i].ulTimestamp;
				var firstname = response.userLog[i].uFirstname;
				var lastname = response.userLog[i].uLastname;
				var profilepicture = response.userLog[i].uppImagePath;
				var img = "";

				if (profilepicture == null) {
					img = '<div class="ppCircle circle z-depth-1"><i class="ppUser fa fa-user"></i></div>';
				}
				else {
					img = '<img src="'+profilepicture+'" alt="" class="z-depth-1 circle responsive-img">';
				}
				var time = timeDifference(timestamp, response.Time);

				var html = '';
				html += '<div class="log col s12 m8 offset-m2 l6 ">';
				html += '<div class="card-panel grey lighten-5 z-depth-1 hoverable">';
				html += '<div class="row valign-wrapper">';
				html += '<div class="col s2">';
				html += img;
				html += '</div>';
				html += '<div class="col s10">';
				html += '<span class="black-text truncate">';
				html += firstname+' '+lastname;
				html += '</span>';
				html += '<span class="grey-text">';
				html += ' Signed In to the application ';
				html += time;
				html += '</span>';
				html += '</div>';
				html += '</div>';
				html += '</div>';
				html += '</div>';

				$("#userLog").append(html);
			}
		}

	}).fail(function(response){
		// console.log(response);
	});
}

function timeDifference(userTimestamp, currentTimestamp) {
	var minutesInMs = 60*1000;
	var hoursInMs = minutesInMs * 60;
	var daysInMs = hoursInMs * 24;

	var timeDiff = (currentTimestamp - userTimestamp) * 1000;

	if (timeDiff < minutesInMs) {
		return Math.round(timeDiff / 1000) + ' seconds ago';
	}
	else if (timeDiff < hoursInMs) {
		return Math.round(timeDiff / minutesInMs) + ' minutes ago';		
	}
	else if (timeDiff < daysInMs) {
		return Math.round(timeDiff / hoursInMs) + ' hours ago';		
	}
	else {
		return Math.round(timeDiff / daysInMs) + ' days ago';		
	}
}