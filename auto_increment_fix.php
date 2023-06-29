<?php

// Database credentials
$db_host  = '';
$db_name  = '';
$db_username = '';
$db_password = '';

// Connection
$db = new PDO(
	'mysql:host='.$db_host.';dbname='.$db_name,
	$db_username,
	$db_password,
	[PDO::MYSQL_ATTR_INIT_COMMAND => 'SET sql_mode="NO_AUTO_VALUE_ON_ZERO"']
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<pre>";

try {

    // Tables
	$tables = $db->query('SHOW TABLES')->fetchAll();
	$tables = array_column($tables, 0);

    // Check each table
	foreach( $tables as $table ) {

		$result = $db->query("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '$table'")->fetchAll();

		// For each column
		foreach( $result as $column ) {

			// If column has extra autoincrement
			if( stripos($column['EXTRA'], 'auto_increment')!==false ) {

				echo "Found auto_increment in column <strong style='color:orange'>{$column['COLUMN_NAME']}</strong> of <strong style='color:green'>$table</strong>".PHP_EOL;

				// Find top value for this column
				$topRecordId = $db->query("SELECT MAX({$column['COLUMN_NAME']}) FROM $table")->fetchColumn();

                // If top value exists
				if(  !is_null($topRecordId) ) {

                    // Increase it
					$topRecordId++;

                    // Update top value for this table
					$db->query("ALTER TABLE `$table` auto_increment = $topRecordId")->execute();
					echo "Top record ID set to <strong style='color:red'>$topRecordId</strong>".PHP_EOL;
				}

				break;
			}
		}
	}

} catch (RuntimeException $e) {
	$db = null;
	die('ERROR: '.$e->getMessage());
}

echo "</pre>";
