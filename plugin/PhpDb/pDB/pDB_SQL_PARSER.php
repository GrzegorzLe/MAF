<?PHP
/**
* @desc This class is a wrapper for SQL-Statements to pDB-API-calls.
* The main principle followed here is Look-Ahead-Left_to_Right parsing.
* ControlStructures are in case of SQL : parenthesis '()'.
* @author Benny 'pokee' Zaminga <pDB@zaminga.cjb.net>
* @date Tuesday 14 October 2003
* @version 0.02 Thu Mar 25 20:49:18 2004
* @version 0.03 Sun Mar 28 18:48:11 2004
* @package pDB
*/


define( 'PDB_SQL_PARSER_NOTICE',  0);
define( 'PDB_SQL_PARSER_WARNING', 1);
define( 'PDB_SQL_PARSER_ERROR',   2);
define( 'PDB_SQL_PARSER_PANIC',   3);

define( 'PDB_SQL_PARSER_VERSION', '0.03');

class pDB_SQL_PARSER{
	
	// parsers in/out
	var $IN;					// incoming SQL-statement [SQL].
	var $OUT;					// generated pDB-API-call(s) [PHP].
	var $ERR = array();			// buffer for upcomming errors and warnings [text].
	var $ERR_LEVEL = 1;			// customize level of error-reporting. 0=verbose ... 4=quiet
	
	/**
	 * @desc Various flags and buffers needed while parsing.
	 */
	var $FLAG_P  = 0;			// parentesis-flag '(' increments, ')' decrements flag.
	var $FLAG_SQ = 0;			// single-quote flag '.
	var $FLAG_DQ = 0;			// double-quote flag ".
	var $ESCAPED = 0;			// set to 1 while parsing, when next char is escaped.

	/**
	 * @desc SQL-Vocabulary used and understood by this parser. 
	 * This vocabulary will be extended in future versions.
	 */
	var $SQL_ACTIONS = array( 'alter', 'create', 'delete', 'drop', 'insert', 'select', 'show', 'use');
	var $SQL_HWORDS  = array( 'by', 'from', 'into', 'where');
	var $SQL_TYPES   = array( 'int', 'string', 'blob');
	var $SQL_FUNCS   = array( 'count', 'distinct', 'limit', 'now', 'order');
	var $SQL_OPER    = array( '=', '!=', '+', '-', '*', '/', '<', '>', 'AND', 'OR');
	var $SQL_CONSTS  = array( 'asc', 'desc');
	
	/**
	 * @desc SQL-statements are stored here.
	 * Use _statementize() to populate this.
	 * @var array $STATEMENTS
	 */
	var $STATEMENTS = array();
	
	/**
	 * @desc SQL-statements are stored here in tokenized state.
	 * Use _tokenize() to populate this.
	 * @var array $TOKENS
	 */
	var $TOKENS     = array();
	
	/**
	 * @desc Actual Line of code (or number of statement) that is parsed.
	 * Must begin by 1 for meaningful error messages. 
	 * Line 0 wouldn't make any sense !
	 * @var int $LINE
	 */
	var $LINE = 1;
	
	/**
	 * @desc Prefix used for CodeGeneration. Use set_class_prefix() to set
	 * this to whatever needed.
	 */
	var $CLASS_PREFIX = '$this';
	
