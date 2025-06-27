<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/**
 * Layout variables
 *
 * @var string $controlGroupClass
 *
 */

$articleId = $this->plan->terms_and_conditions_article_id ?: $this->config->article_id;

if ($articleId > 0)
{
	$articleUrl = OSMembershipHelperHtml::getArticleUrl($articleId);

	if ($articleUrl)
	{
		OSMembershipHelperModal::iframeModal('.osm-modal');
	?>
		<div class="<?php echo $controlGroupClass ?> osm-terms-and-conditions-container">
			<label class="checkbox">
				<input type="checkbox" id="osm-accept-terms-conditions" name="accept_term" value="1" class="validate[required]<?php echo $this->bootstrapHelper->getFrameworkClass('uk-checkbox', 1); ?>" />
				<?php echo Text::_('OSM_ACCEPT'); ?>&nbsp;<a href="<?php echo Route::_($articleUrl . '&tmpl=component&format=html'); ?>" class="osm-modal"><?php echo Text::_('OSM_TERM_AND_CONDITION'); ?></a>
			</label>
		</div>
	<?php
	}
}
