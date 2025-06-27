<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\Utilities\ArrayHelper;

class plgOSMembershipDOcuments extends CMSPlugin implements SubscriberInterface
{
	use MPFEventResult;

	/**
	 * Application object.
	 *
	 * @var    \Joomla\CMS\Application\CMSApplication
	 */
	protected $app;

	/**
	 * Database object.
	 *
	 * @var \Joomla\Database\DatabaseDriver
	 */
	protected $db;

	/**
	 * Make language files will be loaded automatically.
	 *
	 * @var bool
	 */
	protected $autoloadLanguage = true;

	/**
	 * Path to the folder which documents are store
	 *
	 * @var string
	 */
	protected $documentsPath;

	/**
	 * Path to the folder store update packages
	 *
	 * @var bool
	 */
	protected $updatePackagesPath;

	public static function getSubscribedEvents(): array
	{
		return [
			'onEditSubscriptionPlan'      => 'onEditSubscriptionPlan',
			'onAfterSaveSubscriptionPlan' => 'onAfterSaveSubscriptionPlan',
			'onProfileDisplay'            => 'onProfileDisplay',
		];
	}

	/**
	 * Plugin constructor.
	 *
	 * @param   \Joomla\Event\DispatcherInterface  $subject
	 * @param   array                              $config
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($subject, $config)
	{
		parent::__construct($subject, $config);

		$path = $this->params->get('documents_path', 'media/com_osmembership/documents');

		if (is_dir(Path::clean(JPATH_ROOT . '/' . $path)))
		{
			$this->documentsPath = JPATH_ROOT . '/' . $path;
		}
		elseif (is_dir(Path::clean($path)))
		{
			$this->documentsPath = $path;
		}
		else
		{
			throw new InvalidArgumentException(sprintf('Invalid documents path %s', $path));
		}

		if (is_dir(Path::clean($this->documentsPath . '/update_packages')))
		{
			$this->updatePackagesPath = $this->documentsPath . '/update_packages';
		}
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onEditSubscriptionPlan(Event $event): void
	{
		/* @var OSMembershipTablePlan $row */
		[$row] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_clean();

