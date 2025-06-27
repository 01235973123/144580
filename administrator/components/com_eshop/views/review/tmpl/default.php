<?php
/**
 * @version		4.0.2
 * @package		Joomla
 * @subpackage	EShop
 * @author  	Giang Dinh Truong
 * @copyright	Copyright (C) 2012 - 2024 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die();

use Joomla\CMS\Editor\Editor;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$editor = Editor::getInstance(Factory::getApplication()->get('editor'));
EShopHelper::chosen();
?>
<script src="<?php echo Uri::root(true); ?>/components/com_eshop/assets/rating/dist/star-rating.js" type="text/javascript"></script>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'review.cancel') {
			Joomla.submitform(pressbutton, form);
			return;				
		} else {
			//Validate the entered data before submitting
			if (form.author.value.length < 3 || form.author.value.length > 25) {
				alert("<?php echo Text::_('ESHOP_ERROR_AUTHOR'); ?>");
				form.author.focus();
				return;
			}
			if (form.email.value == '') {
				alert("<?php echo Text::_('ESHOP_ERROR_EMAIL'); ?>");
				form.email.focus();
				return;
			}
			if (form.review.value.length < 3 || form.review.value.length > 1000) {
				alert("<?php echo Text::_('ESHOP_ERROR_REVIEW'); ?>");
				form.review.focus();
				return;
			}
			if (form.rating.value == '') {
				alert("<?php echo Text::_('ESHOP_ERROR_RATING'); ?>");
				return;
			}
			Joomla.submitform(pressbutton, form);
		}
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PRODUCT'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['products']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_AUTHOR'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="author" id="author" maxlength="128" value="<?php echo $this->item->author; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo  Text::_('ESHOP_EMAIL'); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="email" id="email" maxlength="128" value="<?php echo $this->item->email; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_REVIEW'); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="review" cols="40" rows="5"><?php echo $this->item->review; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<span class="required">*</span>
			<?php echo Text::_('ESHOP_RATING'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['rating']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('ESHOP_PUBLISHED'); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="clearfix"></div>
	<script type="text/javascript">
		var starRatingControl = new StarRating( '.star-rating' );
	</script>
	<?php echo HTMLHelper::_( 'form.token' ); ?>
	<input type="hidden" name="option" value="com_eshop" />
	<input type="hidden" name="cid[]" value="<?php echo intval($this->item->id); ?>" />
	<input type="hidden" name="task" value="" />
</form>