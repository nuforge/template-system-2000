<?PHP
class DB_Object {

	/*public function __construct($table=false)
	Passed Variables:
	db [DATABASE LINK] (Optional) - If dblink already created, pass the reference in the constructor.
	table [STRING] (Optional) - If the class name is not represented in the table name, pass the name in the constructor.
	
		
	Load the table's meta data for future use and reference.
	*/
	public function __construct($db=false,$table=false,$connection=false) {
		$this->setConnection($connection);
		$this->setDB($db);
		$this->setTable($table);
		$this->setMeta($this->db, $this->table);
		$this->initialize();
	}
	
	public function setConnection($connection=false) {
		if ($connection) {return $this->connection_info = $connection;} else {return $this->connection_info;}
	}
	
	/*public function initialize()

	Load the table's meta data for future use and reference. Used in extended classes mostly.
	*/
	
	public function initialize () {
		return NULL;
	}
	
	/*public function setDB($db=false)
	Passed Variables: 
	db [DATABASE LINK] (Optional) - dblink of open database connection.

	Set object's current database connection.
	RETURN: [DATABASE LINK]
	*/
	public function setDB ($db=false) {
		if ($db) {
			return $this->db = $db;
		} else {
			$this->db = DB_Connection::connect($this->connection_info);
			return $this->db;
		}
	}
	
	/*public function setTable($table=false)
	Passed Variables: 
	table [STRING] (Optional) - table name for function to affect. Overrides object set table
		
	Set object's current table reference.
	RETURN: Table Name
	*/
	public function setTable ($table=false) {
		if ($table) {return $this->table = $table;} else {return $this->table = get_class($this);}
	}
	
	/*public function setMeta($db=false,$table=false)
	Passed Variables: 
	db [DATABASE LINK] (Optional) - dblink of open database connection. Overrides object set db.
	table [STRING] (Optional) - table name for function to affect. Overrides object set table
		
	Set object's current table reference.
	RETURN: returns Meta Data
	*/
	public function setMeta ($db=false,$table=false) {
		if (!$table) {$table = $this->table;}
		if (!$db) {$db = $this->db;}
		return $this->meta = pg_meta_data($db, $table);
	}
	
	public function verifyRequired ($required, $values) {
		$msg = new messageHandler();
		foreach ($required as $k=>$v) {
			if (empty($values[$k])) { $msg->add('error','Please enter a value for \'' . $v . '\'');}
		}
		return $msg;
	}
	
	public function begin () {
		return @pg_query('BEGIN;');
	}
	
	public function rollback () {
		return @pg_query('ROLLBACK;');
	}
	
	public function commit () {
		return @pg_query('COMMIT;');
	}
	
	/*public function formatWhere($where,$andor='AND')
	Passed Variables: 
	where [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'where' statement.
		Keys of arrays are table columns,
		Values of arrays are values with which they should be compared
		LIMITATIONS: must be simple == commands.
	andor [STRING] (Optional) - With [array() where] determines if conditions are joined by 'AND's or 'OR's
		
	Formats and cleans passed values for use with postgres.
	RETURN: formated where statement.
	*/
	public function formatWhere ($where,$andor='AND') {
		if (is_string($where)) {return ' WHERE ' . $where;}
		$t_where = $this->pruneValues($where);
		foreach ($t_where as $k=>$v) {
			switch ($v) {
				case 'NULL':
					$a[] = $k . ' is ' . $v;
					break;
				case 'NOT NULL':
					$a[] = $k . ' is ' . $v;
					break;
				default:
					$t_default[$k] = $v;
					break;
			}
		}
		if(!empty($t_default)) {
			$t_default = $this->cleanValues($t_default);
			if(!empty($t_default)) {
				foreach ($t_default as $k=>$v) {
					$a[] = $k . ' = ' . $v;
				}
			}
		}
		if(!empty($a)) {
			return ' WHERE ' . implode (' ' . $andor . ' ', $a);	
		} else {
			return false;
		}
	}
	
	
	/*public function formatOrder($where,$andor='AND')
	Passed Variables: 
	order [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'order' statement.
		Keys of arrays are table columns,
		Values of arrays are either 'ASC' or 'DESC'
		
	Formats and cleans passed values for use with postgres.
	*/
	public function formatOrder ($order) {
		if (is_string($order)) {return ' ORDER BY ' . $this->escapeData($order);}
		foreach ($order as $k=>$v) {$a[] = $k . ' ' . $v;}
		return ' ORDER BY ' . implode (',', $a);
	}
	
