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

/**
 * Layout variables
 * -----------------
 * @var   int                 $supportJoomlaUpdate
 * @var   array               $documents
 * @var   array               $existingDocuments
 * @var   array               $updatePackages
 * @var   array               $options
 * @var   array               $planExistingDocumentIds
 * @var OSMembershipTablePlan $row
 */

HTMLHelper::_('jquery.framework');
?>
<table class="adminlist table table-striped">
    <tr>
        <td width="20%">
            <?php echo Text::_('OSM_CHOOSE_EXISTING_DOCUMENTS'); ?>
        </td>
        <td>
            <?php echo OSMembershipHelperHtml::getChoicesJsSelect(HTMLHelper::_('select.genericlist', $existingDocuments, 'existing_document_ids[]', 'class="advSelect input-xlarge form-select" multiple', 'id', 'title', $planExistingDocumentIds)); ?>
        </td>
    </tr>
</table>
<table class="adminlist table table-striped" id="adminForm">
    <thead>
    <tr>
        <th class="nowrap center"><?php echo Text::_('ID'); ?></th>
        <th class="nowrap center"><?php echo Text::_('OSM_TITLE'); ?></th>
        <th class="nowrap center"><?php echo Text::_('OSM_ORDERING'); ?></th>
        <th class="nowrap center"><?php echo Text::_('OSM_DOCUMENT'); ?></th>
        <?php
        if ($supportJoomlaUpdate)
        {
        ?>
            <th class="nowrap center"><?php echo Text::_('OSM_UPDATE_PACKAGE'); ?></th>
        <?php
        }
        ?>
        <th class="nowrap center"><?php echo Text::_('OSM_REMOVE'); ?></th>
    </tr>
    </thead>
    <tbody id="additional_documents">
    <?php
    for ($i = 0; $i < count($documents); $i++)
    {
        $document = $documents[$i];
        ?>
        <tr id="document_<?php echo $i; ?>">
            <td class="center">
                <?php if ($document->id) echo $document->id; ?>
                <input type="hidden" name="document_id[]" value="<?php echo $document->id; ?>"/>
            </td>
            <td><input type="text" class="form-control input-xlarge" name="document_title[]"
                       value="<?php echo $document->title; ?>"/></td>
            <td><input type="text" class="form-control input-mini" name="document_ordering[]"
                       value="<?php echo $document->ordering; ?>"/></td>
            <td><input type="file" name="document_attachment[]"
                       value=""><?php echo HTMLHelper::_('select.genericlist', $options, 'document_available_attachment[]', 'class="form-select input-xlarge"', 'value', 'text', $document->attachment); ?>
            </td>
            <?php
            if ($supportJoomlaUpdate)
            {
            ?>
                <td><?php echo HTMLHelper::_('select.genericlist', $updatePackages, 'update_package[]', 'class="form-select input-xlarge"', 'value', 'text', $document->update_package); ?></td>
            <?php
            }
            ?>
            <td>
                <button type="button" class="btn btn-danger"
                        onclick="removeDocument(<?php echo $i; ?>)"><i
                        class="icon-remove"></i><?php echo Text::_('OSM_REMOVE'); ?></button>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<button type="button" class="btn btn-success" onclick="adddocument()"><i
        class="icon-new icon-white"></i><?php echo Text::_('OSM_ADD'); ?></button>
<script type="text/javascript">
    (function ($) {
        removeDocument = (function (id) {
            if (confirm('<?php echo Text::_('OSM_REMOVE_ITEM_CONFIRM', true); ?>')) {
                $('#document_' + id).remove();
            }
        });
        var countDocument = <?php echo count($documents) ?>;
        adddocument = (function () {
            var html = '<tr id="document_' + countDocument + '">'
            html += '<td><input type="hidden" name="document_id[]" value="0" /></td>';
            html += '<td><input type="text" class="form-control input-xlarge" name="document_title[]" value="" /><input type="hidden" name="document_id[]" value="0" /></td>';
            html += '<td><input type="text" class="form-control input-mini" name="document_ordering[]" value="" /></td>';
            html += '<td><input type="file" name="document_attachment[]" value=""><?php echo preg_replace(['/\r/', '/\n/'], '', addslashes(HTMLHelper::_('select.genericlist', $options, 'document_available_attachment[]', 'class="input-xlarge form-select"', 'value', 'text', ''))); ?></td>';
			<?php
			if ($supportJoomlaUpdate)
			{
			?>
            html += '<td><?php echo preg_replace(['/\r/', '/\n/'], '', addslashes(HTMLHelper::_('select.genericlist', $updatePackages, 'update_package[]', 'class="form-select input-xlarge"', 'value', 'text', ''))); ?></td>';
			<?php
			}
			?>
            html += '<td><button type="button" class="btn btn-danger" onclick="removeDocument(' + countDocument + ')"><i class="icon-remove"></i><?php echo Text::_('OSM_REMOVE'); ?></button></td>';
            html += '</tr>';
            $('#additional_documents').append(html);
            countDocument++;
        })
    })(jQuery)
</script>
