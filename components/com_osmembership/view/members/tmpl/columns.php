<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012-2014 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

Factory::getApplication()
	->getDocument()
	->getWebAssetManager()
	->useScript('core');

/* @var OSMembershipViewMembersHtml $this */

$showAvatar              = $this->params->get('show_avatar', 1);
$showPlan                = $this->params->get('show_plan', 1);
$showSubscriptionDate    = $this->params->get('show_subscription_date', 1);
$showSubscriptionEndDate = $this->params->get('show_subscription_end_date', 0);
$numberColumns           = $this->params->get('number_columns', 2);
$showLinkToProfile       = $this->params->get('show_link_to_detail', 0);
$showMembershipId        = $this->params->get('show_membership_id', 0);
$hideEmptyFields         = $this->params->get('hide_empty_fields', 0);

$span = intval(12 / $numberColumns);

$bootstrapHelper = $this->bootstrapHelper;
$spanClass       = $bootstrapHelper->getClassMapping('span' . $span);
$rowFluidClass   = $bootstrapHelper->getClassMapping('row-fluid');
$span4Class      = $bootstrapHelper->getClassMapping('span4');
$span8Class      = $bootstrapHelper->getClassMapping('span8');
$clearfixClass   = $bootstrapHelper->getClassMapping('clearfix');
$imgCircle       = $bootstrapHelper->getClassMapping('img-circle');

$fieldsData = $this->fieldsData;
$items = $this->items;
$fields = $this->fields;

// Remove first_name and last_name as it is displayed in single name field
for ($i = 0, $n = count($fields); $i < $n; $i++)
{
	if (in_array($fields[$i]->name, ['first_name', 'last_name']))
	{
		unset($fields[$i]);
	}
}

$rootUri   = Uri::root(true);

