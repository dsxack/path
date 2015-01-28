<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

namespace dsxack\Path;


class Win32Path extends AbstractPathAdapter {

    /**
     * @param string $path
     *
     * @return string
     */
    public static function normalize($path) {
        preg_match(static::PATTERN_SPLIT_DEVICE, $path, $result);

        $device = $result[1] ?: '';
        $isUnc = $device && substr($device, 1, 1) !== ':';
        $isAbsolute = static::isAbsolute($path);
        $tail = $result[3];
        $trailingSlash = preg_match("/[\\\\\\/]$/", $tail);

        // Normalize the tail path
        $tail = implode('\\', static::normalizeArray(preg_split("/[\\\\\\/]+/", $tail), !$isAbsolute));

        if (!$tail && !$isAbsolute) {
          $tail = '.';
        }
        if ($tail && $trailingSlash) {
          $tail .= '\\';
        }

        // Convert slashes to backslashes when `device` points to an UNC root.
        // Also squash multiple slashes into a single one where appropriate.
        if ($isUnc) {
          $device = static::normalizeUNCRoot($device);
        }

        return $device . ($isAbsolute ? '\\' : '') . $tail;
    }

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     *
     * @return string
     */
    public static function join($path1, $path2 = null, $_ = null) {
        $paths = array_filter(func_get_args(), function ($p) {
            if (!is_string($p)) {
                throw new Exception('Arguments to path.join must be strings');
            }
            return $p;
        });

        $joined = implode('\\', $paths);

        // Make sure that the joined path doesn't start with two slashes, because
        // normalize() will mistake it for an UNC path then.
        //
        // This step is skipped when it is very clear that the user actually
        // intended to point at an UNC path. This is assumed when the first
        // non-empty string arguments starts with exactly two slashes followed by
        // at least one more non-slash character.
        //
        // Note that for normalize() to treat a path as an UNC path it needs to
        // have at least 2 components, so we don't filter for that here.
        // This means that the user can use join to construct UNC paths from
        // a server name and a share name; for example:
        //   path.join('//server', 'share') -> '\\\\server\\share\')
        if (!preg_match("/^[\\\\\\/]{2}[^\\\\\\/]/.test(paths[0]/", $paths[0])) {
            $joined = preg_replace("/^[\\\\\\/]{2,}/", '\\', $joined);
        }

        return static::normalize($joined);
    }

