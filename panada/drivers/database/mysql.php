<?php defined('THISPATH') or die('Can\'t access directly!');
/**
 * Panada MySQL Database Driver.
 *
 * @package	Panada
 * @subpackage	Driver
 * @author	Iskandar Soesman.
 * @since	Version 0.1
 */

class Drivers_database_mysql {
    
    protected $port = 3306;
    protected $column = '*';
    protected $distinct_ = false;
    protected $tables = array();
    protected $joins = null;
    protected $joins_type = null;
    protected $joins_on = array();
    protected $criteria = array();
    protected $group_by_ = null;
    protected $is_having = array();
    protected $limit_ = null;
    protected $offset_ = null;
    protected $order_by_ = null;
    protected $order_ = null;
    protected $is_quotes = true;
    private $link;
    private $connection;
    private $db_config;
    private $last_query;
    private $last_error;
    public $insert_id;
    public $client_flags = 0;
    public $new_link = true;
    public $persistent_connection = false;
    public $instantiate_class = 'stdClass';
    
    /**
     * Define all properties needed.
     * @return void
     */
    function __construct( $config_instance, $connection_name ){
	
	$this->db_config = $config_instance;
	$this->connection = $connection_name;
	
    }
    
    /**
     * Establish a new connection to mysql server
     *
     * @return string | boolean MySQL persistent link identifier on success, or FALSE on failure.
     */
    private function establish_connection(){
	
	$arguments = array(
			$this->db_config->host.':'.$this->port,
			$this->db_config->user,
			$this->db_config->password,
			$this->new_link,
			$this->client_flags
		    );
	
	$function = 'mysql_connect';
	
	if( $this->db_config->persistent ){
	    
	    $arguments = array(
			$this->db_config->host,
			$this->db_config->user,
			$this->db_config->password,
			$this->client_flags
		    );
	    
	    $function = 'mysql_pconnect';
	}
	
	return call_user_func_array($function, $arguments);
    }
    
    /**
     * Inital for all process
     *
     * @return void
     */
    private function init(){
	
	if( is_null($this->link) )
	    $this->link = $this->establish_connection();
        
        if ( ! $this->link ){
	    $this->error = new Library_error();
            $this->error->database('Unable connet to database in <strong>'.$this->connection.'</strong> connection.');
        }
        
        $collation_query = '';
        
        if ( ! empty($this->db_config->charset) ) {
            $collation_query = "SET NAMES '".$this->db_config->charset."'";
	    if ( ! empty($this->db_config->collate) )
                $collation_query .= " COLLATE '".$this->db_config->collate."'";
	}
	
        if ( ! empty($collation_query) )
            $this->query($collation_query);
        
        $this->select_db($this->db_config->database);
    }
    
    /**
     * Select the databse
     *
     * @return void
     */
    private function select_db($dbname){
	
	if( is_null($this->link) )
	    $this->init();
        
        if ( ! @mysql_select_db($dbname, $this->link) )
            Library_error::database('Unable to select database in <strong>'.$this->connection.'</strong> connection.');
        
    }
    
    /**
     * API for "SELECT ... " statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function select(){
        
	$column = func_get_args();
	
        if( ! empty($column) ){
	    $this->column = $column;
	    
	    if( is_array($column[0]) )
		$this->column = $column[0];
        }
        
        return $this;
    }
    
    /**
     * API for "... DISTINCT " statement.
     *
     * @return object
     */
    public function distinct(){
	
	$this->distinct_ = true;
	return $this;
    }
    
    /**
     * API for "...FROM ... " statement.
     *
     * @param string $table1, $table2 etc ...
     * @return object
     */
    public function from(){
	
	$tables = func_get_args();
	
	if( is_array($tables[0]) )
	    $tables = $tables[0];
	
	$this->tables = $tables;
	
	return $this;
    }
    
    /**
     * API for "... JOIN ..." statement.
     *
     * @param string $table Table to join
     * @param string $type Type of join: LEFT, RIGHT, INNER
     */
    public function join($table, $type = null){
	
	$this->joins = $table;
	$this->joins_type = $type;
	
	return $this;
    }
    
