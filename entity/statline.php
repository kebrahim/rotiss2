<?php

/**
 * Represents a set of fantasy statistics.
 */
// TODO add rest of stats
class StatLine {
  private $fantasyPts;
  
  public function __construct($fantasyPts) {
  	$this->fantasyPts = $fantasyPts;
  }
  
  public function getFantasyPoints() {
  	return $this->fantasyPts;
  }
}
?>