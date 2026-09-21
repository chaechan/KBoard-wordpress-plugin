<?php
/**
 * KBoard Admin Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBAdminController {
	
	public function __construct(){
		add_action('admin_post_kboard_update_execute', array($this, 'update'));
		add_action('admin_post_kboard_backup_download', array($this, 'backup'));
		add_action('admin_post_kboard_restore_execute', array($this, 'restore'));
		add_action('admin_post_kboard_latestview_action', array($this, 'latestview_update'));
		add_action('admin_post_kboard_category_update', array($this, 'category_update'));
		add_action('admin_post_kboard_csv_download_execute', array($this, 'csv_download'));
		add_action('admin_post_kboard_csv_upload_execute', array($this, 'csv_upload'));
		add_action('wp_ajax_kboard_content_list_update', array($this, 'content_list_update'));
		add_action('wp_ajax_kboard_system_option_update', array($this, 'system_option_update'));
		add_action('wp_ajax_kboard_search_reindex_batch', array($this, 'search_reindex_batch'));
		add_action('wp_ajax_kboard_svg_batch_scan_execute', array($this, 'svg_batch_scan_execute'));
		add_action('wp_ajax_kboard_svg_batch_restore_execute', array($this, 'svg_batch_restore_execute'));
		add_action('wp_ajax_kboard_tree_category_update', array($this, 'tree_category_update'));
		add_action('wp_ajax_kboard_tree_category_sortable', array($this, 'tree_category_sortable'));
	}
	
	/**
	 * 게시판 정보 수정
	 */
	public function update(){
		global $wpdb;
		if(!defined('KBOARD_COMMNETS_VERSION')) die('<script>alert("게시판 생성 실패!\nKBoard 댓글 플러그인을 설치해주세요.\nhttps://www.cosmosfarm.com/ 에서 다운로드 가능합니다.");history.go(-1);</script>');
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-setting-execute-nonce']) && wp_verify_nonce($_POST['kboard-setting-execute-nonce'], 'kboard-setting-execute')){
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$_POST = stripslashes_deep($_POST);
			
			$board_id         = isset($_POST['board_id'])         ? intval($_POST['board_id'])                               : '';
			$board_name       = isset($_POST['board_name'])       ? esc_sql(sanitize_text_field($_POST['board_name']))       : '';
			$skin             = isset($_POST['skin'])             ? esc_sql(sanitize_text_field($_POST['skin']))             : '';
			$page_rpp         = isset($_POST['page_rpp'])         ? esc_sql(sanitize_text_field($_POST['page_rpp']))         : '';
			$use_comment      = isset($_POST['use_comment'])      ? esc_sql(sanitize_text_field($_POST['use_comment']))      : '';
			$use_editor       = isset($_POST['use_editor'])       ? esc_sql(sanitize_text_field($_POST['use_editor']))       : '';
			$use_editor       = kboard_normalize_editor_value($use_editor);
			$permission_read  = isset($_POST['permission_read'])  ? esc_sql(sanitize_text_field($_POST['permission_read']))  : '';
			$permission_write = isset($_POST['permission_write']) ? esc_sql(sanitize_text_field($_POST['permission_write'])) : '';
			$admin_user       = isset($_POST['admin_user'])       ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['admin_user']))))     : '';
			$use_category     = isset($_POST['use_category'])     ? esc_sql(sanitize_text_field($_POST['use_category']))     : '';
			$category1_list   = isset($_POST['category1_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category1_list'])))) : '';
			$category2_list   = isset($_POST['category2_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category2_list'])))) : '';
			$category3_list   = isset($_POST['category3_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category3_list'])))) : '';
			$category4_list   = isset($_POST['category4_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category4_list'])))) : '';
			$category5_list   = isset($_POST['category5_list'])   ? implode(',', array_map('esc_sql', array_map('sanitize_text_field', explode(',', $_POST['category5_list'])))) : '';
			
			$auto_page = isset($_POST['auto_page']) ? intval($_POST['auto_page']) : '';
			if($auto_page){
				$auto_page_board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='$auto_page'");
				if($auto_page_board_id && $auto_page_board_id != $board_id){
					$auto_page = '';
					echo '<script>alert("게시판 자동 설치 페이지에 이미 연결된 게시판이 존재합니다. 페이지당 하나의 게시판만 설치 가능합니다.");window.history.go(-1);</script>';
					exit;
				}
			}
			
			if(!$board_id){
				$wpdb->insert(
					"{$wpdb->prefix}kboard_board_setting",
					array(
						'board_name'       => $board_name,
						'skin'             => $skin,
						'page_rpp'         => $page_rpp,
						'use_comment'      => $use_comment,
						'use_editor'       => $use_editor,
						'permission_read'  => $permission_read,
						'permission_write' => $permission_write,
						'admin_user'       => $admin_user,
						'use_category'     => $use_category,
						'category1_list'   => $category1_list,
						'category2_list'   => $category2_list,
						'category3_list'   => $category3_list,
						'category4_list'   => $category4_list,
						'category5_list'   => $category5_list,
						'created'          => date('YmdHis', current_time('timestamp'))
					),
					array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
				);
				$board_id = $wpdb->insert_id;
			}
			else{
				$wpdb->update(
					"{$wpdb->prefix}kboard_board_setting",
					array(
						'board_name'       => $board_name,
						'skin'             => $skin,
						'page_rpp'         => $page_rpp,
						'use_comment'      => $use_comment,
						'use_editor'       => $use_editor,
						'permission_read'  => $permission_read,
						'permission_write' => $permission_write,
						'use_category'     => $use_category,
						'category1_list'   => $category1_list,
						'category2_list'   => $category2_list,
						'category3_list'   => $category3_list,
						'category4_list'   => $category4_list,
						'category5_list'   => $category5_list,
						'admin_user'       => $admin_user
					),
					array('uid' => $board_id),
					array('%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
					array('%d')
				);
			}
			
			$board = new KBoard($board_id);
			
			if($board->id){
				$board->meta->auto_page = $auto_page;
				$board->meta->latest_target_page                = isset($_POST['latest_target_page'])                ? $_POST['latest_target_page']                : '';
				$board->meta->add_menu_page                     = isset($_POST['add_menu_page'])                     ? $_POST['add_menu_page']                     : '';
				$board->meta->mobile_page_rpp                   = isset($_POST['mobile_page_rpp'])                   ? $_POST['mobile_page_rpp']                   : '';
				$board->meta->notice_invisible_comments         = isset($_POST['notice_invisible_comments'])         ? $_POST['notice_invisible_comments']         : '';
				$board->meta->use_direct_url                    = isset($_POST['use_direct_url'])                    ? $_POST['use_direct_url']                    : '';
				$board->meta->latest_alerts                     = isset($_POST['latest_alerts'])                     ? implode(',', array_map('sanitize_text_field', explode(',', $_POST['latest_alerts']))) : '';
				$board->meta->latest_alerts_attachments_size    = isset($_POST['latest_alerts_attachments_size'])    ? $_POST['latest_alerts_attachments_size']    : '';
				$board->meta->exclude_admin_reply_alert         = isset($_POST['exclude_admin_reply_alert'])         ? $_POST['exclude_admin_reply_alert']         : '';
				$board->meta->comment_alerts                    = isset($_POST['comment_alerts'])                    ? $_POST['comment_alerts']                    : '';
				$board->meta->comment_skin                      = ($use_comment && isset($_POST['comment_skin']))    ? $_POST['comment_skin']                      : '';
				$board->meta->use_tree_category                 = isset($_POST['use_tree_category'])                 ? $_POST['use_tree_category']                 : '';
				$board->meta->default_content                   = isset($_POST['default_content'])                   ? $_POST['default_content']                   : '';
				$board->meta->pass_autop                        = isset($_POST['pass_autop'])                        ? $_POST['pass_autop']                        : '';
				$board->meta->shortcode_execute                 = isset($_POST['shortcode_execute'])                 ? $_POST['shortcode_execute']                 : '';
				$board->meta->shortcode_execute_only_admin      = isset($_POST['shortcode_execute_only_admin'])      ? $_POST['shortcode_execute_only_admin']      : '';
				$board->meta->autolink                          = isset($_POST['autolink'])                          ? $_POST['autolink']                          : $board->meta->autolink;
				$board->meta->reply_copy_content                = isset($_POST['reply_copy_content'])                ? $_POST['reply_copy_content']                : '';
				$board->meta->view_iframe                       = isset($_POST['view_iframe'])                       ? $_POST['view_iframe']                       : '';
				$board->meta->editor_view_iframe                = isset($_POST['editor_view_iframe'])                ? $_POST['editor_view_iframe']                : '';
				$board->meta->permission_list                   = isset($_POST['permission_list'])                   ? $_POST['permission_list']                   : '';
				$board->meta->show_author_activity_menu         = isset($_POST['show_author_activity_menu'])         ? $_POST['show_author_activity_menu']         : '';
				$board->meta->permission_access                 = isset($_POST['permission_access'])                 ? $_POST['permission_access']                 : '';
				$board->meta->permission_reply                  = isset($_POST['permission_reply'])                  ? $_POST['permission_reply']                  : '';
				$board->meta->permission_comment_write          = isset($_POST['permission_comment_write'])          ? $_POST['permission_comment_write']          : '';
				$board->meta->permission_comment_read           = isset($_POST['permission_comment_read'])           ? $_POST['permission_comment_read']           : '';
				$board->meta->permission_comment_read_minute    = isset($_POST['permission_comment_read_minute'])    ? $_POST['permission_comment_read_minute']    : '';
				$board->meta->permission_order                  = isset($_POST['permission_order'])                  ? $_POST['permission_order']                  : '';
				$board->meta->permission_attachment_download    = isset($_POST['permission_attachment_download'])    ? $_POST['permission_attachment_download']    : '';
				$board->meta->permission_vote                   = isset($_POST['permission_vote'])                   ? $_POST['permission_vote']                   : '';
				$board->meta->permission_vote_hide              = isset($_POST['permission_vote_hide'])              ? $_POST['permission_vote_hide']              : '';
				//소셜 댓글
				$board->meta->comments_plugin_id                = isset($_POST['comments_plugin_id'])                ? $_POST['comments_plugin_id']                : '';
				$board->meta->use_comments_plugin               = isset($_POST['use_comments_plugin'])               ? $_POST['use_comments_plugin']               : '';
				$board->meta->comments_plugin_row               = isset($_POST['comments_plugin_row'])               ? $_POST['comments_plugin_row']               : '';
				
				//사이드톡 연동
				$board->meta->sidetalk_ai_enable                = isset($_POST['sidetalk_ai_enable'])                ? $_POST['sidetalk_ai_enable']                : '';
				$board->meta->sidetalk_ai_reply_user_id         = isset($_POST['sidetalk_ai_reply_user_id'])         ? $_POST['sidetalk_ai_reply_user_id']         : '';
				$board->meta->sidetalk_api_key                  = isset($_POST['sidetalk_api_key'])                  ? $_POST['sidetalk_api_key']                  : '';
				$board->meta->sidetalk_ai_target                = isset($_POST['sidetalk_ai_target'])                ? $_POST['sidetalk_ai_target']                : '';
				$board->meta->sidetalk_ai_post_reply_mode       = isset($_POST['sidetalk_ai_post_reply_mode'])       ? $_POST['sidetalk_ai_post_reply_mode']       : '';
				$board->meta->sidetalk_filter_keywords          = isset($_POST['sidetalk_filter_keywords'])          ? $_POST['sidetalk_filter_keywords']          : '';
				$board->meta->sidetalk_ai_reply_title           = isset($_POST['sidetalk_ai_reply_title'])           ? $_POST['sidetalk_ai_reply_title']           : '';
				$board->meta->sidetalk_ai_reply_author          = isset($_POST['sidetalk_ai_reply_author'])          ? $_POST['sidetalk_ai_reply_author']          : '';
				
				$board->meta->conversion_tracking_code          = isset($_POST['conversion_tracking_code'])          ? $_POST['conversion_tracking_code']          : '';
				$board->meta->always_view_list                  = isset($_POST['always_view_list'])                  ? $_POST['always_view_list']                  : '';
				$board->meta->max_attached_count                = isset($_POST['max_attached_count'])                ? $_POST['max_attached_count']                : '';
				$board->meta->list_sort_numbers                 = isset($_POST['list_sort_numbers'])                 ? $_POST['list_sort_numbers']                 : '';
				$board->meta->list_default_sorting              = isset($_POST['list_default_sorting'])              ? $_POST['list_default_sorting']              : '';
				$board->meta->notice_list_show                  = isset($_POST['notice_list_show'])                  ? $_POST['notice_list_show']                  : '';
				$board->meta->use_notice_expiration             = isset($_POST['use_notice_expiration'])             ? $_POST['use_notice_expiration']             : '';
				$board->meta->list_sorting_range_select         = isset($_POST['list_sorting_range_select'])         ? $_POST['list_sorting_range_select']         : '';
				$board->meta->list_sorting_start_date           = isset($_POST['list_sorting_start_date'])           ? $_POST['list_sorting_start_date']           : '';
				$board->meta->list_sorting_end_date             = isset($_POST['list_sorting_end_date'])             ? $_POST['list_sorting_end_date']             : '';
				$board->meta->permit                            = isset($_POST['permit'])                            ? $_POST['permit']                            : '';
				$board->meta->comment_permit                    = isset($_POST['comment_permit'])                    ? $_POST['comment_permit']                    : '';
				$board->meta->secret_checked_default            = isset($_POST['secret_checked_default'])            ? $_POST['secret_checked_default']            : '';
				$board->meta->secret_checked_forced             = isset($_POST['secret_checked_forced'])             ? $_POST['secret_checked_forced']             : '';
				$board->meta->use_prevent_modify_delete         = isset($_POST['use_prevent_modify_delete'])         ? $_POST['use_prevent_modify_delete']         : '';
				$board->meta->use_prevent_comment_modify_delete = isset($_POST['use_prevent_comment_modify_delete']) ? $_POST['use_prevent_comment_modify_delete'] : '';
				$board->meta->board_username_display_save       = isset($_POST['board_username_display_save'])       ? $_POST['board_username_display_save']       : '';
				$board->meta->comments_username_display_save    = isset($_POST['comments_username_display_save'])    ? $_POST['comments_username_display_save']    : '';
				$board->meta->board_username_masking            = isset($_POST['board_username_masking'])            ? $_POST['board_username_masking']            : '';
				$board->meta->comments_username_masking         = isset($_POST['comments_username_masking'])         ? $_POST['comments_username_masking']         : '';
				$board->meta->comments_anonymous                = isset($_POST['comments_anonymous'])                ? $_POST['comments_anonymous']                : '';
				$board->meta->max_document_limit                = isset($_POST['max_document_limit'])                ? $_POST['max_document_limit']                : '';
				$board->meta->new_document_delay                = isset($_POST['new_document_delay'])                ? $_POST['new_document_delay']                : '';
				$board->meta->default_build_mod                 = isset($_POST['default_build_mod'])                 ? $_POST['default_build_mod']                 : '';
				$board->meta->after_executing_mod               = isset($_POST['after_executing_mod'])               ? $_POST['after_executing_mod']               : '';
				$board->meta->woocommerce_product_tabs_add      = isset($_POST['woocommerce_product_tabs_add'])      ? $_POST['woocommerce_product_tabs_add']      : '';
				$board->meta->woocommerce_product_tabs_priority = isset($_POST['woocommerce_product_tabs_priority']) ? $_POST['woocommerce_product_tabs_priority'] : '';
				$board->meta->prevent_copy                      = isset($_POST['prevent_copy'])                      ? $_POST['prevent_copy']                      : '';
				$board->meta->popular_action                    = isset($_POST['popular_action'])                    ? $_POST['popular_action']                    : '';
				$board->meta->except_count_type                 = isset($_POST['except_count_type'])                 ? $_POST['except_count_type']                 : '';
				$board->meta->except_count_type_keyword         = isset($_POST['except_count_type_keyword'])         ? sanitize_text_field($_POST['except_count_type_keyword'])     : '';
				$board->meta->popular_type                      = isset($_POST['popular_type'])                      ? $_POST['popular_type']                      : '';
				$board->meta->popular_count                     = isset($_POST['popular_count'])                     ? $_POST['popular_count']                     : '';
				$board->meta->popular_range                     = isset($_POST['popular_range'])                     ? $_POST['popular_range']                     : '';
				$board->meta->popular_name                      = isset($_POST['popular_name'])                      ? sanitize_text_field($_POST['popular_name']) : '';
				$board->meta->display_name_masking              = isset($_POST['display_name_masking'])              ? $_POST['display_name_masking']              : '';
				
				if(isset($_POST['latest_list_columns'])){
					$board->meta->latest_list_columns = serialize($_POST['latest_list_columns']);
				}
				if(isset($_POST['permission_read_roles'])){
					$board->meta->permission_read_roles = serialize($_POST['permission_read_roles']);
				}
				if(isset($_POST['permission_write_roles'])){
					$board->meta->permission_write_roles = serialize($_POST['permission_write_roles']);
				}
				if(isset($_POST['permission_reply_roles'])){
					$board->meta->permission_reply_roles = serialize($_POST['permission_reply_roles']);
				}
				if(isset($_POST['permission_comment_write_roles'])){
					$board->meta->permission_comment_write_roles = serialize($_POST['permission_comment_write_roles']);
				}
				if(isset($_POST['permission_order_roles'])){
					$board->meta->permission_order_roles = serialize($_POST['permission_order_roles']);
				}
				if(isset($_POST['permission_admin_roles'])){
					$board->meta->permission_admin_roles = serialize($_POST['permission_admin_roles']);
				}
				if(isset($_POST['permission_vote_roles'])){
					$board->meta->permission_vote_roles = serialize($_POST['permission_vote_roles']);
				}
				if(isset($_POST['permission_attachment_download_roles'])){
					$board->meta->permission_attachment_download_roles = serialize($_POST['permission_attachment_download_roles']);
				}
				
				if(isset($_POST['fields']) && is_array($_POST['fields'])){
					foreach($_POST['fields'] as &$field){
						if(!is_array($field)){
							continue;
						}
						if((!isset($field['show_document_mode']) || $field['show_document_mode'] === '') && !empty($field['show_document'])){
							$field['show_document_mode'] = '1';
						}
					}
					unset($field);
				}
				$board->meta->skin_fields                    = isset($_POST['fields'])                         ? serialize($_POST['fields'])                     : '';
				$board->meta->point_applied_to               = isset($_POST['point_applied_to'])               ? sanitize_text_field($_POST['point_applied_to']) : '';
				$board->meta->document_insert_up_point       = isset($_POST['document_insert_up_point'])       ? abs(intval($_POST['document_insert_up_point']))       : '';
				$board->meta->document_insert_down_point     = isset($_POST['document_insert_down_point'])     ? abs(intval($_POST['document_insert_down_point']))     : '';
				$board->meta->document_delete_up_point       = isset($_POST['document_delete_up_point'])       ? abs(intval($_POST['document_delete_up_point']))       : '';
				$board->meta->document_delete_down_point     = isset($_POST['document_delete_down_point'])     ? abs(intval($_POST['document_delete_down_point']))     : '';
				$board->meta->document_read_down_point       = isset($_POST['document_read_down_point'])       ? abs(intval($_POST['document_read_down_point']))       : '';
				$board->meta->attachment_download_down_point = isset($_POST['attachment_download_down_point']) ? abs(intval($_POST['attachment_download_down_point'])) : '';
				$board->meta->comment_insert_up_point        = isset($_POST['comment_insert_up_point'])        ? abs(intval($_POST['comment_insert_up_point']))        : '';
				$board->meta->comment_insert_down_point      = isset($_POST['comment_insert_down_point'])      ? abs(intval($_POST['comment_insert_down_point']))      : '';
				$board->meta->comment_delete_up_point        = isset($_POST['comment_delete_up_point'])        ? abs(intval($_POST['comment_delete_up_point']))        : '';
				$board->meta->comment_delete_down_point      = isset($_POST['comment_delete_down_point'])      ? abs(intval($_POST['comment_delete_down_point']))      : '';
				
				// kboard_extends_setting_update 액션 실행
				do_action('kboard_extends_setting_update', $board->meta, $board_id);
				do_action("kboard_{$board->skin}_extends_setting_update", $board->meta, $board_id);
				
				$tab_kboard_setting = isset($_POST['tab_kboard_setting'])?'#tab-kboard-setting-'.intval($_POST['tab_kboard_setting']):'';
				wp_redirect(admin_url('admin.php?page=kboard_list&board_id=' . $board_id . $tab_kboard_setting));
				exit;
			}
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	/**
	 * 기존 SVG 파일 정리 작업을 실행한다.
	 */
	private function svg_batch_scan_execute_internal(){
		$lock_ttl = 120;
		if(!current_user_can('manage_kboard')){
			wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
		}
		if(!isset($_POST['kboard-svg-batch-scan-nonce']) || !wp_verify_nonce($_POST['kboard-svg-batch-scan-nonce'], 'kboard-svg-batch-scan')){
			wp_send_json(array('result'=>'error', 'message'=>__('Security check failed.', 'kboard')));
		}
		
		$running = intval(get_option('kboard_svg_batch_scan_running'));
		if($running && (time() - $running) < $lock_ttl){
			$remaining = max(1, $lock_ttl - (time() - $running));
			wp_send_json(array('result'=>'error', 'message'=>sprintf(__('SVG cleanup is already running. Try again in %d seconds.', 'kboard'), $remaining)));
		}
		
		$started_at = time();
		update_option('kboard_svg_batch_scan_running', $started_at, 'no');
		register_shutdown_function(function() use ($started_at){
			if(intval(get_option('kboard_svg_batch_scan_running')) === $started_at){
				delete_option('kboard_svg_batch_scan_running');
			}
		});
		@set_time_limit(0);
		@ini_set('memory_limit', '-1');
		
		if(!function_exists('kboard_svg_batch_scan_uploads')){
			include_once KBOARD_DIR_PATH . '/helper/Security.helper.php';
		}
		if(!function_exists('kboard_svg_batch_scan_uploads')){
			delete_option('kboard_svg_batch_scan_running');
			wp_send_json(array('result'=>'error', 'message'=>__('SVG cleanup helpers are not loaded. Please deploy the latest helper file and clear OPcache.', 'kboard')));
		}
		
		$result = kboard_svg_batch_scan_uploads();
		update_option('kboard_svg_batch_scan_result', $result, 'no');
		delete_option('kboard_svg_batch_scan_pending');
		delete_option('kboard_svg_batch_scan_running');
		
		wp_send_json(array('result'=>'success', 'data'=>$result));
	}
	
	/**
	 * 기존 SVG 파일 정리 작업을 실행한다.
	 */
	public function svg_batch_scan_execute(){
		return $this->svg_batch_scan_execute_internal();
	}

	/**
	 * 기존 SVG 파일 복원 작업을 실행한다.
	 */
	private function svg_batch_restore_execute_internal(){
		$lock_ttl = 120;
		if(!current_user_can('manage_kboard')){
			wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
		}
		if(!isset($_POST['kboard-svg-batch-restore-nonce']) || !wp_verify_nonce($_POST['kboard-svg-batch-restore-nonce'], 'kboard-svg-batch-restore')){
			wp_send_json(array('result'=>'error', 'message'=>__('Security check failed.', 'kboard')));
		}
		
		$running = intval(get_option('kboard_svg_batch_scan_running'));
		if($running && (time() - $running) < $lock_ttl){
			$remaining = max(1, $lock_ttl - (time() - $running));
			wp_send_json(array('result'=>'error', 'message'=>sprintf(__('SVG cleanup is already running. Try again in %d seconds.', 'kboard'), $remaining)));
		}
		
		if(!function_exists('kboard_svg_batch_restore_uploads')){
			include_once KBOARD_DIR_PATH . '/helper/Security.helper.php';
		}
		if(!function_exists('kboard_svg_batch_restore_uploads')){
			wp_send_json(array('result'=>'error', 'message'=>__('SVG cleanup helpers are not loaded. Please deploy the latest helper file and clear OPcache.', 'kboard')));
		}
		
		$result = kboard_svg_batch_restore_uploads();
		wp_send_json(array('result'=>'success', 'data'=>$result));
	}

	/**
	 * 기존 SVG 파일 복원 작업을 실행한다.
	 */
	public function svg_batch_restore_execute(){
		return $this->svg_batch_restore_execute_internal();
	}
	
	/**
	 * 백업
	 */
	public function backup(){
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-backup-download-nonce']) && wp_verify_nonce($_POST['kboard-backup-download-nonce'], 'kboard-backup-download')){
			set_time_limit(3600);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			include_once KBOARD_DIR_PATH . '/class/KBBackup.class.php';
			$backup = new KBBackup();
			$tables = $backup->getTables();
			$data = '';
			foreach($tables as $key=>$value){
				$data .= $backup->getXml($value);
			}
			
			$backup->download($data, 'xml');
			exit;
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>window.location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 복원
	 */
	public function restore(){
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-restore-execute-nonce']) && wp_verify_nonce($_POST['kboard-restore-execute-nonce'], 'kboard-restore-execute')){
			set_time_limit(3600);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$xmlfile = $_FILES['kboard_backup_xml_file']['tmp_name'];
			$xmlfile_name = basename($_FILES['kboard_backup_xml_file']['name']);
			
			if(is_uploaded_file($xmlfile)){
				$file_extension = explode('.', $xmlfile_name);
				if(end($file_extension) == 'xml'){
					include_once KBOARD_DIR_PATH . '/class/KBBackup.class.php';
					$backup = new KBBackup();
					$backup->importXml($xmlfile);
					echo '<script>alert("'.__('복원파일의 데이터로 복구 되었습니다.', 'kboard').'");</script>';
				}
				else{
					echo '<script>alert("'.__('올바른 복원파일이 아닙니다.', 'kboard').'");</script>';
				}
			}
			else{
				echo '<script>alert("'.__('파일 업로드에 실패 했습니다.', 'kboard').'");</script>';
			}
			
			if($xmlfile) unlink($xmlfile);
		}
		$redirect_url = admin_url('admin.php?page=kboard_backup');
		echo "<script>window.location.href='{$redirect_url}';</script>";
		exit;
	}
	
	/**
	 * 최신글 뷰 업데이트
	 */
	function latestview_update(){
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		
		$latestview_uid = $_POST['latestview_uid'];
		$latestview_link = $_POST['latestview_link'];
		$latestview_unlink = $_POST['latestview_unlink'];
		$name = $_POST['name'];
		$skin = $_POST['skin'];
		$rpp = $_POST['rpp'];
		$mobile_rpp = $_POST['mobile_rpp'];
		$sort = $_POST['sort'];
		
		$latestview = new KBLatestview();
		if($latestview_uid) $latestview->initWithUID($latestview_uid);
		else $latestview->create();
		
		$latestview->name = $name;
		$latestview->skin = $skin;
		$latestview->rpp = $rpp;
		$latestview->mobile_rpp = $mobile_rpp;
		$latestview->sort = $sort;
		$latestview->update();
		
		$latestview_link = explode(',', $latestview_link);
		if(is_array($latestview_link)){
			foreach($latestview_link as $key=>$value){
				$value = intval($value);
				if($value) $latestview->pushBoard($value);
			}
		}
		
		$latestview_unlink = explode(',', $latestview_unlink);
		if(is_array($latestview_unlink)){
			foreach($latestview_unlink as $key=>$value){
				$value = intval($value);
				if($value) $latestview->popBoard($value);
			}
		}
		
		echo '<script>window.location.href="'.admin_url("admin.php?page=kboard_latestview&latestview_uid={$latestview->uid}").'"</script>';
		exit;
	}
	
	public function csv_download(){
		global $wpdb;
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_GET['kboard-csv-download-execute-nonce']) && wp_verify_nonce($_GET['kboard-csv-download-execute-nonce'], 'kboard-csv-download-execute')){
			set_time_limit(3600);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$board_id = isset($_GET['board_id'])?$_GET['board_id']:'';
			$board = new KBoard($board_id);
			
			$fields = new KBoardFields($board);
			$field_map = array(); // [meta_key => label]

			foreach($fields->getSkinFields() as $field){
				$meta_key = isset($field['meta_key']) ? $field['meta_key'] : '';
				$label = $field['field_name'] ?? $field['field_label'] ?? $meta_key;
				if($meta_key){
					$field_map[$meta_key] = $label;
				}
			}
			
			if($board->id){
				$date = date('YmdHis', current_time('timestamp'));
				$filename = "KBoard-{$board->id}-{$date}.csv";
				
				$columns = $wpdb->get_col("DESCRIBE `{$wpdb->prefix}kboard_board_content`");
				$option = $wpdb->get_col("SELECT DISTINCT(`option_key`) FROM `{$wpdb->prefix}kboard_board_option` AS `option` LEFT JOIN `{$wpdb->prefix}kboard_board_content` AS `content` ON `option`.`content_uid`=`content`.`uid` WHERE `content`.`board_id`='{$board->id}'");
				
				foreach($option as $option_key){
					$columns[] = KBContent::$SKIN_OPTION_PREFIX . $option_key;
				}
				
				header('Content-type: application/csv');
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header('Pragma: no-cache');
				header('Expires: 0');
				
				@ob_clean();
				@flush();
				
				$csv = fopen('php://output', 'w');
				
				fprintf($csv, chr(0xEF).chr(0xBB).chr(0xBF));
				fputcsv($csv, $columns);
				
				$list = new KBContentList($board_id);
				$list->rpp(1000);
				$list->orderASC('uid');
				$list->initFirstList();
				
				while($list->hasNextList()){
					while($content = $list->hasNext()){
						$row_data = $content->toArray();
						
						$row_data['date'] = kboard_date_format($row_data['date'], 'Y-m-d H:i:s');
						$row_data['update'] = kboard_date_format($row_data['update'], 'Y-m-d H:i:s');
						
						$option_mode = $_GET['kboard_csv_download_option'] ?? '';
						$skin_fields = $fields->getSkinFields();
						
						//항상 옵션 필드를 컬럼으로 추가
						foreach($option as $option_key){
							// 주소 필드 예외 처리
							if (isset($skin_fields[$option_key]) && $skin_fields[$option_key]['field_type'] === 'address') {
								$postcode = $content->option->{$option_key . '_postcode'} ?? '';
								$addr1 = $content->option->{$option_key . '_address_1'} ?? '';
								$addr2 = $content->option->{$option_key . '_address_2'} ?? '';
								$full_address = trim("({$postcode}) {$addr1} {$addr2}");
								$row_data[] = $full_address;
							}
							else {
								$option_value = $content->option->{$option_key};
								if (is_array($option_value)) {
									$row_data[] = json_encode($option_value, JSON_UNESCAPED_UNICODE);
								}
								else {
									$row_data[] = $option_value;
								}
							}
						}
						
						//설정에 따라 본문 content에 병합할지 결정
						if ($option_mode == '1' || $option_mode == '2') {
							$option_content = '';
							foreach($option as $option_key){
								$field = $skin_fields[$option_key] ?? null;

								//show_document 체크된 필드만 본문에 포함
								if ($option_mode == '1') {
									if (!$field) continue;

									$show_document_mode = $field['show_document_mode'] ?? '';
									$legacy_show_document = $field['show_document'] ?? '';

									// 전체 표시 (1) 이거나, 직접 설정(roles)이면 포함
									if ($show_document_mode !== '1' && $show_document_mode !== 'roles' && !$legacy_show_document) {
										continue;
									}
								}

								$label = $field_map[$option_key] ?? $option_key;
								$option_value = $content->option->{$option_key};

								if (is_array($option_value)) {
									$option_value = json_encode($option_value, JSON_UNESCAPED_UNICODE);
								}

								$option_content .= "{$label} : {$option_value}\n";
							}

							$row_data['content'] = trim($option_content) . "\n\n" . $row_data['content'];
						}
						
						foreach($row_data as $key=>$value){
							if(!is_array($value) && !is_numeric($value)){
								$row_data[$key] = kboard_sanitize_csv_field($value);
							}
						}
						
						fputcsv($csv, $row_data);
					}
					@ob_flush();
					@flush();
				}
				
				fclose($csv);
				exit;
			}
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	public function csv_upload(){
		global $wpdb;
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-setting-execute-nonce']) && wp_verify_nonce($_POST['kboard-setting-execute-nonce'], 'kboard-setting-execute')){
			set_time_limit(3600);
			ini_set('memory_limit', '-1');
			
			header('Content-Type: text/html; charset=UTF-8');
			
			$board_id = isset($_POST['board_id']) ? intval($_POST['board_id']) : '';
			$board    = new KBoard($board_id);
			
			$option = isset($_POST['kboard_csv_upload_option']) ? sanitize_text_field($_POST['kboard_csv_upload_option']) : '';
			if(!in_array($option, array('keep', 'update', 'delete'))){
				$option = 'keep';
			}
			
			$file      = $_FILES['kboard_csv_upload_file']['tmp_name'];
			$file_name = basename($_FILES['kboard_csv_upload_file']['name']);
			
			if(is_uploaded_file($file) && $board->id){
				$file_extension = explode('.', $file_name);
				
				if(end($file_extension) == 'csv' || end($file_extension) == 'CSV'){
					if(($handle = fopen($file, 'r')) !== false){
						$length = 0;
						
						while(($data = fgetcsv($handle, 0, ',')) !== false){
							$total = count($data);
							
							for($index=0; $index<$total; $index++){
								// Zero Width No-Break Space 삭제
								$value = preg_replace('/\xEF\xBB\xBF/', '', $data[$index]);
								
								// 인코딩 변환
								if(function_exists('mb_detect_encoding')){
									$encoding = mb_detect_encoding($value, 'auto');
									if($encoding != 'UTF-8'){
										$value = @iconv($encoding, 'UTF-8//TRANSLIT', $value);
									}
								}
								
								if($length){
									// 데이터
									if($columns[$index] == 'date' || $columns[$index] == 'update'){
										$timestamp = kboard_date_to_timestamp($value);
										$value = $timestamp === false ? '' : date('YmdHis', $timestamp);
									}
									
									$decode_value = json_decode($value);
									if(is_array($decode_value)){
										$row_data[$columns[$index]] = $decode_value;
									}
									else{
										$row_data[$columns[$index]] = $value;
									}
								}
								else{
									// 컬럼
									$columns[] = $value;
								}
							}
							
							if(isset($row_data)){
								$rows[] = $row_data;
							}
							
							$length++;
						}
						
						if($length >= 2){
							$content = new KBContent();
							$content->setBoardID($board->id);
							
							if($option == 'keep'){ // insert
								foreach($rows as $key=>$row){
									if(!isset($row['parent_uid']) || !intval($row['parent_uid'])){
										$row['board_id'] = $board->id;
										$insert_id = $content->insertContent($row);
										if(isset($row['uid'])){
											$new_uids[] = $insert_id;
										}
										
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
								foreach($rows as $key=>$row){
									$row['board_id']   = $board->id;
									$row['parent_uid'] = $new_uids[$row['parent_uid']];
									$content->insertContent($row);
									$content->updateOptions($row);
									unset($rows[$key]);
									@ob_flush();
									@flush();
								}
							}
							else if($option == 'update'){ // update
								foreach($rows as $key=>$row){
									$uid = isset($row['uid']) ? intval($row['uid']) : 0;
									
									if($uid){
										$row['board_id'] = $board->id;
										$content->initWithUID($uid);
										$content->updateContent($row);
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
							}
							else if($option == 'delete'){ // delete
								$board->truncate();
								foreach($rows as $key=>$row){
									if(!isset($row['parent_uid']) || !intval($row['parent_uid'])){
										$row['board_id'] = $board->id;
										$insert_id       = $content->insertContent($row);
										if(isset($row['uid'])){
											$new_uids[] = $insert_id;
										}
										
										$content->updateOptions($row);
										unset($rows[$key]);
										@ob_flush();
										@flush();
									}
								}
								foreach($rows as $key=>$row){
									$row['board_id']   = $board->id;
									$row['parent_uid'] = $new_uids[$row['parent_uid']];
									$content->insertContent($row);
									$content->updateOptions($row);
									unset($rows[$key]);
									@ob_flush();
									@flush();
								}
							}
							
							$board->resetTotal();
						}
						
						fclose($handle);
					}
					echo '<script>alert("CSV 파일을 업로드했습니다.");</script>';
				}
				else{
					echo '<script>alert("CSV 파일만 업로드 가능합니다.");</script>';
				}
			}
			else{
				echo '<script>alert("파일 업로드에 실패했습니다");</script>';
			}
			
			if($file) unlink($file);
			
			$tab_kboard_setting = isset($_POST['tab_kboard_setting']) ? '#tab-kboard-setting-'.intval($_POST['tab_kboard_setting']) : '';
			$redirect_url       = admin_url('admin.php?page=kboard_list&board_id=' . $board_id . $tab_kboard_setting);
			echo "<script>window.location.href='{$redirect_url}'</script>";
			exit;
		}
		wp_redirect(admin_url('admin.php?page=kboard_dashboard'));
		exit;
	}
	
	/**
	 * 전체 게시글 정보 업데이트
	 */
	public function content_list_update(){
		if(current_user_can('manage_kboard')){
			$board_ids = isset($_POST['board_id']) && is_array($_POST['board_id']) ? wp_unslash($_POST['board_id']) : array();
			$statuses = isset($_POST['status']) && is_array($_POST['status']) ? wp_unslash($_POST['status']) : array();
			$dates = isset($_POST['date']) && is_array($_POST['date']) ? wp_unslash($_POST['date']) : array();
			$times = isset($_POST['time']) && is_array($_POST['time']) ? wp_unslash($_POST['time']) : array();
			$status_list = kboard_content_status_list();
			$uids = array_unique(array_merge(array_keys($board_ids), array_keys($statuses), array_keys($dates), array_keys($times)));
			$content_uid = isset($_POST['content_uid']) ? intval($_POST['content_uid']) : 0;
			if($content_uid) $uids = array($content_uid);
			$content = new KBContent();
			foreach($uids as $uid){
				$uid = intval($uid);
				if(!$uid) continue;

				$content->initWithUID($uid);
				if(!$content->uid) continue;

				// 게시글 수정 전에 액션 훅 실행
				do_action('kboard_pre_content_list_update', $content);

				$board_id = isset($board_ids[$uid]) && is_scalar($board_ids[$uid]) ? intval($board_ids[$uid]) : 0;
				if($board_id) $content->board_id = $board_id;

				if(isset($statuses[$uid]) && is_scalar($statuses[$uid])){
					$status = sanitize_key($statuses[$uid]);
					if(array_key_exists($status, $status_list)) $content->status = $status;
				}

				if(isset($dates[$uid], $times[$uid]) && is_scalar($dates[$uid]) && is_scalar($times[$uid])){
					$date = sanitize_text_field($dates[$uid]);
					$time = sanitize_text_field($times[$uid]);
					$datetime = $date . ' ' . $time;
					$timestamp = false;
					if(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && preg_match('/^\d{2}:\d{2}:\d{2}$/', $time)){
						$timestamp = strtotime($datetime);
					}
					if($timestamp !== false && date('Y-m-d H:i:s', $timestamp) === $datetime){
						$content->date = date('YmdHis', $timestamp);
					}
				}
				$content->updateContent();
				
				// 게시글 수정 액션 훅 실행
				do_action('kboard_content_list_update', $content);
			}
		}
		exit;
	}
	
	/**
	 * 검색 인덱스 재인덱싱 배치를 실행한다.
	 */
	public function search_reindex_batch(){
		if(!current_user_can('manage_kboard')){
			wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
		}
		if(!isset($_POST['kboard-search-reindex-batch-nonce']) || !wp_verify_nonce($_POST['kboard-search-reindex-batch-nonce'], 'kboard-search-reindex-batch')){
			wp_send_json(array('result'=>'error', 'message'=>__('Security check failed.', 'kboard')));
		}
		
		$_POST = stripslashes_deep($_POST);
		$limit = isset($_POST['limit']) ? intval($_POST['limit']) : 500;
		$last_uid = isset($_POST['last_uid']) ? intval($_POST['last_uid']) : 0;
		$board_id = isset($_POST['board_id']) ? intval($_POST['board_id']) : 0;
		
		if(!kboard_use_search_index()){
			wp_send_json(array(
				'result' => 'error',
				'message' => __('Search index is disabled.', 'kboard'),
				'data' => array('disabled'=>true, 'processed'=>0, 'next_last_uid'=>$last_uid),
			));
		}
		
		$result = KBSearchIndexer::reindexBatch($limit, $last_uid, $board_id);
		wp_send_json(array('result'=>'success', 'data'=>$result));
	}
	
	/**
	 * 시스템 설정 업데이트
	 */
	public function system_option_update(){
		if(current_user_can('manage_kboard')){
			check_ajax_referer('kboard-system-option-update', 'security');
			
			$_POST = stripslashes_deep($_POST);
			
			$option = isset($_POST['option'])?$_POST['option']:array();
			$allowed_options = apply_filters('kboard_system_option_update_allowed_options', array(
				'kboard_allow_file_extensions',
				'kboard_allow_search_engines_always_read',
				'kboard_attached_copy_download',
				'kboard_attached_open_browser',
				'kboard_builtin_pg_inicis_general_api_key',
				'kboard_builtin_pg_inicis_general_mid',
				'kboard_builtin_pg_inicis_general_sign_key',
				'kboard_builtin_pg_nicepay_general_merchant_key',
				'kboard_builtin_pg_nicepay_general_mid',
				'kboard_captcha_stop',
				'kboard_content_delete_immediately',
				'kboard_content_filter',
				'kboard_content_filter_message',
				'kboard_custom_css',
				'kboard_fontawesome',
				'kboard_google_api_key',
				'kboard_iamport_api_key',
				'kboard_iamport_api_secret',
				'kboard_iamport_id',
				'kboard_iframe_whitelist',
				'kboard_image_optimize_height',
				'kboard_image_optimize_quality',
				'kboard_image_optimize_width',
				'kboard_kakao_api_javascript_key',
				'kboard_kakao_api_rest_key',
				'kboard_limit_file_size',
				'kboard_name_filter',
				'kboard_name_filter_message',
				'kboard_naver_api_client_id',
				'kboard_naver_api_client_secret',
				'kboard_new_document_notify_time',
				'kboard_prevent_copy',
				'kboard_public_data_portal_api_key',
				'kboard_recaptcha_secret_key',
				'kboard_recaptcha_site_key',
				'kboard_recaptcha_type',
				'kboard_search_auto_operator_or',
				'kboard_search_include_member_display',
				'kboard_updates_notify_disabled',
				'kboard_use_search_index',
				'kboard_xssfilter',
			));
			
			foreach($option as $name=>$value){
				$name = sanitize_key($name);
				if(!in_array($name, $allowed_options, true) || !is_scalar($value)){
					continue;
				}
				$value = sanitize_textarea_field($value);
				
				if(!$value){
					delete_option($name);
				}
				else if(get_option($name) !== false){
					update_option($name, $value, 'yes');
				}
				else{
					add_option($name, $value, '', 'yes');
				}
			}
		}
		exit;
	}
	
	/**
	 * 카테고리 업데이트
	 */
	public function category_update(){
		global $wpdb;
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		if(isset($_POST['kboard-category-execute-nonce']) && wp_verify_nonce($_POST['kboard-category-execute-nonce'], 'kboard-category-execute')){
			header('Content-Type: text/html; charset=UTF-8');
			
			$board_id = isset($_POST['board_id']) ? absint($_POST['board_id']) : 0;
			$target = isset($_POST['target']) ? sanitize_key(wp_unslash($_POST['target'])) : '';
			$before_category = isset($_POST['before_category']) ? sanitize_text_field(wp_unslash($_POST['before_category'])) : '';
			$after_category  = isset($_POST['after_category'])  ? sanitize_text_field(wp_unslash($_POST['after_category']))  : '';
			
			$msg = '변경할 카테고리가 없습니다.';
			
			if($board_id > 0 && in_array($target, array('category1', 'category2', 'category3', 'category4', 'category5'), true)){
				$table = $wpdb->prefix . 'kboard_board_content';
				
				// 값은 prepare로 안전하게 바인딩
				$sql = $wpdb->prepare(
					"UPDATE `{$table}` SET `{$target}` = %s WHERE `board_id` = %d AND `{$target}` = %s",
					$after_category,
					$board_id,
					$before_category
				);
				
				$updated_count = $wpdb->query($sql);
				
				if($updated_count === false){
					$msg = __('DB Error occurred.', 'kboard');
				}
				else if($updated_count){
					$msg = sprintf(__('%s개의 카테고리가 변경되었습니다.', 'kboard'), number_format($updated_count));
				}
			}
			
			echo '<script>alert("'. esc_js($msg) . '");</script>';
		}
		$redirect_url = admin_url('admin.php?page=kboard_category_update');
		echo "<script>window.location.href='" . esc_url_raw($redirect_url) . "';</script>";
		exit;
	}
	
	/**
	 * 계층형 카테고리 업데이트
	 */
	public function tree_category_update(){
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		
		$_POST = stripslashes_deep($_POST);
		
		$board_id = isset($_POST['board_id'])?$_POST['board_id']:'';
		
		/**
		 * https://github.com/shukebeta/parse-str-unlimited
		 * @param string $string
		 * @param array $result
		 * @return boolean
		 */
		function parse_str_unlimited($string, &$result) {
			if($string === '') return false;
			$result = array();
			// find the pairs "name=value"
			$pairs = explode('&', $string);
			$params = array();
			foreach ($pairs as $pair) {
				// use the original parse_str() on each element
				parse_str($pair, $params);
				$k = key($params);
				if(!isset($result[$k])) {
					$result += $params;
				} else {
					$result[$k] = array_merge_recursive_distinct($result[$k], $params[$k]);
				}
			}
			return true;
		}
		
		// better recursive array merge function listed on the array_merge_recursive PHP page in the comments
		function array_merge_recursive_distinct(array $array1, array $array2) {
			$merged = $array1;
			foreach ($array2 as $key => &$value) {
				if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
					$merged[$key] = array_merge_recursive_distinct($merged[$key], $value);
				} else {
					$merged[$key] = $value;
				}
			}
			return $merged;
		}
		
		$tree_category = array();
		if(isset($_POST['tree_category'])){
			parse_str_unlimited($_POST['tree_category'], $tree_category);
		}
		
		$board = new KBoard($board_id);
		$category = new KBoardTreeCategory();
		$category->setBoardID($board_id);
		$board->meta->tree_category = serialize($tree_category['tree_category']);
		$category->setTreeCategory($tree_category['tree_category']);
		$build_tree_category = $category->buildAdminTreeCategory();
		
		$table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);
		
		wp_send_json(array('table_body'=>$table_body));
	}
	
	/**
	 * 계층형 카테고리 순서 변경
	 */
	public function tree_category_sortable(){
		if(!current_user_can('manage_kboard')) wp_die(__('You do not have permission.', 'kboard'));
		
		$_POST = stripslashes_deep($_POST);
		
		$tree_category_serialize = isset($_POST['tree_category_serialize'])?json_decode($_POST['tree_category_serialize']):'';
		$board_id = isset($_POST['board_id'])?$_POST['board_id']:'';
		
		$board = new KBoard($board_id);
		$category = new KBoardTreeCategory();
		$category->setBoardID($board_id);
		
		$sortable_category = array();
		
		foreach($tree_category_serialize as $item){
			if(isset($item->id) && $item->id){
				foreach($category->tree_category as $key=>$value){
					if($item->id == $value['id']){
						$value['parent_id'] = $item->parent_id;
						$sortable_category[] = $value;
					}
				}
			}
		}
		
		$board->meta->tree_category = serialize($sortable_category);
		$category->setTreeCategory($sortable_category);
		$build_tree_category = $category->buildAdminTreeCategory();
		
		$table_body = $category->buildAdminTreeCategorySortableRow($build_tree_category);
		
		wp_send_json(array('table_body'=>$table_body));
	}
}
