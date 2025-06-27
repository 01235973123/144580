<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\RestorationCheck;

use Psr\Container\ContainerInterface;

defined('_AKEEBA') or die();

/**
 * Pre-restoration check: Magic Quotes GPC must be disabled.
 *
 * @since      10.0
 * @deprecated 11.0 This is no longer supported since PHP 8.0.0.
 */
class MagicQuotesGPC extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_MAGIC_QUOTE_GPC_OFF', false, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		$jVersion = $this->getContainer()->get('session')->get('jversion', '5.0.0');

		/**
		 * Only applies to Joomla! 3.0 or later, on PHP up to and including 7.4.0.
		 *
		 * @link  https://www.php.net/manual/en/function.get-magic-quotes-gpc.php
		 */
		return $this->isJoomla()
		       && version_compare($jVersion, '3.0.0', 'ge')
		       && version_compare(PHP_VERSION, '8.0.0', 'lt');
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		if (function_exists('get_magic_quotes_gpc'))
		{
			return @get_magic_quotes_gpc();
		}

		if (function_exists('ini_get'))
		{
			return (bool) ini_get('magic_quotes_gpc');
		}

		return false;
	}
}