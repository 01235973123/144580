<?php
/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2023 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */


defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Form\Form;

abstract class DonationHelperHtml
{
	/**
	 * Function to render a common layout which is used in different views
	 *
	 * @param string $layout Relative path to the layout file
	 * @param array  $data   An array contains the data passed to layout for rendering
	 */
	public static function loadCommonLayout($layout, $data = array())
	{
		if(file_exists(JPATH_ROOT . '/components/com_jdonation/helper/encrypt.php'))
		{
			require_once JPATH_ROOT . '/components/com_jdonation/helper/encrypt.php';
		}
		$app       = Factory::getApplication();
		$themeFile = str_replace('/tmpl', '', $layout);
		if (file_exists($layout))
		{
			$path = $layout;
		}
		elseif (file_exists(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_jdonation/' . $themeFile))
		{
			$path = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_jdonation/' . $themeFile;
		}
		elseif (file_exists(JPATH_ROOT . '/components/com_jdonation/view/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_jdonation/view/' . $layout;
		}
		else
		{
			throw new RuntimeException(\Joomla\CMS\Language\Text::_('The given shared template path is not exist'));
		}
		// Start an output buffer.
		ob_start();
		extract($data);
		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Format the amount based on the config
	 *
	 * @param      $config
	 * @param      $amount
	 * @param null $currencySymbol
	 *
	 * @return string
	 */ 
	public static function formatAmount($config, $amount, $currencySymbol = null, $showcurrency = 1)
	{
		$decimals      = isset($config->decimals) ? $config->decimals : 2;
		$dec_point     = isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';
		$symbol        = $currencySymbol ? $currencySymbol : $config->currency_symbol;

		if($config->currency_space)
		{
			$space = ' ';
		}
		else
		{
			$space = '';
		}

		if($showcurrency == 0){
			$symbol = '';
		}

		return $config->currency_position ? (number_format($amount, $decimals, $dec_point, $thousands_sep) .$space. $symbol) : ($symbol .$space. number_format($amount, $decimals, $dec_point, $thousands_sep));
	}


	public static function ago($time)
	{
		$periods = array(\Joomla\CMS\Language\Text::_('JD_SECOND'),\Joomla\CMS\Language\Text::_('JD_MINUTE'),\Joomla\CMS\Language\Text::_('JD_HOUR'),\Joomla\CMS\Language\Text::_('JD_DAY'),\Joomla\CMS\Language\Text::_('JD_WEEK'),\Joomla\CMS\Language\Text::_('JD_MONTH'),\Joomla\CMS\Language\Text::_('JD_YEAR'),\Joomla\CMS\Language\Text::_('JD_DECADE'));
		$periods1 = array(\Joomla\CMS\Language\Text::_('JD_SECONDS'),\Joomla\CMS\Language\Text::_('JD_MINUTES'),\Joomla\CMS\Language\Text::_('JD_HOURS'),\Joomla\CMS\Language\Text::_('JD_DAYS'),\Joomla\CMS\Language\Text::_('JD_WEEKS'),\Joomla\CMS\Language\Text::_('JD_MONTHS'),\Joomla\CMS\Language\Text::_('JD_YEARS'),\Joomla\CMS\Language\Text::_('JD_DECADES'));
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");

		$difference = Factory::getDate('now')->toUnix() - $time;

		for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++)
		{
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if ($difference != 1)
		{
			$periods[$j] = $periods1[$j];
		}

		return "$difference $periods[$j] ".\Joomla\CMS\Language\Text::_('JD_AGO');
	}

	/**
	 * Function to add dropdown menu
	 *
	 * @param string $vName
	 */
	public static function renderSubmenu($vName = 'dashboard')
	{
		HTMLHelper::_('bootstrap.dropdown');
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__jd_menus')
			->where('published = 1')
			->where('menu_parent_id = 0')
			->order('ordering');
		$db->setQuery($query);
		$menus = $db->loadObjectList();
		$html  = '';
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			$html .= '<ul id="jd-dropdown-menu" class="nav nav-tabs nav-hover joomdonation-joomla4">';
		}
		else
		{
			$html .= '<div class="clearfix"></div><ul id="mp-dropdown-menu" class="nav nav-tabs nav-hover">';
		}

		$currentLink = 'index.php' . Uri::getInstance()->toString(array('query'));
		for ($i = 0; $n = count($menus), $i < $n; $i++)
		{
			$menu = $menus[$i];
			$query->clear();
			$query->select('*')
				->from('#__jd_menus')
				->where('published = 1')
				->where('menu_parent_id = ' . intval($menu->id))
				->order('ordering');
			$db->setQuery($query);
			$subMenus = $db->loadObjectList();

			if (!count($subMenus))
			{
				$class = '';
				if ($menu->menu_link == $currentLink)
				{
					$class = ' class="active"';
					$extraClass = 'active';
				}
				else
				{
					$class = '';
					$extraClass = '';
				}
				$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="' . $menu->menu_link . '"><span class="icon-' . $menu->menu_class . '"></span> ' . \Joomla\CMS\Language\Text::_($menu->menu_name) .
					'</a></li>';
			}
			else
			{
				$class = ' class="dropdown"';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu = $subMenus[$j];
					if ($subMenu->menu_link == $currentLink)
					{
						$class = ' class="dropdown active"';
						break;
					}
				}
				$html .= '<li' . $class . '>';
				if(DonationHelper::isJoomla4())
				{
					$dropdownToggle = 'data-bs-toggle="dropdown"';
				}
				else
				{
					$dropdownToggle = 'data-toggle="dropdown"';
				}
				$html .= '<a id="drop_' . $menu->id . '" href="#" '.$dropdownToggle.' role="button" class="nav-link dropdown-toggle"><span class="icon-' . $menu->menu_class . '"></span> ' .
					\Joomla\CMS\Language\Text::_($menu->menu_name) . ' <b class="caret"></b></a>';
				$html .= '<ul aria-labelledby="drop_' . $menu->id . '" role="menu" class="dropdown-menu" id="menu_' . $menu->id . '">';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu = $subMenus[$j];
					$class   = '';

					$vars = array();
					parse_str($subMenu->menu_link, $vars);
					$view = isset($vars['view']) ? $vars['view'] : '';

					if ($subMenu->menu_link == $currentLink)
					{
						$class = ' class="active"';
						$extraClass = 'active';
					}
					else
					{
						$class = '';
						$extraClass = '';
					}
					$html .= '<li' . $class . '><a class="nav-link dropdown-item ' . $extraClass . '" href="' . $subMenu->menu_link .
						'" tabindex="-1"><span class="icon-' . $subMenu->menu_class . '"></span> ' . \Joomla\CMS\Language\Text::_($subMenu->menu_name) . '</a></li>';
				}
				$html .= '</ul>';
				$html .= '</li>';
			}
		}
		$html .= '</ul>';

		echo $html;
	}

    public static function showCheckboxfield($name, $value ,$option1='',$option2='')
    {
        if($option1 == ""){
            $option1 = \Joomla\CMS\Language\Text::_('JNO');
        }
        if($option2 == ""){
            $option2 = \Joomla\CMS\Language\Text::_('JYES');
        }

        HTMLHelper::_('jquery.framework');
        $field = FormHelper::loadFieldType('Radio');

        $element = new SimpleXMLElement('<field />');
        $element->addAttribute('name', $name);

        if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
        {
           $element->addAttribute('layout', 'joomla.form.field.radio.switcher');
        }
        else
        {
            $element->addAttribute('class', 'radio btn-group btn-group-yesno');
        }

        $element->addAttribute('default', '0');

        $node = $element->addChild('option', $option1);
        $node->addAttribute('value', '0');

        $node = $element->addChild('option', $option2);
        $node->addAttribute('value', '1');

        $field->setup($element, $value);

        return $field->input;
    }

	/**
	 * Get label of the field (including tooltip)
	 *
	 * @param           $name
	 * @param           $title
	 * @param   string  $tooltip
	 * @param   bool    $required
	 *
	 * @return string
	 */
	public static function getFieldLabel($name, $title, $tooltip = '', $required = false)
	{
		$label = '';
		$text  = $title;

		// Build the class for the label.
		$class = !empty($tooltip) ? 'hasTooltip hasTip' : '';

		// Add the opening label tag and main attributes attributes.
		$label .= '<label id="' . $name . '-lbl" for="' . $name . '" class="' . $class . '"';

		// If a description is specified, use it to build a tooltip.
		if (!empty($tooltip))
		{
			$label .= ' title="' . self::tooltipText(trim($text, ':'), $tooltip, 0) . '"';
		}

		$label .= '>' . $text . ($required ? '<span class="required">*</span>' : '') . '</label>';

		return $label;
	}

	/**
	 * Converts a double colon seperated string or 2 separate strings to a string ready for bootstrap tooltips
	 *
	 * @param   string  $title      The title of the tooltip (or combined '::' separated string).
	 * @param   string  $content    The content to tooltip.
	 * @param   int     $translate  If true will pass texts through JText.
	 * @param   int     $escape     If true will pass texts through htmlspecialchars.
	 *
	 * @return  string  The tooltip string
	 *
	 * @since   2.0.7
	 */
	public static function tooltipText($title = '', $content = '', $translate = 1, $escape = 1)
	{
		// Initialise return value.
		$result = '';

		// Don't process empty strings
		if ($content != '' || $title != '')
		{
			// Split title into title and content if the title contains '::' (old Mootools format).
			if ($content == '' && !(strpos($title, '::') === false))
			{
				list($title, $content) = explode('::', $title, 2);
			}

			// Pass texts through JText if required.
			if ($translate)
			{
				$title   = Text::_($title);
				$content = Text::_($content);
			}

			// Use only the content if no title is given.
			if ($title == '')
			{
				$result = $content;
			}
			// Use only the title, if title and text are the same.
            elseif ($title == $content)
			{
				$result = '<strong>' . $title . '</strong>';
			}
			// Use a formatted string combining the title and content.
            elseif ($content != '')
			{
				$result = '<strong>' . $title . '</strong><br />' . $content;
			}
			else
			{
				$result = $title;
			}

			// Escape everything, if required.
			if ($escape)
			{
				$result = htmlspecialchars($result);
			}
		}

		return $result;
	}


	/**
	 * Get categories filter dropdown
	 *
	 * @param   string  $name
	 * @param   int     $selected
	 * @param   string  $attributes
	 * @param   string  $fieldSuffix
	 * @param   array   $filters
	 * @param   int     $selectCategoryValue
	 * @param   string  $selectCategoryText
	 */
	public static function getCategoryListDropdown(
		$name,
		$selected,
		$attributes = null,
		$fieldSuffix = null,
		$filters = [],
		$selectCategoryValue = 0,
		$selectCategoryText = 'JD_SELECT_CATEGORY'
	) {
		$config = DonationHelper::getConfig();
		$db     = Factory::getContainer()->get('db');
		$query  = $db->getQuery(true)
			->select('id, title')
			->from('#__jd_categories')
			->where('published = 1');

		foreach ($filters as $filter)
		{
			$query->where($filter);
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$options = DonationHelper::callOverridableHelperMethod('Html', 'getCategoryOptions', [$rows, $selectCategoryValue, $selectCategoryText]);

		return HTMLHelper::_('select.genericlist', $options, $name, $attributes, 'value', 'text', $selected);
	}

	/**
	 * Get media input field type
	 *
	 * @param   string  $value
	 * @param   string  $fieldName
	 *
	 * @return string
	 */
	public static function getMediaInput($value, $fieldName = 'image', $groupName = 'images', $label = false, $description = false)
	{
		PluginHelper::importPlugin('content');
		
		$xml  = file_get_contents(JPATH_ADMINISTRATOR . '/components/com_jdonation/forms/mediaInput.xml');
		$xml  = str_replace('name="image"', 'name="' . $fieldName . '"', $xml);
		$form = Form::getInstance('com_jdonation.' . $fieldName, $xml);

		$data[$fieldName] = $value;

		Factory::getApplication()->triggerEvent('onContentPrepareForm', [$form, $data]);

		$form->bind($data);

		return $form->getField($fieldName)->input;
	}

	/**
	 * Get clean image path
	 *
	 * @param   string  $image
	 */
	public static function getCleanImagePath($image)
	{
		// This command is needed to make sure image contains space still being displayed properly on Joomla 4
		if (DonationHelper::isJoomla4())
		{
			$image = str_replace('%20', ' ', $image);
		}

		$pos = strrpos($image, '#');

		if ($pos !== false)
		{
			$image = substr($image, 0, $pos);
		}

		return $image;
	}

	/**
	 * Get category tree
	 *
	 * @param   array       $rows
	 * @param   string|int  $selectCategoryValue
	 * @param   string      $selectCategoryText
	 *
	 * @return array
	 */
	public static function getCategoryOptions($rows, $selectCategoryValue = 0, $selectCategoryText = 'JD_SELECT_CATEGORY')
	{
		$options   = [];
		$options[] = HTMLHelper::_('select.option', $selectCategoryValue, Text::_($selectCategoryText));

		foreach ($rows as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->id,  $item->title);
		}

		return $options;
	}

	/**
	 * Get BootstrapHelper class for admin UI
	 *
	 * @return EventbookingHelperBootstrap
	 */
	public static function getAdminBootstrapHelper()
	{
		if (version_compare(JVERSION, '4.0.0-dev', 'ge'))
		{
			return new DonationHelperBootstrap('4');
		}
		
		return new DonationHelperBootstrap('2');
	}
}
