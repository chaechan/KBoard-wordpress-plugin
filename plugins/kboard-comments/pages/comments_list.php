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
			<input type="hidden" id="kboard-comments-update-security" value="<?php echo esc_attr(wp_create_nonce('kboard_comments_list_update'))?>">
			<?php $table->display()?>
		</form>
	</div>
</div>

<script>
function kboard_comment_list_update(uid, field){
	uid = parseInt(uid, 10);
	if(!uid || jQuery.inArray(field, ['status', 'date']) === -1) return false;
	var row = jQuery('#kboard-comments-list tr[data-uid="' + uid + '"]');
	if(!row.length || row.data('kboard-saving')) return false;
	var data = {action:'kboard_comments_list_update', security:jQuery('#kboard-comments-update-security').val(), comment_uid:uid, update_field:field};
	var control = row.find('select[name="status[' + uid + ']"]');
	if(field === 'date'){
		data.date = row.find('input[name="comment_date[' + uid + ']"]').val();
		data.time = row.find('input[name="comment_time[' + uid + ']"]').val();
	}
	else{
		data.value = control.val();
	}
	var result = row.find('.kboard-inline-update-result');
	if(!result.length){
		result = jQuery('<span class="kboard-inline-update-result" role="status" aria-live="polite"></span>').appendTo(row.find('.kboard-comments-list-date'));
	}
	result.text('저장 중…');
	row.data('kboard-saving', true);
	row.find('select, .kboard-comment-date-update').prop('disabled', true);
	jQuery.ajax({url:ajaxurl, type:'POST', dataType:'json', data:data}).done(function(response){
		if(!response || !response.success){
			result.text(response && response.data && response.data.message ? response.data.message : '저장하지 못했습니다.');
			if(field === 'status') control.val(row.data('kboard-saved-status'));
			return;
		}
		result.text(response.data.message);
		if(field === 'date'){
			row.find('input[name="comment_date[' + uid + ']"]').val(response.data.date.date);
			row.find('input[name="comment_time[' + uid + ']"]').val(response.data.date.time);
		}
		else{
			row.data('kboard-saved-status', data.value);
		}
	}).fail(function(xhr){
		result.text(xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message ? xhr.responseJSON.data.message : '통신 오류로 저장하지 못했습니다.');
		if(field === 'status') control.val(row.data('kboard-saved-status'));
	}).always(function(){
		row.data('kboard-saving', false);
		row.find('select, .kboard-comment-date-update').prop('disabled', false);
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
	jQuery('#kboard-comments-list tr[data-uid]').each(function(){
		var row = jQuery(this);
		row.data('kboard-saved-status', row.find('select[name^="status["]').val());
	});
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
	jQuery('.kboard-comment-content-timepicker').kboardAdminTimepicker({'timeFormat': 'HH:mm:ss'});
});
</script>
