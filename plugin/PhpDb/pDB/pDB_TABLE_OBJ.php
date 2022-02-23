<?PHP

define( 'PDB_SORT_ASC', 'PDB_SORT_ASC', true);
define( 'PDB_SORT_DSC', 'PDB_SORT_DSC', true);
define( 'PDB_SORT_REV', 'PDB_SORT_REV', true);

/**
* @desc Super-generic-table-object that can hold nearly ANY type of result-set.
* Has some builtin methods to manipulate and access the tabular data contained in it.
* There are methods provided to create a simple but effective persistent-storage by
* only using this objects capabilities to save/load to/from filesystem (miniDB), but
* the primary design goal was to use it as ResultSet or as an ´In-Memory´-database.
* If you think you need SQL to fulfill your work, then this table-object is surely not
* the right choice. Use pDB_CORE instead.
* pDB_TABLE_OBJ - welcome to the transitive pDatabase-table-object.
* @package pDB
* @author	BennyZaminga	<bzaminga@web.de>
* @version 0.21 - Sat Dec 27 15:40:57 2003
* @version 0.22 - Mon Dec 29 04:47:57 2003
* @version 0.23 - Sat Mar 06 19:47:00 2004
* @version 0.24 - Sat Mar 27 16:16:33 2004
* @version 0.25 - Sat Apr 24 05:10:00 2004
*/
class pDB_TABLE_OBJ{
	/**
	* @var string $TABLENAME
	* @access public
	*/
	var $TABLENAME;
	
	/**
	* @var array $_KEYS
	* @access private
	* NOTE: Should be accessed only using the appropriate methods!
	*/
	var $_KEYS   = array();
	var $_VALUES = array();
	var $_ATTR = array();
	var $_NUM_KEYS;
	var $_NUM_ROWS;
	
	/**
	* @var array $_PTR
	* @desc First ROW, first FIELD is default '$_PTR[0,0]'
	* @access private
	*/
	var $_PTR    = array(0,0);		// First ROW, first FIELD is default
	
	/**
	* @desc Supported sort-algorithms listed here
	*/
	var $_pDB_SORT_TYPES = array( "PDB_SORT_ASC", "PDB_SORT_REV", "PDB_SORT_DSC");
	
	/**
	* @var array __ID__ Array containing the indexes of current table.
	* ID's are physical row-indizes (like they appear in filesystem).
	* NOTE: 	This Buffer is only populated when table was loaded with
	* 			an appropriate method like pDB_select and when the table
	*			is also present in filesystem.
	*			VIRTUAL-TABLES or IN-MEMORY-TABLES do NOT have ID's !
	*/
	var $__ID__ = array();
	
	/**
	* @var array ERR Message or error-buffer of pDB_TABLE_OBJ.
	* NOTE:	Please use the appropriate methods to push/get
	* 			messages onto/from this buffer.
	* 			There's a possibility to automagically spool all
	* 			warnings/errors from this stack to core's stack.
	*/
	var $ERR = array();
	
	/**
	* @desc Initializes the table and does some internal work
	* @param string $tablename Name of table to initialize
	* @param array $keys Array containing the keys
	* @return boolean
	*/
	function initTable( $tablename, $keys){
		$this->TABLENAME = $tablename;
		
		// check given keys
		if ( !is_array( $keys)){
			echo "pDB_TABLE_OBJ::init_table() - Passed keys('$keys') are not of type array.";
			return false;
		}
		
		// prepare the table structure to hold data
		foreach ($keys as $key){
			// separate keys from attributes
			$both = explode(" ", $key);
			$key = $both[1];
			$attribute = $both[0];
			
			// push key to $this->_KEYS
			array_push($this->_KEYS, $key);
			
			// push attribute to $this->_ATTR
			array_push($this->_ATTR, $attribute);
			
			// prepare the values-slots
			$this->_VALUES[$key]=array();
		}
		
		// set $_NUM_KEYS & $_NUM_ROWS
		$this->_NUM_KEYS = count($this->_KEYS);
		$this->_NUM_ROWS = 0;
		
		return true;
	}
	
	
	/**
	* @desc Returns the name of this table as string.
	* @param void
	* @return string $tablename Returns tablename as string.
	*/
	function getTablename(){
		return $this->TABLENAME;
	}
	
	
	/**
	* @desc Inserts a new row into table, returns false if number of keys and values do not correspond or something went wrong.
	* @param array $values Array (numeric) containing the values to be inserted
	* @return boolean
	*/
	function insertRow( $values){
		
		// check if values are passed as array
		if ( !is_array( $values)){
			$err = "pDB_TABLE_OBJ::insertRow() - Given values('".var_dump( $values)."') are not of type array, error.";
			echo basename(__FILE__).", ".__LINE__." : $err\n";
			return false;
		}
		
		// check if number of values and keys correspond
		if ( count( $this->_KEYS) != count( $values)){
			echo basename(__FILE__).", ".__LINE__." : pDB_TABLE_OBJ::insert_row() - Number of keys(".count($this->_KEYS).") and values(".count($values).") do not match, error.\n";
			return false;
		}
		
		// push values into $this->_VALUES-slots
		$k=0;
		foreach ($values as $value){
			array_push($this->_VALUES[$this->_KEYS[(int)$k]],$value);
			$k++;
		}
		
		// Assign an __ID__ to newly inserted row
		array_push( $this->__ID__, $this->_NUM_ROWS);
		
		// increment NUM_ROWS
		$this->_NUM_ROWS++;
		
		return true;
	}
	
