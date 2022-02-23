<?PHP
# pDB_CORE-0.37c
# Author: BennyZaminga <bzaminga@web.de>

define( "PDB_CORE_VER", "0.37c",      true);
define( "PDB_NUM",      "pDB_NUM",   true);
define( "PDB_ASSOC",    "pDB_ASSOC", true);


/**
* We assume that the biggest possible string in pDB is PDB_NUM_STRINGLENGTH_MAX bytes.
* PDB's maximum size of a row is limited to 65500 bytes. This limitation will be removed.
*/
define( "PDB_NUM_LINEWIDTH_MAX",  65500); // this will disapear in further versions
define( "PDB_NUM_INTLENGTH_MAX",     24); // there are only 16 digits possible, but leave this to 24 cause of base64-encoding used
define( "PDB_NUM_STRINGLENGTH_MAX",4096); // change this to whatever you need
define( "PDB_NUM_BLOBLENGTH_MAX",    50); // leave this value untouched please !
define( "PDB_NUM_BLOBHEADER_LENGTH",500); // leave this value untouched please !
define( "PDB_FIELD_FILL_CHAR"      ,  0);
define( "PDB_DELIMETER"           , ";");


/**
* @desc pDB_CORE - the core of pDB.
* ------------------------------------.
* ( A nice introduction will follow here )
*
* NOTE:
* 		It was reported by users that FHNDL is somewhat near broken at the moment.
*		Do NOT use FHNDL-tables till this warning disappears, please.
*
*
* By Default the debug-switch CONF_DATA['DEBUG'] is set to false (disabled).
* Feel free to enable it in 'conf/pDB.conf.php' if you're encountering problems.
* @author Benny 'pokee' Zaminga	<bzaminga@web.de>
* @package pDB
* @access public
* @version 0.37b - Thu May 06 00:49:16 2004 - �Optimized for speed�
* - introduced Accelerator (still very alpha and buggy)
* @version 0.37c - Fri Aug 13 02:23:52 2004 - �Fixing up some bugs�
* - fixed a major bug in pDB_select() which caused wrong row-IDs
* - optimized a few passages to meet the new '===' recommendations while comparing values
* - disabled Accelerator by default (needs a lot of work to done thereon first)
* - fixed many odd bugs but a lot still remain (to be continued)
*/
class pDB_CORE{
    /**
    * @desc array $CONF_DATA associative array where configuration data is stored
    */
    var $CONF_DATA=array();
    /**
    * @desc array $CORE_DATA associative array where core data is stored
    */
    var $CORE_DATA=array();
    /** DEPRECATED (will be gone in next versions)
    * @desc string $DELIMETER (inherited from FHNDL-Class, only here for historical reasons)
    */
    #var $DELIMETER=";";
    /**
    * @desc array $TBL_PROP Array contanining the known table-properties (for table-properties-file).
    */
    var $TBL_PROP = array( "TBLTYPE", "TBLNAME", "USRNAME", "LASTLOG", "LASTCHG", "LOCKEDF", "DEFAULT", "AUTOINC");
    /**
    * @desc array $TBL_TYPE All supported table-types are listed herein.
    */
    # FHNDL-tables are currently not properly supported.
    #var $TBL_TYPE = array( "FHNDL", "LFIO");
    var $TBL_TYPE = array( "LFIO");
    /**
    * @desc array $FIELD_TYPE All supported field-types are listed herein.
    */
    var $FIELD_TYPE = array( 'int', 'string', 'blob', 'enum');
    /**
    * @desc array $PDB_FIELDLENGTH_CONSTANTS Field-Lengths resumed in assoc array.
    */
    var $PDB_FIELDLENGTH_CONSTANTS = array( 'int'=>PDB_NUM_INTLENGTH_MAX, 'string'=>PDB_NUM_STRINGLENGTH_MAX, 'blob'=>PDB_NUM_BLOBLENGTH_MAX, 'enum'=>PDB_NUM_STRINGLENGTH_MAX);
    
    /**
    * @desc int $NUM_AFFECTED_ROWS Number of affected (changed) rows by last
    * INSERT, UPDATE or DELETE call. Use pDB_affected_rows() to retrieve this
    * value, NEVER access it directly.
    */
    var $NUM_AFFECTED_ROWS = 0;
    
    /**
    * @desc array $COLUMN_PROPERTIES A dictionary of supported column-properties.
    * 'AUTOINCREMENT', 'PRIMARY', 'UNIQUE' are so called COLUMN_PROPERTIES in pDB.
    */
    var $COLUMN_PROPERTIES = array( 'AUTOINCREMENT', 'PRIMARY', 'UNIQUE', 'NOTNULL', 'DEFAULT');
    
    /**
    * @desc array $MESSAGE_BUFFER pDB's internal message_buffer.
    */
    var $MESSAGE_BUFFER = array();
    /**
    * @desc int $TIMER pDB's internal Timer-Slot.
    */
    var $TIMER = null;
    
    /**
    * @desc Loads the pDB.ini-file and extracts vital values.
    * Some settings (eg: DB_ROOT,BASE_DIR,OSTYPE) are evaluated.
    * Various internal buffers get created by calling this method.
    * NEW: pDB-0.37a and above will try to initialize the Accelerator and his
    * buffers. Since the core strongly depends of pDB_TABLE_OBJ, it will be
    * introduced a new check that looks if a TableObject is currently available
    * in certain environment or yields a error when this is not the case.
    * @param string Path to your 'pDB.conf.php'-file.
    * @param array prepopulated config array
    * @return boolean Returns true on success, false on failure.
    */
    function pDB_init( $ini_path, $preconfig = null, $preusers = null){
        // set execution-time to 0
        @set_time_limit(0);
        
        // set mute, CONF_DATA['DEBUG']=false
        $this->CONF_DATA['DEBUG'] = false;
        
        // read config file; added $preconfig param, to allow better config integration -GL 04.05.14
        if ( $preconfig != null) $this->CONF_DATA = $preconfig;		// use prepropulated config
        else if ( !is_file( $ini_path )) return false;				// pDB ini-file can not be found
        else $this->CONF_DATA = parse_ini_file( $ini_path, true);	// some annoying output can occur here !
        
        // set version number from used CORE in CONF_DATA
        $this->CONF_DATA['VERSION'] = PDB_CORE_VER;
        
        // extract OS type and report it to $CONF_DATA['OS']
        // tested OS are (Linux and win32)
        // NOTE : There is a directive in pDB.ini named OSTYPE.
        // If OSTYPE was not defined in conf/pDB.ini, PHP tries to guess looking at $_ENV['OSTYPE'].
        // If no OS could be defined then default is : Linux (not i'm saying that, statistics do ;-) )
        // It's not clear if $_ENV['OSTYPE'] is provided under different environment than CGI.
        // If you plan to let php autoselect the OS, disable the line containing OSTYPE in conf/pDB.ini.
        $this->CONF_DATA['OSTYPE'] = PHP_OS;
        if ( empty($this->CONF_DATA['OSTYPE'])) $this->CONF_DATA['OSTYPE'] = "Linux";

        // added $preusers param to allow better config integration -GL 04.05.14
       	if ( $preusers != null) $this->USER_DATA = $preusers;
       	else $this->USER_DATA = parse_ini_file($this->CONF_DATA['BASE_DIR'] . $this->CONF_DATA['CONF_DIR'] . 'pDB.users.php');

        // prepare a few buffers
        $this->CORE_DATA['DB_LIST']     = array();
        $this->CORE_DATA['TABLE_LIST']  = array();
        $this->CORE_DATA['DB_CURRENT']  = null;
        $this->CORE_DATA['AUTH']        = array(0,null,null,0);
        
        // ACCELERATOR
        $ACC                            = new pDB_TABLE_OBJ();
        $ACC->initTable( 'ACC', array( 'string id', 'string key', 'string value'));
        $this->ACC                      = $ACC;
        unset( $ACC);
        
        // Check if DB_ROOT exists
        if ( empty( $this->CONF_DATA['DB_ROOT']) or !@is_dir( $this->CONF_DATA['DB_ROOT'])){
            $this->_put_message( "pDB_PANIC", array(__FILE__, __LINE__),"Your DB_ROOT ('".$this->CONF_DATA['DB_ROOT']."') could not be found ! ", 1);
            return false;
        }
        
        // check if DB_ROOT is writable
        if ( !is_writable($this->CONF_DATA['DB_ROOT']."/.")){
            if($this->CONF_DATA['DEBUG']){
                $this->_put_message("pDB_PANIC", array(__FILE__, __LINE__), "Your DB_ROOT is not writable!", 0);
            }
            return false;
        }
        
        if($this->CONF_DATA['CONF_LOADED']<1){
            return false;
        }else{
            if ( $this->CONF_DATA['VERBOSE']){
                $this->_put_message( "pDB_WARNING", array( __FILE__, __LINE__),"pDB initialized on " . date('d-m-Y H:i s') . "");
            }
            return true;
        }
    }
    
