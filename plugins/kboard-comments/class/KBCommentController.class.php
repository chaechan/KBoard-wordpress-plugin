<?php
/**
 * KBoard Comments Controller
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentController {
	
	private $abspath;
	
	// 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
	var $skin_attach_prefix = 'comment_attach_';
	// 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
	var $skin_option_prefix = 'comment_option_';
	
	public function __construct(){
		$this->abspath = untrailingslashit(ABSPATH);
		$action = isset($_GET['action'])?$_GET['action']:'';
		switch($action){
			case 'kboard_comment_insert': add_action('wp_loaded', array($this, 'insert')); break;
			case 'kboard_comment_delete': add_action('wp_loaded', array($this, 'delete')); break;
			case 'kboard_comment_update': add_action('wp_loaded', array($this, 'update')); break;
		}
		
		add_action('wp_ajax_kboard_comments_list_update', array($this, 'comments_list_update'));
		add_action('wp_ajax_kboard_comment_like', array($this, 'commentLike'));
		add_action('wp_ajax_nopriv_kboard_comment_like', array($this, 'commentLike'));
		add_action('wp_ajax_kboard_comment_unlike', array($this, 'commentUnlike'));
		add_action('wp_ajax_nopriv_kboard_comment_unlike', array($this, 'commentUnlike'));
		
	}
	
	/**
	 * 댓글 입력
	 */
	public function insert(){
		global $wpdb;
		
		$content_uid = isset($_POST['content_uid'])?intval($_POST['content_uid']):'';
		
		if(isset($_POST['kboard-comments-execute-nonce']) && wp_verify_nonce($_POST['kboard-comments-execute-nonce'], "kboard-comments-execute-{$content_uid}")){
			header("Content-Type: text/html; charset=UTF-8");
			
			// 되돌아오는 페이지 주소 깨지는 버그 해결
			$_SERVER['HTTP_REFERER'] = preg_replace('/\{[^}]*\}/', '%', wp_get_raw_referer());
			
			if(!wp_get_referer()){
				wp_die(__('This page is restricted from external access.', 'kboard-comments'));
			}
			
			$_POST = stripslashes_deep($_POST);
			
			$content = isset($_POST['content'])?$_POST['content']:'';
			$comment_content = isset($_POST['comment_content'])?$_POST['comment_content']:'';
			$content = $content?$content:$comment_content;
			$comment_hide = isset($_POST['comment_option_hide'])?sanitize_text_field($_POST['comment_option_hide']):'';
			$comment_anonymous = isset($_POST['comment_option_anonymous'])?sanitize_text_field($_POST['comment_option_anonymous']):'';
			
			$parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):'';
			$member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):'';
			$member_display = isset($_POST['member_display'])?sanitize_text_field($_POST['member_display']):'';
			$status = isset($_POST['status'])?sanitize_key($_POST['status']):'';
			$password = isset($_POST['password'])?sanitize_text_field($_POST['password']):'';
			
			$option = new stdClass();
			foreach($_POST as $key=>$value){
				if(strpos($key, $this->skin_option_prefix) !== false){
					$key = sanitize_key(str_replace($this->skin_option_prefix, '', $key));
					$value = kboard_safeiframe(kboard_xssfilter($value));
					$option->{$key} = $value;
				}
			}
			
			$document = new KBContent();
			$document->initWithUID($content_uid);
			$board = new KBoard($document->board_id);
			
			if(is_user_logged_in()){
				$current_user = wp_get_current_user();
				$member_uid = $current_user->ID;
				
				if ($board->meta->comments_username_display_save === 'name') {
					$first_name = trim($current_user->user_firstname);
					$last_name = trim($current_user->user_lastname);
					$member_display = trim($last_name . ' ' . $first_name);
				}
				else if ($board->meta->comments_username_display_save === 'email') {
					$member_display = $current_user->user_email;
				}
				else {
					$member_display = $current_user->display_name;
				}
			}
			
			// 임시저장
			$temporary = new stdClass();
			$temporary->member_display = $member_display;
			$temporary->content = $content;
			$temporary->option = $option;
			$temporary->comment_hide = $comment_hide;
			$temporary->comment_anonymous = $comment_anonymous;
			$_SESSION['kboard_temporary_comments'] = $temporary;
			
			if(!$board->id){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
			else if(!$document->uid){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
			else if(!is_user_logged_in() && $board->meta->permission_comment_write){
				die('<script>alert("'.__('You do not have permission.', 'kboard-comments').'");history.go(-1);</script>');
			}
			else if(!is_user_logged_in() && !$member_display){
				die("<script>alert('".__('Please enter the author.', 'kboard-comments')."');history.go(-1);</script>");
			}
			else if(!is_user_logged_in() && !$password){
				die("<script>alert('".__('Please enter the password.', 'kboard-comments')."');history.go(-1);</script>");
			}
			else if(!$content){
				die("<script>alert('".__('Please enter the content.', 'kboard-comments')."');history.go(-1);</script>");
			}
			else if(!$content_uid){
				die("<script>alert('".__('content_uid is required.', 'kboard-comments')."');history.go(-1);</script>");
			}
			
			// 금지단어 체크
			if(!$board->isAdmin()){
				$replace = array(' ', '「', '」', '『', '』', '-', '_', '.', '(', ')', '［', '］', ',', '~', '＊', '+', '^', '♥', '★', '!', '#', '=', '­', '[', ']', '/', '▶', '▷', '<', '>', '%', ':', 'ღ', '$', '*', '♣', '♧', '☞');
				
				// 작성자 금지단어 체크
				$name_filter = kboard_name_filter(true);
				if($name_filter){
					$subject = urldecode($member_display);
					$subject = strtolower($subject);
					$subject = str_replace($replace, '', $subject);
					
					$name_filter_message = get_option('kboard_name_filter_message', '');
					
					foreach($name_filter as $filter){
						if($filter && strpos($subject, $filter) !== false){
							if(!$name_filter_message){
								$name_filter_message = sprintf(__('%s is not available.', 'kboard'), $filter);
							}
							$name_filter_message = apply_filters('kboard_comments_name_filter_message', $name_filter_message, $filter, $subject, $board);
							die("<script>alert('".$name_filter_message."');history.go(-1);</script>");
						}
					}
				}
				
				// 본문/제목/댓글 금지단어 체크
				$content_filter = kboard_content_filter(true);
				if($content_filter){
					$subject = urldecode($content);
					$subject = strtolower($subject);
					$subject = str_replace($replace, '', $subject);
					
					$content_filter_message = get_option('kboard_content_filter_message', '');
					
					foreach($content_filter as $filter){
						if($filter && strpos($subject, $filter) !== false){
							if(!$content_filter_message){
								$content_filter_message = sprintf(__('%s is not available.', 'kboard'), $filter);
							}
							$content_filter_message = apply_filters('kboard_comments_content_filter_message', $content_filter_message, $filter, $subject, $board);
							die("<script>alert('".$content_filter_message."');history.go(-1);</script>");
						}
					}
				}
			}
			
			// Captcha 검증
			if($board->useCAPTCHA()){
				if(!class_exists('KBCaptcha')){
					include_once KBOARD_DIR_PATH.'/class/KBCaptcha.class.php';
				}
				$captcha = new KBCaptcha();
			
				if(!$captcha->validate()){
					die("<script>alert('".__('CAPTCHA is invalid.', 'kboard-comments')."');history.go(-1);</script>");
				}
			}
			
			// 댓글쓰기 감소 포인트
			if($board->meta->comment_insert_down_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
				if(function_exists('mycred_add')){
					if(!is_user_logged_in()){
						die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
					}
					else{
						$balance = mycred_get_users_balance(get_current_user_id());
						if($board->meta->comment_insert_down_point > $balance){
							die('<script>alert("'.__('You have not enough points.', 'kboard-comments').'");history.go(-1);</script>');
						}
						else{
							$point = intval(get_user_meta(get_current_user_id(), 'kboard_comments_mycred_point', true));
							update_user_meta(get_current_user_id(), 'kboard_comments_mycred_point', $point + ($board->meta->comment_insert_down_point*-1));
							
							mycred_add('comment_insert_down_point', get_current_user_id(), ($board->meta->comment_insert_down_point*-1), __('Writing comment decrease points', 'kboard-comments'));
						}
					}
				}
			}
			
			do_action('kboard_comments_pre_insert', 0, $content_uid, $board);
			
			// 업로드된 파일이 있는지 확인한다. (없으면 중단)
			$upload_checker = false;
			foreach($_FILES as $key=>$value){
				if(strpos($key, $this->skin_attach_prefix) === false) continue;
				if($_FILES[$key]['tmp_name']){
					$upload_checker = true;
					break;
				}
			}
			
			$upload_attach_files = array();
			if($upload_checker){
				$upload_dir = wp_upload_dir();
				$attach_store_path = str_replace($this->abspath, '', $upload_dir['basedir']) . "/kboard_attached/{$board->id}/" . date('Ym', current_time('timestamp')) . '/';
				
				$file = new KBFileHandler();
				$file->setPath($attach_store_path);
				
				foreach($_FILES as $key=>$value){
					if(strpos($key, $this->skin_attach_prefix) === false) continue;
					$key = str_replace($this->skin_attach_prefix, '', $key);
					$key = sanitize_key($key);
					
					// SVG 파일 업로드 제한
					if(strpos($key, 'image') === 0 && isset($value['type']) && $value['type'] === 'image/svg+xml') {
						continue;
					}
					
					$upload = $file->upload($this->skin_attach_prefix . $key);
					$file_path = $upload['path'] . $upload['stored_name'];
					$file_name = $upload['original_name'];
					$metadata = $upload['metadata'];
					
					if($file_name){
						$filetype = wp_check_filetype($this->abspath . $file_path, array('jpg|jpeg|jpe'=>'image/jpeg', 'png'=>'image/png'));
						
						if(in_array($filetype['type'], array('image/jpeg', 'image/png'))){
							$image_optimize_width = intval(get_option('kboard_image_optimize_width'));
							$image_optimize_height = intval(get_option('kboard_image_optimize_height'));
							$image_optimize_quality = intval(get_option('kboard_image_optimize_quality'));
							
							$image_editor = wp_get_image_editor($this->abspath . $file_path);
							if(!is_wp_error($image_editor)){
								$is_save = false;
								
								if($image_optimize_width && $image_optimize_height){
									$image_editor->resize($image_optimize_width, $image_optimize_height);
									$is_save = true;
								}
								if(0 < $image_optimize_quality && $image_optimize_quality < 100){
									$image_editor->set_quality($image_optimize_quality);
									$is_save = true;
								}
								if($is_save){
									$image_editor->save($this->abspath . $file_path);
								}
							}
						}
						
						$attach_file = new stdClass();
						$attach_file->key = $key;
						$attach_file->path = $file_path;
						$attach_file->name = $file_name;
						$attach_file->metadata = $metadata;
						$upload_attach_files[] = $attach_file;
					}
				}
			}
			
			if($board->meta->comment_permit){
				$status = 'pending_approval';
			}
			
			$comment_list = new KBCommentList($content_uid);
			$comment_list->board = $board;
			$comment_uid = $comment_list->add($parent_uid, $member_uid, $member_display, $content, $status, $password);
			
			// 자동답변 기능이 활성화 되어 있다면 답변을 생성한다.
			$board->sidetalk_generate_comment_ai($content_uid, $comment_uid, $content);
			
			if($comment_uid && $upload_attach_files && is_array($upload_attach_files)){
				foreach($upload_attach_files as $attach_file){
					$file_key = esc_sql($attach_file->key);
					$file_path = esc_sql($attach_file->path);
					$file_name = esc_sql($attach_file->name);
					$file_size = intval(filesize($this->abspath . $file_path));
					
					$metadata = apply_filters('kboard_comments_file_metadata', $attach_file->metadata, $attach_file, $this);
					$metadata = serialize($metadata);
					$metadata = esc_sql($metadata);
					
					$present_file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `comment_uid`='$comment_uid' AND `file_key`='$file_key'");
					if($present_file){
						@unlink($this->abspath . $present_file);
						$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `file_path`='$file_path', `file_name`='$file_name', `file_size`='$file_size', `metadata`='$metadata' WHERE `comment_uid`='$comment_uid' AND `file_key`='$file_key'");
					}
					else{
						$date = date('YmdHis', current_time('timestamp'));
						$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_attached` (`content_uid`, `comment_uid`, `file_key`, `date`, `file_path`, `file_name`, `file_size`, `download_count`, `metadata`) VALUES ('0', '$comment_uid', '$file_key', '$date', '$file_path', '$file_name', '$file_size', '0', '$metadata')");
					}
				}
			}
			else if($upload_attach_files && is_array($upload_attach_files)){
				foreach($upload_attach_files as $attach_file){
					kbaord_delete_resize($this->abspath . $attach_file->path);
					@unlink($this->abspath . $attach_file->path);
				}
			}
			
			// 댓글과 미디어의 관계를 입력한다.
			$media = new KBCommentMedia();
			$media->board_id = $board->id;
			$media->comment_uid = $comment_uid;
			$media->media_group = isset($_POST['media_group']) ? sanitize_key($_POST['media_group']) : '';
			$media->createRelationships();
			
			$comment_option = new KBCommentOption($comment_uid);
			foreach($option as $key=>$value){
				$comment_option->{$key} = $value;
			}
			
			// 댓글쓰기 증가 포인트
			if($board->meta->comment_insert_up_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
				if(function_exists('mycred_add')){
					if(is_user_logged_in()){
						$point = intval(get_user_meta(get_current_user_id(), 'kboard_comments_mycred_point', true));
						update_user_meta(get_current_user_id(), 'kboard_comments_mycred_point', $point + $board->meta->comment_insert_up_point);
						
						mycred_add('comment_insert_up_point', get_current_user_id(), $board->meta->comment_insert_up_point, __('Writing comment increase points', 'kboard-comments'));
					}
				}
			}
			
			if($comment_uid){
				unset($_SESSION['kboard_temporary_comments']);
			}
			
			$next_page_url = wp_get_referer() . "#kboard-comments-{$content_uid}";
			$next_page_url = apply_filters('kboard_comments_after_executing_url', $next_page_url, $comment_uid, $content_uid);
			
			$comment = new KBComment();
			$comment->initWithUID($comment_uid);
			
			do_action('kboard_comments_execute_pre_redirect', $next_page_url, $comment, $content, $board);
			
			// 댓글 알림 설정이 활성화가 되어 있다면 메일을 보낸다.
			if($board->meta->comment_alerts &&is_user_logged_in()){
				$user = get_userdata($document->member_uid);
				$email = $user ? $user->user_email : null;
				
				$url = new KBUrl();
				$mail = kboard_mail();
				$mail->to = $email;
				$mail->title = apply_filters('kboard_new_comment_alerts_subject', '['.__('신규 댓글', 'kboard').'] ' .__('작성하신 글에 새로운 댓글이 달렸습니다.', 'kboard'), $this);
				$mail->content = apply_filters('kboard_new_comment_alerts_message', $content, $this);
				$mail->url = $url->getDocumentRedirect($document->uid);
				$mail->url_name = __('Go to Homepage', 'kboard');
				$mail->send();
			}
			
			wp_redirect($next_page_url);
			exit;
		}
		wp_die(__('You do not have permission.', 'kboard-comments'));
	}
	
	/**
	 * 댓글 삭제
	 */
	public function delete(){
		header("Content-Type: text/html; charset=UTF-8");
		
		// 되돌아오는 페이지 주소 깨지는 버그 해결
		$_SERVER['HTTP_REFERER'] = preg_replace('/\{[^}]*\}/', '%', wp_get_raw_referer());
		
		if(!wp_get_referer()){
			wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		}
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$password = isset($_POST['password'])?sanitize_text_field($_POST['password']):'';
		
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$comment = new KBComment();
		$comment->initWithUID($uid);
		$board = $comment->getBoard();
		
		if(!$comment->isEditor() && !$comment->checkPassword($password, false)){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		if(!isset($_REQUEST['kboard-comments-delete-nonce'])){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		if($password){
			if(!wp_verify_nonce($_REQUEST['kboard-comments-delete-nonce'], "kboard-comments-delete-{$comment->getPassword()}")){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
		}
		else{
			if(!wp_verify_nonce($_REQUEST['kboard-comments-delete-nonce'], "kboard-comments-delete-{$comment->uid}")){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
		}
		
		do_action('kboard_comments_pre_delete', $comment->uid, $comment->content_uid, $board);
		
		$comment->delete();
		
		if($comment->getPassword() && kboard_password_verify($password, $comment->getPassword())){
			// 팝업창으로 비밀번호 확인 후 opener 윈도우를 새로고침 한다.
			echo '<script>';
			echo 'opener.window.location.reload();';
			echo 'window.close();';
			echo '</script>';
		}
		else{
			// 삭제권한이 있는 사용자일 경우 팝업창은 없기 때문에 페이지 이동한다.
			wp_redirect(wp_get_referer());
		}
		exit;
	}
	
	/**
	 * 댓글 수정
	 */
	public function update(){
		header("Content-Type: text/html; charset=UTF-8");
		
		// 되돌아오는 페이지 주소 깨지는 버그 해결
		$_SERVER['HTTP_REFERER'] = preg_replace('/\{[^}]*\}/', '%', wp_get_raw_referer());
		
		if(!wp_get_referer()){
			wp_die(__('This page is restricted from external access.', 'kboard-comments'));
		}
		
		$_POST = stripslashes_deep($_POST);
		
		$content = isset($_POST['content'])?$_POST['content']:'';
		$comment_content = isset($_POST['comment_content'])?$_POST['comment_content']:'';
		$content = $content?$content:$comment_content;
		
		$uid = isset($_GET['uid'])?intval($_GET['uid']):'';
		$password = isset($_POST['password'])?sanitize_text_field($_POST['password']):'';
		
		if(!$uid){
			die("<script>alert('".__('uid is required.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!$content){
			die("<script>alert('".__('Please enter the content.', 'kboard-comments')."');history.go(-1);</script>");
		}
		else if(!is_user_logged_in() && !$password){
			die("<script>alert('".__('Please log in to continue.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		$comment = new KBComment();
		$comment->initWithUID($uid);
		$board = $comment->getBoard();
		
		if(!$comment->isEditor() && !$comment->checkPassword($password, false)){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		if(!isset($_REQUEST['kboard-comments-update-nonce'])){
			die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
		}
		
		if($password){
			if(!wp_verify_nonce($_REQUEST['kboard-comments-update-nonce'], "kboard-comments-update-{$comment->getPassword()}")){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
		}
		else{
			if(!wp_verify_nonce($_REQUEST['kboard-comments-update-nonce'], "kboard-comments-update-{$comment->uid}")){
				die("<script>alert('".__('You do not have permission.', 'kboard-comments')."');history.go(-1);</script>");
			}
		}
		
		do_action('kboard_comments_pre_update', $comment->uid, $comment->content_uid, $board);
		
		$comment->content = $content;
		$comment = apply_filters('kboard_comments_update_data', $comment, $board->id);
		$comment->update();
		
		$option = new stdClass();
		foreach($_POST as $key=>$value){
			if(strpos($key, $this->skin_option_prefix) !== false){
				$key = sanitize_key(str_replace($this->skin_option_prefix, '', $key));
				$value = kboard_safeiframe(kboard_xssfilter($value));
				$comment->option->{$key} = $value;
			}
		}
		
		echo '<script>';
		echo 'opener.window.location.reload();';
		echo 'window.close();';
		echo '</script>';
		exit;
	}
	
	/**
	 * 댓글 정보 업데이트
	 */
	public function comments_list_update(){
		if(!current_user_can('manage_kboard')) wp_send_json_error(array('message'=>'권한이 없습니다.'), 403);
		if(!check_ajax_referer('kboard_comments_list_update', 'security', false)) wp_send_json_error(array('message'=>'보안 확인에 실패했습니다. 페이지를 새로고침해 주세요.'), 403);
		$uid_input = isset($_POST['comment_uid']) && is_scalar($_POST['comment_uid']) ? (string)wp_unslash($_POST['comment_uid']) : '';
		$uid = preg_match('/^[1-9][0-9]*$/D', $uid_input) ? intval($uid_input) : 0;
		$field = isset($_POST['update_field']) && is_scalar($_POST['update_field']) ? sanitize_key(wp_unslash($_POST['update_field'])) : '';
		if($uid < 1 || !in_array($field, array('status', 'date'), true)) wp_send_json_error(array('message'=>'잘못된 수정 요청입니다.'), 400);
		$comment = new KBComment();
		$comment->initWithUID($uid);
		if(!$comment->uid) wp_send_json_error(array('message'=>'댓글을 찾을 수 없습니다.'), 404);
		$message = '';
		if($field == 'status'){
			if(!isset($_POST['value']) || !is_scalar($_POST['value'])) wp_send_json_error(array('message'=>'상태를 선택해 주세요.'), 400);
			$status = sanitize_key(wp_unslash($_POST['value']));
			if(!in_array($status, array('', 'pending_approval'), true)) wp_send_json_error(array('message'=>'잘못된 상태입니다.'), 400);
			$comment->status = $status;
		}
		else{
			if(!isset($_POST['date'], $_POST['time']) || !is_scalar($_POST['date']) || !is_scalar($_POST['time'])) wp_send_json_error(array('message'=>'날짜와 시간을 입력해 주세요.'), 400);
			$date = sanitize_text_field(wp_unslash($_POST['date']));
			$time = sanitize_text_field(wp_unslash($_POST['time']));
			$resolved = kboard_admin_resolve_date($comment->created, $date, $time);
			if(!$resolved) wp_send_json_error(array('message'=>'유효한 날짜나 시간을 입력해 주세요. 저장된 날짜도 사용할 수 없습니다.'), 400);
			$comment->created = $resolved['value'];
			$message = $resolved['message'];
		}
		$comment->update();
		do_action('kboard_comments_list_update', $comment);
		$saved = new KBComment();
		$saved->initWithUID($uid);
		if(!$saved->uid || ($field == 'date' && $saved->created != $resolved['value']) || ($field == 'status' && $saved->status != $status)) wp_send_json_error(array('message'=>'수정 결과를 확인할 수 없습니다. 목록을 새로고침해 주세요.'), 500);
		wp_send_json_success(array('message'=>$message ? $message : '저장했습니다.', 'date'=>kboard_admin_date_parts($saved->created), 'status'=>$saved->status));
	}
	
	/**
	 * 댓글 좋아요
	 */
	public function commentLike(){
		check_ajax_referer('kboard_ajax_security', 'security');
		if(isset($_POST['comment_uid']) && intval($_POST['comment_uid'])){
			$comment = new KBComment();
			$comment->initWithUID($_POST['comment_uid']);
			if($comment->uid){
				$board = $comment->getBoard();
				if($board->isVote()){
					$args['target_uid'] = $comment->uid;
					$args['target_type'] = KBVote::$TYPE_COMMENT;
					$args['target_vote'] = KBVote::$VOTE_LIKE;
					$vote = new KBVote();
					if($vote->isExists($args) === 0){
						if($vote->insert($args)){
							$comment->like += 1;
							$comment->vote = $comment->like - $comment->unlike;
							$comment->update();
							
							do_action('kboard_comment_like', $comment, $board);
							
							wp_send_json(array('result'=>'success', 'data'=>array('vote'=>intval($comment->vote), 'like'=>intval($comment->like), 'unlike'=>intval($comment->unlike))));
						}
					}
					else{
						wp_send_json(array('result'=>'error', 'message'=>__('You have already voted.', 'kboard-comments')));
					}
				}
				else if(!is_user_logged_in()){
					wp_send_json(array('result'=>'error', 'message'=>__('Please log in to continue.', 'kboard-comments')));
				}
			}
		}
		wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard-comments')));
	}
	
	/**
	 * 댓글 싫어요
	 */
	public function commentUnlike(){
		check_ajax_referer('kboard_ajax_security', 'security');
		if(isset($_POST['comment_uid']) && intval($_POST['comment_uid'])){
			$comment = new KBComment();
			$comment->initWithUID($_POST['comment_uid']);
			if($comment->uid){
				$board = $comment->getBoard();
				if($board->isVote()){
					$args['target_uid'] = $comment->uid;
					$args['target_type'] = KBVote::$TYPE_COMMENT;
					$args['target_vote'] = KBVote::$VOTE_UNLIKE;
					$vote = new KBVote();
					if($vote->isExists($args) === 0){
						if($vote->insert($args)){
							$comment->unlike += 1;
							$comment->vote = $comment->like - $comment->unlike;
							$comment->update();
							
							do_action('kboard_comment_unlike', $comment, $board);
							
							wp_send_json(array('result'=>'success', 'data'=>array('vote'=>intval($comment->vote), 'like'=>intval($comment->like), 'unlike'=>intval($comment->unlike))));
						}
					}
					else{
						wp_send_json(array('result'=>'error', 'message'=>__('You have already voted.', 'kboard-comments')));
					}
				}
				else if(!is_user_logged_in()){
					wp_send_json(array('result'=>'error', 'message'=>__('Please log in to continue.', 'kboard-comments')));
				}
			}
		}
		wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard-comments')));
	}
}
