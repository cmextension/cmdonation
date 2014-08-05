<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2012-2014 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$controller	= JControllerLegacy::getInstance('CMDonation');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
