<?php
/**
 * @package     MPF
 * @subpackage  Event
 *
 * @copyright   Copyright (C) 2015 Ossolution Team, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class MPFEventModelSave extends MPFEventBase
{
	protected $requiredArguments = ['context', 'row', 'data', 'isNew'];
}