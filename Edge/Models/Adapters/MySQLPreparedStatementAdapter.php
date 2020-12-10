<?php
namespace Edge\Models\Adapters;

use Edge\Models\Record,
    Edge\Core\Edge;

class MySQLPreparedStatementAdapter extends MySQLAdapter {

    protected $params = [];
    public static $DEFAULT_TTL = 86400;

    public function executeQuery($sql){
        $db = $this->getDbConnection();
        return $db->prepareAndExecute($sql, $this->params);
    }

    protected function getCacheKey(){
        return md5(serialize([$this->fetchMode, $this->query, $this->params]));
    }

    public function in(array $args){
        $this->params = array_merge($this->params, array_values($args));
        return parent::in($args);
    }

    public function notIn(array $args){
        $this->params = array_merge($this->params, array_values($args));
        return parent::notIn($args);
    }

    public function reset(){
        parent::reset();
        $this->params = [];
    }

    /**
     * Save the object to the database
     * @param \Edge\Models\Record $entry
     */
    public function save(Record $entry){
        $db = Edge::app()->writedb;
        $this->onBeforeSave($entry);
        $data = $entry->getAttributes();
        $db->prepareAndExecute($this->getInsertQuery($data, $entry), array_values($data));
        $this->setAutoIncrement($entry);
    }

    /**
     * Construct the INSERT sql query
     * @param $data
     * @param \Edge\Models\Record $entry
     * @return string
     */
    protected function getInsertQuery($data, Record $entry) {
        $vars = array_keys($data);
        $q = "INSERT INTO ".$entry::getTable()." (";
        $q .= join(",", $vars).") VALUES(";
        $q .= join(",", array_fill(0, count($vars), '?')).")";
        return $q;
    }
    protected function clause($args, $var){
       parent::clause($args, $var);
       if(is_array($args)){
           $this->params = array_merge($this->params, array_values($args));
       }
       return $this;
    }

    /**
     * Join the conditions array to be used as a WHERE
     * clause in a SQL query
     * $object->delete(array(
            'conditions' => array(
               'id' => array(10,20),
               'lang' => 'uk'
           )
     * ));
     */
    public function joinConditions(array $conditions, $db, $clause='AND'){
        $data = array_map(function($k, $v) use ($db){
            if(is_array($v)){
                $vals = array();
                foreach($v as $kv){
                    $vals[] = sprintf("%s", '?');
                }
                return sprintf("%s in (%s)", $k, join(",", $vals));
            }
            return sprintf('%s = %s', $k, '?');
        }, array_keys($conditions), array_values($conditions));
        return join(" $clause ", $data);
    }

    /**
     * Delete object from the database
     * @param \Edge\Models\Record $entry
     * @param array $criteria
     */
    public function delete(Record $entry){
        $db = Edge::app()->writedb;
        $where = $this->getPkValues($entry);
        $sql = sprintf("DELETE FROM %s WHERE %s", $entry::getTable(), $this->joinConditions($where, $db));
        $db->prepareAndExecute($sql, array_values($where));
    }

    /**
     * Construct the UPDATE sql query and update the object
     * @param \Edge\Models\Record $entry
     */
    public function update(Record $entry){
        $pks = $this->getPkValues($entry);
        $data = array_diff_assoc($entry->getAttributes(), $pks);
        $db = Edge::app()->writedb;
        $k = array_keys($data);
        $v = array_values($data);
        $c = join(", ", array_map(function($k, $v) use ($db){
            return sprintf('%s=%s', $k, '?');
        }, $k, $v));
        $q = sprintf("UPDATE %s SET %s WHERE %s", $entry::getTable(), $c, $this->joinConditions($pks, $db));
        $db->prepareAndExecute($q, array_merge($v, array_values($pks)));
    }
}