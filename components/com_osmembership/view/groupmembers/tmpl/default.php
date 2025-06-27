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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;

$rowFluidClass = $this->bootstrapHelper->getClassMapping('row-fluid');
$clearFixClass = $this->bootstrapHelper->getClassMapping('clearfix');
$centerClass   = $this->bootstrapHelper->getClassMapping('center');
$fields = $this->fields;
$cols = count($fields) + 4;
?>
<div id="osm-subscription-history" class="osm-container osm-container-j4">
	<?php
	if ($this->params->get('show_page_heading', 1))
	{
		if ($this->input->getInt('hmvc_call'))
		{
			$hTag = 'h2';
		}
		else
		{
			$hTag = 'h1';
		}
	?>
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo Text::_('OSM_GROUP_MEMBERS_LIST') ;?></<?php echo $hTag; ?>>
	<?php
	}

	if (OSMembershipHelper::isValidMessage($this->params->get('intro_text')))
	{
	?>
		<div class="osm-description osm-page-intro-text <?php echo $this->bootstrapHelper->getClassMapping('clearfix'); ?>">
			<?php echo HTMLHelper::_('content.prepare', $this->params->get('intro_text')); ?>
		</div>
	<?php
	}
	?>
    <div class="btn-toolbar" id="btn-toolbar">
		<?php echo Toolbar::getInstance('toolbar')->render(); ?>
    </div>
    <form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=groupmembers&Itemid=' . $this->Itemid); ?>">
        <fieldset class="filters btn-toolbar <?php echo $clearFixClass; ?>">
            <?php echo $this->loadTemplate('search'); ?>
        </fieldset>
        <table class="<?php echo $this->bootstrapHelper->getClassMapping('table table-striped table-bordered table-hover'); ?>">
            <thead>
                <tr>
                    <th width="20">
		                <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </th>
                    <th><?php echo Text::_('OSM_PLAN'); ?></th>
	                <th><?php echo Text::_('OSM_USERNAME'); ?></th>
                    <?php
						foreach($fields as $field)
						{
						?>
                            <th><?php echo $field->title; ?></th>
                        <?php
						}

						if ($this->config->auto_generate_membership_id)
						{
							$cols++ ;
						?>
                            <th width="8%" class="<?php echo $centerClass; ?>">
                                <?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>
                            </th>
                        <?php
						}
					?>
                    <th class="<?php echo $centerClass; ?>">
                        <?php echo Text::_('OSM_CREATED_DATE') ; ?>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php
				for ($i = 0 , $n = count($this->items) ; $i < $n ; $i++)
				{
					$row  = $this->items[$i];
					$link = Route::_('index.php?option=com_osmembership&view=groupmember&id=' . $row->id . '&Itemid=' . $this->Itemid);
				?>
                    <tr>
                        <td>
                            <?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
                        </td>
                        <td>
                            <a href="<?php echo $link; ?>"><?php echo $row->plan_title;?></a>
                        </td>
	                    <td>
		                    <?php echo $row->username; ?>
	                    </td>
                        <?php
						foreach ($fields as $field)
						{
						?>
                            <td>
                                <?php
									if ($field->is_core)
									{
										echo $row->{$field->name};
									}
									elseif (isset($this->fieldsData[$row->id][$field->id]))
									{
										echo $this->fieldsData[$row->id][$field->id];
									}
								?>
                            </td>
                        <?php
						}

						if ($this->config->auto_generate_membership_id)
						{
						?>
                            <td class="<?php echo $centerClass; ?>">
                                <?php echo $row->membership_id ? OSMembershipHelper::formatMembershipId($row, $this->config) : ''; ?>
                            </td>
                        <?php
						}
						?>
                        <td class="<?php echo $centerClass; ?>">
                            <?php echo HTMLHelper::_('date', $row->created_date, $this->config->date_format); ?>
                        </td>
                    </tr>
                <?php
				}
				?>
                </tbody>
                <?php
				if ($this->pagination->total > $this->pagination->limit)
				{
				?>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $cols; ?>">
                            <div class="pagination"><?php echo $this->pagination->getPagesLinks(); ?></div>
                        </td>
                    </tr>
                </tfoot>
                <?php
				}
			?>
        </table>
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
</div>