	/**
	* @desc Main method of this class. Parses given SQL-Statement(s) to a serie of pDB-API-calls.
	* NOTE: As side-effect of this method $this->OUT is populated with output from this method.
	* OUT is a buffer so code gets beffered there till you flush the buffer calling clear_buffers().
	* @param string $sql SQL-statement(s) to be converted passed as string.
	* @return mixed Generated code needed to perform all actions requested in passed SQL-state-
	* ment-block, or -1 when something wicked happens while parsing.
	*/
	function parse( $sql){
		$pos = 0;
		
		// copy incoming sql-block to IN ($sql is not used anymore)
		$this->IN = $sql;
		unset( $sql);
		
		// split-up SQL-block into statements
		if ( !$this->_statementize( $this->IN)){
			// push an error and abort parsing
			$err = "parse() - No statement to parse, giving up.";
			$this->err_push( 2, $err);
			return -1;
		}
		
		
		/**
		 * Loop thru all statements and wrap SQL to pDB_API-calls.
		 *
		 * The followed approach here is to lookup the requested action if it's
		 * known and call the appropriate macro in SQL-Parser-Class to let it 
		 * create the needed pDB-API-code. The requested action will be evaluated
		 * looking at the first (in some cases first two) tokens.
		 *
		 * We are gonna calulate the offset( position where we currently are 
		 * parsing in current statement) using the index of current token.
		 * $line holds the actual number of statements. This value by default
		 * begins by 1 (human readable form) and is incremented while pasing 
		 * through statements in current block.
		 */
		$offset = 0;
		$token  = 0;
		foreach ( $this->STATEMENTS as $statement){
			// tokenize current statement
			$tokens = $this->_tokenize( $statement);
			// check length of token-array, can not be 0
			if ( count( $tokens) < 1){
				$err = "parse() - Tokenize returned empty array, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			
			// analize which sql-action is requested by looking at first token
			$first_token = $tokens[0];
			if ( !is_string( $first_token)){
				$err = "parse() - First token is not a string, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			
			// check if requested action is known to parser, else abort
			if ( !$this->is_valid_action( $first_token)){
				$err = "parse() - Action('$first_token') is unknown, error near('$first_token') at offset(0), error.";
				$this->err_push( 2, $err);
				return -1;
			}

			// call requested macro-method to get generated code.
			$cg_macro = 'cg_' . strtolower( $first_token);
			if ( !in_array( $cg_macro, get_class_methods( $this))){
				$err = "parse() - CG-Macro('$cg_macro') is not callable, error!";
				$this->err_push( 3, $err);
			}
			$code = $this->$cg_macro();
			
			/* 
			Code should be linted in pDB_CORE against instance of it, 
			here it's simply impossible to do it. 
			*/
			
			// add generated code to OUT
			$this->OUT .= $code;
			
			// set line and offset
			$this->LINE++;
			$offset += strlen( $tokens[$token]) + 1;
		}
		// return generated code
		return $this->OUT;
	}
	
	
	/**
	 * @desc Checks if given action is valid and currently supported.
	 * @param string $action SQL-Action to check if valid as string.
	 * @return bool Returns TRUE when a known action is passed and FALSE when not valid or unknown.
	 */
	function is_valid_action( $action){
		if ( in_array( strtolower( $action), $this->SQL_ACTIONS)) return true;
		else return false;
	}
	
	/**
	 * @desc Checks if given HelperWord is valid and currently supported.
	 * @param string $action HelperWord to check if valid as string.
	 * @return bool Returns TRUE when a known action is passed and FALSE when not valid or unknown.
	 */
	function is_valid_hword( $hword){
		if ( in_array( strtolower( $hword), $this->SQL_HWORDS)) return true;
		else return false;
	}
	
	/**
	 * @desc Checks if given operator is valid and currently supported.
	 * @param string $operator Operator to check if valid as string.
	 * @return bool Returns TRUE when a known operator is passed and FALSE when not valid or unknown.
	 */
	function is_valid_operator(){
		if ( in_array( $operator, $this->SQL_OPER)) return true;
		else return false;
	}
	
	/**
	 * @desc Checks if given constant is valid and currently supported.
	 * @param string $operator Constant to check if valid as string.
	 * @return bool Returns TRUE when a known constant is passed and FALSE when not valid or unknown.
	 */
	function is_valid_constant(){
		if ( in_array( $constant, $this->SQL_CONSTS)) return true;
		else return false;
	}
	
	/**
	 * @desc Checks if given type is valid and currently supported.
	 * @param string $action type to check if valid as string.
	 * @return bool Returns TRUE when a known type is passed and FALSE when not valid or unknown.
	 */
	function is_valid_type( $type){
		if ( in_array( strtolower( $type), $this->SQL_TYPES)) return true;
		else return false;
	}
	
	/**
	 * @desc Checks if given function is valid and currently supported.
	 * @param string $action function to check if valid as string.
	 * @return bool Returns TRUE when a known function is passed and FALSE when not valid or unknown.
	 */
	function is_valid_function( $function){
		if ( in_array( strtolower( $function), $this->SQL_FUNCS)) return true;
		else return false;
	}
	
	/**
	* @desc This method does the following :
	* - split statements (if statement-block was passed) to single statements.
	* - tokenize each statement to tokens.
	* An idea would be to use php's tokenizer when available on host system.
	* There surely would be an enourmous amount in parsing speed, but
	* I don't know if the use of php's tokenizer would make any sense here.
	* Please check it out and report please (someone who has got php's tokenizer) !
	* @param string $sql SQL-Statement(s) to be tokenized.
	* @return bool Returns TRUE on success, FALSE on failure.
	*/
	function _statementize( $sql){
		// temp-buffer for storing single statements.
		$buffer = "";
		/**
		* We always assume that it's a block of statements that is passed,
		* also if there is effectively only one statement passed (most common
		* case). A block means multiple statements like in a script.
		* We do NOT assume that statements are delimeted from each other
		* by a linebreak! We'll carefully crawl the code for semicolons that
		* are not escaped and not quoted, and split the statements there.
		* All statements are stored in $this->STATEMENTS for further eval.
		*/
		
		/**
		* Split statements from each other.
		* A loop is produced that crawls thru the whole statement and searches
		* for semicolons that are NOT escaped and NOT quoted in any way !
		*/
		$n_chars = strlen( $this->IN);
		for ( $pos=0;$pos<$n_chars;$pos++){
			// get current char
			$c = substr( $this->IN, $pos, 1);
			// add current char to temp-buffer
			$buffer .= $c;
			// check for special chars : �;�, �'�, �"�, �\�, chr(10)
			if ( $c === ";"){
				// only count this semicolon when it's a valid statement-delimeter.
				if ( !$this->FLAG_P and !$this->FLAG_SQ and !$this->FLAG_DQ and !$this->ESCAPED){
					$this->err_push( 0, "| Semicolon at offset:$pos, end of statement.");
					// trim the buffer first
					$buffer = trim( $buffer);
					// move content of buffer to STATEMENTS omitting the semicolon.
					array_push( $this->STATEMENTS, substr( $buffer, 0, -1));
					// clear the buffer
					$buffer = "";
				}
			}elseif ( $c === "("){
				// increase parenthesis reference only when not quoted or escaped
				if ( !$this->FLAG_SQ and !$this->FLAG_DQ and !$this->ESCAPED){
					$this->err_push( 0, "| Parenthesis-open at offset:$pos.");
					$this->FLAG_P++;
				}
			}elseif ( $c === ")"){
				// decrease parenthesis reference only when not quoted or escaped
				if ( $this->FLAG_P and !$this->FLAG_SQ and !$this->FLAG_DQ and !$this->ESCAPED){
					$this->err_push( 0, "| Parenthesis-closed at offset:$pos.");
					$this->FLAG_P--;
					// parenthesis-flag can NEVER be negative
					if ( $this->FLAG_P < 0){
						$this->err_push( 2, "\nSYNTAX ERROR : near offset:$pos, unexpected ')' found near:['".trim( $buffer)."'], aborting !");
						return false;
					}
				}
			}elseif ( $c === "'"){
				$this->err_push( 0, "| Single-quote at offset:$pos.");
				// when we're between single-quotes and not escaped, set sq-flag to 0
				if ( $this->FLAG_SQ and !$this->ESCAPED){
					$this->FLAG_SQ = 0;
					// when we're NOT between sq and not escaped, set sq-flag to 1
				}elseif ( !$this->ESCAPED){
					$this->FLAG_SQ = 1;
				}
			}elseif ( $c === "\""){
				$this->err_push( 0, "| Double-quote at offset:$pos.");
				// when we're between double-quotes and not escaped, set dq-flag to 0
				if ( $this->FLAG_DQ and !$this->ESCAPED){
					$this->FLAG_DQ = 0;
					// when we're NOT between dq and not escaped, set dq-flag to 1
				}elseif ( !$this->ESCAPED){
					$this->FLAG_DQ = 1;
				}
			}elseif ( $c === chr(10)){
				// when a linebreak occurs increase the line-counter
				if ( !$this->ESCAPED)$this->LINE++;
				$this->err_push( 0, "| Linebreak at offset:$pos.");
			}elseif ( $c === '\\'){
				// escape-evaluation comes always last !
				// when a backslash occurs set the escape-flag for the next char
				$this->err_push( 0, "| Escape-char at offset:$pos.");
				if ( !$this->ESCAPED)	$this->ESCAPED = 1;
				else $this->ESCAPED = 0;
			}
		}
		
		
		// check if parenthesis-flag was left open -> throw syntax error
		if ( $this->FLAG_P){
			$this->err_push( 2, "SYNTAX ERROR - ".$this->FLAG_P." Open parenthesis at offset:$pos near ['".trim($buffer)."'], aborting  !");
			return false;
		}
		// check if single-quote was left open -> throw syntax error
		if ( $this->FLAG_SQ){
			$this->err_push( 2, "SYNTAX ERROR - Open single-quotes at offset:$pos near [".trim($buffer)."], aborting !");
			return false;
		}
		// check if double-quote was left open -> throw syntax error
		if ( $this->FLAG_DQ){
			$this->err_push( 2, "SYNTAX ERROR - Open double-quotes at offset:$pos near [".trim($buffer)."], aborting !");
			return false;
		}
		// check if buffer is not empty : last statement was not correctly closed by a semicolon ';'
		if ( strlen( $buffer)>1){
			$this->err_push( PDB_SQL_PARSER_ERROR, "SYNTAX ERROR on Line ".$this->LINE." - Statement not correctly terminated by semicolon at offset:$pos near [".trim($buffer)."] !");
			return false;
		}
		return true;
	}
	
	/**
	* @desc Tokenizes the given statement.
	* This method takes care of lists and expressions enclosed in paranthesis.
	* A whole list is treated as one token and stored in the return-array as ARRAY.
	* It's strongly recommended to check the type of a token when extracting it.
	* Normal tokens (like: SELECT, INSERT INTO, CREATE,...) are stored as strings.
	* Whitespace is trimmed from tokens.
	* @param string $statement SQL-Statement to split up into tokens.
	* @return array A numeric array containing the tokens split-up the correct way.
	*/
	function _tokenize( $statement){
		$buffer = "";
		$n_chars = strlen( $statement);
		$tokens = array();			// this is needed to store all tokens and lists, preserving the structure.
		$lists = array();			// this is needed for storing different list encountered.
		
		
		// reset all flags
		$this->ESCAPED = 0;
		$this->FLAG_P = 0;
		$this->FLAG_SQ = 0;
		$this->FLAG_DQ = 0;
		
		
		// initialize loop
		for ($pos=0;$pos<$n_chars;$pos++){
			// get current char that is evaluated
			$c = substr( $statement, $pos, 1);
			
			
			
			// check each char if it's a special one associated with an action or meaning in SQL.
			if ( $c === "("){
				// actual char is a open-parenthesis '(' (begin of a list when not escaped or in quotes)
				if ( !$this->FLAG_SQ and !$this->FLAG_DQ and !$this->ESCAPED){
					// increment p-flag and use it as index to create a list where elements of this list go into.
					// A simple example : two nested list could be easily separated by using the p-flag
					// as index into $lists[$this->FLAG_P]
					$this->FLAG_P++;
					// when a new list is opened $n_list[$this-YFLAG_P] should be reset to zero
					$n_list[$this->FLAG_P] = 0; 
					// create a new array where elements of this list are stored
					$lists[$this->FLAG_P] = array();
				}
			}elseif ( $c === ")"){
				// actual char is a close-parenthesis ')' (end of a list when not escaped or in quotes)
				if ( !$this->FLAG_P and !$this->ESCAPED){
					// the use of a ')' is not allowed here -> throw syntax error
					$this->err_push( PDB_SQL_PARSER_ERROR, "SYNTAX ERROR : Unexpected ')' at offset:$pos near [".$buffer."], aborting !");
					//return false;
				}elseif ( $this->FLAG_P and !$this->FLAG_SQ and !$this->FLAG_DQ){
					// BUGFIX : ommitt the ')' from buffer
					$c = null;
					// assign finished list to $tokens as next token
					array_push(  $tokens, $lists[$this->FLAG_P]);
					// decrement p-flag
					$this->FLAG_P--;
				}
			}elseif ( $c === " "){
				// actual char is a space (space is the most common delimeter, but not the only one)
				// we're not in a list -> !$this->FLAG_P
				// data stored in token goes to tokens as (string)token
				if ( !$this->FLAG_P and !$this->FLAG_SQ and !$this->FLAG_DQ){
					// trim the current buffer and treat it as (string)token
					$buffer = trim( $buffer);
					// store token
					array_push( $tokens, $buffer);
					// clean the buffer
					$buffer = "";
				}
			}elseif ( $c === ","){
				// actual char is a comma (comma is the delimeter for lists, the only one).
				// we assume to be in a list, not quoted and not escaped.
				// current data in buffer is one element of a list. push buffer into $lists[$this->FLAG_P].
				if ( $this->FLAG_P and !$this->FLAG_SQ and !$this->FLAG_DQ and !$this->ESCAPED){
					// increment $n_list
					$n_list[$this->FLAG_P]++;
				}
			}
			
			
			// check where to put currently buffered data.
			// we have to copy each char except comma and parenthesis (functional-chars) to $lists...
			// when we are in a list. destination address to list is defined by using $this-FLAG_P as index into $lists.
			if ( $this->FLAG_P){
				// strip '(' and ',' when NOT quoted NOR escaped
				# FIX: Sun Mar 28 18:21:19 2004
				#if( $c != '(' and $c != ')' and $c != ',' and !$this->FLAG_SQ and !$this->FLAG_DQ){
				if( $c != '(' and $c != ')' and $c != ',' and $c != '\'' and $c != '"' and !$this->FLAG_SQ and !$this->FLAG_DQ){
					// $n_lists is also an array where $this->FLAG_P is the index into it
					$lists[$this->FLAG_P][(int)$n_list[$this->FLAG_P]] .= $c;
				}else{
					$c = null;
				}
			}
			
			// add current char to generic token buffer when not in a list (between paranthesis)
			// there is need for a little bugfix : the first '(' is not
			if( !$this->FLAG_P) $buffer .= $c;
			
		}
		// check for left open flags, would probably mean a syntax error.
		// syntax errors should allready have been catched by previously using _sentenceize().
		if ( $this->FLAG_P){
			$this->err_push( PDB_SQL_PARSER_ERROR, "SYNTAX ERROR : Unclosed '(' at offset:$pos in [".$statement."] , aborting !");
			return false;
		}
		
		
		
		// cleanup buffer and store remaining data as last token
		// BUG with last empty token does not rely on this
		$buffer = trim( $buffer);
		if ( strlen( $buffer) > 0 and $buffer !== null  ){
			array_push( $tokens, $buffer);
			$buffer = "";
		}
		
		
		// BUGFIX : drop all tokens that contain nothing or only spaces and trim each token or element
		$temp_tokens = array();
		foreach ( $tokens as $token){
			// trim (string)tokens
			if ( is_string( $token)){
				$token = trim( $token);
			}
			// trim (array)tokens
			if( is_array( $token)){
				$temp_list = array();
				foreach ( $token as $element){
					$element = trim( $element);
					if ( $element != "" and $element != null){
						array_push( $temp_list, $element);
					}
				}
				$token = $temp_list;
			}
			// add token to tokens, only if not null or empty
			if ( $token != "" and $token != " " and $token != null){
				array_push( $temp_tokens, $token);
			}
		}
		$tokens = $temp_tokens;
		
		// add actual tokens to TOKENS-storage
		array_push( $this->TOKENS, $tokens);
		
		// return tokenized statement as array
		return $tokens;
	}
	
	/**
	* @desc Tries to evaluate a given expression and return a meaningful value.
	* @param array $exp Expression passed to be evaluated as array with on ore more elements.
	* Passed expression-array could look like: �array( '3', '+', '5')  => 8�.
	* @return mixed Returns the result from evaluation of current expression or NULL on failure.
	*/
	function _eval( $exp){
		// evaluate given expression. 
		// Must have form: �value  operator  value�* (expression can be longer)
		eval("\$res = (".implode( " ", $exp).");");
		return $res;
	}
	
	/**
	 * @desc Clears the internal buffers from previously parsed data, resets initial state of module.
	 * @param void
	 * @return void
	 */
	function clear_buffers(){
		// reset all flags
		$this->ESCAPED = 0;
		$this->FLAG_DQ = 0;
		$this->FLAG_P  = 0;
		$this->FLAG_SQ = 0;
		$this->LINE    = 0;
		// clear all buffers
		$this->IN      = '';
		$this->OUT     = '';
		$this->ERR     = array();
		$this->STATEMENTS = array();
		$this->TOKENS     = array();
		// push message
		$err = "_clear_buffers() - Cleared all buffers, reset initial state.";
		$this->err_push( 0, $err);
	}
	
	/**
	 * @desc Sets the internal class-prefix used while code-generation.
	 * @param string $prefix Prefix or classname as string in form �$class�.
	 * NOTE: If you pass null as prefix then the default value will be reset.
	 * @return void
	 */
	function set_class_prefix( $prefix){
		if ( $prefix != null and substr( $prefix, 0, 1) === '$'){
			$this->CLASS_PREFIX = $prefix;
		}else{
			$err = "set_class_prefix() - Prefix('$prefix') is invalid, resetting default('\$this').";
			$this->err_push( 1, $err);
			$this->CLASS_PREFIX = '$this';
		}
	}
	
#----- ERRORSTACK -----
	
	/**
	* @desc Pushes an error or warning onto parsers ERR_BUFFER.
	* @param int $err_type Type of error to raise. Possible constant are here :
	* - PDB_SQL_PARSER_NOTICE		0
	* - PDB_SQL_PARSER_WARNING		1
	* - PDB_SQL_PARSER_ERROR		2
	* - PDB_SQL_PARSER_PANIC		3
	* (To avoid to write the whole word, you can also specify the corresponding number.
	* Eg : PDB_SQL_PARSER_WARNING = 1)
	*
	* @param string @desc Short description of the error or warning.
	* @return bool Returns true on success and FALSE on failure.
	*/
	function err_push( $err_type, $desc){
		// check if error_type is defined and known
		if ( !is_int( $err_type) or $err_type < 0 or $err_type > 3){
			$this->err_push( 2, "Malformed error or warning, could not be pushed onto stack!");
			return false;
		}
		// check error-reporting level settings (when error-level is to low it's ignored)
		if ( $err_type < $this->ERR_LEVEL){
			// simply ignore this error or warning
			return true;
		}
		// formulate $error
		$errors = array( "PDB_SQL_PARSER_NOTICE", "PDB_SQL_PARSER_WARNING", "PDB_SQL_PARSER_ERROR", "PDB_SQL_PARSER_PANIC");
		$error = "".$errors[$err_type]." on Line(".(int)$this->LINE.") : $desc\n";
		// push error
		array_push( $this->ERR, $error);
		return true;
	}
	
	/**
	* @desc Retrieves the next occured error or warning beginning from first error.
	* NOTE : do not mix these two methods cause results get unpredicatble !
	* @param void
	* @return string Returns a string representation of next error.
	*/
	function err_get(){
		$error = array_shift( $this->ERR);
		return $error;
	}
	
	/**
	* @desc Retrieve the last occured error or warning.
	* @param void
	* @return string Returns a string representation of last error.
	*/
	function err_get_last(){
		$error = array_pop( $this->ERR);
		return $error;
	}
	
#----- CodeGenerator -----
	
	/* 
	CodeGenerator-Macros 
	All these methods have one thing common: they return pDB-API-code.
	The returned code (string) is NOT validated and not sure to work.
	Maybe it would be a good idea to introduce a lint-interface where
	code can be passed to for validation against the current instance of
	pDB that is running.
	
	Macros herein are:
	'alter', 'create', 'delete', 'drop', 'insert', 'select', 'show', 'use'
	*/
	
	/**
	 * @desc This method is not implemented yet. Further version of pDB_CORE
	 * should implement that functionality first.
	 */
	function cg_alter(){}
	
	/**
	 * @desc CodeGenerator-Macro for: �CREATE DATABASE|TABLE {dest}[{values-array}]�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_create(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Can be min 3, max 4.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens < 3 or $num_tokens > 4){
			$err = "cg_create() - Expected num of tokens(3 or 4), encountered num of tokens($num_tokens), error.";
			$err.= "\nMaybe you're using MySQL-syntax, which is not yet fully supported :'(";
			$this->err_push( 2, $err);
			return -1;
		}
		/*
		Look at second token. Can be: 
		- �DATABASE�.
		- �TABLE�.
		*/
		$what = $this->TOKENS[$line][1];
		$dest = $this->TOKENS[$line][2];
		if ( strtolower( $what) == 'database'){
			$code = '$res = ' . $this->CLASS_PREFIX . '->pDB_create_database( \'' . $dest . '\');' . chr(10);
		}elseif ( strtolower( $what) == 'table'){
			# MySQL-hack will follow soon, till now ONLY pDB-syntax is supported!
			# MySQL syntax is something like:
			# �create table table_02 (ID int(3),Name varchar(255), Data blob 5000000)�
			# pDB syntax is like:
			# �create table table_02 (ID int 3,Name varchar 255, Data blob)�
			// check given table structure's syntax
			$struct = @$this->TOKENS[$line][3];
			if ( !is_array( $struct)){
				$err = "cg_create() - Cannot create table with given structure, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			$num_cols   = count( $struct);
			$col_struct = "";
			for ( $c=0; $c<$num_cols; $c++){
				$col_struct .= '\'' . $struct[$c] . '\'';
				if ( $c < ( $num_cols - 1)) $col_struct .= ', ';
			}
			$code   = '$res = ' . $this->CLASS_PREFIX . '->pDB_create_table( \'' . $dest . '\', array( ' . $col_struct . '));' . chr(10);
		}else{
			$err = "cg_create() - Cannot create('$what'), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		$code .= 'return $res;' . chr(10);
		return $code;
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �DELETE FROM TABLE WHERE {sql-condition}�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_delete(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Must be 5 and more.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens < 5){
			$err = "cg_delete() - Expected num of tokens(5 or more), encountered num of tokens($num_tokens), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		/*
		Look at third token. Can be:
		- �TABLE�.
		*/
		$what      = $this->TOKENS[$line][2];
		/*
		Look at sql-condition commonly beginning after second token.
		*/
		#$condition        = array();
		$condition_string = "array(";
		for ( $c=3; $c<$num_tokens; $c++){
			#array_push( $condition, $this->TOKENS[$line][$c]);
			// check if current token is array, convert it to something meaningful (string)
			if ( is_array( $this->TOKENS[$line][$c])){
				$next_token = $this->TOKENS[$line][$c+1]; 
				$this->TOKENS[$line][$c] = join( ',', $this->TOKENS[$line][$c]);
			}
			// strip hwords from condition-string
			if ( !$this->is_valid_hword( $this->TOKENS[$line][$c])){
				$condition_string .= ' "' . $this->TOKENS[$line][$c] . '"';
				if ( $c < ($num_tokens-1)){
					$condition_string .= ",";
				}
			}
		}
		$condition_string .= ")";
		// generate code-block
		$code  = '$IDs = ' . $this->CLASS_PREFIX . '->pDB_get_selection( \'' . $what . '\', ' . $condition_string . ');' . chr(10);
		$code .= 'foreach ( $IDs as $id){' . chr(10);
		$code .= "\t" . '$res += ' . $this->CLASS_PREFIX . '->pDB_drop_row( \'' . $what . '\', $id);' . chr(10);
		$code .= '}' . chr(10);
		$code .= 'return $res;' . chr(10);
		// return generated code
		return $code;
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �DROP DATABASE|TABLE {dest}�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_drop(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Must be 3.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens <> 3){
			$err = "cg_drop() - Expected num of tokens(3), encountered num of tokens($num_tokens), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		/*
		Look at second token. Can be: 
		- �DATABASE�.
		- �TABLE�.
		*/
		$what = $this->TOKENS[$line][1];
		$dest = $this->TOKENS[$line][2];
		// check if dest is an expression and evaluate it
		if ( is_array( $dest)) $dest = $this->_eval( $dest);
		if ( strtolower( $what) == 'database'){
			$code = $this->CLASS_PREFIX . '->pDB_drop_database( \'' . $dest . '\');' . chr(10);
		}elseif ( strtolower( $what) == 'table'){
			$code = $this->CLASS_PREFIX . '->pDB_drop_table( \'' . $dest . '\');' . chr(10);
		}else{
			$err = "cg_drop() - Cannot drop $what('$dest'), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		return $code;
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �INSERT INTO TABLE {dest} (col1,col2) VALUES (type Name length,type Name length)�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_insert(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Must be 3.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens < 5){
			$err = "cg_insert() - Expected num of tokens(5 or more), encountered num of tokens($num_tokens), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		// second token MUST be hword �INTO�
		if ( !$this->is_valid_hword( $this->TOKENS[$line][1])){
			$err = "cg_insert() - Expected('INTO') near('" . substr( $this->STATEMENTS[$line], 0, 10) . "...'), found('".$this->TOKENS[$line][1]."') instead, error.";
			$this->err_push( 2, $err);
			return -1;
		}
		// third token is the tablename {dest}
		$dest   = $this->TOKENS[$line][2];
		// fourth token can be custom-list-of-columns OR simply the hword �VALUES�,
		// when no custom-list-of-columns (where to insert values to) is passed.
		$colset = $this->TOKENS[$line][3];
		if( is_array( $colset)){
			// check if array is empty, can not be empty
			if ( count( $colset) < 1){
				$err = "cg_insert() - ColumnSet can not be empty, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			// check next token. MUST be hword �VALUES�
			if ( strtolower( $this->TOKENS[$line][4]) != 'values'){
				$err = "cg_insert() - Expected('VALUES') after colset('" . $this->TOKENS[$line][3] . "...'), found('".$this->TOKENS[$line][4]."') instead, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			// set n_token to jump the next token
			$next_token = 5;
		}else{
			// when it's no colset then it MUST be hword �VALUES�
			if ( strtolower( $colset) != 'values'){
				$err = "cg_insert() - Expected('VALUES') after tablename('" . $this->TOKENS[$line][2] . "...'), found('".$this->TOKENS[$line][3]."') instead, error.";
				$this->err_push( 2, $err);
				return -1;
			}
			// set n_token to jump the next token
			$next_token = 4;
		}
		// next token MUST be a list of values to insert
		$values = $this->TOKENS[$line][$next_token];
		if ( !is_array( $values)){
			$err = "cg_insert() - Expected List containing values to insert after('".$this->TOKENS[$line][$next_token-1]."'), found('".$this->TOKENS[$line][$next_token]."') instead, error.";
			$this->err_push( 2, $err);
			return -1;
		}
		
		// no custom column-set was specified, build simple insert code and return.
		if ( !is_array( $colset)){
			$num_vals = count( $values);
			$simple_insert_array  = 'array( ';
			for ( $c=0; $c<$num_vals; $c++){
				$simple_insert_array .= '\'' . $values[$c] . '\'';
				if ( $c < ( $num_vals-1)){
					$simple_insert_array .= ', ';
				}
			}
			$simple_insert_array .= ")";
			$code = $this->CLASS_PREFIX . '->pDB_add_row( \''.$dest.'\', '.$simple_insert_array.');' . chr(10);
			return $code;
		}
		
		/* 
		Now comes a tricky passage:
		We have to generate some code that gets the keys from table and stores
		them to an associative array (key/value-pairs).
		Since it's allowed to declare a custom subset of coulmns, we have to:
		- ask for keys in given table PDB_ASSOC-style (only possible during runtime)
		- pad given values array to length of keys-array, filling gaps with nothing.
		*/
		$err = "CustomSet of columns feature is not implemented yet, coming soon... ;)";
		$this->err_push( 2, $err);
		return -1;
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �SELECT {what} FROM {dest}[ WHERE {where-expr}]�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 * NOTE: pDB_CORE::pDB_select() is too weak by now. I have to reinforce it first!
	 * It MUST be possible to request a custom set of columns from select().
	 */
	function cg_select(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Can be min 2, max 4.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens < 2){
			$err = "cg_show() - Expected num of tokens(4 and more), encountered num of tokens($num_tokens), error.";
			$err.= "\n\tMaybe you're using MySQL-syntax, which is not yet supported :'(";
			$this->err_push( 2, $err);
			return -1;
		}
		// check if second token is string or array
		$second_tok = $this->TOKENS[$line][1];
		if ( is_array( $second_tok)){
			// custom set of columns was requested
			# NOT YET REALIZABLE WITH CURRENT CORE
		}else{
			// single column or alias(*)
		}
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �SHOW DATABASES|TABLES|COLUMNS�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_show(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Can be min 2, max 4.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens != 2 and  $num_tokens != 4){
			$err = "cg_show() - Expected num of tokens(2 or 4), encountered num of tokens($num_tokens), error.";
			$err.= "\nMaybe you're using MySQL-syntax, which is not yet fully supported :'(";
			$this->err_push( 2, $err);
			return -1;
		}
		/*
		Look at second token. Can be: 
		- �DATABASES�.
		- �TABLES�.
		- �COLUMNS�.
		*/
		$what = $this->TOKENS[$line][1];
		if ( strtolower( $what) == 'databases'){
			$code = '$res = ' . $this->CLASS_PREFIX . '->pDB_show_databases();' . chr(10);
		}elseif ( strtolower( $what) == 'tables'){
			$code = '$res = ' . $this->CLASS_PREFIX . '->pDB_show_tables();' . chr(10);
		}elseif ( strtolower( $what) == 'columns'){
			$tablename = $this->TOKENS[$line][3];
			$code      = '$res = ' . $this->CLASS_PREFIX . '->pDB_get_keys( \''. $tablename .'\');' . chr(10);
		}else{
			$err = "cg_show() - Can not show �$what�, error.";
			$this->err_push( 2, $err);
			return -1;
		}
		$code .= 'return $res;' . chr(10);
		return $code;
	}
	
	/**
	 * @desc CodeGenerator-Macro for: �USE {db_name}�
	 * @param void
	 * @return string Generated pDB-API-code (not validated) or -1 on error.
	 */
	function cg_use(){
		// get current LINE or STATEMENT number
		$line = $this->LINE - 1;
		// count tokens. Can be max 2.
		$num_tokens = count( $this->TOKENS[$line]);
		if ( $num_tokens <> 2){
			$err = "cg_use() - Expected num of tokens(2), encountered num of tokens($num_tokens), error.";
			$this->err_push( 2, $err);
			return -1;
		}
		/*
		Look at second token. Can be: 
		- string name of database.
		- array expression resulting in name of database.
		*/
		$db_name = $this->TOKENS[$line][1];
		// check if it's an expression (BUGGY)
		if ( is_array( $db_name)){
			$db_name = $this->_eval( $db_name);
		}
		// create code
		$code = $this->CLASS_PREFIX . '->pDB_use_database( \'' . $db_name . '\');' . chr(10);
		return $code;
	}
}
?>