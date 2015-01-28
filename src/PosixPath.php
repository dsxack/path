<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace dsxack\Path;


class PosixPath extends AbstractPathAdapter {

    /**
     * @param string $path
     *
     * @return string
     */
    public static function normalize($path) {
        $isAbsolute = static::isAbsolute($path);
        $trailingSlash = substr($path, -1) === '/';

        // Normalize the path
        $path = implode('/', static::normalizeArray(preg_split('/\//', $path), !$isAbsolute));

        if (!$path && !$isAbsolute) {
            $path = '.';
        }
        if ($path && $trailingSlash) {
            $path .= '/';
        }

        return ($isAbsolute ? '/' : '') . $path;
    }

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     * @return string
     *
     * @throws Exception
     */
    public static function join($path1, $path2 = null, $_ = null) {
        $path = '';
        $args = func_get_args();

        for ($i = 0; $i < count($args); $i++) {
            $segment = $args[$i];

            if (!is_string($segment)) {
                throw new Exception('Arguments to path.join must be strings');
            }

            if ($segment) {
                if (!$path) {
                    $path .= $segment;
                } else {
                    $path .= '/' . $segment;
                }
            }
        }
        return static::normalize($path);
    }

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     * @return string
     * @throws Exception
     */
    public static function resolve($path1, $path2 = null, $_ = null) {
        $resolvedPath = '';
        $resolvedAbsolute = false;
        $arguments = func_get_args();

        for ($i = count($arguments) - 1; $i >= -1 && !$resolvedAbsolute; $i--) {
            $path = ($i >= 0) ? $arguments[$i] : getcwd();

            // Skip empty and invalid entries
            if (!is_string($path)) {
                throw new Exception('Arguments to path.resolve must be strings');
            } else if (!$path) {
                continue;
            }

            $resolvedPath = $path . '/' . $resolvedPath;
            $resolvedAbsolute = substr($path, 0, 1) === '/';
        }

        // At this point the path should be resolved to a full absolute path, but
        // handle relative paths to be safe (might happen when process.cwd() fails)

        // Normalize the path
        $resolvedPath = implode('/', static::normalizeArray(preg_split('/\//', $resolvedPath), !$resolvedAbsolute));

        return (($resolvedAbsolute ? '/' : '') . $resolvedPath) ?: '.';
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    public static function relative($from, $to) {
        $from = substr(static::resolve($from), 1);
        $to = substr(static::resolve($to), 1);

        $trim = function ($arr) {
            $start = 0;
            for (; $start < count($arr); $start++) {
                if ($arr[$start] !== '') {
                    break;
                }
            }

            $end = count($arr) - 1;
            for (; $end >= 0; $end--) {
                if ($arr[$end] !== '') {
                    break;
                }
            }

            if ($start > $end) {
                return array();
            }

            return array_slice($arr, $start, $end + 1);
        };

        $fromParts = $trim(preg_split('/\//', $from));
        $toParts = $trim(preg_split('/\//', $to));

        $length = min(count($fromParts), count($toParts));
        $samePartsLength = $length;
        for ($i = 0; $i < $length; $i++) {
            if ($fromParts[$i] !== $toParts[$i]) {
                $samePartsLength = $i;
                break;
            }
        }

        $outputParts = array();
        for ($i = $samePartsLength; $i < count($fromParts); $i++) {
            $outputParts[] = '..';
        }

        $outputParts = array_merge($outputParts, array_slice($toParts, $samePartsLength));

        return implode('/', $outputParts);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function isAbsolute($path) {
        return substr($path, 0, 1) === '/';
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function dirname($path) {
        $result = static::splitPath($path);
        $root = $result[0];
        $dir = $result[1];

        if (!$root && !$dir) {
          // No dirname whatsoever
          return '.';
        }

        if ($dir) {
          // It has a dirname, strip trailing slash
          $dir = substr($dir, 0, strlen($dir) - 1);
        }

        return $root . $dir;
    }

    /**
     * @param $path
     * @param $ext
     *
     * @return string
     */
    public static function basename($path, $ext = null) {
        $result = static::splitPath($path);
        $f = $result[2];
        // TODO: make this comparison case-insensitive on windows?
        if ($ext && substr($f, -1 * strlen($ext)) === $ext) {
            $f = substr($f, 0, strlen($f) - strlen($ext));
        }
        return $f;
    }

    /**
     * @param $path
     *
     * @return string
     */
    public static function extname($path) {
        $result = static::splitPath($path);

        return $result[3];
    }

    /**
     * @return string
     */
    public static function sep() {
        return '/';
    }

    /**
     * @return string
     */
    public static function delimiter() {
        return ':';
    }

    public static function parse($path) {
        if (!is_string($path)) {
            throw new Exception("Parameter 'pathString' must be a string, not " . gettype($path));
        }

        $allParts = static::splitPath($path);

        if (!$allParts || count($allParts) !== 4) {
            throw new Exception("Invalid path '" . $path . "'");
        }

        $allParts[1] = $allParts[1] ?: '';
        $allParts[2] = $allParts[2] ?: '';
        $allParts[3] = $allParts[3] ?: '';

        return array(
            'root' => $allParts[0],
            'dir' => $allParts[0] . substr($allParts[1], 0, -1),
            'base' => $allParts[2],
            'ext' => $allParts[3],
            'name' => substr($allParts[2], 0, count($allParts[2]) - count($allParts[3]))
        );
    }

    public static function format($params) {
        if (!is_array($params)) {
            throw new Exception("Parameter 'pathObject' must be an object, not " . gettype($params));
        }

        $root = $params['root'] ?: '';

        if (!is_string($root)) {
            throw new Exception("'pathObject.root' must be a string or undefined, not " . gettype($params['root']));
        }

        $dir = $params['dir'] ? $params['dir'] . static::sep() : '';
        $base = $params['base'] ?: '';
        return $dir . $base;
    }

    private static function splitPath($path) {
        preg_match(static::PATTERN_SPLIT_PATH, $path, $matches);

        return array_slice($matches, 1);
    }
}