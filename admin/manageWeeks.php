<?php
  require_once '../util/sessions.php';
  require_once '../util/time.php';

  SessionUtil::checkUserIsLoggedInSuperAdmin();
  // if year is not specified, use the current year.
  $redirectUrl = "admin/manageWeeks.php";
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  	$redirectUrl .="?year=$year";
  } else {
  	$year = TimeUtil::getYearByEvent(Event::OFFSEASON_START);
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once '../util/layout.php';
  LayoutUtil::displayHeadTag("Manage Scoring Weeks", false);
?>

<script>
// shows the scoring week page for the specified year
function showYear(year) {
    // If year is blank, then clear the yearDisplay div.
	if (year=="" || year=="0") {
		document.getElementById("weekDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("weekDisplay"),
	    "../manager/weekManager.php?type=manage&year=" + year);
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
</script>

<body>

<?php
  require_once '../dao/weekDao.php';
  require_once '../util/yearManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(false, LayoutUtil::MANAGE_WEEKS_BUTTON);

  // allow user to choose year.
  YearManager::displayYearChooser($year, WeekDao::getMinimumYear(), WeekDao::getMaximumYear());

  echo "<div class='row-fluid'>
          <div class='span12 center'>";

  if (isset($_POST['save'])) {
    $weeks = WeekDao::getWeeksByYear($year);
    foreach ($weeks as $week) {
      $weekStartPicker = "weekStart" . $week->getWeekNumber();
      $updatedStartTime = $_POST[$weekStartPicker];
      $week->setStartTime($updatedStartTime);
      WeekDao::updateWeek($week);
    }
    echo "<br/><div class='alert alert-success'>Scoring Weeks Updated!</div>";
  }

  echo "<FORM ACTION='manageWeeks.php' METHOD=POST>";
  echo "<div id='weekDisplay'></div>";
?>

<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value);
</script>

<?php
  echo "<p><button class=\"btn btn-primary\" name='save' type=\"submit\">Save changes</button>";
  echo "&nbsp&nbsp<button class=\"btn\" name='cancel' type=\"submit\">Reset</button></p>";
  echo "</form>";
  echo "</div>"; // span12
  echo "</div>"; // row-fluid

  // Footer
  LayoutUtil::displayAdminFooter();
?>

</body>
</html>
