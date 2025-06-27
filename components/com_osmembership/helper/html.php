<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2002 - 2013 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Filesystem\Path;

class OSMembershipHelperHtml
{
	/**
	 * Method to add overridable js files to document. Just add override.
	 * at the beginning of the file which you want to override
	 *
	 * @param   string|array  $files
	 * @param   array         $options
	 * @param   array         $attribs
	 */
	public static function addOverridableScript($files, $options = [], $attribs = [])
	{
		$wa = Factory::getApplication()
			->getDocument()
			->getWebAssetManager();

		$files = (array) $files;

		foreach ($files as $file)
		{
			$parts             = explode('/', $file);
			$count             = count($parts);
			$parts[$count - 1] = 'override.' . $parts[$count - 1];
			$overridableFile   = implode('/', $parts);

			if (is_file(JPATH_ROOT . '/' . $overridableFile))
			{
				$file = $overridableFile;
			}

			$wa->registerAndUseScript('com_osmembership.' . $file, $file, $options, $attribs);
		}
	}

	/**
	 * Render showon string
	 *
	 * @param   array  $fields
	 *
	 * @return string
	 */
	public static function renderShowon($fields)
	{
		$output = [];

		$i = 0;

		foreach ($fields as $name => $values)
		{
			$i++;

			$values = (array) $values;

			$data = [
				'field'  => $name,
				'values' => $values,
				'sign'   => '=',
			];

			$data['op'] = $i > 1 ? 'AND' : '';

			$output[] = json_encode($data);
		}

		return '[' . implode(',', $output) . ']';
	}

	/**
	 * Helper method to add multiple language strings to JS
	 *
	 * @param   array  $items
	 */
	public static function addJSStrings($items = [])
	{
		foreach ($items as $item)
		{
			Text::script($item, true);
		}
	}

	/**
	 * Basic implement for conditional text
	 *
	 * @param   string  $text
	 *
	 * @return string mixed
	 */
	public static function processConditionalText($text)
	{
		$regex = '#{mpShowText (.*?)(=|>|gt|<|lt|~)(.*?)}(.*?){/mpShowText}#s';

		return preg_replace_callback($regex, static::class . '::processCondition', $text);
	}

	/**
	 * Process conditional text, for now, we support = , >, and < operator
	 *
	 * @param   array  $matches
	 *
	 * @return string
	 */
	public static function processCondition($matches)
	{
		$a        = trim($matches[1]);
		$operator = $matches[2];
		$b        = trim($matches[3]);
		$text     = $matches[4];

		switch ($operator)
		{
			case '=':
				if ($a == $b)
				{
					return $text;
				}
				break;
			case '>':
			case 'gt':
				if ($a > $b)
				{
					return $text;
				}
				break;
			case '<':
			case 'lt':
				if ($a < $b)
				{
					return $text;
				}
				break;
			case '~':
				if ($a != $b)
				{
					return $text;
				}
				break;
		}

		return;
	}

