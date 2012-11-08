<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Team</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
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
	    "displayTeamForTransaction.php?type=manage&team_id="+teamId);
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
  require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printHeader(true, false, NavigationUtil::MANAGE_TEAM_BUTTON);
  echo "<div class='bodyleft'>";

  if (isset($_POST['update'])) {
    // Update team.
    $teamToUpdate = new Team($_POST['teamId'], $_POST['teamName'], $_POST['league'],
        $_POST['division'], $_POST['abbreviation'], $_POST['sportslineImage']);
    TeamDao::updateTeam($teamToUpdate);
    $teamId = $_POST['teamId'];
    echo "<div class='alert_msg'>Team successfully updated!</div>";
  } else if (isset($_REQUEST["team_id"])) {
    $teamId = $_REQUEST["team_id"];
  }

  // Allow user to choose from list of teams to see corresponding team management page.
  $allTeams = TeamDao::getAllTeams();
  echo "<FORM ACTION='manageTeam.php' METHOD=POST>";
  echo "<br/><label for='team_id'>Choose team: </label>";
  echo "<select id='team_id' name='team_id' onchange='showTeam(this.value)'>
          <option value='0'></option>";
  foreach ($allTeams as $selectTeam) {
    echo "<option value='" . $selectTeam->getId() . "'";
    if ($selectTeam->getId() == $teamId) {
      echo " selected";
    }
    echo ">" . $selectTeam->getName() . " (" . $selectTeam->getAbbreviation() . ")</option>";
  }
  echo "</select><br/>";
  echo "<div id='teamDisplay'></div><br/>";
?>

<script>
  // initialize teamDisplay with selected team
  showTeam(document.getElementById("team_id").value);
</script>

<?php
  echo "</form></div>";

  // TODO add/delete contracts
  // TODO seltzer player

  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>
