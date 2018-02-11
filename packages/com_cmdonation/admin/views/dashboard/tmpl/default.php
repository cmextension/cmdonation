<?php
/**
 * @package    CMDonation
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$donations = $this->latestDonations;
$campaigns = $this->campaigns;

$params					= JComponentHelper::getParams('com_cmdonation');
$currencySign			= $params->get('currency_sign');
$currencySignPosition	= $params->get('currency_sign_position');
$decimals				= $params->get('decimals');
$decimalPoint			= $params->get('decimal_point');
$thousandSeparator		= $params->get('thousand_separator');
$dateFormat				= $params->get('date_format');
?>
<div class="cmdonation">
	<?php echo $this->submenu; ?>

	<div class="content-container">
		<div class="pure-g">
			<div class="pure-u-1 pure-u-md-1-2">
				<h3><?php echo JText::_('COM_CMDONATION_DASHBOARD_CAMPAIGNS'); ?></h3>
				<?php if (!empty($campaigns)) : ?>
				<table class="pure-table pure-table-horizontal pure-table-striped">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_CMDONATION_DONATION_CAMPAIGN_LABEL'); ?></th>
							<th><?php echo JText::_('COM_CMDONATION_STATS_AMOUNT_DONATED'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($campaigns as $campaign) : ?>
						<tr>
							<td><?php echo $campaign->name; ?></td>
							<td>
							<?php
							echo CMDonationHelper::showDonationAmount(
									$campaign->amount, $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator, false
								); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
				<div class="text-error"><?php echo JText::_('COM_CMDONATION_NO_CAMPAIGNS'); ?></div>
				<?php endif; ?>

				<h3><?php echo JText::_('COM_CMDONATION_DASHBOARD_LATEST_DONATIONS'); ?></h3>
				<?php if (!empty($donations)) : ?>
				<table class="pure-table pure-table-horizontal pure-table-striped">
					<thead>
						<tr>
							<th><?php echo JText::_('COM_CMDONATION_DONOR'); ?></th>
							<th><?php echo JText::_('COM_CMDONATION_DONATION_CAMPAIGN_LABEL'); ?></th>
							<th><?php echo JText::_('COM_CMDONATION_DONATION_AMOUNT_LABEL'); ?></th>
							<th><?php echo JText::_('COM_CMDONATION_DATE'); ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($donations as $donation) : ?>
						<tr>
							<td><?php echo $donation->first_name . ' ' . $donation->last_name; ?></td>
							<td>
							<?php
							if (isset($campaigns[$donation->campaign_id]))
								echo $campaigns[$donation->campaign_id]->name;
							else
								echo '<span class="text-error">' . JText::_('COM_CMDONATION_CAMPAIGN_NOT_FOUND') . '</span>';
							?>
							</td>
							<td>
							<?php
							echo CMDonationHelper::showDonationAmount(
									$donation->amount, $currencySign, $currencySignPosition, $decimals, $decimalPoint, $thousandSeparator, false
								); ?>
							</td>
							<td><?php echo JHtml::_('date', $donation->created, $dateFormat); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
				<div class="text-error"><?php echo JText::_('COM_CMDONATION_NO_DONATIONS'); ?></div>
				<?php endif; ?>
			</div>

			<div class="pure-u-1 pure-u-md-1-2">
				<div class="dashboard-info">
					<h3>Thank you for using CM Donation!</h3>
					<h4>Extension information</h4>
					<ul>
						<li>Extension: CM Donation</li>
						<li>Version: 1.1.2</li>
						<li>Released date: February 11, 2018</li>
						<li>License: <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License version 2 or later</a></li>
						<li>Author: <a href="http://www.cmext.vn/" target="_blank">CMExtension team</a></li>
					</ul>
					<h4>Credits</h4>
					<ul>
						<li>Flag icons: <a href="http://www.famfamfam.com/" target="_blank">Mark James</a></li>
						<li>CSS framework: <a href="http://www.purecss.io/" target="_blank">Pure</a> (customized)</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</div>
