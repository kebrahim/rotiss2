<?php

/**
 * Represents a scoring week during the season.
 */
class Week {
  private $weekId;
  private $year;
  private $weekNumber;
  private $startTime;

  public function __construct($weekId, $year, $weekNumber, $startTime) {
    $this->weekId = $weekId;
    $this->year = $year;
    $this->weekNumber = $weekNumber;
    $this->startTime = $startTime;
  }

  public function getId() {
    return $this->weekId;
  }

  public function getYear() {
    return $this->year;
  }

  public function getWeekNumber() {
    return $this->weekNumber;
  }

  public function getStartTime() {
    return $this->startTime;
  }

  public function setStartTime($startTime) {
    $this->startTime = $startTime;
  }

  public function __toString() {
    return $this->year . " - " . $this->weekNumber . ": " . $this->startTime;
  }
}
