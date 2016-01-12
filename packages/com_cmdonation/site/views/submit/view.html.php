<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View for submitting payment form to payment service.
 *
 * @since  1.0.0
 */
class CMDonationViewSubmit extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False on error, null otherwise.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();

		// Load payment form from payment plug-in.
		jimport('joomla.plugin.helper');
		JPluginHelper::importPlugin('cmdonation');
		$result = JFactory::getApplication()->triggerEvent('onCMPaymentNew',
			array($this->data->payment_method, $user, $this->data)
		);

		if (empty($result))
		{
			$message = JText::_('COM_CMDONATION_ERROR_EMPTY_SUBMISSION_FORM');
			JFactory::getApplication()->redirect($this->returnUrl, $message, 'error');
		}

		$donationForm = '';

		foreach ($result as $r)
		{
			if ($r === false)
			{
				continue;
			}

			$donationForm = $r;
		}

		$this->donationForm = $donationForm;
		$title = JText::_('COM_CMDONATION_PLEASE_WAIT');
		$this->document->setTitle($title);
		parent::display($tpl);
	}
}
