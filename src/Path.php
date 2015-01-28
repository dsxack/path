<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace dsxack\Path;

/**
 * Class Path
 * @package dsxack\path
 *
 * @method static string normalize(string $path1, string $path2 = null, string $_ = null)
 * @method static string join(string $path1, string $path2 = null, string $_ = null)
 * @method static string resolve(string $path1, string $path2 = null, string $_ = null)
 * @method static string relative(string $from, string $to)
 * @method static string isAbsolute(string $path)
 * @method static string dirname(string $path)
 * @method static string basename(string $path, string $ext = null)
 * @method static string extname(string $path)
 * @method static string sep(string $path)
 * @method static string delimiter(string $path)
 * @method static string parse(string $path)
 * @method static array format() // TODO
 */
class Path {

    private static $_instance;

    private static function _call($method, $args) {
        return call_user_func_array(array(static::instance(), $method), $args);
    }

    public static function __callStatic($method, $args) {
        return static::_call($method, $args);
    }

    public function __call($method, $args){
        return static::_call($method, $args);
    }

    public static function instance() {
        if (isset(static::$_instance)){
            return static::$_instance;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            static::$_instance = new Win32Path();
        } else {
            static::$_instance = new PosixPath();
        }

        return static::$_instance;
    }

    public static function className() {
        return get_called_class();
    }
}

