<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Model admin for campaign.
 *
 * @since  1.0.0
 */
class CMDonationModelCampaign extends JModelAdmin
{
	/**
	 * The prefix to use with controller messages.
	 */
	protected $text_prefix = 'COM_CMDONATION_CAMPAIGN';

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   1.0.0
	 */
	public function getTable($name = 'Campaign', $prefix = 'CMDonationTable', $config = array())
	{
		return JTable::getInstance($name, $prefix, $config);
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_cmdonation.campaign', 'campaign', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_cmdonation.edit.campaign.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   1.0.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Alter the name for save as copy.
		if ($app->input->get('task') == 'save2copy')
		{
			$data['name'] = $this->cmGenerateNewTitle($data['name']);
		}

		$return = parent::save($data);

		return $return;
	}

	/**
	 * Method to change the name.
	 *
	 * @param   string  $name  The name.
	 *
	 * @return  array   Contains the modified name.
	 *
	 * @since   1.0.0
	 */
	protected function cmGenerateNewTitle($name)
	{
		// Alter the name.
		$table = $this->getTable();

		while ($table->load(array('name' => $name)))
		{
			if ($name == $table->name)
			{
				$name = JString::increment($name);
			}
		}

		return $name;
	}
}
