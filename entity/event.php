<?php

/**
 * Represents an event during the fantasy season. If the event date has occurred, then it is said
 * to be the specified year, according to the specified event type. Otherwise, it is the previous
 * year.
 */
class Event {
  private $eventId;
  private $year;
  private $eventType;
  private $eventDate;
  
  const OFFSEASON_START = 1;
  const RANKINGS_OPEN = 2;
  const RANKINGS_CLOSE = 3;
  const AUCTION = 4;
  const KEEPER_NIGHT = 5;
  const DRAFT = 6;
  const SEASON_START = 7;
  const TRADE_DEADLINE = 8;
  
  public function __construct($eventId, $year, $eventType, $eventDate) {
  	$this->eventId = $eventId;
  	$this->year = $year;
  	$this->eventType = $eventType;
  	$this->eventDate = $eventDate;
  }
  
  public function getId() {
  	return $this->eventId;
  }
  
  public function getYear() {
  	return $this->year;
  }
  
  public function getEventType() {
  	return $this->eventType;
  }
  
  public function getEventDate() {
  	return $this->eventDate;
  }
}

?>