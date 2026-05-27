<?php

/**
 * A class that contains database-specific code for PostgreSQL 14
 */

include_once('./classes/database/Postgres13.php');

class Postgres14 extends Postgres13 {

	public $major_version = 14;

	function __construct($conn) {
		parent::__construct($conn);
	}

	/**
	 * Returns all tables in a given schema
	 * @param $schema The name of the schema
	 * @return All tables, sorted alphabetically
	 */
	function getTables($schema = '', $all = false) {
		if ($schema === '') {
			$schema = $this->_schema;
		}
		$this->clean($schema);

		$sql = "SELECT c.relname AS relname, pg_catalog.pg_get_userbyid(c.relowner) AS relowner, 
		               pg_catalog.obj_description(c.oid, 'pg_class') AS relcomment
		        FROM pg_catalog.pg_class c
		        LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
		        WHERE n.nspname = '{$schema}'
		          AND c.relkind IN ('r', 'p', 'v', 'f') 
		        ORDER BY relname";

		return $this->selectSet($sql);
	}
}
?>
