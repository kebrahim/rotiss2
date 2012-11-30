<?php
  require_once 'util/sessions.php';
  SessionUtil::checkUserIsLoggedIn();
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Team Summary</title>
<link href='css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='css/stpetes.css' rel='stylesheet' type='text/css'>
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
	    "admin/displayTeam.php?type=display&team_id="+teamId);
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
  require_once 'dao/contractDao.php';
  require_once 'dao/teamDao.php';
  require_once 'util/layout.php';
  require_once 'util/time.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::TEAM_SUMMARY_BUTTON);

  // Get team from REQUEST; otherwise, use logged-in user's team.
  if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  } else {
    $teamId = SessionUtil::getLoggedInTeam()->getId();
  }
  $team = TeamDao::getTeamById($teamId);

  // Allow user to choose from list of teams to see corresponding summary page.
  echo "<div class='row-fluid'>
          <div class='span12 center chooser'>";
  $allTeams = TeamDao::getAllTeams();
  echo "<label for='team_id'>Select team:</label>&nbsp&nbsp";
  echo "<select id='team_id' name='team_id' onchange='showTeam(this.value)'>";
  foreach ($allTeams as $selectTeam) {
    echo "<option value='" . $selectTeam->getId() . "'";
    if ($selectTeam->getId() == $teamId) {
      echo " selected";
    }
    echo ">" . $selectTeam->getName() . " (" . $selectTeam->getAbbreviation() . ")</option>";
  }
  echo "</select>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

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
