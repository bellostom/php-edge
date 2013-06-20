<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 18/4/2013
 *
 */
namespace Edge\Models\Adapters;

use Edge\Models\ActiveRecord,
    Edge\Core\Edge;

class MySQLAdapter implements AdapterInterface{

    /**
     * Normalize select attributes in order to build the
     * SELECT query. Valid examples are
     * Record::find(1)
     * Record::find("all", array(
        'conditions' => array("name" => "English"),
     *  'order' => array("name DESC"),
     *  'limit' => 10,
     *  'offset' => 0
     * ))
     * Record::find("last")
     * Record::find("first")
     * Record::find(array(
        "id" => array(2,4)
     * ))
     * Record::find(array("id"=>array("in" => array(1,2)), "age"=>array("gt"=>30), "name" => "John"), array(
        'order' => array("name desc"),
        'limit' => 10,
        'offset' => 0
    ));
     * @param array $options
     * @param $class
     */
    public function find(array $options, $class){
        $criteria = $options[1];
        $db = $this->getDbConnection();

        if(!array_key_exists('conditions', $criteria)){
            $criteria['conditions'] = array();
        }
        if(gettype($options[0]) == 'integer'){
            $options[0] = (string) $options[0];
        }

        //if(gettype($options[0]) == 'string'){
        if(in_array($options[0], array("first", "last", "all"))){
            switch($options[0]){
                case "first":
                    $criteria['limit'] = 1;
                    $criteria['offset'] = 0;
                    break;
                case "last":
                    $criteria['limit'] = 1;
                    $criteria['order'] = join(' DESC, ',$class::getPk()) . ' DESC';
                    break;
            }
        }else{
            //custom
            $pks = array_combine($class::getPk(), array($options[0]));
            $criteria['conditions'] = array_merge($pks, $criteria['conditions']);
        }

        $sql = $this->createSelectQuery($criteria, $db);
        Edge::app()->logger->debug($sql);
        $rs = $db->db_query($sql);
        return array($rs, $db->db_num_rows($rs));
    }

    /**
     * Convenient method so that the AR
     * can access the db object
     * @return mixed
     */
    public function getDbConnection(){
        return Edge::app()->db;
    }

    /**
     * Build the sql query based on the provided options
     */
    protected function createSelectQuery(array $options, $db){
        $sql = array();
        if(array_key_exists('conditions', $options) && count($options['conditions']) > 0){
            $sql[] = "WHERE ". $this->joinConditions($options['conditions'], $db);
        }
        if(array_key_exists('order', $options)){
            $order_val = is_array($options['order'])?$options['order'][0]:$options['order'];
            $sql[] = "ORDER BY ". $order_val;
        }
        if(array_key_exists('limit', $options)){
            if(array_key_exists('offset', $options)){
                $options['limit'] = join(",", array($options['offset'], $options['limit']));
            }
            $sql[] = "LIMIT ". $options['limit'];
        }
        return sprintf("SELECT * FROM %s %s", $options['from'], join(" ", $sql));
    }

    /**
     * Save the object to the database
     * @param \Edge\Models\ActiveRecord $entry
     */
    public function save(ActiveRecord $entry){
        $db = Edge::app()->writedb;
        $data = array_map(function($v) use ($db){
            return sprintf('"%s"', $db->db_escape_string($v));
        }, $entry->getAttributes());
        $db->db_query($this->getInsertQuery($data, $entry));
        $this->setAutoIncrement($entry);
    }

    /**
     * After the object has been created check for
     * auto increment fields and set the value
     * assigned by MySQL
     * @param \Edge\Models\ActiveRecord $entry
     */
    private function setAutoIncrement(ActiveRecord $entry){
        $table = $entry->getTable();
        $pks = $entry->getPk();
        if(count($pks) > 0){
            $db = Edge::app()->db;
            $metadata = $db->db_metadata($table);
            foreach($pks as $attr){
                if(isset($metadata[$attr]) && $metadata[$attr][1] & \MYSQLI_AUTO_INCREMENT_FLAG){
                    $entry->$attr = $db->db_insert_id();
                }
            }
        }
    }

    /**
     * Construct the INSERT sql query
     * @param $data
     * @param \Edge\Models\ActiveRecord $entry
     * @return string
     */
    private function getInsertQuery($data, ActiveRecord $entry) {
        $q = "INSERT INTO ".$entry::getTable()." (";
        $q .= join(",", array_keys($data)).") VALUES(";
        $q .= join(",", array_values($data)).")";
        Edge::app()->logger->debug($q);
        return $q;
    }

    /**
     * Return an associative array with primary keys
     * and their values
     * @param \Edge\Models\ActiveRecord $entry
     * @return array
     * @throws \Exception
     */
    private function getPkValues(ActiveRecord $entry){
        $attrs = array();
        $pk = $entry::getPk();
        foreach($pk as $key){
            $val = $entry->$key;
            if(empty($val)){
                throw new \Exception("Primary key must have a value");
            }
            $attrs[$key] = $val;
        }
        return $attrs;
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
    public function joinConditions(array $conditions, $db){
        $data = array_map(function($k, $v) use ($db){
            if(is_array($v)){
                return $this->processAttrs($db, $k, $v);
            }
            return sprintf('%s = \'%s\'', $k, $db->db_escape_string($v));
        }, array_keys($conditions), array_values($conditions));
        return join(' AND ', $data);
    }

    private function processAttrs($db, $key, array $attrs){
        $vals = $attrs;
        $op = "";
        $modifier = function($v){
            return $v;
        };

        if(isset($attrs['in'])){
            $vals = $attrs['in'];
            $op = "IN";
            $modifier = function($v){
                return sprintf("(%s)", $v);
            };
        }
        elseif(isset($attrs['between'])){
            $vals = $attrs['between'];
            $op = "BETWEEN";
            $modifier = function($v){
                $v = explode(",", $v);
                return sprintf("%s", join(" AND ", $v));
            };
        }

        $_vals = array();
        foreach($vals as $val){
            $_vals[] = sprintf('"%s"', $db->db_escape_string($val));
        }
        $v = join(",", $_vals);
        return sprintf('%s %s %s', $key, $op, $modifier($v));
    }

    /**
     * Delete object from the database
     * @param \Edge\Models\ActiveRecord $entry
     * @param array $criteria
     */
    public function delete(ActiveRecord $entry, array $criteria=array()){
        $db = Edge::app()->writedb;
        $where = array();
        if(count($criteria) > 0){
            if(array_key_exists('conditions', $criteria)){
                $where = $criteria['conditions'];
            }
        }
        else{
            $where = $this->getPkValues($entry);
        }

        $sql = sprintf("DELETE FROM %s WHERE %s", $entry::getTable(), $this->joinConditions($where, $db));
        Edge::app()->logger->debug($sql);
        $db->db_query($sql);
    }

    /**
     * Construct the UPDATE sql query and update the object
     * @param \Edge\Models\ActiveRecord $entry
     */
    public function update(ActiveRecord $entry){
        $pks = $this->getPkValues($entry);
        $data = array_diff_assoc($entry->getAttributes(), $pks);
        $db = Edge::app()->writedb;
        $k = array_keys($data);
        $v = array_values($data);
        $c = join(", ", array_map(function($k, $v) use ($db){
            return sprintf('%s="%s"', $k, $db->db_escape_string($v));
        }, $k, $v));
        $q = sprintf("UPDATE %s SET %s WHERE %s", $entry::getTable(), $c, $this->joinConditions($pks, $db));
        Edge::app()->logger->debug($q);
        $db->db_query($q);
    }
}