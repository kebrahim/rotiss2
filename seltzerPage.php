<?php
  require_once 'util/sessions.php';

  // Get team from REQUEST; if not present, use logged-in team ID.
  $redirectUrl = "seltzerPage.php";
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
  LayoutUtil::displayHeadTag("Seltzer Simulator", true);
?>

<script>
//sets the display of the div with the specified id
function setDisplay(id, display) {
  var divid = document.getElementById(id);
  divid.style.display = display;
}

//shows the team with the specified id
function showTeam(teamId) {
    // If teamid is blank, then clear the team div.
	if (teamId=="" || teamId=="0") {
		document.getElementById("teamDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("teamDisplay"),
	    "admin/displayTeam.php?type=seltzer&team_id="+teamId);
}

// shows the player with the specified id
function showPlayer(playerId) {
    // If playerId is blank, then clear the player div.
	if (playerId == "" || playerId == "0") {
		document.getElementById("playerDisplay").innerHTML="";

	    // hide seltzer buttons
		setDisplay("seltzerConfig", "none");
		return;
	}
	setDisplay("seltzerConfig", "block");

	// Display player information.
	getRedirectHTML(document.getElementById("playerDisplay"),
	    "admin/displayPlayer.php?type=seltzer&player_id="+playerId);
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

function selectType(type) {
	if (type == 1) {
		// major league type
		setDisplay("major_config", "block");
		setDisplay("minor_config", "none");
    } else if (type == 2) {
		// major type
		setDisplay("major_config", "none");
		setDisplay("minor_config", "block")
    } else {
		setDisplay("major_config", "none");
		setDisplay("minor_config", "none");
    }
}

function selectCallup(isCallup) {
    if (isCallup == 1) {
        // is callup = false; remove stat row and show uncalled price
        var minorTable = document.getElementById("minorTable");
        minorTable.deleteRow(1);

        // update price to default uncalled-up minor league price
        var currentPrice = document.getElementById("seltzer_minor_price").value;
        minorTable.rows[1].cells[1].innerHTML = minorTable.rows[1].cells[1].innerHTML.replace(
                "value=\"" + currentPrice + "\"", "value=\"15\"");
    } else if (isCallup == 2) {
        // is callup = true; show row for innings/ABs, which updates price
        var minorTable = document.getElementById("minorTable");
        var nextRowNumber = minorTable.rows.length;
        var posRow = minorTable.insertRow(nextRowNumber - 1);

        var posLabelCell = posRow.insertCell(0);
        getRedirectHTML(document.getElementById("minorTable").rows[nextRowNumber - 1].cells[0],
                "util/playerManager.php?type=attribute&player_id=" +
                document.getElementById("seltzer_player").value + "&attr=minorseltzerstatlabel");

        var posCell = posRow.insertCell(1);
        posCell.innerHTML = "<input id='seltzer_stat' type='text' class='input-mini center' " +
                                   "value='0' onchange='selectStat(this.value)'></input>";

        // update price to default called-up minor league price
        var currentPrice = document.getElementById("seltzer_minor_price").value;
        minorTable.rows[2].cells[1].innerHTML = minorTable.rows[2].cells[1].innerHTML.replace(
                "value=\"" + currentPrice + "\"", "value=\"20\"");
    }
}

function selectStat(stat) {
    var minorTable = document.getElementById("minorTable");
    var divider = 0;
    if (minorTable.rows[1].cells[0].innerHTML.indexOf("At Bats") == -1) {
      // pitcher - divide by 6 IP
      divider = 6;
    } else {
      // batter - divide by 25 AB
      divider = 25;
    }
    var calculatedPrice = 20 + (2 * Math.floor(stat / divider));

    // update price to calculated price
    var currentPrice = document.getElementById("seltzer_minor_price").value;
    minorTable.rows[2].cells[1].innerHTML = minorTable.rows[2].cells[1].innerHTML.replace(
            "value=\"" + currentPrice + "\"", "value=\"" + calculatedPrice + "\"");
}

</script>

<body>

<?php
  require_once 'dao/teamDao.php';
  require_once 'util/teamManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::BUDGET_BUTTON);

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
    die("<h1>Team ID " . $teamId . " not found!</h1>");
  }

  // Allow user to choose from list of teams to see corresponding contract management page.
  TeamManager::displayTeamChooser($team);
  echo "<div id='teamDisplay'></div><br/>";
?>

  <script>
    // initialize teamDisplay with selected team
    showTeam(document.getElementById("team_id").value);
  </script>

<?php

  // Footer
  LayoutUtil::displayFooter();
?>

</body>
</html>