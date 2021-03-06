<?php
  require_once 'util/sessions.php';

  // Get team from REQUEST; otherwise, use logged-in user's team.
  $redirectUrl = "teamPage.php";
  if (isset($_REQUEST["team_id"])) {
  	$teamId = $_REQUEST["team_id"];
  	$redirectUrl .= "?team_id=$teamId";
  } else if (SessionUtil::isLoggedIn()) {
  	$teamId = SessionUtil::getLoggedInTeam()->getId();
  }

  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("Team Summary", true);
?>

<script>
// shows the team with the specified id
function showTeam(teamId) {
    // If teamid is blank, then clear the team div.
	if (teamId=="" || teamId=="0") {
		document.getElementById("teamDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("teamDisplay"),
	    "admin/displayTeam.php?type=display&team_id="+teamId);
}

// shows the draft year for the specified team
function showDraftYear(teamId, year) {
    // If year is blank, then clear the draftDisplay div.
	if (year=="" || year=="0") {
		document.getElementById("draftDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("draftDisplay"),
	    "util/draftManager.php?type=teamdisplay&year=" + year + "&team_id=" + teamId);
}

// populates the innerHTML of the specified elementId with the HTML returned by the specified
// htmlString
function getRedirectHTML(element, htmlString) {
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			element.innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET", htmlString, true);
	xmlhttp.send();
}
</script>

<body>
<?php
  require_once 'dao/teamDao.php';
  require_once 'util/teamManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::TEAM_SUMMARY_BUTTON);

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
  	die("<h1>Team ID " . $teamId . " not found!</h1>");
  }

  // Allow user to choose from list of teams to see corresponding summary page.
  TeamManager::displayTeamChooser($team);

  echo "<div id='teamDisplay'></div><br/>";
?>

<script>
  // initialize teamDisplay with selected team
  showTeam(document.getElementById("team_id").value);
</script>

<?php

  // Display footer
  LayoutUtil::displayFooter();
?>

</body>
</html>