    // --- MESSAGE-BUFFER-METHODS -----------------------------------
    /**
    * @desc Adds a new message to pDB's internal message_buffer (use $this->_get_message() to obtain the last message)
    * @param string $message_type Message-type can be : pDB_WARNING, pDB_ERROR, pDB_PANIC.
    * @param array $message_source Simply and always pass: �array(__FILE__,__LINE__)�.
    * @param string $message_text Message's text as string.
    * @param int $log Optional parameter passed as int. Can be 0 or 1. Set to one this message is gonna be written to pDB's log-file
    * @return void
    */
    function _put_message($message_type, $message_source=array(__FILE__,__LINE__), $message_text, $log=0){
        
        // look for optional 4th param and set to zero if not given
        $log = @func_get_arg(3);
        if ( (int)$log!=0 and (int)$log!=1)$log=0;
        
        // check if given $message_type is valid
        if($message_type!="pDB_WARNING" and $message_type!="pDB_ERROR" and $message_type!="pDB_PANIC"){
            $message_type="pDB_PANIC";	// This means after this method's code has executed, pDB will die !!
        }
        
        $message  = /* microtime(). */"[".$message_type."] ".basename($message_source[0]).", Line ".$message_source[1]." : ";
        $message .= "\n" . $message_text.chr(10);
        
        // push the given message into message_buffer
        array_push($this->MESSAGE_BUFFER, $message);
        
        // if DEBUG is set, output all to php://stdout
        if ( $this->CONF_DATA['DEBUG']){
            echo '<p>' . $message . '</p>';
        }
        
        // if the fourth optional param was specified as 1 then write this message to log-file
        if($log==1){
            // get path of log-file specified in conf/pDB.conf
            $log_path = $this->CONF_DATA['BASE_DIR']."".$this->CONF_DATA['LOG_FILE'];
            // if there is no log file, a new one gets generated
            if(!is_file($log_path)){
                $fp_log = @fopen($log_path, "w", false);
            }
            // there is a log-file, open it
            $fp_log = @fopen($log_path, "a", false);
            // finally if there's a open filepointer, write the contents of the message to log-file
            if($fp_log){
                fputs($fp_log, date('Ymd . H.i:s')." - ".$message);
                fclose($fp_log);
            }else{
                // report that the log file could not be accessed
                $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__),"The LogFile('$log_path') could not be found!");
            }
        }
        
        // pDB dies if a pDB_PANIC message is passed
        if ( $message_type == 'pDB_PANIC'){
            // debug-only, spools all errors to php://stdout
            while($err = $this->_get_message()){
                echo "<P>".$err."</P>";
            }
            // --
            die( "pDB died in cause off an internal fatal error that was encountered".chr(10));
        }
    }
    
    /**
    * @desc Gets the last message from pDB's internal message_buffer.
    * NOTE: Use $this->_put_message() to push a new message into buffer.
    * @param void
    * @return mixed Returns the last message as string or false when there is no more message in buffer
    */
    function _get_message(){
        $message = array_shift( $this->MESSAGE_BUFFER);
        return $message;
    }
    
    // ------------ TIMER METHODS ----------------------------------
    /**
    * @desc Starts the internal pDB_TIMER
    * @param void
    * @return void
    */
    function pDB_timer_start(){
        if($this->TIMER!=null){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "pDB_TIMER is allready running.");
        }
        $this->TIMER = microtime();
    }
    
    /**
    * @desc Stops the internal pDB_TIMER
    * @param void
    * @return void
    */
    function pDB_timer_end(){
        $this->TIMER = abs($this->TIMER - microtime());
    }
    
    /**
    * @desc Returns the internal's pDB_TIMER's value and resets the timer.
    * @param void
    * @return void
    */
    function pDB_timer_get(){
        $timer = explode(" ", $this->TIMER);
        $timer = $timer[0];
        $this->TIMER = null;
        return $timer;
    }
    
    // ----------------------------------------------------------------
    /**
    * @desc Returns indexed array containing the available DB_LIST
    * and refreshes the internal $this->CORE_DATA['DB_LIST']
    * @param void
    * @return array
    */
    function pDB_show_databases(){
        // check if DB_ROOT is properly set
        if ( empty( $this->CONF_DATA['DB_ROOT'])){
            $this->_put_message( "pDB_PANIC", basename(__FILE__).", ".__LINE__, " : NO DB_ROOT IS SET", 1);
        }
        
        // flush DB_LIST (fix)
        $this->CORE_DATA['DB_LIST'] = array();
        
        $dir = dir($this->CONF_DATA['DB_ROOT']);
        while($db = $dir->read($dir->handle)){
            if($db!="."&&$db!=".."){
                //check if .pdb-file is of valid kind before validating db
                if($this->pDB_check_dotpdb($db)){
                    array_push($this->CORE_DATA['DB_LIST'],$db);
                }
            }
        }
        return $this->CORE_DATA['DB_LIST'];
    }
    
    /**
    * Returns the number of available tables in the requested DB
    * @param void
    * @return int
    */
    function pDB_count_tables(){
        // no DB_CURRENT-check is actually performed
        $path = $this->getPath();
        $dir = dir($path);
        while($table = $dir->read($dir->handle)){
            if($this->is_pDB_table($table)){
                $tables++;
            }
        }
        return (int)$tables;
    }
    
    /**
    * @desc Returns indexed array containing the available TABLE_LIST in given DB or null if no db is selected
    * @param void
    * @return array
    */
    function pDB_show_tables(){
        // check if DB_CURRENT-flag is properly set
        if(!$this->CORE_DATA['DB_CURRENT']){
            $this->_put_message("pDB_ERROR", array(__FILE__, __LINE__), "pDB_show_tables() - DB_CURRENT-flag is not set. Select a database first.", 0);
        }
        
        $this->CORE_DATA['TABLE_LIST']=array();
        $path = $this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT'];
        $dir = dir($path);
        while($table = $dir->read($dir->handle)){
            if(is_file($path."/".$table) && is_file($path."/".$table.".pdk") && is_file($path."/".$table.".pdk")){
                // there is no check if it's a valid table, yet !
                array_push($this->CORE_DATA['TABLE_LIST'],$table);
            }
        }
        return $this->CORE_DATA['TABLE_LIST'];
    }
    
    /**
    * @desc Returns true when the DB_CURRENT-flag has been changed. If the '.pdb' file is missing or the database is not recognised as valid, false is returned.
    * @param string Path to DB to switch as current
    * @return boolean
    */
    function pDB_use_database($db_name){
        // check if db-name id allowed before doing anything
        if ( !$this->is_valid_name( $db_name)){
            $err = "pDB_use_database() - Name of database('$db_name') is invalid, a database can never have that name in pDB, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), $err, 0);
            return false;
        }
        // if DB is not found or has missing .pDB (pDB_ERR_23)
        if ( !is_dir( $this->CONF_DATA['DB_ROOT']."/".$db_name) && !is_file( $this->CONF_DATA['DB_ROOT']."/".$db_name."/.pdb")){
            $err  = "pDB_use_database() - Database('$db_name') could not be found or is maybe corrupt, error.";
            $err .= "\n\tThis error has been logged and the administrator will be informed of that problem.";
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), $err, 1);
            return false;
        }
        $this->CORE_DATA['DB_CURRENT'] = $db_name;
        return true;
    }
    
    /**
    * @desc Creates a new database(with .pdb-file) and returns true(user must be logged in, else or in case of failure false is returned!)
    * @param string Name of new DB as string
    * @return boolean
    */
    function pDB_create_database($db_name){
        // some preliminary checks
        if ( !$this->CORE_DATA['AUTH'][0]){
            // user is not logged in correctly
            $this->_put_message("pDB_ERROR", array(__FILE__, __LINE__), "pDB_create_database() - You are not logged in. Please login first.", 0);
            return false;
        }
        
        // check given db_name, if it's of valid kind
        if ( !$this->is_valid_name( $db_name)){
            // db_name can not contain following chars : space, points, slashes
            $err = "pDB_create_dotpdb() - Given db_name('".$db_name."') is not valid !";
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), $err, 0);
            return false;
        }
        
        //check if DB already exists
        if ( is_dir( $this->CONF_DATA['DB_ROOT']."/".$db_name)){
            $err = "pDB_create_dotpdb() - Database('$db_name') already exists, aborting creation.";
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), $err, 0);
            return false;
        }
        
        // made it till here? do the job now!
        $mkdir = @mkdir( $this->CONF_DATA['DB_ROOT'].$db_name);
        
        // set the appropriate rights on new folder (db)
        @chmod( $this->CONF_DATA['DB_ROOT']."/".$db_name, 0777);
        
        //if DB-folder was generated successfully create dotpdb
        if ( $mkdir){
            $this->pDB_create_dotpdb($db_name);
            return true;
        }else{
            # EMITT ERROR HERE
            $err = "pDB_create_dotpdb() - Could not create directory('".$this->CONF_DATA['DB_ROOT'].$db_name."'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
    }
    
    
    /**
    * @desc Delete a database and all tables in it.
    * @param string $db_name Name of database to delete as string.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_drop_database( $db_name){
        // check given db_name
        $dbs = $this->pDB_show_databases();
        if ( array_search( $db_name, $dbs) === null){
            $err = "pDB_drop_database() - DataBase('$db_name') could not be found, aborting.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // select given database
        $sel = $this->pDB_use_database( $db_name);
        
        // recursively drop this database (newly introduced method, failsafe)
        $path = $this->getPath();
        $this->deleteDir( $path);
        
        /* OLD AND BUGGY CODE
        // get a list of all tables in database
        $tables = $this->pDB_show_tables();
        // delete all tables in database (if any)
        foreach ( $tables as $table){
        $u_table = $this->pDB_drop_table( $table);
        }
        // delete database itself
        ob_start();
        $u_db = unlink( $path);
        $out  = ob_get_contents();
        ob_end_clean();
        if ( !$u_db){
        $err  = "pDB_drop_database() - There was an error while dropping DataBase('$db_name'), error.\n";
        $err .= "PHP said: '$out'";
        $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
        return false;
        }
        */
        return true;
    }
    
    
    /**
    * @desc Creates a new table in currently selected database.
    * @param string $table_name Name of new table as string.
    * @param array $keys Array containing the keys.
    * @param optional string $type Type of table listed in $this->TBL_TYPE.
    * @return boolean Returns TRUE on success, FALSE on failure.
    */
    function pDB_create_table( $table_name, $keys, $type=null){
        print_r( $type);
        // Look for optional 3rd parameter : Type of table  (FHNDL ? LFIO).
        // If nothing was specified then choose LFIO by default (stable).
        $tbl_type = @func_get_arg(2);
        if ( !$tbl_type) $tbl_type = 'LFIO';
        
        // check if DB has been chosen
        if ( !$this->CORE_DATA['DB_CURRENT']){
            $err = "pDB_create_table() - No DB selected yet, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // check table_type if valid
        if ( !in_array( strtoupper( $tbl_type), $this->TBL_TYPE)){
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), 'pDB_create_table() : TableType( \''.$type.'\') is unknown, aborting creation!');
            return false;
        }
        
        // assuming table has no blobs by default.
        $has_blob = false;
        
        // check user authentication
        if ( !$this->CORE_DATA['AUTH'][0]){
            //user is not logged in correctly
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), "pDB_create_table() - You're not authorized to create a new table !", 0);
            return false;
        }
        
        // check given table_name if it's of valid kind
        if(!$this->is_valid_name($table_name)){
            $err  = "pDB_create_table() - Given tablename('$table_name') is not valid !\n";
            $err .= "NOTE: Tablename can NOT contain following chars : space, points, slashes .";
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), $err, 0);
            return false;
        }
        
        // check if table allready exists
        if(is_file($this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name)){
            $err = "pDB_create_table() - Table('$table_name') allready exists, aborting creation.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // check keys and attributes (type, name, length, optionally passed COLUMN_PROPERTIES)
        $n_keys = count( $keys);
        
        // table MUST contain at least ONE key
        if( $n_keys < 1){
            $err = "pDB_create_table() - Table('$table_name') MUST contain at least one column, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // loop through all keys
        $k                   = 0;
        $num_autoi_cols      = 0;
        $num_prima_cols      = 0;
        $prop_values         = array();
        $prop_values_default = array();
        foreach ($keys as $key){
            // reinitialize some needed buffers for each key
            $length = "";
            $properties     = array();
            $is_null_col    = 0;
            
            // keys can not be EMPTY or too short
            // in pDB the smallest possible definition is: �int A� -> 5 chars.
            if ( strlen( $key) < 5){
                $err = "pDB_create_table() - Key at position ".((int)$k+1)." of $n_keys ('$key') is invalid!";
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
                return false;
            }
            
            // trim trailing whitespace from key
            $key = ltrim( $key);
            $key = rtrim( $key);
            
            /**
            * Check given column-definition: type, name[, length, column-properties(s)]
            * correct examples are:
            * �int ID 8 AUTOINCREMENT�
            * �int ID 10 AUTOINCREMENT PRIMARY�
            * �string MyField 4096�
            * �string HerField 50 UNIQUE�
            * �blob Data�
            * �enum Stuff�
            * �enum MyList 255�
            */
            
            // split current key in tokens
            $all       = explode( " ", $key);
            
            // sort out empty tokens (erronous spaces)
            for ( $t=0; $t<count( $all); $t++){
                $token = $all[$t];
                // replace all spaces and look if there is something left.
                $token = str_replace( " ", "", $token);
                if ( !$token){
                    array_splice( $all, $t, 1);
                }
            }
            
            // first two are mandatory
            $type      = $all[0];
            $name      = $all[1];
            
            /**
            * NOTE: This parameter (custom Field-Length) is optional.
            * When it's not passed, pDB assumes the MAX-possible length for the
            * respective field-type.
            * Refer to PDB_FIELDLENGTH_CONSTANTS for each field-type to get his
            * MAX-possible field-length value.
            */
            
            // We have to check if Field-Length wasn't omitted by directly
            // passing one or more column_properties.
            if ( count( $all) > 2){
                
                // --
                if ( !$this->is_valid_column_property( $all[2]) and is_numeric( $all[2])){
                    $length      = $all[2];
                    $prop_index  = 3;
                }else{
                    // length was not passed, setting default length later.
                    $length      = null;
                    $prop_index  = 2;
                }
                // gather properties (if any)
                if ( count( $all) > $prop_index){
                    $prev_prop    = null;
                    $default_flag = false;
                    for ( $p=$prop_index; $p<count( $all); $p++){
                        $property = $all[$p];
                        $property = ltrim( $property);
                        $property = rtrim( $property);
                        // check if DEFAULT comes last
                        if ( $default_flag and $this->is_valid_column_property( $property)){
                            $err = "pDB_create_table() - Property('DEFAULT') comes always last, error.";
                            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
                            return false;
                        }
                        if ( !$this->is_valid_column_property( $property) and $prev_prop!='DEFAULT'){
                            $err = "pDB_create_table() - Invalid ColumnProperty('$property') was passed for column('$name'), error.";
                            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
                            return false;
                        }
                        // when property default was encountered, no other property is allowed anymore. DEFAULT comes always last.
                        if ( $property == 'DEFAULT') $default_flag = true;
                        array_push( $properties, $property);
                        $prev_prop = $property;
                    }
                }
            }
            
            /**
            * @desc AUTOINCREMENT-hack on columns of type INT.
            * NOTE: AUTOINCREMENT is only allowed on columns of type INT.
            * Passing �AUTOINCREMENT� or ANY function on another type of
            * field yields an error.
            * @date Wed Apr 7 13:28:08 2004
            
            Introducing COLUMN_PROPERTIES( �AUTOINCREMENT�, �PRIMARY�, �UNIQUE�,
            �DEFAULT�, �NULL� and �NOTNULL� into pDB.
            The rules are simple:
            - There can be always only one PRIMARY-column in a table.
            - A PRIMARY-column behaves always like a UNIQUE-column.
            - There can be multiple UNIQUE-columns in one table.
            - There can be always only one AUTOINCREMENT-column in a table.
            - PRIMARY-column can be AUTOINCREMENT-column too.
            - AUTOINCREMENT-columns MUST be of type INT.
            - BLOB-column can never be AUTOINCREMENT or PRIMARY or UNIQUE.
            - BLOB-column, due to his technical nature, is always UNIQUE.
            - COLUMN CAN NOT BE SET �NULL� or �NOTNULL�.
            - A NOTNULL-column MUST have a value.
            */
            
            // check if AUTOINCREMENT is requested on other column type as INT.
            if ( !strtolower( $type) == "int" and !in_array( 'AUTOINCREMENT', $properties)){
                $err = "pDB_create_table() - ColumnProperty('AUTOINCREMENT') is not allowed on other columns than of type �int�, error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                return false;
            }elseif ( strtolower( $type) == "int" and in_array( 'AUTOINCREMENT', $properties) !== false){
                // only one AUTOINCREMENT-column is allowed for each table.
                $num_autoi_cols++;
                if ( $num_autoi_cols > 1){
                    $err = "pDB_create_table() - Only one AUTOINCREMENT-column is allowed for each table, error.";
                    $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                    return false;
                }
            }
            
            // check if PRIMARY is reuqested on other column-type than STRING or INT.
            if ( in_array( 'PRIMARY', $properties) and (strtolower( $type) !== "int" and strtolower( $type) !== "string")){
                $err = "pDB_create_table() - ColumnProperty('PRIMARY') for column('$name') of type('$type') is not allowed on other columns than of type �int� or �string�, error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                return false;
            }elseif ( (strtolower( $type) == "int" or strtolower( $type) == "string") and in_array( 'PRIMARY', $properties) !== false){
                // only one PRIMARY-column is allowed for each table.
                $num_prima_cols++;
                if ( $num_prima_cols > 1){
                    $err = "pDB_create_table() - Only one PRIMARY-column is allowed for each table, error.";
                    $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                    return false;
                }
            }
            
            // check if UNIQUE is requested on other column-type than STRING or INT.
            if ( ( !strtolower( $type) == "int" or !strtolower( $type) == "string") and !in_array( 'UNIQUE', $properties)){
                $err = "pDB_create_table() - ColumnProperty('UNIQUE') is not allowed on other columns than of type INT or STRING, error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                return false;
            }
            // check if unique is requested on a column that is already declared �PRIMARY�.
            if ( in_array( 'UNIQUE', $properties) and in_array( 'PRIMARY', $properties)){
                $err  = "pDB_create_table() - Cannot declare column('$name') as both (�PRIMARY� and �UNIQUE�), error.";
                $err .= "\nNOTE: PRIMARY-columns are always UNIQUE, so you don't have to set it twice.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 0);
                return false;
            }
            // multiple UNIQUE-columns are allowed in one table, so no check is needed.
            
            // check �NULL� and �NOTNULL�
            if ( in_array( 'NULL', $properties)) $is_null_col = 1;
            if ( $is_null_col and in_array( 'NOTNULL', $properties)){
                $err = "pDB_create_table() - Cannot declare column('$name') as both (�NULL� and �NOTNULL�), error.";
                $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                return false;
            }
            
            // check if DEFAULT-value was passed on BLOB-column, PRIMARY or AUTOINCREMENT
            if ( in_array( 'DEFAULT', $properties) and ( strtolower($type=='blob') or in_array( 'PRIMARY', $properties) or in_array( 'AUTOINCREMENT', $properties)) ){
                $err = "pDB_create_table() - Can not declare DEFAULT-value for Column('$name') of type('$type'), error.";
                $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                return false;
            }
            // create entry in $prop_values (for each defined DEFAULT-col)
            if ( in_array( 'DEFAULT', $properties)){
                $prop_values_default['DEFAULT.' . $name] = $properties[count( $properties)-1];
            }
            
            // check token �type� of field
            if ( strtolower( $type) == "blob"){
                // enables the creation of a blob-folder
                $has_blob = true;
            }
            if( !$this->is_valid_type( $type)){
                $err = "pDB_create_table() - Type('$type') at position ".((int)$k+1)." of $n_keys keys is invalid!";
                $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                return false;
            }
            
            // check token �name� of field
            if ( !$this->is_valid_name( $name)){
                $err = "pDB_create_table() - Name('$name') at position ".((int)$k+1)." of $n_keys keys is invalid!";
                $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                return false;
            }
            
            // If token �length� was not provided set default MAX-possible length for this column.
            // NOTE: if maybe someone passes zero as length this code will apply to that column too.
            // BUG:  Due to encoding reasons (base64) Field-Length can not be shorter than 4 (only a guess).
            $max_length = $this->PDB_FIELDLENGTH_CONSTANTS[$type];
            if ( !$length){
                $err = "pDB_create_table() - Length was not passed, using MAX-possible length('".$this->PDB_FIELDLENGTH_CONSTANTS[$type]."') for field('$name').";
                $this->_put_message( "pDB_WARNING", array( __FILE__, __LINE__), $err, 0);
                $length = $max_length;
            }else{
                // check if length is of type int
                if ( @is_nan( $length) or !@is_finite( $length)){
                    $err = "pDB_create_table() - Length('$length') provided for key('$type $name') is not a number(int), error.";
                    $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                    return false;
                }
                // check token �length� since it was provided against PDB_FIELDLENGTH_CONSTANTS.
                # Maybe here we could apply the base64 encoding-overhead-fix
                if ( $length > $max_length){
                    $err = "pDB_create_table() - Length('$length') exceeds MAX-possible-Length('$max_length') for key('$type $name'), error.";
                    $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), $err, 0);
                    return false;
                }
            }
            
            // modify original key to fit into format: �type name length properties�
            $keys[$k] = $type . ' ' . $name . ' ' . $length;
            // add properties to current key (if there are some)
            if ( count( $properties)){
                $keys[$k] .= " " . join( " ", $properties);
            }
            
            // increment $k (key_counter-flag)
            $k++;
        }
        
        // create new PROPERTY-FILE (*)
        $fp_mkprop = fopen($this->getPath()."/".$table_name,"w",false);
        
        // set the appropriate rights on new * (maybe this is unsafe?)
        @chmod( $this->getPath()."/".$table_name, 0777);
        
        // collecting some needed property-values
        // see: $this->TBL_PROP for list of known properties.
        // TBLTYPE
        $prop_values['TBLTYPE'] = $tbl_type;
        // TBLNAME
        $prop_values['TBLNAME'] = $table_name;
        // USRNAME
        $prop_values['USRNAME'] = $this->CORE_DATA['AUTH'][1];
        // LASTLOG
        $prop_values['LASTLOG'] = date("Ymd_H:i:s");
        // LASTCHG
        $prop_values['LASTCHG'] = date("Ymd_H:i:s");
        // LOCKEDF
        $prop_values['LOCKEDF'] = "No";
        // DEFAULT is done above (for each row that applies)
        // AUTOINC is done here
        if ( $num_autoi_cols){
            $prop_values_default['AUTOINC'] = 0;
        }
        
        // completing table_properties with collected values
        $prop_values     = array_merge( $prop_values, $prop_values_default);
        $tbl_prop_header = "";
        $prop_keys       = array_keys( $prop_values);
        for ( $i=0; $i < count( $prop_values); $i++){
            $tbl_prop_header .= $prop_keys[$i] . "=" . $prop_values[$prop_keys[$i]] . "\n";
        }
        // write properties of table $tablename
        fwrite( $fp_mkprop, $tbl_prop_header, strlen( $tbl_prop_header));
        fclose( $fp_mkprop);
                
        
        //  create new KEY-FILE (*.pdk)
        $mkpdk = fopen( $this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".pdk","w",false);
        // write the keys
        $k = 0;
        foreach ( $keys as $key){
            if ( $k < ( $n_keys-1)){
                $key.=";\n";
            }else{
                $key.=";";
            }
            // this fucking str_replace() workaround should be replaced as soon as possible
            fputs( $mkpdk, str_replace(" ;", ";", $key));
            $k++;
        }
        fclose( $mkpdk);
        @chmod( $this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".pdk", 0777);
        
        // create an empty VALUES-FILE (*.pdv)
        $mkpdv = fopen( $this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".pdv","w",false);
        fclose( $mkpdv);
        @chmod( $this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".pdv", 0777);
        
        // create the $table.blob folder in currently used db (only if needed, fix BZaminga 20030808)
        if ( $has_blob){
            mkdir($this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".blob");
            @chmod($this->CONF_DATA['DB_ROOT']."/".$this->CORE_DATA['DB_CURRENT']."/".$table_name.".blob", 0777);
        }
        return true;
    }
    
    /**
    * @desc Delete a given table from currently selected database.
    * WARNING: Dropping a table means DELETE his structure and data.
    * 		   All data in table will be lost!
    * @param string $tablename Name of table to delete as string.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_drop_table( $tablename){
        // check given tablename
        if ( !$this->is_pDB_table( $tablename)){
            $err = "pDB_drop_table() - Tablename('$tablename') could not be found, error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // get path to currently selected db
        $path = $this->getPath();
        // get types of columns before deleting anything
        $keys = $this->pDB_get_keys( $tablename, PDB_ASSOC);
        $names  = array_keys( $keys);
        $types  = array();
        $length = array();
        foreach ( $keys as $key){
            array_push( $types,  $key['type']);
            array_push( $length, $key['length']);
        }
        // delete *-file 		(PROPERTIES)
        $u_prop = unlink( $path .'/'. $tablename);
        // delete *.pdk-file	(KEYS/STRUCTURE)
        $u_keys = unlink( $path .'/'. $tablename . '.pdk');
        // delete *.pdk-file	(VALUES/DATA)
        $u_vals = unlink( $path .'/'. $tablename . '.pdv');
        // delete blob-folder if any
        if ( in_array( 'blob', $types)){
            $this->deleteDir( $path .'/'. $tablename . '.blob');
        }
        // check if correctly deleted all files and blob-folders
        if ( !$u_prop or !$u_keys or $u_vals){
            $err = "pDB_drop_table() - Could not correctly drop table('$tablename'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        return true;
    }
    // ----------------- SMALL HELPER FUNCS -----------------------------------
    
    /**
    * @desc Returns the path to currently selected database or false when DB_CURRENT-Flag has not been set yet.
    * This means you have to select a db to use first ($this->pDB_use_database()).
    * @param void
    * @return string Returns a string containing the actual path to currenly selected database or false when DB_CURRENT-Flag is not set
    * @see pDB_use_database()
    */
    function getPath(){
        // check if DB_CURRENT-Flag is set properly
        if( !$this->CORE_DATA['DB_CURRENT']){
            $err = "getPath() - DB_CURRENT-Flag is not properly set, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        $path = $this->CONF_DATA['DB_ROOT'].$this->CORE_DATA['DB_CURRENT'];
        if( $this->CONF_DATA['VERBOSE']){
            $this->_put_message( "pDB_WARNING", array(__FILE__,__LINE__), "Path to DB_CURRENT is ('".$path."')", 0);
        }
        return $path;
    }
    
    /**
    * @desc Retrieves the type of table with given tablename.
    * @param string $table_name Name of table as string.
    * @return mixed Returns the type of table as string or null when table is not found or unknown.
    */
    function pDB_get_table_type( $table_name){
        // check if given table exists
        if ( !$this->is_pDB_table( $table_name)){
            $err = "pDB_get_table_type() - Table('$table_name') does not exist.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return null;
        }
        // get table-type
        $table_properties = $this->pDB_get_properties( $table_name);
        $table_type       = $table_properties['TBLTYPE'];
        return $table_type;
    }
    
    /**
    * @desc Updates given table's Property-File with given values.
    * NOTE: This method is only for internal usage! Do NOT tinker
    * around with this method or you will completely mess-up your
    * tables.
    * @param string $table_name Name of table as string.
    * @param string $macro Call one of the builtin macros.
    * BUILTIN MACROS are:
    * - �RESET�        Resets all counters and values.
    * - �AUTOINC++�    Increments AUTOINCREMENT_VALUE by 1.
    * - �DEFAULTVAL�   Set Default-value for a column. Requires two optional params: string $column_name, mixed $value.
    * - �AUTOINCVAL�   Set AUTOINC-value for a table.  Requires third param: mixed $value.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_propfile_update( $table_name, $macro){
        // list of known macros
        $macros = array( 'RESET', 'AUTOINC++', 'DEFAULTVAL', 'AUTOINCVAL');
        // check if passed macro is known
        if ( !in_array( $macro, $macros, false)){
            $err = "pDB_propfile_update() - Unknown Macro('".$macro."'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
            return false;
        }
        // check if table exists
        if ( !$this->is_pDB_table( $table_name)){
            $err = "pDB_propfile_update() - Table('$table_name') could not be found, error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // get path to prop-file
        $path = $this->getPath() . "/" . $table_name;
        // check if prop-file exists
        if ( !is_file( $path)){
            $err = "pDB_propfile_update() - Table('$table_name')'s properties-file('$path') is missing, error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // readin current values
        $current_values = file( $path, false);
        
        // prepare buffers
        $new_values     = array();
        $tmp_values     = array();
        // parse old values
        for ( $l=0; $l<count( $current_values); $l++){
            $line = trim( $current_values[$l]);
            $line = explode( "=", $line, 2);
            $tmp_values[$line[0]] = $line[1];
        }
        // copy temp_values to new_values
        $new_values = $tmp_values;
        // execute reuqested macro on new values
        if ( $macro == 'RESET'){
            # GOOD CODE IS NEEDED HERE
            # DON'T KNOW IF THIS FEATURE IS NEEDED AT ALL
        }elseif ( $macro == 'AUTOINC++'){
            $new_values['AUTOINC'] = $tmp_values['AUTOINC'] + 1;
        }elseif ($macro == 'DEFAULTVAL'){
            if ( func_num_args() < 4){
                $err = "pDB_propfile_update() - Too less parameters for Macro('DEFAULTVAL'), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
                return false;
            }
            $column  = func_get_arg(2);
            $value   = func_get_arg(3);
            // check if given column exists in table
            $columns = array_keys( $this->pDB_get_keys( $table_name, PDB_ASSOC));
            if ( !in_array( $column, $columns)){
                $err = "pDB_propfile_update() - Will not set DEFAULTVAL for not existent Column('$column'), error";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
                return false;
            }
            // assign new DEFAULT-value to requested colunm.
            $new_values['DEFAULT.'.$column] = $value;
        }elseif ( $macro == 'AUTOINCVAL'){
            if ( func_num_args() < 3){
                $err = "pDB_propfile_update() - Too less parameters for Macro('AUTOINCVAL'), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
                return false;
            }
            $value  = func_get_arg(2);
            $new_values['AUTOINC'] = $value;
        }
        // write into new prop-file
        $prop_names      = array_keys( $new_values);
        $tbl_prop_string = "";
        $new_values_keys = array_keys( $new_values);
        for ( $n=0; $n<count($new_values); $n++){
            $tbl_prop_string .=  $new_values_keys[$n]."=".$new_values[$new_values_keys[$n]]."\n";
        }
        // create new prop-file (overwrites existing one)
        $fp_prop        = fopen($this->getPath()."/".$table_name,"w",false);
        $bytesw  = fwrite( $fp_prop, $tbl_prop_string, strlen( $tbl_prop_string));
        fclose( $fp_prop);
        // return report as bool
        return true;
    }
    
    /**
    * @desc Set description of database.
    * @param string $desc Description (max 2048 chars) as string.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_database_set_desc( $desc){
        // get path to currently selected database
        $path      = $this->getPath();
        // check if .desc-file is there
        $desc_file = $path . "/.desc";
        // create new description-file (old one is overwritten)
        $fp_desc   = @fopen( $desc_file, 'w', false);
        if ( !$fp_desc){
            $err = "pDB_database_set_desc() - Could not open DescriptorFile('$desc_file'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // write new description
        fwrite( $fp_desc, $desc);
        fclose( $fp_desc);
        return true;
    }
    
    /**
    * @desc Get description from database.
    * @param void
    * @return mixed string Returns description of database as string or false on failure.
    */
    function pDB_database_get_desc(){
        // get path to currently selected database
        $path      = $this->getPath();
        // check if .desc-file is there
        $desc_file = $path . "/.desc";
        if ( !is_file( $desc_file)){
            if ( $this->CONF_DATA['VERBOSE']){
                $err = "pDB_database_get_desc() - Table('') DescriptorFile('$desc_file'), error.";
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
            }
            return false;
        }
        // open pointer to description-file
        $fp_desc   = @fopen( $desc_file, 'r', false);
        if ( !$fp_desc){
            $err = "pDB_database_get_desc() - Could not open DescriptorFile('$desc_file'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // read description-file
        $desc = fread( $fp_desc, filesize( $desc_file));
        fclose( $fp_desc);
        return $desc;
    }
    
    /**
    * @desc Set description of table.
    * @param string $table_name Name of table as string.
    * @param string $desc Description (max 2048 chars) as string.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_table_set_desc( $table_name, $desc){
        // get path to currently selected database
        $path      = $this->getPath();
        // path to $table_name.desc-file
        $desc_file = $path . "/$table_name.desc";
        // create new description-file (old one is overwritten)
        $fp_desc   = @fopen( $desc_file, 'w', false);
        if ( !$fp_desc){
            $err = "pDB_table_set_desc() - Could not open DescriptorFile('$desc_file'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // write new description
        fwrite( $fp_desc, $desc);
        fclose( $fp_desc);
        return true;
    }
    
    /**
    * @desc Get description from table.
    * @param string $table_name Name of database as string.
    * @return bool string Returns description of database as string.
    */
    function pDB_table_get_desc( $table_name){
        // get path to currently selected database
        $path      = $this->getPath();
        // check if $table_name.desc-file is there
        $desc_file = $path . "/$table_name.desc";
        if ( !is_file( $desc_file)){
            if ( $this->CONF_DATA['VERBOSE']){
                $err = "pDB_table_get_desc() - Could not open DescriptorFile('$desc_file'), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            }
            return false;
        }
        // open pointer to description-file
        $fp_desc   = @fopen( $desc_file, 'r', false);
        if ( !$fp_desc){
            $err = "pDB_table_get_desc() - Could not open DescriptorFile('$desc_file'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // read description-file
        $desc = fread( $fp_desc, filesize( $desc_file));
        fclose( $fp_desc);
        return $desc;
    }
    
    /**
    * @desc Removes a folder and ALL of it's contents from filesystem.
    * SECURITY: Maybe we'll introduce some checks that allow $dir to
    * be only under DB_ROOT/ to avoid malicious code from deleting
    * sensible files outside of DB_ROOT/ .
    * @param string $dir Path of directory to delete as string.
    * @return void
    */
    function deleteDir( $dir){
        $current_dir = opendir( $dir);
        while ( $entryname = readdir( $current_dir)){
            if ( is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){
                $this->deleteDir( "${dir}/${entryname}");
            } elseif ( $entryname != "." and $entryname!=".."){
                unlink( "${dir}/${entryname}");
            }
        }
        closedir( $current_dir);
        rmdir( ${dir});
    }
    
    /**
    * @desc Checks if a table with given name exists in currently selected DB.
    * @param string $table_name Tablename.
    * @return bool Returns TRUE when table is valid, FALSE on failure.
    */
    function is_pDB_table($table_name){
        // prepend the path to the currently selected DB
        $table_name = $this->getPath()."/".$table_name;
        
        /* THIS ONE MAKES CORE slower IF ENABLED !!??
        // ACC: check if it's allready stored
        $is_file    = $this->ACC_get( $table_name, 'IS_TABLE');
        if ( $is_file !== null){
            return $is_file;
        }
        */
        // physically check if files exist
        if ( is_file($table_name) && is_file($table_name.".pdk") && is_file($table_name.".pdv")){
            /*
            // ACC: store value $is_table
            $this->ACC_put( $table_name, 'IS_TABLE', $is_file);
            */
            return true;
        }            
        return false;
    }
    
    /**
    * This method checks either a given name is valid for a Database,Table or Column
    * @param string $name
    * @return bool
    */
    function is_valid_name($name){
        // check name's size
        if(strlen($name)<1 or strlen($name)>255){
            return false;
        }
        
        // loop through all chars searching bad ones
        for($c=0;$c<strlen($name);$c++){
            $char = substr($name, $c, 1);
            
            // check name for points
            if($char==".")return false;
            
            // check name for slashes
            if($char=="/")return false;
            
            // check name for spaces
            if($char==" ")return false;
        }
        // everything is OK, return true
        return true;
    }
    
    /**
    * @desc Checks if a given attribute is valid.
    * @param string $attribute An attribute for a column in a pDB_table (int, string, blob).
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function is_valid_type($attribute){
        if ( $attribute=="int" or $attribute=="string" or $attribute=="blob" or $attribute=="enum"){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @desc Checks if given column-property is valid.
    * @param string $column_property Column-Prperty passed as string (eg: �UNIQUE�,�PRIMARY�,...).
    */
    function is_valid_column_property( $column_property){
        if ( !in_array( $column_property, $this->COLUMN_PROPERTIES)){
            return false;
        }else{
            return true;
        }
    }
    
    /**
    * @desc Parses a given conf-file to a pDB_TBL_OBJ
    * @param string $conf_path String to conf-file to parse
    * @return mixed Returns a pDB_TBL_OBJ containing the configuration (key, value, comment), FALSE on failure
    */
    function pDB_conf2tbl($conf_path){
        // check specified config-file
        if(!@is_file($conf_path)){
            if($this->CONF_DATA['DEBUG']){
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__),"pDB_CORE : Conf-file ('$conf_path') could not be found!", 0);
            }
            return false;
        }
        // prepare $return_table
        $return_table = new pDB_TABLE_OBJ();
        $return_table->initTable( basename($conf_path), array("key", "value", "comment"));
        // Parse the conf-file now and populate $return_table
        $fp = fopen($conf_path, "r", false);
        if(!$fp){
            if($this->CONF_DATA['DEBUG']){
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "pDB_CORE : Error opening conf-file ('$conf_path').", 0);
            }
            return false;
        }
        // Parse line by line and evaluate the contents of it
        while($lb=fgets($fp, 1024)){
            if(substr($lb,0, 1)==";"){
                $comment .= $lb;	// line is a comment
            }else {
                // check for valid user
                $pair = explode("=", $lb);
                if(count($pair)>1){
                    // it's a directive !!
                    $key    = $pair[0];
                    $value  = $pair[1];
                    // add row to table and get next line
                    $row = array($key, $value, $comment);
                    $return_table->insertRow($row);
                    $comment = "";
                }else{
                    // it's a comment or something not understood
                    $comment .= $lb;
                }
            }
        }
        return $return_table;
    }
    
    /**
    * @desc Rebuilds a string ready to be written or used as config-file from given conf-table
    * @param A pDB_TBL_OBJ containing 3 columns (key, value, comment)
    * @return string A well formatted conf-file as string
    */
    function pDB_tbl2conf( $conf_table){
        while($row = $conf_tbl->getRow()){
            $back .= $row[2]."\n";
            $back .= $row[0]."=".$row[1]."\n";
        }
        return $back;
    }
    
    // -------------- FROM FHNDL-CLASS  ----------------------------------
    
    /**
    * @desc Loads a pDB_TABLE and returns it as pDB_TABLE_OBJ [usage:public]
    * NOTE : This method does not return correct __ID__'s.
    * Do not use this method when you plan to use the physical __ID__'s.
    * This method is much faster than pDB_select().
    * @param string $table The physical tablename as string.
    * @return resource pDB_TABLE_OBJ containing the data from physical table or false on failure.
    */
    function pDB_load_table($table){
        //get path from core
        $path = $this->getPath()."/".$table;
        
        // read the properties from *-file
        $prop = $this->pDB_get_properties($table);
        
        // get keys from table and reform it to: �type name length�.
        // NOTE: pDB_TABLE_OBJ does not support any COLUMN_PROPERTIES.
        $keys   = array();
        $struct = $this->pDB_get_keys($table, PDB_NUM);
        for ( $k=0; $k<count( $struct); $k++){
            $type   = $struct[$k]['type'];
            $name   = $struct[$k]['name'];
            $length = $struct[$k]['length'];
            $key    = $type . ' ' . $name . ' ' . $length;
            array_push( $keys, $key);
        }
        
        
        // initialize table_object
        $table_obj=new pDB_TABLE_OBJ;
        $table_obj->initTable($table, $keys);
        
        // fill table object with data from *.pdv
        // NOTE: Since different tbl_types need different methods for handling.
        // data from/to .pdv, we have to decide which methods are to call by
        // evaluating the option 'TBLTYPE' in table's properties-file.
        if ( $prop['TBLTYPE']=='FHNDL'){
            // FHNDL
            $values = $this->FHNDL_read_pdv($table, "base64_decode");
        }elseif ( $prop['TBLTYPE']=='LFIO'){
            // LFIO
            $num = $this->LFIO_count_rows( $table);
            for ( $i=0; $i<$num; $i++){
                $values = $this->LFIO_read( $table, $i);
                $table_obj->insertRow( $values);
                $table_obj->__ID__[$i] = $i;
            }
            return $table_obj;
            // LFIO-METHODS end here
        }
        
        // when table is empty, return tbl_obj now
        if ( count( $values) < 1){
            return $table_obj;
        }
        
        // cycle thru rows and fields (ONLY FOR FHNDL-TABLES!)
        $rows_inserted = 0;
        foreach ($values as $row_raw){
            // chop-off control-byte from each value-rows field
            $row_clean = array();
            foreach ($row_raw as $field){
                array_push($row_clean, substr($field, 1));
            }
            // insert the actual __ID__ in FS from current row
            # BUG : incorrect __ID__'s are assigned
            # This is disabled till we have fast and working code.
            $table_obj->__ID__[$rows_inserted] = null;
            
            // insert clean row into table-object
            if( $table_obj->insertRow($row_clean))$rows_inserted++;
        }
        // return the finished table_object
        return $table_obj;
    }
    
    /**
    * @desc Reads the properties-file (*) and returns the properties for given table
    * @param string $table Tablename as string
    * @return array Array containing the known and supported properties
    */
    function pDB_get_properties($table){
        // ACC: ask accelerator
        if ( $this->CONF_DATA['ACCELERATED']){
            $properties = $this->ACC_get( $table, 'PROPERTIES');
            if ( $properties){
                return $properties;
            }
        }
        
        // check if properties-file exists
        if ( !is_file( $this->getPath()."/".$table)){
            if ( $this->CONF_DATA['DEBUG']){
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "The properties-file for this table ('$table') is missing!", 0);
            }
            return false;
        }
        
        // copy known and accepted properties from $this->TBL_PROP
        $known_props = $this->TBL_PROP;
        
        // load table's properties-file
        $raw_props = parse_ini_file( $this->getPath() . "/" . $table);
        
        // if no properties can be found for this table : fire an error (table is corrupt)
        if ( count( $raw_props) < 1){
            if($this->CONF_DATA['DEBUG']){
                $err = "pDB_get_properties() - Table('$table') is corrupt! \n";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            }
            // no properties specified, error!
            return false;
        }
        
        // ACC: store properties
        if ( $this->CONF_DATA['ACCELERATED']){
            $this->ACC_put( $table, 'PROPERTIES', $raw_props);
        }
        
        return $raw_props;
    }
    
    /**
    * @desc Returns the size of a database in bytes.
    * NOTE: You must have selected a database before for this to work.
    * @return int Returns size of database in bytes, or -1 or failure.
    */
    function pDB_database_get_size(){
        // check if there is a database selected
        if ( !$this->CORE_DATA['DB_CURRENT']){
            $err = "pDB_database_get_size() - There is no currently selected database, error.";
            $this->_put_message( "pDB_WARNING", array(__FILE__,__LINE__),$err, 1);
            return false;
        }
        // get path to currently selected database
        $path   = $this->getPath();
        // get list of tables in currently selected database
        $tables = $this->pDB_show_tables();
        // get size of .pdb-file
        $sum    = filesize( $path."/.pdb");
        // get size of all tables in database
        foreach ( $tables as $table){
            $sum += $this->pDB_table_get_size( $table);
        }
        return $sum;
    }
    
    /**
    * @desc Returns the size of a table in bytes.
    * NOTE: You must have selected a database before for this to work.
    * @param string $table_name Name of table as string.
    * @return int Returns size of table in bytes, or -1 or failure.
    */
    function pDB_table_get_size( $table_name){
        // check if there is a database selected
        if ( !$this->CORE_DATA['DB_CURRENT']){
            $err = "pDB_table_get_size() - There is no currently selected database, error.";
            $this->_put_message( "pDB_WARNING", array(__FILE__,__LINE__),$err, 1);
            return false;
        }
        // check if table exists
        if ( !$this->is_pDB_table( $table_name)){
            $err = "pDB_table_get_size() - Table('$table_name') could not be found, error.";
            $this->_put_message( "pDB_WARNING", array(__FILE__,__LINE__),$err, 1);
            return false;
        }
        // get path to table
        $path = $this->getPath();
        // sum sizes of all table-files
        $sum_prop = filesize( $path."/".$table_name);
        $sum_pdv  = filesize( $path."/".$table_name.".pdk");
        $sum_pdk  = filesize( $path."/".$table_name.".pdv");
        $sum      = $sum_prop + $sum_pdk + $sum_pdv;
        return $sum;
    }
    
    /**
    * @desc Returns keys(structure) of given table as array. (FHNDL & LFIO)
    * Pass second optional param to get different behaviour:
    * - as numeric-array     : pass �pDB_NUM� (default)
    * - as associative array : pass �PDB_ASSOC�
    * NOTE: �PDB_CB� is deprecated and can NOT be used anymore.
    * @param string table Name of table passed as string.
    * @param string optional Optional second param can be : PDB_NUM, PDB_ASSOC
    * @return mixed Returns array containing keys in requested style, or FALSE on failure.
    */
    function pDB_get_keys( $table){
        // look for optional second param
        $output_type = @func_get_arg(1);
        if ( !isset( $output_type)){
            $output_type = "pDB_NUM";
        }
        
        if ( $output_type!="pDB_NUM" and $output_type!="pDB_ASSOC"){
            if ( $this->CONF_DATA['VERBOSE']){
                $err = "pDB_get_keys() - Mode('$output_type') not recognized, try �PDB_NUM� or �PDB_ASSOC� instead, error.";
                $this->_put_message( "pDB_WARNING", array(__FILE__,__LINE__),$err, 0);
                return false;
            }
            // default mode is pDB_NUM
            $output_type="pDB_NUM";
        }
        
        // Accelerator
        if ( $this->CONF_DATA['ACCELERATED']){
            // query the accelerator if it has the value cached.
            $keys = $this->ACC_get( $table, 'KEYS');
            if ( $keys !== null){
                return $keys;
            }
        }
        
        
        // check if table exists
        if ( !$this->is_pDB_table( $table)){
            $err = "pDB_get_keys() - Table('$table') does not exists, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // read in the keys-file linewise(.pdk)
        $pdk_file = $this->getPath()."/".$table.".pdk";
        if ( !is_file( $pdk_file)){
            $err = "pDB_get_keys() - KeyFile('$pdk_file') was not found, check your tables integrity!\n";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        $keys_raw  = file( $pdk_file, false);
        $keys_pure = array();
        
        foreach ($keys_raw as $key){
            // create an empty buffer for properties
            $properties  = array();
            
            // cleaning up the key from trailing garbage
            $key = eregi_replace(chr(10),"",$key);	// strip linebreaks
            $key = eregi_replace("\r","",$key);		// strip carriage returns
            $key = eregi_replace(";","",$key);		// strip semicolon
            
            // split up key into tokens
            $key_all     = explode( " ",$key);
            $type        = $key_all[0];
            $name        = $key_all[1];
            $length      = $key_all[2];
            // gather all properties into array -> $properties
            for ( $p=3; $p>count( $key_all); $p++){
                array_push( $properties, $key_all[$p]);
            }
            
            if ( $this->CONF_DATA['VERBOSE']){
                $err = "pDB_get_keys() - Processing Key('$key')".chr(10);
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err, 1);
            }
            
            /*
            Sat Apr 10 15:17:53 2004 BennyZam
            Structure of returned data is always:
            array(
            [0] -> array(
            [type]          -> string,
            [name]          -> TestTab,
            [length]        -> 25,
            [PRIMARY]       -> 0,
            [AUTOINCREMENT] -> 0,
            [UNIQUE]        -> 1,
            [NOTNULL]       -> 0,
            [DEFAULT]       ->
            )
            )
            */
            
            $PRIMARY       = (int)in_array( 'PRIMARY',       $key_all, false);
            $AUTOINCREMENT = (int)in_array( 'AUTOINCREMENT', $key_all, false);
            $UNIQUE        = (int)in_array( 'UNIQUE',        $key_all, false);
            $NOTNULL       = (int)in_array( 'NOTNULL',       $key_all, false);
            $tbl_props     = $this->pDB_get_properties( $table);
            $DEFAULT       = $tbl_props['DEFAULT.'.$name];
            $ALL = array(
            "type"          => $type,
            "name"          => $name,
            "length"        => $length,
            "PRIMARY"       => $PRIMARY,
            "AUTOINCREMENT" => $AUTOINCREMENT,
            "UNIQUE"        => $UNIQUE,
            "NOTNULL"       => $NOTNULL,
            "DEFAULT"       => $DEFAULT
            );
            
            // create output in buffer according to $output_type
            if ( $output_type=="pDB_NUM"){
                array_push( $keys_pure, $ALL);
            }elseif ( $output_type=="pDB_ASSOC"){
                $keys_pure[$name] = $ALL;
            }
        }
        
        // ACC: store �KEYS�
        $this->ACC_put( $table, 'KEYS', $keys_pure); 
        
        return $keys_pure;
    }
    
    
    // GENERIC API METHODS (these work transparently on all table-types) ----------------------
    // pDB_add_row()
    // pDB_update_row()
    // pDB_drop_row()
    // pDB_select()
    // pDB_count_rows()
    
    /**
    * @desc   Adds a new row to given table. This method supports both, FHNDL & LFIO tables!
    * NOTE:   Newly added are some functionalities like: AUTOINCREMENT, UNIQUE, PRIMARY, NOTNULL and DEFAULT.
    * @param  string $table A tablename as string should be passed (do not prefix the db).
    * @param  array  $values An array containing the values to insert (numeric).
    * @return bool   Returns TRUE on success and FALSE on failure.
    */
    function pDB_add_row( $table, $values){
        // get path from core
        $path = $this->getPath()."/".$table;
        
        // check if table exists
        if ( !$this->is_pDB_table( $table)){
            $err  = "pDB_add_row() - Table('$table') does not exist, aborting.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // check table-type before doing anything (Sun Feb 29 15:28:42 2004)
        $tbl_type = $this->pDB_get_table_type( $table);
        if ( !$tbl_type){
            $err  = "pDB_add_row() - Table('$table') is of unknown type('$tbl_type'), aborting.\n";
            $err .= "ATTENTION: Due to last errors, it could be the case that your database has been corrupted.\n";
            $err .= "Please consult the pDB-manual to get some help or simply try to run some rescue-scripts.\n\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
            return false;
        }
        
        /*
        UPDATE: Tue Mar 30 02:37:05 2004
        Adding ability to pass a custom set of columns where to insert values to.
        We have to check here if it's an associative array and if all given names
        match the tables keys.
        I'm not sure if i'm really gonna introduce this here?
        This idea remains documented but still not in use.
        */
        
        // check values against COLUMN_PROPERTIES for each column.
        $keys   = $this->pDB_get_keys( $table, PDB_ASSOC);
        $names  = array_keys( $keys);
        $k      = 0;
        foreach ( $keys as $key){
            $value  = $values[$k];
            $type   = $key['type'];
            $name   = $key['name'];
            $length = $key['length'];
            
            // AUTOINCREMENT (applies only when NO value was provided)
            if ( $key['AUTOINCREMENT'] and $value=='' ){
                // get stored AUTOINCREMENT-VALUE for this column
                $props_all = $this->pDB_get_properties( $table);
                $value     = $props_all['AUTOINC'];
            }
            // UNIQUE (applies on UNIQUE and PRIMARY columns)
            if ( $key['UNIQUE'] or $key['PRIMARY']){
                // check if given value would be unique in table
                $res = $this->pDB_select( $table, $k, $value);
                $num = $res->countRows();
                if ( $num){
                    $err = "pDB_add_row() - Value('$value') for Column('$name') is not unique, cannot insert.";
                    $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
                    return false;
                }
            }
            // NOTNULL (checks if value can be empty or not)
            if ( $key['NOTNULL'] and !$value){
                $err = "pDB_add_row() - Value('$value') for Column('$name') can not be NULL, cannot insert.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
                return false;
            }
            // DEFAULT (applies when no value was provided and column has default-value)
            if ( $key['DEFAULT'] and !$value){
                // get stored DEFAULT-value for this column
                # THIS FEATURE HAS TO BE CODED YET
            }
            // assign maybe modified value back to $values
            $values[$k] = $value;
            // increase key-counter
            $k++;
        }
        
        
        // handle tables of type LFIO (Sun Feb 29 15:33:18 2004)
        if ( $tbl_type == strtoupper( 'LFIO')){
            if ( $this->LFIO_add( $table, $values)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                // AUTOINC++ (update AUTOINCREMENT value for given table)
                $res_autoi = $this->pDB_propfile_update( $table, 'AUTOINC++');
                return true;
            }else{
                return false;
            }
        }
        
        // handle tables of type FHNDL (Sun Feb 29 16:14:29 2004)
        if ( strtoupper( $tbl_type) == 'FHNDL'){
            if ( $this->FHNDL_add_row( $table, $values)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                // AUTOINC++ (update AUTOINCREMENT value for given table)
                $res_autoi = $this->pDB_propfile_update( $table, 'AUTOINC++');
                return true;
            }else{
                $err = "pDB_add_row() - Could not insert row into table('$table'), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
                return false;
            }
        }
        
        if ( !$res_autoi){
            $err = "pDB_add_row() - Unable to increment the AUTOINCval, error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
            return false;
        }
        
        // if table could not be handled, emitt error
        $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), "pDB_add_row() - Table('$table) could not be handled, unknown type('$tbl_type')!");
        return false;
    }
    
    /**
    * @desc This method is an alias of pDB_add_row().
    * @see pDB_add_row() for further information.
    */
    function pDB_insert_row( $table, $values){
        return $this->pDB_add_row( $table, $values);
    }
    
    /**
    * @desc Updates a row at given index in given table with given values. (FHNDL & LFIO)
    * @param string $table Tablename as string
    * @param int $row_index Index in current table (physically) pointing to a row
    * @param array $values Array containing the current values to update row with.
    * @return bool TRUE on success, FALSE on failure
    */
    function pDB_update_row( $table, $row_index, $values){
        
        // check table-type before doing anything (Sun Feb 29 15:28:42 2004)
        $tbl_type = $this->pDB_get_table_type( $table);
        if ( !$tbl_type){
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), "pDB_add_row() - Table('$table') is of unknown type, aborting.
			ATTENTION: Due to last errors, it could be the case that your database has been corrupted.
			           Please consult the pDB-manual to get some help or simply try to run some rescue-scripts.\n\n");
            return false;
        }
        
        // LFIO
        if ( strtoupper( $tbl_type) == 'LFIO'){
            if ( $this->LFIO_update( $table, $row_index, $values)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                return true;
            }else{
                return false;
            }
        }
        
        // FHNDL
        if ( strtoupper( $tbl_type) == 'FHNDL'){
            if ( $this->FHNDL_update_row( $table, $row_index, $values)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                return true;
            }else{
                return false;
            }
        }
    }
    
    /**
    * @desc Deletes a row at given index in given table. (FHNDL & LFIO)
    * @param string $table Tablename as string
    * @param int $row_index Index in current table (physically) pointing to a row OR
    * a condition passed as array. For each cell of the statement we take one element
    * in array.
    * @return bool TRUE on success, FALSE on failure
    * FIX: Mon Mar 29 09:43:58 2004 Introducing the possibility to pass a condition.
    * A valid condition would be: �WHERE column1 > 5�
    */
    function pDB_drop_row($table,$row_index){
        
        // check if table exists
        if ( !$this->is_pDB_table( $table)){
            $err = "pDB_drop_row() - Table('$table') does not exist.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // get table-type
        $tbl_type = $this->pDB_get_table_type( $table);
        
        // LFIO
        if ( strtoupper( $tbl_type) == 'LFIO'){
            if ( $this->LFIO_delete( $table, $row_index)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                return true;
            }else{
                return false;
            }
        }
        
        // FHNDL
        if ( strtoupper( $tbl_type) == 'FHNDL'){
            if ( $this->FHNDL_drop_row( $table, $row_index)){
                /* AFFECTED_ROWS (Wed Apr 7 00:03:17 2004) */
                $this->NUM_AFFECTED_ROWS++;
                return true;
            }else{
                return false;
            }
        }
    }
    
    /**
    * @desc This method looks in a given table for given value in given column.
    * This method does NOT YET support a specific set of columns to be returned !!
    * @param string $tablename Name of table as string.
    * @param mixed $column Column to look in. Can be string or int.
    * When passed as string, something like : 'NAME' would be appropriate.
    * @param mixed $value Value as string or integer to look for.
    * @return object Returns a pDB_TABLE_OBJ filled with rows that match the given value.
    * FIXED 	Sat Dec 27 22:34:42 2003
    * RE-FIXED	Tue Mar 02 03:40:07 2004
    */
    function pDB_select( $tablename, $column, $value){
        
        // check if table exists
        if ( !$this->is_pDB_table( $tablename)){
            $err = "pDB_select() - Given Tablename('$tablename') could not be found in this DB('".$this->CORE_DATA['DB_CURRENT']."'), aborting search.";
            $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__), $err);
            return false;
        }
        
        // get table-type
        $tbl_type = $this->pDB_get_table_type( $tablename);
        
        // check if column exists
        $keys      = $this->pDB_get_keys( $tablename, PDB_NUM);
        $num_keys  = count( $keys);
        // --if column was passed as INT, check if it's in bounds
        if ( is_int( $column)){
            // check if given index is in bounds
            if ( $column > ( $num_keys - 1) or $column < 0){
                $err = "pDB_select() - Given ColumnIndex('$column') is out of range(0..." . ($num_keys-1) . "), aborting search.";
                $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__), $err, 0);
                return false;
            }
        }
        
        // translate (int)column to (string)column before searching
        if( is_string( $column)){
            // no conversion is needed, do nothing
        }elseif( is_int( $column)){
            $column = $keys[$column]['name'];
        }
        
        // split type from keyname in each key
        $keys_temp = array();
        $col_names = array();
        for( $k=0;$k<$num_keys; $k++){
            array_push( $col_names, $keys[$k]['name']);
            $key = $keys[$k]['type']." ".$keys[$k]['name']." ".$keys[$k]['length'];
            array_push( $keys_temp, $key);
        }
        
        // search for column in keys
        if( array_search( $column, $col_names) === false){
            $err = "pDB_select() Given ColumnName('$column') could not be found in current Table('$tablename'), aborting search.";
            $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__), $err, 0);
            return false;
        }
        
        // create result_table ($res) using keys as table in FS for rows.
        $res = new pDB_TABLE_OBJ();
        $res->initTable( $tablename, $keys_temp);
        
        // search row-by-row for matches
        $continue    = true;
        $real_row    = 0;
        $num_matches = 0;
        $match       = false;
        while( $continue){
            
            // get next row (FHNDL & LFIO)
            $row = $this->pDB_get_row( $tablename, $real_row);
            if ( !$row){
                $continue = false;
                break;
            }
            
            // get the ColumnIndex (Name to Index)
            for ( $k = 0; $k < $res->_NUM_KEYS; $k++){
                if ( $res->_KEYS[$k] == $column){
                    $column_index = $k;
                }
            }
            
            // look for a match in given column current field
            $field = $row[$column_index];
            
            /*
            use the appropriate match-sytle (default is EXACT)
            [optional third param passed to this method]
            */
            
            ## FEEL FREE TO ADD MORE MATCH-STYLES HERE !
            
            // EXACT-MATCH
            if ( strcmp( $value, (string)$field) === 0){
            	$match = true;
            }
            
            // '*' SELECT * (ALL) or MATCH-ALL
            if ( $value == '*') $match = true;
            
            # More match-styles are gonna appear soon.
            # Things like fulltext-search, PCRE and the '*' (SELECT *) are gonna be implemented soon.
            
            ## -- END OF MATCH-STYLES
            
            // add this row to res if it contains a match
            if ( $match){
                // insert row
                if ( !is_array( $row)){
                    $err = "pDB_select() - Given Row is not of type array.";
                    $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
                }else{
                	$res->insertRow( $row);
                	// put the __ID__ in $res
                	# to avoid a serious bug with __ID__ here, we MUST set the right __ID__
                	# for the previsouly inserted row. If not, the __ID__ stack will have a 
                	# first entry that is always zero.
                	$res->__ID__[($res->_NUM_ROWS-1)] = $real_row;    
                }
                
                # BUGGY: Freitag, 13.August 2004 (Benny Zaminga)
                # __ID__'s seem to be inserted twice for each row
                # (Corrected above errors and removed an unecessary code passage here)
                
                $num_matches++;
                $match = false;
            }
            $real_row++;
        }
        // return the result_table (it can occur that an empty table is returned if nothing matches, this is correct)
        return $res;
    }
    
    /**
    * @desc Returns a selection of physical row-ID's pointing into table, that meet the given condition.
    * @param string $tablename Name of table as string.
    * @param array  $condition A condition passed as array. Each element is a logical unit.
    * @return array Returns set of rowID's matching the condition (sorted backwards).
    * NOTE: Biggest rowID comes always first. Do never resort this array before accessing
    * rows using that physical rowID's specified therein.
    */
    function pDB_get_selection( $tablename, $condition){
        // check if table exists
        if ( !$this->is_pDB_table( $tablename)){
            $err = "pDB_get_selection() - Table('$tablename') could not be found, error.";
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // create buffer for IDs (return-value)
        $IDs      = array();
        // look which columns are mentioned in condition. Later we have to get
        // some values from there (each row) to relpace the column-name.
        $affected_columns = array();
        $keys     = array_keys( $this->pDB_get_keys( $tablename, PDB_ASSOC));
        $num_cond = count( $condition);
        for ( $c=0; $c<$num_cond; $c++){
            if ( in_array( $condition[$c], $keys)){
                $affected_columns[$c] = $condition[$c];
            }
        }
        // loop over all rows in table to see which of them match.
        // we rebuild the condition for each row using the values from it.
        $num_rows = $this->pDB_count_rows( $tablename);
        for ( $i=0; $i<$num_rows; $i++){
            // by default every row does NOT match.
            $match = false;
            // get values of current row
            $row = $this->pDB_get_row( $tablename, $i, PDB_ASSOC);
            // make current_condition with value from given column current row
            $current_cond    = $condition;
            $cond_indexes    = array_keys( $affected_columns);
            foreach ( $cond_indexes as $cond_index){
                if ( $row[$current_cond[$cond_index]]){
                    $current_cond[$cond_index] = $row[$current_cond[$cond_index]];
                }
            }
            $match = $this->pDB_eval( $current_cond );
            // add matching row's ID to $IDs
            if ( $match){
            	
                array_push( $IDs, $i);
            }
            
            // sort array backwards, biggest ID's go first, EVER!!
            natsort( $IDs);
            $IDs = array_reverse( $IDs, true);
        }
        return $IDs;
    }
    
    /**
    * @desc Retrieves the number of affected (changed) rows.
    * NOTE: Only call like INSERT, UPDATE or DELETE infulence this value.
    * @param void
    * @return int Returns the number of affected (changed) rows.
    */
    function pDB_affected_rows(){
        return $this->NUM_AFFECTED_ROWS;
    }
    
    /**
    * @desc Count rows in given table. (FHNDL & LFIO)
    * @param string $tablename Name of table to count rows in.
    * @return int Number of found rows in given table.
    */
    function pDB_count_rows($tablename){
        
        // check if table exists
        if ( !$this->is_pDB_table( $tablename)){
            $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__), "Given Tablename('$tablename') could not be found in this DB('".$this->CORE_DATA['DB_CURRENT']."'), aborting count of rows.");
            return false;
        }
        
        // get type of table
        $tbl_type = $this->pDB_get_table_type( $tablename);
        
        // call the apropriate method to handle the given table-type
        if ( $tbl_type == 'LFIO') return $this->LFIO_count_rows( $tablename);		// LFIO
        elseif ( $tbl_type == 'FHNDL') return $this->FHNDL_count_rows( $tablename); // FHNDL
    }
    
    
    /**
    * @desc Tries to evaluate a given expression and return a meaningful value.
    * @param array $exp Expression passed to be evaluated as array with on ore more elements.
    * Passed expression-array could look like: �array( '3', '+', '5')  => 8�.
    * @return mixed Returns the result from evaluation of current expression or NULL on failure.
    * NOTE: Same method is given in pDB_SQL_PARSER.
    */
    function pDB_eval( $exp){
        // evaluate given expression.
        // Must have form: �value  operator  value�* (expression can be longer)
        eval("\$res = (".implode( " ", $exp).");");
        return $res;
    }
    
    /**
    * @desc Executes a given SQL-query.
    * NOTE: MUST have class.pDB_SQL_PARSER.php available for this to work.
    * @param string $sql SQL-Query passed as string.
    * @return mixed Returns the result of performed operation or -1 on failure.
    */
    function pDB_query( $sql){
        // check if pDB_SQL_PARSER is available.
        if ( !in_array( 'pdb_sql_parser', get_declared_classes())){
            print_r( get_declared_classes());
            $err = "pDB_query() - pDB_SQL_PARSER-class is missing, cannot understand SQL, aborting.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return -1;
        }
        // create instance of SQL-parser
        $Parser = new pDB_SQL_PARSER();
        // parse SQL to pDB-API-code
        $code   = $Parser->parse( $sql);
        print_r( $code);
        // spool errors and warnings from parser to pDB's msg-spool
        while ( $perr = $Parser->err_get()){
            $this->_put_message( 'pDB_ERROR', array( __FILE__, __LINE__), $perr, 0);
        }
        // execute code generated from parser
        $res    = $this->pDB_exec( $code);
        // return result
        return $res;
    }
    
    // GENERIC API END -------------------------------------------
    
    
    // HELPER FUNCTIONS ------------------------------------------
    // NOTE: These methods should handle every table-type transparently!
    
    /**
    * @desc Executes given code in this local environment.
    * @param string $code Code to execute herein.
    * @return mixed Returns the result from excuted code.
    */
    function pDB_exec( $code){
        // buffer all output from now on
        ob_start();
        // lint code before execution to see if it's syntax is OK
        # GOOD CODE IS NEEDED HERE
        # Remember that this should be done by another
        # PHP-process than this one we're currently in.
        // return result from executed code
        $res = eval( " $code ");
        $out = ob_get_contents();
        ob_end_clean();
        if ( $this->CONF_DATA['VERBOSE']){
            $err = "pDB_exec() - Code('$code') returned following output: \n'" . $out . "'\n";
            $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err, 0);
        }
        return $res;
    }
    
    /**
    * @desc Empties a table by creating a new empty .pdv-file. (FHNDL & LFIO)
    * WARNING : This method drops every data contained in given table, while the structure is preserved.
    * @param string $table Tablename as string.
    * @return bool
    */
    function pDB_empty_table($table){
        // get path from core
        $path = $this->getPath()."/".$table.".pdv";
        
        // this should be the fastest way to wipe (empty) a table
        $fp_new = fopen($path, "w", false);
        fclose($fp_new);
        
        // drop all blob-files in $table.blob/*
        $remove_path = $this->getPath()."/$table.blob";
        system("rm $remove_path -Rf");		/* not a very smooth way to do that */
        system("mkdir $remove_path");		/* but it works */
        @chmod($remove_path, 0777);
        
        return true;
    }
    
    /**
    * @desc Exports a table as pDB_Package and returns the path to tmp-file.
    * @param string $table Name as string of table to export.
    * @return mixed On success a string containing the path to exported table, false on failure.
    */
    function pDB_export_table($table){
        # SOME NICE CODE IS NEEDED HERE
        # Please have a look at libs/zip.lib.php before you begin to hack on something.
        # We have to think about this when the structure of the whole pDB is well defined and mature.
        # Frequent changes could mess up the package's structure. So stay calm, this comes last.
    }
    
    // SERIALIZE FUNCS ---------------------------------------------------------------------------------
    
    /**
    * @desc Serializes a given pDB_TABLE_OBJECT to a file named $table.ser in /tmp (java-like, experimental).
    * @param pDB_TABLE_OBJ $table The pDB_TABLE_OBJECT to serialize.
    * @return mixed
    */
    function pDB_serialize_table($table){
        // serialize the table
        $ser_table = serialize($table);
        // write the serialized table
        $fp_ser = fopen("tmp/".$table->TABLENAME.".ser","w");
        if($fp_ser){
            $bytes  = fwrite($fp_ser, $ser_table);
            fclose($fp_ser);
            return $bytes;
        }else{
            $err = "pDB_serialize_table() - Could not write serialized table to file. returning it to php://stdout.";
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return $ser_table;
        }
    }
    
    /**
    * @desc Unserializes a previously serialized given pDB_TABLE_OBJECT from a file (java-like, experimental).
    * @param string $ser_file The file that contains the serialized pDB_TABLE_OBJ to unserialize.
    * @return mixed Returns the unserialized table_object contained in given file.
    */
    function pDB_unserialize_table($ser_file){
        // rebuild file path
        $tablename = "tmp/".$ser_file.".ser";
        // file exists ?
        if(!is_file($ser_file)){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "$ser_file could not be found!", 0);
        }
        // read the serialized file
        $fp_ser = fopen($ser_file, "r", false);
        if($fp_ser){
            $ser_table = fread($fp_ser, filesize($ser_file));
            fclose($fp_ser);
        }
        // unserialize serialized table
        $table = unserialize($ser_table);
        return $table;
    }
    
    
    // FROM AUTH-CLASS -----------------------------------------------------------------------------
    
    /**
    * @desc Login a specified user using a given password.
    * Sets $this->AUTH[0] ... [3]
    * If the third and optional parameter ($db) is defined and
    * user and password match the values in $db/.pdb
    * this method sets the DB_CURRENT-Flag to given db.
    * NOTE : This method has to be called to set the AUTH-flag (see docs/ for details)
    * @param string $user Name of User
    * @param string $pass Password to be used
    * @param [string $db] OPTIONAL! Name of database
    * @return int Returns 0 on failure, 1 for normal user, 2+ for privileged users (root).
    */
    function pDB_login($user, $pass){
        /*
        conf/users.conf is gonna be introduced :
        - it allows new-users (users with less than 1 db) to login and create their first db
        - it allows multiple users to one db (they can be assigned in conf/users.conf)
        */
        
        // by default user is not logged in
        $log = 0;
        #$users_allowed = parse_ini_file($this->CONF_DATA['BASE_DIR']."conf/pDB.users.php");
        // moved to pDC_init() -GL 04.05.14
        #$users_allowed = parse_ini_file($this->CONF_DATA['BASE_DIR'] . $this->CONF_DATA['CONF_DIR'] . 'pDB.users.php');
//        if( $users_allowed[$user]){
//            $temp = explode(":", $users_allowed[$user]);
        if( $this->USER_DATA[$user]){
           	$temp = explode(":", $this->USER_DATA[$user]);
            // Old wrong-login-counter
            //$this->CORE_DATA['AUTH'][3]<$this->CONF_DATA['MAX_LOGINS']){
            
            // check if user and password are correct
            if($temp[0] == $pass){
                
                $this->CORE_DATA['AUTH'] = array(1, $user, $pass, 0);
                $known_dbs = @explode(",", $temp[1]);
                $log = 1;
                
                // write $dbs_allowed to core->CORE_DATA['DBS_ALLOWED']
                $this->CORE_DATA['DBS_ALLOWED'] = $known_dbs;
                
                // check for optional third param : $db
                if(($num_args=func_num_args())>2)$db = func_get_arg(2);
                
                // only executed when $db was specified
                // user/pass-pair must match values in .pdb now
                if(isset($db)){
                    // form the current path to given db
                    $db_path = $this->CONF_DATA['DB_ROOT']."/".$db;
                    
                    // get values from DB_ROOT/.pdb file (predifined access rights written to file)
                    $dotpdb = parse_ini_file($db_path."/.pdb",true);
                    $temp_users = array_flip(explode(",", $dotpdb['users']));
                    if(isset($temp_users[$user]))$log = 2;
                    if($user == $dotpdb['owner'] || $user == "root")$log = 3;
                    
                    // check if values in .pdb correspond and number of wrong logins is not higher than allowed
                    if($log > 1 && $this->CORE_DATA['AUTH'][3]<$this->CONF_DATA['MAX_LOGINS']){
                        // values do match, reset login-counter to 0
                        // $this->CORE_DATA['AUTH'] = array(0, null, null, 0);
                        // Set $log to 1, cause one corresponding pair was found.
                        // User is authenticated now!
                        if ( $this->CONF_DATA['VERBOSE']){
                            $err = "pDB_login() - Successfully authenticated as user('".$this->CORE_DATA['AUTH'][1]."').";
                            $this->_put_message("pDB_WARNING", array(__FILE__,__LINE__), $err, 1);
                        }
                        // last but not least : set the DB_CURRENT-Flag
                        $this->pDB_use_database($db);
                    }
                    $this->CORE_DATA['AUTH'][0] = $log;
                }
                if($user == "root")$log = 5;
                // increase the counter of wrong logins needed to limit number of logins to a certain number
                // this prevents brute force-attacks and increases security
                // BAN-Machanism is not defined yet
            }else{
                $this->CORE_DATA['AUTH'][3]++;
            }
        }
        
        
        // ->	--	// no db was specified
        // look in conf/users.conf for given corresponding user&pass-pair
        
        return $log;
    }
    
    /**
    * @desc Writes DB's DescriptorFile with encrypted password in it.
    * @param string $db Path to database as string.
    * @return boolean Returns TRUE on success, FALSE on failure.
    */
    function pDB_create_dotpdb($db){
        // check if user is logged in
        $user_data=$this->CORE_DATA['AUTH'];
        if(!$user_data[0])return false;
        
        // get db_path
        $db_dir=$this->CONF_DATA['DB_ROOT'];
        
        // try to generate a new .pdb-file
        if ( !$hdl=fopen("$db_dir/$db/.pdb", "w+")){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "could not write '.pdb' file.", 0);
            return false;
        }else if(fwrite($hdl, "owner=".$user_data[1]."\nusers=".md5($user_data[2])."\n")){
            fclose($hdl);
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @desc Checks if the .pdb-file is valid and present (NO AUTHORISATION) [usage:private]
    * @param string $db_name Name of database as string.
    * @return boolean Returns TRUE on success, FALSE on failure.
    */
    function pDB_check_dotpdb( $db_name){
        if ( !$pdb = @parse_ini_file( $this->CONF_DATA['DB_ROOT']."/".$db_name."/.pdb")) return false;
        if ( $pdb['owner'] && $pdb['users']) return true;
        return false;
    }
    
    
    // LFIO (LightFileInputOutput) -----------------------------------------------
    // LFIO METHODS --------------------------------------------------------------
    
    /**
    * @desc Add a row filled with given values to given table.
    * @param string $tablename Name of table as string.
    * @param array $values Numeric array containing values.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function LFIO_add( $tablename, $values){
        // get the last index in table +1 (append a row)
        $index = $this->LFIO_count_rows( $tablename);
        // write row
        if ( $this->LFIO_write( $tablename, $index, $values)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @desc Update row at given index with given values.
    * @param string $tablename Name of LFIO-table as string.
    * @param int $index Index of row to update in LFIO-table.
    * @param array $values Numeric array containing values.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function LFIO_update( $tablename, $index, $values){
        // look if index is in bounds
        $num_rows = $this->LFIO_count_rows( $tablename);
        if ( $index >= $num_rows){
            $err = "LFIO_update() - Index('$index') is out of bounds(0,$num_rows), error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // write row
        if ( $this->LFIO_write( $tablename, $index, $values)){
            return true;
        }else{
            return false;
        }
    }
    
    /**
    * @desc Deletes a row at given index in given LFIO-table.
    * NOTE: This method is VERY expensive. It's the most expensive of all methods
    * in LFIO! I can only say: �That's the price we pay for simplicity !�
    * The principle is very simple. To delete a row (since LFIO does NOT
    * use any zombies or control-bytes to mark rows as deleted and then clean them
    * away in one cleaning-pass like FHNDL does), we have to physically delete
    * the data from filesystem.
    *
    * Proper method to do this would be to write into a temporary file,
    * then copy that file over the original in a single operation.
    * If the execution stops (worst case), only the temp file will be corrupt.
    *
    * To archieve this goal, we have to:
    * - Check if table exists.
    * - Look if passed index is valid.
    * - Set Transcription-Flag. Describe pending action in flag-file.
    * - If there is a failure while transcripting, flag remains set.
    * - LFIO will discover the crash and restart failed transcription.
    * - After successful transcription, overwrite old .pdv by copying
    *   temp-file over it in single operation (is safe, needs no lock).
    * - Remove Transcription-Flag.
    *
    * There's a big problem regarding this method an BLOB's. We MUST read
    * BLOB's unique_name before we can delete the row containing it's name.
    *
    * @param string $tablename Name of table as string.
    * @param int $index Index pointing to physical row that should be deleted.
    * @return bool Returns True on success, FALSE on failure.
    */
    function LFIO_delete( $tablename, $index){
        // check if table exists
        if ( !$this->is_pDB_table( $tablename)){
            $err = "LFIO::delete - Table('$tablename') could not be found, error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // look if index is in bounds
        $num_rows = $this->LFIO_count_rows( $tablename);
        if ( $index >= $num_rows){
            $err = "LFIO::delete( '$tablename', $index) - Index-Error, error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // get path to table from CORE
        $path = $this->getPath() . "/" . $tablename;
        
        // check if table has blob-field(s) and drop them too
        // THIS METHOD IS CHECKED AND WORKS BennyZam Sun Mar 14 12:43:57 2004
        $struct = $this->pDB_get_keys( $tablename, PDB_ASSOC);
        $cur_index  = 0;
        $blob_cols  = array();
        $keys       = array_keys( $struct);
        foreach ( $struct as $type){
            $row_dec = array();
            if ( strtolower( $type) == 'blob'){
                // store column-name in blob_cols with corresponding index
                $blob_cols[$cur_index] = $keys[$cur_index];
                // get unique_name from row before deleting it
                // ATTENTION: pDB_get_row() returns the value not the name
                $fp_blob = $this->LFIO_open( $tablename, $index);
                $row     = fgets( $fp_blob, PDB_NUM_LINEWIDTH_MAX);
                $row     = explode( PDB_DELIMETER, $row);
                foreach ( $row as $field){
                    $field   = $this->LFIO__decode_value( $field, $type);
                    array_push( $row_dec, $field);
                }
                $unique_name = $row_dec[$cur_index];
                $drop_blob   = $this->BLOB_delete( $tablename, $unique_name);
                // no need to check if BLOB was dropped, BLOB_delete()'ll do
                // this instead
            }
            $cur_index++;
        }
        
        // look for failure (presence of .pending-file)
        # good code is needed here !
        
        // set Transcription-Flag (create flag-file)
        $fp_flag = @fopen( $path . ".pending", "a", false);
        if ( !$fp_flag){
            $err = "LFIO::delete() - Could not append to flag-file( '$path'), error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }else{
            fwrite( $fp_flag, "LFIO::delete( '$tablename', $index) - " . time() . chr(10));
            fflush( $fp_flag);
            fclose( $fp_flag);
        }
        
        // create temp file
        $fp_tmp = fopen( $path . ".tmp", "w", false);
        // open .pdv (original table) only for reading
        $fp_pdv = fopen( $path . ".pdv", "r", false);
        // copy all rows except the one pointed by $index
        $row_index = 0;
        $bw        = 0;
        while ( $row_raw = fgets( $fp_pdv, PDB_NUM_LINEWIDTH_MAX)){
            if ( $row_index != $index){
                $bw += fwrite( $fp_tmp, $row_raw);
            }
            $row_index++;
        }
        // close open pointers
        fclose( $fp_tmp);
        fclose( $fp_pdv);
        // overwrite .pdv with .tmp by copying
        copy( $path . ".tmp", $path . ".pdv");
        // delete .tmp
        unlink( $path . ".tmp");
        // delete .pending when everything went OK
        unlink( $path . ".pending");
        
        return true;
    }
    
    // LFIO PRIVATE METHODS -----------------------------------------------------------------
    
    /**
    * @desc Open filepointer to given table and return it.
    * NOTE: Filepointer points to $tablename.row{$index}.field0.
    * NOTE: Filepointer is suitable for read/write operations.
    * @access private
    * @param string $tablename Name of table as string
    * @param int $index Row-index as int where $fp should point to.
    * @return stream Returns a filepointer (stream) to value-file or FALSE.
    */
    function LFIO_open( $tablename, $index){
        // check if table exists
        if ( !$this->is_pDB_table( $tablename)){
            $err = "Table('$tablename') could not be found, error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // get table's structure
        $struct = $this->pDB_get_keys( $tablename, PDB_ASSOC);
        // open filepointer to value-file for read/write
        $path = $this->getPath() . "/" . $tablename . ".pdv";
        $fp = @fopen( $path, "r+", false);
        // emitt an error if stream could not be opened
        if ( !$fp){
            $err = "Could not open value-file('$tablename), error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }else{
            // look if there is need to seek the filepointer
            if ( $index > 0){
                // calculate length of a row in given table
                // linebreak + (n_columns * delimeter)
                $row_len = 1 + ( count( $struct));
                for ( $i=0; $i<count( $struct); $i++){
                    $row_len = $row_len + $this->LFIO_get_fieldsize( $tablename, $i);
                }
                // seek filepointer to begin of row at given $index
                $offset = $row_len * $index;
                fseek( $fp, $offset, SEEK_SET);
            }
            return $fp;
        }
    }
    /**
    * @desc Read row from given LFIO-table at given index.
    * @param string $tablename Name of LFIO-table as string.
    * @param int $index Index of row in LFIO-table.
    * @return array Returns numeric array containing row's values.
    */
    function LFIO_read( $tablename, $index){
        // get filepointer to values-file pointing to row at $index
        $fp = $this->LFIO_open( $tablename, $index);
        // read in raw row-values
        $row = fgets( $fp, PDB_NUM_LINEWIDTH_MAX);
        if ( !$row) return null;
        // decode row
        $row = $this->LFIO_decode_row( $tablename, $row);
        return $row;
    }
    
    /**
    * @desc Writes given values to given LFIO-table to row at $index.
    * @access private
    * @param string $tablename Name of LFIO-table as string.
    * @param int $index Index (row) where to write values.
    * @param array $values Array (num) containing the values.
    * @return int Returns the number of written bytes to LFIO-table.
    */
    function LFIO_write( $tablename, $index, $values){
        // get filepointer to values-file pointing to row at $index
        $fp = $this->LFIO_open( $tablename, $index);
        // encode values (row)
        $values = $this->LFIO_encode_row( $tablename, $values);
        // FIX against invalid fields in LFIO-tables.
        if( !$values){
            $err = "LFIO_write() - Could not encode given row. LFIO encoder returned FALSE, error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // FIX-END
        // write encoded row to table ($fp)
        $bw = fwrite( $fp, $values . chr(10));
        fflush( $fp);
        fclose( $fp);
        return $bw;
    }
    
    /**
    * @desc Returns size of field in given table without delimeter.
    * @param string $tablename Name of table as string.
    * @param int    $num_col Number of column as integer.
    * @return int   Returns the size of field in bytes.
    * THIS METHOD IS TESTED AND WORKS Wed May 5 21:26:53 2004
    */
    function LFIO_get_fieldsize( $tablename, $num_col){
        // get structure of given table
        $struct = $this->pDB_get_keys( $tablename, PDB_NUM);
        // check if $num_col is in range
        $num_keys  = count( $struct);
        if ( $num_col >= $num_keys){
            $err = "LFIO_get_fieldsize() - ColumnIndex('$num_col') is out of bounds(0,$num_keys), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err, 1);
            return -1;
        }
        // get fieldsize
        $length = $struct[$num_col]['length'];
        return $length;
    }
    
    function LFIO_get_rowsize( $tablename){
    	
    }
    
    /**
    * @desc Counts number of rows in LFIO-table.
    * THIS IS VERY WEAK BY NOW -> BUGGY
    * @param string $tablename Name of LFIO-table as string.
    * @return int Returns number of rows in LFIO-table.
    */
    function LFIO_count_rows( $tablename){
        // check if given tablename is valid
        $path = $this->getPath() . "/" . $tablename . ".pdv";
		/*
		We have tried two different approaches to 
		count the rows in a table (fgets() and file(), both 
		horribly not apropriate on big tables). 
		Finally'll try something completely different:
		We will calculate the number of rows in a table by 
		divideing it's size (bytes) by the size of a row (inkl.
		delimeters,linebreak).
		This is gonna speed up things ;)
		*/
		// calculate size of a row in this table
		$size_row   = 0;
		$num_keys   = count( $this->pDB_get_keys( $tablename));
		for ( $c=0; $c<$num_keys; $c++){
			$size_field   = $this->LFIO_get_fieldsize( $tablename, $c);
			// This means: �row_size = field_size + number_of_keys + linebreak�
			$size_row    += $size_field;
		}
		$size_row        += $num_keys + 1;
		# Error checking is needed here.
		# Number of rows can never be a float.
		// get actual size of table (*.pdv) in bytes
		$size_table = filesize( $path);
		// calculate number of rows in table
		$num_rows   = $size_table / $size_row;
		/*
        // count rows sequentially
        $fp       = fopen( $path, "r", false);
        $num_rows = 0;
        while ( $line = fgets( $fp, PDB_NUM_LINEWIDTH_MAX)) $num_rows++;
        */
        // readin pdv-file as array (Chews too much memory, avoid this!!)
        #$pdv      = file( $path);
        #$num_rows = count( $pdv);
        return $num_rows;
    }
    
    // FHNDL-CODEC --------------------------------------------------------------------------
    
    /**
    * @desc Add a row to a table of type FHNDL.
    * @param string $table Name of table as string.
    * @param array $values Numeric array containing the values of current row.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function FHNDL_add_row( $table, $values){
        // get path to table
        $path = $this->getPath() . "/$table";
        
        // pdbize given row
        $values = $this->FHNDL_pdbize_row( $table, $values);
        
        // check the row's structure
        if ( !$this->FHNDL_check_row( $table, $values)){
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "FHNDL_add_row() - [Row's structure does not match the table's structure !]", 0);
            return false;
        }
        
        $n_value = 0;
        foreach ($values as $value){
            // get the fields control-byte
            $control_byte = substr( $value, 0, 1);
            
            // check if it's a valid control_byte
            if( $control_byte!="%" and $control_byte!="�" and $control_byte!="@"){
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__),"No valid control_byte ('$control_byte') provided for field($n_value) !", 0);
                return false;
            }
            
            // chop of the first byte (control-byte)
            $value = substr_replace($value, "", 0, 1);
            
            // handle blobs (create blob-files)
            if ( $control_byte=="@"){
                // get a unique name for blob-file
                $blob_unique_name = $this->BLOB_get_unique_name( $value);
                
                // write the blob using core's own method
                $blob_written = $this->BLOB_write( $table, $blob_unique_name, $value);
                
                // do report
                if( $this->CONF_DATA['VERBOSE']){
                    $this->_put_message( 'pDB_ERROR', null, (int)$blob_written."pDB_add_row() - blob-field was successfully written to $table.\n");
                }
                
                // change value of blob field to blob-reference
                $values[$n_value] = "@".$blob_unique_name;
            }
            $n_value++;
        }
        
        // open the $table.pdv (values-file)
        $fp_out = fopen( $path.".pdv", "a", false);
        if ( !$fp_out){
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "Could not open table ('$path') for writing!", 0);
            return false;
        }
        
        // prepare the row to be written
        $n_keys = count($values);
        if ( $n_keys<1){
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "No Values to insert!", 0);
            return false;
        }
        
        // essential base64_encode is done here
        //$values = $this->FHNDL_enc_row(&$values); - was throwing Fatal error: Call-time pass-by-reference has been removed in C:\Users\Lesio\workspace\MAF\plugin\PhpDb\pDB\pDB_CORE.php on line 2706
        $values = $this->FHNDL_enc_row($values);
        
        // write the row
        $bytes = fputs($fp_out, $values."\n");
        if ( $this->CONF_DATA['VERBOSE']){
            $err = "$bytes bytes were successfully written to $table\n";
            $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
        }
        
        // clean up
        fclose($fp_out);
        return true;
    }
    
    
    /**
    * @desc This method checks if a given row-structure is valid to fit in a given table.
    * @param string $table Name of table as string.
    * @param array $row_array A numeric array containing a pdbized row.
    */
    function FHNDL_check_row($table, $row_array){
        // MAKING THIS METHOD MORE LIGHTWEIGHTED
        /*
        // check if table exsits
        if( !$this->is_pDB_table( $table)){
        $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "FHNDL_check_row() Given table('$table') was not found !", 0);
        return false;
        }
        */
        
        // get row's structure from table
        $struct = $this->pDB_get_keys( $table, PDB_CB);
        $n_struct = count( $struct);
        
        // check $row_array num_fields
        $n_fields = count( $row_array);
        // this is silently triggered when an END_OF_TABLE is reached
        // (a fix for this annoying message is needed)
        if ( $n_fields != $n_struct){
            $err = "FHNDL_check_row() - Number of fields('$n_fields') and keys('$n_struct') do not match !";
            $this->_put_message("pDB_WARNING", array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // check $row_array's fields if they're valid at given position
        $n_column = 0;
        foreach ($row_array as $field){
            // divide $control_byte from $value
            $control_byte = substr($field,0,1);
            $value = substr( $field, 1);
            
            #$this->_put_message( "pDB_WARNING", array( __FILE__, __LINE__), "VALUE: " . $value . "\n");
            
            // check if given attribute is valid at current position
            $struct_cb = $struct[$n_column];
            
            if ( $control_byte != $struct_cb){
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "FHNDL_check_row() - [Field('$n_fields') should be('$struct_cb') but is actually('$control_byte') !", 0);
                return false;
            }
            $n_column++;
        }
        // everything is OK
        return true;
    }
    
    
    /**
    * @desc Count rows in given FHNDL-table.
    * @param string $table Name of FHNDL-table as string.
    * @return int Number of rows in FHNDL-table.
    */
    function FHNDL_count_rows( $table){
        
        // check if table-type is FHNDL
        if ( $this->pDB_get_table_type( $table) != 'FHNDL'){
            $err = "FHNDL_count_rows() - Not a FHNDL-table, could not count rows.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // try open filepointer to *.pdv-file
        $pdv_file   = $this->getPath()."/".$table.".pdv";
        if ( !is_file($pdv_file)){
            $this->_put_message("pDB_WARNING", array(__FILE__, __LINE__), "FHNDL_count_rows() - Given Table's value-file('$table.pdv') could not be found in this DB('".$this->CORE_DATA['DB_CURRENT']."'), aborting count of rows.");
            return false;
        }else{
            $fp_pdv = fopen($pdv_file, "r+b", false);
        }
        
        // get struct
        $struct = $this->pDB_get_keys( $table);
        
        // cycle through table and count all valid rows
        /*
        The method of checking the rows should be strongly optimized!
        By now it's too expensive, cause it can not cache anything and
        everytime needs to reaccess the same files again and again for
        each row -> in cost of speed :'(
        */
        $continue = true;
        while($continue){
            // seek allways to real-rows (escape FHNDL-zombies)
            $this->FHNDL_seek_to_real_row($fp_pdv, $real_row);
            
            // read in the next real-row (raw)
            $row = fgets($fp_pdv, PDB_NUM_STRINGLENGTH_MAX);
            
            // decode current row
            // $row = $this->FHNDL_dec_row(&$row); - was throwing Fatal error: Call-time pass-by-reference has been removed in C:\Users\Lesio\workspace\MAF\plugin\PhpDb\pDB\pDB_CORE.php on line 2816
            $row = $this->FHNDL_dec_row($row);
            
            // check current decoded row's structure before declaring it as valid
            // if ( !$this->FHNDL_check_row( $table, $row)){
            if ( count( $row) != count( $struct)){
                $continue = false;
            }else{
                // count this row
                $num_rows++;
            }
            $real_row++;
        }
        return $num_rows;
    }
    
    
    /**
    * @desc Drop a row at given index from given FHNDL-table.
    * @param string $table Name of table as string.
    * @param int $row_index Index of row to drop in current table as integer.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function FHNDL_drop_row( $table, $row_index){
        
        // get path from core
        $path = $this->getPath()."/".$table;
        
        // open the [$table].pdv (values-file)
        $fp_out = @fopen($path.".pdv", "r+", false);	// 'r+'-mode is needed for seeking the file-pointer
        if(!$fp_out){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "Could not open table ('$path') for writing!", 0);
            return false;
        }
        
        // seek filepointer to desired row
        // PROBLEMS COULD EMERGE HERE, he musn't seek to REAL-rows cause __ID__ are physical rows!
        // This is very error-prone and needs a revision !
        $this->FHNDL_seek_to_real_row($fp_out, $row_index);
        
        // drop the currently pointed row by setting control-byte to : #
        $bw = fwrite($fp_out, "#");
        fclose($fp_out);
        
        return true;
    }
    
    /**
    * @desc Reads a single row from given table at given index. Returns null at the EOT.
    * @param string $table Name of table as string.
    * @param int $row_index Index of row as integer.
    * @param optional $result_type Can be: PDB_NUM(default), PDB_ASSOC.
    * @return mixed Returns an array populated with values from asked row OR null when something goes wrong.
    */
    function pDB_get_row( $table, $row_index, $result_type=PDB_NUM){
        // get table-type
        $tbl_type = $this->pDB_get_table_type( $table);
        
        // get table's keys
        $tbl_struct = $this->pDB_get_keys( $table);
        
        // LFIO
        if ( $tbl_type == 'LFIO'){
            $values = $this->LFIO_read( $table, $row_index);
            // PDB_ASSOC-style
            if ( $result_type == PDB_ASSOC){
                $keys = array_keys( $this->pDB_get_keys( $table, PDB_ASSOC));
                $values_assoc = array();
                foreach ( $keys as $key){
                    $values_assoc[$key] = array_shift( $values);
                }
                return $values_assoc;
            }
            return $values;
        }
        
        // FHNDL
        if ( $tbl_type == 'FHNDL'){
            // check row index
            $num_rows = $this->FHNDL_count_rows( $table);
            if ( $row_index >= $num_rows or $row_index < 0){
                $err = "pDB_get_row() - Index('$row_index') is out of bounds(0,".($num_rows-1)."), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
                return null;
            }
            // check if values-file (*.pdv) exists
            $path   = $this->getPath()."/".$table.".pdv";
            if ( !is_file( $path)){
                $err = "pDB_get_row() - Table('$table') could not be found using this path: '".$path."'.";
                $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
                return null;
            }
            // open filepointer for reading
            $fp = fopen( $path, 'r', false);
            // read in the rows sequentially
            $fp_in  = fopen( $path, "r", false);
            // seek to real_row
            $this->FHNDL_seek_to_real_row( $fp_in, $row_index);
            // read row
            $row = fgets( $fp_in, PDB_NUM_LINEWIDTH_MAX);
            fclose ( $fp_in);
            // generic decode method for FHNDL tables
            $row = $this->FHNDL_dec_row( $row);
            // rebundle decoded values to array, if $row is not null
            if ( $row){
                return $row;
            }
            return null;
        }
        
        // unrecongnized table, error.
        $err = "pDB_get_row() - Table('$table') of type('$tbl_type') could not be handled, error.";
        $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
        return null;
    }
    
    /**
    * @desc Parses the .pdv-file (rowwise) which contains the values of the specified table and returns them in a array.
    * NOTE: This method handles ONLY .pdv's from tables of type 'FHNDL'. Do NOT call this on LFIO-tables!
    * @param string $table Name of table as string.
    * @return array Returns an array containing the tables values.
    * @access private
    */
    function FHNDL_read_pdv( $table){
        
        // check if table is FHNDL before doing anything
        $prop = $this->pDB_get_properties( $table);
        if ( $prop['TABLTYPE']!='FHNDL'){
            $err = "FHNDL_read_pdv() - Not a FHNDL-table, could not read values.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // check if values-file (*.pdv) exists
        $path   = $this->getPath()."/".$table.".pdv";
        if ( !is_file( $path)){
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "TABLE : '".$path."' COULD NOT BE FOUND!", 0);
        }
        
        // read in the rows sequentially
        $fp_in  = fopen( $path, "r", false);
        $values = array();
        while( $row = fgets( $fp_in, PDB_NUM_LINEWIDTH_MAX)){
            
            // generic decode method for FHNDL tables
            $row = $this->FHNDL_dec_row( $row);
            
            // rebundle decoded values to array, if $row is not null
            if ( $row){
                array_push( $values, $row);
            }
        }
        fclose ( $fp_in);
        return $values;
    }
    
    /**
    * @desc Udates a row at given index with given values in FHNDL-table.
    * @param string $table Name of table as string.
    * @param int $row_index Index of row to be updated as integer.
    * @param array $values Values contained in numeric array.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function FHNDL_update_row( $table, $row_index, $values){
        // get path from core
        $path = $this->getPath()."/".$table;
        
        // get filepointer to values-file ([$table].pdv)
        $fp_out = @fopen($path.".pdv", "r+", false);	// 'r+'-mode is needed for seeking the file-pointer
        if(!$fp_out){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "pDB_update_row() Could not open table ('$path') for writing!", 0);
            return false;
        }
        
        // check if number of fields in given row match those in table's structure
        $num_fields = count($values);
        $struct = $this->pDB_get_keys($table, 'pDB_CB');
        $num_struct = count($struct);
        if( $num_fields != $num_struct ){
            $this->_put_message("pDB_ERROR", array(__FILE__, __LINE__),"pDB_update_row() - Given number of elements in row('$num_fields') and number of elemnents in table's structure('$num_struct') do not match, aborting update!");
            return false;
        }
        
        $n_value = 0;
        foreach ($values as $value){
            
            // get the fields control-byte
            $control_byte = substr( $value, 0, 1);
            if($this->CONF_DATA['VERBOSE']){
                $err = "FHNDL_update_row() - Control-byte of field $n_value : ".$control_byte;
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
            }
            
            // update the blob-file
            if($control_byte=="@"){
                // get the name of allready existing blob-file to update it
                $this->FHNDL_seek_to_row($fp_out, $row_index);
                $row_temp = fgets($fp_out);
                $row_temp = $this->FHNDL_dec_row($row_temp);
                $blob_unique_name = substr($row_temp[$n_value], 1);
                if ( $this->CONF_DATA['VERBOSE']){
                    $err = "FHNDL_update_row() - Blob_unique-name is now : $blob_unique_name";
                    $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
                }
                
                // chop of the first byte (the @ char : it only was a marker, not real data!)
                $value = substr_replace($value, "", 0, 1);
                
                // write the blob using core's own method
                $blob_written = $this->BLOB_write( $table, $blob_unique_name, $value);
                
                // do report
                if($this->CONF_DATA['VERBOSE']){
                    $err = "FHNDL_update_row() - ".(int)$blob_written." blob-field was successfully written to $table.\n";
                    $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
                }
                
                // change value of blob field to new blob-reference
                $values[$n_value] = "@".$blob_unique_name;
            }
            (int)$n_value++;
        }
        
        // seek filepointer to row that has to be updated
        #$this->FHNDL_seek_to_row($fp_out, $row_index);
        fclose($fp_out);
        
        # After having tryied a lot of mechanisms to update a row in place,
        # i'm giving up by using an alternative approach.
        # I'm gonna fix this when a good idea comes along.
        # The new approach is to drop the row and then add the updated one to table using conventional
        # methods like : pDB_drop_row() and pDB_add_row().
        
        // drop the row
        if ( $dropped = $this->pDB_drop_row( $table, $row_index) and $this->CONF_DATA['VERBOSE']){
            $this->_put_message( "pDB_WARNING", array( __FILE__, __LINE__), "FHNDL_update_row() Dropped Row('$row_index').");
        }
        
        // insert the updated one
        if ( $inserted = $this->pDB_add_row( $table, $values) and $this->CONF_DATA['VERBOSE']){
            $this->_put_message( "pDB_WARNING", array( __FILE__, __LINE__), "FHNDL_update_row() Row was inserted.");
        }
        
        if ( $dropped and $inserted){
            return true;
        }else{
            $this->_put_message( "pDB_ERROR", array( __FILE__, __LINE__), "FHNDL_update_row() There has been an internal error updating Row('$row_index').");
            return false;
        }
    }
    
    
    // LFIO-CODEC ---------------------------------------------------------------------------
    
    /**
    * @desc Encodes a given array($row) before it can be passed to LFIO_write().
    * NOTE: This method has as sideeffect that it writes BLOB's passed to it.
    * 		Do NEVER call this method directly, use LFIO__encode_value() instead.
    * @access private
    * @param string $tablename Name of table as string.
    * @param array $values Array (num) containing values (row).
    * @return string Returns an encoded string ready to write or FALSE on failure.
    */
    function LFIO_encode_row( $tablename, $values){
        // get table's structure
        $struct = $this->pDB_get_keys( $tablename, PDB_ASSOC);
        // compose row (encoded string)
        $row     = "";
        $num_col = 0;
        foreach ( $struct as $key){
            $type  = $key['type'];
            // NOTE: $values gets consumed
            $value = array_shift( $values);
            // BLOB's data is stored to a BLOB-FILE and the unique_name is kept as value.
            if ( strtolower( $type) == 'blob'){
                $unique_name = $this->BLOB_get_unique_name( $value);
                $data        = $value;
                $blob        = $this->BLOB_write( $tablename, $unique_name, $data);
                if ( !$blob){
                    $err = "LFIO_encode_row() - Error while writing BLOB-field('$type'), error.";
                    $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
                    return false;
                }
                // make unique_name of BLOB the value now, data is already stored.
                $value = $unique_name;
            }
            // encode field/value ()
            $value = $this->LFIO__encode_value( $tablename, $num_col, $value, $type);
            // FIX against invalid field-lengths in LFIO-tables.
            if ( !$value){
                $keys = $this->pDB_get_keys( $tablename, PDB_NUM);
                $err  = "LFIO_encode_row() - Invalid value for field('".$type."') in column('".$keys[$num_col]['name']."'), error.";
                $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
                return false;
            }
            $num_col++;
            // FIX-END
            $row .= $value;
        }
        return $row;
    }
    
    /**
    * @desc Private function internally needed by LFIO-methods.
    * @param string $tablename Name of table as string. (NEW 0.35c and above)
    * @param int $num_col Number of column as integer.  (NEW 0.35c and above)
    * @param mixed $val Value to be encoded.
    * @param string $type Type of value as string.
    * @return mixed Returns encoded value or FALSE when there was an error.
    */
    function LFIO__encode_value( $tablename, $num_col, $val, $type){
        // check if given type is valid
        if ( !$this->is_valid_type( $type)){
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), "LFIO__encode_value() - Unknown field-type('$type') was passed, error!\n");
            return false;
        }
        
        // get field-size of given field-type from key-file (0.35c and above)
        $struct     = $this->pDB_get_keys( $tablename, PDB_NUM);
        if ( $type == "int" or $type == "string" or $type == "enum"){
            $field_size = $struct[$num_col]['length'];
        }elseif ( $type == "blob"){
        	// BLOB's field-size is UNCHANGEABLE.
        	$field_size = PDB_NUM_BLOBLENGTH_MAX;
        }
        
        // store old value
        $val_org = $val;
        $len_org = strlen( $val_org);
        
        // encode given value
        $val = base64_encode( $val);
        $len = strlen( $val);
        
        // check if given value is not too long
        # BETTER CODE IS NEEDED HERE
        if ( $len > $field_size){
            $err = "LFIO__encode_value() - Encoded Field-Length('$len') for Field-Type('$type') exeeds('$field_size'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        // pad encoded value to maximum-fieldsize
        while ( strlen( $val) < $field_size){
            $char = PDB_FIELD_FILL_CHAR;
            $val .= chr( $char);
        }
        
        // add delimeter to each field
        $val .= PDB_DELIMETER;
        
        // return this encoded, padded and delimeted field
        return $val;
    }
    
    /**
    * @desc Decodes a given row.
    * @access private
    * @param string $tablename Name of table as string
    * @param string $row Row as string to be decoded.
    * @return array Returns a numeric array containing the decoded values.
    */
    function LFIO_decode_row( $tablename, $row){
        // get table's structure
        $struct = $this->pDB_get_keys( $tablename, PDB_ASSOC);
        // split fields by delimeter
        $fields = explode( PDB_DELIMETER, $row);
        // build array to return (row)
        $row = array();
        foreach ( $struct as $key){
            // get type of column from key-struct
            $type  = $key['type'];
            $value = array_shift( $fields);
            $value = $this->LFIO__decode_value( $value, $type);
            // get value of BLOB now with the decoded unique_name
            if ( strtolower( $type) == 'blob'){
                $value = $this->BLOB_read( $tablename, $value);
            }
            // add value to row
            array_push( $row, $value);
        }
        return $row;
    }
    
    // needed decoder function (for values/fields)
    function LFIO__decode_value( $value, $type){
        // check if given type is valid
        if ( !$this->is_valid_type( $type)){
            $err = "LFIO_decode_row() - Wrong field-type('$type') was passed, error!\n";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        // strip delimeter
        $value = substr( $value, 0, -2);
        // strip all field_fill_chars
        $value = str_replace( chr(PDB_FIELD_FILL_CHAR), "", $value);
        $value = base64_decode( $value);
        return $value;
    }
    
    // FHNDL --------------------------------------------------------------------------------------------
    
    
    /**
    * @desc This method writes a complete row to given table at given index (physical). Table MUST be of type FHNDL!
    * NOTE : If a row was previously dropped, this method leaves the same state behind it!
    * A zombie remains a zombie. Use summon_zombie() instead to reenable it. ��]
    * @param string $tablename Name of table as string.
    * @param int $index Physical index in table pointing to a row.
    * @param array $row A previously pdbized row to write in given table.
    * @return bool Returns true on success and FALSE on failure.
    * @access private
    */
    function FHNDL_write_row( $tablename, $index, $row){
        // check if table('$tablename') exists and open a filepointer to it.
        if ( !$this->is_pDB_table( $tablename)){
            $this->_put_message( "pDB_ERROR", array(__FILE__, __LINE__), "FHNDL_write_row() Table('$tablename') could not be found, abort writing row.");
            return false;
        }
        
        // check if table is FHNDL before doing anything
        $prop = $this->pDB_get_properties( $table);
        if ( $prop['TABLTYPE']!='FHNDL'){
            # EMITT ERROR FOR WRONG TABLE TYPE
            return false;
        }
        
        // open filepointer to *.pdv
        $path = $this->getPath();
        $fp_pdv = fopen( $path.$tablename, "r+", false);	// 'r+' is needed for seeking
        if ( !$fp_pdv){
            $this->_put_message( "pDB_error", array( __FILE__, __LINE__), "FHNDL_write_row() Table('$tablename') was found but could not be opened, abort writing row.");
            return false;
        }
        
        // check if given index	 is valid in current table.
        # Maybe this can be done constantly while advancing from row to row.
        # Would be much smarter then moving one complete time through the table to evaluate the number of rows.
        # The given index must allways be between 0 and the number of rows in table subracting one.
        
        // check if given row's structure is appropriate in given table's context.
        $struct = $this->pDB_get_keys( $tablename, 'pDB_CB');
        if(!$this->FHNDL_check_row( $tablename, $row)){
            $this->_put_message("pDB_ERROR", array( __FILE__, __LINE__), "FHNDL_write_row() - The row you provided is not compatible with this table. See previous messages for more details.");
            return false;
        }
        
        // seek filepointer to given index.
        if(!$this->FHNDL_seek_to_row( $fp_pdv, $index)){
            $this->_put_message("pDB_ERROR", array( __FILE__, __LINE__), "FHNDL_write_row() - Could not seek to given Index('$index'). See previous messages for more details.");
            return false;
        }
        
        // move filepointer one char to leave the row's control-byte untouched.
        fseek( $fp_pdv, 1, SEEK_CUR); /* this seeks 1 byte from current position 'SEEK_CUR' */
        
        // write field for field there.
        $bytes_written = 0;
        foreach ($row as $field){
            $bytes_written .= fwrite( $fp_pdv, $field.PDB_DELIMETER);
        }
        
        // return status
        return $bytes_written;
    }
    
    /**
    * @desc Returns a pdbized row, needed by methods like : FHNDL_add_row(), FHNDL_update_row().
    * @param string $tablename Name of destination-table as string.
    * @param array $row Array containing the row-data (numeric!)
    * @return array Returns a pdbized row on sucess, FALSE on failure.
    */
    function FHNDL_pdbize_row( $tablename, $values){
        // check if table is available
        if ( !$this->is_pDB_table( $tablename)){
            // warning should already be emitted by is_pDB_table() !
            $this->_put_message( "pDB_ERROR", array(__FILE__,__LINE__), "FHNDL_pdbize_row() Tablename('$tablename') not found, aborting pdbizing.");
            return false;
        }
        // get struct from given table
        $struct = $this->pDB_get_keys( $tablename, "pDB_CB");
        // check if number of elements in struct and elements in row do match, else emitt error
        if( count($struct) != count($values) ){
            $this->_put_message("pDB_ERROR", array(__FILE__, __LINE__) ,"FHNDL_pdbize_row() Number of keys('".count($struct)."') and number of fields in row('".count($row)."') do not match, aborting pdbizing.");
            return false;
        }
        // pdbize given row
        $pdbized_row = array();
        for ( $n=0;$n< (count($struct));$n++ ){
            $pdbized_row[$n] = $struct[$n] . $values[$n];
        }
        // return pdbized row
        return $pdbized_row;
    }
    
    /**
    * @desc Seeks a given filepointer to the really desired row in .pdv .
    * NOTE : This method is not appropriate when dealing with __ID__'s.
    * __ID__'s are physical adresses pointing into tables. Use FHNDL_seek_to_row() instead.
    * @param resource $fp_pdv A valid filepointer to a table
    * @param int $row_index A valid index pointing to a row in table
    * @return boolean Returns TRUE on SUCCESS, and FALSE on FAILURE
    * @access private
    */
    /* THIS FUNCTION IS TESTED AND WORKS 100% BZam */
    function FHNDL_seek_to_real_row($fp_pdv, $row_index){
        // before anything is done check the given filepointer
        if(!is_resource($fp_pdv)){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "FHNDL_seek_to_real_row() Given FilePointer('$fp_pdv') is not a valid resource!", 0);
            return false;
        }
        
        // keep the position of filepointer stored
        $fp_pdv_old_pos = ftell($fp_pdv);
        if($this->CONF_DATA['VERBOSE']){
            $err = "FHNDL_seek_to_real_row() - Filepointer pos old : ".$fp_pdv_old_pos.chr(10);
            $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
        }
        
        // rewind filepointer to the beginning of file
        fseek($fp_pdv, 0);
        
        // seek filepointer to row that really is pointed by $row_index (skip dropped zombies)
        $seek_index = 0;	// seek index is incremented on each row encountered
        $real_index = 0;	// real index is only incremented when a regular row is encountered
        while($real_index <= $row_index){
            // read the control-byte only
            $control_byte = fread($fp_pdv, 1);
            // return filepointer to it's original pos (-1)
            fseek($fp_pdv, -1, SEEK_CUR);
            
            if($this->CONF_DATA['VERBOSE']){
                $err = "FHNDL_seek_to_real_row() - ControlByte('".$control_byte."') at seek($seek_index) and real($real_index)\n";
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
            }
            
            /**
            * @desc Before seeking another row (what would be wrong), look if filepointer
            * is in the right row at the right position (on first byte of row, pDB_TABLE_CONTROL_BYTE) !!
            * Make sure the row pointed now is not also a zombie, else result would be wrong !
            * Make sure the end of table was not allready reached, else result may be wrong !
            */
            if($real_index==$row_index && $control_byte!="#"){
                // jump out the loop before the pointer is moved to next line
                return true;
            }
            
            // actualize indexes
            $seek_index++;
            
            // this is a hack needed to pass important data to $this->pDB_select()
            $this->seek_index = $seek_index;
            
            // increment real_index only if row was not previously dropped (!zombie)
            if($control_byte!="#"){
                $real_index++;
            }
            
            // seek filepointer to next row
            $next_row = fgets($fp_pdv, PDB_NUM_LINEWIDTH_MAX);
        }
        
        // set back filepointer to old stored position, otherwise many other methods will have odd behaviour
        fseek($fp_pdv, $fp_pdv_old_pos);
        return true;
    }
    
    /**
    * @desc Seeks a given filepointer to a physical row in .pdv .
    * NOTE : This method is recommended when dealing with __ID__'s.
    * __ID__'s are physical adresses pointing into tables.
    * Use FHNDL_seek_to_real_row() when u wanna have seek to a real-row at given position instead.
    * @param resource $fp_pdv A valid filepointer pointing into a table in FileSystem.
    * @param int $row_index A valid index pointing to a row in table.
    * @return boolean Returns TRUE on SUCCESS, and FALSE on FAILURE.
    * @access private
    */
    function FHNDL_seek_to_row( $fp_pdv, $row_index){
        // before anything is done check the given filepointer
        if(!is_resource($fp_pdv)){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "Given FilePointer is not a valid resource!", 0);
            return false;
        }
        
        // keep the position of filepointer stored
        $fp_pdv_old_pos = ftell($fp_pdv);
        if($this->CONF_DATA['VERBOSE']){
            $err = "FHNDL_seek_to_row() - Filepointer pos old : ".$fp_pdv_old_pos.chr(10);
            $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
        }
        
        // rewind filepointer to the beginning of file
        fseek($fp_pdv, 0);
        $this->seek_index = 0;
        
        // if row_index is 0, return
        if($row_index<1){
            return true;
        }
        
        // seek filepointer to physical-row pointed by that index
        while($this->seek_index < $row_index){
            // there is no check for control-bytes needed here
            # --
            // seek filepointer to next row
            $next_row = fgets($fp_pdv, PDB_NUM_LINEWIDTH_MAX);
            (int)$this->seek_index++;
        }
        
        // filepointer is left where it is
        # --
        
        return true;
    }
    
    // ------------------ FHNDL ROW FACTORY ---------------------------
    
    /**
    * @desc Encodes a given array of values to a pDB_ROW. (FHNDL only)
    * @param array $row_values Array containing the values of a row
    * @return string $pDB_ROW
    */
    function FHNDL_enc_row($row_values){
        // check if $row_values is array, else fire error and abort
        if ( !is_array( $row_values)){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "Param passed to FHNDL_enc_row is not an array !", 0);
        }
        // set the control-byte (first byte of a row) empty
        $row = " ";
        // encode values
        // $row_values = array_map("base64_encode", &$row_values); - was throwing Fatal error: Call-time pass-by-reference has been removed in C:\Users\Lesio\workspace\MAF\plugin\PhpDb\pDB\pDB_CORE.php on line 3446
        $row_values = array_map("base64_encode", $row_values);
        // add values to row
        $n_values = count($row_values);
        $n = 0;
        foreach ($row_values as $row_value){
            $row .= $row_value;
            if ( $n < ($n_values-1) ) $row .= ";";
            $n++;
        }
        return $row;
    }
    
    /**
    * @desc Decodes a given pDB_ROW back to it's original values. [Without Control-Bytes!]
    * @param string $pDB_ROW String as pDB_ROW
    * @return array Returns an array containing the row's values or false when a column is disabled
    */
    function FHNDL_dec_row($pDB_row){
        // chop off the first byte of row-string (control-byte for entire row)
        $control_byte = substr($pDB_row, 0, 1);	// BETTER CODE NEEDED HERE
        $pDB_row      = substr($pDB_row, 1);    // this one could cost performance on long rows!!!
        
        // check control-byte
        if ( $control_byte=="#"){
            // row is dropped ('#')
            if ( $this->CONF_DATA['VERBOSE']){
                $err = "FHNDL_dec_row() - Row is dropped ('$control_byte') : ".rtrim($pDB_row)."\n";
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
            }
            return false;
        }else{
            // row is regular (' ') [empty control-byte]
            if($this->CONF_DATA['VERBOSE']){
                $err = "FHNDL_dec_row() - Row is regular ('$control_byte') : ".rtrim($pDB_row)."\n";
                $this->_put_message( 'pDB_WARNING', array(__FILE__,__LINE__), $err);
            }
            // split the row(string) up to values(array)
            $row_values = explode(";", $pDB_row);
            // decode all fields (must be performed before BLOB-CHECK)
            // $row_values = array_map("base64_decode", &$row_values); - was throwing Fatal error: Call-time pass-by-reference has been removed in C:\Users\Lesio\workspace\MAF\plugin\PhpDb\pDB\pDB_CORE.php on line 3486
            $row_values = array_map("base64_decode", $row_values);
            // cut-off control-bytes of fields (from FHNDL)
            $values = array();
            foreach ( $row_values as $value){
                array_push( $values, substr( $value, 1));
            }
            // BLOB-CHECK IS GONNA BE APPLIED HERE ON EACH KEY
            
        }
        return $values;
    }
    
    // MAINTAINANCE FUNCTIONS -------------------------------------------------
    
    /**
    * @desc [EXPERIMENTAL] Cleans the currently used database by moving backup-tables out from db to folder 'tmp/'
    * @param void
    * @return bool
    */
    function pDB_clean_db(){
        // get the currently used db from
        $db = $this->getPath();
        
        // get a list of available tables in current db
        $tables = $this->pDB_show_tables();
        
        // look for each tables '$table.bak'-files to move to tmp/
        foreach ($tables as $table){
            $err = "pDB_clean_db() - Looking for : $table.pdv.bak";
            $this->_put_message("pDB_WARNING", array(__FILE__,__LINE__), $err, 1);
            $table_path = $db."/".$table.".pdv.bak";
            if(is_file($table_path)){
                $err = "pDB_clean_db() - Try moving '$table.pdv.bak' to tmp/ ... ";
                $this->_put_message("pDB_WARNING", array(__FILE__,__LINE__), $err,1);
                copy( $table_path, "tmp/".$table.".pdv.bak");
                if ( strlen( $back = system( "rm $table_path -f")) < 1){	// THIS IS LOUSY CODE! WORKS ONLY ON POSIX
                $this->_put_message("pDB_WARNING", array(__FILE__,__LINE__),  "pDB_clean_db() - OK [$back]", 1);
                }else{
                    $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__),  "pDB_clean_db() - FAILED [$back]", 1);
                }
            }else{
                $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "pDB_clean_db() - NOT FOUND", 1);
            }
        }
        
        // more functionality is gonna be added from time to time when it's needed
    }
    
    /**
    * @desc Removes dead-rows (zombies) and cleans up given FHNDL-table.
    * @param string $table Tablename as string.
    * @return bool
    */
    /* THIS FUNCTION IS STABLE 0629 BZam */
    function FHNDL_clean_table($table){
        
        // check if DB_CURRENT-Flag is properly set
        if(!$path = $this->getPath()){
            $err = "FHNDL_clean_table() - DB_CURRENT-flag is not set! SELECT A DB FIRST.";
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // check if it's a FHNDL-table, else do nothing
        if ( $this->pDB_get_table_type( $table) != 'FHNDL'){
            $err = "FHNDL_clean_table() - Table('$table') is not a FHNDL-table, abort cleaning.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return false;
        }
        
        //
        
        /*
        a little trick is used for transcripting the table.
        the trick consists in creating a file named temp.pdv
        where the table gets transcripted in.
        this tactic is safe, cause there will be no loss of data in
        case of failure or system-hang while cleaning up a table.
        so we can easy read in line by line, clean it &
        write the buffer down the the newly created temp.pdv.
        during this action the table will be available to other users (not proof!!)
        while we do not need large amount of memory for cleaning tables up,
        this makes it possible to handle large tables processing them sequentially.
        after cleaning-routine has finished the original table gets renamed,
        the newly transcripted table will take over the name of the original table.
        doing so everything is OK and the table is cleaned from dead rows (zombies).
        the remaining old table can be removed by calling $this->pDB_clean_db.
        */
        
        // create temp-file (temp.pdv)
        $table_new = $path."/".$table.".temp.pdv";
        $fp_new = fopen($table_new, "w", false);
        
        // open table to clean (read-only)
        $table_old = $path."/".$table.".pdv";
        $fp_old = fopen($table_old, "r", false);
        
        // check both filepointers
        if(!$fp_new or !$fp_old){
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), "There was an error cleaning table($table)!", 1);
        }
        
        $row = 0;
        // get the first rows-data
        $this->FHNDL_seek_to_real_row($fp_old, $row);
        $data = fgets($fp_old);
        // init crawler-loop
        while(strlen($data)>1){
            // read in (only real-rows)
            $this->FHNDL_seek_to_real_row($fp_old, $row);
            $data = fgets($fp_old);
            
            // break loop (else an empty line gets written)
            if(strlen($data)<=1){
                break;
            }
            
            // write raw-data without to parse it
            fputs($fp_new, $data);
            
            // increment the row-counter
            $row++;
        }
        // close both filepointers, they're not needed anymore
        fclose($fp_new);
        fclose($fp_old);
        
        // rename the original table to '$table.pdv.bak'
        rename($table_old, $table_old.".bak");
        
        // rename the newly transcripted table-file to '$table.pdv'
        rename($table_new, $table_old);
        
        // change file-permissions on new file
        @chmod ($table_old, 0777);  // octal; correct value of mode
        
        // everything is done successfully
        return true;
    }
    
    // BLOB-FACTORY ---------------------------------------------------------------
    
    /**
    * @desc Writes a blob to filesystem. (FHNDL & LFIO)
    * NOTE: pDB creates for each blob-column an own folder.
    * @param string $table_name Name of table as string.
    * @param string $unique_name Name for blob. Must be unique!
    * @param mixed $data Data or value of blob passed as mixed type.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function BLOB_write( $table_name, $unique_name, $data){
        // get path to table from core
        $path = $this->getPath()."/".$table_name.".blob/".$unique_name;
        
        // create a new blob-file named $blob_unique_name.blob, or overwrite existent one
        $fp_blob = fopen( $path, "w+b", false);
        
        // add BLOB-HEADER to data
        $data  = $this->BLOB_add_header( $data, $unique_name);
        
        // write all given data there in
        $bytes_written = fputs( $fp_blob, $data);
        
        // close filepointer
        fclose( $fp_blob);
        
        // change the permissions on this blob-file
        @chmod( $path, 0777);
        
        return true;
    }
    
    /**
    * @desc This method reads a given BLOB and returns his contents.(FHNDL & LFIO)
    * @param string $table Name of table as string.
    * @param string $unique_name Name of blob as string.
    * @return mixed Returns the blob's value or FALSE on failure.
    */
    function BLOB_read( $table_name, $unique_name){
        // get Path to table's blob-folder
        $path = $this->getPath()."/".$table_name.".blob/".$unique_name;
        
        // check if blob exists
        if ( !is_file( $path)){
            $err = "BLOB_read() - Could not find blob('$unique_name') in path('$path'), error.";
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // open filepointer in binary-read-mode
        $fp_blob   = fopen( $path, "r+b", false);
        $data      = fread( $fp_blob, filesize( $path));
        fclose( $fp_blob);
        
        // remove BLOB-HEADER from data
        $data = $this->BLOB_remove_header( $data);
        
        return $data;
    }
    
    /**
    * @desc Deletes given BLOB.
    * @param string $table_name Name of table as string.
    * @param string $unique_name Name of BLOB as string.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function BLOB_delete( $table_name, $unique_name){
        // get Path to table's blob-folder
        $path = $this->getPath()."/".$table_name.".blob/".$unique_name;
        
        // check if blob exists
        if ( !is_file( $path)){
            $err = "BLOB_delete() - Could not find blob('$unique_name') in path('$path'), error.";
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // delete BLOB
        return unlink( $path);
    }
    
    /**
    * @desc This method returns a unique_blob_name.
    * @param mixed $data Data of blob as mixed (binary-proof).
    * @return string Returns a string containing blob's unique_name.
    */
    function BLOB_get_unique_name( $data){
        $unique_name = md5( microtime().$data);
        return $unique_name;
    }
    
    /**
    * @desc Retrieves size in bytes from given BLOB in given table.
    * @param string $table_name Tablename as string.
    * @param string $unique_name Unique name of BLOB as string.
    * @return int Returns the size of blob in bytes as int, or null on failure.
    */
    function BLOB_get_size( $table_name, $unique_name){
        // get path from core
        $path = $this->getPath() . '/' . $table_name .'.blob/'. $unique_name;
        // check if blob exists
        if ( !is_file( $path)){
            $err = "BLOB_get_size() - BLOB('$unique_name') could not be found using path('$path'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return null;
        }
        // get filesize of BLOB
        $size = filesize( $path);
        // MISSING CODE HERE: Size of BLOB-HEADER must be subtracted (once implemented)!!
        return $size;
    }
    
    function BLOB_compress( $data, $compression_type=null){}
    function BLOB_decompress( $data){}
    
    /**
    * @desc Adds a BLOB-HEADER to given data.
    * @param mixed $data Data/value of BLOB passed as mixed type.
    * @param string $unique_name Unique name of BLOB got once from BLOB_get_unique_name().
    * @return mixed Returns data/value of BLOB with prefixed BLOB-HEADER.
    */
    function BLOB_add_header( $data, $unique_name){
        /**
        * NOTE:
        *   BLOB-HEADERS have always the same size as defined
        *   in constant PDB_NUM_BLOBHEADER_LENGTH .
        */
        // build BLOB-HEADER
        $blob_size = strlen( $data);
        $blob_name = $unique_name;
        $blob_check   = chr(4) . chr(1) . chr(5) . chr(3) . '__PDBBLOB__';
        $blob_header  = $blob_check . PDB_CORE_VER . "\n";
        $blob_header .= "HSIZE:" . PDB_NUM_BLOBHEADER_LENGTH . "\n";
        $blob_header .= "DSIZE:$blob_size\n";
        $blob_header .= "BNAME:$blob_name\n";
        // pad new header to size of PDB_NUM_BLOBHEADER_LENGTH
        $blob_pchar   = chr(0);
        $header_size  = strlen( $blob_header);
        if ( $header_size > PDB_NUM_BLOBHEADER_LENGTH){
            $err = "BLOB_add_header() - Headersize('$header_size') exceeds PDB_NUM_BLOBHEADER_LENGTH('".PDB_NUM_BLOBHEADER_LENGTH."'), error.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return null;
        }
        $pad_string = "";
        $pad_length = PDB_NUM_BLOBHEADER_LENGTH - strlen( $blob_header);
        for ( $i=0; $i<$pad_length; $i++){
            $pad_string .= $blob_pchar;
        }
        $blob_header  = $blob_header . $pad_string;
        // add newly created BLOB-HEADER to data
        $data = $blob_header . $data;
        // return data (with BLOB-HEADER)
        return $data;
    }
    
    /**
    * @desc Removes a BLOB-HEADER from given data.
    * @param mixed $data Data/value of BLOB passed as mixed type.
    * @return mixed Returns data/value of BLOB without prefixed BLOB-HEADER.
    */
    function BLOB_remove_header( $data){
        /**
        * NOTE:
        *   BLOB-HEADERS have always the same size as defined
        *   in constant PDB_NUM_BLOBHEADER_LENGTH .
        */
        // check if passed data has a valid BLOB-HEADER
        $blob_check = chr(4) . chr(1) . chr(5) . chr(3) . '__PDBBLOB__';
        $check_segm = substr( $data, 0, strlen( $blob_check));
        if ( strcmp( $check_segm, $blob_check) !== 0){
            $blob_name = $this->BLOB_get_unique_name( $data);
            $err  = "BLOB_remove_header() - Invalid header found in BLOB('$blob_name'), error.\n";
            $err .= "Nothing was done on passed data.";
            $this->_put_message( 'pDB_ERROR', array(__FILE__,__LINE__), $err);
            return $data;
        }
        // remove BLOB-HEADER from data
        $data = substr( $data, PDB_NUM_BLOBHEADER_LENGTH);
        return $data;
    }
    
    // OLD BLOB HANDLING ------------------------------------------------------
    // THESE METHODS ARE NOT CALLED ANYMORE, THEY CAN BE REMOVED !
    // (checked by BZaminga on Wed Mar 10 19:19:06 2004)
    
    /**
    * @desc This method returns a unique_blob_name.
    * We're using md5 here to create some unique names from given data for our blob-references.
    * The hack with the microtime is essential to avoid a serious bug.
    * The bug can be caused when inserting 10 blobs with the same
    * data, resulting in having only one blob-file, what would be totally wrong!
    * @param mixed $data Data of blob as mixed (binary-proof).
    * @return string Returns a string containing a unique blob name.
    */
    function pDB_get_unique_blob_name( $data){
        $unique_name = md5( microtime().$data);
        return $unique_name;
    }
    
    
    /**
    * @desc This method reads a given blob (BinaryLargeOBject) and returns his contents.(FHNDL & LFIO)
    * @param string $table Name of table as string.
    * @param string $blob_unique_name Name of blob to retrieve.
    * @return mixed Returns a string containing the blob's value or FALSE on failure.
    */
    function pDB_read_blob( $table, $blob_unique_name){
        
        // if first-byte is a control-byte (FHNDL), chop it off
        $cb_check = substr($blob_unique_name, 0, 1);
        if ( $cb_check == "@"){
            // split of the @-char from blob_unique_name
            $blob_unique_name = substr_replace( $blob_unique_name, "", 0, 1);
        }
        
        // get Path to table's blob-folder
        $path = $this->getPath()."/".$table.".blob/".$blob_unique_name;
        
        // check if blob exists in FS
        if ( !is_file( $path)){
            $err = "pDB_read_blob() - Could not find blob('$blob_unique_name') in ('$path') !";
            $this->_put_message("pDB_ERROR", array(__FILE__,__LINE__), $err, 0);
            return false;
        }
        
        // open filepointer in binary-read-mode
        $fp_blob = fopen($path, "r+b", false);
        $blob_data = fread($fp_blob, filesize($path));
        fclose($fp_blob);
        
        return $blob_data;
    }
    
    /**
    * @desc Writes a blob to filesystem. (FHNDL & LFIO)
    * NOTE: The new strategy is to create for each blob-column an own folder.
    * @param string $table Name of table as string.
    * @param string $blob_unique_name Name for blob. Must be unique!
    * @param mixed $blob_value Value of blob. Can be of mixed type.
    * @return bool Returns TRUE on success, FALSE on failure.
    */
    function pDB_write_blob( $table, $blob_unique_name, $blob_value){
        // get path to table from core
        $path = $this->getPath()."/".$table.".blob/".$blob_unique_name;
        
        // create a new blob-file named $blob_unique_name.blob, or overwrite existent one
        $fp_blob = fopen( $path, "w+b", false);
        
        // write all given data there in
        $bytes_written = fputs( $fp_blob, $blob_value);
        
        // close filepointer
        fclose( $fp_blob);
        
        // change the permissions on this blob-file
        @chmod( $path, 0777);
        
        return true;
    }
    
    /**
    * @desc Reads a single line from php://stdin (console or terminal).
    * NOTE: This is gonna build his own pDB_READLINE-class.
    * @param void
    * @return string Returns a string.
    */
    function pDB_readline(){
        echo "> ";
        # 4092 max on win32 fopen
        $fp = fopen("php://stdin","r");
        $last_line = fgets($fp,4092);
        $last_line = rtrim($last_line);
        fclose($fp);
        return $last_line;
    }
    
    // ACCELERATOR ------------------------------------------
    /*
    NOTES:
    All accelerator methods should be very fast and slim. It would make no 
    sense to make killer heavy methods to gain performace. We will leave 
    out many check we would do normally on other safer methods.
    All this methods rely on CORE_DATA['DB_CURRENT'] which is the pointer or
    PRIMARY-value in ACC-Table. When a method calls ACC_pop(), all data that
    was previously stored for that hash will be deleted from ACC table.
    
    I think this implementation is wrong like it is now. The method used with
    the hash is not as planned on beginning. The original plan was maybe the 
    best also to work with. 
    The structure of the ACC table must be: {db.table}, key, value.
    Doing so thing will get much more transparent and debugable than before.
    */
    
    /**
     * @desc Stores a call and his results into accelerator.
     * @param string $table Name of table as string.
     * @param string $key Key or type of stored data.
     * @param mixed $value Value from above call.
     * @return bool Returns TRUE on success, FALSE on failure.
     */
    function ACC_put( $table, $key, $value){
        // get identifier for current action
        $identifier   = $this->ACC_id( $table);
        // if there are values stored for this table and key, delete them.
        $this->ACC_pop( $table, $key);
        // insert new action and his respective value.
        $ins          = $this->ACC->insertRow( array( $identifier, $key, $value));
        return $ins;
    }
    
    /**
     * @desc Retrieves the value from a previously stored call.
     * @param string $action Action or call as string.
     * @param mixed $returnval Returnvalue from above call.
     * @return mixed Returns the stored value when found or NULL.
     */
    function ACC_get( $table, $key){
         // get hash for current action
        $identifier   = $this->ACC_id( $table);
        // check if there is some data for this identifier
        $res          = $this->ACC->matchValue( 'id', $identifier);
        if ( $res->countRows() > 0){
            // look for a matching key in results
            $match       = $res->matchValue( 'key', $key);
            if ( $match->countRows() > 0){
                $row         = $match->getRow();
                $value       = $row[2];
                return $value;
            }
        }
        return null;
    }
    
    /**
     * @desc Deletes stored values from ACC.
     * @param string $table Name of table as string.
     * @param string $key Optional parameter key as string. 
     * NOTE: Leave this blank if you wanna pop all keys stored for given table.
     * @return bool Returns TRUE on success, FALSE on failure.
     */
    function ACC_pop( $table, $key=null){
        // get ACC_id for current action.
        $identifier    = $this->ACC_id( $table);
        // check if a key was passed.
        if ( $key !== null){
            //loop through rows looking for matches (id AND key MUST match here).
            $num_rows = $this->ACC->countRows();
            for ( $r=0; $r<$num_rows; $r++){
                $row  = $this->ACC->getRowByIndex( $r);
                $id   = $row[0];
                $vkey = $row[1];
                if ( $id == $identifier and $vkey == $key){
                    // this row matches both, delete it.
                    $del = $this->ACC->dropRowByIndex( $r);
                }
                return $del;
            }
        }else{
             // get a selection of rows for given identifier.
            $selection = $this->ACC->getSelection( 0, $identifier, 'EXACT');
            // drop all rows in current selection.
            foreach ( $selection as $rowID){
                $del   = $this->ACC->dropRowByIndex( 0);
            }
            return $del;
        }    
    }
    
    /**
     * @desc Returns the current ACC_id (�db.table�).
     * @param string $table Name of table as string.
     * @return string Returns an ACC_id as string.
     */
    function ACC_id( $table){
        $db          = $this->CORE_DATA['DB_CURRENT'];
        $id          = $db.".".$table;
        return $id;
    }
}
?>
