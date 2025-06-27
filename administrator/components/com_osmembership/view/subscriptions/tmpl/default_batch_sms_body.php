<?php
/**
 * @package           Joomla
 * @subpackage        Membership Pro
 * @author            Tuan Pham Ngoc
 * @copyright         Copyright (C) 2012 - 2025 Ossolution Team
 * @license           GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<div class="row-fluid form form-horizontal">
	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('OSM_SMS_MESSAGE'); ?>
		</div>
		<div class="controls">
			<textarea class="form-control" name="sms_message" rows="10" cols="75"></textarea>
		</div>
	</div>
	<div class="control-group">
		<strong><?php echo Text::_('OSM_AVAILABLE_TAGS'); ?> :
            [PLAN_ID]
            [PLAN_TITLE]
            [ID]
            [FIRST_NAME]
            [LAST_NAME]
            [ORGANIZATION]
            [ADDRESS]
            [ADDRESS2]
            [CITY]
            [ZIP]
            [STATE]
            [COUNTRY]
            [PHONE]
            [FAX]
            [EMAIL]
            [COMMENT]
            [FROM_DATE]
            [TO_DATE]
            [CREATED_DATE]
            [END_DATE]
            [FROM_PLAN_TITLE]
        </strong>
	</div>
</div>
