<?php

/**
 * @package     Joomla.EshopRAD
 * @subpackage  Table
 * @author      Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

use Joomla\CMS\Table\Table;

/**
 * Since the Table class is marked as abstract, we need to define this package so that we can create a Table object without having to creating a file for it.
 * Simply using the syntax $row = new EshopRADTable('#__mycom_mytable', 'id', $db);
 *
 */
class EshopRADTable extends Table
{

}
