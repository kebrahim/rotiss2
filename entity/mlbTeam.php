<?php

/**
 * MLB Team
 */
class MlbTeam {
  private $mlbTeamId;
  private $city;
  private $name;
  private $abbreviation;
  private $league;
  private $division;

  public function __construct($mlbTeamId, $city, $name, $abbreviation, $league, $division) {
    $this->mlbTeamId = $mlbTeamId;
    $this->city = $city;
    $this->name = $name;
    $this->abbreviation = $abbreviation;
    $this->league = $league;
    $this->division = $division;
  }

  public function getId() {
    return $this->mlbTeamId;
  }

  public function getCity() {
    return $this->city;
  }

  public function getName() {
    return $this->name;
  }

  public function getAbbreviation() {
    return $this->abbreviation;
  }

  public function getLeague() {
    return $this->league;
  }

  public function getDivision() {
    return $this->division;
  }
}
?>