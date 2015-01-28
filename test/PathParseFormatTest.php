<?php
use dsxack\Path\Exception;
use dsxack\Path\PathAdapterInterface;
use dsxack\Path\PosixPath;
use dsxack\Path\Win32Path;

/**
 * @author Smotrov Dmitriy <dsxack@gmail.com>
 */

class PathParseFormatTest extends  PHPUnit_Framework_TestCase {
    public function parseFormatProvider() {
        return array(
            array(new Win32Path(), array(
                'C:\\path\\dir\\index.html',
                'C:\\another_path\\DIR\\1\\2\\33\\index',
                'another_path\\DIR with spaces\\1\\2\\33\\index',
                '\\foo\\C:',
                'file',
                '.\\file',

                // unc
                '\\\\server\\share\\file_path',
                '\\\\server two\\shared folder\\file path.zip',
                '\\\\teela\\admin$\\system32',
                '\\\\?\\UNC\\server\\share'
            )),
            array(new PosixPath(), array(
                '/home/user/dir/file.txt',
                '/home/user/a dir/another File.zip',
                '/home/user/a dir//another&File.',
                '/home/user/a$$$dir//another File.zip',
                'user/dir/another File.zip',
                'file',
                '.\\file',
                './file',
                'C:\\foo'
            ))
        );
    }

    public function parseFormatErrorsProvider() {
        return array(
            array(array("method" => 'parse', "input" => array(null), "message" =>  "Parameter 'pathString' must be a string, not NULL")),
            array(array("method" => 'parse', "input" => array(array()), "message" =>  "Parameter 'pathString' must be a string, not array")),
            array(array("method" => 'parse', "input" => array(true), "message" =>  "Parameter 'pathString' must be a string, not boolean")),
            array(array("method" => 'parse', "input" =>  array(1), "message" =>  "Parameter 'pathString' must be a string, not integer")),
            // array(array("method" => 'parse', "input" =>  array(''), "message" =>  "Invalid path")), "" omitted because it's hard to trigger!
            array(array("method" => 'format', "input" =>  array(null), "message" =>  "Parameter 'pathObject' must be an object, not NULL")),
            array(array("method" =>  'format', "input" =>  array(''), "message" =>  "Parameter 'pathObject' must be an object, not string")),
            array(array("method" => 'format', "input" =>  array(true), "message" =>  "Parameter 'pathObject' must be an object, not boolean")),
            array(array("method" => 'format', "input" =>  array(1), "message" =>  "Parameter 'pathObject' must be an object, not integer")),
            array(array("method" => 'format', "input" =>  array(array("root" => true)), "message" =>  "'pathObject.root' must be a string or undefined, not boolean")),
            array(array("method" => 'format', "input" =>  array(array("root" => 12)), "message" =>  "'pathObject.root' must be a string or undefined, not integer")),
        );
    }

    /**
     * @dataProvider parseFormatProvider
     * @param PathAdapterInterface $adapter
     * @param $paths
     */
    public function testParseFormat(PathAdapterInterface $adapter, $paths) {
        foreach ($paths as $path) {
            $output = $adapter::parse($path);
            $this->assertEquals($path, $adapter::format($output));
            $this->assertEquals($output['dir'] ? $adapter::dirname($path) : '', $output['dir']);
            $this->assertEquals($adapter::basename($path), $output['base']);
            $this->assertEquals($adapter::extname($path), $output['ext']);
        }
    }

    /**
     * @dataProvider parseFormatErrorsProvider
     * @param $params
     */
    public function testParseFormatErrors($params) {
        $this->_testParseFormatErrorsWithPathAdapter(new Win32Path(), $params);
        $this->_testParseFormatErrorsWithPathAdapter(new PosixPath(), $params);
    }

    private function _testParseFormatErrorsWithPathAdapter(PathAdapterInterface $pathAdapter, $params) {
        try {
            call_user_func_array(array($pathAdapter, $params['method']), $params['input']);
        } catch (Exception $e) {
            $this->assertEquals($e->getMessage(), $params['message']);

            return;
        }

        $this->fail('should have thrown');
    }

}