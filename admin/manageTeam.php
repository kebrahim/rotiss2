<?php
  require_once '../util/sessions.php';
  
  SessionUtil::checkUserIsLoggedInAdmin();
  
  // Get team from REQUEST; otherwise, use logged-in user's team.
  $redirectUrl = "admin/manageTeam.php";
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
<head>
<title>St Pete's Rotiss - Manage Team</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

<script>
//shows the team with the specified id
function showTeam(teamId) {
    // If teamid is blank, then clear the team div.
	if (teamId=="" || teamId=="0") {
		document.getElementById("teamDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("teamDisplay"),
	    "displayTeam.php?type=manage&team_id="+teamId);
}

//populates the innerHTML of the specified elementId with the HTML returned by the specified
//htmlString
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
  require_once '../dao/teamDao.php';
  require_once '../entity/team.php';
  require_once '../util/layout.php';
  require_once '../util/teamManager.php';
  
  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_TEAM_BUTTON);
  
  if (isset($_POST['update'])) {
    // Update team.
    $teamToUpdate = new Team($_POST['teamId'], $_POST['teamName'], $_POST['league'],
        $_POST['division'], $_POST['abbreviation'], $_POST['sportslineImage']);
    TeamDao::updateTeam($teamToUpdate);
    echo "<div class='alert alert-success center'>
            <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>
            <strong>Team successfully updated!</strong>
          </div>";
  }
  
  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
  	die("<h1>Team ID " . $teamId . " not found!</h1>");
  }
  
  echo "<FORM ACTION='manageTeam.php' METHOD=POST>";
  
  // Allow user to choose from list of teams to see corresponding team management page.
  TeamManager::displayTeamChooser($team);

  echo "<div id='teamDisplay'></div><br/>";
?>

<script>
  // initialize teamDisplay with selected team
  showTeam(document.getElementById("team_id").value);
</script>

<?php

  // TODO add/delete contracts
  // TODO seltzer player

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