		$result = [
			'title' => Text::_('OSM_DOWNLOADS_MANAGER'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onAfterSaveSubscriptionPlan(Event $event): void
	{
		/**
		 * @var string                $context
		 * @var OSMembershipTablePlan $row
		 * @var array                 $data
		 * @var                       $isNew
		 */
		[$context, $row, $data, $isNew] = array_values($event->getArguments());

		if (!$this->isExecutable())
		{
			return;
		}

		$db    = $this->db;
		$query = $db->getQuery(true);

		$documentIds = $data['document_id'] ?? [];
		$documentIds = ArrayHelper::toInteger($documentIds);

		// Remove the removed documents
		if (!$isNew)
		{
			$query->delete('#__osmembership_documents')
				->where('plan_id = ' . (int) $row->id);

			if (count($documentIds))
			{
				$query->whereNotIn('id', $documentIds);
			}

			$db->setQuery($query);
			$db->execute();
		}

		//save new data
		if (isset($data['document_title']))
		{
			$pathUpload           = Path::clean($this->documentsPath . '/');
			$documentIds          = $data['document_id'];
			$documentTitles       = $data['document_title'];
			$documentAttachments  = $_FILES['document_attachment'];
			$availableAttachments = $data['document_available_attachment'];
			$updatePackages       = $data['update_package'] ?? [];
			$orderings            = $data['document_ordering'];

			for ($i = 0; $n = count($documentTitles), $i < $n; $i++)
			{
				$documentTitle = $documentTitles[$i];

				if (empty($documentTitle))
				{
					continue;
				}

				$attachmentsFileName = '';

				if (is_uploaded_file($documentAttachments['tmp_name'][$i]))
				{
					$attachmentsFileName = File::makeSafe($documentAttachments['name'][$i]);
					File::upload($documentAttachments['tmp_name'][$i], $pathUpload . $attachmentsFileName);
				}

				$documentId    = (int) $documentIds[$i];
				$documentTitle = $db->quote($documentTitle);
				$ordering      = (int) $orderings[$i];

				if (!$attachmentsFileName)
				{
					$attachmentsFileName = $availableAttachments[$i];
				}

				$attachmentsFileName = $db->quote($attachmentsFileName);
				$updatePackage       = $updatePackages[$i] ?? '';
				$updatePackage       = $db->quote($updatePackage);

				if ($documentId)
				{
					$query->clear()
						->update('#__osmembership_documents')
						->set('ordering =' . $ordering)
						->set('title = ' . $documentTitle)
						->set('attachment = ' . $attachmentsFileName)
						->set('update_package = ' . $updatePackage)
						->where('id = ' . $documentId);
				}
				else
				{
					$query->clear()
						->insert('#__osmembership_documents')
						->columns('plan_id, ordering, title, attachment, update_package')
						->values("$row->id,$ordering,$documentTitle,$attachmentsFileName, $updatePackage");
				}

				$db->setQuery($query);
				$db->execute();
			}
		}

		// Clear data in plan documents table
		$query->clear()
			->delete('#__osmembership_plan_documents')
			->where('plan_id = ' . $row->id);
		$db->setQuery($query);
		$db->execute();

		$sql = 'INSERT INTO #__osmembership_plan_documents(plan_id, document_id) SELECT plan_id, id FROM #__osmembership_documents WHERE plan_id = ' . $row->id;
		$db->setQuery($sql)
			->execute();

		if (!empty($data['existing_document_ids']))
		{
			$documentIds = array_filter(ArrayHelper::toInteger($data['existing_document_ids']));

			if (count($documentIds))
			{
				$query->clear()
					->insert('#__osmembership_plan_documents')
					->columns($db->quoteName(['plan_id', 'document_id']));

				foreach ($documentIds as $documentId)
				{
					$query->values(implode(',', [$row->id, $documentId]));
				}

				$db->setQuery($query)
					->execute();
			}
		}
	}

	/**
	 * Render setting form
	 *
	 * @param   Event  $event
	 *
	 * @return void
	 */
	public function onProfileDisplay(Event $event): void
	{
		if ($this->app->isClient('administrator'))
		{
			return;
		}

		/* @var OSMembershipTableSubscriber $row */
		[$row] = array_values($event->getArguments());

		ob_start();
		$this->drawDocuments($row);
		$form = ob_get_contents();
		ob_end_clean();

		$result = [
			'title' => Text::_('OSM_MY_DOWNLOADS'),
			'form'  => $form,
		];

		$this->addResult($event, $result);
	}

	/**
	 * Display list of files which users can choose for event attachment
	 *
	 * @param   string  $path
	 *
	 * @return array
	 */
	protected function getAttachmentList($path)
	{
		$path      = Path::clean($path);
		$files     = Folder::files($path);
		$options   = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('OSM_SELECT_DOCUMENT'));

		foreach ($files as $file)
		{
			$options[] = HTMLHelper::_('select.option', $file, $file);
		}

		return $options;
	}

	/**
	 * Method to check if the plugin is executable
	 *
	 * @return bool
	 */
	private function isExecutable()
	{
		if ($this->app->isClient('site') && !$this->params->get('show_on_frontend'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param   object  $row
	 */
	private function drawSettingForm($row)
	{
		$db    = $this->db;
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__osmembership_documents')
			->order('ordering')
			->where('plan_id=' . (int) $row->id)
			->order('ordering');
		$db->setQuery($query);
		$documents = $db->loadObjectList();

		$options = $this->getAttachmentList($this->documentsPath);

		if ($this->updatePackagesPath)
		{
			$updatePackages = $this->getAttachmentList($this->updatePackagesPath);
		}
		else
		{
			$updatePackages = [];
		}

		$supportJoomlaUpdate = count($updatePackages);

		// Get the selected existing documents for this plan
		if ($row->id)
		{
			$query->clear()
				->select('document_id')
				->from('#__osmembership_plan_documents')
				->where('plan_id = ' . (int) $row->id);
			$db->setQuery($query);
			$planExistingDocumentIds = $db->loadColumn();
		}
		else
		{
			$planExistingDocumentIds = [];
		}

		// Get list of existing documents which can be selected for this plan
		$query->clear()
			->select('id, title')
			->from('#__osmembership_documents');

		if ($row->id)
		{
			$query->where('plan_id != ' . (int) $row->id);
		}

		$db->setQuery($query);
		$existingDocuments = $db->loadObjectList();

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'form');
	}

	/**
	 * Display Display List of Documents which the current subscriber can download from his subscription
	 *
	 * @param   object  $row
	 */
	private function drawDocuments($row)
	{
		$db            = $this->db;
		$query         = $db->getQuery(true);
		$activePlanIds = OSMembershipHelperSubscription::getActiveMembershipPlans();
		$query->select('a.*')
			->from('#__osmembership_documents AS a')
			->where(
				'a.id IN (SELECT document_id FROM #__osmembership_plan_documents AS b WHERE b.plan_id  IN (' . implode(',', $activePlanIds) . ') )'
			)
			->order('a.ordering');
		$db->setQuery($query);
		$documents = $db->loadObjectList();

		if (empty($documents))
		{
			return;
		}

		$Itemid = $this->app->input->getInt('Itemid');
		$path   = Path::clean($this->documentsPath . '/');

		require PluginHelper::getLayoutPath($this->_type, $this->_name, 'documents');
	}
}
