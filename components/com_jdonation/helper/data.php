<?php

/**
 * @version        5.6.0
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2009 - 2024 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

abstract class DonationHelperData
{
	public static function csvExport($rows, $config, $rowFields, $fieldValues)
	{
		if (count($rows))
		{
			$UserBrowser   = Factory::getApplication()->client->browser;

			$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
			$filename  = "donor_list";
			header('Content-Encoding: UTF-8');
			header('Content-Type: ' . $mime_type . ' ;charset=UTF-8');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			if ($UserBrowser == WebClient::IE)
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Pragma: no-cache');
			}
			$fp = fopen('php://output', 'w');
			fwrite($fp, "\xEF\xBB\xBF");
			$delimiter = isset($config->csv_delimiter) ? $config->csv_delimiter : ',';
			$fields    = array();

			if ($config->use_campaign)
			{
				$fields[] = Text::_('JD_CAMPAIGN');
			}

			if (count($rowFields))
			{
				foreach ($rowFields as $rowField)
				{
					$fields[] = Text::_($rowField->title);
				}
			}
			$fields[] = Text::_('JD_DONATION_TYPE');
			$fields[] = Text::_('Frequency');
			$fields[] = Text::_('Payments/Times');
			$fields[] = Text::_('JD_AMOUNT');
			$fields[] = Text::_('JD_DONATION_DATE');
			$fields[] = Text::_('Payment method');
			$fields[] = Text::_('Status');
			$fields[] = Text::_('JD_TRANSACTION_ID');

			if($config->activate_tributes && $config->add_honoree_in_csv)
			{
				$fields[] = Text::_('JD_DEDICATE_DONATION');
			}

			if ($config->enable_hide_donor)
			{
				$fields[] = Text::_('JD_HIDE_DONOR');
			}

			if ($config->enable_gift_aid)
			{
				$fields[] = Text::_('JD_GIFT_AID');
			}
			fputcsv($fp, $fields, $delimiter);

			foreach ($rows as $r)
			{

				$fields = array();
				if ($config->use_campaign)
				{
					$fields[] = $r->title;
				}
				foreach ($rowFields as $rowField)
				{
					if ($rowField->is_core)
					{
						$fields[] = @$r->{$rowField->name};
					}
					else
					{
						$fieldValue = @$fieldValues[$r->id][$rowField->id];
						if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
						{
							$fieldValue = implode(', ', json_decode($fieldValue));
						}
						$fields[] = $fieldValue;
					}
				}
				if ($r->donation_type == 'R')
				{
					$fields[] = 'Recurring';
				}
				else
				{
					$fields[] = 'One time';
				}

				switch ($r->r_frequency)
				{
					case 'd':
						$fields[] = 'Daily';
						break;
					case 'w':
						$fields[] = 'Weekly';
						break;
					case 'm':
						$fields[] = 'Monthly';
						break;
					case 'q':
						$fields[] = 'Quaterly';
						break;
					case 's':
						$fields[] = 'Semi-Annually';
						break;
					case 'a':
						$fields[] = 'Annually';
						break;
					default:
						$fields[] = '';
						break;
				}

				if ($r->donation_type == 'R')
				{
					if (!$r->r_times)
					{
						$numberDonations = 'Un-limit';
					}
					else
					{
						$numberDonations = $r->r_times;
					}
					$fields[] = $r->payment_made . '/' . $numberDonations;
				}
				else
				{
					$fields[] = '';
				}
				$fields[] = number_format($r->amount, 2);
				//modified on 14th Feb to remove parameter NULL in JHTML Date
				$fields[] = HTMLHelper::_('date', $r->created_date, 'Y-m-d');
				$method   = os_jdpayments::getPaymentMethod($r->payment_method);
				if ($method)
				{
					$fields[] = $method->getTitle();
				}
				else
				{
					$fields[] = '';
				}
				if($r->published == 1)
				{
					$fields[] = Text::_('JD_PAID');
				}
				else
				{
					$fields[] = Text::_('JD_UNPAID');
				}
				$fields[] = $r->transaction_id;
				if($config->activate_tributes && $config->add_honoree_in_csv)
				{
					if($r->show_dedicate == 1)
					{
						$fields[] = DonationHelper::getDedicateType($r->dedicate_type)." - ".$r->dedicate_name;
					}
					else
					{
						$fields[] = '';
					}
				}
				if ($config->enable_hide_donor)
				{
					if($r->hide_me == 1)
					{
						$fields[]        = Text::_('JYES');
					}
					else
					{
						$fields[]        = Text::_('JNO');
					}
				}
				if ($config->enable_gift_aid)
				{
					if($r->gift_aid == 1)
					{
						$fields[]        = Text::_('JYES');
					}
					else
					{
						$fields[]        = Text::_('JNO');
					}
				}
				fputcsv($fp, $fields, $delimiter);
			}
			fclose($fp);
			Factory::getApplication()->close();
		}
	}

	public static function csvExportRevenue($data, $config)
	{
		if (count($data))
		{
			$UserBrowser   = Factory::getApplication()->client->browser;
			$mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
			$filename  = "revenue";
			header('Content-Encoding: UTF-8');
			header('Content-Type: ' . $mime_type . ' ;charset=UTF-8');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			if ($UserBrowser == WebClient::IE)
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Pragma: no-cache');
			}
			$fp = fopen('php://output', 'w');
			fwrite($fp, "\xEF\xBB\xBF");
			$delimiter = isset($config->csv_delimiter) ? $config->csv_delimiter : ',';
			$fields    = array();

			
			$fields[] = Text::_('Month');
			$fields[] = Text::_('Number of donations');
			$fields[] = Text::_('Total donated').''.$config->currency_symbol;
			
			fputcsv($fp, $fields, $delimiter);
			foreach ($data as $r)
			{

				$fields = array();
				$fields[] = $r->month;
				$fields[] = $r->total;
				$fields[] = $r->total_donated;
				
				fputcsv($fp, $fields, $delimiter);
			}
			fclose($fp);
			Factory::getApplication()->close();
		}
	}

	public static function pdfExport($rows, $config, $rowFields, $fieldValues)
    {
        require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/tcpdf.php";
        //require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/config/lang/eng.php";

        $config = DonationHelper::getConfig();
        $pdf    = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(Factory::getConfig()->get('sitename'));
        $pdf->SetTitle('Donors List');
        $pdf->SetSubject('Donors List');
        $pdf->SetKeywords('Donors List');
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $font = empty($config->pdf_font) ? 'times' : $config->pdf_font;

        // True type font
        if (substr($font, -4) == '.ttf')
        {
            $font = TCPDF_FONTS::addTTFfont(JPATH_ROOT . '/components/com_jdonation/tcpdf/fonts/' . $font, 'TrueTypeUnicode', '', 96);
        }

        $pdf->SetFont($font, '', 8);
        $pdf->AddPage('P','A4');

        $pdfOutput = DonationHelperHtml::loadCommonLayout('common/donors_list.php', ['rows' => $rows, 'rowFields' => $rowFields, 'fieldValues' => $fieldValues, 'config' => $config]);

        $pdf->writeHTML($pdfOutput, true, false, false, false, '');

        //Filename
        $filePath = JPATH_ROOT . '/media/com_jdonation/donors.pdf';

        $pdf->Output($filePath, 'F');

        return $filePath;
    }

	public static function pdfExportRevenue($campaigns, $config)
	{
		require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/tcpdf.php";
        //require_once JPATH_ROOT . "/components/com_jdonation/tcpdf/config/lang/eng.php";

        $config = DonationHelper::getConfig();
        $pdf    = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor(Factory::getConfig()->get('sitename'));
        $pdf->SetTitle('Donors List');
        $pdf->SetSubject('Donors List');
        $pdf->SetKeywords('Donors List');
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        //set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        //set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $font = empty($config->pdf_font) ? 'times' : $config->pdf_font;

        // True type font
        if (substr($font, -4) == '.ttf')
        {
            $font = TCPDF_FONTS::addTTFfont(JPATH_ROOT . '/components/com_jdonation/tcpdf/fonts/' . $font, 'TrueTypeUnicode', '', 96);
        }

        $pdf->SetFont($font, '', 8);
        $pdf->AddPage('P','A4');

        $pdfOutput = DonationHelperHtml::loadCommonLayout('common/pdf_revenue.php', ['campaigns' => $campaigns, 'config' => $config]);

        $pdf->writeHTML($pdfOutput, true, false, false, false, '');

        //Filename
        $filePath = JPATH_ROOT . '/media/com_jdonation/donation-report-'.date('Y-m-d').'.pdf';

        $pdf->Output($filePath, 'F');

        return $filePath;
	}
}
