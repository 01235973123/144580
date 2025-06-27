<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->registerAndUseScript('plug-system-mpsppagebuilder', 'media/com_osmembership/js/plug-system-mpsppagebuilder.min.js');
/**
 * Layout variables
 * -----------------
 * @var   array    $categories
 * @var   array    $selectedCategories
 * @var   array    $listPages
 * @var   array    $planPagebuilders
 * @var   stdClass $row
 */

$activeCategoryId = 0;

if (count($categories)) {
    ?>
	<h2><?php
        echo Text::_('OSM_SPPAGEBUILDER_CATEGORIES'); ?></h2>
	<p class="text-info"><?php
        echo Text::_('OSM_SPPAGEBUILDER_CATEGORIES_EXPLAIN'); ?></p>
	<table class="admintable adminform" style="width: 100%;">
        <?php
        foreach ($categories as $category) {
            if ($activeCategoryId == 0 && isset($listPages[$category->id])) {
                $activeCategoryId = $category->id;
            }
            ?>
			<tr>
				<td>
					<label class="checkbox">
						<input type="checkbox" class="form-check-input" value="<?php
                        echo $category->id ?>"
						       name="sppb_category_ids[]"
                            <?php
                            if (in_array($category->id, $selectedCategories)) {
                                echo ' checked="checked"';
                            } ?>/>
						<strong><?php
                            echo $category->title; ?></strong>
					</label>
				</td>
			</tr>
            <?php
        }
        ?>
	</table>
    <?php
}
?>
<h2><?php
    echo Text::_('OSM_SPPAGEBUILDER_PAGES'); ?></h2>
<p class="text-info"><?php
    echo Text::_('OSM_SPPAGEBUILDER_PAGES_EXPLAIN'); ?></p>

<?php
if (count($listPages[0])) {
    $category        = new stdClass;
    $category->id    = 0;
    $category->title = 'Un-categorized';

    $categories[] = $category;
}

echo HTMLHelper::_(
    'bootstrap.startAccordion',
    'sppb-categories-accordion',
    ['active' => 'sppb-category-' . $activeCategoryId, 'parent' => 'sppb-categories-accordion']
);

foreach ($categories as $category) {
    if (!isset($listPages[$category->id])) {
        continue;
    }

    echo HTMLHelper::_(
        'bootstrap.addSlide',
        'sppb-categories-accordion',
        $category->title,
        'sppb-category-' . $category->id
    );
    ?>
	<label class="checkbox">
		<input type="checkbox" value="<?php
        echo $category->id ?>" class="form-check-input sppb-category-check-all"/>
		<strong>Check All</strong>
	</label>
    <?php
    $categoryPages = $listPages[$category->id];

    foreach ($categoryPages as $page) {
        ?>
		<label class="checkbox" style="display: block;">
			<input type="checkbox" <?php
            if (in_array($page->id, $planPagebuilders)) {
                echo ' checked="checked" ';
            } ?>
			       value="<?php
                   echo $page->id; ?>"
			       class="form-check-input sppb-category-<?php
                   echo $category->id ?> sppb-page-checkbox"/>
            <?php
            echo $page->title; ?>
		</label>
        <?php
    }

    echo HTMLHelper::_('bootstrap.endSlide');
}

echo HTMLHelper::_('bootstrap.endAccordion');
?>
<input type="hidden" value="<?php
echo implode(',', $planPagebuilders) ?>" name="sppb_page_ids" id="sppb_page_ids"/>