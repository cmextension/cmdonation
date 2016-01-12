<?php
/**
 * @package    PlgContentCMDonationContent
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Add JavaScript to show and hide recurring cycle selection.
if ($recurring && count($recurringCycles) > 1)
{
	$script = <<<JS
window.onload = function() {
	var cycles = document.getElementById('recurringCycles');

	var recurring = document.getElementById('recurring');

	showCycles(recurring.checked);

	recurring.onchange = function() {
		showCycles(recurring.checked);
	};

	function showCycles(checked) {
		if (checked) {
			cycles.style.display = '';
		}
		else {
			cycles.style.display = 'none';
		}
	}
}
JS;

	JFactory::getDocument()->addScriptDeclaration($script);
}
?>
<div class="cmdonation">
	<form class="pure-form pure-form-aligned" method="post" action="<?php echo $postUrl; ?>">
		<fieldset>
			<div class="pure-control-group">
				<label for="amount"><?php echo $langAmount; ?></label>
					<span class="<?php echo $currencySignPosition == 'before' ? 'pure-input-prepend' : 'pure-input-append'; ?>">
						<?php if ($currencySignPosition == 'before'): ?>
						<span class="add-on"><?php echo $currencySign; ?></span>
						<?php endif; ?>
						<input type="text" placeholder="<?php echo $langAmount; ?>" name="amount" id="amount">
						<?php if ($currencySignPosition == 'after'): ?>
						<span class="add-on"><?php echo $currencySign; ?></span>
						<?php endif; ?>
					</span>
			</div>

			<?php if ($anonymous): ?>
			<div class="pure-control-group">
				<label></label>
				<div class="pure-controls">
					<label for="anonymous" class="pure-checkbox">
						<input type="checkbox" value="1" class="inputbox" name="anonymous" id="anonymous">
						<?php echo $langAnonymous; ?>
					</label>
				</div>
			</div>
			<?php endif; ?>

			<?php
			// Show payment method selection.
			if (!$loneOption || ($loneOption && !$hideLoneOption)):
			?>
			<div class="pure-control-group">
				<label class="item-list-label"><?php echo $langPaymentMethod; ?></label>
					<div class="pure-controls">
					<?php foreach ($paymentMethods as $method): ?>
						<label for="payment_method_<?php echo $method->name; ?>" class="pure-radio">
							<input type="radio" name="payment_method" id="payment_method_<?php echo $method->name; ?>" value="<?php echo $method->name; ?>">
							<?php echo $method->title; ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
			<?php
			// Hide payment method selection.
			elseif ($loneOption && $hideLoneOption):
				$tmp = $paymentMethods;
				$method = reset($tmp);
			?>
			<input type="hidden" name="payment_method" value="<?php echo $method->name; ?>">
			<?php endif; ?>

			<?php
			// Show recurring selection.
			if (count($recurringCycles) >= 1):
			?>
			<?php if ($recurring): ?>
			<div class="pure-control-group">
				<label></label>
				<div class="pure-controls">
					<label for="recurring" class="pure-checkbox">
						<input type="checkbox" value="1" class="inputbox" name="recurring" id="recurring">
						<?php echo $langRecurringDonation; ?>
					</label>
				</div>
			</div>
			<?php endif; ?>

			<?php if (count($recurringCycles) > 1) : ?>
			<div class="pure-control-group" id="recurringCycles" style="display: none">
				<label class="pure-label-top-aligned" for="recurring_cycle"><?php echo $langRecurringCycle; ?></label>
				<div class="pure-controls">
					<?php foreach ($recurringCycles as $cycle): ?>
					<label class="pure-radio">
					<input type="radio" name="recurring_cycle" id="recurring_cycle_<?php echo $cycle; ?>" value="<?php echo $cycle; ?>">
					<?php echo JText::_('COM_CMDONATION_RECURRING_CYCLE_' . strtoupper($cycle)); ?>
					</label>
				<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
			<?php endif; ?>

			<div class="pure-controls">
				<input type="submit" class="pure-button pure-button-primary" value="<?php echo $langDonate; ?>" />
			</div>
		</fieldset>
		<input type="hidden" name="campaign_id" value="<?php echo $campaignId; ?>" />
		<input type="hidden" name="return_url" value="<?php echo $returnUrl; ?>" />
	</form>
</div>
