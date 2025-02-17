<?PHP

class DB_Object_old {

    private $schemas = array('public');
    private $joinedTables = array();


    /* public function __construct($f_table=false)
      Passed Variables:
      db [DATABASE LINK] (Optional) - If dblink already created, pass the reference in the constructor.
      table [STRING] (Optional) - If the class name is not represented in the table name, pass the name in the constructor.


      Load the table's meta data for future use and reference.
     */

    public function __construct($fobj_db_connection=false, $f_table=false, $fa_connection_info=false) {
        DEFINE('JOIN_TYPE_LEFT_JOIN', 'LEFT JOIN');
        DEFINE('JOIN_TYPE_RIGHT_JOIN', 'RIGHT JOIN');
        DEFINE('JOIN_TYPE_JOIN', 'JOIN');
        DEFINE('DB_OBJECT_DEBUG_SESSION_NAME', 'debug_out_ip');

        $this->SELECT_STATMENTS['default']['queries']['*'] = false;

        $this->setSearchPath();
        $this->setConnection($fa_connection_info);
        $this->db = $this->setDBConnection($fobj_db_connection);
        $this->setTable($f_table);
        $this->setMeta($this->db_connection, $this->table);
        $this->initialize();
        $this->globalJoins();
    }

    /* public function initialize()

      Load the table's meta data for future use and reference. Used in extended classes mostly.
     */

    public function globalJoins() {
        return false;
    }
    public function initialize() {
        return NULL;
    }

    public function addSchema($f_new_schema, $f_set_search_path = true) {
        $f_new_schema = trim($f_new_schema);
        if (empty($f_new_schema)) {
            return false;
        }
        array_unshift($this->schemas, $f_new_schema);
        return ($f_set_search_path) ? $this->setSearchPath() : true;
    }

    public function setSchemas($fa_schemas) {
        $this->schemas = (is_array($fa_schemas)) ? $fa_schemas : array($fa_schemas);
        return true;
    }

    public function setSearchPath() {
        if ($this->schema != 'public') {
            $this->addSchema($this->schema, false);
        }
        //$this->debugOut(get_class($this) . ' - set search_path to ' . implode(',', $this->schemas) . ';');
        return @pg_query('set search_path to ' . implode(',', $this->schemas) . ';');
    }

    public function setConnection($fa_connection_info=false) {
        if ($fa_connection_info) {
            return $this->connection_info = $fa_connection_info;
        } else {
            return $this->connection_info;
        }
    }

    /* public function setDB($db=false)
      Passed Variables:
      db [DATABASE LINK] (Optional) - dblink of open database connection.

      Set object's current database connection.
      RETURN: [DATABASE LINK]
     */

    public function setDBConnection($fobj_database_connection=false) {
        if ($fobj_database_connection) {
            return $this->db_connection = $fobj_database_connection;
        } else {
            $this->db_connection = DB_Connection::connect($this->connection_info);
            return $this->db_connection;
        }
    }

    /* public function setTable($f_table=false)
      Passed Variables:
      table [STRING] (Optional) - table name for function to affect. Overrides object set table

      Set object's current table reference.
      RETURN: Table Name
     */

    public function setTable($f_table=false) {
        if ($f_table) {
            return $this->table = $f_table;
        } else {
            return $this->table = get_class($this);
        }
    }

    /* public function setMeta($db=false,$f_table=false)
      Passed Variables:
      db [DATABASE LINK] (Optional) - dblink of open database connection. Overrides object set db.
      table [STRING] (Optional) - table name for function to affect. Overrides object set table

      Set object's current table reference.
      RETURN: returns Meta Data
     */

    public function setMeta($fobj_database_connection=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!$fobj_database_connection) {
            $fobj_database_connection = $this->db_connection;
        }
        //$this->meta = @pg_meta_data($db,$f_table);
        //$this->debugOut($this->meta);


