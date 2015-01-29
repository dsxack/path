Path
====
PHP port nodejs `path` library

[![TravisCI](https://travis-ci.org/DsXack/path.svg)](https://travis-ci.org/DsXack/path)
[![Coverage Status](https://coveralls.io/repos/DsXack/path/badge.svg)](https://coveralls.io/r/DsXack/path)
[![Latest Stable Version](https://poser.pugx.org/dsxack/path/v/stable.svg)](https://packagist.org/packages/dsxack/path)
[![Total Downloads](https://poser.pugx.org/dsxack/path/downloads.svg)](https://packagist.org/packages/dsxack/path)
[![Latest Unstable Version](https://poser.pugx.org/dsxack/path/v/unstable.svg)](https://packagist.org/packages/dsxack/path)
[![License](https://poser.pugx.org/dsxack/path/license.svg)](https://packagist.org/packages/dsxack/path)

# Usage

## Path::normalize(p)

Normalize a string path, taking care of `'..'` and `'.'` parts.

When multiple slashes are found, they're replaced by a single one;
when the path contains a trailing slash, it is preserved.
On Windows backslashes are used.

Example:

    Path::normalize('/foo/bar//baz/asdf/quux/..')
    // returns
    '/foo/bar/baz/asdf'

## Path::join([path1][, path2][, ...])

Join all arguments together and normalize the resulting path.

Arguments must be strings.

Example:

    Path::join('/foo', 'bar', 'baz/asdf', 'quux', '..')
    // returns
    '/foo/bar/baz/asdf'

    Path::join('foo', [], 'bar')
    // throws exception
    TypeError: Arguments to Path::join must be strings

## Path::resolve([from ...], to)

Resolves `to` to an absolute path.

If `to` isn't already absolute `from` arguments are prepended in right to left
order, until an absolute path is found. If after using all `from` paths still
no absolute path is found, the current working directory is used as well. The
resulting path is normalized, and trailing slashes are removed unless the path
gets resolved to the root directory. Non-string `from` arguments are ignored.

Another way to think of it is as a sequence of `cd` commands in a shell.

    Path::resolve('foo/bar', '/tmp/file/', '..', 'a/../subfile')

Is similar to:

    cd foo/bar
    cd /tmp/file/
    cd ..
    cd a/../subfile
    pwd

The difference is that the different paths don't need to exist and may also be
files.

Examples:

    Path::resolve('/foo/bar', './baz')
    // returns
    '/foo/bar/baz'

    Path::resolve('/foo/bar', '/tmp/file/')
    // returns
    '/tmp/file'

    Path::resolve('wwwroot', 'static_files/png/', '../gif/image.gif')
    // if currently in /home/myself/node, it returns
    '/home/myself/node/wwwroot/static_files/gif/image.gif'

## Path::isAbsolute(path)

Determines whether `path` is an absolute path. An absolute path will always
resolve to the same location, regardless of the working directory.

Posix examples:

    Path::isAbsolute('/foo/bar') // true
    Path::isAbsolute('/baz/..')  // true
    Path::isAbsolute('qux/')     // false
    Path::isAbsolute('.')        // false

Windows examples:

    Path::isAbsolute('//server')  // true
    Path::isAbsolute('C:/foo/..') // true
    Path::isAbsolute('bar\\baz')   // false
    Path::isAbsolute('.')         // false

## Path::relative(from, to)

Solve the relative path from `from` to `to`.

At times we have two absolute paths, and we need to derive the relative
path from one to the other.  This is actually the reverse transform of
`path.resolve`, which means we see that:

    Path::resolve(from, path.relative(from, to)) == path.resolve(to)

Examples:

    Path::relative('C:\\orandea\\test\\aaa', 'C:\\orandea\\impl\\bbb')
    // returns
    '..\\..\\impl\\bbb'

    Path::relative('/data/orandea/test/aaa', '/data/orandea/impl/bbb')
    // returns
    '../../impl/bbb'

## Path::dirname(p)

Return the directory name of a path.  Similar to the Unix `dirname` command.

Example:

    Path::dirname('/foo/bar/baz/asdf/quux')
    // returns
    '/foo/bar/baz/asdf'

## Path::basename(p[, ext])

Return the last portion of a path.  Similar to the Unix `basename` command.

Example:

    Path::basename('/foo/bar/baz/asdf/quux.html')
    // returns
    'quux.html'

    Path::basename('/foo/bar/baz/asdf/quux.html', '.html')
    // returns
    'quux'

## Path::extname(p)

Return the extension of the path, from the last '.' to end of string
in the last portion of the path.  If there is no '.' in the last portion
of the path or the first character of it is '.', then it returns
an empty string.  Examples:

    Path::extname('index.html')
    // returns
    '.html'

    Path::extname('index.coffee.md')
    // returns
    '.md'

    Path::extname('index.')
    // returns
    '.'

    Path::extname('index')
    // returns
    ''

## Path::sep()

The platform-specific file separator. `'\\'` or `'/'`.

An example on *nix:

    explode(Path::sep(), 'foo/bar/baz')
    // returns
    ['foo', 'bar', 'baz']

An example on Windows:

    explode(Path::sep(), 'foo\\bar\\baz')
    // returns
    ['foo', 'bar', 'baz']

## Path::delimiter()

The platform-specific path delimiter, `;` or `':'`.

An example on *nix:

    echo getenv('PATH')
    // '/usr/bin:/bin:/usr/sbin:/sbin:/usr/local/bin'

    explode(Path::delimiter(), getenv('PATH'))
    // returns
    ['/usr/bin', '/bin', '/usr/sbin', '/sbin', '/usr/local/bin']

An example on Windows:

    echo getenv('PATH')
    // 'C:\Windows\system32;C:\Windows;C:\Program Files\php\'

    explode(Path::delimiter(), getenv('PATH'))
    // returns
    ['C:\Windows\system32', 'C:\Windows', 'C:\Program Files\php\']

## Path::parse(pathString)

Returns an object from a path string.

An example on *nix:

    Path::parse('/home/user/dir/file.txt')
    // returns
    [
        "root" => "/",
        "dir" => "/home/user/dir",
        "base" => "file.txt",
        "ext" => ".txt",
        "name" => "file"
    ]

An example on Windows:

    Path::parse('C:\\path\\dir\\index.html')
    // returns
    [
        "root" => "C:\",
        "dir" => "C:\path\dir",
        "base" => "index.html",
        "ext" => ".html",
        "name" => "index"
    ]

## Path::format(params)

Returns a path string from an object, the opposite of `Path::parse` above.

    Path::format([
        "root" => "/",
        "dir" => "/home/user/dir",
        "base" => "file.txt",
        "ext" => ".txt",
        "name" => "file"
    ])
    // returns
    '/home/user/dir/file.txt'

## PosixPath class

Provide access to aforementioned `path` methods but always interact in a posix
compatible way.

## Win32Path class

Provide access to aforementioned `path` methods but always interact in a win32
compatible way.
