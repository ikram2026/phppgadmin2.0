<?php

/**
 * Class to represent a database connection
 *
 * phpPgAdmin 2.0 Modernized Engine Build
 */

include_once('./classes/database/ADODB_base.php');

class Connection {

	var $conn;
	
	// The backend platform. Set to UNKNOWN by default.
	var $platform = 'UNKNOWN';
	
	/**
	 * Creates a new connection. Will actually make a database connection.
	 * @param $fetchMode Defaults to associative. Override for different behaviour
	 */
	function __construct($host, $port, $sslmode, $user, $password, $database, $fetchMode = ADODB_FETCH_ASSOC) {
		$this->conn = ADONewConnection('postgres7');
		$this->conn->setFetchMode($fetchMode);

		// Ignore host if null
		if ($host === null || $host == '') {
			if ($port !== null && $port != '')
				$pghost = ':'.$port;
			else
				$pghost = '';
		} else {
			$pghost = "{$host}:{$port}";
		}

		// Add sslmode to $pghost as needed
		if (($sslmode == 'disable') || ($sslmode == 'allow') || ($sslmode == 'prefer') || ($sslmode == 'require')) {
			$pghost .= ':'.$sslmode;
		} elseif ($sslmode == 'legacy') {
			$pghost .= ' requiressl=1';
		}

			$this->conn->connect($pghost, $user, $password, $database);

		// phpPgAdmin 2.0: Force expose the exact authentication error trace
		if (!isset($this->conn->_connectionID) || !$this->conn->_connectionID) {
			echo "<h2>PostgreSQL Connection Failed!</h2>";
			echo "<strong>Error Message:</strong> " . pg_last_error() . "<br/>";
			echo "<strong>Target Host:</strong> {$pghost} | <strong>User:</strong> {$user}<br/>";
			exit;
		}
	}
	
	/**
	 * Gets the name of the correct database driver to use. As a side effect,
	 * sets the platform.
	 * @param (return-by-ref) $description A description of the database and version
	 * @return The class name of the driver eg. Postgres84
	 * @return null if version is < 7.4
	 * @return -3 Database-specific failure
	 */
	function getDriver(&$description) {

		$v = pg_version($this->conn->_connectionID);
		if (isset($v['server'])) $version = $v['server'];
		
		// If we didn't manage to get the version without a query, query...
		if (!isset($version)) {
			$adodb = new ADODB_base($this->conn);
	
			$sql = "SELECT VERSION() AS version";
			$field = $adodb->selectField($sql, 'version');
	
			// Check the platform, if it's mingw, set it
			if (preg_match('/ mingw /i', $field))
				$this->platform = 'MINGW';
	
			$params = explode(' ', $field);
			// Match standard version formats like "PostgreSQL 18.4..."
			$version = isset($params[1]) ? $params[1] : $field; 

		}
		
		$description = "PostgreSQL {$version}";

		// Extract the absolute major version dynamically (handles both "18.4" and "9.6")
		$versionParts = explode('.', $version);
		$majorVersion = (int)$versionParts[0];

		// phpPgAdmin 2.0: Route all double-digit modern PostgreSQL platforms cleanly
		if ($majorVersion >= 14) {
			return 'Postgres';
		}

		// Fallback handlers for legacy structural versions
		switch ($majorVersion) {
			case 13: return 'Postgres13';
			case 12: return 'Postgres12';
			case 11: return 'Postgres11';
			case 10: return 'Postgres10';
		}    

		switch (substr($version, 0, 3)) {
			case '9.6': return 'Postgres96'; break;
			case '9.5': return 'Postgres95'; break;
			case '9.4': return 'Postgres94'; break;
			case '9.3': return 'Postgres93'; break;
			case '9.2': return 'Postgres92'; break;
			case '9.1': return 'Postgres91'; break;
			case '9.0': return 'Postgres90'; break;
			case '8.4': return 'Postgres84'; break;
			case '8.3': return 'Postgres83'; break;
			case '8.2': return 'Postgres82'; break;
			case '8.1': return 'Postgres81'; break;
			case '8.0':
			case '7.5': return 'Postgres80'; break;
			case '7.4': return 'Postgres74'; break;
		}

		/* All <7.4 versions are not supported */
		if ($majorVersion < 8 && substr($version, 0, 3) != '7.4')
			return null;

		// If unknown modern version, default to latest driver architecture safely
		return 'Postgres';
	}

	/** 
	 * Get the last error in the connection
	 * @return Error string
	 */
	function getLastError() {		
		return pg_last_error($this->conn->_connectionID);
	}
}
