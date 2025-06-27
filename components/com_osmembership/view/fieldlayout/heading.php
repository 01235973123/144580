<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/**
 * Layout variables
 *
 * @var stdClass $row
 * @var string   $controlGroupAttributes
 * @var string   $title
 */
?>
<h3 class="osm-heading" <?php echo $controlGroupAttributes; ?>><?php echo Text::_($title); ?></h3>
