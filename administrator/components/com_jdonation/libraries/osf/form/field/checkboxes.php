<?php

use Joomla\CMS\Factory;
/**
 * Form Field class for the Joomla OSF.
 * Supports a checkbox list custom field.
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldCheckboxes extends OSFFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *	 
	 */
	protected $type = 'Checkboxes';

	/**
	 * Options for checkbox lists
	 * @var array
	 */
	protected $options = [];

	/**
	 * Number options displayed perrow
	 * @var int
	 */
	protected $optionsPerRow = 1;

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param   JTable  $row  the table object store form field definitions
	 * @param	mixed	$value the initial value of the form field
	 *
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);
		if ((int) $row->size)
		{
			$this->optionsPerRow = (int) $row->size;
		}
		if (is_array($row->values))
		{
			$this->options = $row->values;
		}
		elseif (strpos($row->values, "\r\n") !== FALSE)
		{
			$this->options = explode("\r\n", $row->values);
		}
		else
		{
			$this->options = explode(",", $row->values);
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 */
	public function getInput($bootstrapHelper = null)
	{
		$html = [];
		$options = $this->options;
		$attributes = $this->buildAttributes();
		
		// Cải thiện class attributes cho checkbox
		if($attributes != "") {
			$attributes = str_replace('class="','class="form-check-input ', $attributes);
		} else {
			$attributes = ' class="form-check-input"';
		}
		
		// Xử lý selected options
		if (is_array($this->value)) {
			$selectedOptions = $this->value;
		} elseif (strpos($this->value, "\r\n")) {
			$selectedOptions = explode("\r\n", $this->value);
		} elseif (is_string($this->value) && is_array(json_decode($this->value))) {
			$selectedOptions = json_decode($this->value);
		} else {
			$selectedOptions = array($this->value);
		}

		// CSS styles cho layout đẹp hơn
		$customCSS = '
		<style>
		.checkbox-field-container {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
			margin: 10px 0;
		}
		
		.checkbox-item {
			display: flex;
			align-items: center;
			padding: 8px 12px;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			background-color: #fafafa;
			transition: all 0.2s ease;
		}
		
		.checkbox-item:hover {
			background-color: #f0f0f0;
			border-color: #ccc;
		}
		
		.checkbox-item input[type="checkbox"] {
			margin-right: 8px;
			transform: scale(1.1);
		}
		
		.checkbox-item label {
			margin: 0;
			cursor: pointer;
			font-weight: 400;
			color: #333;
			flex: 1;
		}
		
		.checkbox-item input[type="checkbox"]:checked + label {
			color: #007cba;
			font-weight: 500;
		}
		
		/* Responsive cho mobile */
		@media (max-width: 768px) {
			.checkbox-field-container {
				grid-template-columns: 1fr;
			}
		}
		</style>';

		// Thêm CSS vào đầu
		$html[] = $customCSS;
		
		// Container chính
		$html[] = '<fieldset id="' . $this->name . '" class="checkbox-fieldset">';
		$html[] = '<div class="checkbox-field-container">';
		
		$i = 0;
		foreach ($options as $option) {
			$i++;
			$optionValue = trim($option);
			
			if($optionValue != '') {
				$checked = in_array($optionValue, $selectedOptions) ? ' checked="checked"' : '';
			} else {
				$checked = '';
			}
			
			$html[] = '<div class="checkbox-item">';
			$html[] = '<input type="checkbox" id="' . $this->name . $i . '" name="' . $this->name . '[]" value="' .
				htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') . '"' . $checked . $attributes . $this->extraAttributes . '/>';
			$html[] = '<label for="' . $this->name . $i . '">' . htmlspecialchars($option, ENT_COMPAT, 'UTF-8') . '</label>';
			$html[] = '</div>';
		}
		
		$html[] = '</div>';
		$html[] = '</fieldset>';

		return implode('', $html);
	}


    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     */
    public function getInputSimple($bootstrapHelper = null, $field, $controlGroupAttributes, $campaignId = 0)
	{
		$config = DonationHelper::getConfig();
		$html = [];
		$options = $this->options;
		$attributes = $this->buildAttributes();
		
		// Cải thiện class attributes cho checkbox
		if($attributes != "") {
			$attributes = str_replace('class="','class="form-check-input ', $attributes);
		} else {
			$attributes = ' class="form-check-input"';
		}
		
		// Xử lý selected options
		if (is_array($this->value)) {
			$selectedOptions = $this->value;
		} elseif (strpos($this->value, "\r\n")) {
			$selectedOptions = explode("\r\n", $this->value);
		} elseif (is_string($this->value) && is_array(json_decode($this->value))) {
			$selectedOptions = json_decode($this->value);
		} else {
			$selectedOptions = [$this->value];
		}

		// Campaign class
		$campaignClass = '';
		if($campaignId > 0) {
			$campaignClass = 'campaign_'.$campaignId . ' ';
		}

		// CSS styles cho layout đẹp hơn
		$customCSS = '
		<style>
		.checkbox-field-simple-container {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 12px;
			margin: 15px 0;
		}
		
		.checkbox-simple-item {
			display: flex;
			align-items: center;
			padding: 10px 14px;
			border: 1px solid #ddd;
			border-radius: 8px;
			background-color: #f9f9f9;
			transition: all 0.3s ease;
			cursor: pointer;
			position: relative;
		}
		
		.checkbox-simple-item:hover {
			background-color: #f0f7ff;
			border-color: #007cba;
			transform: translateY(-1px);
			box-shadow: 0 2px 8px rgba(0, 124, 186, 0.1);
		}
		
		.checkbox-simple-item.checked {
			background-color: #e8f4fd;
			border-color: #007cba;
			box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
		}
		
		.checkbox-simple-item input[type="checkbox"] {
			margin-right: 10px;
			transform: scale(1.2);
			accent-color: #007cba;
		}
		
		.checkbox-simple-item label {
			margin: 0;
			cursor: pointer;
			font-weight: 400;
			color: #333;
			flex: 1;
			line-height: 1.4;
			user-select: none;
		}
		
		.checkbox-simple-item input[type="checkbox"]:checked + label {
			color: #007cba;
			font-weight: 500;
		}
		
		.checkbox-simple-item input[type="checkbox"]:focus {
			outline: 2px solid #007cba;
			outline-offset: 2px;
		}
		
		/* Field title styling */
		.checkbox-field-title {
			font-weight: 600;
			color: #333;
			margin-bottom: 10px;
			font-size: 16px;
		}
		
		/* Field description styling */
		.fielddescription {
			color: #666;
			font-size: 14px;
			line-height: 1.5;
			margin: 10px 0;
			padding: 8px 12px;
			background-color: #f8f9fa;
			border-left: 3px solid #007cba;
			border-radius: 4px;
		}
		
		/* Animation cho selection */
		.checkbox-simple-item input[type="checkbox"]:checked {
			animation: checkboxSelect 0.2s ease;
		}
		
		@keyframes checkboxSelect {
			0% { transform: scale(1.2); }
			50% { transform: scale(1.4); }
			100% { transform: scale(1.2); }
		}
		
		/* Responsive design */
		@media (max-width: 768px) {
			.checkbox-field-simple-container {
				grid-template-columns: 1fr;
				gap: 8px;
			}
			
			.checkbox-simple-item {
				padding: 8px 12px;
			}
		}
		
		/* Variants cho số cột khác nhau */
		.checkbox-field-simple-container.single-column {
			grid-template-columns: 1fr;
		}
		
		.checkbox-field-simple-container.three-columns {
			grid-template-columns: repeat(3, 1fr);
		}
		
		.checkbox-field-simple-container.four-columns {
			grid-template-columns: repeat(4, 1fr);
		}
		
		@media (max-width: 992px) {
			.checkbox-field-simple-container.three-columns,
			.checkbox-field-simple-container.four-columns {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		
		/* Campaign specific styling nếu cần */
		.campaign_' . $campaignId . ' .checkbox-simple-item {
			border-color: #e0e0e0;
		}
		
		.campaign_' . $campaignId . ' .checkbox-simple-item:hover {
			border-color: #007cba;
		}
		</style>';

		// JavaScript để handle interactive behavior
		$customJS = '
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const fieldset = document.getElementById("' . $this->name . '");
			if (fieldset) {
				const checkboxItems = fieldset.querySelectorAll(".checkbox-simple-item");
				const checkboxInputs = fieldset.querySelectorAll("input[type=\"checkbox\"]");
				
				// Function để update checked state
				function updateCheckedState() {
					checkboxItems.forEach(item => {
						const checkbox = item.querySelector("input[type=\"checkbox\"]");
						if (checkbox && checkbox.checked) {
							item.classList.add("checked");
						} else {
							item.classList.remove("checked");
						}
					});
				}
				
				// Initial state
				updateCheckedState();
				
				// Listen for changes
				checkboxInputs.forEach(checkbox => {
					checkbox.addEventListener("change", updateCheckedState);
				});
				
				// Click handler cho toàn bộ item
				checkboxItems.forEach(item => {
					item.addEventListener("click", function(e) {
						if (e.target.tagName !== "INPUT") {
							const checkbox = this.querySelector("input[type=\"checkbox\"]");
							if (checkbox) {
								checkbox.checked = !checkbox.checked;
								checkbox.dispatchEvent(new Event("change"));
							}
						}
					});
				});
			}
		});
		</script>';

		// Thêm CSS và JS
		$html[] = $customCSS;
		$html[] = $customJS;

		// Container chính
		$html[] = '<fieldset id="' . $this->name . '" class="checkbox-fieldset-simple ' . $campaignClass . '" ' . $controlGroupAttributes . '>';
		
		// Field title
		if($field->title) {
			$html[] = '<div class="checkbox-field-title">' . $field->title . '</div>';
		}
		
		// Field description (above field)
		if($field->description && $config->display_field_description == 'above_field') {
			$html[] = '<div class="fielddescription">' . $field->description . '</div>';
		}
		
		// Determine số cột dựa trên optionsPerRow
		$optionsPerRow = (int) $this->optionsPerRow;
		if (!$optionsPerRow) {
			$optionsPerRow = 2; // Default 2 cột
		}
		
		$columnClass = '';
		switch($optionsPerRow) {
			case 1:
				$columnClass = 'single-column';
				break;
			case 3:
				$columnClass = 'three-columns';
				break;
			case 4:
				$columnClass = 'four-columns';
				break;
			default:
				$columnClass = ''; // 2 columns default
		}
		
		// Checkbox container
		$html[] = '<div class="checkbox-field-simple-container ' . $columnClass . '">';
		
		$i = 0;
		foreach ($options as $option) {
			$i++;
			$optionValue = trim($option);
			$checked = in_array($optionValue, $selectedOptions) ? ' checked="checked"' : '';
			
			$html[] = '<div class="checkbox-simple-item' . ($checked ? ' checked' : '') . '">';
			$html[] = '<input type="checkbox" id="' . $this->name . $i . '" name="' . $this->name . '[]" value="' .
				htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') . '"' . $checked . $attributes . $this->extraAttributes . '/>';
			$html[] = '<label for="' . $this->name . $i . '">' . htmlspecialchars($option, ENT_COMPAT, 'UTF-8') . '</label>';
			$html[] = '</div>';
		}
		
		$html[] = '</div>';
		
		// Field description (under field)
		if($field->description && $config->display_field_description == 'under_field') {
			$html[] = '<div class="fielddescription">' . $field->description . '</div>';
		}

		$html[] = '</fieldset>';

		return implode('', $html);
	}

}