	/**
	 * Function to render a common layout which is used in different views
	 *
	 * @param   string  $layout
	 * @param   array   $data
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	public static function loadCommonLayout($layout, $data = [])
	{
		$app       = Factory::getApplication();
		$themeFile = str_replace('/tmpl', '', $layout);

		// This line was added to keep B/C with template override code, don't remove it
		if (str_starts_with($layout, 'common/') && !str_contains($layout, 'common/tmpl'))
		{
			$layout = str_replace('common/', 'common/tmpl/', $layout);
		}

		if (is_file(Path::clean($layout)))
		{
			$path = $layout;
		}
        elseif (is_file(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_osmembership/' . $themeFile))
		{
			$path = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_osmembership/' . $themeFile;
		}
        elseif (is_file(JPATH_ROOT . '/components/com_osmembership/view/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_osmembership/view/' . $layout;
		}
		else
		{
			throw new RuntimeException(Text::_('The given shared template path is not exist'));
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
	 * Function to render a shared layout which is used for both frontend and backend, for example email templates
	 *
	 * or invoice layout
	 *
	 * @param   string  $layout
	 * @param   array   $data
	 *
	 * @return string
	 * @throws RuntimeException
	 */
	public static function loadSharedLayout($layout, $data = [])
	{
		$app       = Factory::getApplication();
		$themeFile = str_replace('/tmpl', '', $layout);

		$paths = [JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_osmembership'];

		if ($app->isClient('administrator'))
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('template')
				->from('#__template_styles')
				->where('client_id = 0')
				->order('home DESC');
			$db->setQuery($query);

			foreach ($db->loadColumn() as $template)
			{
				$paths[] = JPATH_ROOT . '/templates/' . $template . '/html/com_osmembership';
			}
		}

		$path = '';

		// Find override layout first
		foreach ($paths as $overridePath)
		{
			if (is_file($overridePath . '/' . $themeFile))
			{
				$path = $overridePath . '/' . $themeFile;
				break;
			}
		}

		// If there is no override layout, use component layout
		if (!$path && is_file(JPATH_ROOT . '/components/com_osmembership/view/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_osmembership/view/' . $layout;
		}

		if (!$path)
		{
			throw new RuntimeException(Text::sprintf('The given shared layout %s does not exist', $layout));
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

	public static function getPossibleLayouts($layout)
	{
		$layouts = [$layout];

		$config = OSMembershipHelper::getConfig();

		if (empty($config->twitter_bootstrap_version))
		{
			$twitterBootstrapVersion = 2;
		}
		else
		{
			$twitterBootstrapVersion = $config->twitter_bootstrap_version;
		}

		switch ($twitterBootstrapVersion)
		{
			case 2:
				break;
			case 3:
			case 4:
				array_unshift($layouts, $layout . '.bootstrap' . $twitterBootstrapVersion);
				break;
			default:
				array_unshift($layouts, $layout . '.' . $twitterBootstrapVersion);
				break;
		}

		return $layouts;
	}

	/**
	 * Generate category selection dropdown
	 *
	 * @param   int     $selected
	 * @param   string  $name
	 * @param   string  $attr
	 *
	 * @return mixed
	 */
	public static function buildCategoryDropdown($selected, $name = 'parent_id', $attr = null)
	{
		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('id, parent_id, title')
			->from('#__osmembership_categories')
			->where('published=1');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = [];

		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : [];
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$list      = HTMLHelper::_('menu.treerecurse', 0, '', [], $children, 9999, 0, 0);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '0', Text::_('OSM_SELECT_CATEGORY'));

		foreach ($list as $item)
		{
			$options[] = HTMLHelper::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		return HTMLHelper::_(
			'select.genericlist',
			$options,
			$name,
			[
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="form-select" ' . $attr,
				'list.select'        => $selected,
			]
		);
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
			if ($content == '' && str_contains($title, '::'))
			{
				[$title, $content] = explode('::', $title, 2);
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
			} // Use only the title, if title and text are the same.
            elseif ($title == $content)
			{
				$result = '<strong>' . $title . '</strong>';
			} // Use a formatted string combining the title and content.
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
	 * Get bootstrapped style boolean input
	 *
	 * @param $name
	 * @param $value
	 *
	 * @return string
	 */
	public static function getBooleanInput($name, $value)
	{
		$field = FormHelper::loadFieldType('Radio');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $name);
		$element->addAttribute('layout', 'joomla.form.field.radio.switcher');

		$element->addAttribute('default', '0');

		$node = $element->addChild('option', 'JNO');
		$node->addAttribute('value', '0');

		$node = $element->addChild('option', 'JYES');
		$node->addAttribute('value', '1');

		$field->setup($element, (int) $value);

		return $field->input;
	}

	/**
	 * Function to add dropdown menu
	 *
	 * @param   string  $vName
	 */
	public static function renderSubmenu($vName = 'dashboard')
	{
		?>
        <script language="javascript">
            function confirmBuildTaxRules() {
                if (confirm('This will delete all tax rules you created and build EU tax rules. Are you sure ?')) {
                    location.href = 'index.php?option=com_osmembership&task=tool.build_eu_tax_rules';
                }
            }
        </script>
		<?php
		HTMLHelper::_('bootstrap.dropdown');

		/* @var DatabaseDriver $db */
		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_menus')
			->where('published = 1')
			->where('menu_parent_id = 0')
			->order('ordering');
		$db->setQuery($query);
		$menus = $db->loadObjectList();

		$html = '<ul id="mp-dropdown-menu" class="nav nav-tabs nav-hover osm-joomla4">';

		$currentLink = 'index.php' . Uri::getInstance()->toString(['query']);

		for ($i = 0; $n = count($menus), $i < $n; $i++)
		{
			$menu = $menus[$i];
			$query->clear();
			$query->select('*')
				->from('#__osmembership_menus')
				->where('published = 1')
				->where('menu_parent_id = ' . intval($menu->id))
				->order('ordering');
			$db->setQuery($query);
			$subMenus = $db->loadObjectList();

			switch ($i)
			{
				case 2:
				case  3:
					$view = 'subscriptions';
					break;
				case 4:
					$view = 'coupons';
					break;
				case 5:
					$view = 'plugins';
					break;
				case 1:
				case 6:
				case 7:
				case 8:
					$view = 'configuration';
					break;
				default:
					$view = '';
					break;
			}

			if ($view && !OSMembershipHelper::canAccessThisView($view))
			{
				continue;
			}

			if (!count($subMenus))
			{
				$class      = '';
				$extraClass = '';

				if ($menu->menu_link == $currentLink)
				{
					$class      = ' class="active"';
					$extraClass = ' active';
				}

				$html .= '<li' . $class . '><a href="' . $menu->menu_link . '" class="nav-link' . $extraClass . '"><span class="icon-' . $menu->menu_class . '"></span> ' . Text::_(
						$menu->menu_name
					) .
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
				$html .= '<a id="drop_' . $menu->id . '" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" role="button" class="dropdown-toggle nav-link dropdown-toggle"><span class="icon-' . $menu->menu_class . '"></span> ' .
					Text::_($menu->menu_name) . ' <b class="caret"></b></a>';
				$html .= '<ul aria-labelledby="drop_' . $menu->id . '" role="menu" class="dropdown-menu" id="menu_' . $menu->id . '">';

				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu    = $subMenus[$j];
					$class      = '';
					$extraClass = '';

					$vars = [];
					parse_str($subMenu->menu_link, $vars);
					$view = $vars['view'] ?? '';

					if ($view && !OSMembershipHelper::canAccessThisView($view))
					{
						continue;
					}

					if ($subMenu->menu_link == $currentLink)
					{
						$class      = ' class="active"';
						$extraClass = ' active';
					}

					$html .= '<li' . $class . '><a class="nav-link dropdown-item' . $extraClass . '" href="' . $subMenu->menu_link .
						'" tabindex="-1"><span class="icon-' . $subMenu->menu_class . '"></span> ' . Text::_(
							$subMenu->menu_name
						) . '</a></li>';
				}

				$html .= '</ul>';
				$html .= '</li>';
			}
		}

		$html .= '</ul>';

		echo $html;
	}

	/**
	 * Generate article selection box
	 *
	 * @param   int     $fieldValue
	 * @param   string  $fieldName
	 *
	 * @return string
	 */
	public static function getArticleInput($fieldValue, $fieldName = 'article_id')
	{
		FormHelper::addFieldPrefix('Joomla\Component\Content\Administrator\Field');

		$field = FormHelper::loadFieldType('Modal_Article');

		$field->setDatabase(Factory::getContainer()->get('db'));

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('select', 'true');
		$element->addAttribute('clear', 'true');

		$field->setup($element, $fieldValue);

		return $field->input;
	}

	/**
	 * Method to allow select categories from com_content
	 *
	 * @param   string|array  $value
	 * @param   string        $fieldName
	 * @param   bool          $multiple
	 *
	 * @return string
	 */
	public static function getArticleCategoriesInput($value, $fieldName = 'category_id', $multiple = false)
	{
		$field = FormHelper::loadFieldType('Category');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('extension', 'com_content');
		$element->addAttribute('published', '1');
		$element->addAttribute('layout', 'joomla.form.field.list-fancy-select');

		if ($multiple)
		{
			$element->addAttribute('multiple', 'true');
		}

		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Get human readable filesize
	 *
	 * @param   string  $file
	 * @param   int     $precision
	 *
	 * @return string
	 */
	public static function getFormattedFilezize($file, $precision = 2)
	{
		$bytes  = filesize($file);
		$size   = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
		$factor = floor((strlen($bytes) - 1) / 3);

		return sprintf("%.{$precision}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}

	/**
	 * Get media input field type
	 *
	 * @param   string  $value
	 * @param   string  $fieldName
	 *
	 * @return string
	 */
	public static function getMediaInput($value, $fieldName = 'image')
	{
		$field = FormHelper::loadFieldType('Media');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('class', 'readonly input-large');
		$element->addAttribute('preview', 'tooltip');
		$element->addAttribute('directory', 'com_osmembership');

		$form = Form::getInstance('sample-form', '<form> </form>');
		$field->setForm($form);
		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Method to get input to allow selecting user
	 *
	 * @param   string  $value
	 * @param   string  $fieldName
	 *
	 * @return string
	 */
	public static function getUserInput($value, $fieldName)
	{
		// Prevent notices on PHP 8.1 due to a bug on user form field
		if (!$value)
		{
			$value = '';
		}

		$field = FormHelper::loadFieldType('User');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('class', 'readonly input-medium');

		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Method to return password input
	 *
	 * @param   string  $name
	 * @param   string  $value
	 * @param   string  $class
	 *
	 * @return string
	 */
	public static function getPasswordInput($name = '', $value = '', $class = '')
	{
		$field = FormHelper::loadFieldType('Password');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $name);

		if ($class)
		{
			$element->addAttribute('class', $class);
		}

		$field->setup($element, $value);

		return $field->input;
	}

	/**
	 * Get BootstrapHelper class for admin UI
	 *
	 * @return OSMembershipHelperBootstrap
	 */
	public static function getAdminBootstrapHelper()
	{
		return OSMembershipHelperBootstrap::getInstance();
	}

	/**
	 * Add choices JS to dropdown
	 *
	 * @param   string  $html
	 * @param   string  $hint
	 */
	public static function getChoicesJsSelect($html, $hint = '')
	{
		Text::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');
		Text::script('JGLOBAL_SELECT_PRESS_TO_SELECT');

		Factory::getApplication()->getDocument()->getWebAssetManager()
			->usePreset('choicesjs')
			->useScript('webcomponent.field-fancy-select');

		$attributes = [];

		$hint = $hint ?: Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS');

		$attributes[] = 'placeholder="' . $hint . '""';
		$attributes[] = 'search-placeholder="' . $hint . '""';

		return '<joomla-field-fancy-select ' . implode(' ', $attributes) . '>' . $html . '</joomla-field-fancy-select>';
	}

	/**
	 * Get clean image path
	 *
	 * @param   string  $image
	 */
	public static function getCleanImagePath($image)
	{
		// This command is needed to make sure image contains space still being displayed properly on Joomla 4
		$image = str_replace('%20', ' ', $image);

		$pos = strrpos($image, '#');

		if ($pos !== false)
		{
			$image = substr($image, 0, $pos);
		}

		return $image;
	}

	/**
	 * Get supported tags for given message
	 *
	 * @param   string  $name
	 *
	 * @return array
	 */
	public static function getSupportedTags($name)
	{
		static $planTags, $subscriptionTags;

		if ($planTags === null)
		{
			$planTags = OSMembershipHelperEmailtags::getPlanTags();
		}

		if ($subscriptionTags === null)
		{
			$subscriptionTags = OSMembershipHelperEmailtags::getSubscriptionTags();
		}

		$tags = [];

		$commonEmailTags = array_merge($planTags, $subscriptionTags);

		switch ($name)
		{
			case 'admin_email_subject':
			case 'admin_email_body':
			case 'user_email_subject':
			case 'user_email_body':
			case 'user_email_body_offline':
				$tags = $commonEmailTags;
				break;
			case 'subscription_approved_email_subject':
			case 'subscription_approved_email_body':
				$tags = $commonEmailTags;
				break;
			case 'admin_subscription_approved_email_subject':
			case 'admin_subscription_approved_email_body':
				$tags = array_merge($commonEmailTags, ['APPROVAL_USERNAME', 'APPROVAL_NAME', 'APPROVAL_EMAIL']);
				break;
			case 'subscription_form_msg':
				$tags = ['PLAN_TITLE', 'PLAN_DURATION', 'CATEGORY_TITLE', 'AMOUNT', 'PLAN_SHORT_DESCRIPTION', 'PLAN_DESCRIPTION'];
				break;
			case 'thanks_message':
			case 'thanks_message_offline':
				$tags = $commonEmailTags;
				break;
			case 'profile_update_email_subject':
			case 'profile_update_email_body':
				$tags = $commonEmailTags;
				$tags = array_merge($tags, ['profile_updated_details', 'profile_link', 'SUBSCRIPTION_END_DATE']);
				break;
			case 'content_restricted_message':
				$tags = ['PLAN_TITLES', 'SUBSCRIPTION_URL'];
				break;
			case 'subscription_renew_form_msg':
				$tags = ['RENEW_OPTION', 'PLAN_TITLE', 'PLAN_DURATION', 'CATEGORY_TITLE', 'AMOUNT'];
				break;
			case 'admin_renw_email_subject':
			case 'admin_renew_email_body':
			case 'user_renew_email_subject':
			case 'user_renew_email_body':
			case 'user_renew_email_body_offline':
				$tags = array_merge($commonEmailTags, ['number_days']);
				break;
			case 'renew_thanks_message':
			case 'renew_thanks_message_offline':
				$tags = array_merge($commonEmailTags, ['END_DATE']);
				break;
			case 'subscription_upgrade_form_msg':
				$tags = ['FROM_PLAN_TITLE', 'PLAN_TITLE', 'PLAN_DURATION', 'CATEGORY_TITLE', 'AMOUNT'];
				break;
			case 'admin_upgrade_email_subject':
			case 'admin_upgrade_email_body':
			case 'user_upgrade_email_subject':
			case 'user_upgrade_email_body':
			case 'user_upgrade_email_body_offline':
				$tags = array_merge($commonEmailTags, ['TO_PLAN_TITLE']);
				break;
			case 'upgrade_thanks_message':
			case 'upgrade_thanks_message_offline':
				$tags = array_merge($commonEmailTags, ['TO_PLAN_TITLE']);
				break;
			case 'recurring_subscription_cancel_message':
				$tags = ['PLAN_TITLE', 'SUBSCRIPTION_END_DATE'];
				break;
			case 'user_recurring_subscription_cancel_subject':
			case 'user_recurring_subscription_cancel_body':
			case 'admin_recurring_subscription_cancel_subject':
			case 'admin_recurring_subscription_cancel_body':
				$tags = ['plan_title', 'first_name', 'last_name', 'email', 'SUBSCRIPTION_END_DATE'];
				break;
			case 'offline_recurring_email_subject':
			case 'offline_recurring_email_body':
				$tags = $commonEmailTags;
				break;
			case 'first_reminder_email_subject':
			case 'first_reminder_email_body':
			case 'second_reminder_email_subject':
			case 'second_reminder_email_body':
			case 'third_reminder_email_subject':
			case 'third_reminder_email_body':
				$tags = array_merge($commonEmailTags, ['number_days', 'expire_date']);
				break;
			case 'subscription_end_email_subject':
			case 'subscription_end_email_body':
				$tags = [
					'plan_title',
					'first_name',
					'last_name',
					'number_days',
					'membership_id',
					'gross_amount',
					'payment_method'
				];
				break;
			case 'new_group_member_email_subject':
			case 'new_group_member_email_body':
				$tags = array_merge($commonEmailTags, ['group_admin_name', 'password']);
				break;
			case 'join_group_form_message':
				$tags = ['plan_title', 'group_admin_name'];
				break;
			case 'join_group_complete_message':
				$tags = array_merge($commonEmailTags, ['group_admin_name']);
				break;
			case 'join_group_user_email_subject':
			case 'join_group_user_email_body':
			case 'join_group_group_admin_email_subject':
			case 'join_group_group_admin_email_body':
				$tags = array_merge($commonEmailTags, ['group_admin_name']);
				break;
			case 'subscription_payment_form_message':
				$tags = ['id', 'amount', 'plan_title'];
				break;
			case 'subscription_payment_admin_email_subject':
			case 'subscription_payment_admin_email_body':
			case 'subscription_payment_user_email_subject':
			case 'subscription_payment_user_email_body':
				$tags = $commonEmailTags;
				break;
			case 'subscription_payment_thanks_message':
				$tags = $commonEmailTags;
				break;
			case 'request_payment_email_subject':
			case 'request_payment_email_body':
				$tags = array_merge($tags, ['payment_link']);
				break;
			case 'new_subscription_admin_sms':
			case 'new_subscription_renewal_admin_sms':
			case 'new_subscription_upgrade_admin_sms':
				$tags = [
					'plan_id',
					'plan_title',
					'id',
					'first_name',
					'last_name',
					'organization',
					'address',
					'address2',
					'city',
					'zip',
					'state',
					'country',
					'phone',
					'fax',
					'email',
					'comment',
					'from_date',
					'to_date',
					'created_date',
					'end_date',
					'from_plan_title',
				];
				break;
			case 'first_reminder_sms':
			case 'second_reminder_sms':
			case 'third_reminder_sms':
				$tags = [
					'plan_id',
					'plan_title',
					'id',
					'first_name',
					'last_name',
					'organization',
					'address',
					'address2',
					'city',
					'zip',
					'state',
					'country',
					'phone',
					'fax',
					'email',
					'comment',
					'from_date',
					'to_date',
					'created_date',
					'end_date',
					'from_plan_title',
				];
				break;
			case 'card_layout':
				$tags = array_merge($commonEmailTags, [
					'register_date',
					'name',
					'plan_subscription_from_date',
					'plan_subscription_to_date',
					'subscriptions',
					'QRCODE',
				]);
				break;
			case 'invoice_format':
				$tags = array_merge($commonEmailTags, [
					'invoice_date',
					'invoice_status',
					'setup_fee',
					'item_quantity',
					'item_amount',
					'discount_amount',
					'sub_total',
					'tax_amount',
					'payment_processing_fee',
					'total_amount',
					'tax_rate',
					'item_name',
					'payment_link_url',
					'payment_link',
				]);
				break;
		}

		return array_map('strtoupper', $tags);
	}

	/* Get link to article, multilingual association is supported
	 *
	 * @param   int  $articleId
	 *
	 * @return string
	 */
	public static function getArticleUrl($articleId)
	{
		if (Multilanguage::isEnabled())
		{
			$associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);
			$langCode     = Factory::getApplication()->getLanguage()->getTag();

			if (isset($associations[$langCode]))
			{
				$article = $associations[$langCode];
			}
		}

		if (!isset($article))
		{
			/* @var DatabaseDriver $db */
			$db    = Factory::getContainer()->get('db');
			$query = $db->getQuery(true)
				->select('id, catid')
				->from('#__content')
				->where('id = ' . (int) $articleId);
			$db->setQuery($query);
			$article = $db->loadObject();
		}

		if (!$article)
		{
			return '';
		}

		return RouteHelper::getArticleRoute($article->id, $article->catid);
	}
}
