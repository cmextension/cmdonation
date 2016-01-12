<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Campaign model.
 *
 * @since  1.0.0
 */
class CMDonationModelCampaign extends JModelLegacy
{
	/**
	 * Get a campaign by ID.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  object   Campaign object.
	 *
	 * @since   1.0.0
	 */
	public static function getCampaignById($campaignId = 0)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name, complete_message, cancel_message');
		$query->from($db->quoteName('#__cmdonation_campaigns'));
		$query->where($db->quoteName('id') . ' = ' . (int) $campaignId);
		$db->setQuery($query);
		$campaign = $db->loadObject();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return $campaign;
	}
}
