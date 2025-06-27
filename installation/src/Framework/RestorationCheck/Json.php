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
 * Pre-restoration check: JSON support
 *
 * @since      10.0
 */
class Json extends AbstractRestorationCheck
{
	/** @inheritdoc */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container, 'MAIN_LBL_CHECK_JSON_SUPPORT', true, true);
	}

	/** @inheritdoc */
	protected function returnCurrentValue()
	{
		return function_exists('json_encode') && function_exists('json_decode');
	}

	/** @inheritdoc */
	public function isApplicable(): bool
	{
		return $this->isJoomla() || $this->isWordPress();
	}

	/** @inheritdoc */
	public function getNotice(): ?string
	{
		if ($this->isValid())
		{
			return null;
		}

		return $this->getContainer()->get('language')->sprintf('MAIN_ERR_CHECK_JSON_SUPPORT', PHP_VERSION);
	}
}