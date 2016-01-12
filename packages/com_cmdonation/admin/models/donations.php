<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Model for list of donations.
 *
 * @since  1.0.0
 */
class CMDonationModelDonations extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'campaign_id', 'a.campaign_id',
				'first_name', 'a.first_name',
				'last_name', 'a.last_name',
				'email', 'a.email',
				'country_code', 'a.country_code',
				'amount', 'a.amount',
				'anonymous', 'a.anonymous',
				'payment_method_id', 'a.payment_method_id',
				'status', 'a.status',
				'completed', 'a.completed',
				'created', 'a.created'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status');
		$this->setState('filter.status', $status);

		$campaign = $this->getUserStateFromRequest($this->context . '.filter.campaign', 'filter_campaign');
		$this->setState('filter.campaign', $campaign);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_cmdonation');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.id', 'desc');
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.status');
		$id .= ':' . $this->getState('filter.campaign');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$user	= JFactory::getUser();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.campaign_id, a.first_name, a.last_name, a.email, a.country_code, a.anonymous, a.amount, ' .
				'a.payment_method_id, a.status, a.completed, a.created, a.checked_out, a.checked_out_time'
			)
		);

		$query->from($db->quoteName('#__cmdonation_donations') . ' AS a');

		// Filter by search donor's info.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . $db->escape($search, true) . '%');
			$query->where('(a.first_name LIKE ' . $search . ') || ' .
				'(a.last_name LIKE ' . $search . ') || ' .
				'(a.email LIKE ' . $search . ')');
		}

		// Filter by status.
		$status = $this->getState('filter.status');

		if ($status == 'INCOMPLETE' || $status == 'COMPLETED' || $status == 'REFUNDED')
		{
			$query->where('a.status = ' . $db->quote($status));
		}

		// Filter by campaign.
		$campaignId = $this->getState('filter.campaign');
		$campaignId = (int) $campaignId;

		if ($campaignId > 0)
		{
			$query->where('a.campaign_id = ' . $db->quote($campaignId));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a CMPagination object for the data set.
	 *
	 * @return  CMPagination  A CMPagination object for the data set.
	 *
	 * @since   1.0.0
	 */
	public function getPagination()
	{
		require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/cmpagination.php';

		// Get a storage key.
		$store = $this->getStoreId('getPagination');

		// Try to load the data from internal storage.
		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		// Create the pagination object.
		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new CMPagination($this->getTotal(), $this->getStart(), $limit);

		// Add the object to the internal cache.
		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	/**
	 * Get donations for CSV export.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 *
	 * @return  mixed    Array of donation objects or false if error occurs.
	 *
	 * @since   1.0.0
	 */
	public function getDonationsForCSV($campaignId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('first_name, last_name, email, country_code, amount, payment_method_id, completed');
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

		return $donations;
	}

	/**
	 * Get latest donations.
	 *
	 * @param   integer  $campaignId  Campaign ID.
	 * @param   integer  $limit       Number of donations returned.
	 *
	 * @return  mixed    Array of donation objects or false if error occurs.
	 *
	 * @since   1.0.0
	 */
	public function getLatestDonations($campaignId = 0, $limit = 0)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__cmdonation_donations'))
			->where($db->quoteName('status') . ' = ' . $db->quote('COMPLETED'))
			->order('completed DESC');

		if ((int) $campaignId > 0)
		{
			$query->where($db->quoteName('campaign_id') . ' = ' . $db->quote((int) $campaignId));
		}

		if ((int) $limit > 0)
		{
			$db->setQuery($query, 0, (int) $limit);
		}
		else
		{
			$db->setQuery($query);
		}

		$donations = $db->loadObjectList();

		// Check for errors.
		if (count($errors = $db->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		return $donations;
	}
}
