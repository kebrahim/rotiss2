<?php

require_once 'commonUtil.php';
CommonUtil::requireFileIn('/../dao/', 'auctionDao.php');

/**
 * Handles utility methods for auction objects.
 */
class AuctionManager {

  /**
   * Displays an auction year selection box inside a single span12 cell with the specified year
   * selected.
   */
  public static function displayYearChooser($selectedYear) {
  	echo "<div class='row-fluid'>
  	        <div class='span12 center chooser'>";
  	$minYear = AuctionResultDao::getMinimumAuctionYear();
  	$maxYear = AuctionResultDao::getMaximumAuctionYear();
  	if ($selectedYear < $minYear) {
  	  $selectedYear = $minYear;
  	} else if ($selectedYear > $maxYear) {
  	  $selectedYear = $maxYear;
  	}
  	echo "<label for='year'>Choose year: </label>";
  	echo "<select id='year' class='input-small' name='year' onchange='showYear(this.value)'>";
  	for ($yr = $minYear; $yr <= $maxYear; $yr++) {
  	  echo "<option value='" . $yr . "'";
  	  if ($yr == $selectedYear) {
  	    echo " selected";
  	  }
  	  echo ">$yr</option>";
  	}
  	echo "</select>";
  	echo "</div>"; // span12
  	echo "</div>"; // row-fluid
  }
}

?>