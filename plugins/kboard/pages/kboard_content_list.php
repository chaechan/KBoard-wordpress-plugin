<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __('KBoard : 전체 게시글', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<?php $table->views()?>
	
	<div class="kboard-content-list-search">
		<form method="get">
			<input type="hidden" name="page" value="kboard_content_list">
			<input type="hidden" name="filter_view" value="<?php echo $table->filter_view?>">
			<input type="hidden" name="filter_board_id" value="<?php echo $table->filter_board_id?>">
			<input type="hidden" name="per_page" value="<?php echo $table->per_page?>">
			<input type="hidden" name="filter_category1" value="<?php echo esc_attr($table->filter_category1)?>">
			<input type="hidden" name="start_date" value="<?php echo esc_attr(kboard_start_date())?>">
			<input type="hidden" name="end_date" value="<?php echo esc_attr(kboard_end_date())?>">
			
			<select name="target">
				<option value=""><?php echo __('All', 'kboard')?></option>
				<option value="title"<?php if(kboard_target() == 'title'):?> selected<?php endif?>><?php echo __('Title', 'kboard')?></option>
				<option value="content"<?php if(kboard_target() == 'content'):?> selected<?php endif?>><?php echo __('Content', 'kboard')?></option>
				<option value="member_display"<?php if(kboard_target() == 'member_display'):?> selected<?php endif?>><?php echo __('Author', 'kboard')?></option>
			</select>
				
			<?php $table->search_box(__('Search', 'kboard'), 'kboard_content_list_search')?>
		</form>
	</div>
	<div class="kboard-content-list">
		<form id="kboard-content-list" method="post">
			<?php $table->display()?>
		</form>
	</div>
</div>

<script>
function kboard_content_list_update(uid){
	uid = parseInt(uid, 10);
	if(!uid){
		return false;
	}

	var row = jQuery('#kboard-content-list tr[data-uid="' + uid + '"]');
	if(!row.length){
		return false;
	}

	jQuery('#kboard-content-list').find('.spinner').addClass('is-active');
	jQuery.post(ajaxurl, row.find(':input').serialize()+'&content_uid='+uid+'&action=kboard_content_list_update', function(res){
		jQuery('#kboard-content-list').find('.spinner').removeClass('is-active');
	});
	return false;
}
function kboard_content_list_filter(form){
	var url = '<?php echo admin_url('admin.php?page=kboard_content_list') ?>';
	var filter_view = jQuery('input[name=filter_view]', form).val();
	var board_id = jQuery('select[name=filter_board_id]', form).val();
	var per_page = jQuery('select[name=per_page]', form).val();
	var filter_category1 = jQuery('input[name=filter_category1]', form).val();
	var start_date = jQuery('input[name=start_date]', form).val();
	var end_date = jQuery('input[name=end_date]', form).val();
	
	var target = '<?php echo esc_js(kboard_target())?>';
	var keyword = '<?php echo esc_js(isset($_GET["s"])?$_GET["s"]:"")?>';

	if(filter_view){
		url += '&filter_view=' + encodeURIComponent(filter_view);
	}
	if(board_id){
		url += '&filter_board_id=' + encodeURIComponent(board_id);
	}
	if(per_page){
		url += '&per_page=' + encodeURIComponent(per_page);
	}
	if(filter_category1){
		url += '&filter_category1=' + encodeURIComponent(filter_category1);
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

function kboard_content_list_move_to_board(){
	var board_id = jQuery('#move-to-board').val();
	if(!board_id){
		alert('게시판을 선택해주세요.');
		return false;
	}
	var url = '<?php echo admin_url('admin.php') ?>';
	url += '?page=kboard_list';
	url += '&uid=' + encodeURIComponent(board_id);
	window.location.href = url;
}

jQuery(document).ready(function(){
	jQuery('.kboard-content-datepicker').datepicker({
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
	jQuery('.kboard-content-timepicker').timepicker({'timeFormat': 'HH:mm:ss'});
});
</script>