	/**
	* @desc Updates Row at given physical index
	* @param int Index pointing to the destination row
	* @param array Numeric array containing the values to insert
	* @return boolean TRUE on success, FALSE on failure or when row can't be found
	*/
	function updateRowByIndex($index,$values){
		
		// check if row exists
		if($index > ($this->countRows()-1)){
			echo basename(__FILE__).", ".__LINE__."updateRowByIndex() - Invalid row-index !\n";
			return false;
		}
		
		// check if values given are consistent
		$n_values = count($values);
		if(!is_array($values)or($n_values > $this->_NUM_KEYS)){
			echo basename(__FILE__).", ".__LINE__."updateRowByIndex() - Number of values given ($n_values) does not match _NUM_KEYS (".$this->_NUM_KEYS.")!\n";
			return false;
		}
		
		// update the values in pDB_TABLE_OBJ
		foreach($values as $value){
			$this->_VALUES[$this->_KEYS[(int)$k]][$index] = $value;
			$k++;
		}
		return true;
	}
	
	/**
	* @desc Delete row at given index.
	* @param int Index of row to delete as integer.
	* @return boolean
	*/
	function dropRowByIndex($index){
		
		// check index
		if ( $index = $this->check_index($index)){
			echo basename(__FILE__).", ".__LINE__."dropRowByIndex() - Invalid row-index !\n";
			return false;
		}
		
		// drop the row at given index from pDB_table_obj
		foreach ($this->_KEYS as $key){
			array_splice($this->_VALUES[$key],$index,1);
			array_splice($this->__ID__,$index,1);
		}
		
		return true;
	}
	
	/**
	* @desc Returns array containing the value of row at given index
	* @param int Index of row in table (negative index seeks from the end, -1 returns the last row in table).
	* @return array
	*/
	function getRowByIndex($index){
		// check the index
		$index = $this->check_index($index);
		
		$row = array();
		foreach ( $this->_KEYS as $key){
			array_push( $row, $this->_VALUES[$key][$index]);
			// pointer-stuff : increment field offset
			$this->_PTR[1]++;
		}
		// pointer-stuff : reset field offset
		$this->_PTR[1] = 0;
		return $row;
	}
	
	
	/**
	* @desc Returns array containing the next row pointed to by $this->_PTR, increments _PTR by one.
	* @param void
	* @return array Next row pointed to by pointer or false when there are no more rows.
	*/
	function getRow(){
		// check the pointer-position
		if ( $this->_PTR[0] >= $this->_NUM_ROWS){
			return false;
		}
		// get the next row
		$row = $this->getRowByIndex( $this->_PTR[0]);
		// move _PTR ->row_pos +1
		$this->_PTR[0]++;
		return $row;
	}
	
