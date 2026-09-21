<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width">
	<meta http-equiv="X-UA-Compatible" content="IE=Edge">
	<meta name="robots" content="noindex,follow">
	<title><?php echo esc_html(wp_strip_all_tags($content->title))?></title>
	
	<style>
	img { max-width: 100%; height: auto; }
	</style>
	
	<?php
	do_action('kboard_document_print_head');
	?>
</head>
<body onload="window.print()">
<h1><?php echo esc_html(wp_strip_all_tags($content->title))?></h1>
<p><?php echo __('Author', 'kboard')?>:<?php echo esc_html($content->member_display)?> / <?php echo __('Date', 'kboard')?>:<?php echo esc_html(kboard_date_format($content->date, 'Y-m-d H:i:s'))?> / <?php echo __('Views', 'kboard')?>:<?php echo number_format($content->view)?></p>
<?php echo $content->getDocumentOptionsHTML()?>
<?php echo kboard_content_paragraph_breaks($content->content)?>
</body>
</html>