    /**
     * Create criteria condition. It use in on, where and having method
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    protected function create_criteria($column, $operator, $value, $separator){
	
	if( is_string($value) && $this->is_quotes ){
	    $value = $this->escape($value);
	    $value = " '$value'";
	}
	
	if( $operator == 'IN' )
	    if( is_array($value) )
		$value = "('".implode("', '", $value)."')";
	
	if( $operator == 'BETWEEN' )
	    $value = $value[0].' AND '.$value[1];
	
	$return = $column.' '.$operator.' '.$value;
	
	if($separator)
	    $return .= ' '.strtoupper($separator);
	
	return $return;
    }
    
    /**
     * API for "... JOIN ON..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    public function on($column, $operator, $value, $separator = false){
	
	$this->is_quotes = false;
	$this->joins_on[] = $this->create_criteria($column, $operator, $value, $separator);
	$this->is_quotes = true;
	
        return $this;
    }
    
    /**
     * API for "... WHERE ... " statement.
     *
     * @param string $column Column name
     * @param string $operator SQL operator string: =,<,>,<= dll
     * @param string $value Where value
     * @param string $separator Such as: AND, OR
     * @return object
     */
    public function where($column, $operator, $value, $separator = false){
	
	if( is_string($value) ){
	    
	    $value_arr = explode('.', $value);
	    if( count($value_arr) > 1)
		if( array_search($value_arr[0], $this->tables) !== false )
		    $this->is_quotes = false;
	}
	
	$this->criteria[] = $this->create_criteria($column, $operator, $value, $separator);
	$this->is_quotes = true;
	
        return $this;
    }
    
    /**
     * API for "... GROUP BY ... " statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function group_by(){
	
	$this->group_by_ = implode(', ', func_get_args());
	return $this;
    }
    
    /**
     * API for "... HAVING..." statement.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param mix $separator
     */
    public function having($column, $operator, $value, $separator = false){
	
	$this->is_having[] = $this->create_criteria($column, $operator, $value, $separator);
	
        return $this;
    }
    
    /**
     * API for "... ORDER BY..." statement.
     *
     * @param string $column1, $column2 etc ...
     * @return object
     */
    public function order_by($column, $order = null){
	
	$this->order_by_ = $column;
	$this->order_ = $order;
	
	return $this;
    }
    
    /**
     * API for "... LIMIT ..." statement.
     *
     * @param int
     * @param int Optional offset value
     * @return object
     */
    public function limit($limit, $offset = null){
	
	$this->limit_ = $limit;
	$this->offset_ = $offset;
	
	return $this;
    }
    
    /**
     * Build the SQL statement.
     *
     * @return string The complited SQL statement
     */
    public function _command(){
        
        $query = 'SELECT ';
	
	if($this->distinct_){
	    $query .= 'DISTINCT ';
	    $this->distinct_ = false;
	}
        
        $column = '*';
        
        if( is_array($this->column) ){
            $column = implode(', ', $this->column);
	    unset($this->column);
        }
        
        $query .= $column;
        
        if( ! empty($this->tables) ){
            $query .= ' FROM '.implode(', ', $this->tables);
	    unset($this->tables);
        }
	
	if( ! is_null($this->joins) ) {
	    
	    if( ! is_null($this->joins_type) ){
		$query .= ' '.strtoupper($this->joins_type);
		$this->joins_type = null;
	    }
	    
	    $query .= ' JOIN '.$this->joins;
	    
	    if( ! empty($this->joins_on) ){
		$query .= ' ON ('.implode(' ', $this->joins_on).')';
		unset($this->joins_on);
	    }
	    
	    $this->joins = null;
	}
	
	if( ! empty($this->criteria) ){
	    $cr = implode(' ', $this->criteria);
	    $query .= ' WHERE ' . rtrim($cr, 'AND');
	    unset($this->criteria);
	}
	
	if( ! is_null($this->group_by_) ){
	    $query .= ' GROUP BY '.$this->group_by_;
	    $this->group_by_ = null;
	}
	
	if( ! empty($this->is_having) ){
	    $query .= ' HAVING '.implode(' ', $this->is_having);
	    unset($this->is_having);
	}
	
	if( ! is_null($this->order_by_) ){
	    $query .= ' ORDER BY '.$this->order_by_.' '.strtoupper($this->order_);
	    $this->order_by_ = null;
	}
	
	
	if( ! is_null($this->limit_) ){
	    
	    $query .= ' LIMIT';
	    
	    if( ! is_null($this->offset_) ){
		$query .= ' '.$this->offset_.' ,';
		$this->offset_ = null;
	    }
	    
	    $query .= ' '.$this->limit_;
	    $this->limit_ = null;
	}
        
        return $query;
    }
    
