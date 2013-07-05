<?php
namespace Edge\Models\Adapters;

use Edge\Core\Edge,
    Edge\Core\Database\ResultSet\CachedObjectSet,
    Edge\Models\Record;

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
    protected $fetchMode = Record::FETCH_INSTANCE;
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
        $this->fetchMode = Record::FETCH_INSTANCE;
        $this->cacheAttrs = array();
        $this->query = null;
    }

    abstract protected function getQuery();
    abstract protected function getResultSet($rs, $class);
    abstract protected function countResults($rs);
    abstract protected function fetchAll($rs);
    abstract protected function fetchArray($rs);
    abstract public function executeQuery($query);
    abstract public function save(Record $entry);
    abstract public function delete(Record $entry);
    abstract public function update(Record $entry);
    abstract public function getDbConnection();
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

    /**
     * Construct a cache key based on the selections
     * defined for the query
     * @return string
     */
    protected function getCacheKey(){
        $data = array($this->table, $this->where,
                      $this->or, $this->and,
                      $this->order, $this->limit,
                      $this->offset, $this->fetchMode,
                      $this->cacheAttrs);
        return md5(serialize($data));
    }

    protected function cacheData($key, $data, $ttl, $cacheValidator=null){
        $_data = null;
        switch($this->fetchMode){
            case Record::FETCH_ASSOC_ARRAY:
                $_data =  $data;
                break;
            case Record::FETCH_INSTANCE:
                $class = $this->model;
                $attrs = $this->fetchArray($data);
                $_data = new $class($attrs);
                $_data->addKeyToIndex($key);
                break;
            case Record::FETCH_RESULTSET:
                $rs = $this->fetchAll($data);
                $_data = new CachedObjectSet($rs, $this->model);
                break;
            case Record::FETCH_NATIVE_RESULTSET:
                $_data = $this->fetchAll($data);
                break;
        }

        $res = Edge::app()->cache->add($key, $_data, $ttl, $cacheValidator);
        if(!$res){
            throw new \Exception("Could not write data to cache");
        }
        return $_data;
    }

    /**
     * Execute the query and handle caching
     * @return array|CachedObjectSet|mixed|null
     */
    protected function execute(){
        $model = $this->model;
        $cacheAttrs = $this->cacheAttrs;
        $cacheRecord = (($model::cacheRecord() && $this->fetchMode == Record::FETCH_INSTANCE)
                        || $cacheAttrs);
        if($cacheRecord){
            $value = $this->getCachedRecord();
            if($value){
                return $value;
            }
        }

        $result = $this->executeQuery($this->query);
        $records = $this->countResults($result);

        if($cacheRecord && $records){
            $ttl = 0;
            $validator = null;
            if($cacheAttrs && array_key_exists('ttl', $cacheAttrs)){
                $ttl = $cacheAttrs['ttl'];
            }
            if($cacheAttrs && array_key_exists('cacheValidator', $cacheAttrs)){
                $validator = $cacheAttrs['cacheValidator'];
            }
            $cacheKey = $this->getCacheKey();
            return $this->cacheData($cacheKey, $result, $ttl, $validator);
        }

        //no caching specified
        if($records == 0){
            if(in_array($this->fetchMode, array(Record::FETCH_RESULTSET,
                                                Record::FETCH_NATIVE_RESULTSET))){
                return array();
            }
            return null;
        }

        switch($this->fetchMode){
            case Record::FETCH_ASSOC_ARRAY:
            case Record::FETCH_NATIVE_RESULTSET:
                return $result;

            case Record::FETCH_INSTANCE:
                $class = $this->model;
                $attrs = $this->fetchArray($result);
                return new $class($attrs);

            case Record::FETCH_RESULTSET:
                return $this->getResultSet($result, $this->model);
        }
    }

    /**
     * Constructs and runs the query
     * Valid examples
     *
     * Select the first 10 users ordering them by username ascending
     * and caching them for 10 minutes
     * \Edge\Models\User::select()
                        ->order(array("username"=>"asc"))
                        ->limit(0)
                        ->offset(10)
                        ->fetchMode(Record::FETCH_RESULTSET)
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
                        ->in([1,2])
                        ->orWhere("name")
                        ->in(["Thomas", "John"])
                        ->andWhere(array("sex"=>"male"))
                        ->order(array("username"=>"asc"))
                        ->run();
     *
     *
     *
     * @return array|CachedObjectSet|mixed|null
     */
    public function run(){
        //check if we are invoked from selectQuery
        if(!$this->query){
            $this->query = $this->getQuery();
        }
        $ret = $this->execute();
        $this->reset();
        return $ret;
    }

    /**
     * Sets the fields to select
     * @param array $args
     */
    public function select(array $args){
        $this->selectFields = $args;
    }

    /**
     * Set caching attributes
     * @param array $attrs
     * @return $this
     */
    public function cache(array $attrs){
        $this->cacheAttrs = $attrs;
        return $this;
    }

    /**
     * Set result fetch mode
     * @param $mode
     * @return $this
     */
    public function fetchMode($mode){
        $this->fetchMode = $mode;
        return $this;
    }

    /**
     * Sets values for the query
     * @param $args
     * @param $var
     * @return $this
     */
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

    /**
     * Sets sort values
     * @param array $args
     * @return $this
     */
    public function order(array $args){
        $key = array_keys($args)[0];
        $val = array_values($args)[0];
        $this->order[$key] = $val;
        return $this;
    }

    /**
     * Sets limit value
     * @param $w
     * @return $this
     */
    public function limit($w){
        $this->limit = $w;
        return $this;
    }

    /**
     * Set offset
     * @param $w
     * @return $this
     */
    public function offset($w){
        $this->offset = $w;
        return $this;
    }

    /**
     * Define select attributes
     * @param $args
     * @return $this
     */
    public function where($args){
        return $this->clause($args, 'where');
    }

    /**
     * Define select attributes
     * @param $args
     * @return $this
     */
    public function orWhere($args){
        return $this->clause($args, 'or');
    }

    /**
     * Define select attributes
     * @param $args
     * @return $this
     */
    public function andWhere($args){
        return $this->clause($args, 'and');
    }

    /**
     * Define select attributes
     * @param $args
     * @return $this
     */
    public function in(array $args){
        $lastVar = $this->lastVar;
        $key = $this->lastWhere;
        $this->{$lastVar}[$key] = $args;
        return $this;
    }
}