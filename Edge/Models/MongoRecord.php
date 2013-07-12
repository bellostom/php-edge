<?php
namespace Edge\Models;

/**
 * Class MongoRecord
 * @package Edge\Models
 */
abstract class MongoRecord extends Record{

    protected static $_members = array('_id');

    protected static $adapterClass = 'Edge\Models\Adapters\MongoAdapter';

    /**
     * Return an array with the primary keys
     * @return array
     */
    public static function getPk(){
        return array('_id');
    }

	public static function getItemById($id)	{
        if(!($id instanceof \MongoId)){
            $id = new \MongoId($id);
        }
        return parent::select()
                        ->where(array("_id" => $id))
                        ->fetch();
	}

    protected function belongsTo($model, $options=array()){
        if(!isset($options['fk'])){
            throw new \Edge\Core\Exceptions\EdgeException('Specify the fk attribute');
        }
        $options['value'] = $this->_id;
        return parent::belongsTo($model, $options);
    }

    /**
     * Override the base method
     * Mongo relations are a bit different than the relational
     * ones. The relation is specified within the record and not
     * on a different table. In order to resolve the dependency
     * we need to know which attribute in the class stores the
     * relationships.
     * @param $model
     * @param array $options
     * @return mixed
     * @throws \Edge\Core\Exceptions\EdgeException
     */
    protected function hasMany($model, $options=array()){
        if(!isset($options['attr'])){
            throw new \Edge\Core\Exceptions\EdgeException("The attr key is not set");
        }
        $attrName = $options['attr'];
        unset($options['attr']);
        $options['fk'] = '_id';
        $options['value'] = $this->$attrName;
        return parent::hasMany($model, $options);
    }
}