    /**
     * Start transaction.
     *
     * @return void
     */
    public function begin(){
	$this->query("START TRANSACTION");
	$this->query("BEGIN");       
    }
    
    /**
     * Commit transaction.
     *
     * @return void
     */
    public function commit(){
	$this->query("COMMIT");
    }
    
    /**
     * Rollback transaction.
     *
     * @return void
     */
    public function rollback(){
	$this->query("ROLLBACK");
    }
    
    /**
     * Escape all unescaped string
     *
     * @param string $string
     * @return void
     */
    public function escape($string){
        
	if( is_null($this->link) )
	    $this->init();
	
        return mysql_real_escape_string($string, $this->link);
    }
    
    /**
     * Main function for querying to database
     *
     * @param $query The SQL querey statement
     * @return string|objet Return the resource id of query
     */
    public function query($sql){
	
	if( is_null($this->link) )
	    $this->init();
        
        $query = mysql_query($sql, $this->link);
        $this->last_query = $sql;
        
        if ( $this->last_error = mysql_error($this->link) ) {
            $this->print_error();
            return false;
        }
        
        return $query;
    }
    
    /**
     * Previously called get_results.
     * 
     * @since Version 0.3.1
     * @param mix
     * @param array
     * @param array
     * @return object
     */
    public function find_all( $table = false, $where = array(), $fields = array() ){
	
	if( ! $table )
	    return $this->results( $this->_command() );
	
	$column = '*';
	
	if( ! empty($fields) )
	    $column = $fields;
	
	$this->select($column)->from($table);
	
	if ( ! empty( $where ) ) {
	    
	    $separator = 'AND';
            foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $separator = false;
		
		$this->where($key, '=', $val, $separator);
            }
        }
	
