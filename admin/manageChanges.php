<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInAdmin();
  $redirectUrl = "admin/manageChanges.php";
  if (isset($_REQUEST["team_id"])) {
  	$teamId = $_REQUEST["team_id"];
  	$redirectUrl .= "?team_id=$teamId";
  } else {
    $teamId = 0;
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Change Log", false);
?>

<script>
//shows the team with the specified id
function showTeam(teamId) {
    // If teamid is blank, then clear the team div.
	if (teamId=="") {
		document.getElementById("teamDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("teamDisplay"),
	    "displayTeam.php?type=changes&team_id="+teamId);
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
  require_once '../util/teamManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_CHANGES_BUTTON);

  // Filter by team
  $team = TeamDao::getTeamById($teamId);
  TeamManager::displayTeamChooserWithAllTeams($team);

  // TODO filter by year

  echo "<div id='teamDisplay'></div><br/>";
?>

<script>
  // initialize teamDisplay with selected team
  showTeam(document.getElementById("team_id").value);
</script>

<?php

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
