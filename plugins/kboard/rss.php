<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$board_id = isset($_GET['board_id']) ? intval($_GET['board_id']) : '';

$url = new KBUrl();
$list = new KBContentList();
$list->rpp = apply_filters('kboard_per_rss', 20, $board_id);
$list->initWithRSS($board_id);

header('Content-Type: application/xml');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<rss version="2.0">
	<channel>
		<title><?php bloginfo('name')?></title>
		<link><?php bloginfo('url')?></link>
		<description><?php bloginfo('description')?></description>
		
		<?php while($content = $list->hasNext()): $board = new KBoard($content->board_id); $date_timestamp = kboard_date_to_timestamp($content->date);?>
		<item>
			<title><![CDATA[<?php echo wp_strip_all_tags($content->title)?>]]></title>
			<link><![CDATA[<?php echo $url->getDocumentRedirect($content->uid)?>]]></link>
			<description><![CDATA[<?php echo $content->content?>]]></description>
			<author><![CDATA[<?php echo $content->member_display?>]]></author>
			<?php if($date_timestamp !== false):?><pubDate><?php echo gmdate(DATE_RSS, $date_timestamp)?></pubDate><?php endif?>
			<category domain="<?php echo $url->getBoardRedirect($content->board_id)?>"><![CDATA[<?php echo $board->board_name?>]]></category>
		</item>
		<?php endwhile?>
	</channel>
</rss>
