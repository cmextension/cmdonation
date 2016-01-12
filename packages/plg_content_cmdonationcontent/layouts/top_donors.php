<?php
/**
 * @package    PlgContentCMDonationContent
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
?>
<div class="cmdonation">
<?php if (!empty($donors)): ?>
	<table class="pure-table pure-table-horizontal pure-table-striped">
		<thead>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<th class="pure-text-center pure-hidden-phone">#</th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_DONOR'); ?></th>
				<?php if ($countryDisplay != 'hide'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_COUNTRY'); ?></th>
				<?php endif; ?>
				<?php if ($countryDisplay == 'both'): ?>
				<th class="pure-text-center"></th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_NUMBER_OF_DONATIONS'); ?></th>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_AMOUNT_DONATED'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $count = 1; ?>
			<?php foreach ($donors as $donor): ?>
			<?php if ($count > $limit) break; ?>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<td class="pure-text-center pure-hidden-phone"><?php echo $count++; ?></td>
				<?php endif; ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonorName($donor, $donorNameDisplay); ?>
				</td>
				<?php if ($countryDisplay == 'name'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryName($donor->country_code); ?>
				<?php elseif ($countryDisplay == 'flag'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryFlag($donor->country_code); ?>
				<?php elseif ($countryDisplay == 'both'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryName($donor->country_code); ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryFlag($donor->country_code); ?>
				<?php endif; ?>
				<td class="pure-text-center"><?php echo $donor->donation_quantity; ?></td>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($donor->amount, $currencySign,
						$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<div class="text-error"><?php echo JText::_('COM_CMDONATION_NO_DONATIONS'); ?></div>
<?php endif; ?>
</div>
