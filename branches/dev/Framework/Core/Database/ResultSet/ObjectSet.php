<?php
namespace Framework\Core\Database\ResultSet;

use Framework\Core\Database\DB;

class ObjectSet Extends ResultSet implements \ArrayAccess {
	public $currentIndex;
	protected $result;
	public $totalRows = 0;
	protected $class_name;

	public function __construct($rs, $object){
		$this->currentIndex = 0;
        $this->result = $rs;
		$this->class_name = $object;
		$this->setRows();
	}

	public function toArray(){
		return iterator_to_array($this, true);
	}

	public function apply($fn){
		iterator_apply($this, $fn, array($this));
	}

	protected function setRows(){
		$db = DB::getInstance();
        $this->totalRows = $db->db_num_rows($this->result);
	}

	public function offsetExists($offset){
		return ($offset < $this->totalRows);
	}

	public function slice($start, $length){
		return new Slice($this, $start, $length);
	}

	public function offsetGet($offset){
		$db = DB::getInstance();
		$db->db_seek($this->result, $offset);
        $row = $db->db_fetch_array($this->result);
		return new $this->class_name($row);
	}

	public function offsetSet($offset,$value){
		throw new \Exception("This collection is read only.");
	}

	public function offsetUnset($offset){
		throw new \Exception("This collection is read only.");
	}

	public function count(){
		return $this->totalRows;
	}

	public function current(){
		return $this->offsetGet($this->currentIndex);
	}

	public function key(){
		return $this->currentIndex;
	}

	public function next(){
		return $this->currentIndex++;
	}

	public function rewind(){
		$this->currentIndex = 0;
	}

	public function valid(){
		return $this->offsetExists($this->currentIndex);
	}

	public function append($value){
		throw new \Exception("This collection is read only");
	}
}
?>