<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Statistics view.
 *
 * @since  1.0.0
 */
class CMDonationViewStatistics extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$jinput = JFactory::getApplication()->input;
		$campaignId = $jinput->get('campaign', 0, 'integer');

		$campaigns = JModelLegacy::getInstance('Campaigns', 'CMDonationModel')->getCampaignsForFilter();

		$campaignList = array();
		$campaign = array();

		if (!empty($campaigns))
		{
			foreach ($campaigns as $camp)
			{
				$campaignList[$camp->id] = htmlspecialchars($camp->name);

				if ($camp->id == $campaignId)
				{
					$campaign = $camp;
				}
			}
		}

		$statistics = array();

		if (!empty($campaign))
		{
			$statistics = CMDonationHelper::generateStatistics($campaignId);
		}

		$params = JComponentHelper::getParams('com_cmdonation');

		// Get payment methods.
		$paymentMethods = CMDonationHelper::getPaymentMethods();

		$this->assignRef('paymentMethods', $paymentMethods);
		$this->assignRef('params', $params);
		$this->assignRef('campaignList', $campaignList);
		$this->assignRef('campaignId', $campaignId);
		$this->assignRef('campaign', $campaign);
		$this->assignRef('statistics', $statistics);

		$this->submenu = CMDonationHelper::addSubmenu('statistics');
		$this->addToolbar($campaign, $statistics);
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @param   object  $campaign    Campaign object.
	 * @param   array   $statistics  Array or statistics.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar($campaign, $statistics)
	{
		JToolbarHelper::title(JText::_('COM_CMDONATION_MANAGER_STATISTICS'), 'statistics icon-bars');

		if (!empty($campaign) && !empty($statistics['donations']))
		{
			JToolbarHelper::custom('statistics.export_donors', 'export out', 'export out', 'COM_CMDONATION_EXPORT_DONORS', false);
		}
	}
}
