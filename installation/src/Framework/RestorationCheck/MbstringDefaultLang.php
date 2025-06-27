<?php
/**
 * Akeeba Backup Restoration Script
 *
 * @package   brs
 * @copyright Copyright (c)2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\BRS\Framework\RestorationCheck;

defined('_AKEEBA') or die();

use Psr\Container\ContainerInterface;

/**
 * Pre-restoration check: MBstring language must be set to neutral
 *
 * @since      10.0
 */
class MbstringDefaultLang extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_MBSTRING_LANGUAGE_NEUTRAL', true, true);
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla() &&
			$this->hasMbString();
	}

	private function hasMbString(): bool
	{
		if (function_exists('extension_loaded'))
		{
			return extension_loaded( 'mbstring' );
		}

		return function_exists('mb_language');
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		if (extension_loaded('ini_get'))
		{
			return strtolower(ini_get('mbstring.language')) == 'neutral';
		}

		if (function_exists('mb_language'))
		{
			return strtolower(mb_language()) === 'neutral';
		}

		return true;
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->text('MAIN_ERR_MBSTRING_LANGUAGE_NOT_DEFAULT');
	}
}