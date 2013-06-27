<?php
namespace Edge\Models\Adapters;

use Edge\Core\Edge,
    Edge\Core\Database\ResultSet\CachedObjectSet,
    Edge\Models\ActiveRecord;

abstract class BaseAdapter{

    public $table;
    public $model;
    public $query;
    protected $selectFields = array("*");
    protected $lastWhere;
    protected $lastVar = 'where';
    protected $where = array();
    protected $or = array();
    protected $and = array();
    protected $order = array();
    protected $limit;
    protected $offset;
    protected $fetchMode = ActiveRecord::FETCH_INSTANCE;
    protected $cacheAttrs = array();

    public function reset(){
        $this->table = false;
        $this->model = false;
        $this->selectFields = array("*");
        $this->lastWhere = null;
        $this->lastVar = 'where';
        $this->where = array();
        $this->or = array();
        $this->and = array();
        $this->order = array();
        $this->limit = null;
        $this->offset = null;
        $this->fetchMode = ActiveRecord::FETCH_INSTANCE;
        $this->cacheAttrs = array();
        $this->query = null;
    }

    abstract protected function getQuery();
    abstract public function executeQuery($query);
    abstract public function save(ActiveRecord $entry);
    abstract public function delete(ActiveRecord $entry);
    abstract public function update(ActiveRecord $entry);
    abstract public function getDbConnection();
    abstract public function getResultSet($rs, $class);
    abstract public function fetchAll($rs);
    abstract public function fetchArray($rs);
    abstract public function manyToMany($model, array $attrs);

    /**
     * Get the record's cached version
     * @param array $args
     * @return mixed
     */
    protected function getCachedRecord(){
        $cache = Edge::app()->cache;
        return $cache->get($this->getCacheKey());
    }

    protected function getCacheKey(){
        return md5($this->query);
    }

    protected function cacheData($key, $data, $ttl){
        $_data = null;
        switch($this->fetchMode){
            case ActiveRecord::FETCH_ASSOC_ARRAY:
                $_data =  $data;
                break;
            case ActiveRecord::FETCH_INSTANCE:
                $class = $this->model;
                $attrs = $this->fetchArray($data);
                $_data = new $class($attrs);
                $_data->addKeyToIndex($key);
                break;
            case ActiveRecord::FETCH_RESULTSET:
                $rs = $this->fetchAll($data);
                $_data = new CachedObjectSet($rs, $this->model);
                break;
            case ActiveRecord::FETCH_NATIVE_RESULTSET:
                $_data = $this->fetchAll($data);
                break;
        }

        $res = Edge::app()->cache->add($key, $_data, $ttl);
        if(!$res){
            throw new \Exception("Could not write data to cache");
        }
        return $_data;
    }

    protected function execute(){
        $model = $this->model;
        $cacheAttrs = $this->cacheAttrs;
        $cacheRecord = (($model::cacheRecord() && $this->fetchMode == ActiveRecord::FETCH_INSTANCE)
                        || $cacheAttrs);
        if($cacheRecord){
            $value = $this->getCachedRecord();
            if($value){
                return $value;
            }
        }

        list($result, $records) = $this->executeQuery($this->query);

        if($cacheRecord && $records){
            $ttl = 0;
            if($cacheAttrs && array_key_exists('ttl', $cacheAttrs)){
                $ttl = $cacheAttrs['ttl'];
            }
            $cacheKey = $this->getCacheKey();
            return $this->cacheData($cacheKey, $result, $ttl);
        }

        //no caching specified
        if($records == 0){
            if(in_array($this->fetchMode, array(ActiveRecord::FETCH_RESULTSET,
                                                ActiveRecord::FETCH_NATIVE_RESULTSET))){
                return array();
            }
            return null;
        }

        switch($this->fetchMode){
            case ActiveRecord::FETCH_ASSOC_ARRAY:
            case ActiveRecord::FETCH_NATIVE_RESULTSET:
                return $result;

            case ActiveRecord::FETCH_INSTANCE:
                $class = $this->model;
                $attrs = $this->fetchArray($result);
                return new $class($attrs);

            case ActiveRecord::FETCH_RESULTSET:
                return $this->getResultSet($result, $this->model);
        }
    }

    /**
     * Constructs and runs the query
     * Valid examples
     *
     * Select the first 10 users ordering them by username ascending
     * and caching them for 10 seconds
     * \Edge\Models\User::select()
                        ->order(array("username"=>"asc"))
                        ->limit(0)
                        ->offset(10)
                        ->fetchMode(ActiveRecord::FETCH_RESULTSET)
                        ->cache(array('ttl' => 10*60))
                        ->run();
     *
     *
     *
     * Select a user with username thomas
     * \Edge\Models\User::select()
                        ->where(array("username"=>"thomas"))
                        ->run();
     *
     *
     * Select users with id 1 or 2 or name in ("Thomas", "John")
     * and sex = male
     * order them by username
     * \Edge\Models\User::select()
                        ->where("id"))
                        ->in(1,2)
                        ->orWhere("name")
                        ->in("Thomas", "John")
                        ->andWhere(array("sex"=>"male"))
                        ->order(array("username"=>"asc"))
                        ->run();
     *
     *
     *
     * @return array|CachedObjectSet|mixed|null
     */
    public function run(){
        $this->query = $this->getQuery();
        $ret = $this->execute();
        $this->reset();
        return $ret;
    }

    public function select(array $args){
        $this->selectFields = $args;
    }

    public function cache(array $attrs){
        $this->cacheAttrs = $attrs;
        return $this;
    }

    public function fetchMode($mode){
        $this->fetchMode = $mode;
        return $this;
    }

    protected function clause($args, $var){
        $this->lastVar = $var;
        if(is_string($args)){
            $this->lastWhere = $args;
        }
        else{
            $key = array_keys($args)[0];
            $val = array_values($args)[0];
            $this->{$var}[$key] = $val;
        }
        return $this;
    }

    public function order(array $args){
        $key = array_keys($args)[0];
        $val = array_values($args)[0];
        $this->order[$key] = $val;
        return $this;
    }

    public function limit($w){
        $this->limit = $w;
        return $this;
    }

    public function offset($w){
        $this->offset = $w;
        return $this;
    }

    public function where($args){
        return $this->clause($args, 'where');
    }

    public function orWhere($args){
        return $this->clause($args, 'or');
    }

    public function andWhere($args){
        return $this->clause($args, 'and');
    }

    public function in(/*$args*/){
        $args = func_get_args();
        $lastVar = $this->lastVar;
        $key = $this->lastWhere;
        $this->{$lastVar}[$key] = $args;
        return $this;
    }
}