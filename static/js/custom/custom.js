// Load the IFrame Player API code asynchronously.
 var tag = document.createElement('script');
 tag.src = "https://www.youtube.com/player_api";
 var firstScriptTag = document.getElementsByTagName('script')[0];
 firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
 var player;

function onYouTubePlayerAPIReady() {
   //do nothing because we are using the click event
}
function showVideo() {
	document.getElementById("easevideo_frame").style.visibility="visible";
	document.getElementById("ytplayer").style.visibility="visible";
	document.getElementById("easevideo_frame").style.display = 'block';
	document.getElementById("ytplayer").style.display = 'block';
	document.getElementById("easevideo_div").style.cursor="auto";
	document.getElementById("easevideo_div").setAttribute('onclick','');
	embedYouTubePlayer('UNHevzZf7dM', 'ytplayer', '100%', 259);
	return true;
}

function closeVideo() {
	document.getElementById("easevideo_frame").style.visibility="hidden";
	document.getElementById("ytplayer").style.visibility="hidden";
	document.getElementById("easevideo_frame").style.display = 'none';
	document.getElementById("ytplayer").style.display = 'none';
	document.getElementById("easevideo_div").style.cursor="pointer";
	document.getElementById("easevideo_div").setAttribute('onclick','showVideo()');
	return true;
}

function showStarterVideo()	{
	document.getElementById("easestartvideo_frame").style.visibility="visible";
	document.getElementById("startplayer").style.visibility="visible";
	document.getElementById("easestartvideo_frame").style.display = 'block';
	document.getElementById("startplayer").style.display = 'block';
	document.getElementById("easestartvideo_div").style.cursor="auto";
	document.getElementById("easestartvideo_div").setAttribute('onclick','');
	embedYouTubePlayer('QEtwRs9TGoA', 'startplayer', '100%', 259);
	return true;
}

function closeStarterVideo() {
	document.getElementById("easestartvideo_frame").style.visibility="hidden";
	document.getElementById("startplayer").style.visibility="hidden";
	document.getElementById("easestartvideo_frame").style.display = 'none';
	document.getElementById("startplayer").style.display = 'none';
	document.getElementById("easestartvideo_div").style.cursor="pointer";
	document.getElementById("easestartvideo_div").setAttribute('onclick','showStarterVideo()');
	return true;
}

function onYouTubePlayerReady(playerId) {
	var ytplayer = document.getElementById(playerId);
	var stateChangefunction = 'stateChange_' + playerId;
	if(ytplayer.addEventListener) {
		ytplayer.addEventListener('onStateChange', stateChangefunction);
	} else {
		ytplayer.attachEvent('onStateChange', stateChangefunction);
	}
}

function embedYouTubePlayer(videoID, containerID, width, height) {
	var stateChangefunction = 'stateChange_' + containerID;
	player = new YT.Player(containerID, {
		height: height,
		width: width,
		videoId: videoID,
		playerVars: {autoplay:1, controls:2, rel:0, modestbranding:1, showinfo:0, hd:1},
		events: {'onStateChange': stateChangefunction}
	});
}

function stateChange_ytplayer(event) {
	var state = event.data;
	// we may wish to add more states, so we'll use switch
	switch(state) {
		case 0:
			// Ended
			closeVideo();
			break;
			case 1:
			// Playing
			//alert("playing");
			break;
	}
}

function stateChange_startplayer(event) {
	var state = event.data;
	// we may wish to add more states, so we'll use switch
	switch(state) {
		case 0:
			// Ended
			closeStarterVideo();
			break;
			case 1:
			// Playing
			//alert("playing");
			break;
	}
}