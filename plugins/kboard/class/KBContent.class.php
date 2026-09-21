<?php
/**
 * KBoard 게시글
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContent {
	
	private $upload_attach_files;
	private $filter_keys;
	private $abspath;
	
	// 스킨에서 사용 할 첨부파일 input[type=file] 이름의 prefix를 정의한다.
	static $SKIN_ATTACH_PREFIX = 'kboard_attach_';
	// 스킨에서 사용 할 사용자 정의 옵션 input, textarea, select 이름의 prefix를 정의한다.
	static $SKIN_OPTION_PREFIX = 'kboard_option_';
	
	var $board;
	var $board_id;
	var $option;
	var $attach;
	var $attach_store_path;
	var $thumbnail_store_path;
	var $row;
	var $execute_action;
	var $thumbnail;
	var $previous_status;
	var $previous_board_id;
	var $tree_category_depth;
	var $new_password;
	var $class_type;
	
	public function __construct($board_id=''){
		$upload_dir = wp_upload_dir();
		$basedir = explode('wp-content', $upload_dir['basedir']);
		$this->abspath = untrailingslashit($basedir[0]);
		$this->row = new stdClass();
		$this->execute_action = 'insert';
		if($board_id) $this->setBoardID($board_id);
	}
	
	public function __get($name){
		$value = '';
		if(isset($this->row->{$name})){
			if(in_array($name, array('title', 'content'))){
				if(isset($this->row->status) && $this->row->status == 'pending_approval' && in_array(kboard_mod(), array('list', 'document'))){
					if($this->isEditor()){
						switch($name){
							case 'title': return apply_filters('kboard_pending_approval_title', sprintf(__('&#91;Pending&#93; %s', 'kboard'), $this->row->title), $this); break;
							case 'content': return apply_filters('kboard_pending_approval_content', sprintf(__('<p>&#91;Waiting for administrator Approval.&#93;</p>%s', 'kboard'), $this->row->content), $this); break;
						}
					}
					else{
						switch($name){
							case 'title': return apply_filters('kboard_pending_approval_title', __('&#91;Pending&#93; Waiting for administrator Approval.', 'kboard'), $this); break;
							case 'content': return apply_filters('kboard_pending_approval_content', __('&#91;Waiting for administrator Approval.&#93;', 'kboard'), $this); break;
						}
					}
				}
			}
			if($name == 'password' && kboard_mod() == 'editor' && $this->row->{$name}){
				$value = kboard_password_mask();
			}
			else{
				$value = $this->row->{$name};
			}
		}
		return apply_filters('kboard_content_value', $value, $name, $this);
	}
	
	public function __set($name, $value){
		$this->row->{$name} = $value;
	}

	/**
	 * 저장된 비밀번호 값을 반환한다.
	 * @return string
	 */
	public function getPassword(){
		if(isset($this->row->password)){
			return $this->row->password;
		}
		return '';
	}
	
	/**
	 * 게시판 ID를 입력받는다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id){
		$this->board_id = intval($board_id);
		$this->board = new KBoard($this->board_id);
		
		// 첨부파일 업로드 경로를 만든다.
		$upload_dir = wp_upload_dir();
		$this->attach_store_path = str_replace($this->abspath, '', $upload_dir['basedir']) . "/kboard_attached/{$this->board_id}/" . date('Ym', current_time('timestamp')) . '/';
		$this->thumbnail_store_path = str_replace($this->abspath, '', $upload_dir['basedir']) . "/kboard_thumbnails/{$this->board_id}/" . date('Ym', current_time('timestamp')) . '/';
	}
	
	/**
	 * 게시글 고유번호를 입력받아 정보를 초기화한다.
	 * @param int $uid
	 * @return KBContent
	 */
	public function initWithUID($uid){
		global $wpdb;
		$uid = intval($uid);
		if($uid){
			$this->row = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='$uid' LIMIT 1");
			if($this->row){
				$this->setBoardID($this->row->board_id);
			}
			else{
				$this->row = new stdClass();
			}
		}
		else{
			$this->row = new stdClass();
		}
		$this->initOptions();
		$this->checkNoticeExpiration();
		$this->initAttachedFiles();
		$this->setExecuteAction();
		$this->previous_status = $this->status;
		$this->previous_board_id = $this->board_id;
		$this->new_password = $this->getPassword();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 게시글 정보를 입력받는다.
	 * @param object $row
	 * @return KBContent
	 */
	public function initWithRow($row){
		global $wpdb;
		if($row){
			$this->row = $row;
			$this->setBoardID($this->row->board_id);
		}
		else{
			$this->row = new stdClass();
		}
		$this->initOptions();
		$this->initAttachedFiles();
		$this->setExecuteAction();
		$this->previous_status = $this->status;
		$this->previous_board_id = $this->board_id;
		$this->new_password = $this->getPassword();
		$wpdb->flush();
		return $this;
	}
	
	/**
	 * 실행 액션을 설정한다.
	 * @param string $action
	 */
	public function setExecuteAction($action=''){
		if($action){
			$this->execute_action = $action;
		}
		else if($this->uid && $this->date){
			$this->execute_action = 'update';
		}
		else{
			$this->execute_action = 'insert';
		}
	}
	
	/**
	 * 게시글을 등록/수정한다.
	 * @return int
	 */
	public function execute(){
		$board = $this->getBoard();
		
		if($this->execute_action == 'update'){
			/*
			 * 기존 게시글 업데이트
			 */
			
			// 게시글 수정 전에 액션 훅 실행
			do_action('kboard_pre_document_update', $this->uid, $this->board_id, $this, $board);
			
			$this->initUploadAttachFiles();
			$this->updateContent();
			$this->setThumbnail();
			$this->updateOptions();
			$this->updateAttach();
			$this->addMediaRelationships();
			
			// 게시글 수정 액션 훅 실행
			do_action('kboard_document_update', $this->uid, $this->board_id, $this, $board);
			
			// 임시저장 데이터 삭제
			$this->cleanTemporary();
			
			return $this->uid;
		}
		else if($this->execute_action == 'insert'){
			/*
			 * 신규 게시글 등록
			 */
			
			// Captcha 검증
			if($board->useCAPTCHA()){
				$fields = $board->fields()->getSkinFields();
				if(isset($fields['captcha'])){
					if(!class_exists('KBCaptcha')){
						include_once 'KBCaptcha.class.php';
					}
					$captcha = new KBCaptcha();
					
					if(!$captcha->validate()){
						die("<script>alert('".__('CAPTCHA is invalid.', 'kboard')."');history.go(-1);</script>");
					}
				}
			}
			
			if($board->meta->permit){
				// 게시글 승인 대기
				$this->status = 'pending_approval';
			}
			
			// 글쓴이의 id값 등록
			$this->member_uid = get_current_user_id();
			
			// 게시글 입력 전에 액션 훅 실행
			do_action('kboard_pre_document_insert', 0, $this->board_id, $this, $board);
			
			$this->initUploadAttachFiles();
			if($this->insertContent()){
				$this->setThumbnail();
				$this->updateOptions();
				$this->updateAttach();
				$this->addMediaRelationships();
				
				// 게시판 설정에 알림 이메일이 설정되어 있으면 메일을 보낸다.
				if($board->meta->latest_alerts){
					if (!($board->meta->exclude_admin_reply_alert && $board->isAdmin() && $this->parent_uid)) {
						$this->initAttachedFiles();
				
						/*
						 * https://www.cosmosfarm.com/threads/document/3025
						 * 메일 제목에 게시글이 등록된 게시판 이름 추가해서 보낸다.
						 */
						$url = new KBUrl();
						$mail = kboard_mail();
						$mail->to = explode(',', $board->meta->latest_alerts);
						$mail->title = apply_filters('kboard_latest_alerts_subject', '['.__('KBoard new document', 'kboard').'] ' . $board->board_name . ' - ' . $this->title, $this);
						$mail->content = apply_filters('kboard_latest_alerts_message', $this->getDocumentOptionsHTML() . $this->content, $this);
						$mail->url = $url->getDocumentRedirect($this->uid);
						$mail->url_name = __('Go to Homepage', 'kboard');
						$mail->attachments = apply_filters('kboard_latest_alerts_attachments', $this->getMailAttachments(), $this);
						$mail->send();
				
						$this->deleteMailAttachments();
					}
				}
				
				// 게시글 입력 액션 훅 실행
				do_action('kboard_document_insert', $this->uid, $this->board_id, $this, $board);
				do_action("kboard_document_insert_{$this->board_id}", $this->uid, $this->board_id, $this, $board);
				
				if($this->parent_uid){
					$parent = new KBContent();
					$parent->initWithUID($this->parent_uid);
					
					do_action('kboard_reply_insert', $this, $parent, $board);
					do_action("kboard_reply_insert_{$this->board_id}", $this, $parent, $board);
				}
				
				// 임시저장 데이터 삭제
				$this->cleanTemporary();
			}
			
			return $this->uid;
		}
		return 0;
	}
	
	/**
	 * 게시글을 등록한다.
	 * @return int
	 */
	public function insertContent($data = array()){
		global $wpdb;
		
		// WordPress calculates offsets from UTC.
		date_default_timezone_set('UTC');
		
		if(!$data){
			$data['board_id'] = $this->board_id;
			$data['parent_uid'] = $this->parent_uid;
			$data['member_uid'] = $this->member_uid;
			$data['member_display'] = $this->member_display;
			$data['title'] = $this->title;
			$data['content'] = $this->content;
			$data['date'] = date('YmdHis', current_time('timestamp'));
			$data['update'] = $data['date'];
			$data['view'] = 0;
			$data['comment'] = 0;
			$data['like'] = 0;
			$data['unlike'] = 0;
			$data['vote'] = 0;
			$data['category1'] = $this->category1;
			$data['category2'] = $this->category2;
			$data['category3'] = $this->category3;
			$data['category4'] = $this->category4;
			$data['category5'] = $this->category5;
			$data['secret'] = $this->secret;
			$data['notice'] = $this->notice;
			$data['search'] = $this->search;
			$data['thumbnail_file'] = '';
			$data['thumbnail_name'] = '';
			$data['status'] = $this->status;
			$data['password'] = $this->new_password;
			$data['notice_expired_date'] = $this->notice_expired_date;
		}
		
		// 입력할 데이터 필터
		$data = apply_filters('kboard_insert_data', $data, $this->board_id);
		
		// sanitize
		$data['board_id'] = isset($data['board_id'])?intval($data['board_id']):0;
		$data['parent_uid'] = isset($data['parent_uid'])?intval($data['parent_uid']):0;
		$data['member_uid'] = isset($data['member_uid'])?intval($data['member_uid']):0;
		$data['member_display'] = isset($data['member_display'])?sanitize_text_field($data['member_display']):'';
		$data['title'] = isset($data['title'])?kboard_safeiframe(kboard_xssfilter($data['title'])):'';
		$data['content'] = isset($data['content'])?kboard_safeiframe(kboard_xssfilter($data['content'])):'';
		$data['date'] = isset($data['date'])?sanitize_key($data['date']):date('YmdHis', current_time('timestamp'));
		$data['update'] = isset($data['update'])?sanitize_key($data['update']):$data['date'];
		$data['view'] = isset($data['view'])?intval($data['view']):0;
		$data['comment'] = isset($data['comment'])?intval($data['comment']):0;
		$data['like'] = isset($data['like'])?intval($data['like']):0;
		$data['unlike'] = isset($data['unlike'])?intval($data['unlike']):0;
		$data['vote'] = isset($data['vote'])?intval($data['vote']):0;
		$data['category1'] = isset($data['category1'])?sanitize_text_field($data['category1']):'';
		$data['category2'] = isset($data['category2'])?sanitize_text_field($data['category2']):'';
		$data['category3'] = isset($data['category3'])?sanitize_text_field($data['category3']):'';
		$data['category4'] = isset($data['category4'])?sanitize_text_field($data['category4']):'';
		$data['category5'] = isset($data['category5'])?sanitize_text_field($data['category5']):'';
		$data['secret'] = isset($data['secret'])?sanitize_key($data['secret']):'';
		$data['notice'] = isset($data['notice'])?sanitize_key($data['notice']):'';
		$data['search'] = isset($data['search'])?intval(($data['secret'] && $data['search']==1)?'2':$data['search']):'1';
		$data['thumbnail_file'] = isset($data['thumbnail_file'])?sanitize_text_field($data['thumbnail_file']):'';
		$data['thumbnail_name'] = isset($data['thumbnail_name'])?sanitize_text_field($data['thumbnail_name']):'';
		$data['status'] = isset($data['status'])?sanitize_key($data['status']):'';
		$data['password'] = isset($data['password'])?kboard_password_prepare($data['password']):'';
		$data['notice_expired_date'] = isset($data['notice_expired_date']) ? sanitize_text_field($data['notice_expired_date']) : '';
		
		if(!$data['member_display']){
			$data['member_display'] = __('Anonymous', 'kboard');
		}
		
		$status_list = kboard_content_status_list();
		if(!in_array($data['status'], array_keys($status_list))){
			$data['status'] = '';
		}
		
		$data['title'] = $this->titleStripTags($data['title']);
		$data['title'] = $this->encodeEmoji($data['title']);
		
		$data['content'] = $this->encodeEmoji($data['content']);
		
		// 불필요한 데이터 필터링
		$data = kboard_array_filter($data, array('board_id', 'parent_uid', 'member_uid', 'member_display', 'title', 'content', 'date', 'update', 'view', 'comment', 'like', 'unlike', 'vote', 'category1', 'category2', 'category3', 'category4', 'category5', 'secret', 'notice', 'search', 'thumbnail_file', 'thumbnail_name', 'status', 'password', 'notice_expired_date'));
		
		if($data['board_id'] && $data['title']){
			foreach($data as $key=>$value){
				$this->{$key} = $value;
				
				$value = esc_sql($value);
				$insert_key[] = "`$key`";
				$insert_data[] = "'$value'";
			}
			
			$board = $this->getBoard();
			$board_total = $board->getTotal();
			$board_list_total = $board->getListTotal();
			
			if($this->status != 'trash'){
				$board->meta->total = $board_total + 1;
				$board->meta->list_total = $board_list_total + 1;
			}
			else{
				$board->meta->total = $board_total + 1;
			}
			
			$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_content` (".implode(',', $insert_key).") VALUES (".implode(',', $insert_data).")");
			
			$this->uid = $wpdb->insert_id;
			
			$this->insertPost($this->uid, $data['member_uid']);
			
			$wpdb->flush();
			
			return $this->uid;
		}
		return 0;
	}
	
	/**
	 * 게시글 정보를 수정한다.
	 */
	public function updateContent($data = array()){
		global $wpdb;
		
		// WordPress calculates offsets from UTC.
		date_default_timezone_set('UTC');
		
		if($this->uid){
			if(!$data){
				$data['board_id'] = $this->board_id;
				$data['parent_uid'] = $this->parent_uid?$this->parent_uid:0;
				$data['member_uid'] = $this->member_uid;
				$data['member_display'] = $this->member_display;
				$data['title'] = $this->title;
				$data['content'] = $this->content;
				$data['date'] = $this->date;
				$data['update'] = $this->update;
				$data['view'] = $this->view;
				$data['comment'] = $this->comment;
				$data['like'] = $this->like;
				$data['unlike'] = $this->unlike;
				$data['vote'] = $this->vote;
				$data['category1'] = $this->category1;
				$data['category2'] = $this->category2;
				$data['category3'] = $this->category3;
				$data['category4'] = $this->category4;
				$data['category5'] = $this->category5;
				$data['secret'] = $this->secret;
				$data['notice'] = $this->notice;
				$data['search'] = $this->search;
				$data['thumbnail_file'] = $this->thumbnail_file;
				$data['thumbnail_name'] = $this->thumbnail_name;
				$data['status'] = $this->status;
				if($this->member_uid || $this->getPassword()) $data['password'] = $this->new_password;
				$data['notice_expired_date'] = $this->notice_expired_date;
			}
			
			// 수정할 데이터 필터
			$data = apply_filters('kboard_update_data', $data, $this->board_id);
			
			// sanitize
			if(isset($data['board_id'])) $data['board_id'] = intval($data['board_id']);
			if(isset($data['parent_uid'])) $data['parent_uid'] = intval($data['parent_uid']);
			if(isset($data['member_uid'])) $data['member_uid'] = intval($data['member_uid']);
			if(isset($data['member_display'])) $data['member_display'] = sanitize_text_field($data['member_display']);
			if(isset($data['title'])) $data['title'] = kboard_safeiframe(kboard_xssfilter($data['title']));
			if(isset($data['content'])) $data['content'] = kboard_safeiframe(kboard_xssfilter($data['content']));
			if(isset($data['date'])) $data['date'] = sanitize_key($data['date']);
			$data['update'] = date('YmdHis', current_time('timestamp'));
			if(isset($data['view'])) $data['view'] = intval($data['view']);
			if(isset($data['comment'])) $data['comment'] = intval($data['comment']);
			if(isset($data['like'])) $data['like'] = intval($data['like']);
			if(isset($data['unlike'])) $data['unlike'] = intval($data['unlike']);
			if(isset($data['vote'])) $data['vote'] = intval($data['vote']);
			if(isset($data['category1'])) $data['category1'] = sanitize_text_field($data['category1']);
			if(isset($data['category2'])) $data['category2'] = sanitize_text_field($data['category2']);
			if(isset($data['category3'])) $data['category3'] = sanitize_text_field($data['category3']);
			if(isset($data['category4'])) $data['category4'] = sanitize_text_field($data['category4']);
			if(isset($data['category5'])) $data['category5'] = sanitize_text_field($data['category5']);
			if(isset($data['secret'])) $data['secret'] = sanitize_key($data['secret']);
			if(isset($data['notice'])) $data['notice'] = sanitize_key($data['notice']);
			if(isset($data['search'])) $data['search'] = intval($data['search']);
			if(isset($data['thumbnail_file'])){
				$data['thumbnail_file'] = sanitize_text_field($data['thumbnail_file']);
				if(!$this->getSafeThumbnailFilePath($data['thumbnail_file'])){
					$data['thumbnail_file'] = '';
					$data['thumbnail_name'] = '';
				}
			}
			if(isset($data['thumbnail_name'])) $data['thumbnail_name'] = sanitize_text_field($data['thumbnail_name']);
			if(isset($data['status'])) $data['status'] = sanitize_key($data['status']);
			if(isset($data['password'])) $data['password'] = kboard_password_prepare($data['password']);
			if(isset($data['notice_expired_date'])) $data['notice_expired_date'] = sanitize_text_field($data['notice_expired_date']);
			
			if(isset($data['member_display']) && !$data['member_display']){
				$data['member_display'] = __('Anonymous', 'kboard');
			}
			
			$status_list = kboard_content_status_list();
			if(isset($data['status']) && !in_array($data['status'], array_keys($status_list))){
				$data['status'] = '';
			}
			
			if(isset($data['title'])){
				$data['title'] = $this->titleStripTags($data['title']);
				$data['title'] = $this->encodeEmoji($data['title']);
			}
			
			if(isset($data['content'])){
				$data['content'] = $this->encodeEmoji($data['content']);
			}
			
			// 불필요한 데이터 필터링
			$data = kboard_array_filter($data, array('board_id', 'parent_uid', 'member_uid', 'member_display', 'title', 'content', 'date', 'update', 'view', 'comment', 'like', 'unlike', 'vote', 'category1', 'category2', 'category3', 'category4', 'category5', 'secret', 'notice', 'search', 'thumbnail_file', 'thumbnail_name', 'status', 'password', 'notice_expired_date'));
			
			foreach($data as $key=>$value){
				$this->{$key} = $value;
				
				$value = esc_sql($value);
				$update[] = "`$key`='$value'";
			}
			
			if(isset($data['status']) && $this->previous_status != $data['status']){
				if($data['status'] == 'trash'){
					$this->moveReplyToTrash($this->uid);
				}
				else if($this->previous_status == 'trash'){
					$this->restoreReplyFromTrash($this->uid);
				}
			}
			
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET ".implode(',', $update)." WHERE `uid`='{$this->uid}'");
			
			if(isset($data['board_id']) && $this->previous_board_id != $data['board_id']){
				$this->changeBoardID($data['board_id']);
			}
			
			$post_id = $this->getPostID();
			if($post_id){
				if($data['search'] == 3){
					$this->deletePost($post_id);
				}
				else{
					$this->updatePost($post_id, $data['member_uid']);
				}
			}
			else{
				$this->insertPost($this->uid, $data['member_uid']);
			}
			
			$wpdb->flush();
		}
	}
	
	/**
	 * posts 테이블에 내용을 입력한다.
	 * @param int $content_uid
	 * @param int $member_uid
	 */
	public function insertPost($content_uid, $member_uid){
		if($content_uid && $this->search>0 && $this->search<3){
			$args = array(
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret || $this->search==2)?'':$this->content,
				'post_status'   => 'publish',
				'comment_status'=> 'closed',
				'ping_status'   => 'closed',
				'post_name'     => $content_uid,
				'post_parent'   => $this->board_id,
				'post_type'     => 'kboard'
			);
			wp_insert_post($args);
			// Rank Math 검색엔진 최적화 플러그인 실행 시 글 작성 오류 수정
			if(defined('RANK_MATH_VERSION')){
				$this->uid = $content_uid;
			}
			add_action('kboard_document_insert', array($this, 'setPostThumbnail'), 10, 4);
		}
	}
	
	/**
	 * 게시글 수정시 Yoast SEO 플러그인 관련 버그 해결
	 */
	public function preUpdatePost(){
		if(defined('WPSEO_VERSION')){
			remove_all_actions('save_post');
			remove_all_actions('wp_insert_post');
		}
	}
	
	/**
	 * posts 테이블에 내용을 수정한다.
	 * @param int $post_id
	 * @param int $member_uid
	 */
	public function updatePost($post_id, $member_uid){
		if($post_id && $this->search>0 && $this->search<3){
			add_action('save_post_kboard', array($this, 'preUpdatePost'));
			$args = array(
				'ID'            => $post_id,
				'post_author'   => $member_uid,
				'post_title'    => $this->title,
				'post_content'  => ($this->secret || $this->search==2)?'':$this->content,
				'post_status'	=> $this->status == 'trash' ? 'trash' : 'publish',
				'post_parent'   => $this->board_id
			);
			wp_update_post($args);
			add_action('kboard_document_update', array($this, 'setPostThumbnail'), 10, 4);
		}
	}
	
	/**
	 * posts 테이블에 내용을 삭제한다.
	 * @param int $post_id
	 */
	public function deletePost($post_id){
		if(has_post_thumbnail($post_id)){
			$attachment_id = get_post_thumbnail_id($post_id);
			wp_delete_attachment($attachment_id, true);
			delete_post_thumbnail($post_id);
		}
		wp_delete_post($post_id);
	}
	
	/**
	 * 안전한 썸네일 파일 경로를 반환한다.
	 * @param string $thumbnail_file
	 * @return string
	 */
	private function getSafeThumbnailFilePath($thumbnail_file){
		if(!$thumbnail_file){
			return '';
		}

		$upload_dir = wp_upload_dir();
		$base_path = realpath($upload_dir['basedir'] . '/kboard_thumbnails');
		$file_path = realpath($this->abspath . $thumbnail_file);

		if(!$base_path || !$file_path || !is_file($file_path)){
			return '';
		}

		$base_path = trailingslashit(wp_normalize_path($base_path));
		$file_path = wp_normalize_path($file_path);
		if(strpos($file_path, $base_path) !== 0){
			return '';
		}

		return $file_path;
	}

	/**
	 * post에 썸네일을 등록한다.
	 * @param int $uid
	 * @param int $board_id
	 * @param KBContent $content
	 * @param KBoard $board
	 */
	public function setPostThumbnail($uid, $board_id, $content, $board){
		global $wpdb;
		
		if($uid){
			$post_id = $content->getPostId();
			$thumbnail = $wpdb->get_row("SELECT `thumbnail_file`, `thumbnail_name` FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$uid}'");
			
			if($thumbnail->thumbnail_file){
				$file_path = $this->getSafeThumbnailFilePath($thumbnail->thumbnail_file);
				if(!$file_path){
					return;
				}
				$file = file_get_contents($file_path);
				
				if($file){
					$file_type = wp_check_filetype(basename($thumbnail->thumbnail_file), null);
					$upload_dir = wp_upload_dir();
					$upload_file = $upload_dir['path'] . '/' . basename($thumbnail->thumbnail_file);
					
					$save_result = file_put_contents($upload_file, $file);
					
					if($save_result !== false){
						$attachment = array(
							'post_mime_type' => $file_type['type'],
							'post_title' => $thumbnail->thumbnail_name,
							'post_content' => '',
							'post_status' => 'inherit'
						);
						
						$attach_id = wp_insert_attachment($attachment, $upload_file);
						
						if($attach_id){
							if(!function_exists('wp_generate_attachment_metadata')){
								include_once(ABSPATH . 'wp-admin/includes/image.php');
							}
							$media = get_post($attach_id);
							$fullsize_path = get_attached_file($media->ID);
							$attach_data = wp_generate_attachment_metadata($attach_id, $fullsize_path);
							wp_update_attachment_metadata($attach_id, $attach_data);
							if(has_post_thumbnail($post_id)){
								$attachment_id = get_post_thumbnail_id($post_id);
								wp_delete_attachment($attachment_id, true);
							}
							set_post_thumbnail($post_id, $media->ID);
						}
					}
				}
			}
			else{
				if(has_post_thumbnail($post_id)){
					$attachment_id = get_post_thumbnail_id($post_id);
					wp_delete_attachment($attachment_id, true);
					delete_post_thumbnail($post_id);
				}
			}
		}
	}
	
	/**
	 * 게시글의 조회수를 증가한다.
	 */
	public function increaseView(){
		global $wpdb;
		if(!isset($_SESSION['increased_document_uid'])) $_SESSION['increased_document_uid'] = array();
		if($this->uid && !in_array($this->uid, $_SESSION['increased_document_uid'])){
			$_SESSION['increased_document_uid'][] = $this->uid;
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `view`=`view`+1 WHERE `uid`='{$this->uid}'");
			$this->view = $this->view + 1;
		}
	}
	
	/**
	 * 게시글 옵션 정보를 초기화 한다.
	 * @return string
	 */
	public function initOptions(){
		$this->option = new KBContentOption($this->uid);
	}
	
	/**
	 * 게시글 첨부파일 정보를 초기화 한다.
	 * @return array
	 */
	public function initAttachedFiles(){
		global $wpdb;
		$this->attach = array();
		if($this->uid){
			$url = new KBUrl();
			$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='{$this->uid}' ORDER BY `file_key`*1");
			foreach($result as $file){
				$file_info = array(
					0 => $file->file_path,
					1 => $file->file_name,
					2 => $url->getDownloadURLWithAttach($this->uid, $file->file_key),
					3 => intval($file->file_size),
					4 => intval($file->download_count),
					'file_path' => $file->file_path,
					'file_name' => $file->file_name,
					'file_size' => intval($file->file_size),
					'download_url' => $url->getDownloadURLWithAttach($this->uid, $file->file_key),
					'download_count' => intval($file->download_count),
					'metadata' => ($file->metadata ? unserialize($file->metadata) : array())
				);
				
				$file_info = apply_filters('kboard_content_file_info', $file_info, $file, $this);
				
				$this->attach[$file->file_key] = $file_info;
			}
		}
		$this->attach = (object) $this->attach;
		return $this->attach;
	}
	
	/**
	 * 첨부파일을 초기화한다.
	 */
	public function initUploadAttachFiles(){
		global $wpdb;
		if(!$this->attach_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		
		// 업로드된 파일이 있는지 확인한다. (없으면 중단)
		$upload_checker = false;
		foreach($_FILES as $key=>$value){
			if(strpos($key, KBContent::$SKIN_ATTACH_PREFIX) === false) continue;
			if($_FILES[$key]['tmp_name']){
				$upload_checker = true;
				break;
			}
		}
		
		if($upload_checker){
			$file = new KBFileHandler();
			$file->setPath($this->attach_store_path);
			
			foreach($_FILES as $key=>$value){
				if(strpos($key, KBContent::$SKIN_ATTACH_PREFIX) === false) continue;
				$key = str_replace(KBContent::$SKIN_ATTACH_PREFIX, '', $key);
				$key = sanitize_key($key);
				
				$upload = $file->upload(KBContent::$SKIN_ATTACH_PREFIX . $key);
				$file_path = $upload['path'] . $upload['stored_name'];
				$file_name = $upload['original_name'];
				$metadata = $upload['metadata'];
				
				if($file_name){
					$filetype = wp_check_filetype($file->abspath . $file_path, array('jpg|jpeg|jpe'=>'image/jpeg', 'png'=>'image/png'));
					
					if(in_array($filetype['type'], array('image/jpeg', 'image/png'))){
						$image_optimize_width = intval(get_option('kboard_image_optimize_width'));
						$image_optimize_height = intval(get_option('kboard_image_optimize_height'));
						$image_optimize_quality = intval(get_option('kboard_image_optimize_quality'));
						
						$image_editor = wp_get_image_editor($file->abspath . $file_path);
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
								$image_editor->save($file->abspath . $file_path);
							}
						}
					}
					
					$attach_file = new stdClass();
					$attach_file->key = $key;
					$attach_file->path = $file_path;
					$attach_file->name = $file_name;
					$attach_file->metadata = $metadata;
					$this->upload_attach_files[] = $attach_file;
				}
			}
		}
	}
	
	/**
	 * 게시글의 첨부파일을 업데이트한다. (입력/수정)
	 */
	public function updateAttach(){
		global $wpdb;
		if(!$this->attach_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		
		if($this->uid && $this->upload_attach_files && is_array($this->upload_attach_files)){
			foreach($this->upload_attach_files as $file){
				$file_key = esc_sql($file->key);
				$file_path = esc_sql($file->path);
				$file_name = esc_sql($file->name);
				$file_size = intval(filesize($this->abspath . $file_path));
				
				$metadata = apply_filters('kboard_content_file_metadata', $file->metadata, $file, $this);
				$metadata = serialize($metadata);
				$metadata = esc_sql($metadata);
				
				$present_file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$file_key'");
				if($present_file){
					@unlink($this->abspath . $present_file);
					$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_attached` SET `file_path`='$file_path', `file_name`='$file_name', `file_size`='$file_size', `metadata`='$metadata' WHERE `content_uid`='$this->uid' AND `file_key`='$file_key'");
				}
				else{
					$date = date('YmdHis', current_time('timestamp'));
					$wpdb->query("INSERT INTO `{$wpdb->prefix}kboard_board_attached` (`content_uid`, `comment_uid`, `file_key`, `date`, `file_path`, `file_name`, `file_size`, `download_count`, `metadata`) VALUES ('$this->uid', '0', '$file_key', '$date', '$file_path', '$file_name', '$file_size', '0', '$metadata')");
				}
			}
		}
		else if($this->upload_attach_files && is_array($this->upload_attach_files)){
			foreach($this->upload_attach_files as $file){
				kbaord_delete_resize($this->abspath . $file->path);
				@unlink($this->abspath . $file->path);
			}
		}
	}
	
	/**
	 * 게시글의 모든 첨부파일을 삭제한다.
	 */
	private function _deleteAllAttached(){
		global $wpdb;
		if($this->uid){
			$result = $wpdb->get_results("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid'");
			foreach($result as $file){
				kbaord_delete_resize($this->abspath . $file->file_path);
				@unlink($this->abspath . $file->file_path);
			}
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid'");
		}
	}
	
	/**
	 * 첨부파일을 삭제한다.
	 * @param string $key
	 */
	public function removeAttached($key){
		global $wpdb;
		if($this->uid){
			$key = sanitize_key($key);
			$key = esc_sql($key);
			$file = $wpdb->get_var("SELECT `file_path` FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
			if($file){
				kbaord_delete_resize($this->abspath . $file);
				@unlink($this->abspath . $file);
				$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_attached` WHERE `content_uid`='$this->uid' AND `file_key`='$key'");
			}
		}
	}
	
	/**
	 * 게시글의 옵션값을 반환한다.
	 * @param string $option_name
	 * @return string|array
	 */
	public function getOptionValue($option_name){
		return $this->option->{$option_name};
	}
	
	/**
	 * 게시글의 옵션을 저장한다.
	 * @param array $options
	 */
	public function updateOptions($options=array()){
		global $wpdb;
		if($this->uid){
			if(!$options) $options = $_POST;
			$this->option = new KBContentOption($this->uid);
			foreach($options as $key=>$value){
				if(strpos($key, KBContent::$SKIN_OPTION_PREFIX) !== false){
					$key = str_replace(KBContent::$SKIN_OPTION_PREFIX, '', $key);
					$key = sanitize_key($key);
					
					if($key == 'ip'){
						/*
						 * IP 주소는 게시글 작성 시에만 입력되고 수정되지 않는다.
						 */
						if($this->execute_action == 'insert'){
							$value = kboard_user_ip();
							$this->option->{$key} = $value;
						}
					}
					else{
						$value = kboard_xssfilter($value);
						$value = kboard_safeiframe($value);
						$this->option->{$key} = $value;
					}
				}
			}
		}
	}
	
	/**
	 * 옵션을 삭제한다.
	 */
	private function _deleteAllOptions(){
		global $wpdb;
		if($this->uid){
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_option` WHERE `content_uid`='{$this->uid}'");
		}
	}
	
	/**
	 * 썸네일을 등록한다.
	 */
	public function setThumbnail(){
		global $wpdb;
		if(!$this->thumbnail_store_path) die(__('No upload path. Please enter board ID and initialize.', 'kboard'));
		if($this->uid && isset($_FILES['thumbnail']) && $_FILES['thumbnail']['tmp_name']){
			$file = new KBFileHandler();
			$file->setPath($this->thumbnail_store_path);
			$upload = $file->upload('thumbnail');
			$thumbnail_name = esc_sql($upload['original_name']);
			$thumbnail_file = esc_sql($upload['path'] . $upload['stored_name']);
			if($thumbnail_name){
				$thumbnail_size = apply_filters('kboard_thumbnail_size', array(1200, 1200));
				if($thumbnail_size){
					// 업로드된 원본 이미지 크기를 줄인다.
					$file_path = strtolower($this->abspath . $upload['path'] . $upload['stored_name']);
					$image_editor = wp_get_image_editor($file_path);
					if(!is_wp_error($image_editor)){
						$image_editor->resize($thumbnail_size[0], $thumbnail_size[1]);
						$image_editor->save($file_path);
					}
				}
				$this->removeThumbnail(false);
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='{$thumbnail_file}', `thumbnail_name`='{$thumbnail_name}' WHERE `uid`='{$this->uid}'");
			}
		}
	}
	
	/**
	 * 썸네일 주소를 반환한다.
	 * @param int $width
	 * @param int $height
	 * @return string
	 */
	public function getThumbnail($width='', $height=''){
		if($this->uid && $this->secret && !$this->isReader() && !$this->isConfirm()){
			return '';
		}

		$size = array('width'=>$width, 'height'=>$height);
		$size = apply_filters('kboard_content_get_thumbnail_size', $size, $this);
		$width = isset($size['width']) ? intval($size['width']) : '';
		$height = isset($size['height']) ? intval($size['height']) : '';
		
		$thumbnail_url = '';
		if(isset($this->thumbnail["{$width}x{$height}"]) && $this->thumbnail["{$width}x{$height}"]){
			$thumbnail_url = $this->thumbnail["{$width}x{$height}"];
		}
		else if($this->thumbnail_file && $this->getSafeThumbnailFilePath($this->thumbnail_file)){
			if($width && $height){
				$this->thumbnail["{$width}x{$height}"] = kboard_resize($this->thumbnail_file, $width, $height);
			}
			else{
				$this->thumbnail["{$width}x{$height}"] = site_url($this->thumbnail_file);
			}
			$thumbnail_url = $this->thumbnail["{$width}x{$height}"];
		}
		else if($this->uid){
			$media = new KBContentMedia();
			$media->content_uid = $this->uid;
			foreach($media->getList() as $media_item){
				if($thumbnail_url) break;
				if(isset($media_item->file_path) && $media_item->file_path){
					if($width && $height){
						$this->thumbnail["{$width}x{$height}"] = kboard_resize($media_item->file_path, $width, $height);
					}
					else{
						$this->thumbnail["{$width}x{$height}"] = site_url($media_item->file_path);
					}
					$thumbnail_url = $this->thumbnail["{$width}x{$height}"];
				}
			}
			if(!$thumbnail_url){
				foreach($this->attach as $attach){
					if($thumbnail_url) break;
					$extension = strtolower(pathinfo($attach[0], PATHINFO_EXTENSION));
					if(in_array($extension, array('gif','jpg','jpeg','png'))){
						if($width && $height){
							$this->thumbnail["{$width}x{$height}"] = kboard_resize($attach[0], $width, $height);
						}
						else{
							$this->thumbnail["{$width}x{$height}"] = site_url($attach[0]);
						}
						$thumbnail_url = $this->thumbnail["{$width}x{$height}"];
					}
				}
			}
		}
		return apply_filters('kboard_content_get_thumbnail', $thumbnail_url, $width, $height, $this);
	}
	
	/**
	 * 게시글을 삭제한다.
	 * @param boolean $delete_action
	 */
	public function delete($delete_action=true){
		$this->remove($delete_action);
	}
	
	/**
	 * 게시글을 삭제한다.
	 * @param boolean $delete_action
	 */
	public function remove($delete_action=true){
		global $wpdb;
		if($this->uid){
			$board = $this->getBoard();
			
			if($delete_action){
				// 게시글 삭제 전에 액션 실행
				do_action('kboard_pre_document_delete', $this->uid, $this->board_id, $this, $board);
				
				// 글삭제 증가 포인트
				if($board->meta->document_delete_up_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
					if($this->member_uid){
						if(function_exists('mycred_add')){
							$point = intval(get_user_meta($this->member_uid, 'kboard_document_mycred_point', true));
							update_user_meta($this->member_uid, 'kboard_document_mycred_point', $point + $board->meta->document_delete_up_point);
							
							mycred_add('document_delete_up_point', $this->member_uid, $board->meta->document_delete_up_point, __('Deleted increment points', 'kboard'));
						}
					}
				}
				
				// 글쓰기 감소 포인트
				if($board->meta->document_delete_down_point && (!$board->meta->point_applied_to || !$board->isAdmin())){
					if($this->member_uid){
						if(function_exists('mycred_add')){
							$point = intval(get_user_meta($this->member_uid, 'kboard_document_mycred_point', true));
							update_user_meta($this->member_uid, 'kboard_document_mycred_point', $point + ($board->meta->document_delete_down_point*-1));
							
							mycred_add('document_delete_down_point', $this->member_uid, ($board->meta->document_delete_down_point*-1), __('Deleted decrease points', 'kboard'));
						}
					}
				}
			}
			
			$board->meta->total = $board->getTotal() - 1;
			if($this->status != 'trash'){
				$board->meta->list_total = $board->getListTotal() - 1;
			}
			
			$this->_deleteAllOptions();
			$this->_deleteAllAttached();
			$this->removeThumbnail(false);
			$this->deletePost($this->getPostID());
			$this->deleteReply($this->uid);
			
			if(defined('KBOARD_COMMNETS_VERSION')){
				$comment_list = new KBCommentList($this->uid);
				$comment_list->rpp(1000);
				$comment_list->initFirstList();
				
				while($comment_list->hasNextList()){
					while($comment = $comment_list->hasNext()){
						$comment->delete(false);
					}
					$comment_list->initFirstList();
				}
			}
			
			// 미디어 파일을 삭제한다.
			$media = new KBContentMedia();
			$media->deleteWithContentUID($this->uid);
			
			// 게시글 정보 삭제
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_content` WHERE `uid`='{$this->uid}'");
			
			// 추천 정보 삭제
			$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_vote` WHERE `target_uid`='{$this->uid}' AND `target_type`='document'");
			
			$wpdb->flush();
			
			if($delete_action){
				// 게시글 삭제 액션 실행
				do_action('kboard_document_delete', $this->uid, $this->board_id, $this, $board);
			}
		}
	}
	
	/**
	 * 썸네일 파일을 삭제한다.
	 * @param boolean $update
	 */
	public function removeThumbnail($update=true){
		global $wpdb;
		if($this->uid && $this->thumbnail_file){
			$file_path = $this->getSafeThumbnailFilePath($this->thumbnail_file);
			if($file_path){
				kbaord_delete_resize($file_path);
				@unlink($file_path);
			}
			
			if($update){
				$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `thumbnail_file`='', `thumbnail_name`='' WHERE `uid`='{$this->uid}'");
			}
		}
	}
	
	/**
	 * 답글을 삭제한다.
	 * @param int $parent_uid
	 */
	public function deleteReply($parent_uid){
		global $wpdb;
		$parent_uid = intval($parent_uid);
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		$wpdb->flush();
		foreach($results as $row){
			$content = new KBContent();
			$content->initWithRow($row);
			$content->remove(false);
		}
	}
	
	/**
	 * 휴지통으로 이동할 때 실행한다.
	 * @param string $content_uid
	 */
	public function moveReplyToTrash($parent_uid){
		global $wpdb;
		
		$board = $this->getBoard();
		$board->meta->list_total = $board->getListTotal() - 1;
		
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		$wpdb->flush();
		foreach($results as $row){
			if($row->status != 'trash'){
				$this->moveReplyToTrash($row->uid);
			}
		}
	}
	
	/**
	 * 휴지통에서 복구할 때 실행한다.
	 * @param string $content_uid
	 */
	public function restoreReplyFromTrash($parent_uid){
		global $wpdb;
		
		$board = $this->getBoard();
		$board->meta->list_total = $board->getListTotal() + 1;
		
		$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE `parent_uid`='$parent_uid'");
		$wpdb->flush();
		foreach($results as $row){
			if($row->status != 'trash'){
				$this->restoreReplyFromTrash($row->uid);
			}
		}
	}
	
	/**
	 * 게시글 목록에서 게시글과 관련된 CSS 클래스를 반환한다.
	 * @return string
	 */
	public function getClass(){
		$class = array();
		
		if($this->uid == kboard_uid()){
			$class[] = 'kboard-list-selected';
		}
		
		if($this->class_type == 'notice'){
			$class[] = 'kboard-list-notice';
		}
		else if($this->class_type == 'popular'){
			$class[] = 'kboard-list-popular';
		}
		
		$class = apply_filters('kboard_content_get_class', $class, $this);
		return implode(' ', $class);
	}
	
	/**
	 * 게시글의 댓글 개수를 반환한다.
	 * @param string $prefix
	 * @param string $endfix
	 * @param string $default
	 * @return string
	 */
	public function getCommentsCount($prefix='(', $endfix=')', $default=null){
		if($this->uid){
			$board = $this->getBoard();
			if($board->meta->comments_plugin_id && $board->meta->use_comments_plugin){
				$url = new KBUrl();
				return '<span class="cosmosfarm-comments-plugin-count" data-url="'.$url->getCommentsPluginURLWithUID($this->uid).'" data-prefix="'.$prefix.'" data-endfix="'.$endfix.'" data-default="'.$default.'"></span>';
			}
			else if($this->comment || $default !== null){
				$count = $this->comment?$this->comment:$default;
				return "{$prefix}{$count}{$endfix}";
			}
		}
		return '';
	}
	
	/**
	 * 게시글의 댓글 개수를 반환한다.
	 * @param string $prefix
	 * @param string $endfix
	 * @return string
	 */
	public function getCommentsCountOld($prefix='(', $endfix=')'){
		if($this->uid && defined('KBOARD_COMMNETS_VERSION')){
			$commentList = new KBCommentList($this->uid);
			$commentsCount = $commentList->getCount();
			if($commentsCount) return "{$prefix}{$commentsCount}{$endfix}";
		}
		return '';
	}
	
	/**
	 * 게시글의 답글 개수를 반환한다.
	 * @param string $format
	 * @return string
	 */
	public function getReplyCount($format='(%s)'){
		global $wpdb;
		if($this->uid){
			$where = array();
			$where[] = "`parent_uid`='{$this->uid}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->board_id, "{$wpdb->prefix}kboard_board_content");
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			$where = implode(' AND ', $where);
			
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where}");
			if($count){
				return sprintf($format, $count);
			}
		}
		return '';
	}
	
	/**
	 * posts 테이블에 등록된 게시글의 ID 값을 가져온다.
	 */
	public function getPostID(){
		global $wpdb;
		if($this->uid){
			$post_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_name`='$this->uid' AND `post_type`='kboard'");
			if(!$post_id){
				$post_id = $wpdb->get_var("SELECT `ID` FROM `{$wpdb->prefix}posts` WHERE `post_name`='{$this->uid}__trashed' AND `post_type`='kboard'");
			}
			return intval($post_id);
		}
		return 0;
	}
	
	/**
	 * 다음 게시물의 UID를 반환한다.
	 */
	public function getNextUID(){
		global $wpdb;
		if($this->uid){
			$category1 = kboard_category1();
			$category2 = kboard_category2();
			
			$category1 = apply_filters('kboard_content_next_uid_category1', $category1, $this);
			$category2 = apply_filters('kboard_content_next_uid_category2', $category2, $this);
			
			$where[] = "`board_id`='{$this->board_id}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->board_id);
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			if($category1){
				$category1 = esc_sql($category1);
				$where[] = "`category1`='{$category1}'";
			}
			if($category2){
				$category2 = esc_sql($category2);
				$where[] = "`category2`='{$category2}'";
			}
			
			$list = new KBContentList($this->board_id);
			$sorting = $list->getSorting();
			
			if($sorting == 'newest'){
				// 최신순서
				$order_by_sort = 'date';
				$where[] = "`date`>'{$this->date}'";
			}
			else if($sorting == 'best'){
				// 추천순서
				$order_by_sort = 'uid';
				$where[] = "`uid`>'{$this->uid}'";
			}
			else if($sorting == 'viewed'){
				// 조회순서
				$order_by_sort = 'uid';
				$where[] = "`uid`>'{$this->uid}'";
			}
			else if($sorting == 'updated'){
				// 업데이트순서
				$order_by_sort = 'update';
				$where[] = "`update`>'{$this->update}'";
			}
			
			$board = $this->getBoard();
			if($board->isPrivate()){
				if(is_user_logged_in()){
					$user_id = get_current_user_id();
					$where[] = "`member_uid`='{$user_id}'";
				}
			}
			
			$where = implode(' AND ', $where);
			$uid = $wpdb->get_var(apply_filters('kboard_content_next_uid_query', "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `{$order_by_sort}` ASC LIMIT 1", $this, $where, $sorting, $order_by_sort, $category1, $category2));
			$wpdb->flush();
			
			return intval($uid);
		}
		return 0;
	}
	
	/**
	 * 이전 게시물의 UID를 반환한다.
	 */
	public function getPrevUID(){
		global $wpdb;
		if($this->uid){
			$category1 = kboard_category1();
			$category2 = kboard_category2();
			
			$category1 = apply_filters('kboard_content_prev_uid_category1', $category1, $this);
			$category2 = apply_filters('kboard_content_prev_uid_category2', $category2, $this);
			
			$where[] = "`board_id`='{$this->board_id}'";
			
			// 휴지통에 없는 게시글만 불러온다.
			$get_list_status_query = kboard_get_list_status_query($this->board_id);
			if($get_list_status_query){
				$where[] = $get_list_status_query;
			}
			
			if($category1){
				$category1 = esc_sql($category1);
				$where[] = "`category1`='{$category1}'";
			}
			if($category2){
				$category2 = esc_sql($category2);
				$where[] = "`category2`='{$category2}'";
			}
			
			$list = new KBContentList($this->board_id);
			$sorting = $list->getSorting();
			
			if($sorting == 'newest'){
				// 최신순서
				$order_by_sort = 'date';
				$where[] = "`date`<'{$this->date}'";
			}
			else if($sorting == 'best'){
				// 추천순서
				$order_by_sort = 'uid';
				$where[] = "`uid`<'{$this->uid}'";
			}
			else if($sorting == 'viewed'){
				// 조회순서
				$order_by_sort = 'uid';
				$where[] = "`uid`<'{$this->uid}'";
			}
			else if($sorting == 'updated'){
				// 업데이트순서
				$order_by_sort = 'update';
				$where[] = "`update`<'{$this->update}'";
			}
			
			$board = $this->getBoard();
			if($board->isPrivate()){
				if(is_user_logged_in()){
					$user_id = get_current_user_id();
					$where[] = "`member_uid`='{$user_id}'";
				}
			}
			
			$where = implode(' AND ', $where);
			$uid = $wpdb->get_var(apply_filters('kboard_content_prev_uid_query', "SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `{$order_by_sort}` DESC LIMIT 1", $this, $where, $sorting, $order_by_sort, $category1, $category2));
			$wpdb->flush();
			
			return intval($uid);
		}
		return 0;
	}
	
	/**
	 * 최상위 부모 UID를 반환한다.
	 * @return int
	 */
	public function getTopContentUID(){
		if($this->parent_uid){
			$content = new KBContent();
			$content->initWithUID($this->parent_uid);
			return $content->getTopContentUID();
		}
		return $this->uid;
	}
	
	/**
	 * 최상위 부모 object를 반환한다.
	 * @return KBContent
	 */
	public function getTopContent(){
		if($this->parent_uid){
			$content = new KBContent();
			$content->initWithUID($this->parent_uid);
			return $content->getTopContent();
		}
		return $this;
	}
	
	/**
	 * 게시글과 미디어의 관계를 입력한다.
	 */
	public function addMediaRelationships(){
		if($this->uid){
			$media = new KBContentMedia();
			$media->board_id = $this->board_id;
			$media->content_uid = $this->uid;
			$media->media_group = isset($_POST['media_group']) ? sanitize_key($_POST['media_group']) : '';
			$media->createRelationships();
		}
	}
	
	/**
	 * 게시글에 등록된 미디어 목록을 반환한다.
	 * @return array
	 */
	public function getMediaList(){
		$media_list = array();
		if($this->uid){
			$media = new KBContentMedia();
			$media->board_id = $this->board_id;
			$media->content_uid = $this->uid;
			$media_list = $media->getList();
		}
		return $media_list;
	}
	
	/**
	 * 게시글에서 댓글을 보여줄지 확인한다.
	 */
	public function visibleComments(){
		$visible = false;
		
		$board = $this->getBoard();
		$visible = $board->isComment();
		
		if($this->notice && $board->meta->notice_invisible_comments){
			$visible = false;
		}
		
		return apply_filters('kboard_visible_comments', $visible, $this);
	}
	
	/**
	 * 새글인지 확인한다.
	 * @return boolean
	 */
	public function isNew(){
		$is_new = false;
		if($this->uid){
			$notify_time = kboard_new_document_notify_time();
			$date_timestamp = kboard_date_to_timestamp($this->date);
			if($date_timestamp !== false && (current_time('timestamp')-$date_timestamp) <= $notify_time && $notify_time != '1'){
				$is_new = true;
			}
		}
		return apply_filters('kboard_content_is_new', $is_new, $this);
	}
	
	/**
	 * 게시판 정보를 반환한다.
	 * @return KBoard
	 */
	public function getBoard(){
		if(isset($this->board->id) && $this->board->id){
			return $this->board;
		}
		else if($this->board_id){
			$this->board = new KBoard($this->board_id);
			return $this->board;
		}
		return new KBoard();
	}
	
	/**
	 * 첨부파일이 있는지 확인한다.
	 * @return boolean
	 */
	public function isAttached(){
		$is_attached = false;
		if($this->uid && !$this->status){
			if(count((array)$this->getAttachmentList()) > 0){
				$is_attached = true;
			}
		}
		return apply_filters('kboard_content_is_attached', $is_attached, $this, $this->getBoard());
	}
	
	/**
	 * 날짜를 반환한다.
	 * @return string
	 */
	public function getDate(){
		$date = '';
		if(isset($this->row->date)){
			$date_timestamp = kboard_date_to_timestamp($this->row->date);
			if($date_timestamp !== false){
				if(date('Ymd', current_time('timestamp')) == date('Ymd', $date_timestamp)){
					$date = date('H:i', $date_timestamp);
				}
				else{
					$date = date('Y.m.d', $date_timestamp);
				}
			}
		}
		return apply_filters('kboard_content_date', $date, $this, $this->getBoard());
	}
	
	/**
	 * 제목을 반환한다.
	 * @return string
	 */
	public function getTitle(){
		if(isset($this->row->title)){
			return $this->row->title;
		}
		return '';
	}
	
	/**
	 * 내용을 반환한다.
	 * @return string
	 */
	public function getContent(){
		if(isset($this->row->content)){
			return $this->row->content;
		}
		return '';
	}
	
	/**
	 * 게시글 정보를 세션에 저장한다.
	 */
	public function saveTemporary(){
		$this->parent_uid = isset($_POST['parent_uid'])?intval($_POST['parent_uid']):0;
		$this->member_uid = isset($_POST['member_uid'])?intval($_POST['member_uid']):0;
		$this->member_display = isset($_POST['member_display'])?sanitize_text_field($_POST['member_display']):'';
		$this->title = isset($_POST['title'])?kboard_safeiframe(kboard_xssfilter($_POST['title'])):'';
		$this->content = isset($_POST['kboard_content'])?kboard_safeiframe(kboard_xssfilter($_POST['kboard_content'])):'';
		$this->date = isset($_POST['date'])?sanitize_key($_POST['date']):'';
		if(isset($_POST['view'])) $this->view = intval($_POST['view']);
		if(isset($_POST['comment'])) $this->comment = intval($_POST['comment']);
		if(isset($_POST['like'])) $this->like = intval($_POST['like']);
		if(isset($_POST['unlike'])) $this->unlike = intval($_POST['unlike']);
		if(isset($_POST['vote'])) $this->vote = intval($_POST['vote']);
		$this->category1 = isset($_POST['category1'])?sanitize_text_field($_POST['category1']):'';
		$this->category2 = isset($_POST['category2'])?sanitize_text_field($_POST['category2']):'';
		$this->category3 = isset($_POST['category3'])?sanitize_text_field($_POST['category3']):'';
		$this->category4 = isset($_POST['category4'])?sanitize_text_field($_POST['category4']):'';
		$this->category5 = isset($_POST['category5'])?sanitize_text_field($_POST['category5']):'';
		$this->secret = isset($_POST['secret'])?sanitize_key($_POST['secret']):'';
		$this->notice = isset($_POST['notice'])?sanitize_key($_POST['notice']):'';
		$this->search = isset($_POST['wordpress_search'])?intval(($this->secret && $_POST['wordpress_search']==1)?'2':$_POST['wordpress_search']):'1';
		if(isset($_POST['status'])) $this->status = sanitize_key($_POST['status']);
		
		if(is_user_logged_in() && !$this->member_display){
			$current_user = wp_get_current_user();
			$board = $this->getBoard();
			
			if ($board->meta->board_username_display_save === 'name') {
				$first_name = trim($current_user->user_firstname);
				$last_name  = trim($current_user->user_lastname);
				$this->member_display = $last_name . ' ' . $first_name;
			}
			else if ($board->meta->board_username_display_save === 'email') {
				$this->member_display = $current_user->user_email;
			}
			else {
				$this->member_display = $current_user->display_name;
			}
		}
		
		$option = array();
		foreach($_POST as $key=>$value){
			if(strpos($key, KBContent::$SKIN_OPTION_PREFIX) !== false){
				$key = sanitize_key(str_replace(KBContent::$SKIN_OPTION_PREFIX, '', $key));
				if($key){
					$value = kboard_safeiframe(kboard_xssfilter($value));
					$option[$key] = $value;
				}
			}
		}
		
		$temporary = $this->row;
		$temporary->option = (object) $option;
		$_SESSION['kboard_temporary_content'] = $temporary;
		
		$this->setExecuteAction();
	}
	
	/**
	 * 세션에 저장된 게시글 정보로 초기화 한다.
	 */
	public function initWithTemporary(){
		if(isset($_SESSION['kboard_temporary_content']) && $_SESSION['kboard_temporary_content']){
			
			// 민감한 정보 제거
			if(isset($_SESSION['kboard_temporary_content']->uid)){
				$_SESSION['kboard_temporary_content']->uid = '';
			}
			if(isset($_SESSION['kboard_temporary_content']->password)){
				$_SESSION['kboard_temporary_content']->password = '';
			}
			
			$temporary = $_SESSION['kboard_temporary_content'];
			$this->row = $temporary;
		}
		else{
			$this->row = new stdClass();
		}
		if(!isset($temporary->option) || !(array)$temporary->option){
			$this->option = new KBContentOption();
		}
		else{
			$this->option = $temporary->option;
		}
	}
	
	/**
	 * 세션에 저장된 게시글 정보를 비운다.
	 */
	public function cleanTemporary(){
		unset($_SESSION['kboard_temporary_content']);
	}
	
	/**
	 * 글 읽기 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isReader(){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isReader($this->member_uid, $this->secret)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 글 수정 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isEditor(){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isEditor($this->member_uid)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 게시글 비밀번호와 일치하는지 확인한다.
	 * @param boolean $reauth
	 * @return boolean
	 */
	public function isConfirm($reauth=false){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isConfirm($this->getPassword(), $this->uid, $reauth)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 첨부파일 다운로드 권한이 있는 사용자인지 확인한다.
	 * @return boolean
	 */
	public function isAttachmentDownload(){
		if($this->uid){
			$board = $this->getBoard();
			if($board->isAttachmentDownload()){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 휴지통에 있는지 확인한다.
	 * @return boolean
	 */
	public function isTrash(){
		if($this->status == 'trash'){
			return true;
		}
		return false;
	}
	
	/**
	 * 작성자 ID를 반환한다.
	 * @return int
	 */
	public function getUserID(){
		if($this->uid && $this->member_uid){
			return intval($this->member_uid);
		}
		return 0;
	}
	
	/**
	 * 작성자 이름을 반환한다.
	 * @return string
	 */
	public function getUserName(){
		if($this->uid && $this->member_display){
			// 작성자명 마스킹
			$board = $this->getBoard();
			if($board->meta->display_name_masking){
				return kboard_text_masking($this->member_display);
			}
			
			return $this->member_display;
		}
		return '';
	}
	
	/**
	 * 작성자 이름을 반환한다.
	 * @param string $user_display
	 * @return string
	 */
	public function getUserDisplay($user_display=''){
		global $kboard_builder;
		
		if($this->uid){
			$user_id = $this->getUserID();
			$user_name = esc_html($this->getUserName());
			$type = 'kboard';
			$builder = $kboard_builder;
			
			if(!$user_display){
				$user_display = $user_name;
			}
			
			$board = $this->getBoard();
			$masking_option = $board->meta->board_username_masking;
			
			// 작성자 이름 마스킹 처리
			if($masking_option == '1'){
				// 모두 마스킹
				$user_display = esc_html($this->getObfuscateName());
			}
			else if($masking_option == '2'){
				// 관리자 제외 마스킹
				if(!current_user_can('administrator')){
					$user_display = esc_html($this->getObfuscateName());
				}
			}
			
			if($board->meta->show_author_activity_menu){
				$user_display = $this->getUserDisplayDropdown($user_display, $user_id);
			}
			
			$user_display = apply_filters('kboard_user_display', $user_display, $user_id, $user_name, $type, $builder);
		}
		return $user_display;
	}
	
	/**
	 * 작성자 이름을 읽을 수 없도록 만든다.
	 * @param string $replace
	 * @return string
	 */
	public function getObfuscateName($replace='*'){
		if($this->uid && $this->member_display){
			$strlen = mb_strlen($this->member_display, 'utf-8');
			
			if($strlen > 3){
				$showlen = 2;
			}
			else{
				$showlen = 1;
			}
			
			$obfuscate_name = mb_substr($this->member_display, 0, $showlen, 'utf-8') . str_repeat($replace, $strlen-$showlen);
			return apply_filters('kboard_obfuscate_name', $obfuscate_name, $this->member_display, $this, $this->getBoard());
		}
		return apply_filters('kboard_obfuscate_name', '', '', $this, $this->getBoard());
	}
	
	/**
	 * 작성자 드롭다운 HTML 반환
	 *
	 * @param string $user_display 표시될 작성자 이름
	 * @param int $user_id 사용자 ID
	 * @return string HTML 출력 문자열
	 */
	public function getUserDisplayDropdown($user_display, $user_id){
		$url = new KBUrl();

		$author_list_url = esc_url($url->set('mod', 'list')->set('author_id', $user_id)->toString());
		// $author_comments_url = esc_url($url->getCommentsListWithAuthor($user_id));
		
		return '
		<div class="kboard-author-dropdown">
			<span class="kboard-author-name" onclick="kboardToggleAuthorMenu(this)">' . esc_html($user_display) . '</span>
			<div class="kboard-author-menu">
				<ul>
					<li><a href="' . $author_list_url . '">' . __('작성 게시글 보기', 'kboard') . '</a></li>
				</ul>
			</div>
		</div>';
	}

	/**
	 * 게시글에 저장된 카테고리의 값을 반환한다.
	 * @param string $format
	 * @return array
	 */
	public function getCategoryValues($format='%s'){
		$values = array();
		if($this->uid){
			if($this->category1){
				$values[] = sprintf($format, $this->category1);
			}
			if($this->category2){
				$values[] = sprintf($format, $this->category2);
			}
		}
		return $values;
	}
	
	/**
	 * 게시글에 저장된 트리 카테고리의 깊이를 반환한다.
	 * @return int
	 */
	public function getTreeCategoryDepth(){
		$this->tree_category_depth = 0;
		if($this->tree_category_depth){
			return $this->tree_category_depth;
		}
		if($this->uid){
			$tree_category_count = $this->getBoard()->tree_category->getCount();
			for($i=1; $i<=$tree_category_count; $i++){
				if(!$this->option->{'tree_category_'.$i}) break;
				$this->tree_category_depth++;
			}
		}
		return $this->tree_category_depth;
	}
	
	/**
	 * 게시글에 저장된 트리 카테고리의 값을 반환한다.
	 * @param string $format
	 * @return array
	 */
	public function getTreeCategoryValues($format='%s'){
		$values = array();
		if($this->uid){
			$depth = $this->getTreeCategoryDepth();
			for($i=1; $i<=$depth; $i++){
				$values['tree_category_'.$i] = sprintf($format, $this->option->{'tree_category_'.$i});
			}
		}
		return $values;
	}
	
	/**
	 * 게시글 본문 페이지에 표시할 옵션값 태그를 반환한다.
	 * @return string
	 */
	public function getDocumentOptionsHTML(){
		if($this->uid){
			$board = $this->getBoard();
			return $board->fields()->getDocumentValuesHTML($this);
		}
		return '';
	}
	
	/**
	 * 게시글 본문 페이지에 표시할 옵션값을 반환한다.
	 * @return array
	 */
	public function getDocumentOptions(){
		if($this->uid){
			$board = $this->getBoard();
			return $board->fields()->getDocumentValues($this);
		}
		return array();
	}
	
	/**
	 * 게시글에 표시할 첨부파일을 반환한다.
	 * @param string $key_filter
	 * @return object
	 */
	public function getAttachmentList($key_filter=''){
		$attachment_list = new stdClass();
		if($this->uid && ($this->isReader() || $this->isConfirm())){
			$board = $this->getBoard();
			$attachment_list = $board->fields()->getAttachmentList($this);
		}
		
		if($key_filter){
			$new_attachment_list = array();
			
			foreach($attachment_list as $key=>$attachment){
				if(strpos($key, $key_filter) !== false){
					$new_attachment_list[$key] = $attachment;
				}
			}
			
			$attachment_list = (object)$new_attachment_list;
		}
		
		return apply_filters('kboard_content_get_attachment_list', $attachment_list, $key_filter, $this, $this->getBoard());
	}
	
	/**
	 * 메일에 첨부할 첨부파일을 반환한다.
	 * @return array
	 */
	public function getMailAttachments(){
		$attachments = array();
		
		if(count((array)$this->attach) > 0){
			$board = $this->getBoard();
			$max_size = $board->meta->latest_alerts_attachments_size;
			
			if(!$max_size){
				return $attachments;
			}
			
			$kboard_mail_attached_dir = WP_CONTENT_DIR.'/uploads/kboard_mail_attached/';
			if(!is_dir($kboard_mail_attached_dir)){
				wp_mkdir_p($kboard_mail_attached_dir);
			}
			
			$sum_size = 0;
			foreach($this->attach as $key=>$attach){
				$sum_size += $attach['file_size'] / (1024 * 1024); // MB
				
				// 설정된 최대 용량만큼 전송하고 나머지 파일은 제외한다.
				if($sum_size > $max_size) break;
				
				$source = $this->abspath . $attach[0];
				$dest = $kboard_mail_attached_dir . $attach[1];
				copy($source, $dest);
				$attachments[] = $dest;
			}
		}
		
		return $attachments;
	}
	
	/**
	 * 메일에 첨부한 첨부파일을 삭제한다.
	 */
	public function deleteMailAttachments(){
		if(!function_exists('list_files')){
			include_once ABSPATH . '/wp-admin/includes/file.php';
		}
		
		$kboard_mail_attached_dir = WP_CONTENT_DIR.'/uploads/kboard_mail_attached/';
		if(is_dir($kboard_mail_attached_dir)){
			foreach(list_files($kboard_mail_attached_dir) as $attach){
				wp_delete_file($attach);
			}
			
			rmdir($kboard_mail_attached_dir);
		}
	}
	
	/**
	 * 게시글 본문에 이미지가 포함되어 있는지 확인한다.
	 * @return boolean
	 */
	public function hasImage(){
		$has_image = false;
		if($this->uid && strpos($this->content, '<img') !== false){
			$has_image = true;
		}
		return apply_filters('kboard_content_has_image', $has_image, $this, $this->getBoard());
	}
	
	/**
	 * 정보를 배열로 반환한다.
	 * @return array
	 */
	public function toArray(){
		if($this->uid){
			return get_object_vars($this->row);
		}
		return array();
	}
	
	/**
	 * 옵션 데이터를 포함해서 정보를 배열로 반환한다.
	 */
	public function toArrayWithOptions(){
		if($this->uid){
			$object = $this->row;
			foreach($this->option->row as $key=>$value){
				$object->{KBContent::$SKIN_OPTION_PREFIX . $key} = $value;
			}
			return get_object_vars($object);
		}
		return array();
	}
	
	/**
	 * 제목 문자열에서 HTML과 PHP 태그를 제거한다.
	 * @param string $title
	 * @return string
	 */
	public function titleStripTags($title){
		$title = strip_tags($title, apply_filters('kboard_content_title_allowable_tags', '<i><b><u><s><br><span><strong><img><ins><del>', $this, $this->getBoard()));
		return $title;
	}
	
	/**
	 * 이모지를 해당하는 HTML 엔터티로 변환한다.
	 * @param string $string
	 * @return string
	 */
	public function encodeEmoji($string){
		global $wpdb;
		if($string && $wpdb->charset != 'utf8mb4'){
			if(function_exists('wp_encode_emoji') && function_exists('mb_convert_encoding')){
				$string = wp_encode_emoji($string);
			}
		}
		return $string;
	}
	
	/**
	 * 게시판을 이동하면 게시판의 정보를 변경한다.
	 * @param int $new_board_id
	 */
	private function changeBoardID($new_board_id){
		if($this->uid){
			$current_board = new KBoard($this->previous_board_id);
			$new_board = new KBoard($new_board_id);
			
			if($new_board->id && $current_board->id != $new_board->id){
				$current_board->meta->total = $current_board->getTotal() - 1;
				if($this->status != 'trash'){
					$current_board->meta->list_total = $current_board->getListTotal() - 1;
				}
				
				$new_board->meta->total = $new_board->getTotal() + 1;
				if($this->status != 'trash'){
					$new_board->meta->list_total = $new_board->getListTotal() + 1;
				}
			}
		}
	}
	
	/**
	 * 본문에 인터넷 주소가 있을때 자동으로 링크를 생성한다.
	 */
	public static function autolink($contents){
		// http://yongji.tistory.com/28
		$pattern = "/(http|https|ftp|mms):\/\/[0-9a-z-]+(\.[_0-9a-z-]+)+(:[0-9]{2,4})?\/?"; //domain+port
		$pattern .= "([\.~_0-9a-z-]+\/?)*";// sub roots
		$pattern .= "(\S+\.[_0-9a-z]+)?";// file & extension string
		$pattern .= "(\?[_0-9a-z#%&=\-\+]+)*/i";// parameters
		$replacement = "<a href=\"\\0\" target=\"window.opne(this.href); return false;\">\\0</a>";
		return preg_replace($pattern, $replacement, $contents, -1);
	}
	
	/**
	 * 공지사항 만료 여부 체크
	 */
	public function checkNoticeExpiration(){
		if($this->notice && $this->notice_expired_date){
			$current_date = date('YmdHis', current_time('timestamp'));
			$expired_date = preg_replace('/[^0-9]/', '', $this->notice_expired_date); // 날짜 포맷 정리 (숫자만)

			// 만료 시간이 지났으면 공지 해제
			if($expired_date && $current_date > $expired_date){
				$this->notice = '';
				$this->notice_expired_date = '';

				// 바로 DB 업데이트
				$this->updateContent([
					'notice' => '',
					'notice_expired_date' => '',
				]);
			}
		}
	}
}
