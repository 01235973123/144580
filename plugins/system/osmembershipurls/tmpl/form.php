<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * -----------------
 * @var   array $urls
 * @var   array $titles
 */
?>

<div class="control-group">
	<div class="control-label">
        <?php
        echo OSMembershipHelperHtml::getFieldLabel(
            'urls',
            Text::_('PLG_OSMEMBERSHIP_JOOMLA_URLS'),
            Text::_('PLG_OSMEMBERSHIP_JOOMLA_URLS_EXPLAIN')
        ); ?>
	</div>
	<div class="controls">
		<textarea rows="20" cols="70" name="urls" class="input-xxlarge form-control"><?php
            echo implode("\r\n", $urls); ?></textarea>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
        <?php
        echo OSMembershipHelperHtml::getFieldLabel(
            'urls',
            Text::_('PLG_OSMEMBERSHIP_JOOMLA_URLS_TITLE'),
            Text::_('PLG_OSMEMBERSHIP_JOOMLA_URLS_TITLE_EXPLAIN')
        ); ?>
	</div>
	<div class="controls">
		<textarea rows="20" cols="70" name="titles" class="input-xxlarge form-control"><?php
            echo implode("\r\n", $titles); ?></textarea>
	</div>
</div>