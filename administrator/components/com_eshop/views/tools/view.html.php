<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

/**
 * HTML View class for EShop component
 *
 * @static
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopViewTools extends HtmlView
{

	public function display($tpl = null)
	{
		// Check access first
		$mainframe = Factory::getApplication();
		if (!Factory::getUser()->authorise('eshop.tools', 'com_eshop'))
		{
			$mainframe->enqueueMessage(Text::_('ESHOP_ACCESS_NOT_ALLOW'), 'error');
			$mainframe->redirect('index.php?option=com_eshop&view=dashboard');
		}
		else
		{
			parent::display($tpl);
		}
	}

	/**
	 *
	 * Function to create the buttons view.
	 *
	 * @param   string  $link   targeturl
	 * @param   string  $image  path to image
	 * @param   string  $text   image description
	 */
	public function quickiconButton($link, $image, $text, $textConfirm)
	{
		$language = Factory::getLanguage();
		?>
		<div style="float:<?php
		echo ($language->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon">
				<a onclick="confirmation('<?php
				echo $textConfirm; ?>', '<?php
				echo $link; ?>');" title="<?php
				echo $text; ?>" href="#">
					<?php
					echo HTMLHelper::_('image', 'administrator/components/com_eshop/assets/icons/' . $image, $text); ?>
					<span><?php
						echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
}