    /**
     * @param string $path1
     * @param string $path2
     * @param string $_
     * @return string
     * @throws Exception
     */
    public static function resolve($path1, $path2 = null, $_ = null) {
        $resolvedDevice = '';
        $resolvedTail = '';
        $resolvedAbsolute = false;
        $arguments = func_get_args();

        for ($i = count($arguments) - 1; $i >= -1; $i--) {
            if ($i >= 0) {
                $path = $arguments[$i];
            } elseif (!$resolvedDevice) {
                $path = getcwd();
            } else {
                // Windows has the concept of drive-specific current working
                // directories. If we've resolved a drive letter but not yet an
                // absolute path, get cwd for that drive. We're sure the device is not
                // an unc path at this points, because unc paths are always absolute.
                $path = getenv('=' . $resolvedDevice);
                // Verify that a drive-local cwd was found and that it actually points
                // to our drive. If not, default to the drive's root.
                if (!$path || strtolower(substr($path, 0, 3)) !== strtolower($resolvedDevice) . '\\') {
                    $path = $resolvedDevice . '\\';
                }
            }

            // Skip empty and invalid entries
            if (!is_string($path)) {
                throw new Exception('Arguments to path.resolve must be strings');
            } elseif (!$path) {
                continue;
            }

            preg_match(static::PATTERN_SPLIT_DEVICE, $path, $result);
            $device = $result[1] ?: '';
            $isUnc = $device && substr($device, 1, 1) !== ':';
            $isAbsolute = static::isAbsolute($path);
            $tail = $result[3];

            if ($device &&
                $resolvedDevice &&
                strtolower($device) !== strtolower($resolvedDevice)) {
                // This path points to another device so it is not applicable
                continue;
            }

            if (!$resolvedDevice) {
                $resolvedDevice = $device;
            }

            if (!$resolvedAbsolute) {
                $resolvedTail = $tail . '\\' . $resolvedTail;
                $resolvedAbsolute = $isAbsolute;
            }

            if ($resolvedDevice && $resolvedAbsolute) {
                break;
            }
        }

        // Convert slashes to backslashes when `resolvedDevice` points to an UNC
        // root. Also squash multiple slashes into a single one where appropriate.
        /** @noinspection PhpUndefinedVariableInspection */
        if ($isUnc) {
            $resolvedDevice = static::normalizeUNCRoot($resolvedDevice);
        }

        // At this point the path should be resolved to a full absolute path,
        // but handle relative paths to be safe (might happen when process.cwd()
        // fails)

        // Normalize the tail path
        $resolvedTail = implode('\\', static::normalizeArray(preg_split("/[\\\\\\/]+/", $resolvedTail), !$resolvedAbsolute));

        return ($resolvedDevice . ($resolvedAbsolute ? '\\' : '') . $resolvedTail) ?: '.';
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return string
     */
    public static function relative($from, $to) {
        $from = static::resolve($from);
        $to = static::resolve($to);

        // windows is not case sensitive
        $lowerFrom = strtolower($from);
        $lowerTo = strtolower($to);

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

        $toParts = $trim(preg_split("/\\\\/", $to));

        $lowerFromParts = $trim(preg_split("/\\\\/", $lowerFrom));
        $lowerToParts = $trim(preg_split("/\\\\/", $lowerTo));

        $length = min(count($lowerFromParts), count($lowerToParts));
        $samePartsLength = $length;
        for ($i = 0; $i < $length; $i++) {
            if ($lowerFromParts[$i] !== $lowerToParts[$i]) {
                $samePartsLength = $i;
                break;
            }
        }

        if ($samePartsLength == 0) {
            return $to;
        }

        $outputParts = array();
        for ($i = $samePartsLength; $i < count($lowerFromParts); $i++) {
            $outputParts[] = '..';
        }

        $outputParts = array_merge($outputParts, array_slice($toParts, $samePartsLength));

        return implode('\\', $outputParts);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public static function isAbsolute($path) {
        preg_match(static::PATTERN_SPLIT_DEVICE, $path, $result);
        $device = $result[1] ?: '';
        $isUnc = !!$device && substr($device, 1, 1) !== ':';
        // UNC paths are always absolute
        return !!$result[2] ?: $isUnc;
    }

    /**
     * Function to split a filename into [root, dir, basename, ext]
     *
     * @param $filename
     * @return array
     */
    private static function splitPath($filename) {
        // Separate device+slash from tail
        preg_match(static::PATTERN_SPLIT_DEVICE, $filename, $result);

        $device = ($result[1] ?: '') . ($result[2] ?: '');
        $tail = $result[3] ?: '';

        // Split the tail into dir, basename and extension
        preg_match(static::PATTERN_SPLIT_TAIL, $tail, $result2);
        $dir = $result2[1];
        $basename = $result2[2];
        $ext = $result2[3];

        return array($device, $dir, $basename, $ext);
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
        return '\\';
    }

    /**
     * @return string
     */
    public static function delimiter() {
        return ';';
    }

    public static function parse($path) {
        if (!is_string($path)) {
            throw new Exception("Parameter 'pathString' must be a string, not " . gettype($path));
        }

        $allParts = static::splitPath($path);

        if (!$allParts || count($allParts) !== 4) {
            throw new Exception("Invalid path '" . $path . "'");
        }

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

        $dir = $params['dir'];
        $base = $params['base'] ?: '';

        if (substr($dir, -1) === static::sep()) {
            return $dir . $base;
        }

        if ($dir) {
            return $dir . static::sep() . $base;
        }

        return $dir . $base;
    }
}