<?php if(!defined('ABSPATH')) exit;?>

<style>
:root {
	--kboard-primary: #007cba;
	--kboard-primary-hover: #006ba1;
	--kboard-bg: #f0f2f5;
	--kboard-card-bg: #ffffff;
	--kboard-text-main: #2c3e50;
	--kboard-text-sub: #64748b;
	--kboard-border: #e2e8f0;
	--kboard-success-bg: #dcfce7;
	--kboard-success-text: #166534;
	--kboard-warning-bg: #fef9c3;
	--kboard-warning-text: #854d0e;
	--kboard-danger-bg: #fee2e2;
	--kboard-danger-text: #991b1b;
	--kboard-radius: 12px;
	--kboard-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
}

.kboard-dashboard-wrapper {
	font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
	max-width: 1400px;
	margin: 20px auto;
	box-sizing: border-box;
}

.kboard-dashboard-wrapper * {
	box-sizing: border-box;
}

/* Header */
.kboard-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	margin-bottom: 30px;
	padding: 0 5px;
}

.kboard-header h1 {
	font-size: 28px;
	font-weight: 700;
	color: var(--kboard-text-main);
	margin: 0;
	display: flex;
	align-items: center;
	gap: 12px;
}

.kboard-header-actions {
	display: flex;
	gap: 12px;
}

.kboard-action-link {
	text-decoration: none;
	color: var(--kboard-text-sub);
	font-weight: 500;
	padding: 8px 16px;
	background: #fff;
	border: 1px solid var(--kboard-border);
	border-radius: 8px;
	transition: all 0.2s ease;
	font-size: 14px;
}

.kboard-action-link:hover {
	color: var(--kboard-primary);
	border-color: var(--kboard-primary);
	background: #f8fafc;
}

/* Welcome Panel Customization */
.kboard-dashboard-wrapper .welcome-panel {
	background: #fff;
	border: 1px solid var(--kboard-border);
	border-radius: var(--kboard-radius);
	box-shadow: var(--kboard-shadow);
	padding: 24px;
	margin-bottom: 30px;
	position: relative;
}

.kboard-section-title {
	font-size: 20px;
	font-weight: 600;
	color: var(--kboard-text-main);
	margin: 0 0 20px 5px;
	display: flex;
	align-items: center;
}

.kboard-section-title::before {
	content: '';
	display: block;
	width: 4px;
	height: 24px;
	background: var(--kboard-primary);
	margin-right: 12px;
	border-radius: 2px;
}

/* Grid Layout */
.kboard-settings-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
	gap: 24px;
}

/* Card Style */
.kboard-card {
	background: var(--kboard-card-bg);
	border: 1px solid var(--kboard-border);
	border-radius: var(--kboard-radius);
	box-shadow: var(--kboard-shadow);
	display: flex;
	flex-direction: column;
	transition: transform 0.2s, box-shadow 0.2s;
	overflow: hidden;
}

.kboard-card:hover {
	transform: translateY(-2px);
	box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
}

.kboard-card-header {
	padding: 20px;
	border-bottom: 1px solid var(--kboard-border);
	background: #fff;
	display: flex;
	justify-content: space-between;
	align-items: center;
}

.kboard-card-title {
	font-size: 16px;
	font-weight: 600;
	color: var(--kboard-text-main);
	margin: 0;
}

.kboard-badge {
	padding: 4px 10px;
	border-radius: 20px;
	font-size: 12px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.kboard-badge.active { background: var(--kboard-success-bg); color: var(--kboard-success-text); }
.kboard-badge.inactive { background: var(--kboard-dashboard-bg); color: var(--kboard-text-sub); border: 1px solid var(--kboard-border); }
.kboard-badge.warning { background: var(--kboard-warning-bg); color: var(--kboard-warning-text); }

.kboard-card-body {
	padding: 20px;
	flex-grow: 1;
}

.kboard-card > form {
	display: flex;
	flex-direction: column;
	height: 100%;
}

.kboard-description {
	color: var(--kboard-text-sub);
	font-size: 14px;
	line-height: 1.6;
	margin: 0;
}

.kboard-description a {
	color: var(--kboard-primary);
	text-decoration: none;
	font-weight: 500;
}

.kboard-description a:hover {
	text-decoration: underline;
}

.kboard-card-footer {
	padding: 16px 20px;
	background: #f8fafc;
	border-top: 1px solid var(--kboard-border);
	display: flex;
	flex-direction: column;
	gap: 12px;
}

/* Form Elements */
.kboard-input-group {
	display: flex;
	flex-direction: column;
	gap: 8px;
}

.kboard-form-control {
	width: 100%;
	padding: 8px 12px;
	border: 1px solid var(--kboard-border);
	border-radius: 6px;
	font-size: 14px;
	transition: border-color 0.2s;
}

.kboard-form-control:focus {
	border-color: var(--kboard-primary);
	outline: none;
	box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.1);
}

textarea.kboard-form-control {
	min-height: 80px;
	resize: vertical;
}

.kboard-btn {
	display: inline-flex;
	justify-content: center;
	align-items: center;
	padding: 10px 16px;
	background: #fff;
	border: 1px solid var(--kboard-border);
	color: var(--kboard-text-main);
	border-radius: 6px;
	font-weight: 600;
	cursor: pointer;
	font-size: 14px;
	transition: all 0.2s;
}

.kboard-btn:hover {
	background: #f1f5f9;
}

.kboard-btn.primary {
	background: var(--kboard-primary);
	color: #fff;
	border: none;
}

.kboard-btn.primary:hover {
	background: var(--kboard-primary-hover);
}

.kboard-btn.full-width {
	width: 100%;
}

/* Utilities */
.mb-2 { margin-bottom: 8px; }
.mt-2 { margin-top: 8px; }
.mt-4 { margin-top: 16px; }

