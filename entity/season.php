<?php

/**
 * Represents a fantasy season.
 */
class Season {
  private $seasonId;
  private $sportType;
  private $year;
  private $winningOwnerName;
  private $winningTeamName;
  private $runnerUpOwnerName;
  private $runnerUpTeamName;
  private $result;

  const BASEBALL_TYPE = 'Baseball';
  const FOOTBALL_TYPE = 'Football';
  const BASKETBALL_TYPE = 'Basketball';
  const COOKOFF_TYPE = 'Cookoff';

  public function __construct($seasonId, $sportType, $year, $winningOwnerName, $winningTeamName,
  	  $runnerUpOwnerName, $runnerUpTeamName, $result) {
    $this->seasonId = $seasonId;
    $this->sportType = $sportType;
    $this->year = $year;
    $this->winningOwnerName = $winningOwnerName;
    $this->winningTeamName = $winningTeamName;
    $this->runnerUpOwnerName = $runnerUpOwnerName;
    $this->runnerUpTeamName = $runnerUpTeamName;
    $this->result = $result;
  }

  public function getId() {
  	return $this->seasonId;
  }

  public function getSportType() {
  	return $this->sportType;
  }

  public function getYear() {
  	return $this->year;
  }

  public function getWinningOwnerName() {
  	return $this->winningOwnerName;
  }

  public function getWinningTeamName() {
  	return $this->winningTeamName;
  }

  public function getRunnerUpOwnerName() {
  	return $this->runnerUpOwnerName;
  }

  public function getRunnerUpTeamName() {
  	return $this->runnerUpTeamName;
  }

  public function getResult() {
  	return $this->result;
  }
}
?>