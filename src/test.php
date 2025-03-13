<?php
if (!session_id()) @session_start();


// Production
//error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
//ini_set('display_errors', 0);

// Development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//ini_set('memory_limit','1024M');
// Fatal error: Allowed memory size of 134,217,728 bytes exhausted
//

$path = "db_class.php";
require_once($path);


echo 'Database Connect from array<br>';
$dbconfig = array (
    'path' => 'dbconfig.php',
    'dbtype' => 'mysql',
    'port' => '3306',
    'host' => 'localhost',
    'dbname' => '',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8',
    'debug' => '0',
  ) ;

$db2 = new db_class($dbconfig);  
$query = "SHOW DATABASES";
$result = $db2->query($query);
var_dump($result);
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
echo '<hr>Database Connect from default file<br>';
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

$db_class = new db_class();
//var_dump($db_class);

if($db_class === false){
    echo "Error: Cannot connect to Database";
    exit;
}


//check if Database ChatGPT exists
$sql = "SHOW DATABASES";

//Prepare our SQL statement,
$statement = $db_class->prepare($sql);

//Execute the statement.
$statement->execute();

//FetchAll the rows from our statement.
$databses = $statement->fetchAll(PDO::FETCH_NUM);

var_dump($databses);

///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
echo '<hr>';
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
$database = "ChatGPT";

//Loop through our table names to see if $table exists.
$db_exists = false;
foreach($databses as $row){
    if($row[0] == $database){
        $db_exists = true;
        echo "<br>Database $database exists: $db_exists<br>";
        break;
    }
}


//if table does not exist, create it
if(!$db_exists){
    $sql = "CREATE DATABASE `ChatGPT`";
    $sql1 = "CREATE TABLE `notes` (\n"

    . "      `id` int(99) NOT NULL AUTO_INCREMENT,\n"

    . "      `my_parent` int(99) NOT NULL,\n"

    . "      `prompt` text NOT NULL,\n"

    . "      `response` text NOT NULL,\n"

    . "      `settings` text NOT NULL,\n"

    . "      `timestamp` int(75) NOT NULL,\n"

    . "      PRIMARY KEY (`id`)\n"

    . "    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";


    $sql2 = "CREATE TABLE `settings` (\n"

    . "      `id` int(99) NOT NULL AUTO_INCREMENT,\n"

    . "      `title` varchar(254) NOT NULL,\n"

    . "      `data` text NOT NULL,\n"

    . "      `timestamp` int(55) NOT NULL,\n"

    . "      PRIMARY KEY (`id`)\n"

    . "    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";

    $statement = $db_class->prepare($sql);
    $statement->execute();    
    echo "<br>Database $database created<br>";

    $statement = $db_class->prepare($sql1);
    $statement->execute();
    echo "<br>Table notes created<br>";

    $statement = $db_class->prepare($sql2);
    $statement->execute();
    echo "<br>Table settings created<br>";



}

//show tables
$sql = "SHOW TABLES FROM $database";    
$statement = $db_class->prepare($sql);
$statement->execute();
$tables = $statement->fetchAll(PDO::FETCH_NUM);
var_dump($tables);


///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
echo '<hr>';
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

echo '<code><pre>
require_once("db_class.php");
$db_class = new db_class();
echo $db_class->getcwd_name();
</pre></code>
';
echo $db_class->getcwd_name();
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
echo '<hr>';
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////

echo '<code><pre>
$db_class->addError("ERROR: Testing");
$db_class->addError("ERROR: Testing 2");
$db_class->displayError();
</pre></code>
';
$db_class->addError("ERROR: Testing");
$db_class->addError("ERROR: Testing 2");
$db_class->displayError();
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////
echo '<hr>';
///////////////////////////////////////////////////////
///////////////////////////////////////////////////////





//echo '<pre>' . var_export($db_class, true) . '</pre>';

highlight_string("<?php\n\$db_class =\n" . var_export($db_class, true) . ";\n?>");

$get_tpage=$path;
if(is_readable($get_tpage)){
    echo '<div class="medium" style="overflow:scroll; overflow-x:hidden; height:50%;">';
    echo "Displaying file:" . $get_tpage . "<br>";
//Caution
//Care should be taken when using the highlight_file() function to make sure that you do not
// inadvertently reveal sensitive information such as passwords or any other type of information
// that might create a potential security risk.
    // color purple hex #800080
    ini_set('highlight.comment', '#800080; font-weight: bold;');
    //ini_set('highlight.default', '#000000');
    $file =  highlight_file($get_tpage, true);

//since the output is returned as html and new lines are broken with the <br /> tag, let's explode each line to array using <br /> to recognise a new line
    $file = explode ( '<br />', $file );
//first line number should be 1 right?
    $i = 1;
//let's wrap the output with a table
    echo '<table class="table table-striped table-hover ">';
//Now for each line we are gonna add line number to it and wrap it up with their divs
    foreach ( $file as $line ) {
        echo '<tr><td width="34">';
        echo $i;
        echo '. ';
        echo '</td>';

        echo '<td class="syntax-highlight-line">';
        echo $line;
        echo '</td></tr>';

        $i++;
    }
echo '</table>';


    echo '</div>';
}
