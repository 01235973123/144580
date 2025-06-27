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

use Joomla\CMS\Language\Text;

?>
<div class="control-group">
	<div class="control-label">
		<?php echo EShopHtmlHelper::getFieldLabel('social_enable', Text::_('ESHOP_CONFIG_SOCIAL_ENABLE'), Text::_('ESHOP_CONFIG_SOCIAL_ENABLE_DESC')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['social_enable']; ?>
	</div>
</div>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SOCIAL_FACEBOOK'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('app_id', Text::_('ESHOP_CONFIG_SOCIAL_FACEBOOK_APPLICATION_ID'), Text::_('ESHOP_CONFIG_SOCIAL_FACEBOOK_APPLICATION_ID_DESC')); ?>
		</div>
		<div class="controls">
			<input class="input-xlarge form-control" type="text" name="app_id" id="app_id"  value="<?php echo $this->config->app_id; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('button_font', Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_FONT'), Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_FONT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['button_font']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('button_theme', Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_THEME'), Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_THEME_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['button_theme']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('button_language', Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_LANGUAGE'), Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_LANGUAGE_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['button_language']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_facebook_button', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACEBOOK_BUTTON'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACEBOOK_BUTTON_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_facebook_button']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('button_layout', Text::_('ESHOP_CONFIG_SOCIAL_LIKE_BUTTON_LAYOUT'), Text::_('ESHOP_CONFIG_SOCIAL_LIKE_BUTTON_LAYOUT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['button_layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_faces', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACES'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACES_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_faces']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('button_width', Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_WIDTH'), Text::_('ESHOP_CONFIG_SOCIAL_BUTTON_WIDTH_DESC')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="button_width" id="button_width"  value="<?php echo $this->config->button_width; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_facebook_comment', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACEBOOK_COMMENT'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_FACEBOOK_COMMENT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_facebook_comment']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('num_posts', Text::_('ESHOP_CONFIG_SOCIAL_NUMBER_OF_POSTS'), Text::_('ESHOP_CONFIG_SOCIAL_NUMBER_OF_POSTS_DESC')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="num_posts" id="num_posts"  value="<?php echo $this->config->num_posts; ?>" />
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('comment_width', Text::_('ESHOP_CONFIG_SOCIAL_COMMENT_WIDTH'), Text::_('ESHOP_CONFIG_SOCIAL_COMMENT_WIDTH_DESC')); ?>
		</div>
		<div class="controls">
			<input class="input-mini form-control" type="text" name="comment_width" id="comment_width"  value="<?php echo $this->config->comment_width; ?>" />
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SOCIAL_TWITTER'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_twitter_button', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_TWITTER_BUTTON'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_TWITTER_BUTTON_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_twitter_button']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SOCIAL_PINTEREST'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_pinit_button', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_PINIT_BUTTON'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_PINIT_BUTTON_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_pinit_button']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SOCIAL_GOOGLE'); ?></legend>	
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_google_button', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_GOOGLE_BUTTON'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_GOOGLE_BUTTON_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_google_button']; ?>
		</div>
	</div>
</fieldset>
<fieldset class="form-horizontal options-form">
	<legend><?php echo Text::_('ESHOP_CONFIG_SOCIAL_LINKEDIN'); ?></legend>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('show_linkedin_button', Text::_('ESHOP_CONFIG_SOCIAL_SHOW_LINKEDIN_BUTTON'), Text::_('ESHOP_CONFIG_SOCIAL_SHOW_LINKEDIN_BUTTON_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['show_linkedin_button']; ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo EShopHtmlHelper::getFieldLabel('linkedin_layout', Text::_('ESHOP_CONFIG_SOCIAL_LINKEDIN_LAYOUT'), Text::_('ESHOP_CONFIG_SOCIAL_LINKEDIN_LAYOUT_DESC')); ?>
		</div>
		<div class="controls">
			<?php echo $this->lists['linkedin_layout']; ?>
		</div>
	</div>
</fieldset>