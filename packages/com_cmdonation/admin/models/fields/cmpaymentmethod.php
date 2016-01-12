<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Field for selecting country in donation edit form.
 *
 * @since  1.0.0
 */
class JFormFieldCmpaymentmethod extends JFormField
{
	/**
	 * The form field type.
	 */
	public $type = 'CMPaymentMethod';

	/**
	 * Method to get the field input for a list of content types.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.0.0
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$required = $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		$input = '<input type="text" name="' . $this->name . '" id="' . $this->id . '" value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $required . '/>';

		$instruction = '<p></p><p>' . JText::_('COM_CMDONATION_PAYMENT_METHOD_INSTRUCTION_ENTER');
		$instruction .= '<ul>';

		$paymentMethods = CMDonationHelper::getPaymentMethods();

		if (!empty($paymentMethods))
		{
			foreach ($paymentMethods as $method)
			{
				$instruction .= '<li>' . JText::sprintf('COM_CMDONATION_PAYMENT_METHOD_INSTRUCTION_FOR', $method->name, $method->title) . '</li>';
			}
		}

		$instruction .= '<li>' . JText::_('COM_CMDONATION_PAYMENT_METHOD_INSTRUCTION_CUSTOM') . '</li>';
		$instruction .= '</ul></p>';

		return $input . $instruction;
	}
}
