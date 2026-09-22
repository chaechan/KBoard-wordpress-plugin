<?php

/**
 * KBoard 워드프레스 게시판 생성
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardBuilder
{

	var $mod;
	var $board;
	var $board_id;
	var $meta;
	var $uid;
	var $skin;
	var $skin_name;
	var $category1;
	var $category2;
	var $category3;
	var $category4;
	var $category5;
	var $rpp;
	var $sort;
	var $url;
	var $message;
	var $dayofweek;
	var $within_days;
	var $random;
	var $sort_random;
	var $view_iframe;
	var $is_ajax = false;

	public function __construct($board_id = '', $is_latest = false)
	{
		global $kboard_builder;
		$kboard_builder = $this;

		$this->category1 = kboard_category1();
		$this->category2 = kboard_category2();
		$this->category3 = kboard_category3();
		$this->category4 = kboard_category4();
		$this->category5 = kboard_category5();
		$this->uid = kboard_uid();
		$this->sort = 'newest';
		$this->setSkin('default');

		if ($board_id) {
			$this->setBoardID($board_id, $is_latest);
		}
	}

	/**
	 * 게시판 뷰(View)를 설정한다. (List/Document/Editor/Remove/Order/Complete/History/Sales)
	 * @param string $mod
	 */
	public function setMOD($mod)
	{
		$this->mod = strtolower($mod);
		$_GET['mod'] = $this->mod;
	}

	/**
	 * 게시판 스킨을 설정한다.
	 * @param string $skin
	 */
	public function setSkin($skin)
	{
		$this->skin = KBoardSkin::getInstance();
		$this->skin_name = apply_filters('kboard_builder_set_skin', $skin, $this);
	}

	/**
	 * 게시판 ID를 설정한다.
	 * @param int $board_id
	 */
	public function setBoardID($board_id, $is_latest = false)
	{
		static $check_kboard_comments_plugin_once;

		$this->board_id = $board_id;
		$this->board = new KBoard($this->board_id);
		$this->meta = $this->board->meta;

		// 코스모스팜 소셜댓글 스크립트 추가
		if (!$check_kboard_comments_plugin_once) {
			if ($this->meta->comments_plugin_id && $this->meta->use_comments_plugin) {
				wp_localize_script('kboard-script', 'cosmosfarm_comments_plugin_id', $this->meta->comments_plugin_id);
				wp_enqueue_script('cosmosfarm-comments-plugin', 'https://plugin.cosmosfarm.com/comments.js', array(), '1.0', true);
				wp_enqueue_script('kboard-comments-plugin', KBOARD_URL_PATH . '/template/js/comments_plugin.js', array(), KBOARD_VERSION, true);
				$check_kboard_comments_plugin_once = true;
			}
		}

		if (!$is_latest) {
			$default_build_mod = $this->meta->default_build_mod;
			if (!$default_build_mod) {
				$default_build_mod = 'list';
			}
			$this->mod = kboard_mod(apply_filters('kboard_default_build_mod', $default_build_mod, $this->board_id));

			// 외부 요청을 금지하기 위해서 사용될 게시판 id는 세션에 저장한다.
			$_SESSION['kboard_board_id'] = $this->board_id;

			if (!wp_script_is('kboard-script', 'registered')) {
				wp_enqueue_script('kboard-script', KBOARD_URL_PATH . '/template/js/script.js', array(), KBOARD_VERSION, true);
			}
			else if (!wp_script_is('kboard-script', 'enqueued')) {
				wp_enqueue_script('kboard-script');
			}

			$use_editor = kboard_normalize_editor_value($this->board->use_editor);
			wp_localize_script('kboard-script', 'kboard_current', array(
				'board_id'          => $this->board_id,
				'content_uid'       => $this->uid,
				'use_tree_category' => $this->meta->use_tree_category,
				'tree_category'     => unserialize($this->meta->tree_category),
				'mod'               => $this->mod,
				'add_media_url'     => apply_filters('kboard_add_media_url', add_query_arg(array(
					'action'      => 'kboard_media',
					'board_id'    => $this->board_id,
					'media_group' => kboard_media_group(),
					'content_uid' => ($this->mod == 'editor' ? $this->uid : '')
				), home_url('/', 'relative'))),
				'use_editor' => $use_editor,
				'editor_root_selector' => '#kboard-editor-root',
				'editor_textarea_id' => 'kboard_content',
			));

			// KBoard 미디어 추가
			add_action('media_buttons', 'kboard_editor_button');
			add_filter('mce_buttons', 'kboard_register_media_button');
			add_filter('mce_external_plugins', 'kboard_add_media_button');

			// font-awesome 출력
			if (!get_option('kboard_fontawesome')) {
				global $wp_styles;
				wp_enqueue_style('font-awesome', KBOARD_URL_PATH . '/assets/font-awesome/css/font-awesome.min.css', array(), '3.2.1');
			}
		}
	}

	/**
	 * 페이지당 게시글 개수를 설정한다.
	 * @param int $rpp
	 */
	public function setRpp($rpp)
	{
		$this->rpp = intval($rpp);
	}

	/**
	 * 게시글 정렬 순서를 설정한다.
	 * @param string $sort
	 */
	public function setSorting($sort)
	{
		$this->sort = sanitize_text_field($sort);
	}

	/**
	 * 게시판 실제 주소를 설정한다.
	 * @param string $url
	 */
	public function setURL($url)
	{
		$this->url = esc_url_raw($url);
	}

	/**
	 * 최신글 숏코드 최근 특정 요일을 설정한다.
	 * @param int $within_days
	 */
	public function setDayOfWeek($dayofweek)
	{
		$this->dayofweek = sanitize_text_field($dayofweek);
	}

	/**
	 * 최신글 숏코드 기간을 설정한다.
	 * @param int $within_days
	 */
	public function setWithinDays($within_days)
	{
		$this->within_days = intval($within_days);
	}

	/**
	 * 최신글 숏코드 목록의 결과를 랜점하게 정렬할지 설정한다.
	 * @param boolean $random
	 */
	public function setRandom($random)
	{
		$this->random = $random ? true : false;
	}

	/**
	 * 최신글 숏코드 목록 전체를 랜점하게 정렬할지 설정한다.
	 * @param boolean $random
	 */
	public function setSortRandom($sort_random)
	{
		$this->sort_random = $sort_random ? true : false;
	}

	/**
	 * 게시판 리스트를 반환한다.
	 * @return KBContentList
	 */
	public function getList()
	{
		$list = new KBContentList($this->board_id);
		$list->category1($this->category1);
		$list->category2($this->category2);
		$list->category3($this->category3);
		$list->category4($this->category4);
		$list->category5($this->category5);

		if ($this->board->isPrivate()) {
			if (is_user_logged_in()) {
				$list->memberUID(get_current_user_id());
			} else {
				$list->stop = true;
			}
		}

		$list->rpp($this->rpp);
		$list->page(kboard_pageid());
		$list->setCompare(kboard_compare());
		$list->setDateRange(kboard_start_date(), kboard_end_date());
		$list->setSearchOption(kboard_search_option());

		$list = apply_filters('kboard_builder_get_list', $list, $this);

		$list->getList(kboard_keyword(), kboard_target(), kboard_with_notice());
		return $list;
	}

	/**
	 * 게시판 리스트를 배열로 반환한다.
	 * @return array
	 */
	public function getListArray()
	{
		if(!$this->board || !$this->board->isReader(0, '')){
			return array();
		}

		// KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
		kboard_builder_mod('list');

		$list = $this->getList();
		$data = array();

		while ($content = $list->hasNext()) {
			$url = new KBUrl();
			$url->setBoard($this->board);
			$url->setPath(wp_get_referer());
			$can_read = $content->isReader() || $content->isConfirm();

			$_data = array();
			$_data['uid'] = $content->uid;
			$_data['member_uid'] = $can_read ? $content->member_uid : 0;
			$_data['member_display'] = $can_read ? $content->member_display : '';
			$_data['title'] = $content->title;
			$_data['content'] = $can_read ? $content->content : '';
			$_data['date'] = $content->date;
			$_data['view'] = $content->view;
			$_data['comment'] = $content->comment;
			$_data['like'] = $content->like;
			$_data['unlike'] = $content->unlike;
			$_data['vote'] = $content->vote;
			$_data['thumbnail_file'] = $can_read ? $content->thumbnail_file : '';
			$_data['thumbnail_name'] = $can_read ? $content->thumbnail_name : '';
			$_data['category1'] = $content->category1;
			$_data['category2'] = $content->category2;
			$_data['category3'] = $content->category3;
			$_data['category4'] = $content->category4;
			$_data['category5'] = $content->category5;
			$_data['secret'] = $content->secret;
			$_data['search'] = $content->search;
			$_data['attach'] = $can_read ? $this->getSafeListAttachments($content->attach) : (object) array();
			$_data['option'] = $can_read ? $content->option->toArray() : array();

			if ($this->view_iframe) {
				$url->set('kboard_id', $content->board_id);
				$url->set('view_iframe', '1');
			}

			$_data['urls']['document'] = $url->getDocumentURLWithUID($content->uid);
			$_data['urls']['editor'] = $url->getContentEditor($content->uid);
			$_data['urls']['remove'] = $url->getContentRemove($content->uid);

			$data[] = $_data;
		}
		return $data;
	}

	/**
	 * 리스트 응답용 첨부파일 정보를 만든다.
	 * 실제 저장 경로는 웹 직접 접근을 우회하는 데 사용될 수 있으므로 반환하지 않는다.
	 * @param object|array $attachments
	 * @return object
	 */
	private function getSafeListAttachments($attachments)
	{
		$safe_attachments = array();
		$allowed_fields = array('file_name', 'file_size', 'download_url', 'download_count', 'metadata');

		foreach ((array) $attachments as $key => $attachment) {
			$attachment = (array) $attachment;
			$safe_attachment = array();

			foreach ($allowed_fields as $field) {
				if (array_key_exists($field, $attachment)) {
					$safe_attachment[$field] = $attachment[$field];
				}
			}

			$safe_attachments[$key] = $safe_attachment;
		}

		return (object) $safe_attachments;
	}

	/**
	 * 게시판 리스트 페이지의 HTML 코드를 반환한다.
	 * @return string
	 */
	public function getListHTML()
	{
		if(!$this->board || !$this->board->isReader(0, '')){
			return '';
		}

		// KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
		kboard_builder_mod('list');

		ob_start();
		$this->builderList();
		return ob_get_clean();
	}

	/**
	 * 복사 방지 스크립트를 출력한다.
	 * @param KBoardBuilder $board_builder
	 */
	public function printPreventCopy($board_builder = '')
	{
		if ($this->board->isUsePreventCopy() && !current_user_can('manage_kboard')) {
			$prevent_copy = new KBoardPreventCopy();
			$script = '<script>';
			switch ($this->board->isUsePreventCopy()) {
				case 1:
					$script .= $prevent_copy->getCopyScript();
					break;
				case 2:
					$script .= $prevent_copy->getDragRightScript();
					$script .= $prevent_copy->getKeyboardScript();
					break;
				case 3:
					$script .= $prevent_copy->getCopyScript();
					$script .= $prevent_copy->getDragRightScript();
					$script .= $prevent_copy->getKeyboardScript();
					break;
			}
			$script .= '</script>';
			echo $script;
		}
	}

	/**
	 * 게시판 페이지를 생성하고 반환한다.
	 * @return string
	 */
	public function create()
	{
		if ($this->meta->permission_list && $this->meta->permission_access && !is_user_logged_in()) {
			echo '<script>alert("' . __('Please Log in to continue.', 'kboard') . '");</script>';
			echo '<script>top.window.location.href="' . wp_login_url($_SERVER['REQUEST_URI']) . '";</script>';
		} else {
			if (($this->meta->view_iframe || is_admin()) && !kboard_id()) {
				$view_iframe = true;
			} else {
				$view_iframe = false;
			}

			if ($this->meta->editor_view_iframe && !is_admin()) {
				if ($this->mod == 'editor' && !kboard_id()) {
					$view_iframe = true;
				} else if ($this->mod != 'editor' && kboard_id() && !$this->meta->view_iframe) {
					$url = new KBUrl();
					echo '<script>top.window.location.href="' . $url->set('kboard_id', '')->set('uid', kboard_uid())->set('mod', kboard_mod())->set('category1', $this->category1)->set('category2', $this->category2)->set('category3', $this->category3)->set('category4', $this->category4)->set('category5', $this->category5)->set('keyword', kboard_keyword())->set('target', kboard_target())->set('view_iframe', '')->set('iframe_id', '')->toString() . '";</script>';
					exit;
				}
			}

			$view_iframe = apply_filters('kboard_builder_view_iframe', $view_iframe, $this);

			if ($view_iframe) {
				$url = new KBUrl();
				$iframe_id = uniqid();
				$iframe_nonce = wp_create_nonce($iframe_id);
				return '<iframe id="kboard-iframe-' . $iframe_id . '" class="kboard-iframe kboard-iframe-' . $this->board_id . '" src="' . $url->set('kboard_id', $this->board_id)->set('uid', kboard_uid())->set('parent_uid', kboard_parent_uid())->set('mod', kboard_mod())->set('category1', $this->category1)->set('category2', $this->category2)->set('category3', $this->category3)->set('category4', $this->category4)->set('category5', $this->category5)->set('keyword', kboard_keyword())->set('target', kboard_target())->set('view_iframe', '1')->set('iframe_id', $iframe_id)->set('kboard_nonce', $iframe_nonce)->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
			}

			// KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 설정한다.
			kboard_builder_mod($this->mod);

			if ($this->mod == 'document') {
				$this->printPreventCopy();
			}

			if ($this->meta->pass_autop == 'enable') {
				do_action('kboard_skin_header', $this);
				call_user_func(array($this, 'builder' . ucfirst($this->mod)));
				do_action('kboard_skin_footer', $this);
				return '';
			} else {
				ob_start();
				do_action('kboard_skin_header', $this);
				call_user_func(array($this, 'builder' . ucfirst($this->mod)));
				do_action('kboard_skin_footer', $this);
				return ob_get_clean();
			}
		}
	}

	/**
	 * 게시판 리스트 페이지를 생성한다.
	 */
	public function builderList()
	{
		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;

		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$vars = array(
			'list' => $this->getList(),
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);

		echo $this->skin->load($this->skin_name, 'list.php', $vars);
	}

	/**
	 * 답글 리스트를 생성한다.
	 * @param int $parent_uid
	 */
	public function builderReply($parent_uid, $depth = 0)
	{
		$list = new KBContentList($this->board_id);
		$list->getReplyList($parent_uid);

		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;

		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$vars = array(
			'list' => $list,
			'depth' => $depth,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);

		echo $this->skin->load($this->skin_name, 'reply-template.php', $vars);
	}

	/**
	 * 게시판 본문 페이지를 생성한다.
	 */
	public function builderDocument()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$content = new KBContent();
		$content->initWithUID($this->uid);
		$list_url = esc_url_raw($url->set('mod', 'list')->toString());
		$list_url_js = wp_json_encode($list_url);

		if (!$content->uid) {
			echo '<script>alert("' . __('Invalid URL address.', 'kboard') . '");</script>';
			echo '<script>window.location.href=' . $list_url_js . ';</script>';
			exit;
		}

		if ($content->isTrash()) {
			echo '<script>alert("' . __('This post has been removed.', 'kboard') . '");</script>';
			echo '<script>window.location.href=' . $list_url_js . ';</script>';
			exit;
		}

		if ($this->isNotAllowed($content->board_id)) {
			$message = __('This post has been moved.', 'kboard');
			if ($message) {
				echo '<script>alert("' . $message . '");</script>';
			}
			echo '<script>window.location.href=' . $list_url_js . ';</script>';
			exit;
		}

		if ($this->board->isPrivate()) {
			if (is_user_logged_in()) {
				if (!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()) {
					echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
					echo '<script>window.location.href=' . $list_url_js . ';</script>';
					exit;
				}
			} else {
				echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
				echo '<script>window.location.href=' . $list_url_js . ';</script>';
				exit;
			}
		}

		$board = $this->board;
		$content->board = $board;
		$editor = kboard_normalize_editor_value($board->use_editor);
		$board->content = $content;

		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;

		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);

		$allow_document = false;
		if (!$content->isReader()) {
			if ($this->board->permission_read != 'all' && !is_user_logged_in()) {
				if ($this->meta->view_iframe) {
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
				} else {
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
				}
			} else if ($content->secret) {
				if (!$content->isConfirm()) {
					if ($content->parent_uid) {
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if ($this->board->isReader($parent->member_uid, $content->secret) || $parent->isConfirm()) {
							$allow_document = true;
						} else {
							echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
						}
					} else {
						echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
					}
				} else {
					$allow_document = true;
				}
			} else {
				do_action('kboard_cannot_read_document', 'go_back', $url->set('mod', 'list')->toString(), $content, $board, $this);
			}
		} else {
			$allow_document = true;
		}

		// 글읽기 감소 포인트
		if ($allow_document && $board->meta->document_read_down_point && (!$board->meta->point_applied_to || !$board->isAdmin())) {
			if (function_exists('mycred_add')) {
				if (!is_user_logged_in()) {
					if ($this->meta->view_iframe) {
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
					} else {
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
					}
					$allow_document = false;
				} else if ($content->member_uid != get_current_user_id()) {
					$log_args['user_id'] = get_current_user_id();
					$log_args['ref'] = 'document_read_down_point';
					$log_args['ref_id'] = $content->uid;
					$log = new myCRED_Query_Log($log_args);

					if (!$log->have_entries()) {
						$balance = mycred_get_users_balance(get_current_user_id());
						if ($board->meta->document_read_down_point > $balance) {
							do_action('kboard_cannot_read_document', 'not_enough_points', $url->set('mod', 'list')->toString(), $content, $board, $this);
							$allow_document = false;
						} else {
							$point = intval(get_user_meta(get_current_user_id(), 'kboard_document_mycred_point', true));
							update_user_meta(get_current_user_id(), 'kboard_document_mycred_point', $point + ($board->meta->document_read_down_point * -1));

							mycred_add('document_read_down_point', get_current_user_id(), ($board->meta->document_read_down_point * -1), __('Reading decrease points', 'kboard'), $content->uid);
						}
					}
				}
			}
		}

		if ($allow_document) {
			$content->increaseView();

			// 에디터를 사용하지 않고, autolink가 활성화면 자동으로 link를 생성한다.
			include_once KBOARD_DIR_PATH . '/helper/Autolink.helper.php';
			if (!$board->use_editor && $this->meta->autolink) {
				// 댓글 내용에 자동으로 link를 생성한다.
				add_filter('kboard_content', 'kboard_video_url_to_iframe', 10, 1);
				add_filter('kboard_comments_content', 'kboard_video_url_to_iframe', 10, 1);

				add_filter('kboard_comments_content', 'kboard_autolink', 10, 1);

				$content->content = apply_filters('kboard_content_paragraph_breaks', kboard_autolink($content->getContent()), $this);
			} else {
				// 유튜브, 비메오 동영상 URL을 iframe 코드로 변환한다.

				add_filter('kboard_content', 'kboard_video_url_to_iframe', 10, 1);
				add_filter('kboard_comments_content', 'kboard_video_url_to_iframe', 10, 1);

				$content->content = apply_filters('kboard_content_paragraph_breaks', $content->getContent(), $this);
			}

			// kboard_content 필터 실행
			$content->content = apply_filters('kboard_content', $content->getContent(), $content->uid, $this->board_id);

			// 게시글 숏코드(Shortcode) 실행
			if ($this->meta->shortcode_execute == 1) {
				if ($this->meta->shortcode_execute_only_admin) { // 게시판 관리자의 글에만 실행
					if ($content->member_uid && $board->isAdmin($content->member_uid)) {
						$content->content = do_shortcode($content->getContent());
					}
				} else {
					$content->content = do_shortcode($content->getContent());
				}
			} else {
				$content->content = str_replace('[', '&#91;', $content->getContent());
				$content->content = str_replace(']', '&#93;', $content->getContent());
			}

			echo $this->skin->load($this->skin_name, 'document.php', $vars);

			if (apply_filters('kboard_always_view_list', $board->meta->always_view_list, $this)) {
				do_action('kboard_skin_always_view_list', $this);
				$this->builderList();
			}
		}
	}

	/**
	 * 게시판 에디터 페이지를 생성한다.
	 */
	public function builderEditor()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		if ($this->board->isWriter() && $this->board->permission_write == 'all' && isset($_POST['title']) && $_POST['title']) {
			$next_url = $url->set('uid', $this->uid)->set('mod', 'editor')->toString();
			if (!is_user_logged_in() && (!isset($_POST['password']) || !$_POST['password'])) {
				echo '<script>alert("' . __('Please enter your password.', 'kboard') . '");</script>';
				echo '<script>window.location.href="' . $next_url . '";</script>';
				exit;
			}
		}

		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		if ($content->uid) {
			if ($content->isTrash()) {
				echo '<script>alert("' . __('This post has been removed.', 'kboard') . '");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}

			if ($this->isNotAllowed($content->board_id)) {
				$message = __('This post has been moved.', 'kboard');
				if ($message) {
					echo '<script>alert("' . $message . '");</script>';
				}
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}

		$board = $this->board;
		$content->board = $board;
		$editor = kboard_normalize_editor_value($board->use_editor);
		$board->content = $content;

		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;

		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);

		$confirm_view = false;
		if (!$content->uid && !$this->board->isWriter()) {
			if (is_user_logged_in()) {
				echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			} else {
				$login_url = wp_login_url($_SERVER['REQUEST_URI']);
				echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
				echo "<script>top.window.location.href='{$login_url}';</script>";
			}
			exit;
		} else if ($content->uid && !$content->isEditor()) {
			if ($this->board->permission_write == 'all' && !$content->member_uid) {
				if (!$content->isConfirm()) {
					$confirm_view = true;
				}
			} else {
				if (is_user_logged_in()) {
					echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				} else {
					$login_url = wp_login_url($_SERVER['REQUEST_URI']);
					echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
					echo "<script>top.window.location.href='{$login_url}';</script>";
				}
				exit;
			}
		}

		if ($confirm_view) {
			echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
		} else {
			// 글쓰기 감소 포인트 체크
			if ($content->execute_action == 'insert' && $board->meta->document_insert_down_point) {
				if (function_exists('mycred_add')) {
					if (!is_user_logged_in()) {
						$login_url = wp_login_url($_SERVER['REQUEST_URI']);
						echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
						echo "<script>top.window.location.href='{$login_url}';</script>";
						exit;
					} else {
						$balance = mycred_get_users_balance(get_current_user_id());
						if ($board->meta->document_insert_down_point > $balance) {
							echo '<script>alert("' . __('You have not enough points.', 'kboard') . '");</script>';
							echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
							exit;
						}
					}
				}
			}

			// 임시저장된 데이터로 초기화 한다.
			if ($content->execute_action == 'insert') {
				$content->initWithTemporary();
			}

			// 내용이 없으면 등록된 기본 양식을 가져온다.
			if (!$content->uid && !$content->content) {
				$content->content = $this->meta->default_content;
			}
			// 새로운 글 작성 시 기본적으로 비밀글로 설정한다.
			if (!$content->uid && $this->meta->secret_checked_default) {
				$content->secret = 'true';
			}

			// 새로운 답글 쓰기에서만 실행한다.
			if (kboard_parent_uid() && !$content->uid && !$content->parent_uid) {
				$parent = new KBContent();
				$parent->initWithUID(kboard_parent_uid());

				// 부모 고유번호가 있으면 답글로 등록하기 위해서 부모 고유번호를 등록한다.
				$content->parent_uid = $parent->uid;

				// 부모의 제목을 가져온다.
				$content->title = 'Re:' . $parent->title;

				// 답글 기본 내용을 설정한다.
				if ($this->meta->reply_copy_content == '1') {
					$content->content = $parent->getContent();
				} else if ($this->meta->reply_copy_content == '2') {
					$content->content = $this->meta->default_content;
				} else {
					$content->content = '';
				}

				// 부모의 카테고리를 이어받는다.
				if ($parent->category1) {
					$content->category1 = $parent->category1;
				}
				if ($parent->category2) {
					$content->category2 = $parent->category2;
				}
				if ($parent->category3) {
					$content->category3 = $parent->category3;
				}
				if ($parent->category4) {
					$content->category4 = $parent->category4;
				}
				if ($parent->category5) {
					$content->category5 = $parent->category5;
				}
			}

			// 숏코드(Shortcode)를 실행하지 못하게 변경한다.
			$content->content = str_replace('[', '&#91;', $content->getContent());
			$content->content = str_replace(']', '&#93;', $content->getContent());

			if ($editor == 'snote') { // summernote
				wp_print_styles('summernote'); // wp_head() 이후이므로 직접 출력
				wp_enqueue_script('summernote');

				if (get_locale() == 'ko_KR') {
					wp_enqueue_script('summernote-ko-KR');
				} else if (get_locale() == 'ja') {
					wp_enqueue_script('summernote-ja-JP');
				}
			}
			else if (in_array($editor, array('tiptap', 'editorjs'))) {
				wp_enqueue_script('kboard-editor-registry');
				wp_enqueue_script('kboard-editor-bootstrap');

				if ($editor == 'tiptap') {
					wp_enqueue_script('kboard-editor-tiptap-runtime');
					wp_enqueue_script('kboard-editor-tiptap-adapter');
				}
				else if ($editor == 'editorjs') {
					wp_enqueue_script('kboard-editor-editorjs-adapter');
				}
			}

			$vars['parent'] = isset($parent) ? $parent : new KBContent();

			echo $this->skin->load($this->skin_name, 'editor.php', $vars);
		}
	}

	/**
	 * 게시글 삭제 페이지를 생성한다. POST 요청에서만 삭제한다.
	 */
	public function builderRemove()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$remove_nonce = isset($_GET['kboard-content-remove-nonce']) && is_string($_GET['kboard-content-remove-nonce']) ? $_GET['kboard-content-remove-nonce'] : '';
		if (!wp_verify_nonce($remove_nonce, 'kboard-content-remove')) {
			echo '<script>alert("' . __('This page is restricted from external access.', 'kboard') . '");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		if (!$content->uid) {
			echo '<script>alert("' . __('Invalid URL address.', 'kboard') . '");</script>';
			echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
			exit;
		}

		if ($this->isNotAllowed($content->board_id)) {
			$message = __('This post has been moved.', 'kboard');
			if ($message) {
				echo '<script>alert("' . $message . '");</script>';
			}
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		$is_delete_request = isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
		$confirm_view = false;
		if (!$content->isEditor()) {
			if ($this->board->permission_write == 'all' && !$content->member_uid) {
				if (!$is_delete_request || !$content->isConfirm(true)) {
					$confirm_view = true;
				}
			} else {
				if (wp_get_referer()) {
					echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");history.go(-1);</script>';
				} else {
					echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'document')->set('uid',$content->uid)->toString()}';</script>";
				}
				exit;
			}
		}

		if ($confirm_view) {
			$board = $this->board;
			$content->board = $board;
			$board->content = $content;

			$order = new KBOrder();
			$order->board = $board;
			$order->board_id = $board->id;

			$vars = array(
				'content' => $content,
				'order' => $order,
				'url' => $url,
				'skin' => $this->skin,
				'skin_path' => $this->skin->url($this->skin_name),
				'skin_dir' => $this->skin->dir($this->skin_name),
				'board' => $board,
				'boardBuilder' => $this,
			);

			echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
		} else if (!$is_delete_request) {
			$remove_url = $url->getContentRemove($content->uid);
			$document_url = $url->getDocumentURLWithUID($content->uid);
			echo '<div class="kboard-remove-confirm">';
			echo '<p>' . esc_html__('Are you sure you want to delete?', 'kboard') . '</p>';
			echo '<form method="post" action="' . esc_url($remove_url) . '">';
			echo '<button type="submit">' . esc_html__('Delete', 'kboard') . '</button> ';
			echo '<a href="' . esc_url($document_url) . '">' . esc_html__('Cancel', 'kboard') . '</a>';
			echo '</form></div>';
		} else {
			$delete_immediately = get_option('kboard_content_delete_immediately');

			if ($delete_immediately) {
				$content->remove();
			} else {
				$content->status = 'trash';
				$content->updateContent();
			}

			// 삭제뒤 게시판 리스트로 이동한다.
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}
	}

	/**
	 * 주문 작성 페이지를 생성한다.
	 */
	public function builderOrder()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		if (!$content->uid) {
			echo '<script>alert("' . __('Invalid URL address.', 'kboard') . '");</script>';
			echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
			exit;
		}

		if ($content->isTrash()) {
			echo '<script>alert("' . __('This post has been removed.', 'kboard') . '");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		if ($this->isNotAllowed($content->board_id)) {
			$message = __('This post has been moved.', 'kboard');
			if ($message) {
				echo '<script>alert("' . $message . '");</script>';
			}
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		if ($this->board->isPrivate()) {
			if (is_user_logged_in()) {
				if (!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()) {
					echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
					exit;
				}
			} else {
				echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}

		$board = $this->board;
		$content->board = $board;
		$board->content = $content;

		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;
		$order->initOrder();
		$order->initOrderItems();

		$vars = array(
			'merchant_uid' => 'merchant_' . uniqid(),
			'active_method' => kboard_builtin_pg_active_method(),
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);

		$allow_document = false;
		if (!$this->board->isReader($content->member_uid, $content->secret)) {
			if (!is_user_logged_in() && $this->board->permission_read != 'all') {
				if ($this->meta->view_iframe) {
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
				} else {
					do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
				}
			} else if ($content->secret) {
				if (!$this->board->isConfirm($content->password, $content->uid)) {
					if ($content->parent_uid) {
						$parent = new KBContent();
						$parent->initWithUID($content->getTopContentUID());
						if ($this->board->isReader($parent->member_uid, $content->secret)) {
							$allow_document = true;
						} else {
							if (!$this->board->isConfirm($parent->password, $parent->uid)) {
								echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
							} else {
								$allow_document = true;
							}
						}
					} else {
						echo $this->skin->load($this->skin_name, 'confirm.php', $vars);
					}
				} else {
					$allow_document = true;
				}
			} else {
				do_action('kboard_cannot_read_document', 'go_back', $url->set('uid', $content->uid)->set('mod', 'document')->toString(), $content, $board, $this);
			}
		} else {
			$allow_document = true;
		}

		if ($allow_document) {
			if (!$this->board->isOrder()) {
				if (is_user_logged_in()) {
					do_action('kboard_cannot_read_document', 'go_back', $url->set('uid', $content->uid)->set('mod', 'document')->toString(), $content, $board, $this);
				} else {
					if ($this->meta->view_iframe) {
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($url->getDocumentRedirect($content->uid)), $content, $board, $this);
					} else {
						do_action('kboard_cannot_read_document', 'go_login', wp_login_url($_SERVER['REQUEST_URI']), $content, $board, $this);
					}
				}
			} else {
				echo $this->skin->load($this->skin_name, 'order.php', $vars);
			}
		}
	}

	/**
	 * 주문완료 페이지를 생성한다.
	 */
	public function builderComplete()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$content = new KBContent($this->board_id);
		$content->initWithUID($this->uid);

		if (!$content->uid) {
			echo '<script>alert("' . __('Invalid URL address.', 'kboard') . '");</script>';
			echo '<script>window.location.href="' . $url->set('mod', 'list')->toString() . '";</script>';
			exit;
		}

		if ($content->isTrash()) {
			echo '<script>alert("' . __('This post has been removed.', 'kboard') . '");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		if ($this->isNotAllowed($content->board_id)) {
			$message = __('This post has been moved.', 'kboard');
			if ($message) {
				echo '<script>alert("' . $message . '");</script>';
			}
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
			exit;
		}

		if ($this->board->isPrivate()) {
			if (is_user_logged_in()) {
				if (!$content->notice && $content->member_uid != get_current_user_id() && $content->getTopContent()->member_uid != get_current_user_id()) {
					echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
					echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
					exit;
				}
			} else {
				echo '<script>alert("' . __('This post can only be read by the owner.', 'kboard') . '");</script>';
				echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
				exit;
			}
		}

		$board = $this->board;
		$content->board = $board;
		$board->content = $content;

		$order = new KBOrder();
		$order->board = $this->board;
		$order->board_id = $this->board_id;

		$vars = array(
			'content' => $content,
			'order' => $order,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $board,
			'boardBuilder' => $this,
		);

		echo $this->skin->load($this->skin_name, 'complete.php', $vars);
	}

	/**
	 * 주문조회 페이지를 생성한다.
	 */
	public function builderHistory()
	{
		$list = new KBOrderHistory();
		$list->board = $this->board;
		$list->board_id = $this->board_id;
		$list->rpp = $this->rpp;
		$list->page = kboard_pageid();
		$list->setSearchOption(kboard_search_option());

		if (is_user_logged_in()) {
			$list->initOrder(get_current_user_id());
		} else {
			$nonmember_key = '';

			if (isset($_SESSION['nonmember_key'][$this->board_id]) && $_SESSION['nonmember_key'][$this->board_id]) {
				$nonmember_key = sanitize_text_field($_SESSION['nonmember_key'][$this->board_id]);
			}

			$buyer_name = isset($_POST['buyer_name']) ? sanitize_text_field($_POST['buyer_name']) : '';
			$buyer_email = isset($_POST['buyer_email']) ? sanitize_email($_POST['buyer_email']) : '';
			$buyer_password = isset($_POST['buyer_password']) ? sanitize_text_field($_POST['buyer_password']) : '';

			$nonmember_key_1 = $buyer_email;
			$nonmember_key_2 = $buyer_name . $buyer_password;

			list($nonmember_key_1, $nonmember_key_2) = apply_filters('kboard_order_nonmember_key', array($nonmember_key_1, $nonmember_key_2), $this->board);

			if ($nonmember_key_1 && $nonmember_key_2) {
				$nonmember_key = kboard_hash($nonmember_key_1, $nonmember_key_2);
			}

			if ($nonmember_key) {
				$_SESSION['nonmember_key'][$this->board_id] = $nonmember_key;
				$list->initOrderWithKey($nonmember_key);
			}
		}

		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$vars = array(
			'list' => $list,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);

		echo $this->skin->load($this->skin_name, 'history.php', $vars);
	}

	/**
	 * 판매조회 페이지를 생성한다.
	 */
	public function builderSales()
	{
		$url = new KBUrl();
		$url->setBoard($this->board);
		$url->setPath($this->url);

		if ($this->board->isWriter() && is_user_logged_in()) {
			$list = new KBOrderSales();
			$list->board = $this->board;
			$list->board_id = $this->board_id;
			$list->rpp = $this->rpp;
			$list->page = kboard_pageid();
			$list->setSearchOption(kboard_search_option());

			if (kboard_start_date() && kboard_end_date()) {
				$list->setDateRange(kboard_start_date(), kboard_end_date());
			} else {
				$start_date = date('Ymd', strtotime('-1 month', current_time('timestamp')));
				$end_date = date('Ymd', current_time('timestamp'));

				$date_range = apply_filters('kboard_sales_default_date_range', array('start_date' => $start_date, 'end_date' => $end_date), $this->board);

				$list->setDateRange($date_range['start_date'], $date_range['end_date']);
			}

			$list->setContentCategory1(kboard_sales_category1());
			$list->setContentCategory2(kboard_sales_category2());
			$list->init(get_current_user_id());

			$order = new KBOrder();
			$order->board = $this->board;
			$order->board_id = $this->board_id;

			$vars = array(
				'list' => $list,
				'order' => $order,
				'url' => $url,
				'skin' => $this->skin,
				'skin_path' => $this->skin->url($this->skin_name),
				'skin_dir' => $this->skin->dir($this->skin_name),
				'board' => $this->board,
				'boardBuilder' => $this,
			);

			echo $this->skin->load($this->skin_name, 'sales.php', $vars);
		} else if (is_user_logged_in()) {
			echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
			echo "<script>window.location.href='{$url->set('mod', 'list')->toString()}';</script>";
		} else {
			$login_url = wp_login_url($_SERVER['REQUEST_URI']);
			echo '<script>alert("' . __('You do not have permission.', 'kboard') . '");</script>';
			echo "<script>top.window.location.href='{$login_url}';</script>";
		}
	}

	/**
	 * 최신글 리스트를 생성한다.
	 * @param boolean $with_notice
	 * @param array $args
	 * @return string
	 */
	public function createLatest($with_notice = true, $args = array())
	{
		ob_start();

		$list = new KBContentList($this->board_id);

		if (!is_array($this->board_id) && $this->board->isPrivate()) {
			if (is_user_logged_in()) {
				$list->memberUID(get_current_user_id());
			} else {
				$list->stop = true;
			}
		}

		$list->is_latest = true;
		$list->latest = $args;
		$list->category1($this->category1);
		$list->category2($this->category2);
		$list->category3($this->category3);
		$list->category4($this->category4);
		$list->category5($this->category5);
		$list->setSorting($this->sort);
		$list->rpp($this->rpp);
		$list->setDayOfWeek($this->dayofweek);
		$list->setWithinDays($this->within_days);
		$list->setRandom($this->random);
		$list->setSortRandom($this->sort_random);
		$list->getList('', '', $with_notice);

		$url = new KBUrl();
		$url->is_latest = true;
		$url->setBoard($this->board);
		$url->setPath($this->url);

		$vars = array(
			'latest' => $args,
			'board_url' => $this->url,
			'list' => $list,
			'url' => $url,
			'skin' => $this->skin,
			'skin_path' => $this->skin->url($this->skin_name),
			'skin_dir' => $this->skin->dir($this->skin_name),
			'board' => $this->board,
			'boardBuilder' => $this,
		);

		echo $this->skin->load($this->skin_name, 'latest.php', $vars);

		return ob_get_clean();
	}

	public function isNotAllowed($board_id)
	{
		$not_allowed = false;
		$allowed_board_id = $this->board_id;
		$allowed_board_id = apply_filters('kboard_allowed_board_id', $allowed_board_id, $this->board);
		if (is_array($allowed_board_id)) {
			if (!in_array($board_id, $allowed_board_id)) {
				$not_allowed = true;
			}
		} else if ($board_id != $allowed_board_id) {
			$not_allowed = true;
		}
		return $not_allowed;
	}
}
