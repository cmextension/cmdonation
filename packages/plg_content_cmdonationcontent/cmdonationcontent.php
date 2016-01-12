<?php
/**
 * @package    PlgContentCMDonationContent
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * CM Donation Content Plugin
 *
 * @since  1.0.0
 */
class PlgContentCMDonationContent extends JPlugin
{
	/**
	 * Path to layout folder.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	protected $layoutPath;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		$this->layoutPath = dirname(__FILE__) . '/layouts/';

		parent::__construct($subject, $config);
	}

	/**
	 * Search for CM Donation's tags and replace them with actual content.
	 *
	 * @param   string   $context  The context of the content being passed to the plugin.
	 * @param   mixed    &$row     An object with a "text" property
	 * @param   mixed    $params   Additional parameters. See {@see PlgContentContent()}.
	 * @param   integer  $page     Optional page number. Unused. Defaults to zero.
	 *
	 * @return  boolean	True on success.
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepare($context, &$row, $params, $page = 0)
	{
		require_once JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/cmdonation.php';

		$lang = JFactory::getLanguage();
		$lang->load('com_cmdonation', JPATH_SITE, null, false, true);
		$lang->load('com_cmdonation', JPATH_SITE . '/components/com_cmdonation/', null, false, true);

		$enableDonations		= $this->params->get('enable_donations', '1');
		$enableAmount			= $this->params->get('enable_amount', '1');
		$enableDonationForm		= $this->params->get('enable_donation_form', '1');
		$enableLatestDonations	= $this->params->get('enable_latest_donations', '1');
		$enableTopDonors		= $this->params->get('enable_top_donors', '1');
		$enableTopCountries		= $this->params->get('enable_top_countries', '1');

		// Should I run?
		if (!$enableDonations && !$enableAmount && !$enableDonationForm
			&& !$enableLatestDonations && !$enableTopDonors && !$enableTopCountries)
		{
			return;
		}
		else
		{
			// Load CSS.
			JFactory::getDocument()->addStyleSheet('components/com_cmdonation/assets/css/pure-min.css');
		}

		// Number of donations.
		if ($enableDonations)
		{
			$pattern = '/{donations:([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						$replace = $this->countDonations($campaignId);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}

		// Total amount donated.
		if ($enableAmount)
		{
			$pattern = '/{amount:([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						// Component settings.
						$params					= JComponentHelper::getParams('com_cmdonation');
						$currencySign			= $params->get('currency_sign');
						$currencySignPosition	= $params->get('currency_sign_position');
						$decimals				= $params->get('decimals');
						$decimalPoint			= $params->get('decimal_point');
						$thousandSeparator		= $params->get('thousand_separator');

						$replace = $this->countAmount($campaignId);
						$replace = CMDonationHelper::showDonationAmount(
							$replace, $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator
						);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}

		// Donation form.
		if ($enableDonationForm)
		{
			$pattern = '/{donation-form:([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						$replace = $this->buildDonationForm($campaignId);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}

		// Latest donations.
		if ($enableLatestDonations)
		{
			$pattern = '/{latest-donations:([0-9]*):([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						$limit = $match[2];
						$replace = $this->buildLatestDonationsTable($campaignId, $limit);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}

		// Top donors.
		if ($enableTopDonors)
		{
			$pattern = '/{top-donors:([0-9]*):([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						$limit = $match[2];
						$replace = $this->buildTopDonorsTable($campaignId, $limit);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}

		// Top countries.
		if ($enableTopCountries)
		{
			$pattern = '/{top-countries:([0-9]*):([0-9]*)}/';
			preg_match_all($pattern, $row->text, $matches, PREG_SET_ORDER);

			if (!empty($matches))
			{
				foreach ($matches as $match)
				{
					$campaignId = $match[1];
					$existence = CMDonationHelper::doesCampaignExist($campaignId);

					if (!$existence)
					{
						$replace = '';
					}
					else
					{
						$limit = $match[2];
						$replace = $this->buildTopCountryTable($campaignId, $limit);
					}

					$row->text = preg_replace($pattern, $replace, $row->text, 1);
				}
			}
		}
	}

	/**
	 * Get the URL of current page for redirecting if any error occurs.
	 *
	 * @return  string  Base64 URL.
	 *
	 * @since   1.0.0
	 */
	function getErrorReturnUrl()
	{
		$app	= JFactory::getApplication();
		$router	= $app->getRouter();
		$uri	= clone JUri::getInstance();
		$vars	= $router->parse($uri);
		unset($vars['lang']);

		if ($router->getMode() == JROUTER_MODE_SEF)
		{
			if (isset($vars['Itemid']))
			{
				$itemId	= $vars['Itemid'];
				$menu	= $app->getMenu();
				$item	= $menu->getItem($itemId);
				unset($vars['Itemid']);

				if (isset($item) && $vars == $item->query)
				{
					$url = 'index.php?Itemid=' . $itemId;
				}
				else
				{
					$url = 'index.php?' . JUri::buildQuery($vars) . '&Itemid=' . $itemId;
				}
			}
			else
			{
				$url = 'index.php?' . JUri::buildQuery($vars);
			}
		}
		else
		{
			$url = 'index.php?' . JUri::buildQuery($vars);
		}

		return base64_encode($url);
	}

