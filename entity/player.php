<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'mlbTeamDao.php');
CommonEntity::requireFileIn('/../dao/', 'positionDao.php');
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

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
  private $statLines;

  private static $HEADSHOT_URL = "http://sports.cbsimg.net/images/baseball/mlb/players/60x80/";
  private static $STPETES_URL = "http://stpetesorium.baseball.cbssports.com/players/playerpage/";
  private static $BBR_SEARCH_URL = "http://www.baseball-reference.com/pl/player_search.cgi?search=";

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
    $this->statLines = array();
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

  public function getNameLink($isTopLevel) {
  	return $this->getIdLink($isTopLevel, $this->getFullName());
  }

  public function getIdLink($isTopLevel, $linkText) {
  	return "<a href='" . ($isTopLevel ? "" : "../") . "playerPage.php?player_id=" .
  	    $this->playerId . "'>" . $linkText . "</a>";
  }

  public function getNameNewTabLink($isTopLevel) {
    return $this->getIdNewTabLink($isTopLevel, $this->getFullName());
  }

  public function getIdNewTabLink($isTopLevel, $linkText) {
  	return "<a href='" . ($isTopLevel ? "" : "../") . "playerPage.php?player_id=" .
  			$this->playerId . "' target='_blank'>" . $linkText . "</a>";
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
    if ($month_diff < 0 || ($month_diff == 0 && $day_diff < 0)) {
      $year_diff--;
    }
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
    return $this->playsPositionById($position->getId());
  }

  public function playsPositionById($positionId) {
    foreach ($this->getPositions() as $playedPosition) {
      if ($playedPosition->getId() == $positionId) {
        return true;
      }
    }
    return false;
  }

  public function getBasePosition() {
    if ($this->playsPositionById(Position::STARTING_PITCHER) ||
        $this->playsPositionById(Position::RELIEF_PITCHER)) {
      return Position::PITCHER;
    }
    return Position::BATTER;
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

  public function getMiniHeadshotImg() {
  	return $this->getHeadshotImg(24, 32);
  }

  public function getHeadshotImg($width, $height) {
  	return "<img class='img_" . $width . "_" . $height . "' src='" . $this->getHeadshotUrl() . "'
  	         />";
  }

  public function getStPetesUrl() {
    return Player::$STPETES_URL . $this->sportslineId;
  }

  public function getBaseballReferenceUrl() {
    return Player::$BBR_SEARCH_URL . $this->lastName . "," . $this->firstName;
  }

  public function getFantasyTeam() {
  	return TeamDao::getTeamByPlayer($this);
  }

  public function getStatLine($year) {
  	if (isset($this->statLines[$year])) {
  	  return $this->statLines[$year];
  	}
  	return null;
  }

  public function setStatLine($year, $statLine) {
  	$this->statLines[$year] = $statLine;
  }

  public function getAttributes() {
  	return $this->getLastName() . ", " . $this->getFirstName() .
  	    " (" . $this->getPositionString() . ") - " . $this->getMlbTeam()->getAbbreviation();
  }

  public function displayPlayerInfo() {
  	// Name
  	echo "<h4>" . $this->getFullName() . "</h4>";

  	// Headshot
  	if ($this->hasSportslineId()) {
  		echo "<a href='" . $this->getStPetesUrl() . "' target='_blank'>" .
    		$this->getHeadshotImg(60,80). "</a>";
  	}
  }

  public function toString() {
  	return $this->getAttributes() . ", " . $this->getAge() . ", " . $this->getSportslineId();
  }
}
?>