/* Mobile Responsive */
@media (max-width: 782px) {
	/* Reset WordPress admin container paddings */
	#wpcontent {
		padding-left: 0 !important;
	}
	
	#wpbody-content {
		padding-bottom: 20px;
	}
	
	.kboard-dashboard-wrapper,
	.kboard-dashboard-wrapper.wrap {
		margin: 0 !important;
		padding: 15px !important;
		max-width: 100% !important;
	}
	
	.kboard-header {
		flex-direction: column;
		align-items: flex-start;
		gap: 16px;
		margin-bottom: 20px;
	}
	
	.kboard-header h1 {
		font-size: 22px;
	}
	
	.kboard-header h1 .dashicons {
		font-size: 24px !important;
		width: 24px !important;
		height: 24px !important;
	}
	
	.kboard-header-actions {
		flex-wrap: wrap;
		gap: 8px;
	}
	
	.kboard-action-link {
		padding: 6px 12px;
		font-size: 13px;
	}
	
	.kboard-dashboard-wrapper .welcome-panel {
		padding: 16px;
		margin-bottom: 20px;
	}
	
	.kboard-section-title {
		font-size: 18px;
		margin: 0 0 16px 0;
	}
	
	.kboard-section-title::before {
		height: 20px;
		margin-right: 10px;
	}
	
	.kboard-settings-grid {
		grid-template-columns: 1fr;
		gap: 16px;
	}
	
	.kboard-card {
		border-radius: 10px;
	}
	
	.kboard-card:hover {
		transform: none;
	}
	
	.kboard-card-header {
		padding: 16px;
		flex-wrap: wrap;
		gap: 8px;
	}
	
	.kboard-card-title {
		font-size: 15px;
	}
	
	.kboard-badge {
		font-size: 11px;
		padding: 3px 8px;
	}
	
	.kboard-card-body {
		padding: 16px;
	}
	
	.kboard-description {
		font-size: 13px;
		line-height: 1.5;
	}
	
	.kboard-card-footer {
		padding: 14px 16px;
	}
	
	.kboard-form-control {
		font-size: 16px; /* Prevents zoom on iOS */
		padding: 10px 12px;
	}
	
	.kboard-btn {
		padding: 12px 16px;
		font-size: 14px;
	}
	
	.kboard-input-group {
		gap: 10px;
	}
}

@media (max-width: 480px) {
	.kboard-dashboard-wrapper {
		margin: 5px;
	}
	
	.kboard-header h1 {
		font-size: 20px;
	}
	
	.kboard-header-actions {
		width: 100%;
	}
	
	.kboard-action-link {
		flex: 1;
		text-align: center;
		padding: 8px 10px;
		font-size: 12px;
	}
	
	.kboard-dashboard-wrapper .welcome-panel {
		padding: 12px;
	}
	
	.kboard-section-title {
		font-size: 16px;
	}
	
	.kboard-card-header {
		padding: 14px;
	}
	
	.kboard-card-body {
		padding: 14px;
	}
	
	.kboard-card-footer {
		padding: 12px 14px;
	}
}

</style>

