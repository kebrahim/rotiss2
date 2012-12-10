<?php

/**
 * Handles utility methods for given years.
 */
class YearManager {

  /**
   * Displays a year selection box, showing years between the specified min/max years, inside a 
   * single span12 cell, with the specified year selected.
   */
  public static function displayYearChooser($selectedYear, $minYear, $maxYear) {
  	if ($selectedYear < $minYear) {
  	  $selectedYear = $minYear;
  	} else if ($selectedYear > $maxYear) {
  	  $selectedYear = $maxYear;
  	}
  	 
  	echo "<div class='row-fluid'>
  	        <div class='span12 center chooser'>";
  	echo "<label for='year'>Choose year: </label>";
  	echo "<select id='year' class='input-small' name='year' onchange='showYear(this.value)'>";
  	for ($year = $minYear; $year <= $maxYear; $year++) {
  		echo "<option value='" . $year . "'";
  		if ($year == $selectedYear) {
  			echo " selected";
  		}
  		echo ">$year</option>";
  	}
  	echo "</select>";
  	echo "</div>"; // span12
  	echo "</div>"; // row-fluid
  }
}

?>