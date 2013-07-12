<?php
	if ( !isset($content))
		$content = null;


?>


<?
	require_once("../../../application/social/Twitter.php");
	$twit = new Twitter($content);
	
	$_SESSION['userInfo'] = $twit->getUserInfo();
	
	
?>

<script type='text/javascript'>
	window.opener.FuspicAuth.setUser(<?= json_encode($twit->getUserInfo()); ?>);
</script>