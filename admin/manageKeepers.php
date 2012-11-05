<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
?>

<html>
<head>
<title>Rotiss.com - Manage Keepers</title>
<link href='../css/style.css' rel='stylesheet' type='text/css'>
</head>

<script>
// sets the display of the div with the specified id
function setDisplay(id, display) {
  var divid = document.getElementById(id);
  divid.style.display = display;
}

// shows the team with the specified id
function showTeam(teamId) {
    // If teamid is blank, then clear the team div.
	if (teamId=="" || teamId=="0") {
		document.getElementById("teamDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("teamDisplay"),
	    "displayTeamForTransaction.php?type=keepers&team_id="+teamId);
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

// populates the value of the specified element with the HTML returned by the specified
// htmlString
function getRedirectValue(element, htmlString) {
	var xmlhttp;
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp = new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange = function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			element.value = xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET", htmlString, true);
	xmlhttp.send();
}

// adds a row to the contracts table for the specified team
function addContract(team_id) {
	// ensure the table is visible
	var keeperDiv = document.getElementById("keeperdiv");
	keeperDiv.style.display = "block";

	// add a new empty row to the table
	var keeperTable = document.getElementById("keepertable");
    var nextRowNumber = keeperTable.rows.length;
    var newRow = keeperTable.insertRow(nextRowNumber);

    // headshot column gets updated automatically when a player is selected
    var headshotCell = newRow.insertCell(0);
    
    // name column gets drop-down of players
    var nameCell = newRow.insertCell(1);
    getRedirectHTML(nameCell,
        "displayTeamForTransaction.php?type=keepercontracts&team_id=" + team_id + "&row=" + nextRowNumber);

    // years column gets dropdown of 1 or 2-year contract
    var yearsCell = newRow.insertCell(2);
    yearsCell.innerHTML = "<select name='keeper_year" + nextRowNumber +
        "'><option value='0'></option>" +
        "<option value='1'>1-year</option><option value='2'>2-year</option></select>";

    // price column gets a read-only textbox, which is updated automatically when a player is
    // selected
    var priceCell = newRow.insertCell(3);
    priceCell.innerHTML = "<input type='text' name='keeper_price" + nextRowNumber +
        "' style='text-align:center' placeholder='Based on rankings' readonly='true'/>";

    // start year, end year, buyout are blank
    var startYearCell = newRow.insertCell(4);
    var endYearCell = newRow.insertCell(5);
    var buyoutCell = newRow.insertCell(6);

    // remove column is activated
    var removeCell = newRow.insertCell(7);
    removeCell.innerHTML = "<input type='button' name='removeKeeperButton' value='remove'"
        + " onclick='removeContract(" + nextRowNumber + ")'>";

    // update count of new keepers
    var oldCount = Number(document.getElementsByName("keeper_newkeepercount").item(0).value);
    document.getElementsByName("keeper_newkeepercount").item(0).value = oldCount + 1;

    // ensure the 'remove' column is visible
    var keeperRemoveColumn = document.getElementById("keeperRemoveColumn");
    keeperRemoveColumn.style.display = "block";
}

// removes a row from the contracts table & updates any rows below it
function removeContract(rowNumber) {
	var keeperTable = document.getElementById("keepertable");
	var totalRows = keeperTable.rows.length;

	// delete row
	keeperTable.deleteRow(rowNumber);

	if ((rowNumber + 1) < totalRows) {
        // iterate through rows after deleted row & update contents
		for (var r = rowNumber; r < (totalRows - 1); r++) {
        	var row = keeperTable.rows[r];

            // update name dropdown, including selected player
            var oldPlayerSelect = "keeper_player" + (r+1);
            var newPlayerSelect = "keeper_player" + r;
            var selectedNameIdx = document.getElementsByName(oldPlayerSelect).item(0).selectedIndex;
            row.cells[1].innerHTML =
                row.cells[1].innerHTML.replace(oldPlayerSelect, newPlayerSelect);
            var oldOnchange = "selectPlayer(this.value, " + (r+1);
            var newOnchange = "selectPlayer(this.value, " + r;
            row.cells[1].innerHTML =
                row.cells[1].innerHTML.replace(oldOnchange, newOnchange);
            document.getElementsByName(newPlayerSelect).item(0).selectedIndex = selectedNameIdx;

            // update years drop-down, including selected year
            var oldYearsSelect = "keeper_year" + (r+1);
            var newYearsSelect = "keeper_year" + r;
            var selectedYearsIdx = document.getElementsByName(oldYearsSelect).item(0).selectedIndex;
            row.cells[2].innerHTML =
                row.cells[2].innerHTML.replace(oldYearsSelect, newYearsSelect);
            document.getElementsByName(newYearsSelect).item(0).selectedIndex = selectedYearsIdx;

            // update price text input, including selected price
            var oldPriceText = "keeper_price" + (r+1);
            var newPriceText = "keeper_price" + r;
            var selectedPrice = document.getElementsByName(oldPriceText).item(0).value;
            row.cells[3].innerHTML =
                row.cells[3].innerHTML.replace(oldPriceText, newPriceText);
            document.getElementsByName(newPriceText).item(0).value = selectedPrice;

        	// update remove button
        	row.cells[7].innerHTML = row.cells[7].innerHTML.replace(
                "removeContract("+(r+1)+")", "removeContract("+r+")");
		}
	} else if (totalRows == 2) {
	    // there are zero non-header rows remaining, so hide the table
     	var keeperDiv = document.getElementById("keeperdiv");
     	keeperDiv.style.display = "none";
	}

    // update count of new keepers
    var newCount = Number(document.getElementsByName("keeper_newkeepercount").item(0).value) - 1;
    document.getElementsByName("keeper_newkeepercount").item(0).value = newCount;

    // if there are no more editable keepers, hide 'remove' column
    if (newCount == 0) {
      	var keeperRemoveColumn = document.getElementById("keeperRemoveColumn");
      	keeperRemoveColumn.style.display = "none";
    }
}

// player is selected; update corresponding fields
function selectPlayer(player_id, rowNumber) {
    // update headshot field to show selected player's stupid face
    getRedirectHTML(document.getElementById("keepertable").rows[rowNumber].cells[0],
        "displayPlayerForTransaction.php?type=headshot&player_id=" + player_id);

	// update price field to show cumulative rank for that player
	getRedirectValue(document.getElementsByName("keeper_price" + rowNumber).item(0),
	    "displayPlayerForTransaction.php?type=cumulativerank&player_id=" + player_id);
}

// adds a row to the ping pong ball table
function addBall() {
	// ensure the table is visible
 	var ppBallDiv = document.getElementById("ppballdiv");
	ppBallDiv.style.display = "block";

	// add a new empty row to the table
	var ppBallTable = document.getElementById("ppballtable");
    var nextRowNumber = ppBallTable.rows.length;
    var newRow = ppBallTable.insertRow(nextRowNumber);
    var numCell = newRow.insertCell(0);
    numCell.innerHTML = nextRowNumber;
    var costCell = newRow.insertCell(1);
    costCell.innerHTML = "<input type='number' name='keeper_pp" + nextRowNumber
        + "' style='text-align:center' placeholder='Enter value >= 100'/>";
    var removeCell = newRow.insertCell(2);
    removeCell.innerHTML = "<input type='button' name='removeBallButton' value='remove'"
        + " onclick='removeBall(" + nextRowNumber + ")'>";

    // update count of new pp balls
    var oldCount = Number(document.getElementsByName("keeper_newppballcount").item(0).value);
    document.getElementsByName("keeper_newppballcount").item(0).value = oldCount + 1;

    // ensure the 'remove' column is visible
    var ppRemoveColumn = document.getElementById("ppRemoveColumn");
    ppRemoveColumn.style.display = "block";
}

// removes a row from the ping pong ball table & updates any rows below it
function removeBall(rowNumber) {
	var ppBallTable = document.getElementById("ppballtable");
	var totalRows = ppBallTable.rows.length;

	// delete row
	ppBallTable.deleteRow(rowNumber);

	if ((rowNumber + 1) < totalRows) {
        // iterate through rows after deleted row & update contents
		for (var r = rowNumber; r < (totalRows - 1); r++) {
        	var row = ppBallTable.rows[r];
        	row.cells[0].innerHTML = r;
        	var priceBoxName = "keeper_pp" + (r + 1);
        	var oldValue = document.getElementsByName(priceBoxName).item(0).value;
            row.cells[1].innerHTML = "<input type='text' name='keeper_pp" + r
                + "' style='text-align:center' placeholder='Enter value >= 100'/>";
            document.getElementsByName("keeper_pp" + r).item(0).value = oldValue;
        	row.cells[2].innerHTML = "<input type='button' name='removeBallButton' value='remove'"
                + " onclick='removeBall(" + r + ")'>";
		}
	} else if (totalRows == 2) {
	    // there are zero non-header rows remaining, so hide the table
     	var ppBallDiv = document.getElementById("ppballdiv");
		ppBallDiv.style.display = "none";
	}

    // update count of new pp balls
    var newCount = Number(document.getElementsByName("keeper_newppballcount").item(0).value) - 1;
    document.getElementsByName("keeper_newppballcount").item(0).value = newCount;

    // if there are no more editable balls, hide 'remove' column
    if (newCount == 0) {
      	var ppRemoveColumn = document.getElementById("ppRemoveColumn");
      	ppRemoveColumn.style.display = "none";
    }
}

</script>
<body>

<?php
require_once '../dao/teamDao.php';
require_once '../entity/keepers.php';
require_once '../util/navigation.php';

  // Display header.
  NavigationUtil::printNoWidthHeader(true, false, NavigationUtil::MANAGE_KEEPERS_BUTTON);
  echo "<div class='bodycenter'>";

  $currentYear = TimeUtil::getCurrentYear();
  echo "<h1>Jeepers keepers $currentYear</h1>";
  echo "<FORM ACTION='manageKeepers.php' METHOD=POST>";

  if(isset($_POST['save'])) {
  	// Create keeper scenario.
  	$keepers = new Keepers();
  	$keepers->parseKeepersFromPost();

  	// Validate keepers.
  	if ($keepers->validateKeepers()) {
  		// display changes to the keepers
  		$keepers->showKeepersSummary();

  		// request final confirmation of keepers before execution
  		echo "<br/><input class='button' type=submit name='confirmSave' value='Confirm'>&nbsp";
  		echo "<input class='button' type=submit name='cancelSave' value='Cancel'><br>";
  	} else {
  		echo "<h3>Cannot save keepers! Please <a href='manageKeepers.php'>try again</a>.</h3>";
  	}
  } elseif(isset($_POST['confirmSave'])) {
  	// Re-create keeper scenario from session.
  	$keepers = new Keepers();
  	$keepers->parseKeepersFromSession();

  	// Validate keepers.
  	if ($keepers->validateKeepers()) {
  	  	// Save keepers & report results.
  		$keepers->saveKeepers();
  		echo "<br><a href='manageKeepers.php'>Let's do it again!</a><br>";
  	} else {
  		echo "<h3>Cannot save keepers! Please <a href='manageKeepers.php'>try again</a>.</h3>";
  	}
  } elseif(isset($_POST['bank'])) {
    // If bank button was pressed, display brogna information that will be updated.
    $team = TeamDao::getTeamById($_POST['keeper_teamid']);
    echo "<h2>" . $team->getName() . "</h2>";
    echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
    echo $team->getOwnersString() . "<br/>";

  	$currentYear = TimeUtil::getCurrentYear();
  	$nextYear = $currentYear + 1;
  	$currentYearBrognas = BrognaDao::getBrognasByTeamAndYear($team->getId(), $currentYear);

  	echo "<h3>Bank it up!</h3>";
  	echo "<strong>" . $currentYear . " Bank:</strong> $" . $currentYearBrognas->getTotalPoints() .
    	" for " . $nextYear . " season<br/><br/>";
  	echo "<strong>Allocate " . $nextYear . " budget:</strong> $450 + $" .
    	$currentYearBrognas->getTotalPoints() . " = $" .
  	    (450 + $currentYearBrognas->getTotalPoints());

  	// request final confirmation of keepers before execution
  	echo "<br/><br/><div style='color:red; font-weight:bold'>Note that once you confirm, this team
  	    will not be able to make any more selections for " . $currentYear . "!</div><br/>";
  	echo "<input class='button' type=submit name='confirmBank' value='Confirm'>&nbsp";
  	echo "<input class='button' type=submit name='cancelBank' value='Cancel'><br>";
    echo "<input type='hidden' name='keeper_teamid' value='" . $team->getId() . "'>";
  } elseif(isset($_POST['confirmBank'])) {
    // If confirmBank button was pressed, save brogna info.
  	$team = TeamDao::getTeamById($_POST['keeper_teamid']);
  	echo "<h2>" . $team->getName() . "</h2>";
  	echo "<img src='" . $team->getSportslineImageUrl() . "'><br/><br/>";
  	echo $team->getOwnersString() . "<br/>";

  	echo "<h3 class='alert_msg'>Banking confirmed!</h3>";
  	$currentYear = TimeUtil::getCurrentYear();
  	$nextYear = $currentYear + 1;
  	$currentYearBrognas = BrognaDao::getBrognasByTeamAndYear($team->getId(), $currentYear);
  	$bankedPoints = $currentYearBrognas->getTotalPoints();
  	echo "<strong>" . $currentYear . " Bank:</strong> $" . $bankedPoints .
  	    " for " . $nextYear . " season";

  	// Save & display brogna info for next year
  	$nextYearTotalPoints = $bankedPoints + 450;
  	$nextYearBrognas = new Brogna($team->getId(), $nextYear, $nextYearTotalPoints, $bankedPoints,
  	    0, 0, 50 + $bankedPoints);
  	BrognaDao::createBrognas($nextYearBrognas);
  	$team->displayBrognas($nextYear, $nextYear, false, 0, 'center');

  	echo "<br><a href='manageKeepers.php'>Let's do it again!</a><br>";
  } else {
  	// clear out keeper session variables from previous keeper scenarios.
  	SessionUtil::clearSessionVarsWithPrefix("keeper_");
  	 
    $teams = TeamDao::getAllTeams();
    echo "<h4>Select Team:</h4><select name='team' onchange='showTeam(this.value)'>
                             <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName()
          . " (" . $team->getAbbreviation() . ")</option>";
    }
    echo "</select><br>";
    echo "<div id='teamDisplay'></div><br/>";
  }
  echo "</form></div>";
  
  // Footer
  NavigationUtil::printFooter();
?>

</body>
</html>