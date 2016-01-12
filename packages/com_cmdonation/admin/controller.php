<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Class for CMDonation controller.
 *
 * @since  1.0.0
 */
class CMDonationController extends JControllerLegacy
{
	// Default view.
	protected $default_view = 'dashboard';

	/**
	 * Method to display the view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  ConfigController  This object to support chaining.
	 *
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$jinput = JFactory::getApplication()->input;
		$view	= $jinput->get('view', 'listings');
		$layout	= $jinput->get('layout', 'default');
		$id		= $jinput->getInt('id');

		// Check for campaign edit form.
		if ($view == 'campaign' && $layout == 'edit' && !$this->checkEditId('com_cmdonation.edit.campaign', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_cmdonation&view=campaigns', false));

			return false;
		}

		// Check for donation edit form.
		if ($view == 'donation' && $layout == 'edit' && !$this->checkEditId('com_cmdonation.edit.donation', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_cmdonation&view=donations', false));

			return false;
		}

		// Load CSS.
		$doc = JFactory::getDocument();
		$doc->addStyleSheet('../components/com_cmdonation/assets/css/pure-min.css');
		$doc->addStyleSheet('../components/com_cmdonation/assets/css/font-awesome.min.css');
		$doc->addStyleSheet('components/com_cmdonation/assets/css/style.css');

		if (version_compare(JVERSION, '3.0.0', 'lt'))
		{
			// Special style for Joomla! 2.5.x only.
			$doc->addStyleDeclaration('.cmdonation .content-container { padding: 1em; background-color: #FFFFFF; border: 1px solid #CCCCCC; }');
		}

		parent::display();

		return $this;
	}
}
