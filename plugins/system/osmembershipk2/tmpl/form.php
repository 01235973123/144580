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
	->registerAndUseScript('plug-system-osmembershipk2', 'media/com_osmembership/js/plug-system-osmembershipk2.min.js');
/**
 * Layout variables
 * -----------------
 * @var   array    $categories
 * @var   array    $selectedCategories
 * @var   array    $articles
 * @var   array    $planArticles
 * @var   stdClass $row
 */

$activeCategoryId = 0;
?>
<h2><?php
    echo Text::_('OSM_K2_CATEGORIES'); ?></h2>
<p class="text-info"><?php
    echo Text::_('OSM_K2_CATEGORIES_EXPLAIN'); ?></p>
<table class="admintable adminform" style="width: 100%;">
    <?php
    foreach ($categories as $category) {
        if ($activeCategoryId == 0 && isset($articles[$category->id])) {
            $activeCategoryId = $category->id;
        }
        ?>
		<tr>
			<td>
				<label class="checkbox">
					<input type="checkbox" class="form-check-input" value="<?php
                    echo $category->id ?>"
                        <?php
                        if (in_array($category->id, $selectedCategories)) {
                            echo ' checked="checked"';
                        } ?>
						   name="k2_item_categories[]"/> <strong><?php
                        echo $category->name; ?></strong>
				</label>
			</td>
		</tr>
        <?php
    }
    ?>
</table>

<h2><?php
    echo Text::_('OSM_K2_ITEMS'); ?></h2>
<p class="text-info"><?php
    echo Text::_('OSM_K2_ITEMS_EXPLAIN'); ?></p>
<?php
echo HTMLHelper::_(
    'bootstrap.startAccordion',
    'k2-categories-accordion',
    ['active' => 'k2-category-' . $activeCategoryId, 'parent' => 'k2-categories-accordion']
);

foreach ($categories as $category) {
    if (!isset($articles[$category->id])) {
        continue;
    }

    echo HTMLHelper::_(
        'bootstrap.addSlide',
        'k2-categories-accordion',
        $category->name,
        'k2-category-' . $category->id
    );
    ?>
	<label class="checkbox">
		<input type="checkbox" value="<?php
        echo $category->id ?>" class="form-check-input k2-category-check-all">
		<strong>Check All</strong>
	</label>
    <?php
    $categoryArticles = $articles[$category->id];

    foreach ($categoryArticles as $article) {
        ?>
		<label class="checkbox" style="display: block;">
			<input type="checkbox" value="<?php
            echo $article->id; ?>"
			       class="form-check-input k2-category-<?php
                   echo $category->id ?> k2-item-checkbox"
                <?php
                if (in_array($article->id, $planArticles)) {
                    echo ' checked="checked" ';
                } ?> />
            <?php
            echo $article->title; ?>
		</label>
        <?php
    }

    echo HTMLHelper::_('bootstrap.endSlide');
}

echo HTMLHelper::_('bootstrap.endAccordion');
?>
<input type="hidden" value="<?php
echo implode(',', $planArticles) ?>" name="k2_item_ids" id="k2_item_ids"/>
