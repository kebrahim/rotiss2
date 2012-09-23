<?php session_start(); ?>
<html>
<head>
<title>Keepers</title>
</head>

<style type="text/css">
html {height:100%;}
body {text-align:center;}
table {text-align:center;}
table.center {margin-left:auto; margin-right:auto;}
#column_container {padding:0; margin:0 0 0 50%; width:50%; float:right;}
#left_col {float:left; width:100%; margin-left:-100%; text-align:center;}
#left_col_inner {padding:10px;}
#right_col {float:right; width:100%; text-align:center;}
#right_col_inner {padding:10px;}
</style>

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

	    // hide save button
		setDisplay("saveButton", "none");
	    return;
	} else {
		setDisplay("saveButton", "block");
	}

	// Display team information.
	if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	} else {// code for IE6, IE5
	    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	xmlhttp.onreadystatechange=function() {
		if (xmlhttp.readyState==4 && xmlhttp.status==200) {
			document.getElementById("teamDisplay").innerHTML=xmlhttp.responseText;
		}
	};
	xmlhttp.open("GET","displayTeamForTransaction.php?type=keepers&team_id="+teamId,true);
	xmlhttp.send();
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
    costCell.innerHTML = "<input type='text' name='pp" + nextRowNumber
        + "' style='text-align:center' placeholder='Enter value >= 100'/>";
    var removeCell = newRow.insertCell(2);
    removeCell.innerHTML = "<input type='button' name='removeBallButton' value='remove'"
        + " onclick='removeBall(" + nextRowNumber + ")'>";

    // update count of new pp balls
    var oldCount = Number(document.getElementsByName("newppballcount").item(0).value);
    document.getElementsByName("newppballcount").item(0).value = oldCount + 1;
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
        	//
        	var row = ppBallTable.rows[r];
        	row.cells[0].innerHTML = r;
        	var priceBoxName = "pp" + (r + 1);
        	var oldValue = document.getElementsByName(priceBoxName).item(0).value;
            row.cells[1].innerHTML = "<input type='text' name='pp" + r
                + "' style='text-align:center' placeholder='Enter value >= 100'/>";
            document.getElementsByName("pp" + r).item(0).value = oldValue;
        	row.cells[2].innerHTML = "<input type='button' name='removeBallButton' value='remove'"
                + " onclick='removeBall(" + r + ")'>";
		}
	} else if (totalRows == 2) {
	    // now, there are zero non-header rows remaining, so hide the table
     	var ppBallDiv = document.getElementById("ppballdiv");
		ppBallDiv.style.display = "none";
	}

    // update count of new pp balls
    var oldCount = Number(document.getElementsByName("newppballcount").item(0).value);
    document.getElementsByName("newppballcount").item(0).value = oldCount - 1;
}

</script>
<body>

<?php
require_once '../dao/teamDao.php';

  echo "<h1>Jeepers keepers!</h1>";
  echo "<FORM ACTION='manageKeepers.php' METHOD=POST>";

  if(isset($_POST['save'])) {
    // TODO If save button was pressed, display changes to be saved.

  } elseif(isset($_POST['confirmSave'])) {
    // TODO If confirmSave button was pressed, save changes.

  } elseif(isset($_POST['bank'])) {
    // TODO If bank button was pressed, display brogna information that will be updated.

  } elseif(isset($_POST['confirmBank'])) {
    // TODO If confirmBank button was pressed, save brogna info.

  } else {
    $teams = TeamDao::getAllTeams();
    echo "Select Team:<br><select name='team' onchange='showTeam(this.value)'>
                             <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select><br>";
    echo "<div id='teamDisplay'></div><br/>";
    echo "<div id='saveButton' style='display:none'>
            <input class='button' type=submit name='save' value='Save changes'>
            <input class='button' type=submit name='bank' value='Bank money'>
            <input class='button' type=submit name='cancel' value='Cancel'>
          </div>";
  }
  echo "</form>";
?>

</body>
</html>