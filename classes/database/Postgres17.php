<?php

/**
 * A class that contains database-specific code for PostgreSQL 17
 */

include_once('./classes/database/Postgres16.php');

class Postgres17 extends Postgres16 {

	public $major_version = 17;

	function __construct($conn) {
		parent::__construct($conn);
	}
}
?>
