<?php session_start(); ?>
<html>
<head>
<title>Trading</title>
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
// toggles the headerbox and tradebox divs of the specified position [1/2]
function toggle(position) {
  var head = document.getElementById("headerbox" + position);
  var ele = document.getElementById("tradebox" + position);
  if (ele.style.display == "block") {
    ele.style.display = "none";
    head.style.display = "none";
  } else {
    ele.style.display = "block";
    head.style.display = "block";
  }
}

// sets the display of the div with the specified id
function setDisplay(id, display) {
  var divid = document.getElementById(id);
  divid.style.display = display;
}

// returns the index of the teamName in the selection dropdown
function getIndex(selection, teamName) {
  for (var i=0; i<selection.length; i++) {
    if (selection.options[i].text.toUpperCase() == teamName.toUpperCase()) {
       return i;
    }
  }
  return -1;
}

// adds the specified option to the specified selection at the specified position or at the end
// if position is -1.
function addOption(option, selection, position) {
  try {
	if (position == -1) {
	  selection.add(new Option(option.text, option.value), null);
	} else {
	  selection.add(new Option(option.text, option.value), selection.options[position]);
	}
  } catch(err) {
	// IE only
	if (position == -1) {
	  selection.add(new Option(option.text, option.value));
	} else {
      selection.add(new Option(option.text, option.value), position);
	}
  }
}

// adds the specified selectionOption to the specified selection, ensuring the list remains in
// alphabetical order.
function addOptionToDropDown(selection, selectedOption) {
  for (var i=0; i<selection.length; i++) {
	// if selection.options[i] comes after teamName, add it before this position
    if (selection.options[i].text.toUpperCase().localeCompare(selectedOption.text.toUpperCase()) > 0) {
      addOption(selectedOption, selection, i);
      return;
    }
  }
  // new option should come after everything in selection; add at end.
  addOption(selectedOption, selection, -1);
}

// synchronizes the two drop-downs, ensuring any options missing in otherTeam [except for the
// option currently selected in selectedTeam] are added back in.
function synchronizeDropDowns(selectedTeam, otherTeam) {
  // if otherteam doesn't have a value in selected, add it, unless it's the value
  // currently selected in selectedTeam
  for (var i=0; i<selectedTeam.length; i++) {
    if ((i !== selectedTeam.selectedIndex)
        && (getIndex(otherTeam, selectedTeam.options[i].text) == -1)) {
      // other team doesn't have the unselected value, so add it.
      addOptionToDropDown(otherTeam, selectedTeam.options[i]);
    }
  }
}

function selectTeam(position, teamid) {
  var otherPosition = "1";
  if (position == "1") {
    otherPosition = "2";
  }
  var selectedTeam = document.getElementsByName("team"+position).item(0);
  var otherTeam = document.getElementsByName("team"+otherPosition).item(0);

  // If teamid is blank, then clear out that position.
  if (teamid=="" || teamid=="0") {
    document.getElementById("teamDisplay"+position).innerHTML="";

    // hide trade button
    setDisplay("tradeButton", "none");

    // Put previously selected team back in other drop-down
    synchronizeDropDowns(selectedTeam, otherTeam);
	return;
  } else {
    // when team is selected, remove it from the other dropdown.
    selectedTeamIndexInOtherTeam =
        getIndex(otherTeam, selectedTeam.options[selectedTeam.selectedIndex].text);
    if (selectedTeamIndexInOtherTeam > -1) {
      otherTeam.remove(selectedTeamIndexInOtherTeam);
    }

    // put previously selected team back in other drop down.
    synchronizeDropDowns(selectedTeam, otherTeam);
  }

  // Only show trade button if two teams are selected
  if (otherTeam.selectedIndex > 0) {
    setDisplay("tradeButton", "block");
  }

  // Display team information.
  if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp=new XMLHttpRequest();
  } else {// code for IE6, IE5
    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange=function() {
    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
	  document.getElementById("teamDisplay"+position).innerHTML=xmlhttp.responseText;
	}
  };
  xmlhttp.open("GET",
      "displayTeamForTransaction.php?type=trade&team_id="+teamid+"&position="+position,true);
  xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../util/time.php';
  require_once '../entity/trade.php';

  echo "<center><h1>Let's Make a Deal!</h1>";
  echo "<FORM ACTION='manageTrade.php' METHOD=POST>";

  // If trade button was pressed, execute validated trade.
  if(isset($_POST['trade'])) {
    // Create trade scenario.
    $trade = new Trade();
    $trade->parseTradeFromPost();

    // Validate trade.
    if ($trade->validateTrade()) {
      // display what will be traded
      $trade->showTradeSummary();

      // request final confirmation of trade before execution
      echo "<input class='button' type=submit name='confirmTrade' value='Confirm'>";
      echo "<input class='button' type=submit name='cancelTrade' value='Cancel'><br>";
    } else {
      echo "<h3>Cannot execute trade! Please <a href='manageTrade.php'>try again</a>.</h3>";
    }
  } elseif (isset($_POST['confirmTrade'])) {
    // Re-create trade scenario from SESSION
    $trade = new Trade();
    $trade->parseTradeFromSession();

    // Validate trade.
    if ($trade->validateTrade()) {
      // Initiate trade & report results.
      $trade->initiateTrade();
      echo "<br><a href='manageTrade.php'>Let's do it again!</a><br>";
    } else {
      echo "<h3>Cannot execute trade! Please <a href='manageTrade.php'>try again</a>.</h3>";
    }
  } else {
    // allow user to select two teams.
    $teams = TeamDao::getAllTeams();
    echo "<div id='column_container'>";

    // team 1
    echo "<div id='left_col'><div id='left_col_inner'>";
    echo "Select Team:<br><select name='team1' onchange='selectTeam(1, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select><br><br>";
    echo "<div id='teamDisplay1'></div><br/></div></div>";

    // team 2
    echo "<div id='right_col'><div id='right_col_inner'>";
    echo "Select Team:<br><select name='team2' onchange='selectTeam(2, this.value)'>
                         <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName() . "</option>";
    }
    echo "</select><br><br>";
    echo "<div id='teamDisplay2'></div><br/></div></div></div>";

    echo "<div id='tradeButton' style='display:none'>
            <input class='button' type=submit name='trade' value='Initiate Trade'>
            <input class='button' type=submit name='cancel' value='Cancel'>
          </div>";
  }
  echo "</form>";
?>

</body>
</html>
