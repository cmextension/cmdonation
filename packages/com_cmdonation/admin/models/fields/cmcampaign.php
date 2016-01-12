<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Field for selecting campaign in donation edit form.
 *
 * @since  1.0.0
 */
class JFormFieldCmcampaign extends JFormField
{
	/**
	 * The form field type.
	 */
	public $type = 'CMCampaign';

	/**
	 * Method to get the field input for a list of content types.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.0.0
	 */
	protected function getInput()
	{
		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->element['readonly'] == 'true' || (string) $this->element['disabled'] == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->required ? ' required="required" aria-required="true"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true')
		{
			$html[] = JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
		}
		// Create a regular list.
		else
		{
			$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}

	/**
	 * Method to get a list of content types
	 *
	 * @return  mixed  Array of the field option objects or false if error occurs.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('COM_CMDONATION_SELECT_CAMPAIGN_OPTION'), 'value', 'text');

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id AS value, name AS text');
		$query->from($db->quoteName('#__cmdonation_campaigns'));
		$query->order($db->quoteName('name'), 'ASC');

		$db->setQuery($query);
		$categories = $db->loadObjectList('value');

		if ($db->getErrorNum())
		{
			$this->setError($db->getErrorMsg());

			return false;
		}

		foreach ($categories as $option)
		{
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_('select.option', $option->value, $option->text, 'value', 'text');

			// Add the option object to the result set.
			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
