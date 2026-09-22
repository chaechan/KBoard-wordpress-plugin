<?php if(!defined('ABSPATH')) exit;?>
<div class="wrap">
	<div class="kboard-header-logo"></div>
	<h1 class="wp-heading-inline"><?php echo __('KBoard : 업데이트', 'kboard')?></h1>
	<a href="https://www.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/threads" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
	<a href="https://www.cosmosfarm.com/support" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
	<a href="https://blog.cosmosfarm.com" class="page-title-action" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
	
	<hr class="wp-header-end">
	
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row"><label for="kboard_updates_notify_disabled">업데이트 알림</label></th>
				<td>
					<form method="post">
						<input type="hidden" name="action" value="kboard_system_option_update">
						<select id="kboard_updates_notify_disabled" name="option[kboard_updates_notify_disabled]" onchange="kboard_system_option_update(this.form)">
							<option value="">알림 받기</option>
							<option value="1"<?php if(get_option('kboard_updates_notify_disabled')):?> selected<?php endif?>>알림 중지</option>
						</select>
						<p class="description">새로운 업데이트가 있을 경우 알림을 받습니다.</p>
					</form>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="">KBoard 게시판</label></th>
				<td>
					<p style="margin:0">현재 설치된 버전은 <strong><?php echo KBOARD_VERSION?></strong> 입니다.</p>
					<p>
						<a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=kboard_updates&action=kboard'), 'kboard_updates'))?>" onclick="return confirm('플러그인을 백업해두셨나요? 모두 최신 파일로 교체됩니다. 계속 할까요?')"><?php echo $version->kboard?> 설치하기</a>
						<a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=kboard_updates&action=kboard-noskins'), 'kboard_updates'))?>" onclick="return confirm('플러그인을 백업해두셨나요? 모두 최신 파일로 교체됩니다. 계속 할까요?')">현재 스킨을 유지하고 <?php echo $version->kboard?> 설치하기</a>
						<a class="button" href="https://github.com/chaechan/KBoard-wordpress-plugin/blob/master/plugins/kboard/history.md" onclick="window.open(this.href);return false;">변경사항 보기</a>
					</p>
					<p class="description">스킨을 유지한 상태로 설치하면 코어 파일만 덮어쓰기 됩니다.</p>
					<p class="description">모든 기능을 활용하시려면 스킨 파일까지 업데이트해주셔야 합니다.</p>
				</td>
			</tr>
			<?php if(defined('KBOARD_COMMNETS_VERSION')):?>
			<tr valign="top">
				<th scope="row"><label for="">KBoard 댓글</label></th>
				<td>
					<p style="margin:0">현재 설치된 버전은 <strong><?php echo KBOARD_COMMNETS_VERSION?></strong> 입니다.</p>
					<p>
						<a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=kboard_updates&action=comments'), 'kboard_updates'))?>" onclick="return confirm('플러그인을 백업해두셨나요? 모두 최신 파일로 교체됩니다. 계속 할까요?')"><?php echo $version->comments?> 설치하기</a>
						<a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=kboard_updates&action=comments-noskins'), 'kboard_updates'))?>" onclick="return confirm('플러그인을 백업해두셨나요? 모두 최신 파일로 교체됩니다. 계속 할까요?')">현재 스킨을 유지하고 <?php echo $version->comments?> 설치하기</a>
						<a class="button" href="https://github.com/chaechan/KBoard-wordpress-plugin/blob/master/plugins/kboard-comments/history.md" onclick="window.open(this.href);return false;">변경사항 보기</a>
					</p>
					<p class="description">스킨을 유지한 상태로 설치하면 코어 파일만 덮어쓰기 됩니다.</p>
					<p class="description">모든 기능을 활용하시려면 스킨 파일까지 업데이트해주셔야 합니다.</p>
				</td>
			</tr>
			<?php else:?>
			<tr valign="top">
				<th scope="row"><label for="">KBoard 댓글</label></th>
				<td>
					<p style="margin:0">댓글 플러그인을 설치해주세요.</p>
					<p>
						<a class="button" href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=kboard_updates&action=comments'), 'kboard_updates'))?>" onclick="return confirm('플러그인 설치를 계속 할까요?')"><?php echo $version->comments?> 설치하기</a>
						<a class="button" href="https://github.com/chaechan/KBoard-wordpress-plugin/blob/master/plugins/kboard-comments/history.md" onclick="window.open(this.href);return false;">변경사항 보기</a>
					</p>
					<p class="description">이미 설치되어 있다면 활성화 해주세요.</p>
				</td>
			</tr>
			<?php endif?>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<p style="margin:0">업데이트에 실패하면 FTP로 접속해서 수동으로 업데이트해주셔야 합니다.</p>
					<p><a href="https://blog.naver.com/PostView.nhn?blogId=chan2rrj&logNo=221215008402" onclick="window.open(this.href);return false;">KBoard(케이보드) 플러그인 업데이트 방법</a></p>
					<p><a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;">KBoard(케이보드) 공식 홈페이지</a></p>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<script>
function kboard_system_option_update(form){
	var data = jQuery(form).serializeArray();
	data.push({name:'security', value:<?php echo wp_json_encode(wp_create_nonce('kboard-system-option-update'))?>});
	jQuery.post(ajaxurl, data, function(res){
		alert('변경되었습니다.');
	});
	return false;
}
</script>
