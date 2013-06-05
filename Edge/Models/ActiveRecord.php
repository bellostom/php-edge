<?php
namespace Edge\Models;
use Edge\Core\Exceptions,
    Edge\Core\Interfaces\EventHandler,
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
     * @return string Return the table's name
     */
    abstract public static function getTable();

    /**
     * @return array Return an array with the table's
     * primary keys
     */
    abstract public static function getPk();

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
        throw new Exceptions\UnknownPropery(get_called_class(), $attr);
    }

    /**
     * Specifies whether the record's of this class
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
    public static function getCacheKey(\Edge\Core\Cache\BaseCache $cache, array $args){
        $table = static::getTable();
        $versionTable = $cache->get("versionIndex");
        if(!$versionTable){
            $versionTable = array(
                $table => 0
            );
        }
        $versionTable[$table]++;
        return sprintf("%s_%s_%s", $table, $versionTable[$table], md5(serialize($args)));
    }

    public function getInstanceIndexKey(){

    }
    public function addKeyToIndex($cached_key){
        $mem = MCache::getInstance();
        $key = $this->getInstanceIndexKey();
        $index = $mem->get($key);
        if(!$index)
            $index = array();
        $index[] = $cached_key;
        $index = array_unique($index);
        $mem->add($key, $index);
    }

    /**
     * Get the record's cached version
     * @param array $args
     * @return mixed
     */
    protected static function getCachedRecord(array $args){
        $cache = \Edge\Core\Edge::app()->cache;
        return $cache->get(static::getCacheKey($cache, $args));
    }

    protected static function transformCachedRecord($data, $fetchMode){
        switch($fetchMode){
            case ActiveRecord::FETCH_ASSOC_ARRAY:
                return $data;
            case ActiveRecord::FETCH_INSTANCE:
                $class = get_called_class();
                return new $class($data);
            case ActiveRecord::FETCH_RESULTSET:
                return new \Edge\Core\Database\ResultSet\CachedObjectSet($data, get_called_class());
        }
    }

    protected static function cacheData($key, $data, $ttl, $fetchMode){
        $_data = null;
        switch($fetchMode){
            case ActiveRecord::FETCH_ASSOC_ARRAY:
                $_data =  $data;
                break;
            case ActiveRecord::FETCH_INSTANCE:
                $_data = $data->getAttributes();
                break;
            case ActiveRecord::FETCH_RESULTSET:
                $_data = $data->toArray();
                break;
            case ActiveRecord::FETCH_NATIVE_RESULTSET:
                $_data = \Edge\Core\Edge::app()->db->db_fetch_all($data);
                break;
        }
        return \Edge\Core\Edge::app()->cache->add($key, $_data, $ttl);
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
    Record::find(array(
      "id" => array(2,4)
    ))
    */
    public static function find(/*args*/){
        $args = func_get_args();
        if(count($args) == 0){
            throw new \Exception("Insufficient arguments supplied");
        }
        $options = array();
        $options['from'] = static::getTable();

        if(is_array($args[0])){
            array_unshift($args, "all");
        }

        $fetchMode = ActiveRecord::FETCH_INSTANCE;
        if(count($args) == 2){
            $args[1]['from'] = static::getTable();
            if(isset($args[1]['fetchMode'])){
                $fetchMode = $args[1]['fetchMode'];
                unset($args[1]['fetchMode']);
            }
        }

        $returnSingle = in_array($fetchMode, array(ActiveRecord::FETCH_INSTANCE,
                                                   ActiveRecord::FETCH_ASSOC_ARRAY));

        $args = array($args);
        $args[] = get_called_class();

        $cacheRecord = (static::cacheRecord() || isset($criteria['cache']));
        if($cacheRecord){
            $value = static::getCachedRecord($args);
            if($value){
                return static::transformCachedRecord($value, $criteria['fetchMode']);
            }
        }

        list($result, $records) = call_user_func_array(array(static::getAdapter(), 'find'), $args);
        return $result;
        if($cacheRecord && $records){
            $ttl = 0;
            if(isset($criteria['cache']) && array_key_exists('ttl', $criteria['cache'])){
                $ttl = $criteria['cache']['ttl'];
            }
            $cache = \Edge\Core\Edge::app()->cache;
            $cacheKey = static::getCacheKey($cache, $args);
            static::cacheData($cacheKey, $data, $ttl, $criteria['fetchMode']);
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

    }

    public function on_delete(){

    }

    public function on_after_delete(){

    }
}
?>