<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/manageKeepers.php");
?>

<!DOCTYPE html>
<html>

<?php
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Manage Keepers", false);
?>

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
	    "displayTeam.php?type=keepers&team_id="+teamId);
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
        "displayTeam.php?type=keepercontracts&team_id=" + team_id + "&row=" + nextRowNumber);

    // years column gets dropdown of 1-year, 2-year or minor-league contract
    var yearsCell = newRow.insertCell(2);
    yearsCell.innerHTML = "<select class='input-small' id='keeper_year" + nextRowNumber + "' " +
        "name='keeper_year" + nextRowNumber +
        "' onchange='selectPlayer(document.getElementById(\"keeper_player" + nextRowNumber +
        "\").value, this.value, " + nextRowNumber + ")'>" +
        "<option value='-1'>-- Type --</option><option value='1'>1-year</option>" +
        "<option value='2'>2-year</option><option value='0'>Minor</option></select>";

    // price column gets a read-only textbox, which is updated automatically when a player is
    // selected
    var priceCell = newRow.insertCell(3);
    priceCell.innerHTML = "<input type='text' class='input-mini' name='keeper_price" +
        nextRowNumber + "' style='text-align:center' size=10 placeholder='Rank' readonly='true'/>";

    // start year, end year, buyout are blank
    var startYearCell = newRow.insertCell(4);
    var endYearCell = newRow.insertCell(5);
    var buyoutCell = newRow.insertCell(6);

    // remove column is activated
    var removeCell = newRow.insertCell(7);
    removeCell.innerHTML = "<button type='button' name='removeKeeperButton' "
        + " onclick='removeContract(" + nextRowNumber + ")' class='btn'><i class='icon-remove'></i>"
        + "</button>";

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
            var oldRowNum = "" + (r + 1);
            var newRowNum = "" + r;
            var oldPlayerSelect = "keeper_player" + oldRowNum;
            var newPlayerSelect = "keeper_player" + newRowNum;
            var oldYearsSelect = "keeper_year" + oldRowNum;
            var newYearsSelect = "keeper_year" + newRowNum;
            var oldPriceText = "keeper_price" + oldRowNum;
            var newPriceText = "keeper_price" + newRowNum;
            var oldOnChange = ".value, " + oldRowNum + ")";
            var newOnChange = ".value, " + newRowNum + ")";

            // update name dropdown, including selected player
            var selectedNameIdx = document.getElementById(oldPlayerSelect).selectedIndex;
            row.cells[1].innerHTML = row.cells[1].innerHTML
                .replace(oldPlayerSelect, newPlayerSelect)
                .replace(oldPlayerSelect, newPlayerSelect)
                .replace(oldYearsSelect, newYearsSelect)
                .replace(oldOnChange, newOnChange);
            document.getElementById(newPlayerSelect).selectedIndex = selectedNameIdx;

            // update years drop-down, including selected year
            var selectedYearsIdx = document.getElementById(oldYearsSelect).selectedIndex;
            row.cells[2].innerHTML = row.cells[2].innerHTML
                .replace(oldYearsSelect, newYearsSelect)
                .replace(oldYearsSelect, newYearsSelect)
                .replace(oldPlayerSelect, newPlayerSelect)
                .replace(oldOnChange, newOnChange);
            document.getElementById(newYearsSelect).selectedIndex = selectedYearsIdx;

            // update price text input, including selected price
            var selectedPrice = document.getElementsByName(oldPriceText).item(0).value;
            row.cells[3].innerHTML = row.cells[3].innerHTML.replace(oldPriceText, newPriceText);
            document.getElementsByName(newPriceText).item(0).value = selectedPrice;

        	// update remove button
        	row.cells[7].innerHTML = row.cells[7].innerHTML.replace(
                "removeContract(" + oldRowNum + ")", "removeContract(" + newRowNum + ")");
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
function selectPlayer(player_id, type_id, rowNumber) {
    // update headshot field to show selected player's stupid face
    getRedirectHTML(document.getElementById("keepertable").rows[rowNumber].cells[0],
        "displayPlayer.php?type=headshot&player_id=" + player_id);

	// update price field to show cumulative rank for that player
	getRedirectValue(document.getElementsByName("keeper_price" + rowNumber).item(0),
	    "displayPlayer.php?type=cumulativerank&player_id=" + player_id + "&contracttype=" +
	    type_id);
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
    costCell.innerHTML = "<input type='number' class='input-small' name='keeper_pp" + nextRowNumber
        + "' style='text-align:center' placeholder='>= 100'/>";
    var removeCell = newRow.insertCell(2);
    removeCell.innerHTML = "<button type='button' name='removeBallButton' "
        + " onclick='removeBall(" + nextRowNumber + ")' class='btn'><i class='icon-remove'></i>"
        + "</button>";

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
            row.cells[1].innerHTML = "<input type='number' class='input-small' name='keeper_pp" + r
                + "' style='text-align:center' placeholder='>= 100'/>";
            document.getElementsByName("keeper_pp" + r).item(0).value = oldValue;
            row.cells[2].innerHTML = "<button type='button' name='removeBallButton' "
                + " onclick='removeBall(" + r + ")' class='btn'><i class='icon-remove'></i>"
                + "</button>";
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

  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_KEEPERS_BUTTON);
  $currentYear = TimeUtil::getCurrentYear();

  echo "<div class='row-fluid'>
          <div class='span8 center offset2'>
            <h3>Jeepers keepers $currentYear</h3>
          </div>
        </div>";

  echo "<form action='manageKeepers.php' method='post'>";
  echo "  <div class='row-fluid'>
            <div class='span12 center'>";

  if (isset($_POST['save'])) {
  	// Create keeper scenario.
  	$keepers = new Keepers();
  	$keepers->parseKeepersFromPost();

  	// Validate keepers.
  	if ($keepers->validateKeepers()) {
  		// display changes to the keepers
  		$keepers->showKeepersSummary();

  		// request final confirmation of keepers before execution
  		echo "<p><button class=\"btn btn-primary\" name='confirmSave'
  		                 type=\"submit\">Confirm</button>&nbsp&nbsp
  		         <button class=\"btn\" name='cancelAuction' type=\"submit\">Cancel</button>
  		</p>";
  	} else {
  		echo "<h4>Cannot save keepers! Please <a href='manageKeepers.php' class='btn btn-primary'>
  		      try again</a></h4>";
  	}
  } elseif(isset($_POST['confirmSave'])) {
  	// Re-create keeper scenario from session.
  	$keepers = new Keepers();
  	$keepers->parseKeepersFromSession();

  	// Validate keepers.
  	if ($keepers->validateKeepers()) {
  	  // Save keepers & report results.
  	  $keepers->saveKeepers();
  	  echo "<a href='manageKeepers.php' class='btn btn-primary'>Let's do it again!</a><br/><br/>";
  	} else {
  	  echo "<h3>Cannot save keepers! Please <a href='manageKeepers.php'
  		    class='btn btn-primary'>try again</a></h3>";
  	}
  } elseif(isset($_POST['bank'])) {
    // If bank button was pressed, display brogna information that will be updated.
    $team = TeamDao::getTeamById($_POST['keeper_teamid']);
  	$currentYear = TimeUtil::getCurrentYear();
  	$nextYear = $currentYear + 1;
  	$currentYearBrognas = BrognaDao::getBrognasByTeamAndYear($team->getId(), $currentYear);

    echo "<h4>" . $team->getName() . " - Bank it up!</h4>";

    // Team info
  	echo "<div class='row-fluid'>
  	        <div class='span4 center'>";
  	echo "<br/>" . $team->getSportslineImg(72);
  	echo "<br/><p class='ptop'>" . $team->getOwnersString() . "</p>";
  	echo "  </div>"; // span4

  	// Brognas
    echo "<div class='span8'>
          <h4>Banking Summary</h4>";
  	echo "<strong>" . $currentYear . " Bank:</strong> $" . $currentYearBrognas->getTotalPoints() .
    	" for " . $nextYear . " season<br/>";
  	echo "<strong>Allocate " . $nextYear . " budget:</strong> $" . Brogna::ANNUAL_ALLOCATION .
  	   	" + $" . $currentYearBrognas->getTotalPoints() . " = $" .
  	    (Brogna::ANNUAL_ALLOCATION + $currentYearBrognas->getTotalPoints()) . "<br/><br/>";

  	// Validate that banked brognas are <= max
  	$bankable = true;
  	if ($currentYearBrognas->getTotalPoints() > Brogna::MAX_BANK) {
  	  echo "<div class='alert alert-error'><strong>Error:</strong> Cannot bank more than " .
  	      Brogna::MAX_BANK . " brognas</div>";
  	  $bankable = false;
  	}

    echo "</div>"; // span8
    echo "</div>"; // row-fluid

    // request final confirmation of banking before execution
    if ($bankable) {
      echo "<div class='alert alert-info'><strong>Once you confirm, this team
          will not be able to make any more keeper selections for " . $currentYear . "!</strong></div>";
      echo "<p><button class='btn btn-primary' name='confirmBank' type='submit'>Confirm</button>
            &nbsp<button class='btn' name='cancelBank' type='submit'>Cancel</button></p><br/>";

      // show all non-contracted players which will be dropped from team
  	  $playersToBeDropped = PlayerDao::getPlayersToBeDroppedForKeepers($team, $currentYear);
  	  if (count($playersToBeDropped) > 0) {
   	    echo "<h4>Players to be Dropped</h4>
  	          <table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	            <thead><tr><th colspan=2>Player</th><th>Team</th><th>Position</th></tr></thead>";
   	    foreach ($playersToBeDropped as $player) {
   	  	  echo "<tr><td>" . $player->getHeadshotImg(24, 32) . "</td>
   	  	            <td>" . $player->getNameLink(false) . "</td>
   	  	            <td>" . $player->getMlbTeam()->getImageTag(32, 32) . "</td>
   	  	            <td>" . $player->getPositionString() . "</td></tr>";
   	    }
        echo "</table>";
  	  }
    } else {
      echo "<h4>Cannot bank money! Please <a href='manageKeepers.php'
            class='btn btn-primary'>try again</a></h4>";
    }
  	echo "<input type='hidden' name='keeper_teamid' value='" . $team->getId() . "'>";
  } elseif(isset($_POST['confirmBank'])) {
    // If confirmBank button was pressed, save brogna info.
  	$team = TeamDao::getTeamById($_POST['keeper_teamid']);
    echo "<h4>" . $team->getName() . " - Banking confirmed!</h4>";

    // Team info
    echo "<div class='row-fluid'>
    <div class='span4 center'>";
    echo "<br/>" . $team->getSportslineImg(72);
    echo "<br/><p class='ptop'>" . $team->getOwnersString() . "</p>";
    echo "  </div>"; // span4

    // Brognas
    echo "<div class='span8'>";
  	$currentYear = TimeUtil::getCurrentYear();
  	$nextYear = $currentYear + 1;

  	// Show brognas banked from previous season.
  	$currentYearBrognas = BrognaDao::getBrognasByTeamAndYear($team->getId(), $currentYear);
  	$bankedPoints = $currentYearBrognas->getTotalPoints();
  	echo "<br/><strong>" . $currentYear . " Bank:</strong> $" . $bankedPoints .
  	    " for " . $nextYear . " season";

  	// Save & display brogna info for next year
  	$nextYearTotalPoints = $bankedPoints + Brogna::ANNUAL_ALLOCATION;
  	$nextYearBrognas = new Brogna($team->getId(), $nextYear, $nextYearTotalPoints, $bankedPoints,
  	    0, 0, Brogna::TRADEABLE + $bankedPoints);
  	BrognaDao::createBrognas($nextYearBrognas);
  	$team->displayBrognas($nextYear, $nextYear, false, 0, 'center');

  	// update changelog
  	ChangelogDao::createChange(new Changelog(-1, Changelog::BANK_TYPE,
  	    SessionUtil::getLoggedInUser()->getId(), TimeUtil::getTimestampString(), $nextYear,
  	    $team->getId(), null));

  	echo "</div>"; // span8
    echo "</div>"; // row-fluid

    // drop all non-contracted players from team
    $playersToBeDropped = PlayerDao::getPlayersToBeDroppedForKeepers($team, $currentYear);
    if (count($playersToBeDropped) > 0) {
      echo "<h4>Dropped Players</h4>
        	<table class='table vertmiddle table-striped table-condensed table-bordered center'>
  	          <thead><tr><th colspan=2>Player</th><th>Team</th><th>Position</th></tr></thead>";
   	  foreach ($playersToBeDropped as $player) {
   	  	echo "<tr><td>" . $player->getHeadshotImg(24, 32) . "</td>
   	  	          <td>" . $player->getNameLink(false) . "</td>
   	  	          <td>" . $player->getMlbTeam()->getImageTag(32, 32) . "</td>
   	  	          <td>" . $player->getPositionString() . "</td></tr>";
        TeamDao::assignPlayerToTeam($player, 0);
   	  }
   	  echo "</table>";
    }

    echo "<a href='manageKeepers.php' class='btn btn-primary'>Let's do it again!</a><br/><br/>";
  } else {
  	// clear out keeper session variables from previous keeper scenarios.
  	SessionUtil::clearSessionVarsWithPrefix("keeper_");

    $teams = TeamDao::getAllTeams();
    echo "<div class='chooser'><label for='team'>Select Team:</label>&nbsp
          <select id='team' name='team' class='span6' onchange='showTeam(this.value)'>
            <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName()
          . " (" . $team->getAbbreviation() . ")</option>";
    }
    echo "</select></div>";

    echo "<div id='teamDisplay'></div>";
  }
  echo "</div></div>
        </form>";

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
