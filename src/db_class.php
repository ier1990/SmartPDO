<?php
/*
The provided db_class.php file is a PHP class designed to handle PDO connections and database operations. Here are some observations and suggestions for improvement:

Observations:
Class Structure: The class is well-structured with methods for connecting to the database, executing queries, and handling errors.
Error Handling: The class has a robust error handling mechanism, logging errors to a file if writable.
Debugging: The class includes a debug mode that provides detailed error messages and outputs.
Database Operations: The class provides methods for executing queries, fetching results, and preparing statements.
Suggestions for Improvement:    
1. Configuration File: The class uses a configuration file to store database connection settings. It would be helpful to include a sample configuration file with instructions on how to set it up.
2. Error Logging: The error logging mechanism could be improved by providing more detailed information, such as the date and time of the error.
3. Security: The class should include measures to prevent SQL injection attacks, such as using prepared statements and parameterized queries.
4. Documentation: The class would benefit from more detailed documentation, including descriptions of each method and its parameters.
5. Code Comments: Adding comments to the code would make it easier for developers to understand how the class works and how to use it.
6. Exception Handling: The class could benefit from using try-catch blocks to handle exceptions more effectively.
7. Code Refactoring: The class could be refactored to improve readability and maintainability, such as breaking down complex methods into smaller, more manageable functions.
8. Code Reusability: The class could be made more reusable by separating database connection logic from query execution logic.
9. Unit Testing: The class could be tested using unit tests to ensure that it functions as expected and to catch any bugs or issues.
10. Performance Optimization: The class could be optimized for performance by using caching mechanisms, connection pooling, or other techniques.
Overall, the db_class.php file provides a solid foundation for handling database operations in PHP, but there are areas where it could be improved to make it more robust, secure, and user-friendly.
*/


//db_class.php
//AI smart class to handle PDO connections
//by: ierpe
//date: 2019-01-01  
//version: 1.0
//license: MIT
//usage:
//include('db_class.php');
//$db = new db_class();
//$query = 'SELECT * FROM table';
//$result = $db->query($query);
//var_dump($result);
/*
example dbconfig array

$dbconfig = array(
  'path' => 'C:/xampp/htdocs/ier1990/PHP-PDO-Class/private/dbconfig.php',
  'dbtype' => 'mysql',
  'port' => '3306',
  'host' => 'localhost',
  'dbname' => 'test',
  'username' => 'root',
  'password' => 'password',
  'charset' => 'utf8',
  'debug' => '0',
) ;
include('db_class.php');
$db = new db_class($dbconfig);

*/


//class api
class db_class{    
    private $path_to_dbconfig = 'C:/xampp/htdocs/ier1990/PHP-PDO-Class/private/dbconfig.php';    
    private $path_to_errors = 'C:/xampp/htdocs/ier1990/PHP-PDO-Class/private/log/';    
    private $settings;
    private $config;

    public $db;
    private $db_dbtype = array('mysql','pgsql','sqlite','oracle');
    //private $db_port = 3306;
    //private $db_host = 'localhost';
    //private $db_dbname = 'database';
    //private $db_charset = 'utf8';
    //private $db_prefix = '_';
    private $db_debug = false;// true = show DB-Errors (for development only!)
    public $errors = array();
    

    /**
     * Connect to database and set parameters array
     * @access public
     * @return object
     */    
    public function __construct($dbconfig=false) {        
       
        if($this->Connect($dbconfig) == false) {
            $this->addError('ERROR Database connection failed');            
            if($this->db_debug) {$this->displayError(); var_dump($this);}
            return false;

        }else{
            if($this->db_debug) {var_dump($this);}
            return $this->db;
        }
    }

