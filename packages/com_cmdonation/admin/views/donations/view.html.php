<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View class for a list of donations.
 *
 * @since  1.0.0
 */
class CMDonationViewDonations extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

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
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->params		= $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Get payment methods.
		$paymentMethods = CMDonationHelper::getPaymentMethods();

		// Get campaigns.
		$campaigns = JModelList::getInstance('Campaigns', 'CMDonationModel')->getCampaignsForFilter();

		// Build campaign options for filter and index for displaying campaign name.
		$campaignOptions = array();
		$campaignIndex = array();

		if (!empty($campaigns))
		{
			foreach ($campaigns as $campaign)
			{
				$campaignOptions[] = JHtml::_('select.option', $campaign->id, htmlspecialchars($campaign->name));
				$campaignIndex[$campaign->id] = $campaign->name;
			}
		}

		$this->assignRef('paymentMethods', $paymentMethods);
		$this->assignRef('campaigns', $campaigns);
		$this->assignRef('campaignOptions', $campaignOptions);
		$this->assignRef('campaignIndex', $campaignIndex);

		$this->submenu = CMDonationHelper::addSubmenu('donations');
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
		$state	= $this->get('State');
		$canDo	= CMDonationHelper::getActions();

		JToolbarHelper::title(JText::_('COM_CMDONATION_MANAGER_DONATIONS'), 'donation icon-heart-2');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('donation.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('donation.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::checkin('donations.checkin');
		}

		if ($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList(JText::_('COM_CMDONATION_WARNING_DELETE_ITEMS'), 'donations.delete');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.id'					=> JText::_('JGRID_HEADING_ID'),
			'a.first_name'			=> JText::_('COM_CMDONATION_DONATION_FIRST_NAME_LABEL'),
			'a.last_name'			=> JText::_('COM_CMDONATION_DONATION_LAST_NAME_LABEL'),
			'a.email'				=> JText::_('COM_CMDONATION_DONATION_EMAIL_LABEL'),
			'a.country_code'		=> JText::_('COM_CMDONATION_DONATION_COUNTRY_LABEL'),
			'a.amount'				=> JText::_('COM_CMDONATION_DONATION_AMOUNT_LABEL'),
			'a.anonymous'			=> JText::_('COM_CMDONATION_DONATION_ANONYMOUS_LABEL'),
			'a.payment_method_id'	=> JText::_('COM_CMDONATION_DONATION_PAYMENT_METHOD_LABEL'),
			'a.status'				=> JText::_('COM_CMDONATION_DONATION_STATUS_LABEL'),
			'a.created'				=> JText::_('COM_CMDONATION_DONATION_CREATED_LABEL'),
			'a.completed'			=> JText::_('COM_CMDONATION_DONATION_COMPLETED_LABEL')
		);
	}
}
