<?php
/**
 * @package    PlgCMDonationPaypalProExpress
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();

use Joomla\Filter\InputFilter;

$include = include_once JPATH_ADMINISTRATOR . '/components/com_cmdonation/helpers/cmpayment.php';

if (!$include)
{
	unset($include);

	return;
}
else
{
	unset($include);
}

/**
 * Paypal Pro Express Checkout payment plugin.
 * Based on Akeeba Subscription's plgAkpaymentPaypalproexpress class (akeebabackup.com).
 *
 * @since  1.0.0
 */
class PlgCMDonationPaypalProExpress extends PlgCMPaymentAbstract
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$config = array_merge(
			$config,
			array(
				'name'	=> 'paypalproexpress',
				'key'	=> 'PLG_CMDONATION_PAYPALPROEXPRESS_TITLE',
			)
		);

		parent::__construct($subject, $config);

		$lang = JFactory::getLanguage();
		$lang->load('plg_cmdonation_' . $this->name, JPATH_ADMINISTRATOR . $this->name, null, false, true);
		$lang->load('plg_cmdonation_' . $this->name, JPATH_PLUGINS . '/cmdonation/' . $this->name, null, false, true);
		$lang->load('com_cmdonation', JPATH_SITE, null, false, true);
		$lang->load('com_cmdonation', JPATH_SITE . '/components/com_cmdonation/', null, false, true);
	}

	/**
	 * Returns the payment form to be submitted by the user's browser. The form must have an ID of
	 * "paymentForm" and a visible submit button.
	 *
	 * @param   string  $paymentMethod  The currently used payment method. Check it against $this->name.
	 * @param   JUser   $user           Current user.
	 * @param   object  $data           Object that contains the data of purchased items.
	 *
	 * @return  string  The payment form to render on the page. Use the special
	 *                  id 'paymentForm' to have it automatically submitted after
	 *                  5 seconds.
	 *
	 * @since   1.0.0
	 */
	public function onCMPaymentNew($paymentMethod, $user, $data)
	{
		if ($paymentMethod != $this->name)
		{
			return false;
		}

		$errorUrl		= $data->return_url;
		$returnUrl		= $this->prepareRoute('index.php?option=com_cmdonation&view=thankyou&layout=complete&donation=' . $data->donation_id);
		$cancelUrl		= $this->prepareRoute('index.php?option=com_cmdonation&view=thankyou&layout=cancel&donation=' . $data->donation_id);
		$callbackUrl	= JURI::base() . 'index.php?option=com_cmdonation&task=donation.notify&gateway=' . $this->name . '&ipn=false&donation_id=' . $data->donation_id . '&return=' . $data->return_url_64;

		$requestData = (object) array(
			'METHOD'							=> 'SetExpressCheckout',
			'USER'								=> $this->getMerchantUsername(),
			'PWD'								=> $this->getMerchantPassword(),
			'SIGNATURE'							=> $this->getMerchantSignature(),
			'VERSION'							=> $this->getAPIVersion(),
			'RETURNURL'							=> $callbackUrl,
			'CANCELURL'							=> $cancelUrl,
			'PAYMENTREQUEST_0_AMT'				=> sprintf('%.2f', $data->amount),
			// 'PAYMENTREQUEST_0_TAXAMT'			=> sprintf('%.2f', $weDonotUseTax),
			'PAYMENTREQUEST_0_ITEMAMT'			=> sprintf('%.2f', $data->amount),
			'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
			'PAYMENTREQUEST_0_CURRENCYCODE'		=> $this->getCurrency(),
			'L_PAYMENTREQUEST_0_NAME0'			=> $this->getDescription($data->campaign_name),
			'L_PAYMENTREQUEST_0_QTY0'			=> 1,
			'L_PAYMENTREQUEST_0_AMT0'			=> sprintf('%.2f', $data->amount),
			'NOSHIPPING'						=> 1,
			'ALLOWNOTE'							=> 0,
			// Allow paying with credit cards without signing up for a PayPal account.
			'SOLUTIONTYPE'						=> 'Sole',
			'LOCALECODE'						=> $this->getLocale(),
		);

		if ($data->recurring)
		{
			$requestData->L_BILLINGTYPE0 = 'RecurringPayments';
			$requestData->L_BILLINGAGREEMENTDESCRIPTION0 = $this->getDescription($data->campaign_name);
		}

		$requestQuery = http_build_query($requestData);
		$requestContext = stream_context_create(
			array(
			'http' => array (
				'method' => 'POST',
				'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
							"Connection: close\r\n" .
							"Content-Length: " . strlen($requestQuery) . "\r\n",
				'content' => $requestQuery)
			)
		);
		$responseQuery = file_get_contents(
			$this->getPaymentUrl(),
			false,
			$requestContext
		);

		// Payment Response.
		$responseData = array();
		parse_str($responseQuery, $responseData);

		if (preg_match('/^SUCCESS/', strtoupper($responseData['ACK'])))
		{
			$transactionUrl = $this->getPaypalUrl($responseData['TOKEN']);

			// Update transaction token of donation.
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->update($db->quoteName('#__cmdonation_donations'))
				->set($db->quoteName('transaction_token') . ' = ' . $db->quote($responseData['TOKEN']))
				->where($db->quoteName('id') . ' = ' . $db->quote($data->donation_id));

			$db->setQuery($query)->execute();
		}
		else
		{
			JFactory::getApplication()->redirect($errorUrl, $responseData['L_LONGMESSAGE0'], 'error');
		}

		$paymentMethodName = JText::_($this->key);

		@ob_start();
		include dirname(__FILE__) . '/form.php';
		$html = @ob_get_clean();

		return $html;
	}

	/**
	 * Processes a callback from the payment processor.
	 *
	 * @param   string  $paymentMethodName  The currently used payment method. Check it against $this->name
	 *
	 * @return  boolean  True if the callback was handled, false otherwise
	 *
	 * @since   1.0.0
	 */
	public function onCMPaymentCallback($paymentMethodName)
	{
		$ipn = JFactory::getApplication()->input->get('ipn', 'true', 'word');

		// Check if we're supposed to handle this.
		if ($paymentMethodName != $this->name)
		{
			return false;
		}

		if ($ipn == 'false')
		{
			return $this->formCallback();
		}
		else
		{
			return $this->IPNCallback();
		}
	}

	/**
	 * Handle callback from form submission.
	 *
	 * @return  void   Redirect to different page.
	 *
	 * @since   1.0.0
	 */
	private function formCallback()
	{
		$app 		= JFactory::getApplication();
		$jinput 	= $app->input;
		$donationId	= $jinput->get('donation_id', 0, 'uint');
		$token		= $jinput->get('token', '');
		$payerId	= $jinput->get('PayerID', '');
		$return		= $jinput->get('return', '');

		if (empty($return))
		{
			$errorUrl = JRoute::_('index.php', false);
		}
		else
		{
			$errorUrl = JRoute::_(base64_decode($return), false);
		}

		JLoader::import('joomla.utilities.date');
		$isValid = true;

		if ($isValid)
		{
			$donation = null;

			if ($donationId > 0)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('d.*')
					->from($db->quoteName('#__cmdonation_donations') . ' AS d')
					->where($db->quoteName('d.id') . ' = ' . $db->quote($donationId))
					->where($db->quoteName('d.transaction_token') . ' = ' . $db->quote($token));

				// Join with campaign table to get campaign's name.
				$query->join('LEFT', $db->quoteName('#__cmdonation_campaigns') . ' AS c ON c.id = d.campaign_id')
					->select($db->quoteName('c.name') . ' AS campaign_name');

				$donation = $db->setQuery($query)->loadObject();

				if (!isset($donation->id) || ($donation->id != $donationId))
				{
					$donation = null;
					$isValid = false;
				}
			}
			else
			{
				$isValid = false;
			}

			if (!$isValid)
			{
				$responseData['cmdonation_failure_reason'] = 'The donation ID is invalid';
			}
		}

		if ($isValid && isset($token) && isset($payerId))
		{
			$requestData = (object) array(
				'METHOD'							=> 'DoExpressCheckoutPayment',
				'USER'								=> $this->getMerchantUsername(),
				'PWD'								=> $this->getMerchantPassword(),
				'SIGNATURE'							=> $this->getMerchantSignature(),
				'VERSION'							=> $this->getAPIVersion(),
				'TOKEN'								=> $token,
				'PAYERID'							=> $payerId,
				'PAYMENTREQUEST_0_PAYMENTACTION'	=> 'Sale',
				'PAYMENTREQUEST_0_AMT'				=> sprintf('%.2f', $donation->amount),
				'PAYMENTREQUEST_0_CURRENCYCODE'		=> $this->getCurrency(),
				'PAYMENTREQUEST_0_INVNUM'			=> $donation->id,
				'PAYMENTREQUEST_0_DESC'				=> $this->getDescription($donation->campaign_name),
				'IPADDRESS'							=> $_SERVER['REMOTE_ADDR']
			);

			$requestQuery = http_build_query($requestData);
			$requestContext = stream_context_create(
				array(
				'http' => array (
					'method' => 'POST',
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
								"Connection: close\r\n" .
								"Content-Length: " . strlen($requestQuery) . "\r\n",
					'content' => $requestQuery)
				)
			);
			$responseQuery = file_get_contents(
				$this->getPaymentUrl(),
				false,
				$requestContext
			);

			// Payment Response.
			$responseData = array();
			parse_str($responseQuery, $responseData);

			if (!preg_match('/^SUCCESS/', strtoupper($responseData['ACK'])))
			{
				$isValid = false;
				$app->redirect($errorUrl, $responseData['L_LONGMESSAGE0'], 'error');
			}
			elseif (! preg_match('/^SUCCESS/', strtoupper($responseData['PAYMENTINFO_0_ACK'])))
			{
				$isValid = false;
				$responseData['cmdonation_failure_reason'] = "PayPal error code: " . $responseData['PAYMENTINFO_0_ERRORCODE'];
			}

			$supportedPeriods = array('D', 'W', 'M', 'Y');

			if ($donation->recurring && in_array($donation->recurring_cycle, $supportedPeriods))
			{
				$period = $this->convertPeriod($donation->recurring_cycle);

				// Create recurring payment profile.
				$startDate = new JDate;
				$callbackUrl = JURI::base() . 'index.php?option=com_cmdonation&task=donation.notify&gateway=' . $this->name . '&ipn=true&donation_id=' . $donationId;
				$recurringRequestData = (object) array(
					'METHOD'			=> 'CreateRecurringPaymentsProfile',
					'NOTIFYURL'			=> $callbackUrl,
					'USER'				=> $this->getMerchantUsername(),
					'PWD'				=> $this->getMerchantPassword(),
					'SIGNATURE'			=> $this->getMerchantSignature(),
					'VERSION'			=> $this->getAPIVersion(),
					'PAYMENTACTION'		=> 'Sale',
					'TOKEN'				=> $token,
					'PAYERID'			=> $payerId,
					'IPADDRESS'			=> $_SERVER['REMOTE_ADDR'],
					'AMT'				=> sprintf('%.2f', $donation->amount),
					// 'TAXAMT'			=> sprintf('%.2f', $weDonotUseTax),
					'CURRENCYCODE'		=> $this->getCurrency(),
					'DESC'				=> $this->getDescription($donation->campaign_name),
					'PROFILEREFERENCE'	=> $donation->id,
					'PROFILESTARTDATE'	=> $startDate->toISO8601(),
					'BILLINGPERIOD'		=> $period,
					'BILLINGFREQUENCY'	=> 1,
				);

				$recurringRequestQuery = http_build_query($recurringRequestData);
				$recurringRequestContext = stream_context_create(
					array(
					'http' => array (
						'method' => 'POST',
						'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
									"Connection: close\r\n" .
									"Content-Length: " . strlen($recurringRequestQuery) . "\r\n",
						'content' => $recurringRequestQuery)
					)
				);
				$recurringResponseQuery = file_get_contents(
					$this->getPaymentUrl(),
					false,
					$recurringRequestContext
				);

				// Response of payment profile.
				$recurringResponseData = array();
				parse_str($recurringResponseQuery, $recurringResponseData);

				if (!preg_match('/^SUCCESS/', strtoupper($recurringResponseData['ACK'])))
				{
					$isValid = false;
					$app->redirect($errorUrl, $recurringResponseData['L_LONGMESSAGE0'], 'error');
				}
				else
				{
					$recurringCheckData = (object) array(
						'METHOD'	=> 'GetRecurringPaymentsProfileDetails',
						'USER'		=> $this->getMerchantUsername(),
						'PWD'		=> $this->getMerchantPassword(),
						'SIGNATURE'	=> $this->getMerchantSignature(),
						'VERSION'	=> $this->getAPIVersion(),
						'PROFILEID'	=> $recurringResponseData['PROFILEID'],
					);

					$recurringCheckQuery = http_build_query($recurringCheckData);
					$recurringCheckContext = stream_context_create(
						array(
						'http' => array (
							'method' => 'POST',
							'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
										"Connection: close\r\n" .
										"Content-Length: " . strlen($recurringCheckQuery) . "\r\n",
							'content' => $recurringCheckQuery)
						)
					);
					$recurringCheckQuery = file_get_contents(
						$this->getPaymentUrl(),
						false,
						$recurringCheckContext
					);

					// Response of payment profile
					$recurringCheckData = array();
					parse_str($recurringCheckQuery, $recurringCheckData);

					if (!preg_match('/^SUCCESS/', strtoupper($recurringCheckData['ACK'])))
					{
						$isValid = false;
						$app->redirect($errorUrl, $recurringCheckData['L_LONGMESSAGE0'], 'error');
					}

					if (strtoupper($responseData['PAYMENTINFO_0_CURRENCYCODE']) !== strtoupper($recurringCheckData['CURRENCYCODE']))
					{
						$isValid = false;
						$responseData['cmdonation_failure_reason'] = "Currency code doesn't match.";
					}

					if (strtoupper($responseData['PAYMENTINFO_0_AMT']) !== strtoupper($recurringCheckData['AMT']))
					{
						$isValid = false;
						$responseData['cmdonation_failure_reason'] = "Amount doesn't match.";
					}

					$period = $this->convertPeriod($donation->recurring_cycle);

					if (strtoupper($recurringCheckData['BILLINGPERIOD']) !== strtoupper($period))
					{
						$isValid = false;
						$responseData['cmdonation_failure_reason'] = "Recurring period doesn't match.";
					}

					if ($recurringCheckData['BILLINGFREQUENCY'] != 1)
					{
						$isValid = false;
						$responseData['cmdonation_failure_reason'] = "Recurring duration doesn't match";
					}
				}
			}
		}

		if ($isValid)
		{
			if ($donation->transaction_id == $responseData['PAYMENTINFO_0_TRANSACTIONID'])
			{
				$isValid = false;
				$responseData['cmdonation_failure_reason'] = "I will not process the same TRANSACTIONID " . $responseData['PAYMENTINFO_0_TRANSACTIONID'] . " twice";
			}
		}

		// Check that currency is correct.
		if ($isValid)
		{
			if (strtoupper($this->getCurrency()) != strtoupper($responseData['PAYMENTINFO_0_CURRENCYCODE']))
			{
				$isValid = false;
				$responseData['cmdonation_failure_reason'] = "Currency code doesn't match.";
			}
		}

		// Check that amount is correct.
		if ($isValid)
		{
			$mc_gross = floatval($responseData['PAYMENTINFO_0_AMT']);
			$gross = $donation->amount;

			$isValid = ($gross - $mc_gross) < 0.01;

			if (!$isValid)
			{
				$responseData['cmdonation_failure_reason'] = 'Paid amount does not match the subscription amount';
			}
		}

		// Log the IPN data.
		$this->logIPN($responseData, $isValid);

		// Fraud attempt? Do nothing more!
		if (!$isValid)
		{
			$app->redirect($errorUrl, $responseData['cmdonation_failure_reason'], 'error');
		}

		// Check payment status.
		switch ($responseData['PAYMENTINFO_0_PAYMENTSTATUS'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$newStatus = 'COMPLETED';
				break;

			case 'Refunded':
				$newStatus = 'REFUNDED';
				break;

			default:
				$newStatus = 'INCOMPLETE';
				break;
		}

		// Only get payer's info when payment is completed.
		if ($newStatus == 'COMPLETED')
		{
			$detailRequestData = (object) array(
				'METHOD'	=> 'GetExpressCheckoutDetails',
				'USER'		=> $this->getMerchantUsername(),
				'PWD'		=> $this->getMerchantPassword(),
				'SIGNATURE'	=> $this->getMerchantSignature(),
				'VERSION'	=> $this->getAPIVersion(),
				'TOKEN'		=> $token,
			);

			$detailRequestQuery = http_build_query($detailRequestData);
			$detailRequestContext = stream_context_create(
				array(
				'http' => array (
					'method' => 'POST',
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
								"Connection: close\r\n" .
								"Content-Length: " . strlen($detailRequestQuery) . "\r\n",
					'content' => $detailRequestQuery)
				)
			);
			$detailRequestQuery = file_get_contents(
				$this->getPaymentUrl(),
				false,
				$detailRequestContext
			);

			$detailResponseData = array();
			parse_str($detailRequestQuery, $detailResponseData);

			// If it is failed to get transaction detail, we ignore and leave payer's info empty in our database.
			if (preg_match('/^SUCCESS/', strtoupper($detailResponseData['ACK'])))
			{
				$firstName = isset($detailResponseData['FIRSTNAME']) ? $detailResponseData['FIRSTNAME'] : '';
				$lastName = isset($detailResponseData['LASTNAME']) ? $detailResponseData['LASTNAME'] : '';
				$email = isset($detailResponseData['EMAIL']) ? $detailResponseData['EMAIL'] : '';
				$countryCode = isset($detailResponseData['COUNTRYCODE']) ? $detailResponseData['COUNTRYCODE'] : '';
			}
			else
			{
				$firstName = $lastName = $email = $countryCode = '';
			}
		}

		// Update donation.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->quoteName('#__cmdonation_donations'))
			->set($db->quoteName('transaction_id') . ' = ' . $db->quote($responseData['PAYMENTINFO_0_TRANSACTIONID']))
			->set($db->quoteName('transaction_token') . ' = ' . $db->quote(''))
			->set($db->quoteName('transaction_params') . ' = ' . $db->quote(json_encode($responseData)))
			->set($db->quoteName('status') . ' = ' . $db->quote($newStatus))
			->where($db->quoteName('id') . ' = ' . $db->quote($donation->id));

		if ($newStatus == 'COMPLETED')
		{
			$now = new JDate;
			$query->set($db->quoteName('first_name') . ' = ' . $db->quote($firstName));
			$query->set($db->quoteName('last_name') . ' = ' . $db->quote($lastName));
			$query->set($db->quoteName('email') . ' = ' . $db->quote($email));
			$query->set($db->quoteName('country_code') . ' = ' . $db->quote($countryCode));
			$query->set($db->quoteName('completed') . ' = ' . $db->quote($now->toSql()));
		}
		else
		{
			$query->set($db->quoteName('completed') . ' = ' . $db->quote($db->getNullDate()));
		}

		$db->setQuery($query)->execute();

		// Redirect the user to the "thank you" page
		$thankYouUrl = $this->prepareRoute('index.php?option=com_cmdonation&view=thankyou&layout=complete&donation=' . $donationId);
		JFactory::getApplication()->redirect($thankYouUrl);
	}

	/**
	 * Handle callback from PayPal.
	 *
	 * @return  boolean  True if valid.
	 *
	 * @since   1.0.0
	 */
	private function IPNCallback()
	{
		$app = JFactory::getDbo();

		JLoader::import('joomla.utilities.date');

		$data = $_POST;

		if (empty($data))
		{
			$app->close();
		}

		$isValid = $this->isValidIPN($data);

		$filter = new InputFilter;

		foreach ($data as $k => $v)
		{
			$data[$k] = $filter->clean($v);
		}

		if (!$isValid)
		{
			$data['cmdonation_failure_reason'] = 'PayPal reports transaction as invalid';
		}

		if ($isValid)
		{
			$validTypes = array('express_checkout', 'recurring_payment', 'recurring_payment_profile_created');
			$isValid = in_array($data['txn_type'], $validTypes);

			if (!$isValid)
			{
				$data['cmdonation_failure_reason'] = "Transaction type " . $data['txn_type'] . " can't be processed by this payment plugin.";
			}
			else
			{
				$recurring = ($data['txn_type'] == 'recurring_payment');
			}
		}

		if ($isValid)
		{
			$id = $recurring ? $data['rp_invoice_id'] : $data['invoice'];
			$donation = null;

			if ($id > 0)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from($db->quoteName('#__cmdonation_donations'))
					->where($db->quoteName('id') . ' = ' . $db->quote($id));

				$donation = $db->setQuery($query)->loadObject();

				if (!isset($donation->id) || $donation->id != $id)
				{
					$donation = null;
					$isValid = false;
				}
			}
			else
			{
				$isValid = false;
			}

			if (!$isValid)
			{
				$data['cmdonation_failure_reason'] = 'The donation ID is invalid';
			}
		}

		// Check that amount is correct.
		if ($isValid)
		{
			$eAmount = floatval($data['mc_gross']);
			$gAmount = $donation->amount;

			$isValid = ($gAmount - $eAmount) < 0.01;

			if (!$isValid)
			{
				$data['cmdonation_failure_reason'] = "Amounts do not match. Expect $eAmount, but get $gAmount.";
			}
		}

		// Check that currency is correct.
		if ($isValid)
		{
			$eCurrency = strtoupper($this->getCurrency());
			$gCurrency = strtoupper($data['mc_currency']);

			if ($eCurrency != $gCurrency)
			{
				$isValid = false;
				$data['cmdonation_failure_reason'] = "Currency code doesn't match. Expect $eCurrency, but get $gCurrency.";
			}
		}

		// Log the IPN data.
		$this->logIPN($data, $isValid);

		// Fraud attempt? Do nothing more!
		if (!$isValid)
		{
			$app->close();
		}

		// Check  payment status
		switch ($data['payment_status'])
		{
			case 'Canceled_Reversal':
			case 'Completed':
				$status = 'COMPLETED';
				break;

			case 'Refunded':
				$status = 'REFUNDED';
				break;

			default:
				$status = "INCOMPLETED";
				break;
		}

		$db = JFactory::getDbo();
		$now = new JDate;

		if ($recurring)
		{
			// Save new donation.
			$donationData = array(
				'campaign_id'			=> $donation->campaign_id,
				'first_name'			=> $donation->first_name,
				'last_name'				=> $donation->last_name,
				'email'					=> $donation->email,
				'country_code'			=> $donation->country_code,
				'anonymous'				=> $donation->anonymous,
				'recurring'				=> $recurring,
				'recurring_cycle'		=> $donation->recurring_cycle,
				'first_donation_id'		=> $donation->id,
				'amount'				=> $data['mc_gross'],
				'payment_method_id'		=> $donation->payment_method_id,
				'status'				=> $status,
				'transaction_id'		=> $data['txn_id'],
				'transaction_token'		=> '',
				'transaction_params'	=> json_encode($data),
				'created'				=> $now->toSql(),
			);

			if ($status == 'COMPLETED')
			{
				$donationData['completed'] = $now->toSql();
			}

			$db->insertObject('#__cmdonation_donations', $donationData, 'id');
		}
		else
		{
			// Update old donation.
			$query = $db->getQuery(true)
				->update($db->quoteName('#__cmdonation_donations'))
				->set(
					array(
						$db->quoteName('status') . ' = ' . $db->quote($status),
						$db->quoteName('transaction_params') . ' = ' . $db->quote(json_encode($data)),
					)
				)
				->where($db->quoteName('id') . ' = ' . $db->quote($donation->id));

			if ($status == 'COMPLETED')
			{
				$query->set($db->quoteName('completed') . ' = ' . $db->quote($now->toSql()));
			}

			$db->setQuery($query);
			$db->execute();
		}

		$app->close();
	}

	/**
	 * Validates the incoming data against PayPal's IPN to make sure this is not a
	 * fraudelent request.
	 *
	 * @param   array  $data  PayPal's data.
	 *
	 * @return  boolean  True if valid, false if invalid.
	 *
	 * @since   1.0.0
	 */
	private function isValidIPN($data)
	{
		$sandbox = $this->params->get('sandbox', 0);
		$hostname = $sandbox ? 'www.sandbox.paypal.com' : 'www.paypal.com';

		$url = 'ssl://' . $hostname;
		$port = 443;

		$req = 'cmd=_notify-validate';

		foreach ($data as $key => $value)
		{
			$value = urlencode($value);
			$req .= "&$key=$value";
		}

		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: $hostname:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "Connection: Close\r\n\r\n";

		$fp = fsockopen($url, $port, $errno, $errstr, 30);

		if (!$fp)
		{
			// HTTP ERROR.
			return false;
		}
		else
		{
			fputs($fp, $header . $req);

			while (!feof($fp))
			{
				$res = fgets($fp, 1024);

				if (stristr($res, "VERIFIED"))
				{
					return true;
				}
				elseif (stristr($res, "INVALID"))
				{
					return false;
				}
			}

			fclose($fp);
		}
	}

	/**
	 * Get Paypal's NVP URL.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getPaymentUrl()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return 'https://api-3t.sandbox.paypal.com/nvp';
		}
		else
		{
			return 'https://api-3t.paypal.com/nvp';
		}
	}

	/**
	 * Get Paypal's transaction page URL.
	 *
	 * @param   string  $token  Transaction's token.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getPaypalUrl($token)
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=' . $token;
		}
		else
		{
			return 'https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=' . $token;
		}
	}

	/**
	 * Get merchant's API username from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getMerchantUsername()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return trim($this->params->get('sb_apiuser', ''));
		}
		else
		{
			return trim($this->params->get('apiuser', ''));
		}
	}

	/**
	 * Get merchant's API password from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getMerchantPassword()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return trim($this->params->get('sb_apipw', ''));
		}
		else
		{
			return trim($this->params->get('apipw', ''));
		}
	}

	/**
	 * Get merchant's API signature from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getMerchantSignature()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return trim($this->params->get('sb_apisig', ''));
		}
		else
		{
			return trim($this->params->get('apisig', ''));
		}
	}

	/**
	 * Get currency from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getCurrency()
	{
		return trim($this->params->get('currency', 'USD'));
	}

	/**
	 * Get locale from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.0.2
	 */
	private function getLocale()
	{
		$locale = trim($this->params->get('locale', 'GB'));

		if ($locale == 'JOOMLA')
		{
			$lang = JFactory::getLanguage();
			$langTag = $lang->getTag();

			/*
			 * Array(Joomla! language tag => PayPal local code)
			 * https://developer.paypal.com/docs/classic/api/merchant/SetExpressCheckout_API_Operation_NVP/
			 */
			$langMaps = array(
				'en-AU'	=> 'AU',
				'nl-BE'	=> 'BE',
				'pt-PT'	=> 'BR', // Or pt_BR?
				'en-CA'	=> 'CA',
				'de-DE'	=> 'DE',
				'de-CH'	=> 'DE',
				'de-AT'	=> 'DE',
				'es-ES'	=> 'ES',
				'en-GB'	=> 'GB',
				'fr-CA'	=> 'FR',
				'it-IT'	=> 'IT',
				'nl-NL'	=> 'NL',
				'pl-PL'	=> 'PL',
				'pt-PT'	=> 'PT',
				'ru-RU'	=> 'RU', // Or ru_RU?
				'en-US'	=> 'US',
				'zh-CN'	=> 'zh_CN',
				'zh-TW'	=> 'zh_TW',
				'da-DK'	=> 'da_DK',
				'he-IL'	=> 'he_IL',
				'id-ID'	=> 'id_ID',
				'ja-JP'	=> 'ja_JP',
				'nb-NO'	=> 'no_NO',
				'nn-NO'	=> 'no_NO',
				'sv-SE'	=> 'sv_SE',
				'th-TH'	=> 'th_TH',
				'tr-TR'	=> 'tr_TR'
			);

			return isset($langMaps[$langTag]) ? $langMaps[$langTag] : 'GB';
		}

		return $locale;
	}

	/**
	 * Get PayPal's API version
	 *
	 * @return  float
	 *
	 * @since   1.0.0
	 */
	private function getAPIVersion()
	{
		return 114.0;
	}

	/**
	 * Get transaction description.
	 *
	 * @param   string  $campaignName  Campaign name.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function getDescription($campaignName)
	{
		return JText::sprintf('COM_CMDONATION_TRANSACTION_NAME', $campaignName);
	}

	/**
	 * Generate site's URL for redirection. Fix subfolder issue.
	 *
	 * @param   string  $url  Page URL.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function prepareRoute($url)
	{
		$rootUrl = rtrim(JURI::base(), '/');
		$subPathUrl = JURI::base(true);

		if (!empty($subPathUrl) && ($subPathUrl != '/'))
		{
			$rootUrl = substr($rootUrl, 0, -1 * strlen($subPathUrl));
		}

		return $rootUrl . str_replace('&amp;', '&', JRoute::_($url));
	}

	/**
	 * Convert the component's cycle key to PayPal's period key.
	 *
	 * @param   string  $cycle  Cycle.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function convertPeriod($cycle = '')
	{
		switch ($cycle)
		{
			case 'D':
				$period = 'Day';
				break;

			case 'W':
				$period = 'Week';
				break;

			case 'M':
				$period = 'Month';
				break;

			case 'Y':
				$period = 'Year';
				break;

			default:
				$period = '';
				break;
		}

		return $period;
	}
}
