<?PHP
// This file is intended as example and for testing purposes of pDB_SQL_PARSER

// load parser-class
require_once( 'pDB_SQL_PARSER.php');
require_once( 'pDB_TABLE_OBJ.php');
require_once( 'pDB_CORE.php');


// read queries from 'common_queries.txt'
$queries     = file( 'common_queries.txt', false);
array_push( $queries, '');
$num_queries = count( $queries);
// trim linebreak from each query
for ( $q=0; $q<$num_queries; $q++){
	$queries[$q] = trim( $queries[$q]);
}


// show HTML-interface
if ( $_REQUEST['exec']) $CHECKED = "CHECKED";
else $CHECKED = "";
$query_sel   = "<form action='' method=POST>";
$query_sel  .= "<input type=checkbox name=exec value=1 $CHECKED>Execute code&nbsp;&nbsp;<br>";
// custom SQL-query (entry made by user)
$query_sel  .= "SQL-query:<br>";
$query_sel  .= "<textarea name=sqlAB cols=120 rows=2>";
$query_sel  .= @$_REQUEST['sqlAB'];
$query_sel  .= "</textarea><br>";
// premade queries
$query_sel  .= "Select query to test:<br>";
$query_sel  .= "<select name=QID>";
for ( $i=0; $i<$num_queries; $i++){
	$selected   = "";
	if ( @$_REQUEST['QID'] == $i) $selected = "SELECTED";
	$query_sel  .= "\t<option value='$i' $selected>$queries[$i];</option>";
}
$query_sel  .= "</select>";
$query_sel  .= "<input type=submit value=Go><br>";
$query_sel  .= "</form>";


// parse sqlAB entered by user (if given) or execute
// query specified by QID when sqlAB is empty
$parser     = new pDB_SQL_PARSER;
if ( strlen( @$_REQUEST['sqlAB']) > 0){
	$sqlAB  = $_REQUEST['sqlAB'];
	$code   = $parser->parse( $sqlAB);
}elseif ( isset( $_REQUEST['QID'])){
	$sqlAB  = $queries[$_REQUEST['QID']] . ';';
	$code   = $parser->parse( $sqlAB);
}


// show interface
echo "<h1>pDB_SQL_PARSER quick-test</h1>";
echo $query_sel;
echo "<p>";
echo "Statements:<br>";
echo "<textarea cols=120 rows=3>";
if ( $parser) print_r( $parser->STATEMENTS);
echo "</textarea>";
echo "</p>\n";
echo "<p>";
echo "Statements (tokenized):<br>";
echo "<textarea cols=120 rows=5>";
if ( $parser) print_r( $parser->TOKENS);
echo "</textarea>";
echo "</p>\n";


// generated code
echo "Generated code:<br>";
if ( $parser){
	$code = $parser->OUT;
	echo "<textarea cols=120 rows=3>";
	echo $code;
	echo "</textarea><br>";
}

// execute code
if ( @$_REQUEST['exec']){
	$PDB = new pDB_CORE();
	$PDB->pDB_init( 'pDB.conf.php');
	$PDB->pDB_login( 'root', 'pDB');
	$res = $PDB->pDB_exec( $code);
	if ( $res) {
		ob_start();
		echo "<p>";
		echo '<b>Result:</b><br>';
		echo "<textarea cols=120 rows=5>";
		print_r( $res);
		echo "</textarea>";
		echo "</p>\n";
		$out = ob_get_contents();
		#echo $out;
	}
}

// debug
echo "<p>Debug:<br>";
echo "<textarea cols=120 rows=5>";
if ( $parser){
	while( $error = $parser->err_get()){
		echo $error;
	}
}
if ( $PDB){
	while( $error = $PDB->_get_message()){
		echo $error;
	}
}
echo "</textarea>";
echo "</p>";
echo "<div align=right><small>pDB_SQL_PARSER_VERSION: " . PDB_SQL_PARSER_VERSION . "</small></div>";
echo "<div align=right><small>pDB_CORE_VERSION: " . PDB_CORE_VER . "</small></div>";
?>