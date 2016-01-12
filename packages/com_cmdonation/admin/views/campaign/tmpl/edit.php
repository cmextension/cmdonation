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
	Joomla.submitbutton = function(task)
	{
		if(task == 'campaign.cancel' || document.formvalidator.isValid(document.id('campaign-form')))
		{
			Joomla.submitform(task, document.getElementById('campaign-form'));
		}
	}
</script>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<form action="<?php echo JRoute::_('index.php?option=com_cmdonation&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="campaign-form" class="form-validate pure-form pure-form-aligned">
			<fieldset>
				<div class="pure-control-group">
					<?php echo $this->form->getLabel('name'); ?>
					<?php echo $this->form->getInput('name'); ?>
				</div>

				<div class="pure-g">
					<div class="pure-u-1-2">
						<div class="grid-inner">
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('complete_message'); ?>
								<div class="pure-clearfix"></div>
								<?php echo $this->form->getInput('complete_message'); ?>
							</div>
						</div>
					</div>
					<div class="pure-u-1-2">
						<div class="grid-inner">
							<div class="pure-control-group">
								<?php echo $this->form->getLabel('cancel_message'); ?>
								<div class="pure-clearfix"></div>
								<?php echo $this->form->getInput('cancel_message'); ?>
							</div>
						</div>

					</div>
				</div>

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
			</fieldset>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</form>
	</div>
</div>
