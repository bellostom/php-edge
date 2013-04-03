<?php
namespace Framework\Models;

use Framework\Core;
use Framework\Core\Interfaces;
use Framework\Core\Database;
use Framework\Core\Cache\MemoryCache;

function getPublicObjectVars($obj) {
  return get_object_vars($obj);
}

/*
BASE CLASS FOR ALL DB TABLES
*/
class Table implements Interfaces\EventHandler, Interfaces\Cacheable {
	/**
	 * Initialize object
	 * Accept an assoc array (table row or posted data), representing
	 * one row of the object's subclass table.
	 * Loop through the class variables and assign
	 * the values of the array
	 *
	 * @param array $data assoc array
	 * @return void
	 */
	public function __construct(array &$data) {
		$this->__updateMembers($data);
	}

	public static function getTable() {
		return static::$table['table'];
	}

	/********** CACHABLE INTERFACE *********************/

    public static function useCache(){
        $settings = Core\Settings::getInstance();
        return $settings->use_cache;
    }

	/**
	 *
	 * Return a namespace(instance specific).
	 * In this NS all cached keys referring
	 * to the instance are stored, in order
	 * to easilly invalidate them when needed
	 *
	 */
	public function getInstanceIndexKey() {
		$keys = array_keys(static::$table['PK']);
		$v = array();
		foreach($keys as $key){
			if($this->$key == '')
				throw new AppException("The instance's PK's variables must be set
										before calling getInstanceIndexKey");
			$v[] = $this->$key;
		}
		return sprintf("%s:%s", static::getTable(),
								implode(':', $v));
	}

	/**
	 *
	 * Store the cached key to the instance index.
	 * After we store the value to memcached, we
	 * also store the key to the instance index.
	 * @param string $cached_key
	 */
	public function addKeyToIndex($cached_key) {
		$mem = MemoryCache::getInstance();
		$key = $this->getInstanceIndexKey();
		$index = $mem->get($key);
		if(!$index)
			$index = array();
		$index[] = $cached_key;
		$index = array_unique($index);
		$mem->add($key, $index);
	}

	/********** END CACHABLE INTERFACE *********************/

	/**
	 *
	 * This method is used as a callback to the
	 * McacheMutex method.
	 * @param MemoryCache $cache
	 * @param string $key
	 */
	public static function getByCacheKey(MemoryCache $cache, $key) {
		return $cache->get($key);
	}

	public static function query($query, $is_multi=false, $cache_attrs=false) {
		$class = get_called_class();
		if((!$is_multi && static::useCache()) || $cache_attrs){
			$cache = MemoryCache::getInstance();
			$key = md5($query);
			$data = $cache->get($key);
			if($data){
				if(!$is_multi){
					return new $class($data);
				}
				return new CachedObjectSet($data, $class);
			}
		}
		$db = Database\DB::getInstance();
		$rs = $db->db_query($query);
		$num = $db->db_num_rows($rs);

		if($num == 0){
			if(!$is_multi) {
				return null;
			}
			return new ResultSet();
		}

		if($num == 1 && !$is_multi){
			$data = $db->db_fetch_array($rs);
			$instance = new $class($data);
			if(static::useCache()){
				static::cacheResult($cache, $key, $data, $instance);
			}
			return $instance;
		}
		if($cache_attrs){
			$cc = array();
			while($row = $db->db_fetch_array($rs)){
				$cc[] = $row;
			}
            static::cacheResult($cache, $key, $cc, null, $cache_attrs['ttl']);
			return new CachedObjectSet($cc, $class);
		}
		return new ObjectSet($rs, $class);
	}

	private static function cacheResult(MemoryCache $cache, $key, $data,
										Table $instance=null, $ttl=0) {
		$callback = array(
			'method' => array('Table', 'getByCacheKey'),
			'args' => array($cache, $key)
		);

		$lock = new McacheMutex($key.".lock");
		try{
			$lock->get_lock($callback);
		}catch(MaxAttemptsExceeded $e){
			return;
		}catch(CacheExists $e){
			return;
		}
		$cache->add($key, $data, 0, $ttl);
		if(!is_null($instance)){
			$instance->addKeyToIndex($key);
		}
		$lock->release_lock();
	}

	private function __updateMembers(array &$data) {
		$members = getPublicObjectVars($this);
		foreach ($members as $k=>$v)
			if (array_key_exists($k, $data))
				$this->$k = $data[$k];
	}

	public function updateMembers(array &$data) {
		$this->__updateMembers($data);
	}

	public function on_create(){}
	public function on_after_create(){}
	public function on_update(){}

	/**
	 * Update all cached items with the
	 * updated attributes. In case any of the items
	 * that we have in the index, was evicted from the cache
	 * we update the index key, removing any items not found
	 * @see framework/core/EventHandler::on_after_update()
	 */
	public function on_after_update() {
		if(static::useCache()){
			$mem = MemoryCache::getInstance();
			$index = $this->getInstanceIndexKey();
			$list = $mem->get($index);
			$notFound = array();
			if($list && count($list) > 0){
				$data = $this->getAttrs();
				foreach($list as $item){
					try{
						$mem->replace($item, $data);
					}catch(AppException $e){
						$notFound[] = $item;
					}
				}

				if(count($notFound) > 0){
					Logger::log('Updating memcache index key ' . $index);
					//refetch the list in case another thread
					//modified it
					$list = $mem->get($index);
					$newList = array_diff($list, $notFound);
					$mem->replace($index, $newList);
				}
			}
		}
	}
	public function on_delete(){}

	/**
	 * Delete all cached items for the instance
	 * @see framework/core/EventHandler::on_after_delete()
	 */
	public function on_after_delete() {
		if(static::useCache()){
			$mem = MemoryCache::getInstance();
			$index = $this->getInstanceIndexKey();
			$list = $mem->get($index);
			if($list && count($list) > 0){
				foreach($list as $item){
					$mem->delete($item);
					Logger::log('deleting from cache '.$item);
				}
				$mem->delete($index);
				Logger::log('deleting from cache '.$index);
			}
		}
	}

	public final function __set($name, $val) {
		$attrs = $this->getAttrs();
		if (in_array($name, $attrs)){
			$this->$name = $val;
		}
	}

	public final function getAttrs() {
		$data = getPublicObjectVars($this);
		unset($data['table']);
		return $data;
	}

	/**
	 * Delete object
	 *
	 * @return bool
	 */
	public function delete() {
		$this->on_delete();
		$db = Database\WriteDB::getInstance();
		$q = "DELETE FROM ".static::getTable()." WHERE ". $this->getPkValues();
		if(Context::$autoCommit){
			$db->start_transaction();
		}
		$db->db_query($q);
		$this->on_after_delete();
	}

	/**
	 * Save or update object
	 * @param bool $forceSave
	 * @return bool
	 */
	public function save($forceSave = false) {
		$key = $this->getFirstPk();
		if (($key && $this->$key == '') || $forceSave){
			$this->doSave();
		}else{
			$this->update();
		}
	}

	/**
	 * Save object to DB
	 * Walk through the object's attrs
	 * and escape them
	 *
	 * @return bool
	 */
	private function doSave() {
		$this->on_create();
		$this->setPkValues();
		$data = $this->getAttrs();
		try{
			array_walk($data,array($this, 'escape'));
		}
		catch(InvalidValue $e){
			throw new AppException($e->getMessage());
		}
		$db = Database\WriteDB::getInstance();
		if(Context::$autoCommit){
			$db->start_transaction();
		}
		$db->db_query($this->getInsertQuery($data));
		$this->on_after_create();
	}

	public function getInsertQuery(array &$data) {
		$q = "INSERT INTO ".static::getTable()." (";
		$q .= join(",", array_keys($data)).") VALUES(";
		$q .= join(",", array_values($data)).")";
		return $q;
	}

	/**
	 * Iterate through the table's
	 * PK(s) and assign to each one
	 * the corresponding object value,
	 * imploding them finally to be used
	 * as a WHERE SQL clause
	 *
	 * @return string
	 */
	private function getPkValues() {
		$ret = array();
		foreach (static::$table['PK'] as $key=>$val)
		{
			if (DataMapping::getDataType(static::getTable(), $key) == 'string')
				$ret[] = "$key = '".$this->$key."'";
			else
				$ret[] = "$key = ".$this->$key;
		}
		return implode(" AND ", $ret);
	}

	/**
	 * Return the first PK
	 *
	 * @return string
	 */
	private function getFirstPk() {
		$keys = array_keys(static::$table['PK']);
        if(count($keys) > 0) {
		    return $keys[0];
        }
        return false;
	}

	/**
	 * Set the values of the defined PK(s)
	 * If the pk's value is defined as null
	 * in the object's definition, then
	 * the corresponding value of its
	 * class member is assigned to it
	 *
	 * @return void
	 */
	protected final function setPkValues() {
		foreach (static::$table['PK'] as $key=>$val)
		{
			if (is_null($val))
				$this->$key = $this->$key;
			else
				$this->$key = $val;
		}
	}

	/**
	 * Update object to DB
	 * Walk through the object's attrs
	 * and escape them
	 *
	 * @return bool
	 */
	protected function update() {
		$this->on_update();
		$pk = $this->getPkValues();
		$data = $this->getAttrs();

		foreach (static::$table['PK'] as $key=>$val)
			unset($data[$key]);
		try{
			array_walk($data, array($this, 'escape'));
		}
		catch(InvalidValue $e){
			throw new AppException($e->getMessage());
		}
		$k = array_keys($data);
		$v = array_values($data);
		$c = join(",", array_map(array($this, 'joinAll'), $k, $v));
		$q = "UPDATE ".static::getTable()." SET $c WHERE $pk";
		$db = Database\WriteDB::getInstance();
		if(Context::$autoCommit){
			$db->start_transaction();
		}
		$db->db_query($q);
		$this->on_after_update();
	}

	/**
	 * Walk $this->attrs array
	 * and return a set of $k=$v
	 * to be used in an update stmt
	 *
	 * @param $k string array key
	 * @param $v string array value
	 * @return string
	 */
	protected function joinAll($k, $v) {
		return "$k = $v";
	}

	/**
	 * Walk $this->attrs array
	 * and return a set of $k=$v
	 * to be used in an update stmt
	 *
	 * @param $k string array key
	 * @param $v string array value
	 * @return void
	 */
	public function escape(&$value, $key) {
		$db = Database\DB::getInstance();
		$type = DataMapping::getDataType(static::getTable(), $key);
		if ($type == 'string'){
			if ($value == 'null' || $value==''){
				$value = 'NULL';
			}else{
				$value = "'".$db->db_escape_string($value)."'";
			}
		}else{
			$value = ($value == '' || is_null($value))?'NULL':$db->db_escape_string($value);
		}
	}

}

class DataMapping {
	private static $mapArray = array(
					'integer' => array(MYSQLI_TYPE_DECIMAL,
                                        MYSQLI_TYPE_NEWDECIMAL,
                                        MYSQLI_TYPE_SHORT,
                                        MYSQLI_TYPE_LONG,
                                        MYSQLI_TYPE_FLOAT,
                                        MYSQLI_TYPE_LONGLONG,
                                        MYSQLI_TYPE_INT24,
                                        MYSQLI_TYPE_DOUBLE,
                                        MYSQLI_TYPE_TINY),
					'string' => array(MYSQLI_TYPE_VAR_STRING,
                                        MYSQLI_TYPE_STRING,
                                        MYSQLI_TYPE_ENUM,
                                        MYSQLI_TYPE_DATETIME,
                                        MYSQLI_TYPE_TIMESTAMP,
                                        MYSQLI_TYPE_NEWDATE,
                                        MYSQLI_TYPE_INTERVAL,
                                        MYSQLI_TYPE_YEAR,
                                        MYSQLI_TYPE_DATE,
                                        MYSQLI_TYPE_TIME,
                                        MYSQLI_TYPE_BLOB,
                                        MYSQLI_TYPE_LONG_BLOB,
                                        MYSQLI_TYPE_MEDIUM_BLOB,
                                        MYSQLI_TYPE_CHAR)
	);

	private static $metadata_cache = array();

	public static function getMetadataByTable($table) {
		if(!array_key_exists($table, self::$metadata_cache)){
			$db = Database\DB::getInstance();
			self::$metadata_cache[$table] = $db->db_metadata($table);
		}
		return self::$metadata_cache[$table];
	}

    public static function columnHasFlag($table, $column, $flag) {
        $table = DataMapping::getMetadataByTable($table);
        return $table[$column][1] & $flag;
    }

	private static function getDbType($table, $column) {
		$table = DataMapping::getMetadataByTable($table);
		return $table[$column][0];
	}

	public static function getDataType($table, $column) {
		$columnType = self::getDbType($table, $column);
		foreach (self::$mapArray as $key=>$val)
			if (in_array($columnType, $val))
				return $key;
		return null;
	}
}
?>