	/**
	* @desc Returns pDB_TABLE_OBJ containing the rows matching the given value in column key in a certain way
	* @param mixed $column can be int or string (NOTE:use '*' as wildcard)
	* @param mixed $value A value to look for in current field
	* @param [string] pDB_MATCH_TYPE  (eg: EXACT, LIKE), default is LIKE.
	* @return object Returns a pDB_TABLE_OBJ like a result-set
	*/
	function matchValue($column,$value){
		
		// return the the original table object if it matches all
		if(!$value || $value == " " || $value == "*")return $this;
		
		// check optional third param
		$match_type = @func_get_arg(2);
		if ( !$match_type){
			// if no match_type was passed, Like is used
			$match_type = "LIKE";
		}
		
		// create new table to return
		$table_obj = new pDB_TABLE_OBJ();
		$keys_new = array();
		$n = 0;
		foreach ($this->_KEYS as $key){
			array_push($keys_new, $this->_ATTR[(int)$n]." ".$key);
			$n++;
		}
		$table_obj->initTable("matchValue('".$column."', '".$value."', '".$match_type."')", $keys_new);
		
		$this->countRows();			// internally recount rows (performance ???)
		
		// column is INT
		if ( is_int( $column)&&$column<$this->_NUM_KEYS) $column = $this->_KEYS[$column];
		
		// column is STRING
		if ( !is_string( $column) && !array_search( $column,$this->_KEYS)){
			if  ($this['CONF_DATA']['DEBUG'])echo $column." is NOT a key of this table\n";
			return false;
		}
		
		// column was passed as "*" (means all columns have to be searched)
		if($column=="*"){
			foreach ($this->_KEYS as $key){
				for($n_row=0;$n_row<$this->_NUM_ROWS;$n_row++){
					if($match_type=="EXACT"){
						// look for a match (EXACT)
						if(strcmp($value, (string)$this->_VALUES[$key][$n_row]) == 0){
							// this row matches the given value (EXACT)
							$table_obj->insertRow($this->getRowByIndex($n_row));
							$table_obj->__ID__[$n_row] = $this->__ID__[$n_row];
						}
					}elseif ($match_type=="LIKE"){
						// look for a match (LIKE)
						if(eregi($value, (string)$this->_VALUES[$key][$n_row])){
							// this row matches the given value (LIKE)
							$table_obj->insertRow($this->getRowByIndex($n_row));
							$table_obj->__ID__[$n_row] = $this->__ID__[$n_row];
						}
					}
				}
			}
		}else{
			for ( $n_row=0; $n_row<$this->_NUM_ROWS; $n_row++){
				if ( $match_type=="EXACT"){
					// look for a match (EXACT)
					if( strcmp( $value, (string)$this->_VALUES[$column][$n_row]) == 0){
						// this row matches the given value (EXACT)
						$table_obj->insertRow( $this->getRowByIndex ($n_row));
						$table_obj->__ID__[$n_row] = $this->__ID__[$n_row];
					}
				}elseif ($match_type=="LIKE"){
					// look for a match (LIKE)
					if ( @eregi( $value, (string)$this->_VALUES[$column][$n_row])){
						// this row matches the given value, add to result-set
						$table_obj->insertRow( $this->getRowByIndex( $n_row));
						$table_obj->__ID__[$n_row] = $this->__ID__[$n_row];
					}
				}
			}
		}
		// ALLWAYS return a result-set also when empty
		return $table_obj;
	}
	
	
	
	/**
	* Returns the number of rows contained in table. (Side-Effect: updates $this->_NUM_ROWS)
	* @param void
	* @return int Returns number of rows in this table-object as integer.
	*/
	function countRows(){
		$this->_NUM_ROWS = count( $this->_VALUES[$this->_KEYS[0]]);
		return $this->_NUM_ROWS;
	}
	
	/**
	* Returns an indexed array containing information about this table
	* @param void
	* @return mixed Array containing all or only requested table-info
	*/
	function getTableInfo(){
		// dynamic number of parameters possible here
		$num_args = func_num_args();
		for($a=0;$a<$num_args;$a++){
			$args[$a] = func_get_arg($a);
		}
		// get all available info
		$info = array(
		"TABLENAME"=>$this->TABLENAME,
		"KEYS"=>$this->_KEYS,
		"NUM_KEYS"=>$this->_NUM_KEYS,
		"NUM_ROWS"=>$this->_NUM_ROWS
		);
		// return the asked value
		if($num_args<1){
			return $info;
		}else{
			foreach ($args as $arg)$info_custom[$arg]=$info[$arg];
			return $info_custom;
		}
	}
	
	
	# SORTING -----------------------------------------------------------------
	
	
	/**
	* @desc Sorts table using given $pDB_SORT_TYPE (see -> $this->_pDB_SORT_TYPES)
	* @param mixed $column The name or the index of the column to sort after as string or integer
	* @param string $pDB_sort_type The name of the used pDB_SORT_TYPE (sort-algorithm)
	* @return boolean TRUE on success , FALSE on failure
	*/
	function sortTableByColumn( $column, $pDB_SORT_TYPE){
		
		// check if column is INT, convert to column-name(string)
		if ( is_int( $column) && $column < $this->_NUM_KEYS){
			$column = $this->_KEYS[$column];
		}
		// check if given column exists in this table
		if ( !in_array( $column, $this->_KEYS)){
			echo "pDB_TBL_OBJ : Invalid column-name ('$column'), aborting sort.\n";
			return false;
		}
		// check if given $pDB_SORT_TYPE is valid
		if ( !in_array( $pDB_SORT_TYPE, $this->_pDB_SORT_TYPES)){
			echo "pDB_TBL_OBJ::sortTableByColumn() : Invalid sort-type ('$pDB_SORT_TYPE'), aborting sort !\n";
			return false;
		}
		// SORT : pDB_SORT_REVERSE
		if ( $pDB_SORT_TYPE == "PDB_SORT_REV"){
			$this->_sort_reverse();
		}
		// SORT : pDB_SORT_ASC
		if ( $pDB_SORT_TYPE == "PDB_SORT_ASC"){
			$this->_sort_asc( $column);
		}
		// SORT : pDB_SORT_DSC
		if ( $pDB_SORT_TYPE == "PDB_SORT_DSC"){
			// sort using : $pDB_SORT_TYPE=pDB_SORT_ASC
			$this->_sort_asc( $column);
			// sort reverse : $pDB_SORT_TYPE=pDB_SORT_REV
			$this->_sort_reverse();
		}
	}
	
