<?php
/**
 * @version        1.0
 * @package        OSFramework
 * @subpackage     EShopController
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Utilities\ArrayHelper;

class EShopAdminController extends BaseController
{

	private $component = '';

	private $entityName = '';

	private $langPrefix = '';

	private $viewListUrl = '';

	public function __construct($config = [])
	{
		parent::__construct($config);

		$input = Factory::getApplication()->input;

		$this->component = $input->get('option');

		if (isset($config['entity_name']))
		{
			$this->entityName = $config['entity_name'];
		}
		else
		{
			$this->entityName = $this->getEntityName();
		}

		$this->langPrefix = ESHOP_LANG_PREFIX;

		if (isset($config['view_list_url']))
		{
			$this->viewListUrl = $config['view_list_url'];
		}
		else
		{
			$this->viewListUrl = 'index.php?option=' . $this->component . '&view=' . EShopInflector::pluralize($this->entityName);
		}

		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
	}

	/**
	 * Basic add function
	 */
	public function add()
	{
		$input = Factory::getApplication()->input;
		$input->set('view', $this->entityName);
		$input->set('edit', false);
		$this->display();
	}

	/**
	 * Basic edit function
	 */
	public function edit()
	{
		$input = Factory::getApplication()->input;
		$input->set('view', $this->entityName);
		$input->set('edit', true);

		$this->display();
	}

	/**
	 * Implementing Generic save function
	 */
	public function save()
	{
		$input      = new EshopRADInput();
		$post       = $input->getData(ESHOP_RAD_INPUT_ALLOWRAW);
		$model      = $this->getModel($this->entityName);
		$cid        = $post['cid'];
		$post['id'] = (int) $cid[0];
		$ret        = $model->store($post);

		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper($this->entityName) . '_SAVED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper($this->entityName) . '_SAVING_ERROR');
		}

		$task = $this->getTask();

		if ($task == 'save')
		{
			$url = $this->viewListUrl;
		}
		elseif ($task == 'save2new')
		{
			$url = 'index.php?option=' . $this->component . '&view=' . $this->entityName;
		}
		else
		{
			$url = $this->getEditEntityUrl($post['id']);
		}

		$this->setRedirect($url, $msg);
	}

	/**
	 * Save ordering of the record
	 */
	public function save_order()
	{
		$input = Factory::getApplication()->input;
		$order = $input->get('order', [], 'post');
		$cid   = $input->get('cid', [], 'post');
		$order = ArrayHelper::toInteger($order);
		$cid   = ArrayHelper::toInteger($cid);
		$model = $this->getModel($this->entityName);
		$ret   = $model->saveOrder($cid, $order);

		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_ORDERING_SAVED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_ORDERING_SAVING_ERROR');
		}

		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function save_order_ajax()
	{
		$input = Factory::getApplication()->input;

		// Get the input
		$pks   = $input->post->get('cid', [], 'array');
		$order = $input->post->get('order', [], 'array');

		// Sanitize the input
		$pks   = ArrayHelper::toInteger($pks);
		$order = ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel($this->entityName);

		// Save the ordering
		$return = $model->saveOrder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}

	/**
	 * Order up an entity from the list
	 */
	public function orderup()
	{
		$model = $this->getModel($this->entityName);
		$model->move(-1);
		$msg = Text::_($this->langPrefix . '_ORDERING_UPDATED');

		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Order down an entity from the list
	 */
	public function orderdown()
	{
		$model = $this->getModel($this->entityName);
		$model->move(1);
		$msg = Text::_($this->langPrefix . '_ORDERING_UPDATED');

		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Remove entities function
	 */
	public function remove()
	{
		$input         = Factory::getApplication()->input;
		$model         = $this->getModel($this->entityName);
		$cid           = $input->get('cid', []);
		$cid           = ArrayHelper::toInteger($cid);
		$deletedStatus = $model->delete($cid);
		if ($deletedStatus == '0')
		{
			$msg     = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_REMOVED_ERROR');
			$msgType = 'error';
		}
		elseif ($deletedStatus == '2')
		{
			$msg     = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_REMOVED_WARNING');
			$msgType = 'notice';
		}
		else
		{
			$msg     = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_REMOVED');
			$msgType = 'message';
		}
		$this->setRedirect($this->viewListUrl, $msg, $msgType);
	}

	/**
	 * Publish entities
	 */
	public function publish()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', [], 'post');
		$cid   = ArrayHelper::toInteger($cid);
		$model = &$this->getModel($this->entityName);
		$ret   = $model->publish($cid, 1);
		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_PUBLISHED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_PUBLISH_ERROR');
		}

		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Unpublish entities
	 */
	public function unpublish()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', [], 'post');
		$cid   = ArrayHelper::toInteger($cid);
		$model = &$this->getModel($this->entityName);
		$ret   = $model->publish($cid, 0);
		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_UNPUBLISHED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_UNPUBLISH_ERROR');
		}

		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Featured entities
	 */
	public function featured()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', [], 'post');
		$cid   = ArrayHelper::toInteger($cid);
		$model = &$this->getModel($this->entityName);
		$ret   = $model->featured($cid);
		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_FEATURED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_FEATURED_ERROR');
		}
		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Unfeatured entities
	 */
	public function unfeatured()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', [], 'post');
		$cid   = ArrayHelper::toInteger($cid);
		$model = &$this->getModel($this->entityName);
		$ret   = $model->unfeatured($cid);
		if ($ret)
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_UNFEATURED');
		}
		else
		{
			$msg = Text::_($this->langPrefix . '_' . strtoupper(EShopInflector::pluralize($this->entityName)) . '_UNFEATURED_ERROR');
		}
		$this->setRedirect($this->viewListUrl, $msg);
	}

	/**
	 * Copy entity function
	 */
	public function copy()
	{
		$input = Factory::getApplication()->input;
		$cid   = $input->get('cid', [], 'post');
		$cid   = ArrayHelper::toInteger($cid);
		$id    = $cid[0];
		$model = $this->getModel($this->entityName);
		$newId = $model->copy($id);
		$msg   = Text::_($this->langPrefix . '_' . strtoupper($this->entityName) . '_COPIED');
		if ($newId)
		{
			$url = $this->getEditEntityUrl($newId);
		}
		else
		{
			$url = $this->viewListUrl;
		}

		$this->setRedirect($url, $msg);
	}

	/**
	 * Cancel the entity .
	 * Redirect user to items list page
	 */
	public function cancel()
	{
		$this->setRedirect($this->viewListUrl);
	}

	/**
	 * Get name of entity which we are working on
	 */
	public function getEntityName()
	{
		if (empty($this->entityName))
		{
			$r = null;
			if (preg_match('/(.*)Controller(.*)/i', get_class($this), $r))
			{
				$this->entityName = strtolower($r[2]);
			}
		}

		return $this->entityName;
	}

	public function getEditEntityUrl($id = 0)
	{
		return 'index.php?option=' . $this->component . '&task=' . $this->entityName . '.edit&cid[]=' . $id;
	}
}