	/*public function formatSet($set)
	Passed Variables: 
	set [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'set' statement in an UPDATE command.
		Keys of arrays are table columns,
		Values of arrays are values to which they should be set.
		
	Formats and cleans passed values for use with postgres.
	*/
	public function formatSet ($set) {
		if (is_string($set)) {return ' SET ' . $this->escapeData($set);}
		//$t_set = $this->cleanValues($this->pruneValues($set));
		if(!empty($set)) {
			foreach ($set as $k=>$v) {$a[] = $k . '=' . $v;}
		} else { return false;}
		return ' SET ' . implode (',', $a);
	}
	
	/*public function formatSet($set)
	Passed Variables: 
	set [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'set' statement in an UPDATE command.
		Keys of arrays are table columns,
		Values of arrays are values to which they should be set.
		
	Formats and cleans passed values for use with postgres.
	*/
	public function formatGroup ($group) {
		if (is_string($group)) {return ' GROUP BY ' . $this->escapeData($group);}
		$t_group = $this->cleanValues($group);
		return ' GROUP BY ' . implode (',', $t_group);
	}
	
	public function formatHaving ($having,$andor='AND') {
		if (is_string($having)) {return ' HAVING ' . $this->escapeData($having);}
		$t_where = $this->cleanValues($this->pruneValues($having));
		foreach ($t_having as $k=>$v) {$a[] = $k . ' = ' . $v;}
		return ' HAVING ' . implode (' ' . $andor . ' ', $a);	
	}
	
	
	public function bool2bool ($value) {
		if ($value == 'f'){
			return false;
		} elseif ($value == 't'){
			return true;
		} else {
			return NULL;
		}
	}
	
	/*public function getNext ($column, $table = false)
	Passed Variables: 
		column [STRING] - Field to increment;
		where [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'where' statement;
		table [STRING] (Optional) - Name of Table;
		
	Return: INTEGER - the next largest value in a table column
	*/
	public function getNext($column=false,$where=false,$table=false) {
		if (!$table) {$table = $this->table;}
		if (!$column) {$column = $this->primarykey;}
		$query = 'SELECT ((coalesce(max(' . $column . '),0)::INTEGER) +1) as max FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		$ret = pg_fetch_assoc(pg_query($query . ';'));
		return $ret['max'];		
	}
	
	public function getCount ($column=false,$where=false,$group=false,$table=false) {
		if (!$table) {$table = $this->table;}
		if (empty($column) && !is_array($this->primarykey)) {$column = $this->primarykey;} 
		elseif (empty($column) && is_array($this->primarykey)) {$column = 0;}
		$query = 'SELECT count(' . $column . ') as count FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($group) {$query .= $this->formatGroup($group);}
		$ret = pg_fetch_assoc(pg_query($query . ';'));
		return $ret['count'];		
	}
	
	public function getList ($order=false,$where=false,$limit=false,$offset=false,$group=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($group) {$query .= $this->formatGroup($group);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
		
	public function getSingleList ($column,$order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT ' . $column . ' FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);} else {$query .= ' ORDER BY ' . $this->primarykey;}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$res = @pg_query ($query . ';');
		while ($a = @pg_fetch_assoc($res)) {
			$arr[] = $a[$column];
		}
		return $arr;
	}
		
