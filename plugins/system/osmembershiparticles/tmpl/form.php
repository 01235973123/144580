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
	->registerAndUseScript('plug-system-osmembershiparticles', 'media/com_osmembership/js/plug-system-osmembershiparticles.min.js');
/**
 * Layout variables
 * -----------------
 * @var   array    $categories
 * @var   array    $selectedCategories
 * @var   array    $articles
 * @var   array    $planArticles
 * @var   stdClass $row
 */
?>
<h2><?php
    echo Text::_('OSM_ARTICLES_CATEGORIES'); ?></h2>
<p class="text-info"><?php
    echo Text::_('OSM_ARTICLES_CATEGORIES_EXPLAIN'); ?></p>
<table class="admintable adminform" style="width: 100%;">
    <?php
    $activeCategoryId = 0;

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
						   name="article_categories[]"/> <strong><?php
                        echo $category->title; ?></strong>
				</label>
			</td>
		</tr>
        <?php
    }
    ?>
</table>

<h2><?php
    echo Text::_('OSM_ARTICLES'); ?></h2>
<p class="text-info"><?php
    echo Text::_('OSM_ARTICLES_EXPLAIN'); ?></p>
<?php
echo HTMLHelper::_(
    'bootstrap.startAccordion',
    'categories-accordion',
    ['active' => 'category-' . $activeCategoryId, 'parent' => 'categories-accordion']
);

foreach ($categories as $category) {
    if (!isset($articles[$category->id])) {
        continue;
    }

    echo HTMLHelper::_(
        'bootstrap.addSlide',
        'categories-accordion',
        $category->title,
        'category-' . $category->id
    );
    ?>
	<label class="checkbox">
		<input type="checkbox" value="<?php
        echo $category->id ?>"
		       class="form-check-input category-check-all"/>
		<strong>Check All</strong>
	</label>
    <?php
    $categoryArticles = $articles[$category->id];

    foreach ($categoryArticles as $article) {
        ?>
		<label class="checkbox" style="display: block;">
			<input type="checkbox" value="<?php
            echo $article->id; ?>" class="category-<?php
            echo $category->id ?> article-checkbox form-check-input"
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
echo implode(',', $planArticles) ?>" name="article_ids" id="article_ids"/>