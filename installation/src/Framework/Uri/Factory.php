<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\Uri;

use Akeeba\BRS\Framework\Container\ContainerAwareInterface;
use Akeeba\BRS\Framework\Container\ContainerAwareTrait;
use Akeeba\BRS\Framework\Input\Cli;
use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Uri object factory service.
 *
 * Provides Singleton implementation of the Uri constructor, and deals with receiving information about the current URL
 * reported by the server.
 *
 * @since  10.0
 */
final class Factory implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/**
	 * An array of URI instances.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $instances = [];

	/**
	 * The current calculated base URL segments.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $base = [];

	/**
	 * The current calculated root URL segments.
	 *
	 * @var   array
	 * @since 10.0
	 */
	protected static $root = [];

	/**
	 * The current URL.
	 *
	 * @var   string
	 * @since 10.0
	 */
	protected static $current;

	/**
	 * Constructor.
	 *
	 * @param   ContainerInterface  $container
	 *
	 * @since   10.0
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Returns a singleton URI object for the provided URL.
	 *
	 * @param   string  $uri  The URI to parse. If null, uses current URL.
	 *
	 * @return  Uri  The URI object.
	 * @since   10.0
	 */
	public function instance($uri = 'SERVER'): Uri
	{
		if (isset(self::$instances[$uri]))
		{
			return self::$instances[$uri];
		}

		if ($uri !== 'SERVER')
		{
			return self::$instances[$uri] = new Uri($uri);
		}

		$input       = $this->getContainer()->get('input');

		if ($input instanceof Cli)
		{
			return self::$instances[$uri] = new Uri('https://akeeba.invalid/installation/index.php');
		}

		$serverInput = $input->server;

		// Determine if the request was over SSL (HTTPS).
		$httpsFlag = $serverInput->getRaw('HTTPS', null);
		$isHttps   = $httpsFlag !== null && !empty($httpsFlag) && strtolower($httpsFlag) != 'off';
		$protocol  = ($isHttps ? 'https' : 'http') . '://';

		/**
		 * Determine if we run under Apache or IIS and construct the server URI accordingly.
		 *
		 * If PHP_SELF and REQUEST_URI are present, we will assume we are running on Apache.
		 */
		$phpSelf    = $serverInput->getRaw('PHP_SELF');
		$requestUri = $serverInput->getRaw('REQUEST_URI');

		if (!empty($phpSelf) && !empty($requestUri))
		{
			// To build the entire URI we need to prefix the protocol, and the host to the request URI path string.
			$fullURL = $protocol . $serverInput->getRaw('HTTP_HOST') . $requestUri;

			return self::$instances[$uri] = new Uri($fullURL);
		}

		/**
		 * We assume we are running on IIS. Therefore, we need to use the SCRIPT_NAME and QUERY_STRING to work out the
		 * request URI path since IIS does not provide the REQUEST_URI environment variable.
		 */
		$queryString = $serverInput->getRaw('QUERY_STRING', null);
		$fullURL     = $protocol . $serverInput->getRaw('HTTP_HOST') . $serverInput->getRaw('SCRIPT_NAME')
		               . empty($queryString) ? '' : "?$queryString";

		return self::$instances[$uri] = new Uri($fullURL);
	}

	/**
	 * Returns the base URI for the request.
	 *
	 * @param   bool  $pathOnly  If true, omits the scheme, host and port information.
	 *
	 * @return  string  The base URI string
	 * @since   10.0
	 */
	public function base(bool $pathOnly = false): string
	{
		if (!empty(self::$base))
		{
			return $pathOnly ? self::$base['path'] : self::$base['prefix'] . self::$base['path'] . '/';
		}

		$input                = $this->getContainer()->get('input');
		$serverInput          = $input->server;
		$uri                  = $this->instance();
		self::$base['prefix'] = $uri->toString(['scheme', 'host', 'port']);
		$scriptName           = $serverInput->getRaw('SCRIPT_NAME');

		// Is this PHP-CGI on Apache with "cgi.fix_pathinfo = 0"?
		$requestUri = $serverInput->getRaw('REQUEST_URI', '');

		if (
			strpos(php_sapi_name(), 'cgi') !== false
			&& !ini_get('cgi.fix_pathinfo')
			&& !empty($requestUri)
		)
		{
			$scriptName = $serverInput->getRaw('PHP_SELF');
		}

		self::$base['path'] = rtrim(dirname($scriptName), '/\\');

		return $pathOnly ? self::$base['path'] : self::$base['prefix'] . self::$base['path'] . '/';
	}

	/**
	 * Returns the root URI for the request.
	 *
	 * @param   bool         $pathOnly  If true, omits the scheme, host and port information.
	 * @param   string|null  $path      Override the root path
	 *
	 * @return  string  The root URI string.
	 * @since   10.0
	 */
	public function root(bool $pathOnly = false, ?string $path = null): string
	{
		// Get the scheme
		if (empty(self::$root))
		{
			$uri                  = $this->instance(self::base());
			self::$root['prefix'] = $uri->toString(['scheme', 'host', 'port']);
			self::$root['path']   = rtrim($uri->toString(['path']), '/\\');
		}

		// Get the scheme
		if ($path !== null)
		{
			self::$root['path'] = $path;
		}

		return $pathOnly ? self::$root['path'] : self::$root['prefix'] . self::$root['path'] . '/';
	}

	/**
	 * Returns the URL for the request, minus the query.
	 *
	 * @return  string
	 * @since   10.0
	 */
	public function current(): string
	{
		if (empty(self::$current))
		{
			self::$current = $this->instance()->toString(['scheme', 'host', 'port', 'path']);
		}

		return self::$current;
	}

	/**
	 * Resets the cached information for Uri object instances, and the base, root, and current path.
	 *
	 * @return  void
	 * @since   10.0
	 */
	public function reset(): void
	{
		self::$instances = [];
		self::$base      = [];
		self::$root      = [];
		self::$current   = '';
	}
}