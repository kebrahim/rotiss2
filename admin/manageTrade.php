<?php
  require_once '../util/sessions.php';
  SessionUtil::checkUserIsLoggedInAdmin();
  SessionUtil::logoutUserIfNotLoggedIn("admin/manageTrade.php");
?>

<!DOCTYPE html>
<html>
<head>
<title>St Pete's Rotiss - Manage Trades</title>
<link href='../css/bootstrap.css' rel='stylesheet' type='text/css'>
<link href='../css/stpetes.css' rel='stylesheet' type='text/css'>
</head>

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
    if (selection.options[i].text.toUpperCase().localeCompare(
    	    selectedOption.text.toUpperCase()) > 0) {
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
      "displayTeam.php?type=trade&team_id="+teamid+"&position="+position,true);
  xmlhttp.send();
}
</script>

<body>

<?php
  require_once '../dao/teamDao.php';
  require_once '../entity/trade.php';
  require_once '../util/time.php';
  require_once '../util/layout.php';

  function displayTeamPicker($teamNum, $teams) {
  	echo "<div class='span6 center'>
  	      <div class='chooser'>";
    echo "<label for='team$teamNum'>Select Team:</label>
          <select id='team$teamNum' class='span8 smallfonttable' name='team$teamNum' 
                  onchange='selectTeam($teamNum, this.value)'>
            <option value='0'></option>";
    foreach ($teams as $team) {
      echo "<option value='" . $team->getId() . "'" . ">" . $team->getName()
          . " (" . $team->getAbbreviation() . ")</option>";
    }
    echo "</select></div>"; // chooser
    echo "<div id='teamDisplay$teamNum'></div>";
    echo "</div>"; // span6
  }
  
  // Display nav bar.
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_TRADE_BUTTON);

  echo "<div class='row-fluid'>
          <div class='span12 center'>
            <h3>Let's Make a Deal!</h3><hr/>";
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
      echo "<p><button class=\"btn btn-primary\" name='confirmTrade' 
                       type=\"submit\">Confirm Trade</button>
            &nbsp&nbsp<button class=\"btn\" name='cancelTrade' type=\"submit\">Cancel</button></p>";
    } else {
      echo "<h3>Cannot execute trade! Please <a href='manageTrade.php' 
            class='btn btn-primary'>try again</a></h3>";
    }
  } elseif(isset($_POST['confirmTrade'])) {
    // Re-create trade scenario from SESSION
    $trade = new Trade();
    $trade->parseTradeFromSession();

    // Validate trade.
    if ($trade->validateTrade()) {
      // Initiate trade & report results.
      $trade->initiateTrade();
      echo "<a href='manageTrade.php' class='btn btn-primary'>Let's do it again!</a><br/>";
    } else {
      echo "<h3>Cannot execute trade! Please <a href='manageTrade.php' 
            class=\"btn btn-primary\">try again</a></h3>";
    }
  } else {
  	// clear out trade session variables from previous trade scenarios.
  	SessionUtil::clearSessionVarsWithPrefix("trade_");

    // allow user to select two teams.
    $teams = TeamDao::getAllTeams();

    // show pickers for team 1 and 2
    echo "<div class='row-fluid'>";
    displayTeamPicker(1, $teams);
    displayTeamPicker(2, $teams);
    echo "</div>"; // row-fluid
    
    echo "<div id='tradeButton' style='display:none'>
            <p><button class=\"btn btn-primary\" name='trade' 
                       type=\"submit\">Initiate Trade</button>
            &nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Cancel</button></p>
          </div>";
  }
    
  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid
  
  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
