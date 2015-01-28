<?php
/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

use dsxack\Path\Path;
use dsxack\Path\PosixPath;
use dsxack\Path\Win32Path;

class PathTest extends PHPUnit_Framework_TestCase {
    private function _isWindows() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return true;
        }

        return false;
    }

    public function testBasename() {
        $f = __FILE__;

        $this->assertEquals('PathTest.php', Path::basename($f));
        $this->assertEquals('PathTest', Path::basename($f, '.php'));
        $this->assertEquals('', Path::basename(''));
        $this->assertEquals('basename.ext', Path::basename('/dir/basename.ext'));
        $this->assertEquals('basename.ext', Path::basename('/basename.ext'));
        $this->assertEquals('basename.ext', Path::basename('basename.ext'));
        $this->assertEquals('basename.ext', Path::basename('basename.ext/'));
        $this->assertEquals('basename.ext', Path::basename('basename.ext//'));

        // On Windows a backslash acts as a path separator.
        $this->assertEquals('basename.ext', Win32Path::basename('\\dir\\basename.ext'));
        $this->assertEquals('basename.ext', Win32Path::basename('\\basename.ext'));
        $this->assertEquals('basename.ext', Win32Path::basename('basename.ext'));
        $this->assertEquals('basename.ext', Win32Path::basename('basename.ext\\'));
        $this->assertEquals('basename.ext', Win32Path::basename('basename.ext\\\\'));

        // On unix a backslash is just treated as any other character.
        $this->assertEquals('\\dir\\basename.ext', PosixPath::basename('\\dir\\basename.ext'));
        $this->assertEquals('\\basename.ext', PosixPath::basename('\\basename.ext'));
        $this->assertEquals('basename.ext', PosixPath::basename('basename.ext'));
        $this->assertEquals('basename.ext\\', PosixPath::basename('basename.ext\\'));
        $this->assertEquals('basename.ext\\\\', PosixPath::basename('basename.ext\\\\'));

        // POSIX filenames may include control characters
        // c.f. http://www.dwheeler.com/essays/fixing-unix-linux-filenames.html
        if (!$this->_isWindows()) {
            $controlCharFilename = 'Icon' . chr(13);
            $this->assertEquals($controlCharFilename, Path::basename('/a/b/' . $controlCharFilename));
        }
    }

    public function testDirname() {
        $f = __FILE__;

        $this->assertEquals(substr(Path::dirname($f), -4), 'test');
        $this->assertEquals('/a', Path::dirname('/a/b/'));
        $this->assertEquals('/a', Path::dirname('/a/b'));
        $this->assertEquals('/', Path::dirname('/a'));
        $this->assertEquals('.', Path::dirname(''));
        $this->assertEquals('/', Path::dirname('/'));
        $this->assertEquals('/', Path::dirname('////'));

        $this->assertEquals('c:\\', Win32Path::dirname('c:\\'));
        $this->assertEquals('c:\\', Win32Path::dirname('c:\\foo'));
        $this->assertEquals('c:\\', Win32Path::dirname('c:\\foo\\'));
        $this->assertEquals('c:\\foo', Win32Path::dirname('c:\\foo\\bar'));
        $this->assertEquals('c:\\foo', Win32Path::dirname('c:\\foo\\bar\\'));
        $this->assertEquals('c:\\foo\\bar', Win32Path::dirname('c:\\foo\\bar\\baz'));
        $this->assertEquals('\\', Win32Path::dirname('\\'));
        $this->assertEquals('\\', Win32Path::dirname('\\foo'));
        $this->assertEquals('\\', Win32Path::dirname('\\foo\\'));
        $this->assertEquals('\\foo', Win32Path::dirname('\\foo\\bar'));
        $this->assertEquals('\\foo', Win32Path::dirname('\\foo\\bar\\'));
        $this->assertEquals('\\foo\\bar', Win32Path::dirname('\\foo\\bar\\baz'));
        $this->assertEquals('c:', Win32Path::dirname('c:'));
        $this->assertEquals('c:', Win32Path::dirname('c:foo'));
        $this->assertEquals('c:', Win32Path::dirname('c:foo\\'));
        $this->assertEquals('c:foo', Win32Path::dirname('c:foo\\bar'));
        $this->assertEquals('c:foo', Win32Path::dirname('c:foo\\bar\\'));
        $this->assertEquals('c:foo\\bar', Win32Path::dirname('c:foo\\bar\\baz'));
        $this->assertEquals('\\\\unc\\share', Win32Path::dirname('\\\\unc\\share'));
        $this->assertEquals('\\\\unc\\share\\', Win32Path::dirname('\\\\unc\\share\\foo'));
        $this->assertEquals('\\\\unc\\share\\', Win32Path::dirname('\\\\unc\\share\\foo\\'));
        $this->assertEquals(Win32Path::dirname('\\\\unc\\share\\foo\\bar'),
            '\\\\unc\\share\\foo');
        $this->assertEquals(Win32Path::dirname('\\\\unc\\share\\foo\\bar\\'),
            '\\\\unc\\share\\foo');
        $this->assertEquals(Win32Path::dirname('\\\\unc\\share\\foo\\bar\\baz'),
            '\\\\unc\\share\\foo\\bar');
    }

    public function testExtname() {
        $f = __FILE__;

        $this->assertEquals('.php', Path::extname($f));
        $this->assertEquals('', Path::extname(''));
        $this->assertEquals('', Path::extname('/path/to/file'));
        $this->assertEquals('.ext', Path::extname('/path/to/file.ext'));
        $this->assertEquals('.ext', Path::extname('/path.to/file.ext'));
        $this->assertEquals('', Path::extname('/path.to/file'));
        $this->assertEquals('', Path::extname('/path.to/.file'));
        $this->assertEquals('.ext', Path::extname('/path.to/.file.ext'));
        $this->assertEquals('.ext', Path::extname('/path/to/f.ext'));
        $this->assertEquals('.ext', Path::extname('/path/to/..ext'));
        $this->assertEquals('', Path::extname('file'));
        $this->assertEquals('.ext', Path::extname('file.ext'));
        $this->assertEquals('', Path::extname('.file'));
        $this->assertEquals('.ext', Path::extname('.file.ext'));
        $this->assertEquals('', Path::extname('/file'));
        $this->assertEquals('.ext', Path::extname('/file.ext'));
        $this->assertEquals('', Path::extname('/.file'));
        $this->assertEquals('.ext', Path::extname('/.file.ext'));
        $this->assertEquals('.ext', Path::extname('.path/file.ext'));
        $this->assertEquals('.ext', Path::extname('file.ext.ext'));
        $this->assertEquals('.', Path::extname('file.'));
        $this->assertEquals('', Path::extname('.'));
        $this->assertEquals('', Path::extname('./'));
        $this->assertEquals('.ext', Path::extname('.file.ext'));
        $this->assertEquals('', Path::extname('.file'));
        $this->assertEquals('.', Path::extname('.file.'));
        $this->assertEquals('.', Path::extname('.file..'));
        $this->assertEquals('', Path::extname('..'));
        $this->assertEquals('', Path::extname('../'));
        $this->assertEquals('.ext', Path::extname('..file.ext'));
        $this->assertEquals('.file', Path::extname('..file'));
        $this->assertEquals('.', Path::extname('..file.'));
        $this->assertEquals('.', Path::extname('..file..'));
        $this->assertEquals('.', Path::extname('...'));
        $this->assertEquals('.ext', Path::extname('...ext'));
        $this->assertEquals('.', Path::extname('....'));
        $this->assertEquals('.ext', Path::extname('file.ext/'));
        $this->assertEquals('.ext', Path::extname('file.ext//'));
        $this->assertEquals('', Path::extname('file/'));
        $this->assertEquals('', Path::extname('file//'));
        $this->assertEquals('.', Path::extname('file./'));
        $this->assertEquals('.', Path::extname('file.//'));

        // On windows, backspace is a path separator.
        $this->assertEquals('', Win32Path::extname('.\\'));
        $this->assertEquals('', Win32Path::extname('..\\'));
        $this->assertEquals('.ext', Win32Path::extname('file.ext\\'));
        $this->assertEquals('.ext', Win32Path::extname('file.ext\\\\'));
        $this->assertEquals('', Win32Path::extname('file\\'));
        $this->assertEquals('', Win32Path::extname('file\\\\'));
        $this->assertEquals('.', Win32Path::extname('file.\\'));
        $this->assertEquals('.', Win32Path::extname('file.\\\\'));

        // On unix, backspace is a valid name component like any other character.
        $this->assertEquals('', PosixPath::extname('.\\'));
        $this->assertEquals('.\\', PosixPath::extname('..\\'));
        $this->assertEquals('.ext\\', PosixPath::extname('file.ext\\'));
        $this->assertEquals('.ext\\\\', PosixPath::extname('file.ext\\\\'));
        $this->assertEquals('', PosixPath::extname('file\\'));
        $this->assertEquals('', PosixPath::extname('file\\\\'));
        $this->assertEquals('.\\', PosixPath::extname('file.\\'));
        $this->assertEquals('.\\\\', PosixPath::extname('file.\\\\'));
    }

    public function joinTestsProvider() {
        $joinTests = array(array(array('.', 'x/b', '..', '/b/c.js'), 'x/b/c.js'),
            array(array('/.', 'x/b', '..', '/b/c.js'), '/x/b/c.js'),
            array(array('/foo', '../../../bar'), '/bar'),
            array(array('foo', '../../../bar'), '../../bar'),
            array(array('foo/', '../../../bar'), '../../bar'),
            array(array('foo/x', '../../../bar'), '../bar'),
            array(array('foo/x', './bar'), 'foo/x/bar'),
            array(array('foo/x/', './bar'), 'foo/x/bar'),
            array(array('foo/x/', '.', 'bar'), 'foo/x/bar'),
            array(array('./'), './'),
            array(array('.', './'), './'),
            array(array('.', '.', '.'), '.'),
            array(array('.', './', '.'), '.'),
            array(array('.', '/./', '.'), '.'),
            array(array('.', '/////./', '.'), '.'),
            array(array('.'), '.'),
            array(array('', '.'), '.'),
            array(array('', 'foo'), 'foo'),
            array(array('foo', '/bar'), 'foo/bar'),
            array(array('', '/foo'), '/foo'),
            array(array('', '', '/foo'), '/foo'),
            array(array('', '', 'foo'), 'foo'),
            array(array('foo', ''), 'foo'),
            array(array('foo/', ''), 'foo/'),
            array(array('foo', '', '/bar'), 'foo/bar'),
            array(array('./', '..', '/foo'), '../foo'),
            array(array('./', '..', '..', '/foo'), '../../foo'),
            array(array('.', '..', '..', '/foo'), '../../foo'),
            array(array('', '..', '..', '/foo'), '../../foo'),
            array(array('/'), '/'),
            array(array('/', '.'), '/'),
            array(array('/', '..'), '/'),
            array(array('/', '..', '..'), '/'),
            array(array(''), '.'),
            array(array('', ''), '.'),
            array(array(' /foo'), ' /foo'),
            array(array(' ', 'foo'), ' /foo'),
            array(array(' ', '.'), ' '),
            array(array(' ', '/'), ' /'),
            array(array(' ', ''), ' '),
            array(array('/', 'foo'), '/foo'),
            array(array('/', '/foo'), '/foo'),
            array(array('/', '//foo'), '/foo'),
            array(array('/', '', '/foo'), '/foo'),
            array(array('', '/', 'foo'), '/foo'),
            array(array('', '/', '/foo'), '/foo')
        );

        if (!$this->_isWindows()) {
            return $joinTests;
        }

        // Windows-specific join tests
        return array_merge($joinTests, array(// UNC path expected
            array(array('//foo/bar'), '//foo/bar/'),
            array(array('\\/foo/bar'), '//foo/bar/'),
            array(array('\\\\foo/bar'), '//foo/bar/'),
            // UNC path expected - server and share separate
            array(array('//foo', 'bar'), '//foo/bar/'),
            array(array('//foo/', 'bar'), '//foo/bar/'),
            array(array('//foo', '/bar'), '//foo/bar/'),
            // UNC path expected - questionable
            array(array('//foo', '', 'bar'), '//foo/bar/'),
            array(array('//foo/', '', 'bar'), '//foo/bar/'),
            array(array('//foo/', '', '/bar'), '//foo/bar/'),
            // UNC path expected - even more questionable
            array(array('', '//foo', 'bar'), '//foo/bar/'),
            array(array('', '//foo/', 'bar'), '//foo/bar/'),
            array(array('', '//foo/', '/bar'), '//foo/bar/'),
            // No UNC path expected (no double slash in first component)
            array(array('\\', 'foo/bar'), '/foo/bar'),
            array(array('\\', '/foo/bar'), '/foo/bar'),
            array(array('', '/', '/foo/bar'), '/foo/bar'),
            // No UNC path expected (no non-slashes in first component - questionable)
            array(array('//', 'foo/bar'), '/foo/bar'),
            array(array('//', '/foo/bar'), '/foo/bar'),
            array(array('\\\\', '/', '/foo/bar'), '/foo/bar'),
            array(array('//'), '/'),
            // No UNC path expected (share name missing - questionable).
            array(array('//foo'), '/foo'),
            array(array('//foo/'), '/foo/'),
            array(array('//foo', '/'), '/foo/'),
            array(array('//foo', '', '/'), '/foo/'),
            // No UNC path expected (too many leading slashes - questionable)
            array(array('///foo/bar'), '/foo/bar'),
            array(array('////foo', 'bar'), '/foo/bar'),
            array(array('\\\\\\/foo/bar'), '/foo/bar'),
            // Drive-relative vs drive-absolute paths. This merely describes the
            // status quo, rather than being obviously right
            array(array('c:'), 'c:.'),
            array(array('c:.'), 'c:.'),
            array(array('c:', ''), 'c:.'),
            array(array('', 'c:'), 'c:.'),
            array(array('c:.', '/'), 'c:./'),
            array(array('c:.', 'file'), 'c:file'),
            array(array('c:', '/'), 'c:/'),
            array(array('c:', 'file'), 'c:/file')
        ));
    }

    /**
     * @dataProvider joinTestsProvider
     */
    public function testJoin($paths, $result) {
        $this->assertEquals($result, call_user_func_array(array(Path::className(), 'join'), $paths));
    }

    public function testNormalize() {
        // path normalize tests
        $this->assertEquals(Win32Path::normalize('./fixtures///b/../b/c.js'),
            'fixtures\\b\\c.js');
        $this->assertEquals('\\bar', Win32Path::normalize('/foo/../../../bar'));
        $this->assertEquals('a\\b', Win32Path::normalize('a//b//../b'));
        $this->assertEquals('a\\b\\c', Win32Path::normalize('a//b//./c'));
        $this->assertEquals('a\\b', Win32Path::normalize('a//b//.'));
        $this->assertEquals(Win32Path::normalize('//server/share/dir/file.ext'),
            '\\\\server\\share\\dir\\file.ext');

        $this->assertEquals(PosixPath::normalize('./fixtures///b/../b/c.js'),
            'fixtures/b/c.js');
        $this->assertEquals('/bar', PosixPath::normalize('/foo/../../../bar'));
        $this->assertEquals('a/b', PosixPath::normalize('a//b//../b'));
        $this->assertEquals('a/b/c', PosixPath::normalize('a//b//./c'));
        $this->assertEquals('a/b', PosixPath::normalize('a//b//.'));
    }

    public function resolveTestsProvider() {
        // path.resolve tests
        if ($this->_isWindows()) {
            // windows
            $resolveTests =
                // arguments                                    result
                array(array(array('c:/blah\\blah', 'd:/games', 'c:../a'), 'c:\\blah\\a'),
                    array(array('c:/ignore', 'd:\\a/b\\c/d', '\\e.exe'), 'd:\\e.exe'),
                    array(array('c:/ignore', 'c:/some/file'), 'c:\\some\\file'),
                    array(array('d:/ignore', 'd:some/dir//'), 'd:\\ignore\\some\\dir'),
                    array(array('.'), getcwd()),
                    array(array('//server/share', '..', 'relative\\'), '\\\\server\\share\\relative'),
                    array(array('c:/', '//'), 'c:\\'),
                    array(array('c:/', '//dir'), 'c:\\dir'),
                    array(array('c:/', '//server/share'), '\\\\server\\share\\'),
                    array(array('c:/', '//server//share'), '\\\\server\\share\\'),
                    array(array('c:/', '///some//dir'), 'c:\\some\\dir')
                );
        } else {
            // Posix
            $resolveTests =
                // arguments                                    result
                array(array(array('/var/lib', '../', 'file/'), '/var/file'),
                    array(array('/var/lib', '/../', 'file/'), '/file'),
                    array(array('a/b/c/', '../../..'), getcwd()),
                    array(array('.'), getcwd()),
                    array(array('/some/dir', '.', '/absolute/'), '/absolute'));
        }

        return $resolveTests;
    }

    /**
     * @dataProvider resolveTestsProvider
     */
    public function testResolve($paths, $result) {
        $this->assertEquals($result, call_user_func_array(array(Path::className(), 'resolve'), $paths));
    }

    public function testIsAbsolute() {
        $this->assertEquals(true, Win32Path::isAbsolute('//server/file'));
        $this->assertEquals(true, Win32Path::isAbsolute('\\\\server\\file'));
        $this->assertEquals(true, Win32Path::isAbsolute('C:/Users/'));
        $this->assertEquals(true, Win32Path::isAbsolute('C:\\Users\\'));
        $this->assertEquals(false, Win32Path::isAbsolute('C:cwd/another'));
        $this->assertEquals(false, Win32Path::isAbsolute('C:cwd\\another'));
        $this->assertEquals(false, Win32Path::isAbsolute('directory/directory'));
        $this->assertEquals(false, Win32Path::isAbsolute('directory\\directory'));

        $this->assertEquals(true, PosixPath::isAbsolute('/home/foo'));
        $this->assertEquals(true, PosixPath::isAbsolute('/home/foo/..'));
        $this->assertEquals(false, PosixPath::isAbsolute('bar/'));
        $this->assertEquals(false, PosixPath::isAbsolute('./baz'));
    }

    public function relativeTestsProvider() {
        if ($this->_isWindows()) {
            // windows
            $relativeTests =
                // arguments                     result
                array(array('c:/blah\\blah', 'd:/games', 'd:\\games'),
                    array('c:/aaaa/bbbb', 'c:/aaaa', '..'),
                    array('c:/aaaa/bbbb', 'c:/cccc', '..\\..\\cccc'),
                    array('c:/aaaa/bbbb', 'c:/aaaa/bbbb', ''),
                    array('c:/aaaa/bbbb', 'c:/aaaa/cccc', '..\\cccc'),
                    array('c:/aaaa/', 'c:/aaaa/cccc', 'cccc'),
                    array('c:/', 'c:\\aaaa\\bbbb', 'aaaa\\bbbb'),
                    array('c:/aaaa/bbbb', 'd:\\', 'd:\\'));
        } else {
            // posix
            $relativeTests =
                // arguments                    result
                array(array('/var/lib', '/var', '..'),
                    array('/var/lib', '/bin', '../../bin'),
                    array('/var/lib', '/var/lib', ''),
                    array('/var/lib', '/var/apache', '../apache'),
                    array('/var/', '/var/lib', 'lib'),
                    array('/', '/var/lib', 'var/lib'));
        }

        return $relativeTests;
    }

    /**
     * @dataProvider relativeTestsProvider
     */
    public function testRelative($path1, $path2, $result) {
        $this->assertEquals($result, call_user_func_array(array(Path::className(), 'relative'), array($path1, $path2)));
    }

    public function testSeparator() {
        $this->assertEquals('\\', Win32Path::sep());
        $this->assertEquals('/', PosixPath::sep());
    }

    public function testDelimiter() {
        $this->assertEquals(';', Win32Path::delimiter());
        $this->assertEquals(':', PosixPath::delimiter());
    }

    public function testRuntimeEnvironmentDetect() {
        if ($this->_isWindows()) {
            $this->assertInstanceOf(Win32Path::className(), Path::instance(), 'should be win32 path module');
        } else {
            $this->assertInstanceOf(PosixPath::className(), Path::instance(), 'should be posix path module');
        }
    }
}