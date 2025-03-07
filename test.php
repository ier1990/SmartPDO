<?php


$dbname = isset($_GET['dbname']) ? $_GET['dbname'] : '';

echo getcwd() . "<br>";

require_once( getcwd()."/src/db_class.php");
$db_class = new db_class();
//echo "<pre>".print_r($db_class,true)."</pre>";
//
//
echo "<br>db_class->getcwd_name()=". $db_class->getcwd_name();
// 'SHOW DATABASES' 
$dbs = $db_class->query( 'SHOW DATABASES' );
//echo "<pre>".print_r($dbs,true)."</pre>";
echo "<br>SHOW DATABASES:<br>";
foreach( $dbs as $db ) {
    echo '<a href="?dbname='. $db['Database'] . '" >'.$db['Database'] .'</a>'."<br>";    
}
// 'select schema_name from information_schema.schemata'
$dbs = $db_class->query( 'select schema_name from information_schema.schemata' );
//echo "<pre>".print_r($dbs,true)."</pre>";
echo "<br>Databases information_schema:<br>";
foreach( $dbs as $db ) {
    echo '<a href="?dbname='. $db['schema_name'] . '" >'.$db['schema_name'] .'</a>'."<br>";
}

if($dbname==""){exit;}

// 'SHOW TABLES' 
//Our SQL statement, which will select a list of tables from the current MySQL database.
$sql = "SHOW TABLES FROM $dbname";

//Prepare our SQL statement,
$statement = $db_class->prepare($sql);

//Execute the statement.
$statement->execute();

//FetchAll the rows from our statement.
$tables = $statement->fetchAll(PDO::FETCH_NUM);

//Loop through our table names.
foreach($tables as $table){
    //Print the table name out onto the page.
    echo $table[0], '<br>';
}
echo "<pre>".print_r($tables,true)."</pre>";





echo "<HR>end of test.php<HR>";
?>