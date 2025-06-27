<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$bootstrapHelper = OSMembershipHelperBootstrap::getInstance();
?>
<div id="osm-categories-list" class="osm-container">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->categoryId)
		{
			$pageHeading = $this->params->get('page_heading') ?: $this->category->title;
		}
		else
		{
			$pageHeading = $this->params->get('page_heading') ?: Text::_('OSM_CATEGORIES');
		}

		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
		?>
			<<?php echo $hTag; ?> class="osm-page-title"><?php echo $pageHeading;?></<?php echo $hTag; ?>>
		<?php
	}

	if(!empty($this->category->description))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->category->description);?>
		</div>
	<?php
	}
	elseif (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text'));?>
		</div>
	<?php
	}

	if (count($this->items))
	{
		$items = $this->items;

		echo HTMLHelper::_('bootstrap.startAccordion', 'mp-categories-accordion',
			['active' => 'mp-category-' . $items[0]->id, 'parent' => 'mp-categories-accordion']);

		foreach ($items as $item)
		{
			$accordionTitle = $item->title . ' <span class="badge badge-info osm-category-number-plans">' . $item->total_plans . ' ' . ($item->total_plans > 1 ? Text::_('OSM_PLANS') : Text::_('OSM_PLAN')) . '</span>';

			echo HTMLHelper::_('bootstrap.addSlide', 'mp-categories-accordion', $accordionTitle,
				'mp-category-' . $item->id);

			echo OSMembershipHelperHtml::loadCommonLayout('common/tmpl/accordion_plans.php', [
				'items'           => $item->plans,
				'input'           => $this->input,
				'config'          => $this->config,
				'Itemid'          => $this->Itemid,
				'categoryId'      => $this->categoryId,
				'bootstrapHelper' => $bootstrapHelper,
			]);

			echo HTMLHelper::_('bootstrap.endSlide');
		}

		echo HTMLHelper::_('bootstrap.endAccordion');
	}


	if ($this->pagination->total > $this->pagination->limit)
	{
	?>
		<div class="pagination">
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
	<?php
	}
	?>
</div>
