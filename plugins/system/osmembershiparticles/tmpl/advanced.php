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
 *
 * @var \Joomla\CMS\Form\Form     $form
 * @var \Joomla\Registry\Registry $params
 */
?>
	<h2><?php echo Text::_('OSM_ARTICLES_CATEGORIES'); ?></h2>
	<p class="text-info"><?php echo Text::_('OSM_ARTICLES_CATEGORIES_EXPLAIN'); ?></p>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_ARTICLES_CATEGORIES');?>
		</div>
		<div class="controls">
			<?php echo OSMembershipHelperHtml::getArticleCategoriesInput(explode(',', $params->get('article_categories', '')), 'article_categories', true); ?>
		</div>
	</div>
	<h2><?php echo Text::_('OSM_ARTICLES'); ?></h2>
	<p class="text-info"><?php echo Text::_('OSM_ARTICLES_EXPLAIN'); ?></p>
<?php

foreach ($form->getFieldset() as $field)
{
	echo $field->input;
}