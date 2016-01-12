<?php
/**
 * @package    PlgContentCMDonationContent
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
?>
<div class="cmdonation">
<?php if (!empty($countries)): ?>
	<table class="pure-table pure-table-horizontal pure-table-striped">
		<thead>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<th class="pure-text-center pure-hidden-phone">#</th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_COUNTRY'); ?></th>
				<?php if ($countryDisplay == 'both'): ?>
				<th class="pure-text-center"></th>
				<?php endif; ?>
				<?php if ($contributionColumn == 'show'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_CONTRIBUTION'); ?></th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_NUMBER_OF_DONATIONS'); ?></th>
				<?php if ($lowestColumn == 'show'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_LOWEST'); ?></th>
				<?php endif; ?>
				<?php if ($averageColumn == 'show'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_AVERAGE'); ?></th>
				<?php endif; ?>
				<?php if ($highestColumn == 'show'): ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_HIGHEST'); ?></th>
				<?php endif; ?>
				<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_TOTAL'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $count = 1; ?>
			<?php foreach ($countries as $country): ?>
			<?php if ($count > $limit) break; ?>
			<tr>
				<?php if ($rowNumberColumn == 'show'): ?>
				<td class="pure-text-center pure-hidden-phone"><?php echo $count++; ?></td>
				<?php endif; ?>
				<?php if ($countryDisplay == 'name'): ?>
				<td class="pure-text-center"><?php echo $country->country_name; ?></td>
				<?php elseif ($countryDisplay == 'flag'): ?>
				<td class="pure-text-center"><?php echo $country->country_flag; ?></td>
				<?php elseif ($countryDisplay == 'both'): ?>
				<td class="pure-text-center"><?php echo $country->country_name; ?></td>
				<td class="pure-text-center"><?php echo $country->country_flag; ?></td>
				<?php endif; ?>
				<?php if ($contributionColumn == 'show'): ?>
				<td class="pure-text-center"><?php echo $country->contribution . '%'; ?></td>
				<?php endif; ?>
				<td class="pure-text-center"><?php echo $country->donation_quantity; ?></td>
				<?php if ($lowestColumn == 'show'): ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($country->lowest, $currencySign,
						$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
				</td>
				<?php endif; ?>
				<?php if ($averageColumn == 'show'): ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($country->average, $currencySign,
						$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
				</td>
				<?php endif; ?>
				<?php if ($highestColumn == 'show'): ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($country->highest, $currencySign,
						$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
				</td>
				<?php endif; ?>
				<td class="pure-text-center">
					<?php echo CMDonationHelper::showDonationAmount($country->amount, $currencySign,
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
