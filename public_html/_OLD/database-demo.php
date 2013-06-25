<?php
// Include class file in your script.
require('includes/config.php');
require('models/database.php');

// DB Credentials can be passed to constructor as arguments. 
// Alternatively they can be hardcoded into the class file. 



// init the class same as PDO
$db = new btgPDO( "mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass );

// INSERT, UPDATE, DELETE with prepared statements:
$db->insert( 'table', array( 'name'=>'John Doe', 'age'=>28 ) );
$db->update( 'table', array( 'age'=>29 ), array( 'name'=>'John Doe' ) );
$db->delete( 'table', array( 'name'=>'John Doe' ) );

// SELECT all, single row, single column and single variable:
$db->get_all( 'table', array( 'age'=>22 ) );
$db->get_row( 'table', array( 'name'=>'John Doe' ), array( 'name', 'email' ) );
$db->get_col( 'table', array( 'age'=>28 ), 'name' );
$db->get_var( 'table', array( 'name'=>'John Doe' ) );

// Check if a record exists: 
if( $db->exists( 'table', array( 'name'=>'John Doe' ) ) ) echo 'Record exists!';

// Get the count of matching records:
$db->get_count( 'table', array( 'age'=>22 ) );

// Debug:
$db->sql; // Holds the SQL query executed.
$db->bind; // Holds the bind parameters of a Prepared Statement.
$db->insert_id; // Holds the ID of last inserted row.
$db->num_rows; // Holds the number of rows affected by last query.
$db->result; // Holds the result of the last query executed.

$db->debug(); // Print out all necessary properties.