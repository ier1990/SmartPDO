# SmartPDO Class
## SmartPDO Class

How to use:

```php
//include class
require_once( "/path/to/db/class/db_class.php");

//create new class using default path 
//private $default_path_to_dbconfig = '/path/to/db/array/dbconfig.php';
$db_class = new db_class();  

//create new class using array

$db_class = new db_class( array (
    'path' => '/path/to/db/array/dbconfig.php',
    'dbtype' => 'mysql',
    'port' => '3306',
    'host' => 'localhost',
    'dbname' => 'test',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8',
));

//or

$dbconfig = array (
    'path' => '/path/to/db/array/dbconfig.php',
    'dbtype' => 'mysql',
    'port' => '3306',
    'host' => 'localhost',
    'dbname' => 'test',
    'username' => 'root',
    'password' => 'password',
    'charset' => 'utf8',
);
$db_class = new db_class($dbconfig);

//or include path to file containg the ARRAY()
$path_to_db_file = "path_to_db_array_file.php";
$db_class = new db_class($path_to_db_file);



//Error Handling
    public $errors = array();

    //addError()
    public function addError($error) {
        $this->errors[] = addslashes($error);
    }   
    //display_error()
    public function displayError($display=true) {
        $a='<pre>';
        foreach($this->errors as $error) {
            $a.= $error . "\n";
        }
        $a.='</pre>';
        if($display){echo $a;}
        return $a;
    }


$db_class->addError("This is somehow an error");

$db_class->displayError();
//or
$errors=$db_class->displayError(false);
echo $errors;

```






