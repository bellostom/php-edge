<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 18/4/2013
 *
 */
namespace Edge\Models\Adapters;
use Edge\Core\Database\MysqlMaster;
use Edge\Core\Database\DB;
use Edge\Core\Database\ResultSet\MySQLResultSet;
use Edge\Models\ActiveRecord;
use Edge\Core;

class MongoAdapter implements AdapterInterface{

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
     * Record::find(array("id"=>array(1,2), "name" => "John"), array(
        'order' => array("name desc"),
        'limit' => 10,
        'offset' => 0
    ));
     * @param array $options
     * @param $class
     */
    public function find(array $options, $class){
        $criteria = $options[1];
        //$fetchMode = $criteria['fetchMode'];
        //$returnSingle = in_array($fetchMode, array(ActiveRecord::FETCH_INSTANCE, ActiveRecord::FETCH_ASSOC_ARRAY));
        $db = $this->getDbConnection();

        if(!array_key_exists('conditions', $criteria)){
            $criteria['conditions'] = array();
        }
        if(gettype($options[0]) == 'integer'){
            $options[0] = (string) $options[0];
        }
        $criteria['from'] = $class::getTable();
        if(gettype($options[0]) == 'string'){
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
        }
        else if(is_array($options[0])){
            $criteria['conditions'] = array_merge($criteria['conditions'], $options[0]);
        }

        $sql = $this->createSelectQuery($criteria, $db);
        $rs = $db->db_query($sql);
        return array($rs, $db->db_num_rows($rs));

        /*if($nums == 0){
            if($returnSingle){
                return null;
            }
            return array();
        }
        if($returnSingle){
            $row = $db->db_fetch_array($rs);
            if($fetchMode == ActiveRecord::FETCH_ASSOC_ARRAY){
                return $row;
            }
            return new $class($row);
        }
        return new MySQLResultSet($rs, $class);*/
    }

    public function getDbConnection(){
        return \Edge\Core\Edge::app()->db;
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
        $db = MysqlMaster::getInstance();
        $data = array_map(function($v) use ($db){
            return sprintf('"%s"', $db->db_escape_string($v));
        }, $entry->getAttributes());
        //if(Core\Context::$autoCommit){
        //    $db->start_transaction();
        //}
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
            $db = MysqlMaster::getInstance();
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
                $vals = array();
                foreach($v as $val){
                    $vals[] = sprintf('"%s"', $db->db_escape_string($val));
                }
                $v = join(",", $vals);
                return sprintf('%s IN (%s)', $k, $v);
            }
            return sprintf('%s = \'%s\'', $k, $db->db_escape_string($v));
        }, array_keys($conditions), array_values($conditions));
        return join(' AND ', $data);
    }

    /**
     * Delete object from the database
     * @param \Edge\Models\ActiveRecord $entry
     * @param array $criteria
     */
    public function delete(ActiveRecord $entry, array $criteria=array()){
        $db = MysqlMaster::getInstance();
        $where = array();
        if(count($criteria) > 0){
            if(array_key_exists('conditions', $criteria)){
                $where = $criteria['conditions'];
            }
        }
        else{
            $where = $this->getPkValues($entry);
        }

        $sql = sprintf("DELETE FROM %s WHERE %s", $entry::getTable(), $this->joinConditions($where));
        $db->db_query($sql);
    }

    /**
     * Construct the UPDATE sql query and update the object
     * @param \Edge\Models\ActiveRecord $entry
     */
    public function update(ActiveRecord $entry){
        $pks = $this->getPkValues($entry);
        $data = array_diff_assoc($entry->getAttributes(), $pks);
        $db = MysqlMaster::getInstance();
        $k = array_keys($data);
        $v = array_values($data);
        $c = join(", ", array_map(function($k, $v) use ($db){
            return sprintf('%s="%s"', $k, $db->db_escape_string($v));
        }, $k, $v));
        $q = sprintf("UPDATE %s SET %s WHERE %s", $entry::getTable(), $c, $this->joinConditions($pks));

        //if(Context::$autoCommit){
        //    $db->start_transaction();
        //}
        error_log($q);
        $db->db_query($q);
    }
}