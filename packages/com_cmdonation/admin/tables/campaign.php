<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Campaign table class.
 *
 * @since  1.0.0
 */
class CMDonationTableCampaign extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  Database driver object.
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__cmdonation_campaigns', 'id', $db);
	}

	/**
	 * Method to store a row in the database.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.0.0
	 */
	public function store($updateNulls = false)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		if ($this->id)
		{
			// Existing item
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		}
		else
		{
			// New item. Created and created_by field can be set by the user,
			// so we don't touch either of these if they are set.
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}

			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		return parent::store($updateNulls);
	}

	/**
	 * Validation and filtering
	 *
	 * @return  boolean  True if satisfactory
	 *
	 * @since   1.0.0
	 */
	public function check()
	{
		// Remove whitespace at the beginning and the end of name.
		$this->name = trim($this->name);

		// Check for valid name
		if ($this->name == '')
		{
			$this->setError(JText::_('COM_CMDONATION_CAMPAIGN_ERROR_EMPTY_NAME'));

			return false;
		}

		// Check for existing name
		$query = 'SELECT id FROM #__cmdonation_campaigns WHERE name = ' . $this->_db->quote($this->name);
		$this->_db->setQuery($query);

		$xid = (int) $this->_db->loadResult();

		if ($xid && $xid != (int) $this->id)
		{
			$this->setError(JText::_('COM_CMDONATION_CAMPAIGN_ERROR_DUPLICATED'));

			return false;
		}

		return true;
	}
}
