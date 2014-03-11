<?php
  require_once 'util/sessions.php';
  require_once 'util/time.php';

  // if year is not specified, use the year based on keeper night.
  $redirectUrl = "allBudgetPage.php";
  if (isset($_REQUEST["year"])) {
  	$year = $_REQUEST["year"];
  	$redirectUrl .="?year=$year";
  } else {
  	$year = TimeUtil::getYearByEvent(Event::KEEPER_NIGHT) + 1;
  }
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("All Bodgets", true);
?>

<script>
// shows the draft page for the specified year
function showYear(year) {
    // If year is blank, then clear the yearDisplay div.
	if (year=="" || year=="0") {
		document.getElementById("yearDisplay").innerHTML="";
		return;
	}

	// Display team information.
	getRedirectHTML(document.getElementById("yearDisplay"),
	    "admin/displayYear.php?type=brognas&year=" + year);
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
  require_once 'dao/brognaDao.php';
  require_once 'util/yearManager.php';

  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::BUDGET_BUTTON);

  // allow user to choose year.
  YearManager::displayYearChooser($year, BrognaDao::getMinimumYear(), BrognaDao::getMaximumYear());
  echo "<div id='yearDisplay'></div><br/>";
?>

<script>
  // initialize yearDisplay with selected year
  showYear(document.getElementById("year").value);
</script>

<?php

  // Footer
  LayoutUtil::displayFooter();
?>

</body>
</html>
