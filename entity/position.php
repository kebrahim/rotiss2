<?php

/**
 * MLB position
 */
class Position {
  private $positionId;
  private $name;
  private $abbreviation;

  const CATCHER = 1;
  const FIRST_BASE = 2;
  const SECOND_BASE = 3;
  const THIRD_BASE = 4;
  const SHORTSTOP = 5;
  const OUTFIELD = 6;
  const DESIGNATED_HITTER = 7;
  const STARTING_PITCHER = 8;
  const RELIEF_PITCHER = 9;

  const BATTER = "Batter";
  const PITCHER = "Pitcher";

  public function __construct($positionId, $name, $abbreviation) {
    $this->positionId = $positionId;
    $this->name = $name;
    $this->abbreviation = $abbreviation;
  }

  public function getId() {
    return $this->positionId;
  }

  public function getName() {
    return $this->name;
  }

  public function getAbbreviation() {
    return $this->abbreviation;
  }

  public function toString() {
  	return $this->name . " (" . $this->positionId . ") - " . $this->abbreviation;
  }
}
?>