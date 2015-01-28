<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace dsxack\Path;

interface PathAdapterInterface {
    /**
     * @param string $path
     *
     * @return string
     */
    public static function normalize($path);

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     *
     * @return string
     */
    public static function join($path1, $path2 = null, $_ = null);

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     *
     * @return string
     */
    public static function resolve($path1, $path2 = null, $_ = null);

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    public static function relative($from, $to);

    /**
     * @param $path
     *
     * @return bool
     */
    public static function isAbsolute($path);


    /**
     * @param $path
     *
     * @return string
     */
    public static function dirname($path);

    /**
     * @param $path
     * @param $ext
     *
     * @return string
     */
    public static function basename($path, $ext = null);

    /**
     * @param $path
     *
     * @return string
     */
    public static function extname($path);

    /**
     * @return string
     */
    public static function sep();

    /**
     * @return string
     */
    public static function delimiter();

    public static function parse($path);

    public static function format($params);
}