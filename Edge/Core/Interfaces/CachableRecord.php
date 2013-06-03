<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Thomas
 * Date: 3/6/2013
 * Time: 4:38 μμ
 * To change this template use File | Settings | File Templates.
 */

namespace Edge\Core\Interfaces;


interface CachableRecord {
    public static function cacheRecord();
    public static function getCacheKey(\Edge\Core\Cache\BaseCache $cache, array $args);
}