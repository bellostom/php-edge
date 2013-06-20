<?php
namespace Edge\Models;

use Edge\Core\Exceptions,
    Edge\Core\Interfaces\EventHandler,
    Edge\Core\Database\ResultSet\CachedObjectSet,
    Edge\Core\Interfaces\CachableRecord;

/**
 * Base class for all models that require persistence and
 * interaction with a database. The class is agnostic in
 * regards to the underlying persistence layer.
 * Each class extending it, needs to define the Adapter
 * class that will be responsible for the interaction.
 * The default adapter class is the MySQLAdapter.
 */
abstract class ActiveRecord implements EventHandler, CachableRecord{
    /**
     * @var array Stores the class's attributes
     * These attributes are mapped to the DB record
     */
    protected $attributes = array();
    /**
     * @var string The adapter class which interacts
     * with the persistence layer
     */
    protected static $adapterClass = 'MySQL';
    /**
     * @var array We only require 1 instance of each
     * adapter. Once instantiated for the 1st time,
     * we store it here, so that it can be reused
     * by other ActiveRecords also
     */
    protected static $adapterInstances = array();

    /**
     * @var array Each class extending ActiveRecord
     * should define an array with the names of
     * its attributes
     */
    protected static $_members = array();

    /**
     * Return an instance of ResultSet iterator
     */
    CONST FETCH_RESULTSET = 1;

    /**
     * Return a single ActiveRecord instance
     */
    CONST FETCH_INSTANCE = 2;

    /**
     * Return the Adapter's native resultset
     * without processing it
     */
    CONST FETCH_NATIVE_RESULTSET = 4;

    /**
     * Return an associative array
     */
    CONST FETCH_ASSOC_ARRAY = 8;

    /**
     * @param array $attrs
     * Initialize an ActiveRecord instance. Either supply an associative
     * array or the object will be initialized with default values.
     */
    public function __construct(array $attrs=array()){
        if(count($attrs) == 0){
            $attrs = static::getMembers();
        }
        $this->attributes = $attrs;
    }

    /**
     * @return array Associative array with
     * the object's attributes
     */
    public function getAttributes(){
        return $this->attributes;
    }

    /**
     * Child classes should implement
     * @return string Return the table's name
     */
    public static function getTable(){}

    /**
     * Child classes should implement
     * @return array Return an array with the table's
     * primary keys
     */
    public static function getPk(){}

    /**
     * Load the adapter class to interface with the selected
     * persistance layer
     * @return mixed
     */
    protected static function getAdapter(){
        $className = sprintf("Edge\Models\Adapters\%sAdapter", static::$adapterClass);
        if (!isset(static::$adapterInstances[$className])){
            static::$adapterInstances[$className] = new $className();
        }
        return static::$adapterInstances[$className];
    }

    /**
     * Iterate over the inheritance chain, constructing
     * an array from the $_members attribute.
     * We do that in order to supply the new instance with
     * the default members in case the instantiated object
     * was not supplied with any attrs.
     * @return array
     */
    protected static function getMembers(){
        $class = new \ReflectionClass(get_called_class());
        $staticAttrs = $class->getStaticProperties();
        $lineage = $staticAttrs['_members'];

        while ($class = $class->getParentClass()) {
            $staticAttrs = $class->getStaticProperties();
            $lineage = array_merge($lineage, $staticAttrs['_members']);
        }
        return array_combine($lineage, array_fill(0, count($lineage), null));
    }

    /**
     * If you define a custom setter for any of the attributes
     * you need to call this method to properly assign the value
     * instead of $this->attr = $val, because it will not work and
     * moreover no warning or error is thrown.
     * This is due to PHP's default behavior
     * @see http://www.php.net/manual/en/language.oop5.overloading.php#76622
     * @param $attr
     * @param $val
     */
    public function assignAttribute($attr, $val){
        if(array_key_exists($attr, $this->attributes)){
            $this->attributes[$attr] = $val;
        }
    }

