<?php

/**
 * File for Net\Uri class.
 * @package Phrity > Net > Uri
 * @see https://www.rfc-editor.org/rfc/rfc3986
 * @see https://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface
 */

namespace Phrity\Net;

use InvalidArgumentException;
use Psr\Http\Message\UriInterface;

/**
 * Net\Uri class.
 */
class Uri implements UriInterface
{
    public const REQUIRE_PORT = 1; // Always include port, explicit or default
    public const ABSOLUTE_PATH = 2; // Enforce absolute path
    public const NORMALIZE_PATH = 4; // Normalize path
    public const IDNA = 8; // IDNA-convert host

    private const RE_MAIN = '!^(?P<schemec>(?P<scheme>[^:/?#]+):)?(?P<authorityc>//(?P<authority>[^/?#]*))?'
                          . '(?P<path>[^?#]*)(?P<queryc>\?(?P<query>[^#]*))?(?P<fragmentc>#(?P<fragment>.*))?$!';
    private const RE_AUTH = '!^(?P<userinfoc>(?P<user>[^:/?#]+)(?P<passc>:(?P<pass>[^:/?#]+))?@)?'
                          . '(?P<host>[^:/?#]*|\[[^/?#]*\])(?P<portc>:(?P<port>[0-9]*))?$!';

    private static $port_defaults = [
        'acap' => 674,
        'afp' => 548,
        'dict' => 2628,
        'dns' => 53,
        'ftp' => 21,
        'git' => 9418,
        'gopher' => 70,
        'http' => 80,
        'https' => 443,
        'imap' => 143,
        'ipp' => 631,
        'ipps' => 631,
        'irc' => 194,
        'ircs' => 6697,
        'ldap' => 389,
        'ldaps' => 636,
        'mms' => 1755,
        'msrp' => 2855,
        'mtqp' => 1038,
        'nfs' => 111,
        'nntp' => 119,
        'nntps' => 563,
        'pop' => 110,
        'prospero' => 1525,
        'redis' => 6379,
        'rsync' => 873,
        'rtsp' => 554,
        'rtsps' => 322,
        'rtspu' => 5005,
        'sftp' => 22,
        'smb' => 445,
        'snmp' => 161,
        'ssh' => 22,
        'svn' => 3690,
        'telnet' => 23,
        'ventrilo' => 3784,
        'vnc' => 5900,
        'wais' => 210,
        'ws' => 80,
        'wss' => 443,
    ];

    private $scheme;
    private $authority;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query;
    private $fragment;

    /**
     * Create new URI instance using a string
     * @param string $uri_string URI as string
     * @throws \InvalidArgumentException If the given URI cannot be parsed
     */
    public function __construct(string $uri_string = '', int $flags = 0)
    {
        $this->parse($uri_string);
    }


    // ---------- PSR-7 getters ---------------------------------------------------------------------------------------

    /**
     * Retrieve the scheme component of the URI.
     * @return string The URI scheme
     */
    public function getScheme(int $flags = 0): string
    {
        return $this->getComponent('scheme') ?? '';
    }

    /**
     * Retrieve the authority component of the URI.
     * @return string The URI authority, in "[user-info@]host[:port]" format
     */
    public function getAuthority(int $flags = 0): string
    {
        $host = $this->formatComponent($this->getHost($flags));
        if ($this->isEmpty($host)) {
            return '';
        }
        $userinfo = $this->formatComponent($this->getUserInfo(), '', '@');
        $port = $this->formatComponent($this->getPort($flags), ':');
        return "{$userinfo}{$host}{$port}";
    }

    /**
     * Retrieve the user information component of the URI.
     * @return string The URI user information, in "username[:password]" format
     */
    public function getUserInfo(int $flags = 0): string
    {
        $user = $this->formatComponent($this->getComponent('user'));
        $pass = $this->formatComponent($this->getComponent('pass'), ':');
        return $this->isEmpty($user) ? '' : "{$user}{$pass}";
    }

    /**
     * Retrieve the host component of the URI.
     * @return string The URI host
     */
    public function getHost(int $flags = 0): string
    {
        $host = $this->getComponent('host') ?? '';
        if ($flags & self::IDNA) {
            $host = $this->idna($host);
        }
        return $host;
    }

    /**
     * Retrieve the port component of the URI.
     * @return null|int The URI port
     */
    public function getPort(int $flags = 0): ?int
    {
        $port = $this->getComponent('port');
        $scheme = $this->getComponent('scheme');
        $default = isset(self::$port_defaults[$scheme]) ? self::$port_defaults[$scheme] : null;
        if ($flags & self::REQUIRE_PORT) {
            return !$this->isEmpty($port) ? $port : $default;
        }
        return $this->isEmpty($port) || $port === $default ? null : $port;
    }

    /**
     * Retrieve the path component of the URI.
     * @return string The URI path
     */
    public function getPath(int $flags = 0): string
    {
        $path = $this->getComponent('path') ?? '';
        if ($flags & self::NORMALIZE_PATH) {
            $path = $this->normalizePath($path);
        }
        if ($flags & self::ABSOLUTE_PATH && substr($path, 0, 1) !== '/') {
            $path = "/{$path}";
        }
        return $path;
    }

