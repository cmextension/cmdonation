<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

require JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/cmdonation.php';

/**
 * Donation controller.
 *
 * @since  1.0.0
 */
class CMDonationControllerDonation extends JControllerLegacy
{
	protected $amount;

	protected $anonymous;

	protected $campaignId;

	protected $paymentMethod;

	protected $returnUrl;

	/**
	 * Method to receive donation amount and take user to payment service's donation page.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function submit()
	{
		$app				= JFactory::getApplication();
		$jinput				= $app->input;
		$amount				= $jinput->post->get('amount', 0, 'float');
		$anonymous			= $jinput->post->get('anonymous', false, 'boolean');
		$campaignId			= $jinput->post->get('campaign_id', 0, 'integer');
		$paymentMethodName	= $jinput->post->get('payment_method', '', 'word');
		$returnUrlBase64	= $jinput->post->get('return_url', '', 'base64 ');
		$recurring			= $jinput->post->get('recurring', false, 'boolean');
		$recurringCycle		= $jinput->post->get('recurring_cycle', '', 'word');

		// Convert return URL.
		if (empty($returnUrlBase64))
		{
			$returnUrl = JRoute::_('index.php', false);
			$returnUrlBase64 = base64_encode($returnUrl);
		}
		else
		{
			$returnUrl = JRoute::_(base64_decode($returnUrlBase64), false);
		}

		// Validate data.
		// Check for valid amount.
		if ($amount <= 0)
		{
			$app->enqueueMessage(JText::_('COM_CMDONATION_ERROR_INVALID_AMOUNT'), 'error');
			$app->redirect($returnUrl);
		}

		// Check for campaign's existence.
		$campaign = JModelLegacy::getInstance('Campaign', 'CMDonationModel')->getCampaignById($campaignId);

		if (!isset($campaign->id))
		{
			$app->enqueueMessage(JText::_('COM_CMDONATION_ERROR_INVALID_CAMPAIGN'), 'error');
			$app->redirect($returnUrl);
		}

		// Check if payment method exist
		$paymentMethod = CMDonationHelper::getPaymentMethodById($paymentMethodName);

		if (!isset($paymentMethod->name) || empty($paymentMethod->name))
		{
			$app->enqueueMessage(JText::_('COM_CMDONATION_ERROR_NO_PAYMENT_METHODS_SELECTED'), 'error');
			$app->redirect($returnUrl);
		}

		$params = JComponentHelper::getParams('com_cmdonation');
		$isRecurringEnabled = $params->get('recurring_donation', false, 'boolean');
		$supportedRecurringCycles = $params->get('recurring_cycles', array(), 'array');

		if ($recurring)
		{
			if (!$isRecurringEnabled || count($supportedRecurringCycles) == 0)
			{
				$app->enqueueMessage(JText::_('COM_CMDONATION_ERROR_RECURRING_DISABLED'), 'error');
				$app->redirect($returnUrl);
			}

			if (count($supportedRecurringCycles) == 1)
			{
				$recurringCycle = $supportedRecurringCycles[0];
			}
			elseif (count($supportedRecurringCycles) > 1)
			{
				if (!in_array($recurringCycle, $supportedRecurringCycles))
				{
					$app->enqueueMessage(JText::_('COM_CMDONATION_ERROR_SELECT_RECURRING_CYCLE'), 'error');
					$app->redirect($returnUrl);
				}
			}
		}

		// Save donation to database.
		$date = JFactory::getDate();
		$data = array(
			'campaign_id'		=> $campaignId,
			'anonymous'			=> (string) $anonymous,
			'amount'			=> $amount,
			'payment_method_id'	=> $paymentMethodName,
			'status'			=> 'INCOMPLETE',
			'created'			=> $date->toSql(),
			'recurring'			=> $recurring,
			'recurring_cycle'	=> $recurringCycle,
			'first_donation_id'	=> 0,
		);

		$donationModel = JModelLegacy::getInstance('Donation', 'CMDonationModel');
		$donationId = $donationModel->saveNewDonation($data);

		// Save donation ID in session to use in Thank You and Cancel page.
		$session				= JFactory::getSession();
		$donations				= $session->get('donations', array(), 'CMDonation');
		$donations[$donationId]	= $donationId;
		$session->set('donations', $donations, 'CMDonation');

		// Everything is ok now. Set variables and display the view.
		$data = new StdClass;
		$data->payment_method	= $paymentMethodName;
		$data->amount			= $amount;
		$data->campaign_name	= $campaign->name;
		$data->donation_id		= $donationId;
		$data->recurring		= $recurring;
		$data->recurring_cycle	= $recurringCycle;
		$data->return_url		= $returnUrl;
		$data->return_url_64	= $returnUrlBase64;

		$this->data = $data;

		$this->display(false, array());
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format.
		$viewName	= 'submit';
		$viewFormat	= $document->getType();
		$layoutName	= 'default';

		// Get and render the view.
		if ($view = $this->getView($viewName, $viewFormat))
		{
			// Get the model for the view.
			$model = $this->getModel('Default');
			$view->setLayout($layoutName);

			// Pass variables from controller to view
			$view->data = $this->data;

			// Push document object into the view.
			$view->document = $document;
			$view->display();
		}

		return $this;
	}

	/**
	 * Receive payment service's callback data and update donation's info.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function notify()
	{
		$app = JFactory::getApplication();
		$paymentMethodName  = $app->input->get('gateway', '', 'CMD');
		$valid = false;

		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('cmdonation');
		$results = JFactory::getApplication()->triggerEvent('onCMPaymentCallback', array($paymentMethodName));
		$app->close();
	}
}
