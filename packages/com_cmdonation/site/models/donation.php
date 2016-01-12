<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Donation model.
 *
 * @since  1.0.0
 */
class CMDonationModelDonation extends JModelLegacy
{
	/**
	 * Save a new donation.
	 *
	 * @param   array  $data  Donation's data.
	 *
	 * @return  mixed  New donation's ID if successful, false if failed.
	 *
	 * @since   1.0.0
	 */
	public function saveNewDonation($data = array())
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->insert($db->quoteName('#__cmdonation_donations'));

		$query->columns(
			array(
				$db->quoteName('campaign_id'),
				$db->quoteName('anonymous'),
				$db->quoteName('amount'),
				$db->quoteName('payment_method_id'),
				$db->quoteName('status'),
				$db->quoteName('created'),
				$db->quoteName('recurring'),
				$db->quoteName('recurring_cycle'),
				$db->quoteName('first_donation_id'),
			)
		);

		$query->values(
			$db->quote($data['campaign_id']) . ', '
				. $db->quote($data['anonymous']) . ', '
				. $db->quote($data['amount']) . ', '
				. $db->quote($data['payment_method_id']) . ', '
				. $db->quote($data['status']) . ', '
				. $db->quote($data['created']) . ', '
				. $db->quote($data['recurring']) . ', '
				. $db->quote($data['recurring_cycle']) . ', '
				. $db->quote($data['first_donation_id'])
		);

		$db->setQuery($query);
		$db->execute();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$donationId = $db->insertid();

		return $donationId;
	}

	/**
	 * Get a donation by ID.
	 *
	 * @param   integer  $donationId  Donation ID.
	 *
	 * @return  object   Donation object.
	 *
	 * @since   1.0.0
	 */
	public function getDonationById($donationId = 0)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, campaign_id, first_name, last_name, email, country_code,
			anonymous, amount, payment_method_id, status, completed');
		$query->from('#__cmdonation_donations');
		$query->where('id = ' . (int) $donationId);
		$db->setQuery($query);
		$donation = $db->loadObject();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return $donation;
	}

	/**
	 * Update donation after receive payment gateway's callback.
	 *
	 * @param   array  $data  Data.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function updateCallback($data)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__cmdonation_donations'))
			->set(
				array(
					$db->quoteName('first_name') . ' = ' . $db->quote($data['first_name']),
					$db->quoteName('last_name') . ' = ' . $db->quote($data['last_name']),
					$db->quoteName('email') . ' = ' . $db->quote($data['email']),
					$db->quoteName('country_code') . ' = ' . $db->quote($data['country_code']),
					$db->quoteName('status') . ' = ' . $db->quote($data['status']),
					$db->quoteName('transaction_params') . ' = ' . $db->quote($data['transaction_params']),
					$db->quoteName('completed') . ' = ' . $db->quote($data['completed']),
					$db->quoteName('modified') . ' = ' . $db->quote($data['modified']),
					$db->quoteName('modified_by') . ' = ' . $db->quote($data['modified_by'])
				)
			)
			->where($db->quoteName('id') . ' = ' . $db->quote($data['donation_id']));
		$db->setQuery($query);
		$db->execute();
	}
}