        return $this->find_all();
    }
    
    /**
     * Previously called get_row.
     * 
     * @since Version 0.3.1
     * @param mix
     * @param array
     * @param array
     * @return object
     */
    public function find_one( $table = false, $where = array(), $fields = array() ){
	
	if( ! $table )
	    return $this->row( $this->_command() );
	
	$column = '*';
	
	if( ! empty($fields) )
	    $column = $fields;
	
	$this->select($column)->from($table);
	
	if ( ! empty( $where ) ) {
	    
	    $separator = 'AND';
	    foreach($where as $key => $val){
		
		if( end($where) == $val)
		    $separator = false;
		
		$this->where($key, '=', $val, $separator);
	    }
	}
	
	return $this->find_one();
	
    }
    
    /**
     * Get value directly from single field. Previusly called get_var().
     *
     * @since Version 0.3.1
     * @param string @query
     * @return string|int Depen on it record value.
     */
    public function find_var( $query = null ){
	
	if( is_null($query) )
	    $query = $this->_command();
	
        $result = $this->row($query);
        $key = array_keys(get_object_vars($result));
        
        return $result->$key[0];
    }
    
    /**
     * Get multiple records
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function results($query, $type = 'object'){
        
	if( is_null($query) )
	    $query = $this->_command();
	
        $result = $this->query($query);
        
        while ($row = @mysql_fetch_object($result, $this->instantiate_class)) {
            
            if($type == 'array')
                $return[] = (array) $row;
            else
                $return[] = $row;
        }
        
        @mysql_free_result($result);
        
        return @$return;
    }
    
    /**
     * Get single record
     *
     * @param string $query The sql query
     * @param string $type return data type option. the default is "object"
     */
    public function row($query, $type = 'object'){
	
	if( is_null($query) )
	    $query = $this->_command();
	
	if( is_null($this->link) )
	    $this->init();
        
        $result = $this->query($query);
        $return = mysql_fetch_object($result, $this->instantiate_class);
        
        if($type == 'array')
            return (array) $return;
        else
            return $return;
    }
    
    /**
     * Get value directly from single field
     *
     * @param string @query
     * @return string|int Depen on it record value.
     */
    public function get_var($query = null) {
        
	return $this->find_var($query);
    }
    
    /**
     * Abstraction to get single record
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_row( $table, $where = array(), $fields = array() ){
	
        return $this->find_one( $table, $where, $fields );
    }
    
    /**
     * Abstraction to get multple records
     *
     * @param string
     * @param array Default si null
     * @param array Default is all
     * @return object
     */
    public function get_results( $table, $where = array(), $fields = array() ){
	
	return $this->find_all( $table, $where, $fields );
    }
    
    /**
     * Abstraction for insert
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function insert($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("INSERT INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * Get the id form last insert
     *
     * @return int
     */
    public function insert_id(){
	
	return @mysql_insert_id($this->link);
    }
    
    /**
     * Abstraction for replace
     *
     * @param string $table
     * @param array $data
     * @return boolean
     */
    public function replace($table, $data = array()) {
        
        $fields = array_keys($data);
        
        foreach($data as $key => $val)
            $escaped_date[$key] = $this->escape($val);
        
        return $this->query("REPLACE INTO `$table` (`" . implode('`,`',$fields) . "`) VALUES ('".implode("','",$escaped_date)."')");
    }
    
    /**
     * Abstraction for update
     *
     * @param string $table
     * @param array $dat
     * @param array $where
     * @return boolean
     */
    public function update($table, $dat, $where = null){
        
        foreach($dat as $key => $val)
            $data[$key] = $this->escape($val);
        
        $bits = $wheres = array();
        foreach ( (array) array_keys($data) as $k )
            $bits[] = "`$k` = '$data[$k]'";
        
	if( ! empty($this->criteria) ){
	    $criteria = implode(' ', $this->criteria);
	    unset($this->criteria);
	}
	else if ( is_array( $where ) ){
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
	    
	    $criteria = implode( ' AND ', $wheres );
	}
        else {
            return false;
        }
	
        return $this->query( "UPDATE `$table` SET " . implode( ', ', $bits ) . ' WHERE ' . $criteria );
    }
    
    /**
     * Abstraction for delete
     *
     * @param string
     * @param array
     * @return boolean
     */
    public function delete( $table, $where = null ){
        
	if( ! empty($this->criteria) ){
	    $criteria = implode(' ', $this->criteria);
	    unset($this->criteria);
	}
	
        else if ( is_array( $where ) ){
            foreach ( $where as $c => $v )
                $wheres[] = "$c = '" . $this->escape( $v ) . "'";
	    
	    $criteria = implode( ' AND ', $wheres );
	}
	
        else{
            return false;
        }
        
        return $this->query( "DELETE FROM `$table` WHERE " . $criteria );
    }
    
    /**
     * Print the error at least to PHP error log file
     *
     * @return string
     */
    private function print_error() {
    
        if ( $caller = Library_error::get_caller(2) )
            $error_str = sprintf('Database error %1$s for query %2$s made by %3$s', $this->last_error, $this->last_query, $caller);
        else
            $error_str = sprintf('Database error %1$s for query %2$s', $this->last_error, $this->last_query);
    
        //write the error to log
        @error_log($error_str, 0);
    
        //Is error output turned on or not..
        if ( error_reporting() == 0 )
            return false;
    
        $str = htmlspecialchars($this->last_error, ENT_QUOTES);
        $query = htmlspecialchars($this->last_query, ENT_QUOTES);
    
        // If there is an error then take note of it
        Library_error::database($str.'<br /><b>Query</b>: '.$query.'<br /><b>Backtrace</b>: '.$caller);
    }
    
    /**
     * Get this db version
     *
     * @return void
     */
    public function version(){
	
	return $this->get_var("SELECT version() AS version");
    }
    
    /**
     * Close db connection
     *
     * @return void
     */
    public function close(){
	
	mysql_close($this->link);
    }
    
} // End Driver_mysql Class