    /**
     * Intercept any calls to set an object's members
     * If there is a setter defined (denoted by setAttr, ie setName)
     * call it. Otherwise, set the value within the $attributes array, if the attr
     * exists.
     * @param $attr String, The attribute to be set
     * @param $val Mixed, The value of the attribute
     */
    public function __set($attr, $val){
        $setter = sprintf("set%s", ucfirst($attr));
        if(method_exists($this, $setter)){
            $this->$setter($val);
            return;
        }
        $this->assignAttribute($attr, $val);
    }

    /**
     * Intercept any calls to get an object's member
     * If there is a getter defined (denoted by getAttr, ie getName)
     * call it. Otherwise, get the value from the $attributes array, if the attr
     * exists.
     * @param $attr
     * @return mixed
     * @throws \Edge\Core\Exceptions\UnknownPropery
     */
    public function __get($attr){
        $getter = sprintf("get%s", ucfirst($attr));
        if(method_exists($this, $getter)){
            return $this->$getter();
        }
        else if(array_key_exists($attr, $this->attributes)){
            return $this->attributes[$attr];
        }
        throw new Exceptions\UnknownProperty(get_called_class(), $attr);
    }

    /**
     * Specifies whether the records of this class
     * will be cached
     * @return bool|void
     */
    public static function cacheRecord(){
        return true;
    }

    /**
     * Get a unique cache key for the record
     * @param array $args
     * @return string
     */
    public static function getCacheKey(array $args){
        return md5(serialize($args));
    }

