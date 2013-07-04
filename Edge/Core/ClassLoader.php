<?php
class ClassLoader{
    private $namespaces = array();
    private $prefixes = array();
    private $namespaceFallbacks = array();
    private $prefixFallbacks = array();
    private $useIncludePath = false;
    private $refreshed = false;
    private $classList = array();
    private static $SAVE_FILE;
    public $basePath;

    public function __construct(){
        $this->basePath = realpath(dirname(__DIR__)."/../");
        static::$SAVE_FILE = __DIR__.'/_Modules.php';
        $this->registerNamespaces(array(
            'Edge' => $this->basePath,
            'Monolog' => $this->basePath."/Edge/Core/Logger"
        ));
    }

    private function initClassList(){
        if (file_exists(self::$SAVE_FILE)) {
            require_once(self::$SAVE_FILE);
            $this->refreshed = FALSE;
        }
    }

    /**
     * Turns on searching the include for class files. Allows easy loading
     * of installed PEAR packages
     *
     * @param Boolean $useIncludePath
     */
    public function useIncludePath($useIncludePath)
    {
        $this->useIncludePath = $useIncludePath;
    }

    /**
     * Can be used to check if the autoloader uses the include path to check
     * for classes.
     *
     * @return Boolean
     */
    public function getUseIncludePath()
    {
        return $this->useIncludePath;
    }

    /**
     * Gets the configured namespaces.
     *
     * @return array A hash with namespaces as keys and directories as values
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }

    /**
     * Gets the configured class prefixes.
     *
     * @return array A hash with class prefixes as keys and directories as values
     */
    public function getPrefixes()
    {
        return $this->prefixes;
    }

    /**
     * Gets the directory(ies) to use as a fallback for namespaces.
     *
     * @return array An array of directories
     */
    public function getNamespaceFallbacks()
    {
        return $this->namespaceFallbacks;
    }

    /**
     * Gets the directory(ies) to use as a fallback for class prefixes.
     *
     * @return array An array of directories
     */
    public function getPrefixFallbacks()
    {
        return $this->prefixFallbacks;
    }

    /**
     * Registers the directory to use as a fallback for namespaces.
     *
     * @param array $dirs An array of directories
     *
     * @api
     */
    public function registerNamespaceFallbacks(array $dirs)
    {
        $this->namespaceFallbacks = $dirs;
    }

    /**
     * Registers a directory to use as a fallback for namespaces.
     *
     * @param string $dir A directory
     */
    public function registerNamespaceFallback($dir)
    {
        $this->namespaceFallbacks[] = $dir;
    }

    /**
     * Registers directories to use as a fallback for class prefixes.
     *
     * @param array $dirs An array of directories
     *
     * @api
     */
    public function registerPrefixFallbacks(array $dirs)
    {
        $this->prefixFallbacks = $dirs;
    }

    /**
     * Registers a directory to use as a fallback for class prefixes.
     *
     * @param string $dir A directory
     */
    public function registerPrefixFallback($dir)
    {
        $this->prefixFallbacks[] = $dir;
    }

    /**
     * Registers an array of namespaces
     *
     * @param array $namespaces An array of namespaces (namespaces as keys and locations as values)
     *
     * @api
     */
    public function registerNamespaces(array $namespaces)
    {
        foreach ($namespaces as $namespace => $locations) {
            $this->namespaces[$namespace] = (array) $locations;
        }
    }

    /**
     * Registers a namespace.
     *
     * @param string       $namespace The namespace
     * @param array|string $paths     The location(s) of the namespace
     *
     * @api
     */
    public function registerNamespace($namespace, $paths)
    {
        $this->namespaces[$namespace] = (array) $paths;
    }

    /**
     * Registers an array of classes using the PEAR naming convention.
     *
     * @param array $classes An array of classes (prefixes as keys and locations as values)
     *
     * @api
     */
    public function registerPrefixes(array $classes)
    {
        foreach ($classes as $prefix => $locations) {
            $this->prefixes[$prefix] = (array) $locations;
        }
    }

    /**
     * Registers a set of classes using the PEAR naming convention.
     *
     * @param string       $prefix The classes prefix
     * @param array|string $paths  The location(s) of the classes
     *
     * @api
     */
    public function registerPrefix($prefix, $paths)
    {
        $this->prefixes[$prefix] = (array) $paths;
    }

