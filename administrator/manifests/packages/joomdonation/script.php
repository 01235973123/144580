<?php

/**
 * @package        Joomla
 * @subpackage     Joom Donation
 * @author         Dang Thuc Dam
 * @copyright      Copyright (C) 2009 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class Pkg_JoomdonationInstallerScript
{
	protected $installType;
	public static $languageFiles = array('en-GB');

	/**
	 * Minimum PHP version
	 */
	private const MIN_PHP_VERSION = '7.2.0';

	/**
	 * Minimum Joomla version
	 */
	private const MIN_JOOMLA_VERSION = '4.2.0';

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$this->installType = 'install';
	}

	public function update($parent)
	{
		$this->installType = 'update';
	}

	function preflight($type, $parent)
	{
		if (version_compare(JVERSION, self::MIN_JOOMLA_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Cannot install Edocman in a Joomla! release prior to ' . self::MIN_JOOMLA_VERSION,
				'error'
			);

			return false;
		}

		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				'Edocman requires PHP ' . self::MIN_PHP_VERSION . '+ to work. Please contact your hosting provider, ask them to update PHP version for your hosting account.',
				'error'
			);

			return false;
		}

		if ($type === 'update')
		{
			$this->deleteOldUpdateSite();
		}
	}	

	private function deleteOldUpdateSite(): void
	{
		/* @var \Joomla\Database\DatabaseDriver $db */

		$db    = Factory::getContainer()->get('db');
		$query = $db->getQuery(true)->delete('#__update_sites')->where($db->quoteName('location') . ' = ' . $db->quote('https://www.joomdonation.com/updates/jdonation.xml'));
 
		$db->setQuery($query)->execute();
	}


	public function postflight($type, $parent)
	{
		ob_start();
		?>
		<style>
		.card {
		  border-radius: 1.25rem;
		}
		.card-header .bi {
		  font-size: 2rem;
		}
		.btn-lg {
		  border-radius: 0.7rem;
		  font-size: 1.12rem;
		  font-weight: 600;
		  letter-spacing: 0.01em;
		  padding: 0.7rem 2.2rem;
		}
		.dashboard-btn {
		  background: #2156d9;
		  color: #fff !important;
		  border: none;
		  transition: background 0.18s;
		  box-shadow: 0 2px 8px rgba(33,86,217,0.10);
		}
		.dashboard-btn:hover, .dashboard-btn:focus {
		  background: #1742a8;
		  color: #fff !important;
		}
		.dashboard-btn .bi {
		  font-size: 1.15em;
		  vertical-align: -0.15em;
		  margin-right: 0.6em;
		}
		.table thead th, .table thead td {
		  font-size: 1rem;
		  font-weight: 600;
		  letter-spacing: .01em;
		}
		.badge {
		  font-size: 0.95em;
		}
		.alert {
		  border-radius: .7rem;
		}
		.table .text-danger {
		  color: #dc3545 !important;
		  font-weight: 600;
		}
		.table .text-success {
		  color: #198754 !important;
		  font-weight: 600;
		}
		</style>

		<?php
		// Dữ liệu mẫu để demo, khi tích hợp thực tế thay bằng biến thực tế
		$gd = function_exists('gd_info');
		$curl = is_callable('curl_init');
		// $db = Factory::getDBO();
		// $mysqlVersion = $db->getVersion();
		$mysqlVersion = '8.0.34'; // demo
		$phpVersion = phpversion();
		$uploadLimit = ini_get('upload_max_filesize');
		$memoryLimit = ini_get('memory_limit');
		if (stripos($memoryLimit, 'G') !== false) {
		  list($memoryLimit) = explode('G', $memoryLimit);
		  $memoryLimit = $memoryLimit * 1024;
		}
		$memoryLimit = (int)preg_replace('/\D/', '', $memoryLimit);
		$hasErrors = (version_compare($phpVersion, '7.2.0') == -1) || ($memoryLimit < 128) || (version_compare($mysqlVersion, '8.0.13') == -1) || !$gd || !$curl;
		?>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
		<div class="container my-5" id="es-installer">
		  <div class="row justify-content-center">
			<div class="col-lg-8">

			  <!-- Card thông báo cài đặt thành công -->
			  <div class="card shadow-lg border-0 mb-4">
				<div class="card-header bg-success bg-gradient text-white d-flex align-items-center py-3">
				  <i class="bi bi-check2-circle me-3"></i>
				  <span class="fs-5 fw-bold">Joom Donation Installed Successfully</span>
				</div>
				<div class="card-body bg-light">
				  <h1 class="h4 text-success mb-3">
					Thank you for your recent purchase of Joom Donation.
				  </h1>
				  <p>
					You have made a great choice using the <strong>Best Online Donation Extension</strong> for Joomla!<br>
					Let's get started with your fundraising journey.
				  </p>
				  <div class="mt-4">
					<a href="index.php?option=com_jdonation" class="btn btn-lg dashboard-btn shadow-sm">
					  <i class="bi bi-arrow-right-circle"></i>
					  Go to Joom Donation Dashboard
					</a>
				  </div>
				</div>
			  </div>

			  <!-- Thông báo yêu cầu hệ thống -->
			  <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
				<i class="bi bi-info-circle-fill me-2 fs-5"></i>
				<div>
				  Please ensure your server meets the following requirements for Joom Donation to run smoothly.
				</div>
			  </div>

			  <?php if (!$hasErrors) { ?>
			  <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
				<i class="bi bi-check-circle-fill me-2 fs-5"></i>
				<div>
				  Awesome! The minimum requirements are met. You may proceed with the installation process now.
				</div>
			  </div>
			  <?php } else { ?>
			  <div class="alert alert-danger d-flex align-items-center mb-4" role="alert">
				<i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
				<div>
				  Please ensure that all of the requirements below are met.
				</div>
			  </div>
			  <?php } ?>

			  <!-- Bảng kiểm tra yêu cầu hệ thống -->
			  <div class="table-responsive">
				<table class="table table-bordered align-middle">
				  <thead class="table-light">
					<tr>
					  <th>Settings</th>
					  <th class="text-center">Recommended</th>
					  <th class="text-center">Current</th>
					</tr>
				  </thead>
				  <tbody>
					<tr class="<?php echo version_compare($phpVersion, '7.2.0') == -1 ? 'table-danger' : ''; ?>">
					  <td>
						<span class="badge bg-info text-white me-2">PHP</span> PHP Version
						<i class="bi bi-question-circle" data-bs-toggle="tooltip" title="This is the installed PHP version on your site."></i>
						<?php if (version_compare($phpVersion, '7.2.0') == -1) { ?>
						<a href="https://docs.joomdonation.com/donation/getting-started/requirements" class="btn btn-danger btn-sm float-end">Fix This</a>
						<?php } ?>
					  </td>
					  <td class="text-center text-success">7.2.0 +</td>
					  <td class="text-center <?php echo version_compare($phpVersion, '7.2.0') == -1 ? 'text-danger' : 'text-success'; ?>">
						<?php echo $phpVersion; ?>
					  </td>
					</tr>
					<tr class="<?php echo $memoryLimit < 128 ? 'table-danger' : ''; ?>">
					  <td>
						<span class="badge bg-info text-white me-2">PHP</span> memory_limit
						<i class="bi bi-question-circle" data-bs-toggle="tooltip" title="Memory Limit determines how much memory PHP can utilize per request on the server. On a normal site, 64MB should be sufficient, but on a busier site, it's best to set it to 128MB."></i>
					  </td>
					  <td class="text-center text-success">128M</td>
					  <td class="text-center <?php echo $memoryLimit < 128 ? 'text-danger' : 'text-success'; ?>">
						<?php echo $memoryLimit; ?>
					  </td>
					</tr>
					<tr>
					  <td>
						<span class="badge bg-success text-white me-2">MySQL</span> MySQL Version
						<i class="bi bi-question-circle" data-bs-toggle="tooltip" title="This is the installed MySQL server version on your site."></i>
					  </td>
					  <td class="text-center text-success">8.0.13</td>
					  <td class="text-center <?php echo !$mysqlVersion || version_compare($mysqlVersion, '8.0.13') == -1 ? 'text-danger' : 'text-success'; ?>">
						<?php echo !$mysqlVersion ? 'N/A' : $mysqlVersion; ?>
					  </td>
					</tr>
					<tr>
					  <td>
						<span class="badge bg-info text-white me-2">PHP</span> cURL
						<i class="bi bi-question-circle" data-bs-toggle="tooltip" title="cURL is required for remote requests."></i>
					  </td>
					  <td class="text-center text-success">Enabled</td>
					  <td class="text-center <?php echo !$curl ? 'text-danger' : 'text-success'; ?>">
						<?php echo $curl ? 'Enabled' : 'Disabled'; ?>
					  </td>
					</tr>
				  </tbody>
				</table>
			  </div>

			  <div class="text-center mt-5 text-muted small">
				&copy; <?php echo date('Y'); ?> Joom Donation. All rights reserved.
			  </div>
			</div>
		  </div>
		</div>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
		<script>
		  // Kích hoạt Bootstrap Tooltip
		  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
		  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
			return new bootstrap.Tooltip(tooltipTriggerEl)
		  })
		</script>
		<?php
		$contents 	= ob_get_contents();
		ob_end_clean();

		echo $contents;
	}
}