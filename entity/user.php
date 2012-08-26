<?php

require_once 'commonEntity.php';
CommonEntity::requireFileIn('/../dao/', 'teamDao.php');

/**
 * Represents a user.
 */
class User {
  private $userId;
  private $username;
  private $password;
  private $firstName;
  private $lastName;
  private $teamId;
  private $teamLoaded;
  private $team;
  private $isAdmin;

  public function __construct($userId, $username, $password, $firstName, $lastName, $teamId,
      $isAdmin) {
    $this->userId = $userId;
    $this->username = $username;
    $this->password = $password;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->teamId = $teamId;
    $this->teamLoaded = false;
    $this->isAdmin = $isAdmin;
  }

  public function getId() {
    return $this->userId;
  }

  public function getUsername() {
    return $this->username;
  }

  public function getPassword() {
    return $this->password;
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

  public function getTeam() {
    if ($this->teamLoaded != true) {
      $this->team = TeamDao::getTeamById($this->teamId);
      $this->teamLoaded = true;
    }
    return $this->team;
  }

  public function isAdmin() {
    return $this->isAdmin;
  }
}
?>