<?php
/**
 * @package    CMDonationPaypal
 * @copyright  Copyright (C) 2014-2016 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die();
?>
<form id='donationForm' name='donationForm' action='<?php echo $transactionUrl; ?>' method='post'>
	<?php foreach ($formData as $key => $value): ?>
	<input type="hidden" name="<?php echo $key; ?>" value="<?php echo htmlentities($value); ?>" />
	<?php endforeach; ?>
</form>
<?php echo $redirectMessage; ?>
<script type="text/javascript">
	function submitForm()
	{
		document.donationForm.submit();

		return false;
	}
	setTimeout("submitForm()", <?php echo ($secondsToWait * 1000); ?>);
</script>
