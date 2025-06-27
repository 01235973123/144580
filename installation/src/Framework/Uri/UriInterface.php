<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Uri;

/**
 * Interface for parsing URIs.
 *
 * @since  10.0
 */
interface UriInterface
{
	/**
	 * Include the scheme (http, https, etc.)
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const SCHEME = 1;

	/**
	 * Include the user
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const USER = 2;

	/**
	 * Include the password
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const PASS = 4;

	/**
	 * Include the host
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const HOST = 8;

	/**
	 * Include the port
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const PORT = 16;

	/**
	 * Include the path
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const PATH = 32;

	/**
	 * Include the query string
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const QUERY = 64;

	/**
	 * Include the fragment
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const FRAGMENT = 128;

	/**
	 * Include all available url parts (scheme, user, pass, host, port, path, query, fragment)
	 *
	 * @var    integer
	 * @since  10.0
	 */
	public const ALL = 255;

	/**
	 * Magic method to get the string representation of the URI object.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function __toString();

	/**
	 * Returns full URI string.
	 *
	 * @param   array  $parts  An array of strings specifying the parts to render.
	 *
	 * @return  string  The rendered URI string.
	 * @since   10.0
	 */
	public function toString(array $parts = ['scheme', 'user', 'pass', 'host', 'port', 'path', 'query', 'fragment']): string;

	/**
	 * Checks if variable exists.
	 *
	 * @param   string  $name  Name of the query variable to check.
	 *
	 * @return  boolean  True if the variable exists.
	 * @since   10.0
	 */
	public function hasVar(string $name): bool;

	/**
	 * Returns a query variable by name.
	 *
	 * @param   string       $name     Name of the query variable to get.
	 * @param   string|null  $default  Default value to return if the variable is not set.
	 *
	 * @return  mixed  Requested query variable if present otherwise the default value.
	 * @since   10.0
	 */
	public function getVar(string $name, ?string $default = null);

	/**
	 * Returns flat query string.
	 *
	 * @param   boolean  $toArray  True to return the query as a key => value pair array.
	 *
	 * @return  array|string   Query string, optionally as an array.
	 * @since   10.0
	 */
	public function getQuery(bool $toArray = false);

	/**
	 * Get the URI scheme (protocol)
	 *
	 * @return  string  The URI scheme.
	 * @since   10.0
	 */
	public function getScheme(): string;

	/**
	 * Get the URI username
	 *
	 * @return  string|null  The username, or null if no username was specified.
	 * @since   10.0
	 */
	public function getUser(): ?string;

	/**
	 * Get the URI password
	 *
	 * @return  string|null  The password, or null if no password was specified.
	 * @since   10.0
	 */
	public function getPass(): ?string;

	/**
	 * Get the URI host
	 *
	 * @return  string|null  The hostname/IP or null if no hostname/IP was specified.
	 * @since   10.0
	 */
	public function getHost(): ?string;

	/**
	 * Get the URI port
	 *
	 * @return  integer|null  The port number, or null if no port was specified.
	 * @since   10.0
	 */
	public function getPort(): ?int;

	/**
	 * Gets the URI path string
	 *
	 * @return  string  The URI path string.
	 * @since   10.0
	 */
	public function getPath(): string;

	/**
	 * Get the URI anchor string
	 *
	 * @return  string|null  The URI anchor string.
	 * @since   10.0
	 */
	public function getFragment(): ?string;

	/**
	 * Checks whether the current URI is using HTTPS.
	 *
	 * @return  boolean  True if using SSL via HTTPS.
	 * @since   10.0
	 */
	public function isSsl(): bool;
}