	function _sort_reverse(){
		foreach ($this->_KEYS as $key){
			$this->_VALUES[$key] = array_reverse($this->_VALUES[$key]);
		}
	}
	
	// this method works well !! pokee@20030509
	function _sort_asc( $column){
		// sort the given column first
		natcasesort( $this->_VALUES[$column]);
		// get newly defined order of elements
		$new_indexes = array_keys( $this->_VALUES[$column]);
		// sort all the other columns with the new order ($new_indexes)
		$i = 0;
		foreach ($this->_KEYS as $key){
			// do not resort the allready sorted column
			if ( $key != $column){
				$temp_array = array();
				foreach ( $new_indexes as $index){
					$temp_array[$index] = $this->_VALUES[$key][$index];
					$i++;
				}
				// assign the sorted, temporary array back to values-slot
				$this->_VALUES[$key] = $temp_array;
			}
		}
	}
	
	/**
	* @desc Checks if a given index is valid.
	* NOTE: If a negative index is given, it's translated to a positive index.
	* @param int $index Index pointing to a row as integer.
	* @return mixed The translated positive index as integer or nothing on Failure
	*/
	function check_index($index){
		// check if index is in range
		if ( abs($index) > $this->_NUM_ROWS){
			echo ( basename(__FILE__).", ".__LINE__." : check_index() - Index('$index') is out of bounds.");
			return;
		}
		
		// check if negative index was given and translate it if needed
		if ( (int)$index<0){
			$index = $this->_NUM_ROWS + (int)$index;
			echo basename(__FILE__).", ".__LINE__." : Negative index was passed. Translated it's : $index";
		}
		return $index;
	}
	
	/**
	* @desc Rewinds the internal pointer($this->_PTR) to column 0, field 0.
	* NOTE: Omitt next two parameters to reset the pointer to 0,0 (begin of table).
	* @param void
	* @return bool Returnes TRUE on success, FALSE on failure.
	*/
	function rewind(){
		$this->_PTR[0] = 0;
		$this->_PTR[1] = 0;
	}
	
	# SERIALIZE FUNCS (Persistency) -------------------------------------------
	
	/**
	* @desc Saves this pDB_TABLE_OBJECT to a file.
	* NOTE: This method will overwrite an existing file without asking.
	* @param string $filepath Path to table as string.
	* @return bool Returns TRUE on success, FALSE on failure.
	*/
	function save( $filepath){
		// serialize this table
		$ser_table = serialize( $this);
		// write the serialized table
		$fp_ser = fopen( $filepath, "w");
		if ( $fp_ser){
			$bytes  = fwrite( $fp_ser, $ser_table);
			fclose( $fp_ser);
			return $bytes;
		}else{
			echo "pDB_TABLE_ERROR" . __FILE__ . __LINE__ . "save() - Could not write serialized table('$filepath') to file.";
			return false;
		}
	}
	