    /**
     * Retrieve the query string of the URI.
     * @return string The URI query string
     */
    public function getQuery(int $flags = 0): string
    {
        return $this->getComponent('query') ?? '';
    }

    /**
     * Retrieve the fragment component of the URI.
     * @return string The URI fragment
     */
    public function getFragment(int $flags = 0): string
    {
        return $this->getComponent('fragment') ?? '';
    }


    // ---------- PSR-7 setters ---------------------------------------------------------------------------------------

    /**
     * Return an instance with the specified scheme.
     * @param string $scheme The scheme to use with the new instance
     * @return static A new instance with the specified scheme
     * @throws \InvalidArgumentException for invalid schemes
     * @throws \InvalidArgumentException for unsupported schemes
     */
    /*public function withScheme($scheme, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        if ($flags & self::REQUIRE_PORT) {
            $clone->setComponent('port', $this->getPort(self::REQUIRE_PORT));
            $default = isset(self::$port_defaults[$scheme]) ? self::$port_defaults[$scheme] : null;
        }
        $clone->setComponent('scheme', $scheme);
        return $clone;
    }*/
    public function withScheme(string $scheme, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        if ($flags & self::REQUIRE_PORT) {
            $clone->setComponent('port', $this->getPort(self::REQUIRE_PORT));
            $default = isset(self::$port_defaults[$scheme]) ? self::$port_defaults[$scheme] : null;
        }
        $clone->setComponent('scheme', $scheme);
        return $clone;
    }