	/**
	 * Count how many successful donations of campaign.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  mixed    Number of donations or false if failed.
	 *
	 * @since   1.0.0
	 */
	function countDonations($campaignId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'));
		$db->setQuery($query);
		$count = $db->loadResult();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return $count;
	}

	/**
	 * Count total amount donated of campaign.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  mixed    Total amount or false if failed.
	 *
	 * @since   1.0.0
	 */
	function countAmount($campaignId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(amount)')
			->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'));
		$db->setQuery($query);
		$amount = $db->loadResult();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return $amount;
	}

	/**
	 * Genarate donation form's HTML.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  string   HTML.
	 *
	 * @since   1.0.0
	 */
	function buildDonationForm($campaignId)
	{
		require_once JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/cmdonation.php';
		$postUrl				= 'index.php?option=com_cmdonation&task=donation.submit';
		$langAmount				= JText::_('COM_CMDONATION_AMOUNT');
		$langAnonymous			= JText::_('COM_CMDONATION_ANONYMOUS_CHECKBOX');
		$langDonate				= JText::_('COM_CMDONATION_DONATE');
		$langPaymentMethod		= JText::_('COM_CMDONATION_PAYMENT_METHOD');
		$langRecurringDonation	= JText::_('COM_CMDONATION_RECURRING_DONATION');
		$langRecurringCycle		= JText::_('COM_CMDONATION_RECURRING_CYCLE');
		$returnUrl				= $this->getErrorReturnUrl();
		$paymentMethods			= CMDonationHelper::getPaymentMethods();
		$htmlPaymentMethods		= '';
		$loneOption				= (count($paymentMethods) == 1) ? true : false;
		$params					= JComponentHelper::getParams('com_cmdonation');
		$hideLoneOption			= $params->get('hide_lone_payment_option', '0');
		$currencySign			= $params->get('currency_sign', '');
		$currencySignPosition	= $params->get('currency_sign_position', 'before');
		$anonymous				= $params->get('anonymous_donation', false, 'boolean');
		$recurring				= $params->get('recurring_donation', false, 'boolean');
		$recurringCycles		= $params->get('recurring_cycles', array(), 'array');

		@ob_start();

		if (file_exists($this->layoutPath . 'override/donation_form.php'))
		{
			include $this->layoutPath . 'override/donation_form.php';
		}
		else
		{
			include $this->layoutPath . 'donation_form.php';
		}

		$html = @ob_get_clean();

		return $html;
	}

