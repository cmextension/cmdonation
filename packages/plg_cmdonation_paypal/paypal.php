<?php
/**
 * @package    PlgCMDonationPaypal
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
 * Paypal payment plugin.
 *
 * @since  1.1.0
 */
class PlgCMDonationPaypal extends PlgCMPaymentAbstract
{
	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   1.1.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$config = array_merge(
			$config,
			array(
				'name'	=> 'paypal',
				'key'	=> 'PLG_CMDONATION_PAYPAL_TITLE',
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
	 * @since   1.1.0
	 */
	public function onCMPaymentNew($paymentMethod, $user, $data)
	{
		if ($paymentMethod != $this->name)
		{
			return false;
		}

		$return			= $this->prepareRoute('index.php?option=com_cmdonation&view=thankyou&layout=complete&donation=' . $data->donation_id);
		$cancelReturn	= $this->prepareRoute('index.php?option=com_cmdonation&view=thankyou&layout=cancel&donation=' . $data->donation_id);
		$notifyUrl		= JURI::base() . 'index.php?option=com_cmdonation&task=donation.notify&gateway=' . $this->name . '&ipn=false&donation_id=' . $data->donation_id . '&return=' . $data->return_url_64;

		$itemName		= $this->getDescription($data->campaign_name);
		$business		= $this->getMerchantEmail();
		$currency		= $this->getCurrency();
		$lc				= $this->getLocale();
		$pageStyle		= $this->getPageStyle();
		$logoUrl		= $this->getLogoUrl();
		$headerUrl		= $this->getHeaderUrl();

		$formData = array(
			'cmd'						=> '_donations',
			'item_name'					=> $itemName,
			'business'					=> $business,
			'currency_code'				=> $currency,
			'lc'						=> $lc,
			'item_number'				=> $data->donation_id,
			'amount'					=> $data->amount,
			'no_shipping'				=> 1,
			'rm'						=> 2,
			'return'					=> $return,
			'cancel_return'				=> $cancelReturn,
			'notify_url'				=> $notifyUrl,
			'page_style'				=> $pageStyle,
			'image_url'					=> $logoUrl,
			'cpp_header_image'			=> $headerUrl,
			'cpp_headerback_color'		=> $this->params->get('headerback_color', ''),
			'cpp_headerborder_color'	=> $this->params->get('headerborder_color', ''),
			'cbt'						=> trim($this->params->get('cbt', '')),
		);

		$supportedPeriods = array('D', 'W', 'M', 'Y');

		if ($data->recurring && in_array($data->recurring_cycle, $supportedPeriods))
		{
			$formData['cmd'] = '_xclick-subscriptions';
			$formData['a3'] = $data->amount;
			$formData['p3'] = 1;
			$formData['t3'] = $data->recurring_cycle;
			$formData['src'] = 1;
			$formData['sra'] = 1;
		}

		$transactionUrl = $this->getPaypalUrl();

		$secondsToWait		= $this->params->get('seconds_to_wait', '5');
		$secondsToWait		= (int) $secondsToWait;
		$redirectMessage	= '<a href="#" onClick="return submitForm()">';
		$redirectMessage	.= JText::_('PLG_CMDONATION_PAYPAL_CLICK_HERE');
		$redirectMessage	.= '</a>';
		$redirectMessage	= JText::sprintf('PLG_CMDONATION_PAYPAL_REDIRECT_MESSAGE', $secondsToWait, $redirectMessage);

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
	 * @since   1.1.0
	 */
	public function onCMPaymentCallback($paymentMethodName)
	{
		// Check if we're supposed to handle this.
		if ($paymentMethodName != $this->name)
		{
			return false;
		}

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
			$validTypes = array('web_accept', 'subscr_signup', 'subscr_payment');
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
			$id = $data['item_number'];
			$donation = null;

			if ($id > 0)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__cmdonation_donations'))
					->where($db->qn('id') . ' = ' . $db->q($id));

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
				'first_name'			=> $data['first_name'],
				'last_name'				=> $data['last_name'],
				'email'					=> $data['payer_email'],
				'country_code'			=> $data['residence_country'],
				'anonymous'				=> $donation->anonymous,
				'recurring'				=> $recurring,
				'recurring_cycle'		=> $donation->recurring_cycle,
				'first_donation_id'		=> $donation->id,
				'amount'				=> $data['mc_gross'],
				'payment_method_id'		=> $donation->payment_method_id,
				'status'				=> $status,
				'transaction_id'		=> $filter->clean($data['txn_id']),
				'transaction_token'		=> '',
				'transaction_params'	=> json_encode($data),
				'created'				=> $now->toSql(),
			);

			if ($status == 'COMPLETED')
			{
				$donationData['completed'] = $now->toSql();
			}

			$donationData = JArrayHelper::toObject($donationData);

			$db->insertObject('#__cmdonation_donations', $donationData, 'id');
		}
		else
		{
			// Update old donation.
			$donationData = array(
				'first_name'			=> $data['first_name'],
				'last_name'				=> $data['last_name'],
				'email'					=> $data['payer_email'],
				'country_code'			=> $data['residence_country'],
				'status'				=> $status,
				'transaction_id'		=> $filter->clean($data['txn_id']),
				'transaction_params'	=> json_encode($data),
			);

			$query = $db->getQuery(true)
				->update($db->qn('#__cmdonation_donations'))
				->set(
					array(
						$db->qn('first_name') . ' = ' . $db->q($data['first_name']),
						$db->qn('last_name') . ' = ' . $db->q($data['last_name']),
						$db->qn('email') . ' = ' . $db->q($data['payer_email']),
						$db->qn('country_code') . ' = ' . $db->q($data['residence_country']),
						$db->qn('status') . ' = ' . $db->q($status),
						$db->qn('transaction_id') . ' = ' . $db->q($filter->clean($data['txn_id'])),
						$db->qn('transaction_params') . ' = ' . $db->q(json_encode($data)),
					)
				)
				->where($db->qn('id') . ' = ' . $db->q($donation->id));

			if ($status == 'COMPLETED')
			{
				$query->set($db->qn('completed') . ' = ' . $db->q($now->toSql()));
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
	 * @since   1.1.0
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
	 * Get Paypal's transaction page URL.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getPaypalUrl()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}
		else
		{
			return 'https://www.paypal.com/cgi-bin/webscr';
		}
	}

	/**
	 * Get merchant's PayPal email address.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getMerchantEmail()
	{
		$sandbox = $this->params->get('sandbox', 0);

		if ($sandbox)
		{
			return trim($this->params->get('sandbox_email', ''));
		}
		else
		{
			return trim($this->params->get('email', ''));
		}
	}

	/**
	 * Get currency from plugin's settings.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
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
	 * Get transaction description.
	 *
	 * @param   string  $campaignName  Campaign name.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getDescription($campaignName)
	{
		return JText::sprintf('COM_CMDONATION_TRANSACTION_NAME', $campaignName);
	}

	/**
	 * Get page style.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getPageStyle()
	{
		$pageStyle = $this->params->get('page_style', 'paypal');

		if ($pageStyle == 'custom')
		{
			$pageStyle = trim($this->params->get('custom_page_style'));
		}

		return $pageStyle;
	}

	/**
	 * Get logo's URL.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getLogoUrl()
	{
		$logoUrl = '';
		$logo = $this->params->get('logo');

		if ($logo == 'internal' && $this->params->get('logo_internal') != '')
		{
			$logoUrl = JURI::base() . $this->params->get('logo_internal');
		}
		elseif ($logo == 'external' && trim($this->params->get('logo_external')) != '')
		{
			$logoUrl = trim($this->params->get('logo_external'));
		}

		return $logoUrl;
	}

	/**
	 * Get header's URL.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function getHeaderUrl()
	{
		$headerUrl = '';
		$header = $this->params->get('header');

		if ($header == 'internal' && $this->params->get('header_internal') != '')
		{
			$headerUrl = JURI::base() . $this->params->get('header_internal');
		}
		elseif ($header == 'external' && trim($this->params->get('header_external')) != '')
		{
			$headerUrl = trim($this->params->get('header_external'));
		}

		return $headerUrl;
	}

	/**
	 * Generate site's URL for redirection.
	 *
	 * @param   string  $url  Page URL.
	 *
	 * @return  string
	 *
	 * @since   1.1.0
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
}