    /**
     * Return an instance with the specified user information.
     * @param string $user The user name to use for authority
     * @param null|string $password The password associated with $user
     * @return static A new instance with the specified user information
     */
   /* public function withUserInfo($user, $password = null, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('user', $user);
        $clone->setComponent('pass', $password);
        return $clone;
    }*/
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('user', $user);
        $clone->setComponent('pass', $password);
        return $clone;
    }


    /**
     * Return an instance with the specified host.
     * @param string $host The hostname to use with the new instance
     * @return static A new instance with the specified host
     * @throws \InvalidArgumentException for invalid hostnames
     */
    /*public function withHost($host, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        if ($flags & self::IDNA) {
            $host = $this->idna($host);
        }
        $clone->setComponent('host', $host);
        return $clone;
    }*/
    public function withHost(string $host): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('host', $host);
        return $clone;
    }


    /**
     * Return an instance with the specified port.
     * @param null|int $port The port to use with the new instance
     * @return static A new instance with the specified port
     * @throws \InvalidArgumentException for invalid ports
     */
    public function withPort(?int $port): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('port', $port);
        return $clone;
    }

    /**
     * Return an instance with the specified path.
     * @param string $path The path to use with the new instance
     * @return static A new instance with the specified path
     * @throws \InvalidArgumentException for invalid paths
     */
  /*  public function withPath($path, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        if ($flags & self::NORMALIZE_PATH) {
            $path = $this->normalizePath($path);
        }
        if ($flags & self::ABSOLUTE_PATH && substr($path, 0, 1) !== '/') {
            $path = "/{$path}";
        }
        $clone->setComponent('path', $path);
        return $clone;
    }*/
    public function withPath(string $path): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('path', $path);
        return $clone;
    }


    /**
     * Return an instance with the specified query string.
     * @param string $query The query string to use with the new instance
     * @return static A new instance with the specified query string
     * @throws \InvalidArgumentException for invalid query strings
     */
  /*  public function withQuery($query, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('query', $query);
        return $clone;
    }*/
    public function withQuery(string $query): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('query', $query);
        return $clone;
    }

    /**
     * Return an instance with the specified URI fragment.
     * @param string $fragment The fragment to use with the new instance
     * @return static A new instance with the specified fragment
     */
   /* public function withFragment($fragment, int $flags = 0): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('fragment', $fragment);
        return $clone;
    }*/
    public function withFragment(string $fragment): UriInterface
    {
        $clone = clone $this;
        $clone->setComponent('fragment', $fragment);
        return $clone;
    }


    // ---------- PSR-7 string ----------------------------------------------------------------------------------------

    /**
     * Return the string representation as a URI reference.
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }


    // ---------- Extensions ------------------------------------------------------------------------------------------

    /**
     * Return the string representation as a URI reference.
     * @return string
     */
    public function toString(int $flags = 0): string
    {
        $scheme = $this->formatComponent($this->getComponent('scheme'), '', ':');
        $authority = $this->authority ? "//{$this->formatComponent($this->getAuthority($flags))}" : '';
        $path_flags = ($this->authority && $this->path ? self::ABSOLUTE_PATH : 0) | $flags;
        $path = $this->formatComponent($this->getPath($path_flags));
        $query = $this->formatComponent($this->getComponent('query'), '?');
        $fragment = $this->formatComponent($this->getComponent('fragment'), '#');
        return "{$scheme}{$authority}{$path}{$query}{$fragment}";
    }


    // ---------- Private helper methods ------------------------------------------------------------------------------

    private function parse(string $uri_string = ''): void
    {
        if ($uri_string === '') {
            return;
        }
        preg_match(self::RE_MAIN, $uri_string, $main);
        $this->authority = !empty($main['authorityc']);
        $this->setComponent('scheme', isset($main['schemec']) ? $main['scheme'] : '');
        $this->setComponent('path', isset($main['path']) ? $main['path'] : '');
        $this->setComponent('query', isset($main['queryc']) ? $main['query'] : '');
        $this->setComponent('fragment', isset($main['fragmentc']) ? $main['fragment'] : '');
        if ($this->authority) {
            preg_match(self::RE_AUTH, $main['authority'], $auth);
            if (empty($auth) && $main['authority'] !== '') {
                throw new InvalidArgumentException("Invalid 'authority'.");
            }
            if ($this->isEmpty($auth['host']) && !$this->isEmpty($auth['user'])) {
                throw new InvalidArgumentException("Invalid 'authority'.");
            }
            $this->setComponent('user', isset($auth['user']) ? $auth['user'] : '');
            $this->setComponent('pass', isset($auth['passc']) ? $auth['pass'] : '');
            $this->setComponent('host', isset($auth['host']) ? $auth['host'] : '');
            $this->setComponent('port', isset($auth['portc']) ? $auth['port'] : '');
        }
    }

    private function encode(string $source, string $keep = ''): string
    {
        $exclude = "[^%\/:=&!\$'()*+,;@{$keep}]+";
        $exp = "/(%{$exclude})|({$exclude})/";
        return preg_replace_callback($exp, function ($matches) {
            if ($e = preg_match('/^(%[0-9a-fA-F]{2})/', $matches[0], $m)) {
                return substr($matches[0], 0, 3) . rawurlencode(substr($matches[0], 3));
            } else {
                return rawurlencode($matches[0]);
            }
        }, $source);
    }

    private function setComponent(string $component, $value): void
    {
        $value = $this->parseCompontent($component, $value);
        $this->$component = $value;
    }

    private function parseCompontent(string $component, $value)
    {
        if ($this->isEmpty($value)) {
            return null;
        }
        switch ($component) {
            case 'scheme':
                $this->assertString($component, $value);
                $this->assertpattern($component, $value, '/^[a-z][a-z0-9-+.]*$/i');
                return mb_strtolower($value);
            case 'host': // IP-literal / IPv4address / reg-name
                $this->assertString($component, $value);
                $this->authority = $this->authority || !$this->isEmpty($value);
                return mb_strtolower($value);
            case 'port':
                $this->assertInteger($component, $value);
                if ($value < 0 || $value > 65535) {
                    throw new InvalidArgumentException("Invalid port number");
                }
                return (int)$value;
            case 'path':
                $this->assertString($component, $value);
                $value = $this->encode($value);
                return $value;
            case 'user':
            case 'pass':
            case 'query':
            case 'fragment':
                $this->assertString($component, $value);
                $value = $this->encode($value, '?');
                return $value;
        }
    }

    private function getComponent(string $component)
    {
        return isset($this->$component) ? $this->$component : null;
    }

    private function formatComponent($value, string $before = '', string $after = ''): string
    {
        return $this->isEmpty($value) ? '' : "{$before}{$value}{$after}";
    }

    private function isEmpty($value): bool
    {
        return is_null($value) || $value === '';
    }

    private function assertString(string $component, $value): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException("Invalid '{$component}': Should be a string");
        }
    }

    private function assertInteger(string $component, $value): void
    {
        if (!is_numeric($value) || intval($value) != $value) {
            throw new InvalidArgumentException("Invalid '{$component}': Should be an integer");
        }
    }

    private function assertPattern(string $component, string $value, string $pattern): void
    {
        if (preg_match($pattern, $value) == 0) {
            throw new InvalidArgumentException("Invalid '{$component}': Should match {$pattern}");
        }
    }

    private function normalizePath(string $path): string
    {
        $result = [];
        preg_match_all('!([^/]*/|[^/]*$)!', $path, $items);
        foreach ($items[0] as $item) {
            switch ($item) {
                case '':
                case './':
                case '.':
                    break; // just skip
                case '/':
                    if (empty($result)) {
                        array_push($result, $item); // add
                    }
                    break;
                case '..':
                case '../':
                    if (empty($result) || end($result) == '../') {
                        array_push($result, $item); // add
                    } else {
                        array_pop($result); // remove previous
                    }
                    break;
                default:
                    array_push($result, $item); // add
            }
        }
        return implode('', $result);
    }

    private function idna(string $value): string
    {
        if ($value === '' || !is_callable('idn_to_ascii')) {
            return $value; // Can't convert, but don't cause exception
        }
        return idn_to_ascii($value, IDNA_NONTRANSITIONAL_TO_ASCII, INTL_IDNA_VARIANT_UTS46);
    }
}
