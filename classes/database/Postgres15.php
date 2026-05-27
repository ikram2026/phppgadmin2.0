<?php

/**
 * A class that contains database-specific code for PostgreSQL 15
 */

include_once('./classes/database/Postgres14.php');

class Postgres15 extends Postgres14 {

	public $major_version = 15;

	function __construct($conn) {
		parent::__construct($conn);
	}
}
?>
