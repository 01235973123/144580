<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;

class DonationControllerDashboard extends DonationController
{
    /**
     * Get dashboard data for the specified date range
     */
    public function getData()
    {
        // Check token for CSRF
        //$this->checkToken();
        
        // Get input parameters
        $input = Factory::getApplication()->input;
        $startDate = $input->getString('startDate', '');
        $endDate = $input->getString('endDate', '');
        
        // Get data from model
        $model = $this->getModel('Dashboard');
         
        try {
            $data = [
                'statistics' => $model->getStatistics($startDate, $endDate),
                'donationTimeline' => $model->getDonationTimeline('monthly', 12, $startDate, $endDate),
                'topCampaigns' => $model->getTopCampaigns(5, $startDate, $endDate),
                'endingSoonCampaigns' => $model->getEndingSoonCampaigns(5),
                'donorLocations' => $model->getDonorLocations(10, $startDate, $endDate),
                'recentDonations' => $model->getRecentDonations(10, $startDate, $endDate),
                'campaignDistribution' => $model->getCampaignDistribution($startDate, $endDate)
            ];
            
            // Return success response
            echo new JsonResponse([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            // Return error response
            echo new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        // Close the application
        Factory::getApplication()->close();
    }
    
    /**
     * Get timeline data for the specified type
     */
    public function getTimelineData()
    {
        // Check token for CSRF
        //$this->checkToken();
        
        // Get input parameters
        $input = Factory::getApplication()->input;
        $type = $input->getString('type', 'monthly');
        $startDate = $input->getString('startDate', '');
        $endDate = $input->getString('endDate', '');
        
        $limit = 12;
        switch ($type) {
            case 'daily':
                $limit = 30;
                break;
            case 'weekly':
                $limit = 12;
                break;
            case 'monthly':
                $limit = 12;
                break;
            case 'yearly':
                $limit = 5;
                break;
        }
        
        // Get data from model
        $model = $this->getModel('Dashboard');
        
        try {
            $data = $model->getDonationTimeline($type, $limit, $startDate, $endDate);
            
            // Return success response
            echo new JsonResponse([
                'success' => true,
                'data' => $data
            ]);
        } catch (Exception $e) {
            // Return error response
            echo new JsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        // Close the application
        Factory::getApplication()->close();
    }
    
    /**
     * Install sample data for demonstration
     */
    public function installSampleData()
    {
        // Check token for CSRF
        //$this->checkToken();
        
        // Get database
        $db = Factory::getApplication()->getIdentity();
        $date = Factory::getDate()->toSql();
        
        try {
            // Begin transaction
            $db->transactionStart();
            
            // Insert sample campaigns
            $campaigns = [
                [
                    'title' => 'Help Build a New School',
                    'description' => 'We\'re raising funds to build a new school in an underprivileged area.',
                    'start_date' => Factory::getDate('-30 days')->toSql(),
                    'end_date' => Factory::getDate('+60 days')->toSql(),
                    'goal' => 50000.00,
                    'donated_amount' => 32500.00,
                    'published' => 1,
                    'alias' => 'help-build-new-school'
                ],
                [
                    'title' => 'Clean Water Initiative',
                    'description' => 'Help us provide clean drinking water to communities in need.',
                    'start_date' => Factory::getDate('-60 days')->toSql(),
                    'end_date' => Factory::getDate('+30 days')->toSql(),
                    'goal' => 25000.00,
                    'donated_amount' => 18700.00,
                    'published' => 1,
                    'alias' => 'clean-water-initiative'
                ],
                [
                    'title' => 'Medical Supplies for Clinic',
                    'description' => 'We need your help to purchase essential medical supplies for our free clinic.',
                    'start_date' => Factory::getDate('-90 days')->toSql(),
                    'end_date' => Factory::getDate('+15 days')->toSql(),
                    'goal' => 15000.00,
                    'donated_amount' => 12300.00,
                    'published' => 1,
                    'alias' => 'medical-supplies-clinic'
                ],
                [
                    'title' => 'Disaster Relief Fund',
                    'description' => 'Support communities affected by recent natural disasters.',
                    'start_date' => Factory::getDate('-15 days')->toSql(),
                    'end_date' => Factory::getDate('+45 days')->toSql(),
                    'goal' => 75000.00,
                    'donated_amount' => 28900.00,
                    'published' => 1,
                    'alias' => 'disaster-relief-fund'
                ],
                [
                    'title' => 'Youth Education Program',
                    'description' => 'Help us provide educational opportunities for underprivileged youth.',
                    'start_date' => Factory::getDate('-45 days')->toSql(),
                    'end_date' => Factory::getDate('+5 days')->toSql(),
                    'goal' => 20000.00,
                    'donated_amount' => 17800.00,
                    'published' => 1,
                    'alias' => 'youth-education-program'
                ]
            ];
            
            $campaignIds = [];
            foreach ($campaigns as $campaign) {
                $campaignObj = (object) $campaign;
                $db->insertObject('#__jd_campaigns', $campaignObj);
                $campaignIds[] = $db->insertid();
            }
            
            // Sample donor data
            $donors = [
                // For campaign 1
                [
                    'campaign_id' => $campaignIds[0],
                    'first_name' => 'John',
                    'last_name' => 'Smith',
                    'email' => 'john.smith@example.com',
                    'country' => 'United States',
                    'city' => 'New York',
                    'created_date' => Factory::getDate('-25 days')->toSql(),
                    'payment_date' => Factory::getDate('-25 days')->toSql(),
                    'payment_method' => 'PayPal',
                    'amount' => 1000.00,
                    'published' => 1
                ],
                [
                    'campaign_id' => $campaignIds[0],
                    'first_name' => 'Emma',
                    'last_name' => 'Johnson',
                    'email' => 'emma.johnson@example.com',
                    'country' => 'United Kingdom',
                    'city' => 'London',
                    'created_date' => Factory::getDate('-20 days')->toSql(),
                    'payment_date' => Factory::getDate('-20 days')->toSql(),
                    'payment_method' => 'Credit Card',
                    'amount' => 500.00,
                    'published' => 1
                ],
                
                // For campaign 2
                [
                    'campaign_id' => $campaignIds[1],
                    'first_name' => 'Michael',
                    'last_name' => 'Brown',
                    'email' => 'michael.brown@example.com',
                    'country' => 'Canada',
                    'city' => 'Toronto',
                    'created_date' => Factory::getDate('-40 days')->toSql(),
                    'payment_date' => Factory::getDate('-40 days')->toSql(),
                    'payment_method' => 'Bank Transfer',
                    'amount' => 750.00,
                    'published' => 1
                ],
                
                // Add more sample donors...
                [
                    'campaign_id' => $campaignIds[2],
                    'first_name' => 'Sophia',
                    'last_name' => 'Garcia',
                    'email' => 'sophia.garcia@example.com',
                    'country' => 'Spain',
                    'city' => 'Madrid',
                    'created_date' => Factory::getDate('-55 days')->toSql(),
                    'payment_date' => Factory::getDate('-55 days')->toSql(),
                    'payment_method' => 'PayPal',
                    'amount' => 300.00,
                    'published' => 1
                ],
                
                // Add about 20-30 more sample donors with varying dates, amounts, and countries
                // to make the dashboard look realistic
            ];
            
            // Insert sample donors
            foreach ($donors as $donor) {
                $donorObj = (object) $donor;
                $db->insertObject('#__jd_donors', $donorObj);
            }
            
            // Commit transaction
            $db->transactionCommit();
            
            // Return success response
            echo new JsonResponse([
                'success' => true,
                'message' => 'Sample data installed successfully!'
            ]);
        } catch (Exception $e) {
            // Roll back transaction if failed
            $db->transactionRollback();
            
            // Return error response
            echo new JsonResponse([
                'success' => false,
                'message' => 'Error installing sample data: ' . $e->getMessage()
            ]);
        }
        
        // Close the application
        Factory::getApplication()->close();
    }
}
