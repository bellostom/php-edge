<?php
namespace Framework\Core\Interfaces;

interface Cacheable {
    public function getInstanceIndexKey();
    public function addKeyToIndex($cached_key);
    public static function useCache();
}
?>