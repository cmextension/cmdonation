<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Field display transaction info in donation edit form.
 *
 * @since  1.0.0
 */
class JFormFieldCmtransaction extends JFormField
{
	/**
	 * The form field type.
	 */
	public $type = 'CMTransaction';

	/**
	 * Method to get the field input for a list of content types.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.0.0
	 */
	protected function getInput()
	{
		$html = '';
		$infoArray = json_decode($this->value);

		if (!empty($infoArray))
		{
			$html .= '<div>';

			foreach ($infoArray as $key => $value)
			{
				$html .= '<strong>' . $key . '</strong>: ' . $value . '<br />';
			}

			$html .= '</div>';
		}

		return $html;
	}
}
