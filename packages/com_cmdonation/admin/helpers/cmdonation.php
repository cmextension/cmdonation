<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Statistics controller class.
 *
 * @since  1.0.0
 */
class CMDonationHelper
{
	/**
	 * Build submenu.
	 *
	 * @param   string  $active  Active view's name.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function addSubmenu($active = 'dashboard')
	{
		$items = array(
			'dashboard' => array(
				'label' => '<i class="fa fa-dashboard"> ' . JText::_('COM_CMDONATION_SUBMENU_DASHBOARD') . '</i>',
				'link' => 'index.php?option=com_cmdonation&view=dashboard'
			),
			'campaigns' => array(
				'label' => '<i class="fa fa-flag"> ' . JText::_('COM_CMDONATION_SUBMENU_CAMPAIGNS') . '</i>',
				'link' => 'index.php?option=com_cmdonation&view=campaigns'
			),
			'donations' => array(
				'label' => '<i class="fa fa-heart"> ' . JText::_('COM_CMDONATION_SUBMENU_DONATIONS') . '</i>',
				'link' => 'index.php?option=com_cmdonation&view=donations'
			),
			'statistics' => array(
				'label' => '<i class="fa fa-bar-chart-o"> ' . JText::_('COM_CMDONATION_SUBMENU_STATISTICS') . '</i>',
				'link' => 'index.php?option=com_cmdonation&view=statistics'
			),
		);

		$html = '<div class="sub-menu">';
		$html .= '<div class="pure-menu pure-menu-horizontal">';
		$html .= '<ul class="pure-menu-list">';

		foreach ($items as $view => $item)
		{
			$classes = ($view == $active) ? 'pure-menu-item pure-menu-selected' : 'pure-menu-item';

			$html .= '<li class="' . $classes . '"><a href="' . $item['link'] . '" class="pure-menu-link">' . $item['label'] . '</a></li>';
		}

		$html .= '</ul>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $categoryId  Category ID.
	 *
	 * @return  object
	 *
	 * @since   1.0.0
	 */
	public static function getActions($categoryId = 0)
	{
		$user = JFactory::getUser();
		$result = new JObject;

		$assetName = 'com_cmdonation';
		$level = 'component';

		$actions = JAccess::getActions('com_cmdonation', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Check if a campaign exist.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public static function doesCampaignExist($campaignId = 0)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__cmdonation_campaigns'))
			->where($db->quoteName('id') . ' = ' . (int) $campaignId);

		$db->setQuery($query);
		$result = $db->loadResult();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return ($result > 0) ? true : false;
	}

	/**
	 * Get enabled payment methods.
	 *
	 * @return  array Array contains payment method objects.
	 *
	 * @since   1.0.0
	 */
	public static function getPaymentMethods()
	{
		$paymentMethods = array();

		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('cmdonation');
		$plugins = JFactory::getApplication()->triggerEvent('onCMPaymentGetIdentity');

		foreach ($plugins as $plugin)
		{
			$paymentMethods[$plugin->name] = $plugin;
		}

		return $paymentMethods;
	}

	/**
	 * Get payment method by ID.
	 *
	 * @param   integer  $paymentMethodName  Payment method's name.
	 *
	 * @return  object   Payment method object.
	 *
	 * @since   1.0.0
	 */
	public static function getPaymentMethodById($paymentMethodName)
	{
		$dummy = new StdClass;
		$paymentMethods = self::getPaymentMethods();

		foreach ($paymentMethods as $name => $method)
		{
			if ($name == $paymentMethodName)
			{
				return $method;
			}
		}

		return $dummy;
	}

	/**
	 * Display payment method's name.
	 *
	 * @param   integer  $paymentMethodName  Payment method's name'.
	 * @param   array    $paymentMethods     Available payment methods.
	 *
	 * @return  string   Payment method's name'.
	 *
	 * @since   1.0.0
	 */
	public static function displayPaymentMethodName($paymentMethodName, $paymentMethods = array())
	{
		if ($paymentMethodName != '')
		{
			if (empty($paymentMethods))
			{
				$paymentMethods = self::getPaymentMethods();
			}

			if (array_key_exists($paymentMethodName, $paymentMethods))
			{
				return JText::_($paymentMethods[$paymentMethodName]->title);
			}
			else
			{
				return $paymentMethodName;
			}
		}

		return '';
	}

	/**
	 * Show country name.
	 *
	 * @param   string  $countryCode  Country code.
	 *
	 * @return  string  Country name.
	 *
	 * @since   1.0.0
	 */
	public static function showCountryName($countryCode)
	{
		if ($countryCode != '')
		{
			include JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/countries.php';

			if (array_key_exists($countryCode, $countryList))
			{
				$countryName = JText::_($countryList[$countryCode]);
			}

			return $countryName;
		}

		return '';
	}

	/**
	 * Show country flag.
	 *
	 * @param   string  $countryCode  Country code.
	 *
	 * @return  string  HTML for country's flag.
	 *
	 * @since   1.0.0
	 */
	public static function showCountryFlag($countryCode)
	{
		$countryName = self::showCountryName($countryCode);

		if ($countryName != '')
		{
			include JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/countries.php';

			if (JFactory::getApplication()->isAdmin())
			{
				$flagFolder = '../components/com_cmdonation/assets/img/flags/';
			}
			else
			{
				$flagFolder = 'components/com_cmdonation/assets/img/flags/';
			}

			$countryName = htmlspecialchars($countryName);
			$countryFlag = '<img src="' . $flagFolder . strtolower($countryCode) . '.png" '
							. 'alt="' . $countryName . '" title="' . $countryName . '"/>';

			return $countryFlag;
		}

		return '';
	}

	/**
	 * Display donor name.
	 *
	 * @param   object  $donation          Donation object.
	 * @param   string  $donorNameDisplay  Which part of name is displayed, first/last or full name.
	 *
	 * @return  string  Donor's name.
	 *
	 * @since   1.0.0
	 */
	public static function showDonorName($donation, $donorNameDisplay)
	{
		if ($donation->anonymous)
		{
			return JText::_('COM_CMDONATION_ANONYMOUS');
		}
		else
		{
			switch ($donorNameDisplay)
			{
				case 'first':
					return $donation->first_name;
					break;
				case 'last':
					return $donation->last_name;
					break;
				default:
					return $donation->first_name . ' ' . $donation->last_name;
					break;
			}
		}

		return '';
	}

	/**
	 * Display donation amount.
	 * Use <span> to fix isse in front-end with US dollar, "$" is understood as PHP prefixed.
	 *
	 * @param   float    $amount                Amount.
	 * @param   string   $currencySign          Currency sign.
	 * @param   string   $currencySignPosition  Currency sign's position, before or after.
	 * @param   integer  $decimals              Number of decimals.
	 * @param   string   $decimalPoint          Decimal point.
	 * @param   string   $thousandSeparator     Thousand separator.
	 * @param   boolean  $useSpan               True if use span to wrap currency sign.
	 * 
	 * @return  string   HTML for formatted amount.
	 *
	 * since    1.0.0
	 */
	public static function showDonationAmount($amount, $currencySign, $currencySignPosition = 'before',
		$decimals = '2', $decimalPoint = '.', $thousandSeparator = ',', $useSpan = true)
	{
		$amount = floatval($amount);
		$donationAmount = number_format($amount, $decimals, $decimalPoint, $thousandSeparator);

		if ($currencySign == '$' && $useSpan)
		{
			$currencySign = '<span>' . $currencySign . '</span>';
		}

		return ($currencySignPosition == 'after') ? $donationAmount . $currencySign : $currencySign . $donationAmount;
	}

	/**
	 * Generate country's statistics of a campaign.
	 * It also returns total, lowest, average, highest amount donated of campaign.
	 * This function is used in both front-end and back-end.
	 *
	 * @param   integer  $campaignId  Campaign ID
	 * @param   object   $params      Component's params
	 * @param   boolean  $backend     False if used in back-end, true for front-end
	 *
	 * @return  array    $result      Array contains array countries and donated amounts.
	 *
	 * since    1.0.0
	 */
	public static function generateCountryStatistics($campaignId, $params = null, $backend = false)
	{
		if ($params == null)
		{
			$params = JComponentHelper::getParams('com_cmdonation');
		}

		// Array of countries, includes values of contribution, lowest donation, etc...
		$countries = array();

		// Amount values.
		$totalAmount = 0;

		if ($backend)
		{
			$lowestAmount = $highestAmount = null;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('amount, country_code');

		if ($backend)
		{
			$query->select('first_name, last_name, email, payment_method_id, completed');
		}

		$query->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'))
			->order('id DESC');
		$db->setQuery($query);
		$donations = $db->loadObjectList();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		if (!empty($donations))
		{
			include JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/countries.php';

			foreach ($donations as $key => $donation)
			{
				$amount = floatval($donation->amount);
				$totalAmount += $amount;

				if ($backend)
				{
					if ($lowestAmount == null || $lowestAmount > $amount)
					{
						$lowestAmount = $amount;
					}

					if ($highestAmount == null || $highestAmount < $amount)
					{
						$highestAmount = $amount;
					}
				}

				$countryDisplay = $params->get('statistics_country');

				if (!$backend || ($backend && $countryDisplay != 'hide'))
				{
					if (!isset($countries[$donation->country_code]) && !empty($donation->country_code))
					{
						if (array_key_exists($donation->country_code, $countryList))
						{
							$countryName = JText::_($countryList[$donation->country_code]);

							if ($backend)
							{
								$flagFolder = '../components/com_cmdonation/assets/img/flags/';
							}
							else
							{
								$flagFolder = 'components/com_cmdonation/assets/img/flags/';
							}

							$countryFlag = '<img src="' . $flagFolder . strtolower($donation->country_code) . '.png" '
									. 'alt="' . htmlspecialchars($countryName) . '" title="' . htmlspecialchars($countryName) . '"/>';
						}
						else
						{
							$countryName = $countryFlag = '';
						}

						$country = new StdClass;
						$country->country_code		= $donation->country_code;
						$country->country_name		= $countryName;
						$country->amount			= $amount;
						$country->donation_quantity	= 1;
						$country->lowest			= $amount;
						$country->highest			= $amount;
						$country->country_flag		= $countryFlag;

						$countries[$donation->country_code] = $country;

						if ($backend && $countryDisplay != 'hide')
						{
							$donations[$key]->country_flag = $country->country_flag;
							$donations[$key]->country_name = $country->country_name;
						}
					}
					elseif(isset($countries[$donation->country_code]) && !empty($donation->country_code))
					{
						$countries[$donation->country_code]->donation_quantity += 1;
						$countries[$donation->country_code]->amount += $amount;

						if ($countries[$donation->country_code]->lowest > $amount)
						{
							$countries[$donation->country_code]->lowest = $amount;
						}

						if ($countries[$donation->country_code]->highest < $amount)
						{
							$countries[$donation->country_code]->highest = $amount;
						}

						if ($backend && $countryDisplay != 'hide')
						{
							$donations[$key]->country_flag = $countries[$donation->country_code]->country_flag;
							$donations[$key]->country_name = $countries[$donation->country_code]->country_name;
						}
					}
					else
					{
						$donations[$key]->country_code		= $donation->country_code;
						$donations[$key]->country_name		= '';
						$donations[$key]->country_flag		= '';
					}
				}
			}

			if (!empty($countries))
			{
				$contributionDecimals = $params->get('contribution_decimals');

				if ($contributionDecimals < 0)
				{
					$contributionDecimals = 0;
				}

				foreach ($countries as $key => $country)
				{
					$countries[$key]->contribution = number_format($country->amount * 100 / $totalAmount, $contributionDecimals);
					$countries[$key]->average = $country->amount / $country->donation_quantity;
				}

				usort(
					$countries, function($a, $b)
					{
						return ($a->amount < $b->amount) ? 1 : -1;
					}
				);
			}
		}

		$result = array(
			'countries' => $countries
		);

		if ($backend)
		{
			$averageAmount = (count($donations) > 0) ? $totalAmount / count($donations) : 0;

			$resultBackend = array(
				'donations'	=> $donations,
				'total'		=> $totalAmount,
				'lowest'	=> $lowestAmount,
				'average'	=> $averageAmount,
				'highest'	=> $highestAmount
			);

			$result = array_merge($result, $resultBackend);
		}

		return $result;
	}

	/**
	 * Generate campaign's statistics.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 * @param   object   $params      Component's params.
	 *
	 * @return  array    Array of statistics.
	 *
	 * @since   1.0.0
	 */
	public static function generateStatistics($campaignId, $params = null)
	{
		if ($params == null)
		{
			$params = JComponentHelper::getParams('com_cmdonation');
		}

		// Array of results in HTML
		$stats = array(
			'number_of_donations'	=> '',
			'total'					=> '',
			'lowest'				=> '',
			'average'				=> '',
			'highest'				=> '',
			'countries'				=> ''
		);

		$currencySign			= $params->get('currency_sign');
		$currencySignPosition	= $params->get('currency_sign_position');
		$decimals				= $params->get('decimals');
		$decimalPoint			= $params->get('decimal_point');
		$thousandSeparator		= $params->get('thousand_separator');

		$result = self::generateCountryStatistics($campaignId, $params, true);

		$stats['number_of_donations'] = count($result['donations']);

		$stats['total'] = self::showDonationAmount(
			$result['total'], $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator
		);

		$stats['lowest'] = self::showDonationAmount(
			$result['lowest'], $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator
		);

		$stats['average'] = self::showDonationAmount(
			$result['average'], $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator
		);

		$stats['highest'] = self::showDonationAmount(
			$result['highest'], $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator
		);

		$stats['countries'] = $result['countries'];
		$stats['donations'] = $result['donations'];

		// Assign param for later use.
		$stats['params'] = $params;

		return $stats;
	}

	/**
	 * Get campaigns and their donated amount.
	 *
	 * @return  mixed  Array of campaigns if succeed, false if failure.
	 *
	 * @since   1.0.0
	 */
	public static function getCampaignStats()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id, name')
			->from($db->quoteName('#__cmdonation_campaigns'))
			->order($db->quoteName('id') . ' DESC');

		$db->setQuery($query);
		$campaigns = $db->loadObjectList('id');

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		if (!empty($campaigns))
		{
			foreach ($campaigns as &$campaign)
			{
				$query->clear()
					->select('SUM(amount)')
					->from($db->quoteName('#__cmdonation_donations'))
					->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaign->id))
					->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'));

				$db->setQuery($query);
				$amount = $db->loadResult();

				// Check for errors.
				if (count($errors = $db->get('Errors')))
				{
					JError::raiseError(500, implode("\n", $errors));

					return false;
				}

				$campaign->amount = $amount;
			}
		}

		return $campaigns;
	}
}
