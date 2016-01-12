<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Dashboard view.
 *
 * @since  1.0.0
 */
class CMDonationViewDashboard extends JViewLegacy
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
		// Get campaigns.
		$this->campaigns = CMDonationHelper::getCampaignStats();

		// Get 10 latest donations.
		$this->latestDonations = JModelLegacy::getInstance('Donations', 'CMDonationModel')->getLatestDonations(0, 10);

		$this->submenu = CMDonationHelper::addSubmenu('dashboard');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$canDo = CMDonationHelper::getActions();

		JToolbarHelper::title(JText::_('COM_CMDONATION_MANAGER_DASHBOARD'), 'dashboard.png');

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_cmdonation');
		}
	}
}
