<?php
/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2025 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

class plgOSMembershipUserCoupons extends CMSPlugin implements SubscriberInterface
{
    use MPFEventResult;

    /**
     * Application object.
     *
     * @var    CMSApplication
     */
    protected $app;

    /**
     * Database object.
     *
     * @var DatabaseDriver
     */
    protected $db;

    /**
     * Get list of subscriber
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onProfileDisplay' => 'onProfileDisplay',
        ];
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
        /* @var OSMembershipTableSubscriber $row */
        [$row] = array_values($event->getArguments());

        ob_start();
        $this->drawUserCoupons($row);

        $result = [
            'title' => Text::_('OSM_USER_COUPONS'),
            'form'  => ob_get_clean(),
        ];

        $this->addResult($event, $result);
    }

    /**
     * Display registration history of the current logged in user
     *
     * @param   OSMembershipTableSubscriber  $row
     */
    private function drawUserCoupons($row)
    {
        $db    = $this->db;
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__osmembership_coupons')
            ->where('user_id = ' . $row->user_id)
            ->where('published = 1')
            ->order('id DESC');
        $db->setQuery($query);

        $rows = $db->loadObjectList();

        if ($rows === []) {
            return;
        }

        $config = OSMembershipHelper::getConfig();

        require PluginHelper::getLayoutPath('osmembership', 'usercoupons', 'default');
    }
}
