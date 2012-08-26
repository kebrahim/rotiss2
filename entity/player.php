<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'mlbTeamDao.php');
CommonEntity::requireFileIn('/../dao/', 'positionDao.php');

/**
 * Represents a player in the MLB.
 */
class Player {
  private $playerId;
  private $firstName;
  private $lastName;
  private $birthDate;
  private $mlbTeamId;
  private $mlbTeamLoaded;
  private $mlbTeam;
  private $positions;
  private $positionsLoaded;
  private $sportslineId;

  private static $HEADSHOT_URL = "http://sports.cbsimg.net/images/baseball/mlb/players/60x80/";
  private static $STPETES_URL = "http://stpetesorium.baseball.cbssports.com/players/playerpage/";

  /**
   * Sets the specified fields loads the positions from the database.
   */
  public function __construct($playerId, $firstName, $lastName, $birthDate, $mlbTeamId,
      $sportslineId) {
    $this->playerId = $playerId;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->birthDate = $birthDate;
    $this->mlbTeamId = $mlbTeamId;
    $this->mlbTeamLoaded = false;
    $this->positionsLoaded = false;
    $this->sportslineId = $sportslineId;
  }

  public function getId() {
    return $this->playerId;
  }

  public function setId($playerId) {
    $this->playerId = $playerId;
  }

  public function getFirstName() {
    return $this->firstName;
  }

  public function getLastName() {
    return $this->lastName;
  }

  public function getFullName() {
    return $this->firstName . " " . $this->lastName;
  }

  public function getBirthDate() {
    return $this->birthDate;
  }

  /**
   * Returns the age of the player based on their birth date.
   */
  public function getAge() {
    date_default_timezone_set('America/New_York');
    list($year,$month,$day) = explode("-",$this->birthDate);
    $year_diff  = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff   = date("d") - $day;
    if ($day_diff < 0 || $month_diff < 0)
      $year_diff--;
    return $year_diff;
  }

  public function getMlbTeam() {
    if ($this->mlbTeamLoaded != true) {
      $this->mlbTeam = MlbTeamDao::getMlbTeamById($this->mlbTeamId);
      $this->mlbTeamLoaded = true;
    }
    return $this->mlbTeam;
  }

  public function getPositions() {
    if ($this->positionsLoaded != true) {
      $this->positions = PositionDao::getPositionsByPlayerId($this->playerId);
      $this->positionsLoaded = true;
    }
    return $this->positions;
  }

  public function getPositionString() {
    $first_pos = 1;
    $positionString = '';
    foreach ($this->getPositions() as $position) {
      if ($first_pos == 0) {
        $positionString .= '/';
      } else {
        $first_pos = 0;
      }
      $positionString .= $position->getAbbreviation();
    }
    return $positionString;
  }

  public function playsPosition($position) {
    foreach ($this->getPositions() as $playedPosition) {
      if ($playedPosition->getId() == $position->getId()) {
        return true;
      }
    }
    return false;
  }

  public function getSportslineId() {
    return $this->sportslineId;
  }

  public function hasSportslineId() {
    return ($this->sportslineId > 0);
  }

  public function getHeadshotUrl() {
    return Player::$HEADSHOT_URL . $this->sportslineId . ".jpg";
  }

  public function getStPetesUrl() {
    return Player::$STPETES_URL . $this->sportslineId;
  }
}
?>