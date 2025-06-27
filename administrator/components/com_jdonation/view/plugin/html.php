<?php

/**
 * @version		3.8
 * @package		Joomla
 * @subpackage	Joom Donation
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2009 - 2023 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined ( '_JEXEC' ) or die ();

use Joomla\Registry\Registry;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
class DonationViewPluginHtml extends OSFViewItem
{
    protected function prepareView()
    {
        parent::prepareView();
        $registry = new Registry ();
        $registry->loadString ( $this->item->params );
        $data = new stdClass ();
        $data->params = $registry->toArray ();
        $form = Form::getInstance ( 'jdonation', JPATH_ROOT . '/components/com_jdonation/payments/' . $this->item->name . '.xml', array (), false, '//config' );
        $form->bind ( $data );
        $this->form = $form;
    }

    protected function addToolbar()
    {
        $helperClass = $this->classPrefix . 'Helper';
        if (is_callable($helperClass . '::getActions'))
        {
            $canDo = call_user_func(array($helperClass, 'getActions'), $this->name, $this->state);
        }
        else
        {
            $canDo = call_user_func(array('OSFHelper', 'getActions'), $this->option, $this->name, $this->state);
        }
        if ($this->item->id)
        {
            $toolbarTitle = $this->languagePrefix . '_' . $this->name . '_EDIT';
        }
        else
        {
            $toolbarTitle = $this->languagePrefix . '_' . $this->name . '_NEW';
        }
        ToolbarHelper::title(Text::_(strtoupper($toolbarTitle)));
        if ($canDo->get('core.edit') || ($canDo->get('core.create')))
        {
            ToolbarHelper::apply('apply', 'JTOOLBAR_APPLY');
            ToolbarHelper::save('save', 'JTOOLBAR_SAVE');
        }
        if ($this->item->id)
        {
            ToolbarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
        }
        else
        {
            ToolbarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
        }
    }
}
