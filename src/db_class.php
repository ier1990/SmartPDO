<?php


//class api
class db_class{
    private $path_to_dbconfig = 'C:/xampp/htdocs/ier1990/PHP-PDO-Class/private/dbconfig.php';
    private $path_to_errors = 'C:/xampp/htdocs/ier1990/PHP-PDO-Class/private/log/';    
    private $settings;
    private $config;
    public $db;
    private $db_dbtype = array('mysql','pgsql','sqlite','oracle');
    private $db_debug = false;// true = show DB-Errors (for development only!)
    public $errors = array();
    

    /**
     * Connect to database and set parameters array
     * @access public
     * @return object
     */    
    public function __construct($dbconfig=false) {
        $this->path_to_errors = $this->path_to_errors . 'PDOErrors' . date('Y-m-d-H-i-s') . '.log';
       
        if($this->Connect($dbconfig) == false) {
            $this->addError('ERROR Database connection failed');
            $this->displayError();
            if($this->db_debug) {var_dump($this);}
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
    private function Connect($dbconfig=flase) {         
        
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
            file_put_contents($this->path_to_errors, $e->getMessage(), FILE_APPEND);            
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
        if(is_array($dbconfigfile)) 
        {
            $this->settings = $dbconfigfile;            
            $this->db_debug = isset($this->settings['debug']) ? $this->settings['debug'] : false;
            if($this->db_debug) {
                //$this->displayError();
                $this->addError('Success Database settings loaded from Array');
            }
            return $this->settings;
        } 
        elseif($dbconfigfile==false) 
        {
            if(file_exists($this->path_to_dbconfig))
            {
                $this->settings = include($this->path_to_dbconfig);
                $this->db_debug = isset($this->settings['debug']) ? $this->settings['debug'] : false;
                if($this->db_debug) {
                    //$this->displayError();
                    $this->addError('Success Database settings loaded from file');
                }
                return $this->settings;
            }
            else 
            {
                $this->addError('ERROR Database settings file not found');
                return false;
            }
        }
       elseif(file_exists($dbconfigfile))
        {
                $this->settings = include($dbconfigfile);
                $this->db_debug = isset($this->settings['debug']) ? $this->settings['debug'] : false;
                if($this->db_debug) {
                    //$this->displayError();
                    $this->addError('Success Database settings loaded from file');
                }
                return $this->settings;
        }
        else 
        {
            $this->addError('ERROR Database settings file not found');
            return false;
        }
        return $this->settings;
    }
    //query()
    public function query($query) {
        //echo $query;

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
    //single()
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

    //prepare()
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
    
    //execute()
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

    //fetch()
    public function fetch($stmt) {
        //echo $query;

            $result = $stmt->fetch();
            if(is_array($result) == false){
                $this->addError('ERROR: fetchAll= 0');
                return false;
            }
            return $result;
    }

    //fetchAll()
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

    //lastInsertId()
    public function lastInsertId() {
        return $this->db->lastInsertId();
    }


    public function getcwd_name($path=false) {
        //////////////////////////////////////////////
        /// Get current directory for default dfilename
        //////////////////////////////////////////////
        if($path){return getcwd(); }
        $ddirarray = explode(DIRECTORY_SEPARATOR, getcwd());
        return $ddirarray[(count($ddirarray)-1)];
        
    }


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
    
}

?>