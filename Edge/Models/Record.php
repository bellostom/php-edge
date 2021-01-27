<?php

namespace Edge\Models;

use Edge\Core\Exceptions,
    Edge\Core\Edge,
    Edge\Models\Adapters\MySQLAdapter,
    Edge\Core\Interfaces\EventHandler,
    Edge\Core\Interfaces\CachableRecord,
    PhpMyAdmin\SqlParser\Parser,
    PhpMyAdmin\SqlParser\Statement,
    PhpMyAdmin\SqlParser\Statements\SelectStatement;

/**
 * Base class for all models that require persistence and
 * interaction with a database. The class is agnostic in
 * regards to the underlying persistence layer.
 * Each class extending it, needs to define the Adapter
 * class that will be responsible for the interaction.
 * The default adapter class is the MySQLAdapter.
 */
abstract class Record implements EventHandler, CachableRecord, \Serializable{
    /**
     * @var array Stores the class's attributes
     * These attributes are mapped to the DB record
     */
    protected $attributes = array();

    /**
     * @var array Each class extending Record
     * should define an array with the names of
     * its attributes
     */
    protected static $_members = array();

    /**
     * Return an instance of ResultSet iterator
     */
    CONST FETCH_RESULTSET = 1;

    /**
     * Return a single Record instance
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
     * Initialize an Record instance. Either supply an associative
     * array or the object will be initialized with default values.
     */
    public function __construct(array $attrs = array()){
        $this->setAttributes($attrs);
    }

    /**
     * Serializable interface
     * Serialize only data within the attributes array
     * and not any other members defined by subclasses
     * @return string
     */
    public function serialize(){
        return serialize($this->getAttributes());
    }

    /**
     * Serializable interface
     * Deserialize the array and initialize
     * our object
     */
    public function unserialize($data){
        $this->setAttributes(unserialize($data));
    }

    /**
     * @return array Associative array with
     * the object's attributes
     */
    public function getAttributes(){
        return $this->attributes;
    }

    public function setAttributes(array $attrs){
        $defaults = static::getMembers();
        $attrs = array_intersect_key($attrs, $defaults);
        $attrs = array_replace_recursive($defaults, $attrs);
        $this->attributes = $attrs;
    }

    public function updateAttributes(array $attrs){
        foreach($attrs as $k => $v){
            if(isset($this->attributes[$k])){
                $this->$k = $v;
            }
        }
    }

    /**
     * Child classes should implement
     * @return string Return the table's name
     */
    public static function getTable(){
        throw new Exceptions\EdgeException(get_called_class() . " must implement method getTable()");
    }

    /**
     * Child classes should implement in case they need to
     * specify PKs
     * @return array Return an array with the table's
     * primary keys
     */
    public static function getPk(){
        return array();
    }

    /**
     * php's sprintf does not support named params
     * $values = array(
     * ':table' => 'users',
     * ':linkTable' => 'user_role',
     * ':fk2' => 'user_id',
     * ':fk1' => 'role_id',
     * ':value' => 1
     * );
     * $q = $model::sprintf("SELECT :table.* FROM :table
     * INNER JOIN :linkTable u
     * ON :table.id = u.:fk2
     * AND u.:fk1 = ':value'", $values);
     *
     * @param $subject
     * @param array $values
     *
     * @return mixed
     */
    public static function sprintf($subject, array $values){
        $cls = static::getAdapterClass();
        $keys = array_keys($values);
        $vals = array_values($values);
        $query = str_replace($keys, $vals, $subject);
        if(strpos($cls, "Prepared") !== false && Edge::app()->transformQueriestoPreparedStmts){
            static::parseSqlQuery($subject, $values, $query);
        }
        return $query;
    }

