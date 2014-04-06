
<?php
  require_once 'util/sessions.php';

  // Get team from REQUEST; otherwise, use logged-in user's team.
  $redirectUrl = "keeperSimulator.php";
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
	    "admin/displayTeam.php?type=keepersim&team_id="+teamId);
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
        "admin/displayTeam.php?type=keepercontracts&team_id=" + team_id + "&row=" + nextRowNumber);

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
        "admin/displayPlayer.php?type=headshot&player_id=" + player_id);

	// update price field to show cumulative rank for that player
	getRedirectValue(document.getElementsByName("keeper_price" + rowNumber).item(0),
	    "admin/displayPlayer.php?type=cumulativerank&player_id=" + player_id + "&contracttype=" +
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
        + "' style='text-align:center' placeholder='>= 125'/>";
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

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("Keeper Simulator", true);
?>

<body>

<?php
  require_once 'dao/teamDao.php';
  require_once 'entity/keepers.php';
  require_once 'util/teamManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::BUDGET_BUTTON);

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
    die("<h1>Team ID " . $teamId . " not found!</h1>");
  }

  // Allow user to choose from list of teams to see corresponding keeper simulator page.
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

  <div id='simulatorModal' class='modal hide fade' tabindex='-1' role='dialog'
       aria-labelledby='myModalLabel' aria-hidden='false' style='display:none;'>
    <div class='modal-header'>
      <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
      <h3 id='myModalLabel' class='center'>Keeper Simulation</h3>
    </div>
    <div class='modal-body center'>
<?php
  if (isset($_POST['save'])) {
    // Create keeper scenario.
    $keepers = new Keepers();
    $keepers->parseKeepersFromPost();

    // Validate keepers.
    if ($keepers->validateKeepers()) {
      // display changes to the keepers
      $keepers->showKeepersSummary();

      // TODO: if active user, allow to save keepers which can be edited and/or approved by admin
    } else {
      echo "<h4>Cannot display keepers! Please <a href='keeperSimulator.php?team_id=" .
          $_POST['keeper_teamid'] . "' class='btn btn-primary'>try again</a></h4>";
    }
  } else {
    echo "you lose!";
  }
?>
    </div>
    <div class='modal-footer'>
      <button class='btn' data-dismiss='modal' aria-hidden='true'>Try Again</button>
    </div>
  </div>

<?php
  if (isset($_POST["save"])) {
    echo "<script>
            $('#simulatorModal').modal('toggle');
          </script>";
  }
?>

</body>
</html>
