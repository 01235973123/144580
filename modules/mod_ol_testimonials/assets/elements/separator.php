<?php
defined('JPATH_BASE') or die;
jimport('joomla.form.formfield');
class JFormFieldSeparator extends JFormField {
	protected $type = 'Separator';
	protected function getInput() {
		$text  	= (string) $this->element['text'];
		return '<div id="'.$this->id.'" class="mmSeparator'.(($text != '') ? ' hasText' : '').'" title="'. JText::_($this->element['desc']) .'"><span>' . JText::_($text) . '</span></div>';
	}
}
/* EOF */