	public function getSimpleList ($column,$order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT ' . $this->primarykey . ', ' . $column . ' FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);} else {$query .= ' ORDER BY ' . $this->primarykey;}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		$res = @pg_query ($query . ';');
		while ($a = @pg_fetch_assoc($res)) {
			$arr[$a[$this->primarykey]] = $a[$column];
		}
		return $arr;
	}
	
	public function load ($where,$order=false,$table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($where)){$where = array($this->primarykey => $where);}
		$query = 'SELECT * FROM ' . $table;
		$query .= $this->formatWhere($where);		
		if ($order) {$query .= $this->formatOrder($order);}
		$query .= ' LIMIT 1 ';		
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
	public function getRandom ($where=false) {
		if (!$table) {$table = $this->table;}
		$count = $this->getCount(false,$where);
		$query = "SELECT * FROM " . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		$query .= ' LIMIT 1 ';
		$query .= ' OFFSET ' . mt_rand(0,($count-1)) . ' ';
		return @pg_fetch_assoc(@pg_query($query . ';'));
	}
	
	/*public function pruneValues ($values, $table = false)
	Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table
		
	Return: ARRAY - All array elements present in both the Values passed and the table meta data.
	*/
	public function pruneValues($values,$keys=true,$table=false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($values)) {return $values;}
		return ($keys) ? array_intersect_key($values, $this->meta) : array_intersect($values, array_keys($this->meta));
	}
		
	/*	
	public function cleanValues ($values, $table = false)
	Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table
		
	Return: ARRAY - Array containing SQL-ready values.
	*/
	public function cleanValues($values, $table = false) {
		if (!$table) {$table = $this->table;}
		if (!is_array($values)) {return $values;}
		return pg_convert($this->db, $table, $values);
	}
		
	
	public function validateValues ($values) {
		return $this->cleanValues($this->pruneValues($values));
	}
	
	/*
	public function insert ($values, $table = false)
	Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table
		
	Return: BOOLEAN - Success or failure of Postgres Query
	*/
	
	public function add ($values) {
		$values[$this->primarykey] = $this->getNext();
		return $this->insert($values);
	}
	
	public function insert($values, $table = false) {
		if (!$table) {$table = $this->table;}
		$t_values = $this->pruneValues($values);
		if (!empty($t_values)) {
			$query = "INSERT INTO $table (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',',$this->cleanValues($t_values)) . ")";
			return @pg_query($query . ';');
		}
		return false;
	}
	
	public function update($values,$where=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$t_values = $this->cleanValues($this->pruneValues($values));
		$query = "UPDATE " . $table;
		$query .= $this->formatSet($t_values);
		if ($where) {$query .= $this->formatWhere($where);}
		return @pg_query($query . ';');
	}
	
	
	public function escapeData ($data){
		if (ini_get('magic_quotes_gpc')) {$data = stripslashes($data);}
		return pg_escape_string($data);
	}
	
	public function paginate ($page=1,$show=25,$total=false,$where=false,$display=10,$column=false,$group=false) {
		if (!$total) {$total = $this->getCount($column,$where,$group); }
		$p['current'] = $page;
		$p['start']=(($page-1)*$show);
		$p['end'] = $p['start'] + $show;
		if ($p['end'] > $total) {$p['end'] = $total;}
		$p['last'] = ceil($total / $show);
		if ($page+1 <= $p['last']) {$p['next'] = $page+1; $p['lastshow'] = true;}
		if ($page-1 >= 1) {$p['previous'] = $page-1; $p['first'] = true;}
		$p['startshow'] = $p['start']+1;
		
		$range = floor($display/2);
		//If more than one page.
		if ($p['last'] > 1) {
			//If last page is less than the display count.
			if ($p['last'] < $display) {
				for ($a = 1; $a<=$p['last']; $a++) {
					$p['range'][] = $a;
					$p['lastshow'] = false;
				}
			//If last page is greater than display count.
			} else {
				//If the number of total pages is less than display count.
				if (($page + $range) < $display) {
					for ($a = 1; $a<= $display; $a++) {
						$p['range'][] = $a;
						$p['startshow'] = false;
					}
				
				//If the number of total pages is greater than display count.
				} else {
					//If the number of shown pages is greater than last page.
					if (($page + $display) > $p['last']) {
						for ($a = ($p['last'] - $display)+1; $a<=$p['last']; $a++) {
							$p['range'][] = $a;
							$p['lastshow'] = false;
						}
					//If the number of shown pages is less than last page.
					} else {
						$left = $page+$range;
						for ($a = ($page - $range); $a<=$left; $a++) {
							if ($a < 1) {
								$left++;
							} else {
								$p['range'][] = $a;
							}
						}
					}
				}
			}
		}
		return $p;
	}

}
?>