OSMembershipHelperJquery::responsiveEqualHeight('.osm-user-profile-wrapper', $this->params->get('min_height') ?: 150);
?>
<div id="osm-members-list-columns" class="osm-container">
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
		<<?php echo $hTag; ?> class="osm-page-title"><?php echo $this->params->get('page_heading') ?: Text::_('OSM_MEMBERS_LIST') ; ?></<?php echo $hTag; ?>>
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
	<form method="post" name="adminForm" id="adminForm" action="<?php echo Route::_('index.php?option=com_osmembership&view=members&Itemid=' . $this->Itemid); ?>">
		<fieldset class="filters btn-toolbar <?php echo $clearfixClass; ?>">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo Text::_('OSM_FILTER_SEARCH_MEMBERS_DESC');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo Text::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="form-control hasTooltip" title="<?php echo HTMLHelper::tooltipText('OSM_SEARCH_MEMBERS_DESC'); ?>" />
			</div>
			<?php
			if (count($this->filters))
			{
			?>
				<div class="btn-group <?php echo $this->bootstrapHelper->getClassMapping('pull-right'); ?> btn-members-filter">
					<?php
					foreach ($this->filters as $filter)
					{
						echo $filter;
					}
					?>
				</div>
			<?php
			}
			?>
			<div class="btn-group <?php echo $bootstrapHelper->getClassMapping('pull-left'); ?>">
				<button type="submit" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="<?php echo $bootstrapHelper->getClassMapping('icon-search'); ?>"></span></button>
				<button type="button" class="btn btn-primary hasTooltip" title="<?php echo HTMLHelper::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="<?php echo $bootstrapHelper->getClassMapping('icon-remove'); ?>"></span></button>
			</div>
		</fieldset>
		<div class="clearfix <?php echo $rowFluidClass; ?>">
		<?php
		$i              = 0;
		$numberProfiles = count($items);

		foreach ($items as $item)
		{
			$i++;

			if (!$item->avatar)
			{
				$item->avatar = 'no_avatar.jpg';
			}

			$link = Route::_('index.php?option=com_osmembership&view=member&id=' . $item->id . '&Itemid=' . $this->Itemid);
		?>
			<div class="<?php echo $spanClass; ?>">
				<div class="osm-user-profile-wrapper">
				<?php
				if ($showAvatar)
				{
				?>
				<div class="<?php echo $rowFluidClass; ?>">
					<div class="<?php echo $span4Class; ?>">
						<?php
						if ($showLinkToProfile)
						{
							?>
							<a href="<?php echo $link; ?>"><img class="oms-avatar <?php echo $imgCircle; ?>" src="<?php echo $rootUri . '/media/com_osmembership/avatars/' . $item->avatar; ?>"/></a>
							<?php
						}
						else
						{
						?>
							<img class="oms-avatar <?php echo $imgCircle; ?>" src="<?php echo $rootUri . '/media/com_osmembership/avatars/' . $item->avatar; ?>"/>
						<?php
						}
						?>
					</div>
					<div class="<?php echo $span8Class; ?>">
				<?php
				}
				?>
				<div class="profile-name">
					<?php
					if ($showLinkToProfile)
					{
					?>
						<a href="<?php echo $link; ?>"><?php echo rtrim($item->first_name . ' ' . $item->last_name); ?></a>
					<?php
					}
					else
					{
					?>
						<?php echo rtrim($item->first_name . ' ' . $item->last_name); ?>
					<?php
					}
					?>
				</div>
				<table class="<?php echo $bootstrapHelper->getClassMapping('table table-striped'); ?>">
					<?php
					if ($showMembershipId)
					{
					?>
                        <tr>
                            <td class="osm-profile-field-title">
	                            <?php echo Text::_('OSM_MEMBERSHIP_ID'); ?>:
                            </td>
                            <td>
	                            <?php echo OSMembershipHelper::formatMembershipId($item, $this->config); ?>
                            </td>
                        </tr>
                    <?php
					}

					if ($showPlan)
					{
					?>
						<tr>
							<td class="osm-profile-field-title">
								<?php echo Text::_('OSM_PLAN'); ?>:
							</td>
							<td>
								<?php echo $item->plan_title; ?>
							</td>
						</tr>
					<?php
					}

					if ($showSubscriptionDate)
					{
					?>
						<tr>
							<td class="osm-profile-field-title">
								<?php echo Text::_('OSM_SUBSCRIPTION_DATE'); ?>:
							</td>
							<td>
								<?php echo HTMLHelper::_('date', $item->created_date, $this->config->date_format); ?>
							</td>
						</tr>
					<?php
					}

					if ($showSubscriptionEndDate)
					{
					?>
                        <tr>
                            <td class="osm-profile-field-title">
								<?php echo Text::_('OSM_SUBSCRIPTION_END_DATE'); ?>:
                            </td>
                            <td>
								<?php echo HTMLHelper::_('date', $item->plan_subscription_to_date, $this->config->date_format); ?>
                            </td>
                        </tr>
					<?php
					}

					foreach($fields as $field)
					{
						if ($field->is_core)
						{
							$fieldValue = $item->{$field->name};
						}
						elseif (isset($fieldsData[$item->id][$field->id]))
						{
							$fieldValue = $fieldsData[$item->id][$field->id];
						}
						else
						{
							$fieldValue = '';
						}

						if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
						{
							$fieldValue = implode(', ', array_filter(json_decode($fieldValue)));
						}
						elseif ($field->fieldtype == 'Date' && $fieldValue)
						{
							try
							{
								$fieldValue = HTMLHelper::_('date', $fieldValue, $this->config->date_format, null);
							}
							catch (Exception $e)
							{
								// Do-nothing
							}
						}

						if ($hideEmptyFields && strlen($fieldValue) == 0)
						{
							continue;
						}

						if (filter_var($fieldValue, FILTER_VALIDATE_URL))
						{
							$fieldValue = '<a href="' . $fieldValue . '" target="_blank">' . $fieldValue . '</a>';
						}
						elseif (filter_var($fieldValue, FILTER_VALIDATE_EMAIL))
						{
							$fieldValue = '<a href="mailto:' . $fieldValue . '">' . $fieldValue . '</a>';
						}

						?>
						<tr>
							<td class="osm-profile-field-title">
								<?php echo $field->title; ?>:
							</td>
							<td class="osm-profile-field-value">
								<?php echo $fieldValue; ?>
							</td>
						</tr>
					<?php
					}
					?>
				</table>
				<?php
				if ($showAvatar)
				{
				?>
					</div>
				</div>
				<?php
				}
				?>
				</div>
			</div>
			<?php
			if ($i % $numberColumns == 0 && $i < $numberProfiles)
			{
			?>
				</div>
				<div class="clearfix <?php echo $rowFluidClass; ?>">
			<?php
			}
		}
		?>
		</div>
		
		<?php
			if ($this->pagination->total > $this->pagination->limit)
			{
			?>			
				<div class="pagination">
					<?php echo $this->pagination->getPagesLinks(); ?>
				</div>					
			<?php
			}

			if (count($items) == 0)
			{
			?>
                <p class="text-info"><?php echo Text::_('OSM_NO_MEMBERS_FOUND'); ?></p>
            <?php
			}
		?>
	</form>
</div>