    /**
     * Registers this instance as an autoloader.
     *
     * @param Boolean $prepend Whether to prepend the autoloader or not
     *
     * @api
     */
    public function register($prepend = false)
    {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Loads the given class or interface.
     *
     * @param string $class The name of the class
     *
     * @return Boolean|null True, if loaded
     */
    public function loadClass($class){
        if(count($this->classList) == 0){
            $this->initClassList();
        }
        if (!array_key_exists($class, $this->classList) && !$this->refreshed)
            $this->refreshClassList();
        if (!array_key_exists($class, $this->classList))
            throw new \ReflectionException("Class $class not found");
        require_once($this->classList[$class]);
    }

    private function refreshClassList(){
        $this->__scanDirs();
        $this->refreshed = TRUE;
        $this->saveClassList();
    }

    private function saveClassList(){
        ksort($this->classList);
        $handle = fopen(self::$SAVE_FILE, 'w');
        fwrite($handle, "<?php\r\n");

        foreach($this->classList as $class => $path){
            $line = '$this->classList' . "['" . $class . "'] = '" . $path . "';\r\n";
            fwrite($handle, $line);
        }

        fwrite($handle, '?>');
        fclose($handle);
    }

    private function __scanDirs(){
        $__exclude = array_values($this->namespaces);
        $exclude = array();
        foreach($__exclude as $k=>$v){
            $exclude[] = $v[0];
        }
        foreach ($this->namespaces as $ns => $dirs) {
            unset($exclude[$dirs]);
            foreach ($dirs as $dir) {
                $rs = $this->scanDirectory($dir.DIRECTORY_SEPARATOR.$ns, $dir, $exclude);
                $this->classList = array_merge($rs, $this->classList);
            }
        }
    }

    private function scanDirectory($directory, $baseDir, $exclude){
        if(in_array($directory, $exclude)){
            return array();
        }
        if (substr($directory, -1) == '/')
            $directory = substr($directory, 0, -1);

        if (!file_exists($directory) || !is_dir($directory) || !is_readable($directory))
            return array();

        $dirH = opendir($directory);
        $scanRes = array();

        while(($file = readdir($dirH)) !== FALSE){
            if (strcmp($file , '.') == 0 || strcmp($file , '..') == 0 || $file == '.svn')
                continue;

            $path = $directory . '/' . $file;

            if (!is_readable($path))
                continue;

            // recursion
            if (is_dir($path)){
                $scanRes = array_merge($scanRes, $this->scanDirectory($path, $baseDir, $exclude));

            } elseif (is_file($path)){
                $className = explode('.', $file);
                $ns = substr(str_replace($baseDir, "", $directory), 1);
                $ns = str_replace(DIRECTORY_SEPARATOR, "\\", $ns). "\\". $className[0];
                if (strcmp($className[1], 'php') == 0 ) {
                    $scanRes[$ns] = $path;
                }
            }
        }
        return $scanRes;
    }

    /**
     * Finds the path to the file where the class is defined.
     *
     * @param string $class The name of the class
     *
     * @return string|null The path, if found
     */
    public function findFile($class)
    {
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $namespace = substr($class, 0, $pos);
            $className = substr($class, $pos + 1);
            $normalizedClass = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className).'.php';
            foreach ($this->namespaces as $ns => $dirs) {
                if (0 !== strpos($namespace, $ns)) {
                    continue;
                }

                foreach ($dirs as $dir) {
                    $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }

            foreach ($this->namespaceFallbacks as $dir) {
                $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                if (is_file($file)) {
                    return $file;
                }
            }

        } else {
            // PEAR-like class name
            $normalizedClass = str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';
            foreach ($this->prefixes as $prefix => $dirs) {
                if (0 !== strpos($class, $prefix)) {
                    continue;
                }

                foreach ($dirs as $dir) {
                    $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                    if (is_file($file)) {
                        return $file;
                    }
                }
            }

            foreach ($this->prefixFallbacks as $dir) {
                $file = $dir.DIRECTORY_SEPARATOR.$normalizedClass;
                if (is_file($file)) {
                    return $file;
                }
            }
        }

        if ($this->useIncludePath && $file = stream_resolve_include_path($normalizedClass)) {
            return $file;
        }
    }
}