    /**
     * Return a per instance unique index key
     * where we store each instance's key
     * @return string
     * @throws \Exception
     */
    public function getInstanceIndexKey(){
        $keys = static::getPk();
        $v = array();
        foreach($keys as $key){
            if($this->$key == '')
                throw new \Exception("The instance's PK's variables must be set
										before calling getInstanceIndexKey");
            $v[] = $this->$key;
        }
        return sprintf("%s:%s", static::getTable(),
            implode(':', $v));
    }

    /**
     * Store the cached key in a per instance index
     * so that we can easily invalidate the cached instances
     * @param $cached_key
     */
    public function addKeyToIndex($cached_key){
        $mem = \Edge\Core\Edge::app()->cache;
        $key = $this->getInstanceIndexKey();
        $index = $mem->get($key);
        if(!$index){
            $index = array();
        }
        if(!in_array($cached_key, $index)){
            $index[] = $cached_key;
            $index = array_unique($index);
            $mem->add($key, $index);
        }
    }

    /**
     * Get the record's cached version
     * @param array $args
     * @return mixed
     */
    protected static function getCachedRecord(array $args){
        $cache = \Edge\Core\Edge::app()->cache;
        return $cache->get(static::getCacheKey($args));
    }

    /**
     * Cache the result of the query
     * @param $key
     * @param $data
     * @param $ttl
     * @param $fetchMode
     * @return CachedObjectSet|null
     * @throws \Exception
     */
    protected static function cacheData($key, $data, $ttl, $fetchMode){
        $_data = null;
        switch($fetchMode){
            case ActiveRecord::FETCH_ASSOC_ARRAY:
                $_data =  $data;
                break;
            case ActiveRecord::FETCH_INSTANCE:
                $class = get_called_class();
                $attrs = \Edge\Core\Edge::app()->db->db_fetch_array($data);
                $_data = new $class($attrs);
                $_data->addKeyToIndex($key);
                break;
            case ActiveRecord::FETCH_RESULTSET:
                $rs = \Edge\Core\Edge::app()->db->db_fetch_all($data);
                $_data = new CachedObjectSet($rs, get_called_class());
                break;
            case ActiveRecord::FETCH_NATIVE_RESULTSET:
                $_data = \Edge\Core\Edge::app()->db->db_fetch_all($data);
                break;
        }

        $res = \Edge\Core\Edge::app()->cache->add($key, $_data, $ttl);
        if(!$res){
            throw new \Exception("Could not write data to cache");
        }
        return $_data;
    }

    /**
     Proxy select requests to the object's adapter class

     Record::find(1)

     Record::find("all", array(
          'conditions' => array("name" => "English"),
          'order' => array("name DESC"),
          'limit' => 10,
          'offset' => 0
     ))

     Record::find(array(
        'conditions' => array("id" => 12)
    ))

    Record::find(array(
        'conditions' => array("id" => array("in" => array(2,3))),
        'order' => array("name DESC")
    ))
    Record::find("last")
    Record::find("first")
    */
    public static function find(/*args*/){
        $args = func_get_args();
        if(count($args) == 0){
            throw new \Exception("Insufficient arguments supplied");
        }
        $fetchMode = ActiveRecord::FETCH_INSTANCE;
        $cacheAttrs = false;

        if(is_array($args[0])){
            array_unshift($args, "all");
        }

        if(count($args) == 2){
            $args[1]['from'] = static::getTable();
            if(isset($args[1]['fetchMode'])){
                $fetchMode = $args[1]['fetchMode'];
                unset($args[1]['fetchMode']);
            }
            if(isset($args[1]['cache'])){
                $cacheAttrs = $args[1]['cache'];
                unset($args[1]['cache']);
            }
        }
        else{
            $args[] = array(
                'from' => static::getTable()
            );

        }

        $args = array($args);
        $args[] = get_called_class();

        $cacheRecord = ((static::cacheRecord() && $fetchMode == ActiveRecord::FETCH_INSTANCE)
                            || $cacheAttrs);
        if($cacheRecord){
            $value = static::getCachedRecord($args);
            if($value){
                return $value;
            }
        }

        list($result, $records) = call_user_func_array(array(static::getAdapter(), 'find'), $args);

        if($cacheRecord && $records){
            $ttl = 0;
            if($cacheAttrs && array_key_exists('ttl', $cacheAttrs)){
                $ttl = $cacheAttrs['ttl'];
            }
            $cacheKey = static::getCacheKey($args);
            return static::cacheData($cacheKey, $result, $ttl, $fetchMode);
        }
        return $result;
    }

    /**
     * Save the object to the persistence layer
     */
    public function save(){
        $this->on_create();
        static::getAdapter()->save($this);
        $this->on_after_create();
    }

    /**
     * Delete object from the persistence layer
     * @param array $criteria
     *
     */
    public function delete(array $criteria=array()){
        $this->on_delete();
        static::getAdapter()->delete($this, $criteria);
        $this->on_after_delete();
    }

    /**
     * Update object
     */
    public function update(){
        $this->on_update();
        static::getAdapter()->update($this);
        $this->on_after_update();
    }

    public function on_create(){

    }

    public function on_after_create(){

    }

    public function on_update(){

    }

    public function on_after_update(){
        if(static::cacheRecord()){
            $mem = \Edge\Core\Edge::app()->cache;
            $index = $this->getInstanceIndexKey();
            $list = $mem->get($index);
            if($list && count($list) > 0){
                foreach($list as $item){
                    $mem->add($item, $this);
                }
            }
        }
    }

    public function on_delete(){

    }

    /**
     * Delete any cached versions of the instance
     */
    public function on_after_delete(){
        if(static::cacheRecord()){
            $mem = \Edge\Core\Edge::app()->cache;
            $logger = \Edge\Core\Edge::app()->logger;
            $index = $this->getInstanceIndexKey();
            $list = $mem->get($index);
            if($list && count($list) > 0){
                foreach($list as $item){
                    $mem->delete($item);
                    $logger->info('deleting from cache item '.$item);
                }
                $mem->delete($index);
                $logger->info('deleting from cache index '.$index);
            }
        }
    }
}