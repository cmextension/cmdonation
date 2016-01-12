<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Donations controller class.
 *
 * @since  1.0.0
 */
class CMDonationControllerDonations extends JControllerAdmin
{
	protected $text_prefix = 'COM_CMDONATION_DONATION';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Donation', $prefix = 'CMDonationModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
