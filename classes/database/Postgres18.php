<?php

/**
 * A class that contains database-specific code for PostgreSQL 18
 */

include_once('./classes/database/Postgres17.php');

class Postgres18 extends Postgres17 {

	public $major_version = 18;

	function __construct($conn) {
		parent::__construct($conn);
	}
}
?>