    /**
     * Extract all WHERE conditions and replace the placeholders
     * in order the query to be used with prepared statements
     * @param Statement $stmt
     * @param array $values
     */
    protected static function parseStatement(Statement $stmt, array &$values, array $map){
        $blackList = ["not"];
        foreach ($stmt->where as $where) {
            if (!$where->isOperator) {
                $expr = $where->expr;
                $expr = preg_replace_callback("/LIKE\s*([^)]+)/i", function ($matches) use (&$values, $map, $blackList, $expr) {
                    $keys = array_keys($map);
                    $vals = array_values($map);
                    $values[] = trim(str_replace($keys, $vals, $matches[1]), "'");
                    return 'LIKE ?';
                },$expr);

                $expr = preg_replace_callback("/'?:(\w+)'?/", function ($matches) use (&$values, $map, $blackList, $expr) {
                    if (in_array($matches[1], $blackList)) {
                        return $matches[1];
                    }
                    $key = ":" . $matches[1];
                    preg_match("/IN\s*/i", $expr, $match);
                    if (count($match) > 0) {
                        $vs = array_map(function ($v) {
                            return trim($v, "'");
                        }, explode(",", $map[$key]));
                        $values = array_merge($values, $vs);
                        $placeholders = array_fill(0, count($vs), '?');
                        return join(',', $placeholders);
                    }
                    $values[] = $map[$key];
                    return '?';
                }, $expr);

                $where->expr = $expr;
            }
        }
        if($stmt->union){
            static::parseStatement($stmt->union[0][1], $values, $map);
        }
    }

    protected static function applyExtraConditions(Statement $stmt, $originalQuery){
        $parser = new Parser($originalQuery);
        $_stmt = $parser->statements[0];
        $stmt->join = $_stmt->join;
        $stmt->expr = $_stmt->expr;
        $stmt->from = $_stmt->from;
        $stmt->group = $_stmt->group;
        $stmt->order = $_stmt->order;
        $stmt->limit = $_stmt->limit;
        $stmt->having = $_stmt->having;
    }

    /**
     * Parse a SELECT query and transform it to be
     * compatible with prepare statements
     * @param $query
     * @return array|bool
     */
    protected static function parseSqlQuery($query, array $map, $parsedQuery){
        $parser = new Parser($query);
        $stmt = $parser->statements[0];
        if($stmt instanceof SelectStatement && !$stmt->union && $stmt->where) {
            $values = [];
            static::parseStatement($stmt, $values, $map);
            static::applyExtraConditions($stmt, $parsedQuery);
            $q = $stmt->build();
            if(count($values) > 0) {
                Edge::app()->db->addQueryToMap($parsedQuery, [
                    "q" => $q,
                    "values" => $values
                ]);
            }
        }
    }

    /**
     * Return the DB adapter class
     * If this is defined in the config use it. Otherwise
     * fallback to mysql adapter by default
     * @return mixed|string
     */
    protected static function getAdapterClass(){
        $cls = Edge::app()->getConfig('adapterClass');
        if(!$cls){
            $cls = 'Edge\Models\Adapters\MySQLAdapter';
        }
        return $cls;
    }

    /**
     * Load the adapter class to interface with the selected
     * persistence layer
     * @return mixed
     */
    protected static function getAdapter(){
        $className = static::getAdapterClass();
        return new $className();
    }

    /**
     * Iterate over the inheritance chain, constructing
     * an array from the $_members attribute.
     * Additionally, we cache the result to improve
     * performance. Very efficient, especially if a
     * model has many instances
     * @return array
     */
    protected static function getMembers(){
        static $attrs = [];
        $cls = get_called_class();
        if(!isset($attrs[$cls])){
            $class = new \ReflectionClass($cls);
            $staticAttrs = $class->getStaticProperties();
            $lineage = $staticAttrs['_members'];

            while($class = $class->getParentClass()){
                $staticAttrs = $class->getStaticProperties();
                $lineage = array_merge($lineage, $staticAttrs['_members']);
            }
            $lineage = array_unique($lineage);
            $attrs[$cls] = array_combine($lineage, array_fill(0, count($lineage), ""));
        }
        return $attrs[$cls];
    }