	/**
	 * Genarate donation list's HTML.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 * @param   integer  $limit       Number of donations.
	 *
	 * @return  string   HTML.
	 *
	 * @since   1.0.0
	 */
	function buildLatestDonationsTable($campaignId, $limit = 10)
	{
		if ($limit <= 0)
		{
			$limit = 10;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('first_name, last_name, amount, anonymous, country_code, completed')
			->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'))
			->order('completed DESC');
		$db->setQuery($query, 0, $limit);
		$donations = $db->loadObjectList();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		// Component settings.
		$params					= JComponentHelper::getParams('com_cmdonation');
		$countryDisplay			= $params->get('donor_list_country', 'both');
		$donorNameDisplay		= $params->get('donor_name', 'both');
		$currencySign			= $params->get('currency_sign', '');
		$currencySignPosition	= $params->get('currency_sign_position', 'before');
		$decimals				= $params->get('decimals', '2');
		$decimalPoint			= $params->get('decimal_point', ',');
		$thousandSeparator		= $params->get('thousand_separator', '.');
		$dateColumn				= $params->get('date_column', '1');
		$rowNumberColumn		= $params->get('row_number_column', '1');
		$dateFormat				= $params->get('date_format', 'COM_CMDONATION_DATE_FORMAT_1');

		@ob_start();
		include $this->layoutPath . 'latest_donations.php';
		$html = @ob_get_clean();

		return $html;
	}

	/**
	 * Genarate top donor list's HTML.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 * @param   integer  $limit       Number of donations.
	 *
	 * @return  string   HTML.
	 *
	 * @since   1.0.0
	 */
	function buildTopDonorsTable($campaignId, $limit = 10)
	{
		if ($limit <= 0)
		{
			$limit = 10;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('first_name, last_name, email, amount, anonymous, country_code')
			->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('campaign_id') . ' = ' . $db->quote($campaignId))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'))
			->order('completed DESC');
		$db->setQuery($query);
		$donations = $db->loadObjectList();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$donors = array();

		if (!empty($donations))
		{
			foreach ($donations as $donation)
			{
				if (!isset($donors[$donation->email]) && !empty($donation->email))
				{
					$donor = new StdClass;
					$donor->first_name			= $donation->first_name;
					$donor->last_name			= $donation->last_name;
					$donor->anonymous			= $donation->anonymous;
					$donor->country_code		= $donation->country_code;
					$donor->donation_quantity	= 1;
					$donor->amount				= floatval($donation->amount);

					$donors[$donation->email] = $donor;
				}
				elseif (isset($donors[$donation->email]) && !empty($donation->email))
				{
					$donors[$donation->email]->donation_quantity += 1;
					$donors[$donation->email]->amount += floatval($donation->amount);
				}
			}

			if (!empty($donors))
			{
				usort(
					$donors,
					function($a, $b)
					{
						return ($a->amount < $b->amount) ? 1 : -1;
					}
				);

				// Component settings.
				$params					= JComponentHelper::getParams('com_cmdonation');
				$countryDisplay			= $params->get('donor_list_country', 'both');
				$donorNameDisplay		= $params->get('donor_name', 'both');
				$currencySign			= $params->get('currency_sign', '');
				$currencySignPosition	= $params->get('currency_sign_position', 'before');
				$decimals				= $params->get('decimals', '2');
				$decimalPoint			= $params->get('decimal_point', ',');
				$thousandSeparator		= $params->get('thousand_separator', '.');
				$rowNumberColumn		= $params->get('row_number_column', '1');
			}
		}

		@ob_start();
		include $this->layoutPath . 'top_donors.php';
		$html = @ob_get_clean();

		return $html;
	}

	/**
	 * Genarate top country's HTML.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 * @param   integer  $limit       Number of donations.
	 *
	 * @return  string   HTML.
	 *
	 * @since   1.0.0
	 */
	function buildTopCountryTable($campaignId, $limit = 10)
	{
		if ($limit <= 0)
		{
			$limit = 10;
		}

		// Component settings.
		$params					= JComponentHelper::getParams('com_cmdonation');
		$countryDisplay			= $params->get('country_list_country', 'both');
		$contributionColumn		= $params->get('contribution_column', '1');
		$lowestColumn			= $params->get('lowest_column', '1');
		$averageColumn			= $params->get('average_column', '1');
		$highestColumn			= $params->get('highest_column', '1');
		$currencySign			= $params->get('currency_sign', '');
		$currencySignPosition	= $params->get('currency_sign_position', 'before');
		$decimals				= $params->get('decimals', '2');
		$decimalPoint			= $params->get('decimal_point', ',');
		$thousandSeparator		= $params->get('thousand_separator', '.');
		$rowNumberColumn		= $params->get('row_number_column', '1');

		$result = CMDonationHelper::generateCountryStatistics($campaignId, $params);
		$countries = $result['countries'];

		@ob_start();
		include $this->layoutPath . 'top_countries.php';
		$html = @ob_get_clean();

		return $html;
	}
}