        $this->meta = $this->meta_data($f_table, $this->schema);
        //$this->debugOut( $this->meta_data($f_table, $this->schema));
        $this->meta_joined = $this->meta;
        return $this->meta;
    }

    function meta_data($f_table, $f_schema = 'public') {

        $f_schema = ($f_schema) ? $f_schema: array_shift($this->schemas);
        $result = pg_query_params("SELECT a.attname, a.attnum, t.typname, a.attlen, a.attnotNULL, a.atthasdef, a.attndims
        FROM pg_class as c, pg_attribute a, pg_type t, pg_namespace n
        WHERE a.attnum > 0
        AND a.attrelid = c.oid
        AND c.relname = $1
        AND a.atttypid = t.oid
        AND n.oid = c.relnamespace
        AND n.nspname = $2
        ORDER BY a.attnum", array($f_table, $f_schema));
        $fields = array();
        while ($row = pg_fetch_array($result)) {
            $fields[$row['attname']] = $row;
        }
        return $fields;
    }

    public function getTableName() {
        return $this->table;
    }

    public function getMetaData($f_get_joined=false) {
        return ($f_get_joined) ? $this->meta_joined : $this->meta;
    }

    public function getUsedClasses() {
        return $this->usedClasses;
    }

    public function loadClass($f_class_name) {
        if ($this->loadedClasses[$f_class_name]) {
            return $this->loadedClasses[$f_class_name];
        } else {
            return new $f_class_name;
        }
    }

    public function passClass($f_class_object) {
        $className = get_class($f_class_object);
        if (empty($this->loadedClasses[$className])) {
            $this->loadedClasses[$className] = $f_class_object;
        }
        return $this->loadedClasses[$className];
    }

    public function joinTable($f_class_name, $f_on=false, $f_join_type=JOIN_TYPE_LEFT_JOIN, $f_as=false) {
        $arName = (!$f_as) ? $f_class_name : $f_as;
        if (empty($this->joinedTables[$arName])) {
            $f_table = $this->loadClass($f_class_name);
            if (empty($f_table)) {
                return false;
            }
            $new_table['table_name'] = $f_class_name;
            $new_table['join_type'] = $f_join_type;
            $new_table['join_on'] = $f_on;
            $new_table['join_as'] = $f_as;
            $this->joinedTables[$arName] = $new_table;

            $this->meta_joined = $this->meta_joined + $f_table->getMetaData();
        }
        return $this->meta_joined;
    }

    public function checkIsJoined() {
        return count($this->joinedTables);
    }

    public function formatJoined() {
        $joinStatment = ' ';
        if (!empty($this->joinedTables)) {
            foreach ($this->joinedTables as $joinedTable) {
                $joinStatment .= ' ' . $joinedTable['join_type'] . ' ' . $joinedTable['table_name'] . ' ON (' . $joinedTable['join_on'] . ')';

                if ($joinedTable['join_as']) {
                    $joinStatment .= ' ' . $joinedTable['join_as'];
                }
            }
        }

        return $joinStatment . ' ';
    }

//******* QUERY FUNCTIONS

    public function load($f_where, $f_order=false, $f_table = false) {
        $f_where = (!is_array($f_where)) ? array($this->primarykey => $f_where) : $f_where;
        $query = $this->formatQuery('default', $f_order, $f_where , $f_table);
        return @pg_fetch_assoc(@pg_query($query));
    }


    public function getList($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {

        $query = $this->formatQuery('default', $f_order, $f_where, $f_limit, $f_offset, $f_table);

        return @pg_fetch_all(@pg_query($query));
    }



    public function getNext($f_column=false, $f_where=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!$f_column) {
            $f_column = $this->primarykey;
        }
        $query = 'SELECT ((coalesce(max(' . $f_column . '),0)::INTEGER) +1) as max FROM ' . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        $ret = pg_fetch_assoc(pg_query($query . ';'));
        return $ret['max'];
    }

    public function getCount($f_column=false, $f_where=false, $f_group=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (empty($f_column) && !is_array($this->primarykey)) {
            $f_column = $this->primarykey;
        } elseif (empty($f_column) && is_array($this->primarykey)) {
            $f_column = 0;
        }
        $query = 'SELECT count(' . $f_column . ') as count FROM ' . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        if ($f_group) {
            $query .= $this->formatGroup($f_group);
        }
        $ret = pg_fetch_assoc(pg_query($query . ';'));
        return $ret['count'];
    }


    public function getSingleList($f_column, $f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $query = 'SELECT ' . $f_column . ' FROM ' . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        if ($f_order) {
            $query .= $this->formatOrder($f_order);
        } else {
            $query .= ' ORDER BY ' . $this->primarykey;
        }
        if ($f_limit) {
            $query .= ' LIMIT ' . $f_limit . ' ';
        }
        if ($f_offset) {
            $query .= ' OFFSET ' . $f_offset . ' ';
        }
        $res = @pg_query($query . ';');
        while ($a = @pg_fetch_assoc($res)) {
            $arr[] = $a[$f_column];
        }
        return $arr;
    }

    public function getSimpleList($f_column, $f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $query = 'SELECT ' . $this->primarykey . ', ' . $f_column . ' FROM ' . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        if ($f_order) {
            $query .= $this->formatOrder($f_order);
        } else {
            $query .= ' ORDER BY ' . $this->primarykey;
        }
        if ($f_limit) {
            $query .= ' LIMIT ' . $f_limit . ' ';
        }
        if ($f_offset) {
            $query .= ' OFFSET ' . $f_offset . ' ';
        }
        $res = @pg_query($query . ';');
        while ($a = @pg_fetch_assoc($res)) {
            $arr[$a[$this->primarykey]] = $a[$f_column];
        }
        return $arr;
    }
    public function getRandom($f_where=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $count = $this->getCount(false, $f_where);
        $query = "SELECT * FROM " . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        $query .= ' LIMIT 1 ';
        $query .= ' OFFSET ' . mt_rand(0, ($count - 1)) . ' ';
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }

    public function verifyRequired($required, $fa_values) {
        $msg = new messageHandler();
        foreach ($required as $k => $v) {
            if (empty($fa_values[$k])) {
                $msg->add('error', 'Please enter a value for \'' . $v . '\'');
            }
        }
        return $msg;
    }

    public function begin() {
        return @pg_query('BEGIN;');
    }

    public function rollback() {
        return @pg_query('ROLLBACK;');
    }

    public function commit() {
        return @pg_query('COMMIT;');
    }

    public function formatQuery($f_select, $f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_table=false) {
        $f_table = ($f_table) ? $f_table : $this->table;

        $return_query = $this->formatSelect($f_select);
        $return_query .= $this->formatFrom($f_table);
        $return_query .= $this->formatWhere($f_where);
        $return_query .= $this->formatOrder($f_order);
        $return_query .= $this->formatLimit($f_limit);
        $return_query .= $this->formatOffset($f_offset);

        return $return_query . ';';
    }

    /* public function formatWhere($f_where,$f_and_or='AND')
      Passed Variables:
      where [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'where' statement.
      Keys of arrays are table columns,
      Values of arrays are values with which they should be compared
      LIMITATIONS: must be simple == commands.
      andor [STRING] (Optional) - With [array() where] determines if conditions are joined by 'AND's or 'OR's

      Formats and cleans passed values for use with postgres.
      RETURN: formated where statement.
     */

    public function formatWhere($f_where, $f_and_or='AND') {
        if ($f_where == false) {
            return false;
        }
        if (is_string($f_where)) {
            if (is_numeric($f_where)) {
                return ' WHERE ' . $this->primarykey . ' = ' . $f_where . ' ';
            } else {
                return ' WHERE ' . $f_where . ' ';
            }
        }


        foreach ($f_where as $k => $v) {
            if (is_numeric($k)) {
                $stringQueries[] = $v;
            }
        }

        $t_where = $this->pruneValues($f_where);
        if (!empty($t_where) && is_array($t_where)) {
            foreach ($t_where as $k => $v) {

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
        }
        if (!empty($t_default)) {
            $ret_default = $this->cleanValues($t_default);
            if (!empty($ret_default)) {
                foreach ($ret_default as $k => $v) {
                    $a[] = $k . ' = ' . $v;
                }
            }
        }
        if (!empty($stringQueries)) {
            foreach ($stringQueries as $k => $v) {
                $a[] = $v;
            }
        }
        if (!empty($a)) {
            return ' WHERE ' . implode(' ' . $f_and_or . ' ', $a) . ' ';
        } else {
            return false;
        }
    }

    /* public function formatOrder($f_where,$f_and_or='AND')
      Passed Variables:
      order [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'order' statement.
      Keys of arrays are table columns,
      Values of arrays are either 'ASC' or 'DESC'

      Formats and cleans passed values for use with postgres.
     */

    public function formatOrder($f_order) {

        if ($f_order == false) {
            return false;
        }

        if (is_string($f_order)) {
            return ' ORDER BY ' . $this->escapeData($f_order) . ' ';
        }
        foreach ($f_order as $k => $v) {
            $a[] = $k . ' ' . $v;
        }
        return ' ORDER BY ' . implode(',', $a) . ' ';
    }

    public function formatLimit($f_limit) {
        return ( $f_limit) ? ' LIMIT ' . $f_limit . ' ' : false;
    }

    public function formatOffset($f_offest) {
        return ( $f_offest) ? ' OFFSET ' . $f_offest . ' ' : false;
    }

    /* public function formatSet($f_set_values)
      Passed Variables:
      set [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'set' statement in an UPDATE command.
      Keys of arrays are table columns,
      Values of arrays are values to which they should be set.

      Formats and cleans passed values for use with postgres.
     */

    public function formatSet($f_set_values) {
        if (is_string($f_set_values)) {
            return ' SET ' . $this->escapeData($f_set_values) . ' ';
        }
        //$t_set = $this->cleanValues($this->pruneValues($f_set_values));
        if (!empty($f_set_values)) {
            foreach ($f_set_values as $k => $v) {
                $a[] = $k . '=' . $v;
            }
        } else {
            return false;
        }
        return ' SET ' . implode(',', $a) . ' ';
    }

    /* public function formatSet($f_set_values)
      Passed Variables:
      set [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'set' statement in an UPDATE command.
      Keys of arrays are table columns,
      Values of arrays are values to which they should be set.

      Formats and cleans passed values for use with postgres.
     */

    public function formatGroup($f_group) {
        if (is_string($f_group)) {
            return ' GROUP BY ' . $this->escapeData($f_group) . ' ';
        }
        $t_group = $this->cleanValues($f_group);
        return ' GROUP BY ' . implode(',', $t_group) . ' ';
    }

    public function formatHaving($f_having, $f_and_or='AND') {
        if (is_string($f_having)) {
            return ' HAVING ' . $this->escapeData($f_having) . ' ';
        }
        $t_where = $this->cleanValues($this->pruneValues($f_having));
        foreach ($t_having as $k => $v) {
            $a[] = $k . ' = ' . $v;
        }
        return ' HAVING ' . implode(' ' . $f_and_or . ' ', $a) . ' ';
    }

    public function formatFrom($f_table) {

        $query = ' FROM ' . $f_table . ' ';
        if ($this->checkIsJoined()) {
            $query .= $this->formatJoined();
        }
        return $query . ' ';
    }

    public function formatSelect($f_select) {
        if (is_array($f_select)) {
            return 'SELECT ' . implode($this->formatSelectArray($f_select), ', ') . ' ';
        }
        if (!empty($this->SELECT_STATMENTS[$f_select])) {
            $q = array();
            if ($this->SELECT_STATMENTS[$f_select]['queries']) {
                $q = array_merge($q, $this->formatSelectArray($this->SELECT_STATMENTS[$f_select]['queries']));
            }

            if ($this->SELECT_STATMENTS[$f_select]['booleans']) {
                $q = array_merge($q, $this->formatBooleanArray($this->SELECT_STATMENTS[$f_select]['booleans']));
            }
            return 'SELECT ' . implode($q, ', ') . ' ';
        }

        return 'SELECT ' . $this->escapeData($f_select) . ' ';
    }

    public function formatSelectArray($fa_select_array) {
        if (empty($fa_select_array)) {
            return false;
        }

        foreach ($fa_select_array as $query => $name) {
            $q[] = ($name) ? $query . ' as ' . $name : $query;
        }

        return $q;
    }

    public function formatBooleanArray($fa_boolean_array) {
        if (empty($fa_boolean_array)) {
            return false;
        }
        foreach ($fa_boolean_array as $k => $boolean_name) {
            $q[] = 'case when ' . $boolean_name . ' THEN 1 ELSE 0 END as ' . $boolean_name;
        }
        return $q;
    }

    public function bool2bool($f_value) {
        if ($f_value == 'f') {
            return false;
        } elseif ($f_value == 't') {
            return true;
        } else {
            return NULL;
        }
    }

    /* public function getNext ($f_column, $f_table = false)
      Passed Variables:
      column [STRING] - Field to increment;
      where [STRING OR ARRAY] - a string or array containing conditionals for a postgres 'where' statement;
      table [STRING] (Optional) - Name of Table;

      Return: INTEGER - the next largest value in a table column
     */

    /* public function pruneValues ($fa_values, $f_table = false)
      Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table

      Return: ARRAY - All array elements present in both the Values passed and the table meta data.
     */

    public function pruneValues($f_values_to_prune, $keys=true, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!is_array($f_values_to_prune)) {
            return $f_values_to_prune;
        }

        $prunedValues = ($keys) ? @array_intersect_key($f_values_to_prune, $this->meta) : @array_intersect($f_values_to_prune, array_keys($this->meta));

        $this->prunedValues[$f_table] = $prunedValues;
        if (!empty($this->joinedTables)) {
            foreach ($this->joinedTables as $k => $joinedTable) {
                $t_table = $this->loadClass($joinedTable['table_name']);
                $this->prunedValues[$joinedTable['table_name']] = $t_table->pruneValues($f_values_to_prune);
                $prunedValues = $prunedValues + $this->prunedValues[$joinedTable['table_name']];
            }
        }

        return $prunedValues;
    }
    /*
      public function cleanValues ($fa_values, $f_table = false)
      Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table

      Return: ARRAY - Array containing SQL-ready values.
     */

    public function cleanValues($fa_values_to_clean, $f_table = false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!is_array($fa_values_to_clean)) {
            return $fa_values_to_clean;
        }

        $cleanedValues = pg_convert($this->db_connection, $f_table, $this->prunedValues[$this->table], PGSQL_CONV_FORCE_NULL);
        $this->cleanedValues[$f_table] = $cleanedValues;
        if (!empty($this->joinedTables)) {
            foreach ($this->joinedTables as $joinedTable) {
                $t_table = $this->loadClass($joinedTable['table_name']);
                $t_values = $t_table->pruneValues($fa_values_to_clean);

                $cleanedValues = $cleanedValues + pg_convert($this->db_connection, $joinedTable['table_name'], $this->prunedValues[$joinedTable['table_name']], PGSQL_CONV_FORCE_NULL);
            }
        }
        return $cleanedValues;
    }

    
    //IF pg_convert doesn't work.
    public function convert_values () {

    }

    public function validateValues($fa_values_to_validate) {
        return $this->cleanValues($this->pruneValues($fa_values_to_validate));
    }

    /*
      public function insert ($fa_values, $f_table = false)
      Passed Variables: Values [ARRAY] - Array of Values, Table Name [STRING] (Optional) - Name of Table

      Return: BOOLEAN - Success or failure of Postgres Query
     */

    public function add($fa_values) {
        $fa_values[$this->primarykey] = $this->getNext();
        return $this->insert($fa_values);
    }

    public function insert($fa_values, $f_table = false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $t_values = $this->pruneValues($fa_values);
        if (!empty($t_values)) {
            $query = "INSERT INTO $f_table (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',', $this->cleanValues($t_values)) . ")";
            return @pg_query($query . ';');
        }
        return false;
    }

    public function insert_return($f_values, $f_sequence=false, $f_table = false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (empty($f_sequence)) {
            $f_sequence = $this->table . '_' . $this->primarykey . '_seq';
        }
        $t_values = $this->pruneValues($f_values);
        if (!empty($t_values)) {
            $query = "INSERT INTO " . $f_table . " (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',', $this->cleanValues($t_values)) . ")";

            if (!@pg_query($query . ';')) {
                return false;
            }
            $re = @pg_fetch_assoc(@pg_query("select currval('" . $f_sequence . "');"));
            return $re['currval'];
        }
        return false;
    }

    public function update($fa_values, $f_where=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $t_values = $this->cleanValues($this->pruneValues($fa_values));
        $query = "UPDATE " . $f_table;
        $query .= $this->formatSet($t_values);
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        return @pg_query($query . ';');
    }

    public function escapeData($f_string) {
        if (ini_get('magic_quotes_gpc')) {
            $f_string = stripslashes($f_string);
        }
        return pg_escape_string($f_string);
    }

    public function paginate($f_page_number=1, $f_show_per_page=25, $f_total_to_show=false, $f_where=false, $f_display_pages=10, $f_column=false, $f_group=false) {
        if (!$f_total_to_show) {
            $f_total_to_show = $this->getCount($f_column, $f_where, $f_group);
        }
        $p['current'] = $f_page_number;
        $p['start'] = (($f_page_number - 1) * $f_show_per_page);
        $p['end'] = $p['start'] + $f_show_per_page;
        if ($p['end'] > $f_total_to_show) {
            $p['end'] = $f_total_to_show;
        }
        $p['last'] = ceil($f_total_to_show / $f_show_per_page);
        if ($f_page_number + 1 <= $p['last']) {
            $p['next'] = $f_page_number + 1;
            $p['lastshow'] = true;
        }
        if ($f_page_number - 1 >= 1) {
            $p['previous'] = $f_page_number - 1;
            $p['first'] = true;
        }
        $p['startshow'] = $p['start'] + 1;

        $range = floor($f_display_pages / 2);
        //If more than one page.
        if ($p['last'] > 1) {
            //If last page is less than the display count.
            if ($p['last'] < $f_display_pages) {
                for ($a = 1; $a <= $p['last']; $a++) {
                    $p['range'][] = $a;
                    $p['lastshow'] = false;
                }
                //If last page is greater than display count.
            } else {
                //If the number of total pages is less than display count.
                if (($f_page_number + $range) < $f_display_pages) {
                    for ($a = 1; $a <= $f_display_pages; $a++) {
                        $p['range'][] = $a;
                        $p['startshow'] = false;
                    }

                    //If the number of total pages is greater than display count.
                } else {
                    //If the number of shown pages is greater than last page.
                    if (($f_page_number + $f_display_pages) > $p['last']) {
                        for ($a = ($p['last'] - $f_display_pages) + 1; $a <= $p['last']; $a++) {
                            $p['range'][] = $a;
                            $p['lastshow'] = false;
                        }
                        //If the number of shown pages is less than last page.
                    } else {
                        $left = $f_page_number + $range;
                        for ($a = ($f_page_number - $range); $a <= $left; $a++) {
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

    public function t_debugOut($variable_for_output, $explicit_table, $pre_tags=true, $f_show_per_page_to_ip=false) {

        if ($this->table == $explicit_table) {
            return $this->debugOut($variable_for_output, $pre_tags, $f_show_per_page_to_ip);
        }
        return false;
    }

    public function debugOut($variable_for_output, $pre_tags=true, $f_show_per_page_to_ip=false) {
        $outPut = $this->debugGet($variable_for_output, $pre_tags, $f_show_per_page_to_ip);
        if ($outPut) {
            echo $outPut;
            return $outOut;
        } else {
            return false;
        }
    }

    public function debugGet($variable_for_output, $pre_tags=true, $f_show_per_page_to_ip=false) {
        $f_show_per_pageToIPFinal = ($f_show_per_page_to_ip) ? $f_show_per_page_to_ip : $_SESSION[DB_OBJECT_DEBUG_SESSION_NAME];
        if (!empty($f_show_per_pageToIPFinal) && $_SERVER['REMOTE_ADDR'] == $f_show_per_pageToIPFinal) {
            $outPut = '';
            if ($pre_tags) {
                $outPut .= '<pre>';
            }
            if (is_array($variable_for_output) || $variable_for_output === false) {
                $outPut .= var_export($variable_for_output, true);
            } else {
                $outPut .= $variable_for_output;
            }
            if ($pre_tags) {
                $outPut .= '</pre>';
            }

            return $outPut;
        }
        return false;
    }



// ******** OLD FUNCTIONS



    public function getList_old($f_order=false, $f_where=false, $f_limit=false, $f_offset=false, $f_group=false, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        $query = 'SELECT * FROM ' . $f_table;
        if ($f_where) {
            $query .= $this->formatWhere($f_where);
        }
        if ($f_group) {
            $query .= $this->formatGroup($f_group);
        }
        if ($f_order) {
            $query .= $this->formatOrder($f_order);
        }
        if ($f_limit) {
            $query .= ' LIMIT ' . $f_limit . ' ';
        }
        if ($f_offset) {
            $query .= ' OFFSET ' . $f_offset . ' ';
        }
        return @pg_fetch_all(@pg_query($query . ';'));
    }



    public function pruneValues_old($f_values_to_prune, $keys=true, $f_table=false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!is_array($f_values_to_prune)) {
            return $f_values_to_prune;
        }
        return ($keys) ? @array_intersect_key($f_values_to_prune, $this->meta_joined) : @array_intersect($f_values_to_prune, array_keys($this->meta_joined));
    }


    public function cleanValues_old($fa_values_to_clean, $f_table = false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!is_array($fa_values_to_clean)) {
            return $fa_values_to_clean;
        }

        if (!empty($this->joinedTables)) {
            foreach ($this->joinedTables as $joinedTable) {
                $clean = @pg_convert($this->db_connection, $joinedTable, $fa_values_to_clean, PGSQL_CONV_FORCE_NULL);
            }
        }

        $clean = @pg_convert($this->db_connection, $f_table, $fa_values_to_clean, PGSQL_CONV_FORCE_NULL);



        return $clean;
    }



    public function load_old($f_where, $f_order=false, $f_table = false) {
        if (!$f_table) {
            $f_table = $this->table;
        }
        if (!is_array($f_where)) {
            $f_where = array($this->primarykey => $f_where);
        }
        $query = 'SELECT * FROM ' . $f_table;
        $query .= $this->formatWhere($f_where);
        if ($f_order) {
            $query .= $this->formatOrder($f_order);
        }
        $query .= ' LIMIT 1 ';
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }


}
?>