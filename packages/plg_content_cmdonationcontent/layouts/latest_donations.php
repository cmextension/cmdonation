<?php
/**
 * @package    PlgContentCMDonationContent
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
?>
<div class="cmdonation">
<?php if (!empty($donations)): ?>
	<table class="pure-table pure-table-horizontal pure-table-striped">
		<thead>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<th class="pure-text-center pure-hidden-phone">#</th>
				<?php endif; ?>
				<?php if ($dateColumn == 'show'): ?>
				<th class="pure-text-center "><?php echo JText::_('COM_CMDONATION_DATE'); ?></th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_DONOR'); ?></th>
				<?php if ($countryDisplay != 'hide'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_COUNTRY'); ?></th>
				<?php endif; ?>
				<?php if ($countryDisplay == 'both'): ?>
				<th class="pure-text-center"></th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_AMOUNT_DONATED'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ($rowNumberColumn == 'show') $count = 1; ?>
			<?php foreach ($donations as $donation): ?>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<td class="pure-text-center pure-hidden-phone"><?php echo $count++; ?></td>
				<?php endif; ?>
				<?php if ($dateColumn == 'show'): ?>
				<td class="pure-text-center"><?php echo JHtml::_('date', $donation->completed, $dateFormat); ?></td>
				<?php endif; ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonorName($donation, $donorNameDisplay); ?>
				</td>
				<?php if ($countryDisplay == 'name'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryName($donation->country_code); ?>
				<?php elseif ($countryDisplay == 'flag'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryFlag($donation->country_code); ?>
				<?php elseif ($countryDisplay == 'both'): ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryName($donation->country_code); ?>
				<td class="pure-text-center"><?php echo CMDonationHelper::showCountryFlag($donation->country_code); ?>
				<?php endif; ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($donation->amount, $currencySign,
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