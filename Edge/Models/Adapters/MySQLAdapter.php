<?php
namespace Edge\Models\Adapters;

use Edge\Models\Record,
    Edge\Core\Database\ResultSet\MySQLResultSet,
    Edge\Core\Edge;

class MySQLAdapter extends BaseAdapter{

    public function executeQuery($sql){
        $db = $this->getDbConnection();
        Edge::app()->logger->debug($sql);
        return $db->dbQuery($sql);
    }

    protected function countResults($rs){
        return $this->getDbConnection()->dbNumRows($rs);
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
     * Return an iterator for the mysqli_result object
     * @param $rs mysqli_result object
     * @return \Edge\Core\Database\ResultSet\MySQLResultSet
     */
    protected function getResultSet($rs, $class){
        return new \Edge\Core\Database\ResultSet\MySQLResultSet($rs, $class);
    }

    /**
     * Return an assoc array from the resultset
     * @param $rs mysqli_result object
     * @return array
     */
    protected function fetchArray($rs){
        return Edge::app()->db->dbFetchArray($rs);
    }

    protected function fetchAll($rs){
        return Edge::app()->db->dbFetchAll($rs);
    }

    /**
     * Constructs a query to load data from
     * a linked table for a many to many relationship
     *
     *
        SELECT city.* FROM city
        INNER JOIN country2city u
        ON city.id = u.city_id
        AND u.country_id = '1'
     *
     * @param $model
     * @param array $attrs
     */
    public function manyToMany($model, array $attrs){
        $refTable = $model::getTable();
        $values = array(
            ':table' => $refTable,
            ':linkTable' => $attrs['linkTable'],
            ':fk2' => $attrs['fk2'],
            ':fk1' => $attrs['fk1'],
            ':value' => $attrs['value']
        );
        $q = $model::sprintf("SELECT :table.* FROM :table
                              INNER JOIN :linkTable u
                              ON :table.id = u.:fk2
                              AND u.:fk1 = ':value'", $values);
        Edge::app()->logger->debug("Dumping many to many query: \n".$q);
        return new MySQLResultSet(Edge::app()->db->dbQuery($q), $model);
    }

    /**
     * Save the object to the database
     * @param \Edge\Models\Record $entry
     */
    public function save(Record $entry){
        $db = Edge::app()->writedb;
        $table = $entry->getTable();
        $pks = $entry->getPk();
        if(count($pks) > 0){
            $metadata = $db->dbMetadata($table);
            foreach($pks as $attr){
                if(isset($metadata[$attr]) && $metadata[$attr][1] & \MYSQLI_AUTO_INCREMENT_FLAG){
                    if($entry->$attr == ""){
                        $entry->$attr = 0;
                    }
                }
            }
        }
        $data = array_map(function($v) use ($db){
            return sprintf('"%s"', $db->dbEscapeString($v));
        }, $entry->getAttributes());
        $db->dbQuery($this->getInsertQuery($data, $entry));
        $this->setAutoIncrement($entry);
    }

    /**
     * After the object has been created check for
     * auto increment fields and set the value
     * assigned by MySQL
     * @param \Edge\Models\Record $entry
     */
    private function setAutoIncrement(Record $entry){
        $table = $entry->getTable();
        $pks = $entry->getPk();
        if(count($pks) > 0){
            $db = Edge::app()->writedb;
            $metadata = $db->dbMetadata($table);
            foreach($pks as $attr){
                if(isset($metadata[$attr]) && $metadata[$attr][1] & \MYSQLI_AUTO_INCREMENT_FLAG){
                    $entry->$attr = $db->dbInsertId();
                }
            }
        }
    }

    /**
     * Construct the INSERT sql query
     * @param $data
     * @param \Edge\Models\Record $entry
     * @return string
     */
    private function getInsertQuery($data, Record $entry) {
        $q = "INSERT INTO ".$entry::getTable()." (";
        $q .= join(",", array_keys($data)).") VALUES(";
        $q .= join(",", array_values($data)).")";
        Edge::app()->logger->debug($q);
        return $q;
    }

    /**
     * Construct the SELECT query
     * @return string
     */
    protected function getQuery(){
        $db = $this->getDbConnection();
        $sql = array();

        if(count($this->where) > 0){
            $sql[] = "WHERE ". $this->joinConditions($this->where, $db);

            if(count($this->and) > 0){
                $sql[] = "AND ". $this->joinConditions($this->and, $db);
            }

            if(count($this->or) > 0){
                $sql[] = "OR ". $this->joinConditions($this->or, $db, "OR");
            }
        }
        if($this->order){
            $data = array_map(function($k, $v){
                return sprintf('%s %s', $k, $v);
            }, array_keys($this->order), array_values($this->order));


            $sql[] = "ORDER BY ". join(", ", $data);
        }
        if(!is_null($this->limit)){
            $data = array($this->limit);
            if(!is_null($this->offset)){
                $data[] = $this->offset;
            }
            $sql[] = "LIMIT ". join(",", $data);
        }
        $fields = join(",", $this->selectFields);
        return sprintf("SELECT %s FROM %s %s", $fields, $this->table, join(" ", $sql));
    }

    /**
     * Return an associative array with primary keys
     * and their values
     * @param \Edge\Models\Record $entry
     * @return array
     * @throws \Exception
     */
    private function getPkValues(Record $entry){
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
    public function joinConditions(array $conditions, $db, $clause='AND'){
        $data = array_map(function($k, $v) use ($db){
            if(is_array($v)){
                $vals = array();
                foreach($v as $kv){
                    $vals[] = sprintf("'%s'", $db->dbEscapeString($kv));
                }
                return sprintf("%s in (%s)", $k, join(",", $vals));
            }
            return sprintf('%s = \'%s\'', $k, $db->dbEscapeString($v));
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
        Edge::app()->logger->debug($sql);
        $db->dbQuery($sql);
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
            return sprintf('%s="%s"', $k, $db->dbEscapeString($v));
        }, $k, $v));
        $q = sprintf("UPDATE %s SET %s WHERE %s", $entry::getTable(), $c, $this->joinConditions($pks, $db));
        Edge::app()->logger->debug($q);
        $db->dbQuery($q);
    }
}