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
  
  public function setId($eventId) {
  	$this->eventId = $eventId;
  }
  
  public function getYear() {
  	return $this->year;
  }
  
  public function getEventType() {
  	return $this->eventType;
  }
  
  public function getEventTypeName() {
  	switch ($this->eventType) {
  		case Event::AUCTION:
  			return "Auction";
  		case Event::DRAFT:
  			return "Draft";
  		case Event::KEEPER_NIGHT:
  			return "Keeper Night";
  		case Event::OFFSEASON_START:
  			return "Start of Offseason";
  		case Event::RANKINGS_CLOSE:
  			return "Rankings Close";
  		case Event::RANKINGS_OPEN:
  			return "Rankings Open";
  		case Event::SEASON_START:
  			return "Start of Season";
  		case Event::TRADE_DEADLINE:
  			return "Trade Deadline";
  		default:
  			return "Error";
  	}
  }
  
  public function getEventDate() {
  	return $this->eventDate;
  }
  
  public function setEventDate($eventDate) {
  	$this->eventDate = $eventDate;
  }
  
  public function toString() {
  	return $this->year . ", " . $this->eventType . ": " . $this->eventDate;
  }
}

?>