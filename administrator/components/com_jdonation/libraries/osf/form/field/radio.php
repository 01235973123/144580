<?php
/**
 * Form Field class for the Joomla OSF.
 * Supports a radiolist custom field.
 *
 * @package     Joomla.OSF
 * @subpackage  Form
 */
class OSFFormFieldRadio extends OSFFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *	 
	 */
	protected $type = 'Radio';

	/**
	 * Options for Radiolist
	 * @var array
	 */
	protected $options = array();

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
		$html = array();
		$options = (array) $this->options;
		$attributes = $this->buildAttributes();
		
		// Cải thiện class attributes cho radio
		if($attributes != "") {
			$attributes = str_replace('class="','class="form-check-input ', $attributes);
		} else {
			$attributes = ' class="form-check-input"';
		}
		
		$value = trim($this->value);

		// CSS styles cho radio layout đẹp hơn
		$customCSS = '
		<style>
		.radio-field-container {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 10px;
			margin: 10px 0;
		}
		
		.radio-item {
			display: flex;
			align-items: center;
			padding: 8px 12px;
			border: 1px solid #e0e0e0;
			border-radius: 6px;
			background-color: #fafafa;
			transition: all 0.2s ease;
			cursor: pointer;
		}
		
		.radio-item:hover {
			background-color: #f0f0f0;
			border-color: #ccc;
		}
		
		.radio-item.selected {
			background-color: #e8f4fd;
			border-color: #007cba;
			box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
		}
		
		.radio-item input[type="radio"] {
			margin-right: 8px;
			transform: scale(1.1);
			accent-color: #007cba;
		}
		
		.radio-item label {
			margin: 0;
			cursor: pointer;
			font-weight: 400;
			color: #333;
			flex: 1;
			user-select: none;
		}
		
		.radio-item input[type="radio"]:checked + label {
			color: #007cba;
			font-weight: 500;
		}
		
		.radio-item input[type="radio"]:focus {
			outline: 2px solid #007cba;
			outline-offset: 2px;
		}
		
		/* Animation cho selection */
		.radio-item input[type="radio"]:checked {
			animation: radioSelect 0.2s ease;
		}
		
		@keyframes radioSelect {
			0% { transform: scale(1.1); }
			50% { transform: scale(1.3); }
			100% { transform: scale(1.1); }
		}
		
		/* Responsive cho mobile */
		@media (max-width: 768px) {
			.radio-field-container {
				grid-template-columns: 1fr;
			}
		}
		
		/* Variant cho single column nếu cần */
		.radio-field-container.single-column {
			grid-template-columns: 1fr;
		}
		
		/* Variant cho 3 columns nếu cần */
		.radio-field-container.three-columns {
			grid-template-columns: repeat(3, 1fr);
		}
		
		@media (max-width: 992px) {
			.radio-field-container.three-columns {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		</style>';

		// JavaScript để handle selection styling
		$customJS = '
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const radioContainer = document.getElementById("' . $this->name . '");
			if (radioContainer) {
				const radioItems = radioContainer.querySelectorAll(".radio-item");
				const radioInputs = radioContainer.querySelectorAll("input[type=\"radio\"]");
				
				// Function để update selected state
				function updateSelectedState() {
					radioItems.forEach(item => {
						const radio = item.querySelector("input[type=\"radio\"]");
						if (radio && radio.checked) {
							item.classList.add("selected");
						} else {
							item.classList.remove("selected");
						}
					});
				}
				
				// Initial state
				updateSelectedState();
				
				// Listen for changes
				radioInputs.forEach(radio => {
					radio.addEventListener("change", updateSelectedState);
				});
				
				// Click handler cho toàn bộ item
				radioItems.forEach(item => {
					item.addEventListener("click", function(e) {
						if (e.target.tagName !== "INPUT") {
							const radio = this.querySelector("input[type=\"radio\"]");
							if (radio) {
								radio.checked = true;
								radio.dispatchEvent(new Event("change"));
							}
						}
					});
				});
			}
		});
		</script>';

		// Thêm CSS và JS vào đầu
		$html[] = $customCSS;
		$html[] = $customJS;
		
		// Container chính
		$html[] = '<fieldset id="' . $this->name . '" class="radio-fieldset">';
		$html[] = '<div class="radio-field-container">';
		
		$i = 0;
		foreach ($options as $option) {
			$i++;
			$optionValue = trim($option);
			$checked = ($optionValue == $value) ? ' checked="checked"' : '';
			
			$html[] = '<div class="radio-item' . ($checked ? ' selected' : '') . '">';
			$html[] = '<input type="radio" id="' . $this->name . $i . '" name="' . $this->name . '" value="' .
				htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') . '"' . $checked . $attributes . $this->extraAttributes . '/>';
			$html[] = '<label for="' . $this->name . $i . '">' . htmlspecialchars($option, ENT_COMPAT, 'UTF-8') . '</label>';
			$html[] = '</div>';
		}
		
		$html[] = '</div>';
		$html[] = '</fieldset>';

		return implode('', $html);
	}


    public function getInputSimple($bootstrapHelper = null, $field, $controlGroupAttributes)
	{
		$config = DonationHelper::getConfig();
		$html = array();
		$options = (array) $this->options;
		$attributes = $this->buildAttributes();
		
		// Cải thiện class attributes cho radio
		if($attributes != "") {
			$attributes = str_replace('class="','class="form-check-input ', $attributes);
		} else {
			$attributes = ' class="form-check-input"';
		}
		
		$value = trim($this->value);

		// CSS styles cho radio layout đẹp hơn
		$customCSS = '
		<style>
		.radio-field-simple-container {
			display: grid;
			gap: 10px;
			margin: 15px 0;
		}
		
		/* Dynamic grid columns based on optionsPerRow */
		.radio-field-simple-container.cols-1 { grid-template-columns: 1fr; }
		.radio-field-simple-container.cols-2 { grid-template-columns: repeat(2, 1fr); }
		.radio-field-simple-container.cols-3 { grid-template-columns: repeat(3, 1fr); }
		.radio-field-simple-container.cols-4 { grid-template-columns: repeat(4, 1fr); }
		
		.radio-simple-item {
			display: flex;
			align-items: center;
			padding: 12px 16px;
			border: 2px solid #e0e0e0;
			border-radius: 8px;
			background-color: #fafafa;
			transition: all 0.3s ease;
			cursor: pointer;
			position: relative;
			min-height: 50px;
		}
		
		.radio-simple-item:hover {
			background-color: #f0f7ff;
			border-color: #007cba;
			transform: translateY(-2px);
			box-shadow: 0 4px 12px rgba(0, 124, 186, 0.15);
		}
		
		.radio-simple-item.selected {
			background-color: #e8f4fd;
			border-color: #007cba;
			box-shadow: 0 0 0 3px rgba(0, 124, 186, 0.1);
			transform: translateY(-1px);
		}
		
		.radio-simple-item input[type="radio"] {
			margin-right: 12px;
			transform: scale(1.3);
			accent-color: #007cba;
			flex-shrink: 0;
		}
		
		.radio-simple-item label {
			margin: 0;
			cursor: pointer;
			font-weight: 400;
			color: #333;
			flex: 1;
			line-height: 1.4;
			user-select: none;
			font-size: 15px;
		}
		
		.radio-simple-item input[type="radio"]:checked + label {
			color: #007cba;
			font-weight: 600;
		}
		
		.radio-simple-item input[type="radio"]:focus {
			outline: 3px solid #007cba;
			outline-offset: 2px;
			border-radius: 50%;
		}
		
		/* Field title styling */
		.radio-field-title {
			font-weight: 600;
			color: #333;
			margin-bottom: 12px;
			font-size: 17px;
			line-height: 1.3;
		}
		
		/* Field description styling */
		.fielddescription {
			color: #666;
			font-size: 14px;
			line-height: 1.6;
			margin: 12px 0;
			padding: 12px 16px;
			background-color: #f8f9fa;
			border-left: 4px solid #007cba;
			border-radius: 6px;
			box-shadow: 0 1px 3px rgba(0,0,0,0.1);
		}
		
		/* Animation cho selection */
		.radio-simple-item input[type="radio"]:checked {
			animation: radioSelect 0.3s ease;
		}
		
		@keyframes radioSelect {
			0% { transform: scale(1.3); }
			50% { transform: scale(1.5); }
			100% { transform: scale(1.3); }
		}
		
		/* Selection indicator */
		.radio-simple-item.selected::after {
			content: "✓";
			position: absolute;
			top: 8px;
			right: 12px;
			color: #007cba;
			font-weight: bold;
			font-size: 16px;
			animation: checkMark 0.3s ease;
		}
		
		@keyframes checkMark {
			0% { opacity: 0; transform: scale(0); }
			100% { opacity: 1; transform: scale(1); }
		}
		
		/* Responsive design */
		@media (max-width: 768px) {
			.radio-field-simple-container.cols-2,
			.radio-field-simple-container.cols-3,
			.radio-field-simple-container.cols-4 {
				grid-template-columns: 1fr;
			}
			
			.radio-simple-item {
				padding: 10px 14px;
				min-height: 45px;
			}
			
			.radio-field-title {
				font-size: 16px;
			}
		}
		
		@media (max-width: 992px) {
			.radio-field-simple-container.cols-3,
			.radio-field-simple-container.cols-4 {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		
		/* Fieldset styling */
		.radio-fieldset-simple {
			border: none;
			padding: 0;
			margin: 0;
		}
		
		/* Focus within fieldset */
		.radio-fieldset-simple:focus-within {
			outline: none;
		}
		
		/* Loading state animation nếu cần */
		.radio-simple-item.loading {
			opacity: 0.6;
			pointer-events: none;
		}
		
		.radio-simple-item.loading::before {
			content: "";
			position: absolute;
			top: 50%;
			left: 50%;
			width: 20px;
			height: 20px;
			margin: -10px 0 0 -10px;
			border: 2px solid #007cba;
			border-radius: 50%;
			border-top-color: transparent;
			animation: spin 1s linear infinite;
		}
		
		@keyframes spin {
			to { transform: rotate(360deg); }
		}
		</style>';

		// JavaScript để handle selection styling và interactions
		$customJS = '
		<script>
		document.addEventListener("DOMContentLoaded", function() {
			const fieldset = document.getElementById("' . $this->name . '");
			if (fieldset) {
				const radioItems = fieldset.querySelectorAll(".radio-simple-item");
				const radioInputs = fieldset.querySelectorAll("input[type=\"radio\"]");
				
				// Function để update selected state
				function updateSelectedState() {
					radioItems.forEach(item => {
						const radio = item.querySelector("input[type=\"radio\"]");
						if (radio && radio.checked) {
							item.classList.add("selected");
						} else {
							item.classList.remove("selected");
						}
					});
				}
				
				// Initial state
				updateSelectedState();
				
				// Listen for changes
				radioInputs.forEach(radio => {
					radio.addEventListener("change", function() {
						updateSelectedState();
						
						// Custom event for external listeners
						fieldset.dispatchEvent(new CustomEvent("radioChanged", {
							detail: { value: this.value, name: this.name }
						}));
					});
				});
				
				// Click handler cho toàn bộ item
				radioItems.forEach(item => {
					item.addEventListener("click", function(e) {
						if (e.target.tagName !== "INPUT") {
							const radio = this.querySelector("input[type=\"radio\"]");
							if (radio && !radio.checked) {
								radio.checked = true;
								radio.dispatchEvent(new Event("change"));
							}
						}
					});
					
					// Keyboard support
					item.addEventListener("keydown", function(e) {
						if (e.key === "Enter" || e.key === " ") {
							e.preventDefault();
							const radio = this.querySelector("input[type=\"radio\"]");
							if (radio) {
								radio.checked = true;
								radio.dispatchEvent(new Event("change"));
							}
						}
					});
				});
				
				// Arrow key navigation
				radioInputs.forEach((radio, index) => {
					radio.addEventListener("keydown", function(e) {
						let nextIndex;
						if (e.key === "ArrowDown" || e.key === "ArrowRight") {
							e.preventDefault();
							nextIndex = (index + 1) % radioInputs.length;
						} else if (e.key === "ArrowUp" || e.key === "ArrowLeft") {
							e.preventDefault();
							nextIndex = (index - 1 + radioInputs.length) % radioInputs.length;
						}
						
						if (nextIndex !== undefined) {
							radioInputs[nextIndex].focus();
							radioInputs[nextIndex].checked = true;
							radioInputs[nextIndex].dispatchEvent(new Event("change"));
						}
					});
				});
			}
		});
		</script>';

		// Thêm CSS và JS
		$html[] = $customCSS;
		$html[] = $customJS;

		// Determine số cột
		$optionsPerRow = (int) $this->optionsPerRow;
		if (!$optionsPerRow) {
			$optionsPerRow = 1;
		}
		
		// Container chính
		$html[] = '<fieldset id="' . $this->name . '" class="radio-fieldset-simple" ' . $controlGroupAttributes . '>';
		
		// Field title
		if($field->title) {
			$html[] = '<div class="radio-field-title">' . $field->title . '</div>';
		}
		
		// Field description (above field)
		if($field->description && $config->display_field_description == 'above_field') {
			$html[] = '<div class="fielddescription">' . $field->description . '</div>';
		}
		
		// Radio container với dynamic columns
		$html[] = '<div class="radio-field-simple-container cols-' . $optionsPerRow . '">';
		
		$i = 0;
		foreach ($options as $option) {
			$i++;
			$optionValue = trim($option);
			$checked = ($optionValue == $value) ? ' checked="checked"' : '';
			
			$html[] = '<div class="radio-simple-item' . ($checked ? ' selected' : '') . '" tabindex="0">';
			$html[] = '<input type="radio" id="' . $this->name . $i . '" name="' . $this->name . '" value="' .
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