<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$controller	= JControllerLegacy::getInstance('CMDonation');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
