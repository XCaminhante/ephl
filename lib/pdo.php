<?php
//@+leo-ver=5-thin
//@+node:caminhante.20210904222845.5: * @file pdo.php
//@@first
//@@language php
//@@nocolor
/**
 * The easy-to-use PDO classes.
 *
 * @author Enisseo
 */
if (!@include_once('database.php')) {
  interface Database {};
  interface DatabaseTransaction extends Database {};
  interface DatabaseQuery {};
  interface DatabaseSelect extends DatabaseQuery {};
  interface DatabaseInsert extends DatabaseQuery {};
  interface DatabaseDelete extends DatabaseQuery {};
  interface DatabaseUpdate extends DatabaseQuery {};
}
//@+others
//@+node:caminhante.20210905140926.1: ** PdoDatabase
/**
 * The PDO base class.
 *
 * <p>This is an entry point for all PDO operations.</p>
 */
class PdoDatabase implements Database {
  protected $connection = null;
  protected $dns = null;
  protected $name = null;
  protected $password = null;
  //@+others
  //@+node:caminhante.20210905141018.1: *3* __construct
  public function __construct($dns = 'mysql:host=127.0.0.1', $name = 'root', $password = '') {
    $this->dns = $dns;
    $this->name = $name;
    $this->password = $password;
  }
  //@+node:caminhante.20210905141023.1: *3* connect
  public function connect() {
    if (is_null($this->connection)) {
      $this->connection = new PDO($this->dns, $this->name, $this->password);
    }
    return $this->connection;
  }
  //@+node:caminhante.20210905141028.1: *3* query
  /**
   * @return PdoQuery
   */
  public function query($query) {
    if ($query instanceof PdoQuery) {
      $query->setConnection($this->connect());
      return $query;
    }
    $connect = $this->connect();
    $q = new PdoQuery($connect);
    $q->is($query);
    return $q;
  }
  //@+node:caminhante.20210905141033.1: *3* select
  /**
   * @return PdoSelect
   */
  public function select() {
    $connect = $this->connect();
    $query = new PdoSelect($connect);
    if (func_num_args() > 0) {
      $args = func_get_args();
      $query->fields((count($args) == 1 && is_array($args[0]))? $args[0]: $args);
    }
    return $query;
  }
  //@+node:caminhante.20210905141038.1: *3* insert
  /**
   * @return PdoInsert
   */
  public function insert() {
    $connect = $this->connect();
    $query = new PdoInsert($connect);
    if (func_num_args() == 1) {
      $query->set(func_get_arg(0));
    }
    return $query;
  }
  //@+node:caminhante.20210905141042.1: *3* delete
  /**
   * @return PdoDelete
   */
  public function delete() {
    $connect = $this->connect();
    return new PdoDelete($connect);
  }
  //@+node:caminhante.20210905141048.1: *3* update
  /**
   * @return PdoUpdate
   */
  public function update() {
    $connect = $this->connect();
    $query = new PdoUpdate($connect);
    if (func_num_args() == 1) {
      $query->table(func_get_arg(0));
    }
    return $query;
  }
  //@+node:caminhante.20210905141057.1: *3* transaction
  /**
   * @return PdoTransaction
   */
  public function transaction() {
    $connect = $this->connect();
    return new PdoTransaction($connect);
  }
  //@-others
}
//@+node:caminhante.20210905140931.1: ** PdoTransaction
class PdoTransaction extends PdoDatabase implements DatabaseTransaction {
  public function __construct(&$connection) {
    $this->connection =& $connection;
  }
  public function start() {
    $this->connection->beginTransaction();
  }
  public function commit() {
    $this->connection->commit();
  }
  public function rollback() {
    $this->connection->rollBack();
  }
}
//@+node:caminhante.20210905140935.1: ** PdoQuery
class PdoQuery implements DatabaseQuery {
  protected $connection = null;
  protected $query = null;
  protected $parameters = array();
  //@+others
  //@+node:caminhante.20210905141110.1: *3* __construct
  public function __construct(&$connection) {
    $this->connection = $connection;
  }
  //@+node:caminhante.20210905141115.1: *3* setConnection
  public function setConnection(&$connection) {
    $this->connection = $connection;
  }
  //@+node:caminhante.20210905141119.1: *3* is
  /**
   * @return PdoQuery
   */
  public function is($query) {
    $this->query = $query;
    return $this;
  }
  //@+node:caminhante.20210905141126.1: *3* with
  /**
   * @return PdoQuery
   */
  public function with($param, $value = null) {
    if (!is_array($param)) {
      $param = array($param => $value);
    }
    $this->parameters = array_merge($this->parameters, $param);
    return $this;
  }
  //@+node:caminhante.20210905141130.1: *3* execute
  public function execute() {
    $statement = $this->connection->prepare($this->query);
    foreach ($this->parameters as $param => $value) {
      $statement->bindValue($param, $value);
    }
    if (!$statement->execute()) {
      trigger_error(join(', ', $statement->errorInfo()) . ' (' . $this->query . ')', E_USER_WARNING);
    }
    return $statement;
  }
  //@-others
}
//@+node:caminhante.20210905140951.1: ** PdoSelect
class PdoSelect extends PdoQuery implements DatabaseSelect {
  protected $fields = null;
  protected $from = null;
  protected $join = array();
  protected $where = array();
  protected $limit = 0;
  protected $offset = 0;
  protected $orders = array();
  protected $group = null;
  //@+others
  //@+node:caminhante.20210905141140.1: *3* fields
  /**
   * @return PdoSelect
   */
  public function fields() {
    $args = func_get_args();
    switch (count($args)) {
      case 0: $this->fields = null; break;
      case 1: $this->fields = is_array($args[0])? $args[0]: array($args[0]); break;
      default: $this->fields = $args; break;
    }
    return $this;
  }
  //@+node:caminhante.20210905141147.1: *3* addFields
  /**
   * @return PdoSelect
   */
  public function addFields() {
    $args = func_get_args();
    $this->fields = array_merge(is_null($this->fields)? array('*'): $this->fields, $args);
    return $this;
  }
  //@+node:caminhante.20210905141151.1: *3* from
  /**
   * @return PdoSelect
   */
  public function from($table) {
    $this->from = $table;
    return $this;
  }
  //@+node:caminhante.20210905141200.1: *3* leftJoin
  /**
   * @return PdoSelect
   */
  public function leftJoin($table, $where = null) {
    $this->join[] = array('LEFT JOIN', $table, $where);
    return $this;
  }
  //@+node:caminhante.20210905141211.1: *3* innerJoin
  /**
   * @return PdoSelect
   */
  public function innerJoin($table, $where = null) {
    $this->join[] = array('INNER JOIN', $table, $where);
    return $this;
  }
  //@+node:caminhante.20210905141220.1: *3* rightJoin
  /**
   * @return PdoSelect
   */
  public function rightJoin($table, $where = null) {
    $this->join[] = array('RIGHT JOIN', $table, $where);
    return $this;
  }
  //@+node:caminhante.20210905141232.1: *3* where
  /**
   * @return PdoSelect
   */
  public function where() {
    $args = func_get_args();
    $this->where = array_merge($this->where, (count($args) == 1 && is_array($args[0]))? $args[0]: $args);
    return $this;
  }
  //@+node:caminhante.20210905141239.1: *3* whereEquals
  /**
   * @return PdoSelect
   */
  public function whereEquals($fieldsValues) {
    $fieldsValues = array();
    if (func_num_args() == 2) {
      $fieldsValues = array(func_get_arg(0) => func_get_arg(1));
    }
    else {
      $fieldsValues = func_get_arg(0);
      if (is_string($fieldsValues)) {
        $this->where[] = $this->escapeField($fieldsValues) . ' != \'\'';
        return $this;
      }
    }
    foreach ($fieldsValues as $field => $value) {
      $fieldUniqId = ':' . $field . substr(md5(uniqid()), 0, 8);
      $this->where[] = $this->escapeField($field) . ' = ' . $fieldUniqId;
      $this->with($fieldUniqId, $value);
    }
    return $this;
  }
  //@+node:caminhante.20210905141245.1: *3* orderBy
  /**
   * @return PdoSelect
   */
  public function orderBy($field, $order = 'ASC') {
    $this->orders[$field] = strtoupper($order);
    return $this;
  }
  //@+node:caminhante.20210905141250.1: *3* groupBy
  /**
   * @return PdoSelect
   */
  public function groupBy($field, $having = '') {
    $this->group = is_array($field)? $field: array($field => $having);
    return $this;
  }
  //@+node:caminhante.20210905141256.1: *3* having
  /**
   * @return PdoSelect
   */
  public function having($having) {
    $this->having = $having;
    return $this;
  }
  //@+node:caminhante.20210905141301.1: *3* limit
  /**
   * @return PdoSelect
   */
  public function limit($limit, $offset = 0) {
    $this->limit = $limit;
    $this->offset = $offset;
    return $this;
  }
  //@+node:caminhante.20210905141309.1: *3* execute
  public function execute() {
    $fields = '*';
    if (!empty($this->fields)) {
      $fields = join(', ', $this->fields);
    }
    //@+others
    //@+node:caminhante.20210905141437.1: *4* joins
    $joins = array();
    foreach ($this->join as $joinData) {
      list($type, $table, $clauses) = $joinData;
      $joinOn = array();
      if (!empty($clauses)) {
        if (is_array($clauses)) {
          foreach ($clauses as $clause) {
            if (!empty($clause)) {
              $joinOn[] = $clause;
            }
          }
        }
        else {
          $joinOn[] = $clauses;
        }
      }
      $joins[] = sprintf('%s %s' . (!empty($joinOn)? ' ON %s': '%s'),
        $type, $table, join(' AND ', $joinOn));
    }
    //@+node:caminhante.20210905141442.1: *4* groups
    $groups = array();
    if (!empty($this->group)) {
      foreach ($this->group as $group => $having) {
        $groups[] = $group . (empty($having)? '': (' HAVING ' . $having));
      }
    }
    //@+node:caminhante.20210905141447.1: *4* orders
    $orders = array();
    if (!empty($this->orders)) {
      foreach ($this->orders as $field => $order) {
        if ($order == 'ASC' || $order == 'DESC') {
          $orders[] = $field . ' ' . $order;
        }
      }
    }
    //@+node:caminhante.20210905141451.1: *4* where
    $where = array();
    foreach ($this->where as $clause) {
      if (!empty($clause)) {
        $where[] = '(' . $clause . ')';
      }
    }
    //@-others
    $this->query = sprintf('SELECT ' . $fields . ' FROM %s' .
      (!empty($joins)? ' %s': '%s') .
      (!empty($where)? ' WHERE %s': '%s') .
      (!empty($groups)? ' GROUP BY %s': '%s') .
      (!empty($orders)? ' ORDER BY %s': '%s') .
      (!empty($this->limit) || !empty($this->offset)? ' LIMIT ' . $this->offset . ', ' . $this->limit: ''),
      $this->from,
      join(' ', $joins),
      join(' AND ', $where),
      join(', ', $groups),
      join(', ', $orders));
    return parent::execute();
  }
  //@+node:caminhante.20210905141319.1: *3* fetchFirst
  /**
   * @return array
   */
  public function fetchFirst($parameters = array()) {
    $record = $this->fetchArray($parameters, 1, 0);
    if (empty($record)) {
      return null;
    }
    return $record[0];
  }
  //@+node:caminhante.20210905141326.1: *3* fetchArray
  /**
   * @return array
   */
  public function fetchArray($parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = $res->fetchAll(PDO::FETCH_ASSOC);
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141332.1: *3* fetchLists
  /**
   * @return array
   */
  public function fetchLists($parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = $res->fetchAll(PDO::FETCH_NUM);
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141340.1: *3* fetchArrayByKey
  /**
   * @return array
   */
  public function fetchArrayByKey($keyField, $parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = array();
    while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
      $result[$arr[$keyField]] = $arr;
    }
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141345.1: *3* fetchBy
  /**
   * This is an alias of MysqlSelect::fetchArrayByKey
   * @return array
   */
  public function fetchBy($keyField, $parameters = array(), $max = 0, $from = 0) {
    return $this->fetchArrayByKey($keyField, $parameters = array(), $max, $from);
  }
  //@+node:caminhante.20210905141353.1: *3* fetchKeyValue
  /**
   * @return array
   */
  public function fetchKeyValue($parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = array();
    $niceKeyField = str_replace('`', '', preg_replace('/^(.*\s+AS\s+)/i', '', $this->fields[0]));
    $niceValueField = str_replace('`', '', preg_replace('/^(.*\s+AS\s+)/i', '', $this->fields[1]));
    while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
      $result[$arr[$niceKeyField]] = $arr[$niceValueField];
    }
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141400.1: *3* fetchArrayOf
  /**
   * @return array
   */
  public function fetchArrayOf($field, $parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = array();
    while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
      $result[] = $arr[$field];
    }
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141407.1: *3* fetchByGroup
  /**
   * @return array
   */
  public function fetchByGroup($keyField, $parameters = array(), $max = 0, $from = 0) {
    $this->parameters = array_merge($this->parameters, $parameters);
    $this->limit = intval($max);
    $this->offset = intval($from);
    $res = $this->execute();
    $result = array();
    while ($arr = $res->fetch(PDO::FETCH_ASSOC)) {
      if (!isset($result[$arr[$keyField]])) {
        $result[$arr[$keyField]] = array();
      }
      $result[$arr[$keyField]][] = $arr;
    }
    $res->closeCursor();
    return $result;
  }
  //@+node:caminhante.20210905141413.1: *3* fetchValue
  /**
   * @return array
   */
  public function fetchValue($parameters = array()) {
    $record = $this->fetchFirst($parameters);
    if (empty($record)) {
      return null;
    }
    return array_shift($record);
  }
  //@-others
}
//@+node:caminhante.20210905140956.1: ** PdoInsert
class PdoInsert extends PdoQuery implements DatabaseInsert {
  protected $table = null;
  protected $set = array();
  //@+others
  //@+node:caminhante.20210905141538.1: *3* into
  /**
   * @return PdoInsert
   */
  public function into($table) {
    $this->table = $table;
    return $this;
  }
  //@+node:caminhante.20210905141543.1: *3* set
  /**
   * @return PdoInsert
   */
  public function set($data) {
    $this->set = $data;
    return $this;
  }
  //@+node:caminhante.20210905141548.1: *3* execute
  public function execute() {
    $columns = array();
    $values = array();
    foreach ($this->set as $field => $value) {
      $fieldUniqId = ':' . $field . substr(md5(uniqid()), 0, 8);
      $columns[] = $field;
      $values[] = $fieldUniqId;
      $this->parameters[$fieldUniqId] = $value;
    }
    $this->query = sprintf('INSERT INTO %s (' .
      join(', ', $columns) . ') VALUES (' .
      join(', ', $values) . ')',
      $this->escapeTable($this->table));
    return parent::execute();
  }
  //@+node:caminhante.20210905141559.1: *3* executeAndGetInsertedId
  /**
   * @return int
   */
  public function executeAndGetInsertedId() {
    //TODO: Transaction
    $this->execute();
    return $this->connection->lastInsertId();
    //TODO: End transaction
  }
  //@-others
}
//@+node:caminhante.20210905141000.1: ** PdoDelete
class PdoDelete extends PdoQuery implements DatabaseDelete {
  protected $table = null;
  protected $where = array();
  //@+others
  //@+node:caminhante.20210905141609.1: *3* from
  /**
   * @return PdoDelete
   */
  public function from($table) {
    $this->table = $table;
    return $this;
  }
  //@+node:caminhante.20210905141614.1: *3* where
  /**
   * @return PdoDelete
   */
  public function where($where) {
    $this->where[] = $where;
    return $this;
  }
  //@+node:caminhante.20210905141619.1: *3* whereEquals
  /**
   * @return PdoDelete
   */
  public function whereEquals($fieldsValues) {
    foreach ($fieldsValues as $field => $value) {
      $fieldUniqId = ':' . $field . substr(md5(uniqid()), 0, 8);
      $this->where[] = $field . ' = ' . $fieldUniqId;
      $this->with($fieldUniqId, $value);
    }
    return $this;
  }
  //@+node:caminhante.20210905141623.1: *3* execute
  public function execute() {
    $where = array();
    foreach ($this->where as $clause) {
      if (!empty($clause)) {
        $where[] = '(' . $clause . ')';
      }
    }
    $where = join(' AND ', $where);
    $this->query = sprintf('DELETE FROM %s ' .
      (!empty($this->where)? ' WHERE %s': ''),
      $this->table, $where);
    return parent::execute();
  }
  //@-others
}
//@+node:caminhante.20210905141005.1: ** PdoUpdate
class PdoUpdate extends PdoQuery implements DatabaseUpdate {
  protected $table = null;
  protected $set = array();
  protected $where = array();
  //@+others
  //@+node:caminhante.20210905141637.1: *3* table
  /**
   * @return PdoUpdate
   */
  public function table($table) {
    $this->table = $table;
    return $this;
  }
  //@+node:caminhante.20210905141642.1: *3* where
  /**
   * @return PdoUpdate
   */
  public function where($where) {
    $this->where = $where;
    return $this;
  }
  //@+node:caminhante.20210905141648.1: *3* whereEquals
  /**
   * @return PdoUpdate
   */
  public function whereEquals($fieldsValues) {
    foreach ($fieldsValues as $field => $value) {
      $fieldUniqId = ':' . $field . substr(md5(uniqid()), 0, 8);
      $this->where[] = $field . ' = ' . $fieldUniqId;
      $this->with($fieldUniqId, $value);
    }
    return $this;
  }
  //@+node:caminhante.20210905141652.1: *3* set
  /**
   * @return PdoUpdate
   */
  public function set($data) {
    $this->set = $data;
    return $this;
  }
  //@+node:caminhante.20210905141657.1: *3* execute
  public function execute() {
    $set = array();
    if (is_array($set)) {
      foreach ($this->set as $field => $value) {
        $fieldUniqId = ':' . $field . substr(md5(uniqid()), 0, 8);
        $this->parameters[$fieldUniqId] = $value;
        $set[] = sprintf('%s = %s', $field, $fieldUniqId);
      }
    }
    else {
      $set[] = $set;
    }
    $where = array();
    foreach ($this->where as $clause) {
      if (!empty($clause)) {
        $where[] = '(' . $clause . ')';
      }
    }
    $where = join(' AND ', $where);
    $this->query = sprintf('UPDATE %s SET ' . join(', ', $set) .
      (!empty($this->where)? ' WHERE %s': ''),
      $this->table, $where);
    return parent::execute();
  }
  //@-others
}
//@-others
//@-leo
