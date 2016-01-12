<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Statistics controller class.
 *
 * @since  1.0.0
 */
class CMDonationControllerStatistics extends JControllerForm
{
	/**
	 * Function to export donor list to CSV.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function export_donors()
	{
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_cmdonation');
		$campaignId = $app->input->get('campaign', 0, 'integer');

		// Make sure campaign exists.
		$campaign = JModelAdmin::getInstance('Campaign', 'CMDonationModel')->getItem($campaignId);

		if (empty($campaign))
		{
			$app->enqueueMessage(JText::_('COM_CMDONATION_CAMPAIGN_NOT_FOUND'), 'error');
			$app->redirect(JRoute::_('index.php?option=com_cmdonation'));
		}

		// Get campaign's donations.
		$donations = JModelAdmin::getInstance('Donations', 'CMDonationModel')->getDonationsForCSV($campaignId);

		$data = array(
			array(
				JText::_('COM_CMDONATION_DONATION_FIRST_NAME_LABEL'),
				JText::_('COM_CMDONATION_DONATION_LAST_NAME_LABEL'),
				JText::_('COM_CMDONATION_DONATION_EMAIL_LABEL'),
				JText::_('COM_CMDONATION_DONATION_COUNTRY_LABEL'),
				JText::_('COM_CMDONATION_DONATION_AMOUNT_LABEL'),
				JText::_('COM_CMDONATION_DONATION_COMPLETED_LABEL'),
				JText::_('COM_CMDONATION_DONATION_PAYMENT_METHOD_LABEL'),
			)
		);

		if (!empty($donations))
		{
			include JPATH_ROOT . '/administrator/components/com_cmdonation/helpers/countries.php';

			$currencySign			= $params->get('currency_sign');
			$currencySignPosition	= $params->get('currency_sign_position');
			$decimals				= $params->get('decimals');
			$decimalPoint			= $params->get('decimal_point');
			$thousandSeparator		= $params->get('thousand_separator');

			foreach ($donations as $donation)
			{
				if (array_key_exists($donation->country_code, $countryList))
				{
					$countryName = JText::_($countryList[$donation->country_code]);
				}
				else
				{
					$countryName = '';
				}

				$amount = CMDonationHelper::showDonationAmount(
					$donation->amount, $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator, false
				);

				$paymentMethod = CMDonationHelper::displayPaymentMethodName($donation->payment_method_id);

				$data[] = array(
					$donation->first_name,
					$donation->last_name,
					$donation->email,
					$countryName,
					$amount,
					$donation->completed,
					$paymentMethod
				);
			}
		}

		$delimiter	= $params->get('csv_delimiter_character', ',');
		$enclosure	= $params->get('csv_enclosure_character', 'double');

		if ($enclosure == 'double')
		{
			$enclosure = '"';
		}
		else
		{
			$enclosure = "'";
		}

		$filename = JApplication::stringURLSafe($campaign->name);

		if ($filename == '')
		{
			$filename = JFactory::getDate()->format("Y-m-d-H-i-s");
		}

		$filename .= '.csv';

		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename=$filename");
		header("Pragma: no-cache");
		header("Expires: 0");

		$output = fopen("php://output", "w");

		foreach ($data as $row)
		{
			fputcsv($output, $row, $delimiter, $enclosure);
		}

		fclose($output);

		JFactory::getApplication()->close();
	}
}
