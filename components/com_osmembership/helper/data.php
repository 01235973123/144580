<?php
/**
 * @package            Joomla
 * @subpackage         Membership Pro
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2022 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Filesystem\File;
use OSSolution\MembershipPro\Admin\Event\Import\BeforeGettingDataFromFile;

class OSMembershipHelperData
{
	/**
	 * Get data from excel file using PHPExcel library
	 *
	 * @param $file
	 * @param $filename
	 *
	 * @return array
	 */
	public static function getDataFromFile($file, $filename = '')
	{
		// Allow using a custom library to parse the file
		PluginHelper::importPlugin('osmembership');

		$event = new BeforeGettingDataFromFile([
			'file'     => $file,
			'filename' => $filename,
		]);

		$results = Factory::getApplication()->triggerEvent($event->getName(), $event);

		foreach ($results as $result)
		{
			if (is_array($result))
			{
				return $result;
			}
		}

		// Use Spout to get data
		try
		{
			$reader = ReaderEntityFactory::createReaderFromFile($filename);

			if ($reader instanceof Reader)
			{
				$config = OSMembershipHelper::getConfig();
				$reader->setFieldDelimiter($config->get('csv_delimiter', ','));
			}

			$reader->setShouldFormatDates(true);

			$reader->open($file);
			$headers = [];
			$rows    = [];
			$count   = 0;

			foreach ($reader->getSheetIterator() as $sheet)
			{
				foreach ($sheet->getRowIterator() as $row)
				{
					$cells = $row->getCells();

					if ($count === 0)
					{
						foreach ($cells as $cell)
						{
							$headers[] = $cell->getValue();
						}

						$count++;
					}
					else
					{
						$cellIndex = 0;
						$row       = [];

						foreach ($cells as $cell)
						{
							$row[$headers[$cellIndex++]] = $cell->getValue();
						}

						$rows[] = $row;
					}
				}
			}

			$reader->close();

			return $rows;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return [];
		}
	}

	/**
	 * Export the given data to Excel
	 *
	 * @param   array   $fields
	 * @param   array   $rows
	 * @param   string  $filename
	 * @param   array   $headers
	 *
	 * @return string
	 */
	public static function excelExport($fields, $rows, $filename, $headers = [])
	{
		if (empty($headers))
		{
			$headers = $fields;
		}

		$filename = File::stripExt($filename);

		$config = OSMembershipHelper::getConfig();

		if ($config->get('export_data_format') == 'csv')
		{
			$writer = WriterEntityFactory::createCSVWriter();
			$writer->setFieldDelimiter($config->get('csv_delimiter', ','));

			$filePath = JPATH_ROOT . '/media/com_osmembership/invoices/' . $filename . '.csv';
		}
		else
		{
			$writer = WriterEntityFactory::createXLSXWriter();

			$filePath = JPATH_ROOT . '/media/com_osmembership/invoices/' . $filename . '.xlsx';
		}

		//Delete the file if exist
		if (is_file($filePath))
		{
			File::delete($filePath);
		}

		$writer->openToFile($filePath);

		if (empty($headers))
		{
			$headers = $fields;
		}

		$style = (new StyleBuilder())
			->setShouldWrapText(false)
			->build();

		// Write header columns
		$writer->addRow(WriterEntityFactory::createRowFromArray($headers, $style));

		$floatColumns = [
			'amount',
			'discount_amount',
			'tax_amount',
			'payment_processing_fee',
			'gross_amount',
		];

		foreach ($rows as $row)
		{
			foreach ($floatColumns as $field)
			{
				$row->{$field} = (float) $row->{$field};
			}

			$data = [];

			foreach ($fields as $field)
			{
				if (property_exists($row, $field))
				{
					$data[] = $row->{$field};
				}
				else
				{
					$data[] = '';
				}
			}

			$writer->addRow(WriterEntityFactory::createRowFromArray($data, $style));
		}

		$writer->close();

		return $filePath;
	}
}
