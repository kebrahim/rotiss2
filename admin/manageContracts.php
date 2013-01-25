<?php
  require_once '../util/sessions.php';

  SessionUtil::checkUserIsLoggedInAdmin();

  // Get team from REQUEST; otherwise, use logged-in user's team.
  $redirectUrl = "admin/manageContracts.php";
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
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Contracts", false);
?>

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
	    "displayTeam.php?type=contracts&team_id="+teamId);
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

//adds a row to the contracts table for the specified team
function addContract(team_id) {
	// ensure the table is visible
	var contractDiv = document.getElementById("contractdiv");
	contractDiv.style.display = "block";

	// add a new empty row to the table
	var contractTable = document.getElementById("contracttable");
    var nextRowNumber = contractTable.rows.length;
    var newRow = contractTable.insertRow(nextRowNumber);

    // headshot column gets updated automatically when a player is selected
    var headshotCell = newRow.insertCell(0);

    // name column gets drop-down of players whose contracts are available to be picked up
    var nameCell = newRow.insertCell(1);
    getRedirectHTML(nameCell,
        "../manager/contractManager.php?type=dropped&row=" + nextRowNumber);

    // years, price, start year, end year, type, drop are blank
    var yearsCell = newRow.insertCell(2);
    var priceCell = newRow.insertCell(3);
    var startYearCell = newRow.insertCell(4);
    var endYearCell = newRow.insertCell(5);
    var typeCell = newRow.insertCell(6);
    var buyoutCell = newRow.insertCell(7);

    // remove column is activated
    var removeCell = newRow.insertCell(8);
    removeCell.innerHTML = "<button type='button' name='removeContractButton' "
        + " onclick='removeContract(" + nextRowNumber + ")' class='btn'><i class='icon-remove'></i>"
        + "</button>";

    // update count of assigned contracts
    var oldCount = Number(document.getElementsByName("contract_newcontractcount").item(0).value);
    document.getElementsByName("contract_newcontractcount").item(0).value = oldCount + 1;

    // ensure the 'remove' column is visible
    var contractRemoveColumn = document.getElementById("contractRemoveColumn");
    contractRemoveColumn.style.display = "block";
}

// removes a row from the contracts table & updates any rows below it
function removeContract(rowNumber) {
	var contractTable = document.getElementById("contracttable");
	var totalRows = contractTable.rows.length;

	// delete row
	contractTable.deleteRow(rowNumber);

	if ((rowNumber + 1) < totalRows) {
        // iterate through rows after deleted row & update contents
		for (var r = rowNumber; r < (totalRows - 1); r++) {
        	var row = contractTable.rows[r];
            var oldRowNum = "" + (r + 1);
            var newRowNum = "" + r;
            var oldPlayerSelect = "contract_pickup" + oldRowNum;
            var newPlayerSelect = "contract_pickup" + newRowNum;
            var oldOnChange = ".value, " + oldRowNum + ")";
            var newOnChange = ".value, " + newRowNum + ")";

            // update name dropdown, including selected player
            var selectedNameIdx = document.getElementById(oldPlayerSelect).selectedIndex;
            row.cells[1].innerHTML = row.cells[1].innerHTML
                .replace(oldPlayerSelect, newPlayerSelect)
                .replace(oldPlayerSelect, newPlayerSelect)
                .replace(oldOnChange, newOnChange);
            document.getElementById(newPlayerSelect).selectedIndex = selectedNameIdx;

        	// update remove button
        	row.cells[8].innerHTML = row.cells[8].innerHTML.replace(
                "removeContract(" + oldRowNum + ")", "removeContract(" + newRowNum + ")");
		}
	} else if (totalRows == 2) {
	    // there are zero non-header rows remaining, so hide the table
     	var contractDiv = document.getElementById("contractdiv");
     	contractDiv.style.display = "none";
	}

    // update count of assigned contracts
    var newCount = Number(document.getElementsByName("contract_newcontractcount").item(0).value) - 1;
    document.getElementsByName("contract_newcontractcount").item(0).value = newCount;

    // if there are no more editable contracts, hide 'remove' column
    if (newCount == 0) {
      	var contractRemoveColumn = document.getElementById("contractRemoveColumn");
      	contractRemoveColumn.style.display = "none";
    }
}

function selectContract(contractId, rowNumber) {
    // update headshot field to show selected contract player's stupid face
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[0],
        "../manager/contractManager.php?type=attribute&attr=headshot&contract_id=" + contractId);

	// update years field
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[2],
        "../manager/contractManager.php?type=attribute&attr=years&contract_id=" + contractId);

  	// update price field
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[3],
        "../manager/contractManager.php?type=attribute&attr=price&contract_id=" + contractId);

    // update start year field
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[4],
        "../manager/contractManager.php?type=attribute&attr=start&contract_id=" + contractId);

    // update end year field
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[5],
        "../manager/contractManager.php?type=attribute&attr=end&contract_id=" + contractId);

    // update type field
    getRedirectHTML(document.getElementById("contracttable").rows[rowNumber].cells[6],
        "../manager/contractManager.php?type=attribute&attr=type&contract_id=" + contractId);
}

</script>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../entity/team.php';
  require_once '../util/teamManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_CONTRACTS_BUTTON);

  if (isset($_POST['update'])) {
    // TODO update contracts
  } else {
    // clear out keeper contract variables from previous contract scenarios.
    SessionUtil::clearSessionVarsWithPrefix("contract_");
  }

  $team = TeamDao::getTeamById($teamId);
  if ($team == null) {
  	die("<h1>Team ID " . $teamId . " not found!</h1>");
  }

  echo "<FORM ACTION='manageContracts.php' METHOD=POST>";

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
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
