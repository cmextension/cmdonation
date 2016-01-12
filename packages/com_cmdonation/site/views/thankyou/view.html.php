<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View for returning from payment service's after successfull transaction or cancelling transaction.
 *
 * @since  1.0.0
 */
class CMDonationViewThankYou extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False on error, null otherwise.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$jinput		= $app->input;
		$layout		= $jinput->get('layout', '', 'WORD');
		$donationId	= $jinput->get('donation', 0, 'INTEGER');

		// Start checking if user really comes to this view after making donation or cancelling donation.
		// Redirect to home page if this is a direct access.

		// Check to see if layout is valid.
		if ($layout != 'complete' && $layout != 'cancel')
		{
			$app->redirect(JURI::root());
		}

		// Get donations that user has made in session.
		$session = JFactory::getSession();
		$donations = $session->get('donations', array(), 'CMDonation');

		// This donation is not in session.
		if (!array_key_exists($donationId, $donations))
		{
			$app->redirect(JURI::root());
		}

		// Check for donation exists.
		$donationModel	= JModelLegacy::getInstance('Donation', 'CMDonationModel');
		$donation		= $donationModel->getDonationById($donationId);

		// Donation doesn't exist.
		if (empty($donation))
		{
			$app->redirect(JURI::root());
		}

		// Donation exists and it was already completed,
		// display cancellation layout for it is not good.

		if ($layout == 'cancel' && $donation->status != 'INCOMPLETE')
		{
			$app->redirect(JURI::root());
		}

		// Load campaign.
		$campaignModel = JModelLegacy::getInstance('Campaign', 'CMDonationModel');
		$campaign = $campaignModel->getCampaignById($donation->campaign_id);

		// Campaign doesn't exist.
		if (empty($campaign))
		{
			$app->redirect(JURI::root());
		}

		// End checking.

		// We are only here if all validations are passed, we are ready to display thankful message.

		// Remove donation ID from session.
		unset($donations[$donationId]);
		$session->set('donations', $donations, 'CMDonation');

		$this->campaign	= $campaign;
		$this->layout	= $layout;
		$this->params	= $app->getParams();
		$this->_prepareDocument($layout);
		parent::display($tpl);
	}

	/**
	 * Prepare the document.
	 *
	 * @param   string  $layout  Layout name.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function _prepareDocument($layout)
	{
		$app	= JFactory::getApplication();
		$title	= null;

		// Override menu item's setting for page heading and page title.
		// We use our own page heading/title for specific cases, successful donation or cancelled donation.
		if ($layout == 'complete')
		{
			$pageTitle = JText::_('COM_CMDONATION_SUCCESSFUL_DONATION_PAGE_TITLE_THANK_YOU');
			$pageHeading = JText::_('COM_CMDONATION_SUCCESSFUL_DONATION_PAGE_HEADING_THANK_YOU');
		}
		elseif ($layout == 'cancel')
		{
			$pageTitle = JText::_('COM_CMDONATION_CANCELLED_DONATION_PAGE_TITLE_THANK_YOU');
			$pageHeading = JText::_('COM_CMDONATION_CANCELLED_DONATION_PAGE_HEADING_THANK_YOU');
		}

		$this->params->def('page_heading', $pageHeading);
		$this->document->setTitle($pageTitle);
	}
}
