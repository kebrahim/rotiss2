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
  private $email;
  private $teamId;
  private $teamLoaded;
  private $team;
  private $isAdmin;
  private $isSuperAdmin;

  public function __construct($userId, $username, $password, $firstName, $lastName, $email,
      $teamId, $isAdmin, $isSuperAdmin) {
    $this->userId = $userId;
    $this->username = $username;
    $this->password = $password;
    $this->firstName = $firstName;
    $this->lastName = $lastName;
    $this->email = $email;
    $this->teamId = $teamId;
    $this->teamLoaded = false;
    $this->isAdmin = $isAdmin;
    $this->isSuperAdmin = $isSuperAdmin;
  }

  public function getId() {
    return $this->userId;
  }

  public function getUsername() {
    return $this->username;
  }

  public function setUsername($username) {
    $this->username = $username;
  }

  public function getPassword() {
    return $this->password;
  }

  public function setPassword($password) {
    $this->password = $password;
  }

  public function getFirstName() {
    return $this->firstName;
  }

  public function setFirstName($firstName) {
    $this->firstName = $firstName;
  }

  public function getLastName() {
    return $this->lastName;
  }

  public function setLastName($lastName) {
    $this->lastName = $lastName;
  }

  public function getFullName() {
    return $this->firstName . " " . $this->lastName;
  }

  public function getEmail() {
    return $this->email;
  }

  public function setEmail($email) {
    $this->email = $email;
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

  public function isSuperAdmin() {
    return $this->isSuperAdmin;
  }
}
?>