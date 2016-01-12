<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View class for a list of campaigns.
 *
 * @since  1.0.0
 */
class CMDonationViewCampaigns extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->state		= $this->get('State');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->submenu = CMDonationHelper::addSubmenu('campaigns');
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= CMDonationHelper::getActions();

		JToolbarHelper::title(JText::_('COM_CMDONATION_MANAGER_CAMPAIGNS'), 'campaign icon-flag-3');

		if ($canDo->get('core.create'))
		{
			JToolbarHelper::addNew('campaign.add');
		}

		if ($canDo->get('core.edit'))
		{
			JToolbarHelper::editList('campaign.edit');
		}

		if ($canDo->get('core.edit.state'))
		{
			JToolbarHelper::checkin('campaigns.checkin');
		}

		if ($canDo->get('core.delete'))
		{
			JToolBarHelper::deleteList(JText::_('COM_CMDONATION_WARNING_DELETE_ITEMS'), 'campaigns.delete');
		}
	}

	/**
	 * Returns an array of fields the table can be sorted by.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.name' => JText::_('COM_CMDONATION_NAME'),
			'a.id' => JText::_('JGRID_HEADING_ID')
		);
	}
}
