<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'donation.cancel' || document.formvalidator.isValid(document.id('donation-form'))) {
			Joomla.submitform(task, document.getElementById('donation-form'));
		}
	}
</script>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<form action="<?php echo JRoute::_('index.php?option=com_cmdonation&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="donation-form" class="form-validate pure-form pure-form-aligned">
			<fieldset>
				<div class="pure-g">
					<div class="pure-u-1-2">
						<div class="grid-inner">
							<legend><?php echo empty($this->item->id) ? JText::_('COM_CMDONATION_DONATION_NEW_DONATION', true) : JText::sprintf('COM_CMDONATION_DONATION_EDIT_DONATION', $this->item->id, true); ?></legend>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('campaign_id'); ?>
								<?php echo $this->form->getInput('campaign_id'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('amount'); ?>
								<?php echo $this->form->getInput('amount'); ?>
							</div>
						</div>
					</div>

					<div class="pure-u-1-2">
						<div class="grid-inner">
							<legend><?php echo JText::_('COM_CMDONATION_DONATION_DONOR'); ?></legend>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('first_name'); ?>
								<?php echo $this->form->getInput('first_name'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('last_name'); ?>
								<?php echo $this->form->getInput('last_name'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('email'); ?>
								<?php echo $this->form->getInput('email'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('country_code'); ?>
								<?php echo $this->form->getInput('country_code'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('anonymous'); ?>
								<?php echo $this->form->getInput('anonymous'); ?>
							</div>
						</div>
					</div>
				</div>

				<div class="pure-g">
					<div class="pure-u-1-2">
						<div class="grid-inner">
							<legend><?php echo JText::_('COM_CMDONATION_DONATION_TRANSACTION'); ?></legend>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('payment_method_id'); ?>
								<?php echo $this->form->getInput('payment_method_id'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('transaction_params'); ?>
								<?php echo $this->form->getInput('transaction_params'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('status'); ?>
								<?php echo $this->form->getInput('status'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('completed'); ?>
								<?php echo $this->form->getInput('completed'); ?>
							</div>
						</div>
					</div>

					<div class="pure-u-1-2">
						<div class="grid-inner">
							<legend><?php echo JText::_('COM_CMDONATION_PUBLISHING_INFO'); ?></legend>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('created_by'); ?>
								<?php echo $this->form->getInput('created_by'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('created'); ?>
								<?php echo $this->form->getInput('created'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('modified_by'); ?>
								<?php echo $this->form->getInput('modified_by'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('modified'); ?>
								<?php echo $this->form->getInput('modified'); ?>
							</div>
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('id'); ?>
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>
					</div>
				</div>
			</fieldset>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>
