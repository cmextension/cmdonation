<?php
/**
 * @package    PlgCMDonationPaypalProExpress
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$seconds = 5;
$clickHere = '<a href="' . $transactionUrl . '">' . JText::_('COM_CMDONATION_CLICK_HERE') . '</a>';
?>
<p><?php echo JText::sprintf('COM_CMDONATION_REDIRECT_MESSAGE', $paymentMethodName, $seconds, $clickHere); ?></p>

<form action="<?php echo $transactionUrl ?>" method="post" name="paymentForm" id="paymentForm">
</form>

<script type="text/javascript">
	function submitForm() {
		document.paymentForm.submit();

		return false;
	}

	setTimeout('submitForm()', <?php echo $seconds * 1000; ?>);
</script>
