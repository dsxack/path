<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace dsxack\Path;


abstract class AbstractPathAdapter implements PathAdapterInterface {
    const PATTERN_SPLIT_DEVICE = "/^([a-zA-Z]:|[\\\\\\/]{2}[^\\\\\\/]+[\\\\\\/]+[^\\\\\\/]+)?([\\\\\\/])?([\\s\\S]*?)$/";
    const PATTERN_SPLIT_TAIL = "/^([\\s\\S]*?)((?:\\.{1,2}|[^\\\\\\/]+?|)(\\.[^.\\/\\\\]*|))(?:[\\\\\\/]*)$/";
    const PATTERN_SPLIT_PATH = "/^(\\/?|)([\\s\\S]*?)((?:\\.{1,2}|[^\\/]+?|)(\\.[^.\\/]*|))(?:[\\/]*)$/";

    public static function className() {
        return get_called_class();
    }

    protected static function normalizeArray($parts, $allowAboveRoot) {
        $res = array();

        for ($i = 0; $i < count($parts); $i++) {
            $p = $parts[$i];

            // ignore empty parts
            if (!$p || $p === '.') {
                continue;
            }

            if ($p === '..') {
                if (count($res) && $res[count($res) - 1] !== '..') {
                    array_pop($res);
                } elseif ($allowAboveRoot) {
                    array_push($res, '..');
                }
            } else {
                array_push($res, $p);
            }
        }

        return $res;
    }

    protected static function normalizeUNCRoot($device) {
        $device = preg_replace("/^[\\\\\\/]+/", '', $device);
        $device = preg_replace("/[\\\\\\/]+/", '\\', $device);

        return '\\\\' . $device;
    }
}