    /**
     * Connect to database
     * @access private
     * @return object
     */
    private function Connect($dbconfig=false) {         
        
        if($this->setSettings($this->path_to_dbconfig)==false){return false;}                   

        $d= $this->settings['dbtype'] . ':dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . '';

        try {
            $this->db = new PDO($d, $this->settings['username'], $this->settings['password']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $this->db;
        } catch (PDOException $e) {
            $this->addError('ERROR: ' . $e->getMessage());
            //if path_to_errors is writable to create log file
            if(is_writable($this->path_to_errors)){
                $this->path_to_errors = $this->path_to_errors . 'PDOErrors' . date('Y-m-d-H-i-s') . '.log';   
                file_put_contents($this->path_to_errors, $e->getMessage(), FILE_APPEND);
                $this->addError('ERROR: written to ' . $this->path_to_errors);
                exit;
            }else{
                $this->addError('ERROR: path_to_errors not writable');
            }                   
            return false;
        }
        return false;
    }

    /**
     * Set settings array
     * @access private
     * @return array
     */
    private function setSettings($dbconfigfile=false) {
        if (is_array($dbconfigfile)) {
            $this->settings = $dbconfigfile;
        } elseif ($dbconfigfile == false && file_exists($this->path_to_dbconfig)) {
            $this->settings = include($this->path_to_dbconfig);
        } elseif (file_exists($dbconfigfile)) {
            $this->settings = include($dbconfigfile);
        } else {
            $this->addError('ERROR Database settings file not found');
            return false;
        }

        if (isset($this->settings['debug'])) {
            $this->db_debug = $this->settings['debug'];
        } else {
            $this->addError('ERROR Database settings debug not set');
            return false;
        }

        if ($this->db_debug) {
            $this->addError('Debug On');
        }
        $this->addError('Success Database settings loaded');
        return $this->settings;
    }
    

    /**
     * Query database
     * @access public
     * @return array
     */
    public function query($query,$params=false) {
    
        //trim query
        $query = trim($query);

        //check if query is empty
        if(empty($query)){
            $this->addError('ERROR: query is empty');
            if($this->db_debug) {$this->displayError();}
            return false;
        }

        //check if query string is safe
        //if $params is not false aka an array 
        //then query & array can be used to delete|update|insert|create|drop|alter tables
        //unsure if this is the best way to check for safe queries
        //consider using prepared statements and parameterized queries for security
        //hard to do when using this class to update/rewrite old mysql php5 code
        //https://stackoverflow.com/questions/18239404/how-to-check-if-a-query-is-safe
        //This stackoverflow question was voluntarily removed by its author.
        //better to use class from Composer called "voku/anti-xss"            
        //https://packagist.org/packages/voku/anti-xss
        if(($params==false) && (preg_match('/(delete|update|insert|create|drop|alter)/i', $query))){
            $this->addError('ERROR: query is not safe');
            if($this->db_debug) {$this->displayError();}
            return false;
        }


        //check if $params is array
        if(is_array($params)){
            $stmt = $this->db->prepare($query);
            if($stmt==false) {
                $this->addError('ERROR: prepare');
                if($this->db_debug) {$this->displayError();}
                return false;
            }
            
            if($stmt->execute($params) == false) {
                $this->addError('ERROR: execute');
                if($this->db_debug) {$this->displayError();}
                return false;
            }
            $result = $stmt->fetchAll();
            if(count($result) == 0){
                $this->addError('ERROR: fetchAll= 0');
                if($this->db_debug) {$this->displayError();}
                return false;
            }
            return  $result;
        }

        //no $params
        $stmt = $this->db->prepare($query);
        if($stmt==false) {
            $this->addError('ERROR: prepare');
            if($this->db_debug) {$this->displayError();}
            return false;
        }
        
        if($stmt->execute() == false) {
            $this->addError('ERROR: execute');
            if($this->db_debug) {$this->displayError();}
            return false;
        }
        $result = $stmt->fetchAll();
        if(count($result) == 0){
            $this->addError('ERROR: fetchAll= 0');
            if($this->db_debug) {$this->displayError();}
            return false;
        }
        
        return  $result;


    }
    
    /**
     * Single query database
     * @access public
     * @return array
     */
    public function single($query) {
        //echo $query;

            $stmt = $this->db->prepare($query);
            if($stmt==false) {
                $this->addError('ERROR: prepare');
                return false;
            }
            
            if($stmt->execute() == false) {
                $this->addError('ERROR: execute');
                return false;
            }
            $result = $stmt->fetch();
            if(is_array($result) == false){
                $this->addError('ERROR: fetchAll= 0');
                return false;
            }
            return $result;
        }

    /**
     * Prepare statement
     * @access public
     * @return object
     */
    public function prepare($query) {
        //echo $query;
        if($this->db_debug){
            $this->addError('prepare: ' . $query);
            $this->displayError();
        }

            $stmt = $this->db->prepare($query);
            if($stmt==false) {
                $this->addError('ERROR: prepare');
                if($this->db_debug){$this->displayError();}
                return false;
            }
            
            return $stmt;
    } 
        
    /*
     * $array = array(':id' => $id, etc
     * $stmt = $db->prepare($query);
     * $db->execute($stmt,$array); 
     * or
     * $db->execute($stmt);
     * return true on success
     * return false on failure
    */
    public function execute($stmt,$array=false) {
        //echo $query;
        if($array==false){
            if($stmt->execute() == false) {
                $this->addError('ERROR: execute failed');
                if($this->db_debug){$this->displayError();}
                return false;
            }
        }else{
            if($stmt->execute($array) == false) {
                $this->addError('ERROR: execute array failed');
                if($this->db_debug){$this->displayError();}
                return false;
            }            
        }    
        return true;
    }

    /**
     * Fetch statement
     * @access public
     * @return array
     */
    public function fetch($stmt) {
        //echo $query;

            $result = $stmt->fetch();
            if(is_array($result) == false){
                $this->addError('ERROR: fetchAll= 0');
                return false;
            }
            return $result;
    }

    /**
     * Fetch all statement
     * @access public
     * @return array
     */
    public function fetchAll($stmt) {
        //echo $query;

            $result = $stmt->fetchAll();
            if(is_array($result) == false){
                $this->addError('ERROR: is_array fetchAll= 0');
                return false;
            }
            if(count($result) == 0){
                $this->addError('ERROR: count fetchAll= 0');
                return false;
            }
            return $result;
    }

    /**
     * Last insert id
     * @access public
     * @return int
     */
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }

    /**
     * Get current directory
     * @access public
     * @return string
     */
    public function getcwd_name($path=false) {
        //////////////////////////////////////////////
        /// Get current directory for default dfilename
        //////////////////////////////////////////////
        if($path){return getcwd(); }
        $ddirarray = explode(DIRECTORY_SEPARATOR, getcwd());
        return $ddirarray[(count($ddirarray)-1)];
        
    }


    /**
     * Add error to array
     * @access public
     * @return array
     */
    public function addError($error) {
        $this->errors[] = addslashes($error);
    }   
    
    /**
     * Display error
     * @access public
     * @return string
     */
    public function displayError($display=true) {
        $a='<pre>';
        foreach($this->errors as $error) {
            $a.= $error . "\n";
        }
        $a.='</pre>';
        if($display){echo $a;}
        return $a;
    }    
    
}

?>