<div class="wrap kboard-dashboard-wrapper">
	<h1>
		<span class="dashicons dashicons-dashboard" style="font-size: 32px; width: 32px; height: 32px;"></span>
		<?php echo __('KBoard : 대시보드', 'kboard')?>
	</h1>
	
	<!-- Header -->
	<div class="kboard-header">
		<div class="kboard-header-actions">
			<a href="https://www.cosmosfarm.com" class="kboard-action-link" onclick="window.open(this.href);return false;"><?php echo __('Home', 'kboard')?></a>
			<a href="https://www.cosmosfarm.com/threads" class="kboard-action-link" onclick="window.open(this.href);return false;"><?php echo __('Community', 'kboard')?></a>
			<a href="https://www.cosmosfarm.com/support" class="kboard-action-link" onclick="window.open(this.href);return false;"><?php echo __('Support', 'kboard')?></a>
			<a href="https://blog.cosmosfarm.com" class="kboard-action-link" onclick="window.open(this.href);return false;"><?php echo __('Blog', 'kboard')?></a>
		</div>
	</div>

	<div id="welcome-panel" class="welcome-panel">
		<?php include 'welcome.php'?>
	</div>

	<!-- System Settings -->
	<h2 class="kboard-section-title"><?php echo __('시스템 설정', 'kboard')?></h2>
	<?php
	global $wpdb;
	$kboard_use_search_index = kboard_use_search_index();
	$kboard_search_index_total = intval($wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content`"));
	$kboard_search_index_indexed = intval($wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT `content_uid`) FROM `{$wpdb->prefix}kboard_search_document` WHERE `index_version`=%d", KBSearchEngine::INDEX_VERSION)));
	$kboard_search_index_remaining = max(0, $kboard_search_index_total - $kboard_search_index_indexed);
	$kboard_search_index_completed = $kboard_use_search_index && ($kboard_search_index_total == 0 || $kboard_search_index_remaining == 0);

	$kboard_svg_scan_pending = (get_option('kboard_svg_batch_scan_pending') == KBOARD_VERSION);
	$kboard_svg_scan_running = intval(get_option('kboard_svg_batch_scan_running'));
	$kboard_svg_scan_is_running = $kboard_svg_scan_running && (time() - $kboard_svg_scan_running) < 120;
	$kboard_svg_scan_result = get_option('kboard_svg_batch_scan_result');
	$kboard_svg_restore_manifest = get_option('kboard_svg_batch_restore_manifest');
	$kboard_svg_restore_result = get_option('kboard_svg_batch_restore_result');
	$kboard_svg_scan_has_quarantined_files = is_array($kboard_svg_scan_result) && isset($kboard_svg_scan_result['version']) && $kboard_svg_scan_result['version'] == KBOARD_VERSION && intval($kboard_svg_scan_result['quarantined']) > 0;
	$kboard_svg_restore_available = function_exists('kboard_svg_batch_manifest_has_targets') ? kboard_svg_batch_manifest_has_targets($kboard_svg_restore_manifest) : false;
	$kboard_svg_restore_target_count = 0;
	if(is_array($kboard_svg_restore_manifest)){
		$kboard_svg_restore_attached_uids = isset($kboard_svg_restore_manifest['attached_uids']) ? $kboard_svg_restore_manifest['attached_uids'] : array();
		$kboard_svg_restore_media_uids = isset($kboard_svg_restore_manifest['media_uids']) ? $kboard_svg_restore_manifest['media_uids'] : array();
		$kboard_svg_restore_thumbnail_content_uids = isset($kboard_svg_restore_manifest['thumbnail_content_uids']) ? $kboard_svg_restore_manifest['thumbnail_content_uids'] : array();

		if(!is_array($kboard_svg_restore_attached_uids)){
			$kboard_svg_restore_attached_uids = array($kboard_svg_restore_attached_uids);
		}
		if(!is_array($kboard_svg_restore_media_uids)){
			$kboard_svg_restore_media_uids = array($kboard_svg_restore_media_uids);
		}
		if(!is_array($kboard_svg_restore_thumbnail_content_uids)){
			$kboard_svg_restore_thumbnail_content_uids = array($kboard_svg_restore_thumbnail_content_uids);
		}

		$kboard_svg_restore_target_count += count($kboard_svg_restore_attached_uids);
		$kboard_svg_restore_target_count += count($kboard_svg_restore_media_uids);
		$kboard_svg_restore_target_count += count($kboard_svg_restore_thumbnail_content_uids);
	}
	$kboard_svg_scan_retry_blocked = !$kboard_svg_scan_pending && !$kboard_svg_scan_is_running && $kboard_svg_scan_has_quarantined_files && (!is_array($kboard_svg_restore_result) || empty($kboard_svg_restore_result['completed_at']));
	$kboard_svg_scan_i18n = array(
		'running' => __('Running', 'kboard'),
		'pending' => __('Pending', 'kboard'),
		'completed' => __('Completed', 'kboard'),
		'retry_button' => __('Run SVG cleanup again', 'kboard'),
		'restore_button' => __('Restore quarantined SVG files', 'kboard'),
		'failed' => __('SVG cleanup failed.', 'kboard'),
		'restore_failed' => __('SVG restore failed.', 'kboard'),
		'result_template' => __('Last result: checked %1$s, sanitized %2$s, quarantined %3$s, errors %4$s', 'kboard'),
		'quarantined_files_template' => __('Quarantined files: %s', 'kboard'),
		'restored_files_template' => __('Restored files: %s', 'kboard'),
		'restore_result_template' => __('Restore result: total %1$s, restored %2$s, conflicts %3$s, errors %4$s', 'kboard'),
		'scan_confirm' => __('Running SVG cleanup may disable suspicious SVG files, and the previous restore target list will be overwritten by this run. Do you want to continue?', 'kboard'),
		'restore_confirm' => __('Do you want to restore the files quarantined by the last SVG cleanup run?', 'kboard'),
	);
	?>

	<div class="kboard-settings-grid">
		
		<div class="kboard-card" id="kboard_svg_batch_scan">
			<form method="post" onsubmit="return kboard_svg_batch_scan_execute(this)">
				<input type="hidden" name="action" value="kboard_svg_batch_scan_execute">
				<input type="hidden" name="kboard-svg-batch-scan-nonce" value="<?php echo esc_attr(wp_create_nonce('kboard-svg-batch-scan'))?>">
				<input type="hidden" name="kboard-svg-batch-restore-nonce" value="<?php echo esc_attr(wp_create_nonce('kboard-svg-batch-restore'))?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">SVG 정리 실행</h3>
					<span class="kboard-badge <?php echo $kboard_svg_scan_pending || $kboard_svg_scan_is_running ? 'warning' : ($kboard_svg_scan_result ? 'active' : 'inactive')?>" data-kboard-svg-scan-badge><?php
						if($kboard_svg_scan_is_running) echo __('Running', 'kboard');
						else if($kboard_svg_scan_pending) echo __('Pending', 'kboard');
						else if($kboard_svg_scan_result) echo __('Completed', 'kboard');
						else echo __('Idle', 'kboard');
					?></span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						업데이트 후 기존 업로드 경로의 SVG 파일을 한 번 점검해서 위험 파일은 격리하고, 정제 가능한 파일은 sanitize 합니다.<br>
						실행 시 의심되는 SVG 파일이 사용불가가 됩니다.<br><br>
						<?php if($kboard_svg_scan_pending):?>
							현재 버전에서는 한 번 실행이 필요합니다. 관리자 최초 진입 후 이 카드에서 직접 실행해주세요.
						<?php elseif($kboard_svg_scan_is_running):?>
							현재 다른 관리자 요청에서 SVG 정리 작업이 실행 중입니다. 잠시 후 새로고침 해주세요.
						<?php elseif(is_array($kboard_svg_scan_result) && isset($kboard_svg_scan_result['version']) && $kboard_svg_scan_result['version'] == KBOARD_VERSION):?>
							마지막 결과: 검사 <?php echo intval($kboard_svg_scan_result['checked'])?>개, 정제 <?php echo intval($kboard_svg_scan_result['sanitized'])?>개, 격리 <?php echo intval($kboard_svg_scan_result['quarantined'])?>개, 오류 <?php echo intval($kboard_svg_scan_result['errors'])?>개
							<?php if($kboard_svg_restore_available):?><br><br>마지막 정리 작업에서 격리된 SVG 파일 <?php echo intval($kboard_svg_restore_target_count)?>개를 복원할 수 있습니다.<?php endif?>
							<?php if($kboard_svg_scan_retry_blocked):?><br><br><?php echo __('Quarantined SVG files remain. Restore them or verify them manually before running SVG cleanup again.', 'kboard')?><?php endif?>
							<?php if(is_array($kboard_svg_restore_result) && !empty($kboard_svg_restore_result['completed_at'])):?>
								<br><br>마지막 복원 결과: 전체 <?php echo intval($kboard_svg_restore_result['total'])?>개, 복원 <?php echo intval($kboard_svg_restore_result['restored'])?>개, 충돌 <?php echo intval($kboard_svg_restore_result['conflicts'])?>개, 오류 <?php echo intval($kboard_svg_restore_result['errors'])?>개
							<?php endif?>
						<?php else:?>
							필요할 때 수동으로 다시 실행할 수 있습니다.
						<?php endif?>
					</p>
				</div>
				<div class="kboard-card-footer">
					<?php if(!$kboard_svg_scan_retry_blocked):?>
					<button type="submit" class="kboard-btn full-width primary" data-kboard-svg-scan-button<?php if($kboard_svg_scan_is_running || $kboard_svg_scan_retry_blocked):?> disabled<?php endif?>>
						<?php echo $kboard_svg_scan_pending ? __('Run SVG cleanup', 'kboard') : __('Run SVG cleanup again', 'kboard')?>
					</button>
					<?php endif?>
					<?php if($kboard_svg_restore_available):?>
					<button type="button" class="kboard-btn full-width" data-kboard-svg-restore-button onclick="return kboard_svg_batch_restore_execute(this.form)">
						<?php echo __('Restore quarantined SVG files', 'kboard')?>
					</button>
					<?php endif?>
				</div>
			</form>
		</div>
		
		<!-- XSS Filter -->
		<div class="kboard-card" id="kboard_xssfilter">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_xssfilter]" value="<?php echo get_option('kboard_xssfilter')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">XSS 해킹 차단</h3>
					<span class="kboard-badge <?php echo get_option('kboard_xssfilter')?'warning':'active'?>">
						<?php echo get_option('kboard_xssfilter')?'비활성':'보호중'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						<?php echo get_option('kboard_xssfilter')?'해킹 차단 옵션이 비활성화 되어 있습니다. 서버에 ModSecurity등의 방화벽이 있다면 이 옵션을 끌 수 있습니다.':'해킹으로 부터 보호되고 있습니다.'?><br><br>
						서버와 네트워크에 방화벽 사용을 권장합니다. 옵션을 비활성화 하면 시스템 속도가 빨라집니다.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width <?php echo get_option('kboard_xssfilter')?'primary':''?>">
						<?php echo get_option('kboard_xssfilter')?'XSS공격 차단 활성화':'XSS공격 차단 비활성화'?>
					</button>
				</div>
			</form>
		</div>

		<!-- Font Awesome -->
		<div class="kboard-card" id="kboard_fontawesome">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_fontawesome]" value="<?php echo get_option('kboard_fontawesome')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">Font Awesome</h3>
					<span class="kboard-badge <?php echo get_option('kboard_fontawesome')?'inactive':'active'?>">
						<?php echo get_option('kboard_fontawesome')?'사용안함':'사용중'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						KBoard의 게시판 스킨에 사용되는 오픈소스 아이콘 폰트입니다.<br>
						테마의 레이아웃이 깨지거나 타 플러그인과 충돌 발생 시 이 옵션을 비활성화하세요.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width <?php echo get_option('kboard_fontawesome')?'primary':''?>">
						<?php echo get_option('kboard_fontawesome')?'Font Awesome 활성화':'Font Awesome 비활성화'?>
					</button>
				</div>
			</form>
		</div>

		<!-- Attached Copy Download -->
		<div class="kboard-card" id="kboard_attached_copy_download">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_attached_copy_download]" value="<?php echo get_option('kboard_attached_copy_download')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">첨부파일 깨짐 방지</h3>
					<span class="kboard-badge <?php echo get_option('kboard_attached_copy_download')?'active':'inactive'?>">
						<?php echo get_option('kboard_attached_copy_download')?'활성':'기본'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						다운로드 받은 첨부파일이 깨져 보인다면 활성화하세요.<br>
						시스템 성능이 저하될 수 있으니 서버에 첨부파일 MIME Type 설정을 추가하는 것을 권장합니다.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width">
						<?php echo get_option('kboard_attached_copy_download')?'깨짐 방지 비활성화':'깨짐 방지 활성화'?>
					</button>
				</div>
			</form>
		</div>

		<!-- Download Method -->
		<div class="kboard-card" id="kboard_attached_open_browser">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_attached_open_browser]" value="<?php echo get_option('kboard_attached_open_browser')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">다운로드 방식</h3>
					<span class="kboard-badge <?php echo get_option('kboard_attached_open_browser')?'active':'inactive'?>">
						<?php echo get_option('kboard_attached_open_browser')?'브라우저':'PC저장'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						기본적으로는 파일을 PC에 저장합니다.<br>
						이 옵션을 켜면 가능한 경우 브라우저에서 즉시 내용을 읽을 수 있습니다.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width">
						방식 변경 (<?php echo get_option('kboard_attached_open_browser')?'PC저장':'브라우저 읽기'?>)
					</button>
				</div>
			</form>
		</div>

		<!-- File Size Limit -->
		<div class="kboard-card" id="kboard_limit_file_size">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">첨부파일 용량 제한</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						서버 최대 크기: <strong><?php echo kboard_upload_max_size()?></strong> Byte<br>
						<a href="https://search.naver.com/search.naver?query=<?php echo kboard_upload_max_size()?>+%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%A9%94%EA%B0%80%EB%B0%94%EC%9D%B4%ED%8A%B8+%EB%B3%80%ED%99%98" onclick="window.open(this.href);return false;">단위변환 보기</a>
					</p>
					<div class="kboard-input-group mt-4">
						<input type="number" class="kboard-form-control" name="option[kboard_limit_file_size]" value="<?php echo kboard_limit_file_size()?>" placeholder="Byte 단위 입력">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">용량 설정 변경</button>
				</div>
			</form>
		</div>

		<!-- File Extensions -->
		<div class="kboard-card" id="kboard_allow_file_extensions">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">허용 확장자 제한</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						보안을 위해 허용할 파일의 확장자를 콤마(,)로 구분해서 입력해주세요.
					</p>
					<div class="kboard-input-group mt-4">
						<input type="text" class="kboard-form-control" name="option[kboard_allow_file_extensions]" value="<?php echo kboard_allow_file_extensions()?>">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">확장자 업데이트</button>
				</div>
			</form>
		</div>

		<!-- New Document Notify -->
		<div class="kboard-card" id="kboard_new_document_notify_time">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">새글 알림(New) 시간</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						리스트에서 지정된 시간 이내 등록된 글에 [New] 표시를 합니다.<br>
						(일부 스킨 제외)
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_new_document_notify_time]" class="kboard-form-control">
							<option value="1">비활성화</option>
							<option value="3600"<?php if(kboard_new_document_notify_time() == '3600'):?> selected<?php endif?>>1시간</option>
							<option value="10800"<?php if(kboard_new_document_notify_time() == '10800'):?> selected<?php endif?>>3시간</option>
							<option value="21600"<?php if(kboard_new_document_notify_time() == '21600'):?> selected<?php endif?>>6시간</option>
							<option value="43200"<?php if(kboard_new_document_notify_time() == '43200'):?> selected<?php endif?>>12시간</option>
							<option value="86400"<?php if(kboard_new_document_notify_time() == '86400'):?> selected<?php endif?>>하루</option>
							<option value="172800"<?php if(kboard_new_document_notify_time() == '172800'):?> selected<?php endif?>>2일</option>
							<option value="259200"<?php if(kboard_new_document_notify_time() == '259200'):?> selected<?php endif?>>3일</option>
							<option value="345600"<?php if(kboard_new_document_notify_time() == '345600'):?> selected<?php endif?>>4일</option>
							<option value="432000"<?php if(kboard_new_document_notify_time() == '432000'):?> selected<?php endif?>>5일</option>
							<option value="518400"<?php if(kboard_new_document_notify_time() == '518400'):?> selected<?php endif?>>6일</option>
							<option value="604800"<?php if(kboard_new_document_notify_time() == '604800'):?> selected<?php endif?>>1주일</option>
						</select>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">설정 변경</button>
				</div>
			</form>
		</div>

		<!-- Captcha Stop -->
		<div class="kboard-card" id="kboard_captcha_stop">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_captcha_stop]" value="<?php echo get_option('kboard_captcha_stop')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">비로그인 CAPTCHA</h3>
					<span class="kboard-badge <?php echo get_option('kboard_captcha_stop')?'inactive':'active'?>">
						<?php echo get_option('kboard_captcha_stop')?'중지됨':'사용중'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						CAPTCHA는 스팸을 차단하는 기능입니다.<br>
						기능을 중지하면 스팸이 등록될 확률이 높아집니다.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width <?php echo get_option('kboard_captcha_stop')?'primary':''?>">
						<?php echo get_option('kboard_captcha_stop')?'CAPTCHA 사용하기':'CAPTCHA 중지하기'?>
					</button>
				</div>
			</form>
		</div>

		<!-- Google reCAPTCHA -->
		<div class="kboard-card" id="kboard_recaptcha">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">구글 reCAPTCHA 설정</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						v2/v3 타입을 선택하고 해당 타입의 Site key/Secret key를 입력하면 내장 CAPTCHA 대신 구글 reCAPTCHA를 사용합니다.<br>
						비활성화 선택 시 구글 reCAPTCHA를 사용하지 않고 KBoard 내장 CAPTCHA를 사용합니다.<br>
						v3 사용 시에는 반드시 v3 키를 입력해 주세요.<br>
						<a href="https://www.google.com/recaptcha/admin" target="_blank">키 발급받기</a>
					</p>
					<div class="kboard-input-group mt-4">
						<select class="kboard-form-control" name="option[kboard_recaptcha_type]">
							<option value=""<?php if(kboard_recaptcha_type() == 'disabled'):?> selected<?php endif?>>비활성화</option>
							<option value="v2"<?php if(kboard_recaptcha_type() == 'v2'):?> selected<?php endif?>>reCAPTCHA v2 (Checkbox)</option>
							<option value="v3"<?php if(kboard_recaptcha_type() == 'v3'):?> selected<?php endif?>>reCAPTCHA v3 (Score based)</option>
						</select>
						<input type="text" class="kboard-form-control" name="option[kboard_recaptcha_site_key]" value="<?php echo get_option('kboard_recaptcha_site_key')?>" placeholder="Site key">
						<input type="text" class="kboard-form-control" name="option[kboard_recaptcha_secret_key]" value="<?php echo get_option('kboard_recaptcha_secret_key')?>" placeholder="Secret key">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">reCAPTCHA 정보 업데이트</button>
				</div>
			</form>
		</div>

		<!-- Custom CSS -->
		<div class="kboard-card" id="kboard_custom_css">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">커스텀 CSS</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						스킨 파일 수정 없이 디자인을 추가할 수 있습니다.
					</p>
					<div class="kboard-input-group mt-4">
						<textarea class="kboard-form-control" name="option[kboard_custom_css]" rows="4"><?php echo get_option('kboard_custom_css')?></textarea>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">CSS 업데이트</button>
				</div>
			</form>
		</div>

		<!-- Iframe Whitelist -->
		<div class="kboard-card" id="kboard_iframe_whitelist">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">아이프레임 화이트리스트</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						게시글 작성 시 허용할 아이프레임 도메인을 한 줄씩 입력하세요.
					</p>
					<div class="kboard-input-group mt-4">
						<textarea class="kboard-form-control" name="option[kboard_iframe_whitelist]" rows="4"><?php echo kboard_iframe_whitelist()?></textarea>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">리스트 업데이트</button>
				</div>
			</form>
		</div>

		<!-- Name Filter -->
		<div class="kboard-card" id="kboard_name_filter">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">작성자 금지단어</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						작성자 이름으로 사용할 수 없는 단어를 콤마(,)로 구분해 입력하세요.
					</p>
					<div class="kboard-input-group mt-4">
						<textarea class="kboard-form-control" name="option[kboard_name_filter]"><?php echo kboard_name_filter()?></textarea>
						<input type="text" class="kboard-form-control" name="option[kboard_name_filter_message]" value="<?php echo get_option('kboard_name_filter_message', '')?>" placeholder="<?php echo __('%s is not available.', 'kboard')?>">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- Content Filter -->
		<div class="kboard-card" id="kboard_content_filter">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">본문/제목/댓글 금지단어</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						사용할 수 없는 단어를 콤마(,)로 구분해 입력하세요.
					</p>
					<div class="kboard-input-group mt-4">
						<textarea class="kboard-form-control" name="option[kboard_content_filter]"><?php echo kboard_content_filter()?></textarea>
						<input type="text" class="kboard-form-control" name="option[kboard_content_filter_message]" value="<?php echo get_option('kboard_content_filter_message', '')?>" placeholder="<?php echo __('%s is not available.', 'kboard')?>">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- Delete Immediately -->
		<div class="kboard-card" id="kboard_content_delete_immediately">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="option[kboard_content_delete_immediately]" value="<?php echo get_option('kboard_content_delete_immediately')?'':'1'?>">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">게시글 삭제 방식</h3>
					<span class="kboard-badge <?php echo get_option('kboard_content_delete_immediately')?'warning':'active'?>">
						<?php echo get_option('kboard_content_delete_immediately')?'바로삭제':'휴지통'?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						기본적으로 삭제 시 휴지통으로 이동합니다.<br>
						바로 삭제 기능을 켜면 복구할 수 없습니다.
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn full-width">
						<?php echo get_option('kboard_content_delete_immediately')?'바로 삭제 비활성화 (휴지통 사용)':'바로 삭제 활성화'?>
					</button>
				</div>
			</form>
		</div>

		<!-- Naver API -->
		<div class="kboard-card" id="kboard_naver_api_setting">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">네이버 API</h3>
				</div>
				<div class="kboard-card-body">
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_naver_api_client_id]" value="<?php echo get_option('kboard_naver_api_client_id')?>" placeholder="Client ID">
						<input type="text" class="kboard-form-control" name="option[kboard_naver_api_client_secret]" value="<?php echo get_option('kboard_naver_api_client_secret')?>" placeholder="Client Secret">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- Kakao API -->
		<div class="kboard-card" id="kboard_kakao_api_setting">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">카카오 API</h3>
				</div>
				<div class="kboard-card-body">
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_kakao_api_rest_key]" value="<?php echo get_option('kboard_kakao_api_rest_key')?>" placeholder="REST API 키">
						<input type="text" class="kboard-form-control" name="option[kboard_kakao_api_javascript_key]" value="<?php echo get_option('kboard_kakao_api_javascript_key')?>" placeholder="JavaScript 키">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- Google API -->
		<div class="kboard-card" id="kboard_google_api_setting">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">구글 API</h3>
				</div>
				<div class="kboard-card-body">
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_google_api_key]" value="<?php echo get_option('kboard_google_api_key')?>" placeholder="API 키">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>
		
		<!-- Public Data Portal API -->
		<div class="kboard-card" id="kboard_public_data_portal_api_setting">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">공공데이터포털 API</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description mb-2">
						<a href="https://www.data.go.kr" target="_blank">공공데이터포털</a>에서 발급받은 서비스 키를 입력하세요.
					</p>
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_public_data_portal_api_key]" value="<?php echo esc_attr(get_option('kboard_public_data_portal_api_key'))?>" placeholder="서비스 키">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>
		
		<!-- PG: KG Inicis -->
		<div class="kboard-card" id="kboard_builtin_pg_inicis">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">PG: KG이니시스</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description mb-2">
						<a href="https://www.funnelmoa.com/pg/?ref=kboard_to_funnelmoa_pg&utm_campaign=kboard_to_funnelmoa_pg&utm_source=wordpress&utm_medium=referral" target="_blank">PG 가입하기</a> | <a href="https://blog.cosmosfarm.com/?p=1209" target="_blank">Key 조회방법</a>
					</p>
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_builtin_pg_inicis_general_mid]" value="<?php echo get_option('kboard_builtin_pg_inicis_general_mid')?>" placeholder="상점아이디(MID)">
						<input type="text" class="kboard-form-control" name="option[kboard_builtin_pg_inicis_general_sign_key]" value="<?php echo get_option('kboard_builtin_pg_inicis_general_sign_key')?>" placeholder="Sign Key">
						<input type="text" class="kboard-form-control" name="option[kboard_builtin_pg_inicis_general_api_key]" value="<?php echo get_option('kboard_builtin_pg_inicis_general_api_key')?>" placeholder="API Key">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- PG: Nicepay -->
		<div class="kboard-card" id="kboard_builtin_pg_nicepay">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">PG: 나이스페이</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description mb-2">
						<a href="https://www.funnelmoa.com/pg/?ref=kboard_to_funnelmoa_pg&utm_campaign=kboard_to_funnelmoa_pg&utm_source=wordpress&utm_medium=referral" target="_blank">PG 가입하기</a>
					</p>
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_builtin_pg_nicepay_general_mid]" value="<?php echo get_option('kboard_builtin_pg_nicepay_general_mid')?>" placeholder="상점아이디(MID)">
						<input type="text" class="kboard-form-control" name="option[kboard_builtin_pg_nicepay_general_merchant_key]" value="<?php echo get_option('kboard_builtin_pg_nicepay_general_merchant_key')?>" placeholder="Merchant Key">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- PG: Iamport -->
		<div class="kboard-card" id="kboard_iamport">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">PG: 포트원(아임포트)</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description mb-2">
						기술지원은 KG이니시스/나이스페이 권장.<br>
						<a href="https://admin.iamport.kr/settings" target="_blank">시스템설정 정보 확인</a>
					</p>
					<div class="kboard-input-group">
						<input type="text" class="kboard-form-control" name="option[kboard_iamport_id]" value="<?php echo get_option('kboard_iamport_id')?>" placeholder="가맹점 식별코드">
						<input type="text" class="kboard-form-control" name="option[kboard_iamport_api_key]" value="<?php echo get_option('kboard_iamport_api_key')?>" placeholder="REST API 키">
						<input type="text" class="kboard-form-control" name="option[kboard_iamport_api_secret]" value="<?php echo get_option('kboard_iamport_api_secret')?>" placeholder="REST API secret">
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">업데이트</button>
				</div>
			</form>
		</div>

		<!-- Image Optimize -->
		<div class="kboard-card" id="kboard_image_optimize">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">이미지 최적화</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						업로드되는 이미지를 최적화하여 서버 용량을 절약합니다. (jpg, png)
					</p>
					<div class="kboard-input-group mt-2">
						<div style="display:flex; gap:5px; align-items:center;">
							<input type="text" class="kboard-form-control" name="option[kboard_image_optimize_width]" value="<?php echo get_option('kboard_image_optimize_width')?>" placeholder="width" style="width:80px">
							<span>x</span>
							<input type="text" class="kboard-form-control" name="option[kboard_image_optimize_height]" value="<?php echo get_option('kboard_image_optimize_height')?>" placeholder="height" style="width:80px"> px
						</div>
						<div style="display:flex; gap:5px; align-items:center;">
							<input type="text" class="kboard-form-control" name="option[kboard_image_optimize_quality]" value="<?php echo get_option('kboard_image_optimize_quality')?>" placeholder="1-100" style="width:80px"> % (퀄리티)
						</div>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">설정 저장</button>
				</div>
			</form>
		</div>

		<!-- Prevent Copy -->
		<div class="kboard-card">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">복사 방지 스크립트</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						KBoard가 있는 페이지에서 우클릭/드래그를 방지합니다.
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_prevent_copy]" class="kboard-form-control">
							<option value="">비활성화</option>
							<option value="1"<?php if(get_option('kboard_prevent_copy') == '1'):?> selected<?php endif?>>복사 방지</option>
							<option value="2"<?php if(get_option('kboard_prevent_copy') == '2'):?> selected<?php endif?>>드래그, 우클릭 방지</option>
							<option value="3"<?php if(get_option('kboard_prevent_copy') == '3'):?> selected<?php endif?>>드래그, 우클릭, 복사 방지</option>
						</select>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">적용</button>
				</div>
			</form>
		</div>

		<!-- Search Member Display -->
		<div class="kboard-card">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">전체 검색시 작성자 포함</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						활성화 시: 제목+내용+작성자 검색<br>
						비활성화 시: 제목+내용 검색
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_search_include_member_display]" class="kboard-form-control">
							<option value="">비활성화</option>
							<option value="1"<?php if(get_option('kboard_search_include_member_display') == '1'):?> selected<?php endif?>>활성화</option>
						</select>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">적용</button>
				</div>
			</form>
		</div>

		<!-- Search OR Operator -->
		<div class="kboard-card">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">더 많은 게시글 검색 (OR)</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						키워드 공백 기준 OR 검색을 시도합니다.<br>
						(속도가 느려질 수 있습니다)
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_search_auto_operator_or]" class="kboard-form-control">
							<option value="">비활성화</option>
							<option value="1"<?php if(get_option('kboard_search_auto_operator_or') == '1'):?> selected<?php endif?>>활성화</option>
						</select>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">적용</button>
				</div>
			</form>
		</div>

		<!-- Search Engine Read -->
		<div class="kboard-card">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				
				<div class="kboard-card-header">
					<h3 class="kboard-card-title">검색엔진 항상 읽기 가능</h3>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						검색엔진은 권한 체크 없이 내용을 읽을 수 있습니다.<br>
						보안상 비활성화를 권장합니다.
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_allow_search_engines_always_read]" class="kboard-form-control">
							<option value="">비활성화</option>
							<option value="1"<?php if(get_option('kboard_allow_search_engines_always_read') == '1'):?> selected<?php endif?>>활성화</option>
						</select>
					</div>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width">적용</button>
				</div>
			</form>
		</div>
		
		<!-- Search Index -->
		<div class="kboard-card" id="kboard_search_index">
			<form method="post" onsubmit="return kboard_system_option_update(this)">
				<input type="hidden" name="action" value="kboard_system_option_update">
				<input type="hidden" name="kboard-search-reindex-batch-nonce" value="<?php echo esc_attr(wp_create_nonce('kboard-search-reindex-batch'))?>">
				<input type="hidden" name="kboard-search-index-enabled" value="<?php echo $kboard_use_search_index?'1':'0'?>">
				<input type="hidden" name="kboard-search-reindex-limit" value="500">

				<div class="kboard-card-header">
					<h3 class="kboard-card-title">검색 기능 개선 적용(베타)</h3>
					<span class="kboard-badge <?php echo !$kboard_use_search_index ? 'inactive' : ($kboard_search_index_completed ? 'active' : 'warning')?>" data-kboard-search-index-badge>
						<?php
						if(!$kboard_use_search_index) echo '비활성';
						else if($kboard_search_index_completed) echo '완료';
						else echo '활성';
						?>
					</span>
				</div>
				<div class="kboard-card-body">
					<p class="kboard-description">
						게시글 검색 속도를 높이기 위해 FULLTEXT 검색 인덱스를 사용합니다.<br>
						활성화하면 별도의 검색용 테이블을 생성하여 검색 성능을 개선합니다.<br>
						<strong>검색엔진 항상 읽기 가능</strong> 옵션은 크롤러 접근 설정이며, 이 인덱스 활성화와는 별개입니다.<br><br>
						<strong style="color:#c62828">⚠ 서버 리소스 안내</strong><br>
						게시글이 많을수록(수만 건 이상) 검색 인덱스 테이블의 크기가 커지며, 서버 디스크 공간과 메모리(RAM)를 추가로 사용합니다.<br>
						게시글 작성/수정 시 인덱스를 갱신하므로 쓰기 성능에 약간의 부하가 발생할 수 있습니다.<br>
						서버 사양이 낮거나 공유 호스팅 환경에서는 비활성화를 권장합니다.
					</p>
					<div class="kboard-input-group mt-4">
						<select name="option[kboard_use_search_index]" class="kboard-form-control" data-kboard-search-index-select>
							<option value="">비활성화</option>
							<option value="1"<?php if($kboard_use_search_index):?> selected<?php endif?>>활성화</option>
						</select>
					</div>
					<p class="kboard-description mt-2" data-kboard-search-reindex-progress>
						<?php if($kboard_use_search_index):?>처리 <?php echo intval($kboard_search_index_indexed)?>개 / 전체 <?php echo intval($kboard_search_index_total)?>개 / 남음 <?php echo intval($kboard_search_index_remaining)?>개<?php else:?>검색 인덱스를 활성화하면 전체 재인덱싱을 실행할 수 있습니다.<?php endif?>
					</p>
				</div>
				<div class="kboard-card-footer">
					<button type="submit" class="kboard-btn primary full-width" data-kboard-search-index-apply-button>적용</button>
					<button type="button" class="kboard-btn full-width primary" data-kboard-search-reindex-button onclick="return kboard_search_reindex_batch_execute(this.form)"<?php if(!$kboard_use_search_index):?> disabled<?php endif?>>
						전체 재인덱싱 실행
					</button>
				</div>
			</form>
		</div>

	</div> <!-- End Grid -->
</div>

<script>
var kboard_svg_scan_i18n = <?php echo wp_json_encode($kboard_svg_scan_i18n)?>;

function kboard_svg_scan_sprintf(template, args){
	if(!template){
		return '';
	}
	for(var i=0; i<args.length; i++){
		var index = i + 1;
		template = template.replace(new RegExp('%' + index + '\\$s', 'g'), args[i]);
		template = template.replace('%s', args[i]);
	}
	return template;
}

function kboard_system_option_update(form){
	var data = jQuery(form).serializeArray();
	data.push({name:'security', value:<?php echo wp_json_encode(wp_create_nonce('kboard-system-option-update'))?>});
	jQuery.post(ajaxurl, data, function(res){
		window.location.reload();
	});
	return false;
}

function kboard_svg_batch_scan_execute(form){
	var button = jQuery(form).find('[data-kboard-svg-scan-button]');
	var badge = jQuery(form).find('[data-kboard-svg-scan-badge]');
	if(!confirm(kboard_svg_scan_i18n.scan_confirm)){
		return false;
	}
	button.prop('disabled', true);
	jQuery(form).find('[data-kboard-svg-restore-button]').prop('disabled', true);
	badge.removeClass('active inactive').addClass('warning').text(kboard_svg_scan_i18n.running);
	jQuery.post(ajaxurl, jQuery(form).serialize(), function(res){
		if(res && res.result == 'success'){
			var data = res.data || {};
			var message = kboard_svg_scan_sprintf(kboard_svg_scan_i18n.result_template, [
				(data.checked || 0),
				(data.sanitized || 0),
				(data.quarantined || 0),
				(data.errors || 0)
			]);
			if(data.completed_at){
				message += ' (' + data.completed_at + ')';
			}
			if(data.quarantined_files && data.quarantined_files.length){
				message += '\n' + kboard_svg_scan_sprintf(kboard_svg_scan_i18n.quarantined_files_template, [data.quarantined_files.join(', ')]);
			}
			badge.removeClass('warning inactive').addClass('active').text(kboard_svg_scan_i18n.completed);
			button.text(kboard_svg_scan_i18n.retry_button).prop('disabled', false);
			alert(message);
			window.location.reload();
			return;
		}
		button.prop('disabled', false);
		jQuery(form).find('[data-kboard-svg-restore-button]').prop('disabled', false);
		badge.removeClass('active inactive').addClass('warning').text(kboard_svg_scan_i18n.pending);
		alert(res && res.message ? res.message : kboard_svg_scan_i18n.failed);
	}).fail(function(){
		button.prop('disabled', false);
		jQuery(form).find('[data-kboard-svg-restore-button]').prop('disabled', false);
		badge.removeClass('active inactive').addClass('warning').text(kboard_svg_scan_i18n.pending);
		alert(kboard_svg_scan_i18n.failed);
	});
	return false;
}

function kboard_svg_batch_restore_execute(form){
	var restoreButton = jQuery(form).find('[data-kboard-svg-restore-button]');
	var scanButton = jQuery(form).find('[data-kboard-svg-scan-button]');
	if(!confirm(kboard_svg_scan_i18n.restore_confirm)){
		return false;
	}
	restoreButton.prop('disabled', true);
	scanButton.prop('disabled', true);
	jQuery.post(ajaxurl, {
		action: 'kboard_svg_batch_restore_execute',
		'kboard-svg-batch-restore-nonce': jQuery(form).find('input[name=\"kboard-svg-batch-restore-nonce\"]').val()
	}, function(res){
		if(res && res.result == 'success'){
			var data = res.data || {};
			var message = kboard_svg_scan_sprintf(kboard_svg_scan_i18n.restore_result_template, [
				(data.total || 0),
				(data.restored || 0),
				(data.conflicts || 0),
				(data.errors || 0)
			]);
			if(data.completed_at){
				message += ' (' + data.completed_at + ')';
			}
			if(data.restored_files && data.restored_files.length){
				message += '\n' + kboard_svg_scan_sprintf(kboard_svg_scan_i18n.restored_files_template, [data.restored_files.join(', ')]);
			}
			alert(message);
			window.location.reload();
			return;
		}
		restoreButton.prop('disabled', false);
		scanButton.prop('disabled', false);
		alert(res && res.message ? res.message : kboard_svg_scan_i18n.restore_failed);
	}).fail(function(){
		restoreButton.prop('disabled', false);
		scanButton.prop('disabled', false);
		alert(kboard_svg_scan_i18n.restore_failed);
	});
	return false;
}

function kboard_search_reindex_batch_execute(form){
	var formElement = jQuery(form);
	var reindexButton = formElement.find('[data-kboard-search-reindex-button]');
	var applyButton = formElement.find('[data-kboard-search-index-apply-button]');
	var selectControl = formElement.find('[data-kboard-search-index-select]');
	var badge = formElement.find('[data-kboard-search-index-badge]');
	var progress = formElement.find('[data-kboard-search-reindex-progress]');
	var enabled = formElement.find('input[name="kboard-search-index-enabled"]').val() === '1';
	var nonce = formElement.find('input[name="kboard-search-reindex-batch-nonce"]').val();
	var limit = parseInt(formElement.find('input[name="kboard-search-reindex-limit"]').val(), 10) || 500;
	var lastUID = 0;
	var totalProcessed = 0;

	if(!enabled){
		progress.text('검색 인덱스를 먼저 활성화해주세요.');
		alert('검색 인덱스를 먼저 활성화해주세요.');
		return false;
	}
	if(!confirm('전체 게시글 검색 인덱스를 다시 생성할까요?')){
		return false;
	}

	reindexButton.prop('disabled', true).text('재인덱싱 실행 중...');
	applyButton.prop('disabled', true);
	selectControl.prop('disabled', true);
	badge.removeClass('active inactive').addClass('warning').text('인덱싱');
	progress.text('처리를 시작합니다...');

	var runBatch = function(){
		jQuery.post(ajaxurl, {
			action: 'kboard_search_reindex_batch',
			'kboard-search-reindex-batch-nonce': nonce,
			last_uid: lastUID,
			limit: limit
		}, function(res){
			if(!res || res.result != 'success'){
				var errorMessage = '재인덱싱에 실패했습니다.';
				if(res && res.message){
					errorMessage = res.message;
				}
				else if(typeof res === 'string' && jQuery.trim(res)){
					errorMessage = jQuery.trim(res);
				}
				reindexButton.prop('disabled', false).text('전체 재인덱싱 실행');
				applyButton.prop('disabled', false);
				selectControl.prop('disabled', false);
				badge.removeClass('warning inactive').addClass('active').text('활성');
				progress.text('재인덱싱 중 오류가 발생했습니다.');
				alert(errorMessage);
				return;
			}

			var data = res.data || {};
			if(data.disabled){
				reindexButton.prop('disabled', true).text('전체 재인덱싱 실행');
				applyButton.prop('disabled', false);
				selectControl.prop('disabled', false);
				badge.removeClass('active warning').addClass('inactive').text('비활성');
				progress.text('검색 인덱스가 비활성화되어 작업이 중단되었습니다.');
				return;
			}

			var processed = parseInt(data.processed || 0, 10);
			var total = parseInt(data.total || 0, 10);
			var remaining = parseInt(data.remaining || 0, 10);
			lastUID = parseInt(data.next_last_uid || data.last_uid || lastUID, 10);
			totalProcessed += processed;

			progress.text('처리 ' + totalProcessed + '개 / 전체 ' + total + '개 / 남음 ' + Math.max(remaining, 0) + '개');

			if(data.has_more){
				runBatch();
				return;
			}

			reindexButton.prop('disabled', false).text('전체 재인덱싱 다시 실행');
			applyButton.prop('disabled', false);
			selectControl.prop('disabled', false);
			badge.removeClass('warning inactive').addClass('active').text('완료');
			alert('전체 재인덱싱이 완료되었습니다.');
		}).fail(function(xhr){
			var errorMessage = '재인덱싱 중 통신 오류가 발생했습니다.';
			if(xhr && xhr.responseText && jQuery.trim(xhr.responseText)){
				errorMessage = jQuery.trim(xhr.responseText);
			}
			reindexButton.prop('disabled', false).text('전체 재인덱싱 실행');
			applyButton.prop('disabled', false);
			selectControl.prop('disabled', false);
			badge.removeClass('warning inactive').addClass('active').text('활성');
			progress.text('재인덱싱 중 통신 오류가 발생했습니다.');
			alert(errorMessage);
		});
	};

	runBatch();
	return false;
}
</script>

