<?php
/**
 * @version        4.0.2
 * @package        Joomla
 * @subpackage     EShop
 * @author         Giang Dinh Truong
 * @copyright      Copyright (C) 2012 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

require_once __DIR__ . '/jsonresponse.php';

/**
 * EShop controller
 *
 * @package        Joomla
 * @subpackage     EShop
 * @since          1.5
 */
class EShopControllerProduct extends BaseController
{
	use EShopControllerJsonresponse;

	/**
	 *
	 * Function to write review
	 */
	public function writeReview()
	{
		$post = $this->input->post->getArray();

		/* @var EShopModelProduct $model */
		$model = $this->getModel('Product');
		$json  = $model->writeReview($post);

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to upload file
	 */
	public function uploadFile()
	{
		$json = [];

		$file = $this->input->files->get('file', null, 'raw');

		if (!empty($file['name']))
		{
			$fileName = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($file['name'], ENT_QUOTES, 'UTF-8')));

			//Allowed file extension types
			$allowed   = [];
			$fileTypes = explode("\n", EShopHelper::getConfigValue('file_extensions_allowed'));

			foreach ($fileTypes as $fileType)
			{
				$allowed[] = trim($fileType);
			}

			if (!in_array(substr(strrchr($fileName, '.'), 1), $allowed))
			{
				$json['error'] = Text::_('ESHOP_UPLOAD_ERROR_FILETYPE');
			}

			// Allowed file mime types
			$allowed   = [];
			$fileTypes = explode("\n", EShopHelper::getConfigValue('file_mime_types_allowed'));

			foreach ($fileTypes as $fileType)
			{
				$allowed[] = trim($fileType);
			}

			if (!in_array($file['type'], $allowed))
			{
				$json['error'] = Text::_('ESHOP_UPLOAD_ERROR_FILE_MIME_TYPE');
			}

			if ($file['error'] != UPLOAD_ERR_OK)
			{
				$json['error'] = Text::_('ESHOP_ERROR_UPLOAD_' . $file['error']);
			}
		}
		else
		{
			$json['error'] = Text::_('ESHOP_ERROR_UPLOAD');
		}

		if (!$json && is_uploaded_file($file['tmp_name']) && file_exists($file['tmp_name']))
		{
			if (is_file(JPATH_ROOT . '/media/com_eshop/files/' . $fileName))
			{
				$fileName = uniqid('file_') . '_' . $fileName;
			}

			$json['file'] = $fileName;

			File::upload($file['tmp_name'], JPATH_ROOT . '/media/com_eshop/files/' . $fileName, false, true);

			$json['success'] = Text::_('ESHOP_SUCCESS_UPLOAD');
		}

		$this->sendJsonResponse($json);
	}

	/**
	 *
	 * Function to process ask question
	 */
	public function processAskQuestion()
	{
		$data = $this->input->post->getArray();

		/* @var EShopModelProduct $model */
		$model = $this->getModel('product');
		$model->processAskQuestion($data);
	}
	
	/**
	 *
	 * Function to process price match
	 */
	public function processPriceMatch()
	{
		$data = $this->input->post->getArray();
	
		/* @var EShopModelProduct $model */
		$model = $this->getModel('product');
		$model->processPriceMatch($data);
	}

	/**
	 *
	 * Function to process send a friend
	 */
	public function processEmailAFriend()
	{
		if (EShopHelper::getConfigValue('allow_email_to_a_friend'))
		{
			$data = $this->input->post->getArray();

			/* @var EShopModelProduct $model */
			$model = $this->getModel('product');
			$model->processEmailAFriend($data);
		}
	}

	/**
	 * Function to process notify
	 *
	 */
	public function processNotify()
	{
		$data = $this->input->post->getArray();

		/* @var EShopModelProduct $model */
		$model = $this->getModel('product');
		$model->processNotify($data);
		Factory::getApplication(0)->close();
	}

	/**
	 *
	 * Function to download product with pdf
	 */
	public function downloadPDF()
	{
		$productId = $this->input->getInt('product_id', 0);

		/* @var EShopModelProduct $model */
		$model = $this->getModel('Product');
		$model->downloadPDF($productId);
	}
}