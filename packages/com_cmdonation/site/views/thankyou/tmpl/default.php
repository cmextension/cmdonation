<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
?>
<?php if ($this->params->get('show_page_heading')): ?>
<div class="page-header">
	<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
</div>
<?php endif; ?>
<?php
if ($this->layout == 'complete')
{
	echo $this->campaign->complete_message;
}
elseif ($this->layout == 'cancel')
{
	echo $this->campaign->cancel_message;
}
