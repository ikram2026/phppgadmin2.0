<?php

/**
 * A class that contains database-specific code for PostgreSQL 16
 */

include_once('./classes/database/Postgres15.php');

class Postgres16 extends Postgres15 {

	public $major_version = 16;

	function __construct($conn) {
		parent::__construct($conn);
	}
}
?>