    /**
     * If you define a custom setter for any of the attributes
     * you need to call this method to properly assign the value
     * instead of $this->attr = $val, because it will not work and
     * moreover no warning or error is thrown.
     * This is due to PHP's default behavior
     * @see http://www.php.net/manual/en/language.oop5.overloading.php#76622
     *
     * @param $attr
     * @param $val
     */
    public function assignAttribute($attr, $val){
        if(array_key_exists($attr, $this->attributes)){
            $this->attributes[$attr] = $val;
        }else{
            throw new \Edge\Core\Exceptions\UnknownProperty($attr, get_called_class());
        }
    }

    /**
     * Intercept any calls to set an object's members
     * If there is a setter defined (denoted by setAttr, ie setName)
     * call it. Otherwise, set the value within the $attributes array, if the attr
     * exists.
     *
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
     *
     * @param $attr
     *
     * @return mixed
     * @throws \Edge\Core\Exceptions\UnknownProperty
     */
    public function __get($attr){
        if(method_exists($this, $attr)){
            return $this->$attr();
        }
        $getter = sprintf("get%s", ucfirst($attr));
        if(method_exists($this, $getter)){
            return $this->$getter();
        }else{
            if(array_key_exists($attr, $this->attributes)){
                return $this->attributes[$attr];
            }
        }
        throw new Exceptions\UnknownProperty($attr, get_called_class());
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
     * Return a per instance unique index key
     * where we store each instance's key
     * @return string
     * @throws \Exception
     */
    public function getInstanceIndexKey(){
        $keys = static::getPk();
        $v = array();
        foreach($keys as $key){
            if($this->$key == ''){
                throw new Exceptions\EdgeException("The instance's PK's variables must be set
										before calling getInstanceIndexKey");
            }
            $v[] = $this->$key;
        }
        return sprintf("%s:%s", static::getTable(), implode(':', $v));
    }

    /**
     * Store the cached key in a per instance index
     * so that we can easily invalidate the cached instances
     *
     * @param $cached_key
     */
    public function addKeyToIndex($cached_key){
        $mem = Edge::app()->cache;
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
     * One to one relationship
     * Load the referenced model instance.
     * If not fk value specified, the default is to
     * construct it by taking the table name and concatenate it
     * with an '_' (city_id)
     *
     * @param $model
     * @param array $keys optional (array("fk" => "city_id", "value" => 1))
     *
     * @return mixed
     */
    protected function hasOne($model, $keys = array()){
        static $instances = [];

        if(!isset($keys['fk'])){
            $keys['fk'] = sprintf("%s_id", static::getTable());
        }
        if(!isset($keys['value'])){
            $keys['value'] = $this->id;
        }
        $key = sprintf("%s:%s:%s", $model, $keys['fk'], $keys['value']);
        if(!isset($instances[$key])){
            $instances[$key] = $model::select()->where(array($keys['fk'] => $keys['value']))->fetch();
        }
        return $instances[$key];
    }

    /**
     * Returns an instance of the model, to which
     * the current entry belongs to.
     * ie An Article is set to belong to a Source
     * This is denoted by the existence of a source_id FK
     * entry to the article table.
     * By defining a method source() in the Article model
     * and accessing it by $article->source we get a reference
     * to the Source model
     *
     * @param $model
     * @param array $keys optional (array("fk" => "source_id", "value" => 1))
     *
     * @return mixed
     */
    protected function belongsTo($model, $keys = array()){
        if(!isset($keys['fk'])){
            $keys['fk'] = 'id';
        }
        if(!isset($keys['value'])){
            $id = sprintf("%s_id", $model::getTable());
            $keys['value'] = $this->$id;
        }
        return static::hasOne($model, $keys);
    }

    /**
     * $this->manyToMany('Application\Models\City', array(
     * 'linkTable' => 'country2city',
     * 'fk1' => 'country_id',
     * 'fk2' => 'city_id',
     * 'value' => $this->id
     * ));
     *
     * @param $model
     * @param array $attrs
     *
     * @return ResultSet
     */
    protected function manyToMany($model, $attrs = array()){
        static $instances = [];
        if(!isset($attrs['value'])){
            $attrs['value'] = $this->id;
        }
        $key = sprintf("%s:%s", $attrs['linkTable'], $attrs['value']);
        if(!isset($instances[$key])){
            $instances[$key] = static::getAdapter()->manyToMany($model, $attrs);
        }
        return $instances[$key];
    }

    /**
     * One to many relationship
     * Load the referenced model instances.
     * If not fk value specified, the default is to
     * construct it by taking the table name and concatenate it
     * with an '_' (city_id)
     *
     * @param $model
     * @param array $keys optional (array("fk" => "city_id", "value" => 1))
     *
     * @return mixed
     */
    protected function hasMany($model, $keys = array()){
        static $instances = [];

        if(!isset($keys['fk'])){
            $keys['fk'] = sprintf("%s_id", static::getTable());
        }
        if(!isset($keys['value'])){
            $keys['value'] = array($this->id);
        }
        $key = sprintf("%s:%s:%s", $model, $keys['fk'], md5(serialize($keys['value'])));
        if(!isset($instances[$key])){
            $instances[$key] = $model::select()->where($keys['fk'])->in($keys['value'])->fetch(Record::FETCH_RESULTSET);
        }
        return $instances[$key];
    }

    public static function first(){
        $adapter = static::selectCommon();
        $adapter->limit(1);
        return $adapter;
    }

    public static function select($args = array("*")){
        $adapter = static::selectCommon();
        $adapter->select($args);
        return $adapter;
    }

    /**
     * Execute a select query
     *
     * @param $attrs array of params
     *
     * @return MySQLAdapter
     * @throws \Edge\Core\Exceptions\EdgeException
     */
    public static function selectQuery(array $attrs){
        $adapter = static::selectCommon();
        if(!($adapter instanceof MySQLAdapter)){
            throw new Exceptions\EdgeException("selectQuery can only be invoked for SQL statements");
        }
        $adapter->query = $attrs['query'];
        if(isset($attrs['cache'])){
            $adapter->cache($attrs['cache']);
        }
        if(isset($attrs['fetchMode'])){
            $adapter->fetchMode($attrs['fetchMode']);
        }
        return $adapter->run();
    }

    protected static function selectCommon(){
        $adapter = static::getAdapter();
        $adapter->table = static::getTable();
        $adapter->model = get_called_class();
        return $adapter;
    }

    /**
     * Save the object to the persistence layer
     */
    public function save(){
        $this->onCreate();
        static::getAdapter()->save($this);
        $this->onAfterCreate();
    }

    /**
     * Delete object from the persistence layer
     *
     */
    public function delete(){
        $this->onDelete();
        static::getAdapter()->delete($this);
        $this->onAfterDelete();
    }

    /**
     * Update object
     */
    public function update(){
        $this->onUpdate();
        static::getAdapter()->update($this);
        $this->onAfterUpdate();
    }

    /**
     * Invoked before the object is created
     */
    public function onCreate(){

    }

    /**
     * Invoked after the object is created
     */
    public function onAfterCreate(){

    }

    /**
     * Invoked before the object is updated
     */
    public function onUpdate(){

    }

    /**
     * Remove all caches for the object
     */
    protected function clearInstanceCache(){
        $mem = Edge::app()->cache;
        $logger = Edge::app()->logger;
        $index = $this->getInstanceIndexKey();
        $list = $mem->get($index);
        if($list && count($list) > 0){
            foreach($list as $item){
                $mem->delete($item);
                $logger->info('deleting from cache item ' . $item);
            }
            $mem->delete($index);
            $logger->info('deleting from cache index ' . $index);
        }
    }

    /**
     * Invoked after the object is updated
     * Update any cached versions of the instance
     */
    public function onAfterUpdate(){
        $this->clearInstanceCache();
    }

    /**
     * Invoked before the object is deleted
     */
    public function onDelete(){

    }

    /**
     * Invoked after the object is deleted
     * Delete any cached versions of the instance
     */
    public function onAfterDelete(){
        $this->clearInstanceCache();
    }
}