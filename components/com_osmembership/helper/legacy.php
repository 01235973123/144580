<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class OSMembershipHelperLegacy
{
	/**
	 * Download invoice of a subscription record
	 *
	 * @param   int  $id
	 */
	public static function downloadInvoice($id)
	{
		$config = OSMembershipHelper::getConfig();
		$db     = Factory::getContainer()->get('db');
		$row    = new OSMembershipTableSubscriber($db);
		$row->load($id);
		$invoiceStorePath = JPATH_ROOT . '/media/com_osmembership/invoices/';

		if ($row)
		{
			if (!$row->invoice_number)
			{
				$row->invoice_number = OSMembershipHelper::getInvoiceNumber($row);
				$row->store();
			}

			$invoiceNumber = OSMembershipHelper::formatInvoiceNumber($row, $config);
			OSMembershipHelper::generateInvoicePDF($row);
			$invoicePath = $invoiceStorePath . $invoiceNumber . '.pdf';
			$fileName    = $invoiceNumber . '.pdf';
			while (@ob_end_clean())
			{
			}
			self::processDownload($invoicePath, $fileName);
		}
	}

	/**
	 * Process download a file
	 *
	 * @param   string  $file  : Full path to the file which will be downloaded
	 */
	public static function processDownload($filePath, $filename, $detectFilename = false)
	{
		$fsize    = @filesize($filePath);
		$mod_date = date('r', filemtime($filePath));
		$cont_dis = 'attachment';

		if ($detectFilename)
		{
			$filename = OSMembershipHelper::getOriginalFilename($filename);
		}

		$ext  = OSMembershipHelper::getFileExt($filename);
		$mime = self::getMimeType($ext);

		// required for IE, otherwise Content-disposition is ignored
		if (ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}
		header('Pragma: public');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');
		header('Content-Transfer-Encoding: binary');
		header(
			'Content-Disposition:' . $cont_dis . ';' . ' filename="' . $filename . '";' . ' modification-date="' . $mod_date . '";' . ' size=' . $fsize .
			';'
		); //RFC2183
		header('Content-Type: ' . $mime); // MIME type
		header('Content-Length: ' . $fsize);

		if (!ini_get('safe_mode'))
		{ // set_time_limit doesn't work in safe mode
			@set_time_limit(0);
		}

		self::readfile_chunked($filePath);
	}

	/**
	 * Get mimetype of a file
	 *
	 * @return string
	 */
	public static function getMimeType($ext)
	{
		require_once JPATH_ROOT . '/components/com_osmembership/helper/mime.mapping.php';

		foreach ($mime_extension_map as $key => $value)
		{
			if ($key == $ext)
			{
				return $value;
			}
		}

		return '';
	}

	/**
	 * Read file
	 *
	 * @param   string  $filename
	 * @param   bool    $retbytes
	 *
	 * @return mixed
	 */
	public static function readfile_chunked($filename, $retbytes = true)
	{
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$cnt       = 0;
		$handle    = fopen($filename, 'rb');

		if ($handle === false)
		{
			return false;
		}

		while (!feof($handle))
		{
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			@ob_flush();
			flush();
			if ($retbytes)
			{
				$cnt += strlen($buffer);
			}
		}

		$status = fclose($handle);

		if ($retbytes && $status)
		{
			return $cnt; // return num. bytes delivered like readfile() does.
		}

		return $status;
	}
}