	/**
	* @desc Loads a previously saved pDB_TABLE_OBJECT from a file.
	* NOTE: By calling this method structure AND data of the table will be
	* overwritten with structure and data from serialized table_object.
	* @param string $filepath Path to table as string.
	* @return bool Returns TRUE on success or FALSE on failure.
	*/
	function load( $filepath){
		// check if file exists
		if ( !is_file($filepath)){
			echo "pDB_TABLE_ERROR" . __FILE__ . __LINE__ . "load() - Filepath('$filepath') to load table from could not be found!";
			return false;
		}
		// read the serialized file
		$fp_ser = fopen( $filepath, "r", false);
		if ( $fp_ser){
			$ser_table = fread( $fp_ser, filesize( $filepath));
			fclose( $fp_ser);
		}else{
			echo "pDB_TABLE_ERROR" . __FILE__ . __LINE__ . "load() - Error reading table('$filepath'), aborting!";
			return false;
		}
		// unserialize serialized table
		$table = unserialize( $ser_table);
		// overwrite current table's state with that from loaded table. 
		/*
		NOTE: This code is PHP5 save now. Simply doing ´$this = $table;´ would 
		      not work anymore under PHP5 as it did under PHP4.
		*/
		$class_vars = get_class_vars( 'pDB_TABLE_OBJ');
		$class_vars = array_keys( $class_vars);
		foreach ( $class_vars as $var){
			$this->$var = $table->$var;
		}
		
		/* This would be a customizable way.
		$TABLENAME;
		$_KEYS   = array();
		$_VALUES = array();
		$_ATTR = array();
		$_NUM_KEYS;
		$_NUM_ROWS;
		$_PTR    = array(0,0);
		$_pDB_SORT_TYPES = array( "PDB_SORT_ASC", "PDB_SORT_REV", "PDB_SORT_DSC");
		$__ID__ = array();
		$ERR = array();
		*/
		// return bool
		return true;
	}
	
	
	# OUTPUT ------------------------------------------------------------------
	
	
	/**
	* @desc Outputs this tables values as a formatted HTML-Table.
	* @param void
	* @return string Returns a htmlized representation of this table.
	*/
	function htmlize(){
		$out  = "\n<table class='pDB_TABLE' border='1;solid;black;'>";
		$out .= "\n<tr class='pDB_TR'>";
		$out .= "<th class='pDB_TH' colspan=".$this->_NUM_ROWS.">";
		$out .= $this->TABLENAME."</th></tr>";
		$out .= "\n<tr class='pDB_TR'>";
		for ( $c=0; $c < count( $this->_KEYS); $c++){
			$out .= "<th class='pDB_TH'><b>";
			$out .= $this->_KEYS[$c];
			$out .= "</b></th>";
		}
		$out .= "</tr>";
		$num_rows  = $this->countRows();
		// PART-OF-FIX
		$vals_copy = $this->_VALUES;
		// --
		for ( $r=0; $r < $num_rows; $r++){
			$out .= "\n\t<tr class='pDB_TR'>";
			foreach ( $this->_KEYS as $key){
				// BUG: This consumes the original _VALUES-set what is definitely wrong!
				// 		This will cause the loss of ANY data in this object !!!
				// SOLUTION: We make a copy of the (can be huge!) _VALUES-block and consume
				//			 that values by htmlizeing them in the order they were
				// 			 previously sorted ;)
				$field = array_shift( $vals_copy[$key]);
				$out  .= "\n\t\t<td class='pDB_TD' align=right valign=top>&nbsp;$field&nbsp;</td>";
			}
			$out .= "\n\t</tr>";
		}
		$out .= "\n</table>";
		return $out;
	}
	
	/** UNFINISHED
	* @desc Returns a collection of RowID's that match a certain value as array.
	* @param int $column ONLY INT IS CURRENTLY SUPPORTED, SHOULD BE EXTENDED TO STRINGS TOO.
	* @param mixed $value
	* @param string $match_type
	* @return array Returns a numeric array containing RowID (int).
	*/
	function getSelection( $column, $value, $match_type='EXACT'){
		$ret    = array();
		// check all rows for matches
		for ( $i=0; $i<$this->countRows(); $i++){
			$row   = $this->getRowByIndex( $i);
			$field = $row[$column];
			// look at given field for a match in given MATCH_TYPE
			if ( $match_type=="EXACT"){
				// look for a match (EXACT)
				if( strcmp( $value, (string)$field) == 0){
					// this row matches the given value (EXACT)
					array_push( $ret, $i);
				}
			}elseif ($match_type=="LIKE"){
				// look for a match (LIKE)
				if ( @eregi( $value, (string)$field)){
					// this row matches the given value, add to result-set
					array_push( $ret, $i);
				}
			}
		}
		return $ret;
	}
	
}
?>
