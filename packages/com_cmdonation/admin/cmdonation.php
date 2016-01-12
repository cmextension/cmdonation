<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_cmdonation'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/cmdonation.php';

// Load CM Donation component's front-end language files.
$lang = JFactory::getLanguage();
$lang->load('com_cmdonation', JPATH_SITE, null, false, true);
$lang->load('com_cmdonation', JPATH_SITE . '/components/com_cmdonation/', null, false, true);

$controller = JControllerLegacy::getInstance('CMDonation');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
