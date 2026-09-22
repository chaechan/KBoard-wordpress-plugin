<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __(KBOARD_COMMENTS_PAGE_TITLE, 'kboard-comments')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard-comments')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard-comments')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard-comments')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard-comments')?></a>
	
	<hr class="wp-header-end">

	<div class="kboard-comments-list-search">
		<form method="get">
			<input type="hidden" name="page" value="kboard_comments_list">
			<input type="hidden" name="filter_board_id" value="<?php echo $table->filter_board_id?>">
			<input type="hidden" name="per_page" value="<?php echo esc_attr($table->per_page)?>">

			<select name="target">
				<option value=""<?php if(kboard_target() == 'content'):?> selected<?php endif?>><?php echo __('Content', 'kboard-comments')?></option>
				<option value="user_display"<?php if(kboard_target() == 'user_display'):?> selected<?php endif?>><?php echo __('Author', 'kboard-comments')?></option>
			</select>
			
			<?php $table->search_box(__('Search', 'kboard-comments'), 'kboard_comments_list_search')?>
		</form>
	</div>
	
	<div class="kboard-comments-list">
		<form id="kboard-comments-list" method="post">
			<?php $table->display()?>
		</form>
	</div>
</div>

<script>
function kboard_comment_list_update(){
	jQuery('#kboard-comments-list').find('.spinner').addClass('is-active');
	jQuery.post(ajaxurl, jQuery('#kboard-comments-list').serialize()+'&action=kboard_comments_list_update', function(res){
		jQuery('#kboard-comments-list').find('.spinner').removeClass('is-active');
	});
	return false;
}

function kboard_comment_list_filter(form){
	var url = '<?php echo admin_url('admin.php?page=kboard_comments_list') ?>';

	var per_page = jQuery('select[name=per_page]', form).val();
	var start_date = jQuery('input[name=start_date]', form).val();
	var end_date = jQuery('input[name=end_date]', form).val();
	var target = '<?php echo esc_js(kboard_target())?>';
	var keyword = '<?php echo esc_js(isset($_GET["s"])?$_GET["s"]:"")?>';

	if(per_page){
		url += '&per_page=' + encodeURIComponent(per_page);
	}

	if(start_date){
		url += '&start_date=' + encodeURIComponent(start_date);
	}
	if(end_date){
		url += '&end_date=' + encodeURIComponent(end_date);
	}
	if(target){
		url += '&target=' + encodeURIComponent(target);
	}
	if(keyword){
		url += '&s=' + encodeURIComponent(keyword);
	}

	window.location.href = url;
}

jQuery(document).ready(function(){
	jQuery('.kboard-comment-content-datepicker').datepicker({
		closeText : '닫기',
		prevText : '이전달',
		nextText : '다음달',
		currentText : '오늘',
		monthNames : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
		monthNamesShort : [ '1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월' ],
		dayNames : [ '일', '월', '화', '수', '목', '금', '토' ],
		dayNamesShort : [ '일', '월', '화', '수', '목', '금', '토' ],
		dayNamesMin : [ '일', '월', '화', '수', '목', '금', '토' ],
		weekHeader : 'Wk',
		dateFormat : 'yy-mm-dd',
		firstDay : 0,
		isRTL : false,
		duration : 0,
		showAnim : 'show',
		showMonthAfterYear : true,
		yearSuffix : '년'
	});
	jQuery('.kboard-comment-content-timepicker').timepicker({'timeFormat': 'HH:mm:ss'});
});
</script>
