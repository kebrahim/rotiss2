<?php

/**
 * MLB position
 */
class Position {
  private $positionId;
  private $name;
  private $abbreviation;

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
}
?>