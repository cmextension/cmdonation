<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$campaignId		= $this->campaignId;
$campaign		= $this->campaign;
$statistics		= $this->statistics;
$paymentMethods	= $this->paymentMethods;
?>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<form action="index.php" method="get" class="pure-form">
			<input type="hidden" name="option" value="com_cmdonation" />
			<input type="hidden" name="view" value="statistics" />
			<select name="campaign" id="campaign">
				<option value=""><?php echo JText::_('COM_CMDONATION_SELECT_CAMPAIGN_OPTION');?></option>
				<?php echo JHtml::_('select.options', $this->campaignList, 'value', 'text', $campaignId);?>
			</select>
			<button title="" class="pure-button pure-button-primary hasTooltip" type="submit"><i class="fa fa-search"></i> <?php echo JText::_('COM_CMDONATION_VIEW'); ?></button>
		</form>
	<?php if (empty($campaign) && $campaignId > 0): ?>
		<div class="text-error"><?php echo JText::_('COM_CMDONATION_CAMPAIGN_NOT_FOUND'); ?></div>
	<?php elseif (!empty($campaign)): ?>
		<?php
		$params					= $this->params;
		$countryDisplay			= $params->get('statistics_country');
		$currencySign			= $params->get('currency_sign');
		$currencySignPosition	= $params->get('currency_sign_position');
		$decimals				= $params->get('decimals');
		$decimalPoint			= $params->get('decimal_point');
		$thousandSeparator		= $params->get('thousand_separator');
		?>

		<h2><?php echo $campaign->name; ?></h2>

		<?php if (empty($statistics['donations'])): ?>
			<?php echo '<div class="text-error">' . JText::_('COM_CMDONATION_NO_DONATIONS') . '</div>'; ?>
		<?php else: ?>
		<form action="index.php" method="get" name="adminForm" id="adminForm">
			<input type="hidden" name="option" value="com_cmdonation" />
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="campaign" value="<?php echo $campaign->id; ?>" />
		</form>
		<table class="pure-table pure-table-horizontal">
			<tbody>
				<tr>
					<td><strong><?php echo JText::_('COM_CMDONATION_STATS_NUMBER_OF_DONATIONS'); ?>:</strong></td>
					<td><?php echo $statistics['number_of_donations']; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_('COM_CMDONATION_STATS_AMOUNT_DONATED'); ?>:</strong></td>
					<td><?php echo $statistics['total']; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_('COM_CMDONATION_STATS_LOWEST'); ?>:</strong></td>
					<td><?php echo $statistics['lowest']; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_('COM_CMDONATION_STATS_AVERAGE'); ?>:</strong></td>
					<td><?php echo $statistics['average']; ?></td>
				</tr>
				<tr>
					<td><strong><?php echo JText::_('COM_CMDONATION_STATS_HIGHEST'); ?>:</strong></td>
					<td><?php echo $statistics['highest']; ?></td>
				</tr>
			</tbody>
		</table>

		<h3><?php echo JText::_('COM_CMDONATION_STATS_DONORS'); ?></h3>
		<table class="pure-table pure-table-horizontal pure-table-striped">
			<thead>
				<tr>
					<th class="pure-text-center">#</th>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_DONATION_FIRST_NAME_LABEL'); ?></th>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_DONATION_LAST_NAME_LABEL'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_DONATION_EMAIL_LABEL'); ?></th>
					<?php if ($countryDisplay != 'hide'): ?>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_DONATION_COUNTRY_LABEL'); ?></th>
					<?php endif; ?>
					<?php if ($countryDisplay == 'both'): ?>
					<th class="pure-text-center pure-hidden-phone"></th>
					<?php endif; ?>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_DONATION_AMOUNT_LABEL'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_DONATION_COMPLETED_LABEL'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_DONATION_PAYMENT_METHOD_LABEL'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $count = 1; ?>
				<?php foreach ($statistics['donations'] as $donation): ?>
				<tr>
					<td class="pure-text-center"><?php echo $count++; ?></td>
					<td class="pure-text-center"><?php echo $donation->first_name; ?></td>
					<td class="pure-text-center"><?php echo $donation->last_name; ?></td>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->email; ?></td>
					<?php if ($countryDisplay == 'name'): ?>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->country_name; ?></td>
					<?php elseif ($countryDisplay == 'flag'): ?>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->country_flag; ?></td>
					<?php elseif ($countryDisplay == 'both'): ?>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->country_name; ?></td>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->country_flag; ?></td>
					<?php endif; ?>
					<td class="pure-text-center">
						<?php echo CMDonationHelper::showDonationAmount($donation->amount, $currencySign,
							$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
					</td>
					<td class="pure-text-center pure-hidden-phone"><?php echo $donation->completed; ?></td>
					<td class="pure-text-center pure-hidden-phone">
						<?php echo CMDonationHelper::displayPaymentMethodName($donation->payment_method_id, $paymentMethods); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ($countryDisplay != 'hide'): ?>
		<h3><?php echo JText::_('COM_CMDONATION_STATS_COUNTRIES'); ?></h3>
		<table class="pure-table pure-table-horizontal pure-table-striped">
			<thead>
				<tr>
					<th class="pure-text-center">#</th>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_COUNTRY'); ?></th>
					<?php if ($countryDisplay == 'both'): ?>
					<th class="pure-text-center"></th>
					<?php endif; ?>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_CONTRIBUTION'); ?></th>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_NUMBER_OF_DONATIONS'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_LOWEST'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_AVERAGE'); ?></th>
					<th class="pure-text-center pure-hidden-phone"><?php echo JText::_('COM_CMDONATION_HIGHEST'); ?></th>
					<th class="pure-text-center"><?php echo JText::_('COM_CMDONATION_TOTAL'); ?></th>
				</tr>
			</thead>
			<?php if (!empty($statistics['countries'])): ?>
			<tbody>
				<?php $count = 1; ?>
				<?php foreach ($statistics['countries'] as $country): ?>
				<tr>
					<td class="pure-text-center"><?php echo $count++; ?></td>
					<?php if ($countryDisplay == 'name'): ?>
					<td class="pure-text-center"><?php echo $country->country_name; ?></td>
					<?php elseif ($countryDisplay == 'flag'): ?>
					<td class="pure-text-center"><?php echo $country->country_flag; ?></td>
					<?php elseif ($countryDisplay == 'both'): ?>
					<td class="pure-text-center"><?php echo $country->country_name; ?></td>
					<td class="pure-text-center"><?php echo $country->country_flag; ?></td>
					<?php endif; ?>
					<td class="pure-text-center pure-hidden-phone"><?php echo $country->contribution . '%'; ?></td>
					<td class="pure-text-center"><?php echo $country->donation_quantity; ?></td>
					<td class="pure-text-center pure-hidden-phone">
						<?php echo CMDonationHelper::showDonationAmount($country->lowest, $currencySign,
							$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
					</td>
					<td class="pure-text-center pure-hidden-phone">
						<?php echo CMDonationHelper::showDonationAmount($country->average, $currencySign,
							$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
					</td>
					<td class="pure-text-center pure-hidden-phone">
						<?php echo CMDonationHelper::showDonationAmount($country->highest, $currencySign,
							$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
					</td>
					<td class="pure-text-center">
						<?php echo CMDonationHelper::showDonationAmount($country->amount, $currencySign,
							$currencySignPosition, $decimals, $decimalPoint, $thousandSeparator); ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
			<?php endif; ?>
		</table>
		<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>
	</div>
</div>
