<?php
/*
Plugin Name: KBoard : 게시판
Plugin URI: https://www.cosmosfarm.com/products/kboard
Description: 워드프레스 KBoard 게시판 플러그인 입니다.
Version: 6.7.1
Author: 코스모스팜 - Cosmosfarm
Author URI: https://www.cosmosfarm.com/
*/

if(!defined('ABSPATH')) exit;

define('KBOARD_VERSION', '6.7.1');
define('KBOARD_PAGE_TITLE', 'KBoard : 게시판');
define('KBOARD_WORDPRESS_ROOT', substr(ABSPATH, 0, -1));
define('KBOARD_WORDPRESS_APP_ID', '083d136637c09572c3039778d8667b27');
define('KBOARD_DIR_PATH', dirname(__FILE__));
define('KBOARD_URL_PATH', plugins_url('', __FILE__));
define('KBOARD_DASHBOARD_PAGE', admin_url('admin.php?page=kboard_dashboard'));
define('KBOARD_LIST_PAGE', admin_url('admin.php?page=kboard_list'));
define('KBOARD_NEW_PAGE', admin_url('admin.php?page=kboard_new'));
define('KBOARD_SETTING_PAGE', admin_url('admin.php?page=kboard_list'));
define('KBOARD_LATESTVIEW_PAGE', admin_url('admin.php?page=kboard_latestview'));
define('KBOARD_LATESTVIEW_NEW_PAGE', admin_url('admin.php?page=kboard_latestview_new'));
define('KBOARD_BACKUP_PAGE', admin_url('admin.php?page=kboard_backup'));
define('KBOARD_CATEGORY_PAGE', admin_url('admin.php?page=kboard_category_update'));
define('KBOARD_CONTENT_LIST_PAGE', admin_url('admin.php?page=kboard_content_list'));

if(!defined('KBOARD_STORE_AUTH')){
	define('KBOARD_STORE_AUTH', true);
}

if(!defined('KBOARD_CONNECT_COSMOSFARM')){
	define('KBOARD_CONNECT_COSMOSFARM', true);
}

include_once 'class/KBContent.class.php';
include_once 'class/KBContentList.class.php';
include_once 'class/KBContentMedia.class.php';
include_once 'class/KBCommentMedia.class.php';
include_once 'class/KBContentOption.class.php';
include_once 'class/KBController.class.php';
include_once 'class/KBoard.class.php';
include_once 'class/KBoardBuilder.class.php';
include_once 'class/KBoardFields.class.php';
include_once 'class/KBoardList.class.php';
include_once 'class/KBoardMeta.class.php';
include_once 'class/KBoardPreventCopy.class.php';
include_once 'class/KBoardSkin.class.php';
include_once 'class/KBoardTreeCategory.class.php';
include_once 'class/KBOrder.class.php';
include_once 'class/KBOrderHistory.class.php';
include_once 'class/KBOrderItem.class.php';
include_once 'class/KBOrderSales.class.php';
include_once 'class/KBStore.class.php';
include_once 'class/KBTemplate.class.php';
include_once 'class/KBUrl.class.php';
include_once 'class/KBUpgrader.class.php';
include_once 'class/KBRouter.class.php';
include_once 'class/KBLatestview.class.php';
include_once 'class/KBLatestviewList.class.php';
include_once 'class/KBFileHandler.class.php';
include_once 'class/KBVote.class.php';
include_once 'class/KBSearchEngine.class.php';
include_once 'class/KBSearchIndexer.class.php';
include_once 'helper/Pagination.helper.php';
include_once 'helper/Security.helper.php';
include_once 'helper/Functions.helper.php';
include_once 'helper/Search.helper.php';

/**
 * 애드온 파일 로딩
 */
foreach(glob(KBOARD_DIR_PATH . '/addons/*.php') as $filename){
	include_once $filename;
}

add_action('plugins_loaded', 'kboard_plugins_loaded');
function kboard_plugins_loaded(){
	if(!session_id() && (!is_admin() || kboard_id()) && !wp_is_json_request()){
		session_start();
	}
}

/**
 * KBoard 게시판 시작
 */
function kboard_init(){
	// 번역 파일 등록
	if(version_compare($GLOBALS['wp_version'], '6.7', '<')){
		load_plugin_textdomain('kboard', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}
	else{
		load_textdomain('kboard', KBOARD_DIR_PATH . '/languages/kboard-' . determine_locale() . '.mo');
	}
	
	// 스킨의 functions.php 파일을 실행한다.
	$skin = KBoardSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		$skin->loadFunctions($skin_name);
	}
	
	// 게시판 페이지 이동
	$router = new KBRouter();
	$router->process();
	
	// 컨트롤러 시작
	$controller = new KBController();
	
	// 템플릿 시작
	$template = new KBTemplate();
	$template->route();
	
	$kboard_list_sort = isset($_GET['kboard_list_sort']) ? sanitize_text_field($_GET['kboard_list_sort']) : '';
	$kboard_list_sort_remember = isset($_GET['kboard_list_sort_remember']) ? intval($_GET['kboard_list_sort_remember']) : '';
	if($kboard_list_sort && $kboard_list_sort_remember){
		if(!in_array($kboard_list_sort, kboard_list_sorting_types())){
			$kboard_list_sort = '';
		}
		
		if($kboard_list_sort){
			$_COOKIE["kboard_list_sort_{$kboard_list_sort_remember}"] = $kboard_list_sort;
			setcookie("kboard_list_sort_{$kboard_list_sort_remember}", $kboard_list_sort, strtotime('+1 year'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		}
		else{
			$_COOKIE["kboard_list_sort_{$kboard_list_sort_remember}"] = '';
			setcookie("kboard_list_sort_{$kboard_list_sort_remember}", '', strtotime('-1 year'), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
		}
	}
}
add_action('init', 'kboard_init', 5);

/**
 * 테마 헤더에 정보 출력
 */
function kboard_get_header(){
	// SEO 시작
	include_once 'class/KBSeo.class.php';
	$seo = new KBSeo();
}
add_action('get_header', 'kboard_get_header', 999);

/**
 * KBoard 관리자 페이지 기능 실행
 */
function kboard_admin_init(){
	$page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
	
	// 게시판 관리
	if($page == 'kboard_list' && (!isset($_GET['board_id']) || !$_GET['board_id'])){
		$wp_list_table = _get_list_table('WP_Posts_List_Table');
		if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'reset_total'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->resetTotal();
			}
		}
		else if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'truncate'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->truncate();
			}
		}
		else if(isset($_POST['board_id']) && $wp_list_table->current_action() == 'delete'){
			set_time_limit(0);
			ini_set('memory_limit', '-1');
			foreach($_POST['board_id'] as $key=>$board_id){
				$board = new KBoard($board_id);
				$board->delete();
			}
		}
	}
	
	// 관리자 컨트롤러 시작
	include_once KBOARD_DIR_PATH . '/class/KBAdminController.class.php';
	new KBAdminController();
	
	// 사용자 프로필 필드 추가
	include_once KBOARD_DIR_PATH . '/class/KBUserProfileFields.class.php';
	new KBUserProfileFields();
	
	// 스토어 연동 액세스 토큰 설정
	if($page == 'kboard_store' && isset($_GET['access_token']) && $_GET['access_token']){
		$_COOKIE['kboard_access_token'] = sanitize_text_field($_GET['access_token']);
		setcookie('kboard_access_token', $_COOKIE['kboard_access_token'], 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
	}
}
add_action('admin_init', 'kboard_admin_init');

/**
 * 글쓰기 에디터에 미디어 추가하기 버튼을 추가한다.
 * @param string $editor_id
 */
function kboard_editor_button($editor_id){
	echo ' <button type="button" class="button" onclick="kboard_editor_open_media()">'.__('KBoard Add Media', 'kboard').'</button> ';
}

/**
 * 글쓰기 에디터에 미디어 버튼을 등록한다.
 * @param array $buttons
 * @return string
 */
function kboard_register_media_button($buttons){
	array_push($buttons, 'kboard_media');
	return $buttons;
}

/**
 * 글쓰기 에디터에 미디어 버튼을 추가한다.
 * @param array $plugin_array
 * @return array
 */
function kboard_add_media_button($plugin_array){
	if(!is_admin() || kboard_view_iframe()){
		$plugin_array['kboard_media_button_script'] = plugins_url('/template/js/editor_media_button.js', __FILE__);
	}
	return $plugin_array;
}

/**
 * 플러그인 페이지에 링크 추가
 * @param array $links
 * @return array
 */
function kboard_settings_link($links){
	return array_merge($links, array('kboard-new'=>'<a href="'.admin_url('admin.php?page=kboard_new').'">'.__('New Forum', 'kboard').'</a>'));
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'kboard_settings_link');

/**
 * 워드프레스 관리자 웰컴 패널에 KBoard 패널을 추가한다.
 */
function kboard_welcome_panel(){
	echo '<script>jQuery(document).ready(function(){jQuery("div.welcome-panel-content").eq(0).hide();});</script>';
	include_once 'pages/welcome.php';
}
add_action('welcome_panel', 'kboard_welcome_panel');

/**
 * 워드프레스 관리자 메뉴에 KBoard 추가
 */
function kboard_settings_menu(){
	global $wpdb, $_wp_last_object_menu;
	
	// KBoard 메뉴 등록
	$_wp_last_object_menu++;
	add_menu_page(__(KBOARD_PAGE_TITLE, 'kboard'), 'KBoard', 'manage_kboard', 'kboard_dashboard', 'kboard_dashboard', plugins_url('kboard/images/icon.png'), $_wp_last_object_menu);
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('대시보드', 'kboard'), 'manage_kboard', 'kboard_dashboard');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('게시판 목록 및 관리', 'kboard'), 'manage_kboard', 'kboard_list', 'kboard_list');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('게시판 생성', 'kboard'), 'manage_kboard', 'kboard_new', 'kboard_new');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('최신글 모아보기', 'kboard'), 'manage_kboard', 'kboard_latestview', 'kboard_latestview');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('최신글 모아보기 생성', 'kboard'), 'manage_kboard', 'kboard_latestview_new', 'kboard_latestview_new');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('백업 및 복구', 'kboard'), 'manage_kboard', 'kboard_backup', 'kboard_backup');
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('카테고리 변경', 'kboard'), 'manage_kboard', 'kboard_category_update', 'kboard_category_update');

	if(KBOARD_CONNECT_COSMOSFARM){
		add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('업데이트', 'kboard'), 'manage_kboard', 'kboard_updates', 'kboard_updates');
		
		// 스토어 메뉴 등록
		$_wp_last_object_menu++;
		add_menu_page(__('스토어', 'kboard'), __('스토어', 'kboard'), 'manage_kboard', 'kboard_store', 'kboard_store', plugins_url('kboard/images/icon.png'), $_wp_last_object_menu);
		add_submenu_page('kboard_store', __('스토어', 'kboard'), __('스토어', 'kboard'), 'manage_kboard', 'kboard_store');
		add_submenu_page('kboard_store', __('Partners', 'kboard'), __('Partners', 'kboard'), 'manage_kboard', 'kboard_store_partners', 'kboard_store_partners');
	}
	
	add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), __('전체 게시글', 'kboard'), 'manage_kboard', 'kboard_content_list', 'kboard_content_list');
	
	// 댓글 플러그인 활성화면 댓글 리스트 페이지를 보여준다.
	if(defined('KBOARD_COMMNETS_VERSION') && version_compare(KBOARD_COMMNETS_VERSION, '1.3', '>=') && version_compare(KBOARD_COMMNETS_VERSION, '3.3', '<')){
		add_submenu_page('kboard_dashboard', __(KBOARD_COMMENTS_PAGE_TITLE, 'kboard-comments'), __('전체 댓글', 'kboard'), 'administrator', 'kboard_comments_list', 'kboard_comments_list');
	}
	else if(defined('KBOARD_COMMNETS_VERSION') && version_compare(KBOARD_COMMNETS_VERSION, '3.3', '>=')){
		kboard_comments_settings_menu();
	}
	
	// 메뉴 액션 실행
	do_action('kboard_admin_menu');
	
	$result = $wpdb->get_results("SELECT `meta`.`board_id`, `setting`.`board_name` FROM `{$wpdb->prefix}kboard_board_meta` AS `meta` LEFT JOIN `{$wpdb->prefix}kboard_board_setting` AS `setting` ON `meta`.`board_id`=`setting`.`uid` WHERE `meta`.`key`='add_menu_page'");
	foreach($result as $row){
		add_submenu_page('kboard_dashboard', __(KBOARD_PAGE_TITLE, 'kboard'), $row->board_name, 'manage_kboard', "kboard_admin_view_{$row->board_id}", 'kboard_admin_view');
	}
}
add_action('admin_menu', 'kboard_settings_menu');

/**
 * 스토어 상품 리스트 페이지
 */
function kboard_store(){
	KBStore::productsList();
}

/**
 * 파트너 페이지
 */
function kboard_store_partners(){
	$store_partners_list = get_transient('kboard_store_partners_list');
	
	if(!$store_partners_list){
		$response = wp_remote_get('http://updates.wp-kboard.com/v1/AUTH_3529e134-c9d7-4172-8338-f64309faa5e5/kboard/partners.json');
		
		if(!is_wp_error($response) && isset($response['body']) && $response['body']){
			$store_partners_list = json_decode($response['body']);
		}
		else{
			$store_partners_list = array();
		}
		
		set_transient('kboard_store_partners_list', $store_partners_list, 60*60);
	}
	
	include_once 'pages/kboard_store_partners.php';
}

/**
 * 게시판 대시보드 페이지
 */
function kboard_dashboard(){
	include_once 'pages/kboard_dashboard.php';
}

/**
 * 게시판 목록 페이지
 */
function kboard_list(){
	if(isset($_GET['board_id']) && $_GET['board_id']){
		kboard_setting();
	}
	else{
		include_once 'class/KBoardListTable.class.php';
		$table = new KBoardListTable();
		/*
		 if(isset($_POST['board_id']) && $table->current_action() == 'delete'){
		 foreach($_POST['board_id'] as $key=>$value){
		 $table->board->delete($value);
		 }
		 }
		 */
		$table->prepare_items();
		include_once 'pages/kboard_list.php';
	}
}

/**
 * 새로운 게시판 생성
 */
function kboard_new(){
	$board = new KBoard();
	$meta = $board->meta;
	$skin = KBoardSkin::getInstance();
	if(defined('KBOARD_COMMNETS_VERSION')){
		include_once WP_CONTENT_DIR.'/plugins/kboard-comments/class/KBCommentSkin.class.php';
		$comment_skin = KBCommentSkin::getInstance();
	}
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-timepicker');
	wp_enqueue_style('jquery-flick-style');
	wp_enqueue_style('jquery-timepicker');
	wp_enqueue_script('nested-sortable');
	wp_enqueue_script('kboard-setting-script');
	include_once 'pages/kboard_setting.php';
}

/**
 * 게시판 목록 페이지
 */
function kboard_setting(){
	$board_id = isset($_GET['board_id'])?$_GET['board_id']:'';
	$board = new KBoard($board_id);
	$meta = $board->meta;
	$skin = KBoardSkin::getInstance();
	if(defined('KBOARD_COMMNETS_VERSION')){
		include_once WP_CONTENT_DIR.'/plugins/kboard-comments/class/KBCommentSkin.class.php';
		$comment_skin = KBCommentSkin::getInstance();
	}
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-timepicker');
	wp_enqueue_style('jquery-flick-style');
	wp_enqueue_style('jquery-timepicker');
	wp_enqueue_script('nested-sortable');
	wp_enqueue_script('kboard-setting-script');
	include_once 'pages/kboard_setting.php';
}

/**
 * 최신글 모아보기
 */
function kboard_latestview(){
	if(isset($_GET['latestview_uid']) && $_GET['latestview_uid']){
		$skin = KBoardSkin::getInstance();
		$latestview = new KBLatestview();
		$latestview->initWithUID($_GET['latestview_uid']);
		$linked_board = $latestview->getLinkedBoard();
		$board_list = new KBoardList();
		include_once 'pages/kboard_latestview_setting.php';
	}
	else{
		include_once 'class/KBLatestviewListTable.class.php';
		$table = new KBLatestviewListTable();
		if(isset($_POST['latestview_uid']) && $table->current_action() == 'delete'){
			$latestview = new KBLatestview();
			foreach($_POST['latestview_uid'] as $key=>$latestview_uid){
				$latestview->initWithUID($latestview_uid);
				$latestview->delete();
			}
		}
		$table->prepare_items();
		include_once 'pages/kboard_latestview.php';
	}
}

/**
 * 최신글 모아보기 생성
 */
function kboard_latestview_new(){
	$skin = KBoardSkin::getInstance();
	$latestview = new KBLatestview();
	$linked_board = $latestview->getLinkedBoard();
	$board_list = new KBoardList();
	include_once 'pages/kboard_latestview_setting.php';
}

/**
 * 게시판 백업 및 복구 페이지
 */
function kboard_backup(){
	include_once 'pages/kboard_backup.php';
}

/**
 * 카테고리 변경 페이지
 */
function kboard_category_update(){
	$list = new KBoardList();
	$list->initWithKeyword();
	$board_list = $list->resource;
	include_once 'pages/kboard_category_update.php';
}

/**
 * 플러그인 업데이트 페이지
 */
function kboard_updates(){
	$action = isset($_GET['action'])?kboard_htmlclear($_GET['action']):'';
	$download_url = isset($_GET['download_url'])?kboard_htmlclear($_GET['download_url']):'';
	$download_version = isset($_GET['download_version'])?kboard_htmlclear($_GET['download_version']):'';
	if($action){
		check_admin_referer('kboard_updates');
	}
	$form_url = add_query_arg(array(
		'page'             => 'kboard_updates',
		'action'           => $action,
		'download_url'     => $download_url,
		'download_version' => $download_version
	), admin_url('admin.php'));
	$form_url = wp_nonce_url($form_url, 'kboard_updates');
	
	$upgrader = KBUpgrader::getInstance();
	
	if($action == 'kboard'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 게시판 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getKBoard();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 게시판 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'kboard-noskins'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 게시판 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getKBoardNoSkins();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 게시판 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'comments'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 댓글 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getComments();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 댓글 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'comments-noskins'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>KBoard 댓글 플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->getCommentsNoSkins();
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>KBoard 댓글 플러그인 설치가 완료 되었습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_updates') . "\" class=\"button\">업데이트 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'plugin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_PLUGINS)) exit;
		
		echo "<h1>플러그인 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_PLUGINS);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>플러그인 설치가 완료 되었습니다. 플러그인을 활성화해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('plugins.php') . "\" class=\"button\">플러그인 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'theme'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_THEMES)) exit;
		
		echo "<h1>테마 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_THEMES);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>테마 설치가 완료 되었습니다. 테마를 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('themes.php') . "\" class=\"button\">테마 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'kboard-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_KBOARD_SKIN)) exit;
		
		echo "<h1>KBoard 게시판 스킨 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_KBOARD_SKIN);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>게시판 스킨 설치가 완료 되었습니다. <code>KBoard -&gt; 게시판 목록 -&gt; 게시판 선택 -&gt; 게시판 관리</code> 페이지에서 스킨을 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_store') . "\" class=\"button\">스토어 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	else if($action == 'comments-skin'){
		if(!$upgrader->credentials($form_url, WP_CONTENT_DIR . KBUpgrader::$TYPE_COMMENTS_SKIN)) exit;
		
		echo "<h1>KBoard 댓글 스킨 설치</h1>";
		@ob_flush();
		@flush();
		
		echo "<p>다운로드 중입니다. 기다려주세요.</p>";
		@ob_flush();
		@flush();
		
		$download_file = $upgrader->download($download_url, $download_version, KBStore::getAccessToken());
		echo "<p>다운로드를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		$install_result = $upgrader->install($download_file, KBUpgrader::$TYPE_COMMENTS_SKIN);
		echo "<p>설치를 완료했습니다.</p>";
		@ob_flush();
		@flush();
		
		echo "<p>댓글 스킨 설치가 완료 되었습니다. <code>KBoard -&gt; 게시판 목록 -&gt; 게시판 선택 -&gt; 게시판 관리</code> 페이지에서 스킨을 선택해주세요.</p>";
		@ob_flush();
		@flush();
		
		echo "<p><a href=\"" . admin_url('admin.php?page=kboard_store') . "\" class=\"button\">스토어 메뉴로 이동</a></p>";
		@ob_flush();
		@flush();
	}
	
	if(!$action){
		$upgrader->flush();
		$version = $upgrader->getLatestVersion();
		include_once 'pages/kboard_updates.php';
	}
}

/**
 * 전체 게시글 리스트
 */
function kboard_content_list(){
	include_once 'class/KBContentListTable.class.php';
	$table = new KBContentListTable();
	if(isset($_POST['uid']) && is_array($_POST['uid'])){
		$action = $table->current_action();
		if(in_array($action, array('delete', 'delete_immediately'), true)){
			check_admin_referer('kboard_content_list_bulk_action', 'kboard_content_list_nonce');
		}

		$content = new KBContent();
		if(in_array($action, array('delete', 'delete_immediately'), true)){
			foreach($_POST['uid'] as $key=>$value){
				$uid = intval($value);
				if(!$uid) continue;

				$content->initWithUID($uid);
				if(!$content->uid) continue;

				$content->remove();
			}
		}
		else if($action){
			$status_list = kboard_content_status_list();
			$status = '';
			
			if($action == 'published'){
				$status = '';
			}
			else if(isset($status_list[$action])){
				$status = $action;
			}
			else{
				$status = null;
			}
			
			if($status !== null){
				foreach($_POST['uid'] as $key=>$value){
					$content->initWithUID($value);
					$content->status = $status;
					$content->updateContent();
				}
			}
		}
	}
	$table->prepare_items();
	wp_enqueue_style('jquery-flick-style', KBOARD_URL_PATH.'/template/css/jquery-ui.css', array(), '1.12.1');
	wp_enqueue_style('jquery-timepicker', KBOARD_URL_PATH.'/template/css/jquery.timepicker.css', array(), KBOARD_VERSION);
	include_once 'pages/kboard_content_list.php';
}

/**
 * 관리자 페이지에서 게시판 보기
 */
function kboard_admin_view(){
	$screen = get_current_screen();
	$board_id = intval(str_replace('kboard_page_kboard_admin_view_', '', $screen->id));
	$board = new KBoard($board_id);
	include_once 'pages/kboard_admin_view.php';
}

/**
 * 멀티사이트 게시판 DB 테이블 변경
 */
function kboard_switch_to_blog($args=array()){
	if(isset($args['blog']) && $args['blog']){
		switch_to_blog($args['blog']);
	}
	else if(isset($_SESSION['kboard_switch_to_blog']) && $_SESSION['kboard_switch_to_blog']){
		switch_to_blog($_SESSION['kboard_switch_to_blog']);
	}
}
add_action('kboard_switch_to_blog', 'kboard_switch_to_blog', 0, 1);

/**
 * 멀티사이트 게시판 DB 테이블 복구
 */
function kboard_restore_current_blog($args=array()){
	if(isset($args['blog']) && $args['blog']){
		restore_current_blog();
	}
	else if(isset($_SESSION['kboard_switch_to_blog']) && $_SESSION['kboard_switch_to_blog']){
		restore_current_blog();
	}
}
add_action('kboard_restore_current_blog', 'kboard_restore_current_blog', 9999, 1);

/**
 * 게시판 생성 숏코드 [kboard]
 * @param array $args
 * @return string
 */
function kboard_builder($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	
	if(isset($args['blog']) && $args['blog']){
		$_SESSION['kboard_switch_to_blog'] = $args['blog'];
		do_action('kboard_switch_to_blog', $args);
	}
	else{
		$_SESSION['kboard_switch_to_blog'] = '';
	}
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if($board->id){
		$builder = new KBoardBuilder($board->id);
		$builder->board = $board;
		$builder->setSkin($board->skin);
		if(wp_is_mobile() && $board->meta->mobile_page_rpp){
			$builder->setRpp($board->meta->mobile_page_rpp);
		}
		else{
			$builder->setRpp($board->page_rpp);
		}
		
		if(isset($args['mod']) && $args['mod']){
			if(!kboard_mod()){
				$builder->setMOD($args['mod']);
			}
		}
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		if(isset($args['category3']) && $args['category3']){
			$builder->category3 = $args['category3'];
		}
		if(isset($args['category4']) && $args['category4']){
			$builder->category4 = $args['category4'];
		}
		if(isset($args['category5']) && $args['category5']){
			$builder->category5 = $args['category5'];
		}
		
		$kboard = $builder->create();
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $kboard;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}
add_shortcode('kboard', 'kboard_builder');

/**
 * 선택된 페이지에 자동으로 게시판 생성
 * @param string $content
 * @return string
 */
function kboard_auto_builder($content){
	global $post, $wpdb;
	if(isset($post->ID) && is_page($post->ID) && !post_password_required()){
		$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='$post->ID'");
		if($board_id) return $content . kboard_builder(array('id'=>$board_id));
	}
	return $content;
}
add_filter('the_content', 'kboard_auto_builder');

/**
 * 최신글 생성 숏코드 [kboard_latest]
 * @param array $args
 * @return string
 */
function kboard_latest_shortcode($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	if(!isset($args['url']) || !$args['url']) return 'KBoard 알림 :: url=null, 페이지 주소는 필수입니다.';
	if(!isset($args['rpp']) || !$args['rpp']) $args['rpp'] = 5;
	if(!isset($args['mobile_rpp']) || !$args['mobile_rpp']) $args['mobile_rpp'] = $args['rpp'];
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_switch_to_blog', $args);
	}
	
	$board = new KBoard();
	$board->setID($args['id']);
	
	if(isset($args['private']) && $args['private'] == 'true'){
		$board->setPrivate();
	}
	
	if($board->id){
		$empty_list_message = isset($args['empty_list_message']) ? trim($args['empty_list_message']) : '';
		if(!$board->getListTotal() && $empty_list_message){
			return sprintf('<div class="kboard-latest-empty-list-message">%s</div>', $empty_list_message);
		}
		
		$builder = new KBoardBuilder($board->id, true);
		$builder->board = $board;
		$builder->setSkin($board->skin);
		
		if(wp_is_mobile()){
			$builder->setRpp($args['mobile_rpp']);
		}
		else{
			$builder->setRpp($args['rpp']);
		}
		$builder->setURL($args['url']);
		
		if(isset($args['sort']) && $args['sort']){
			if(strpos($args['sort'], '|') !== false){
				$sort = explode('|', $args['sort']);
				$args['sort'] = reset($sort);
			}
			$builder->setSorting($args['sort']);
		}
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		else{
			$builder->category1 = '';
		}
		
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		else{
			$builder->category2 = '';
		}
		
		if(isset($args['category3']) && $args['category3']){
			$builder->category3 = $args['category3'];
		}
		else{
			$builder->category3 = '';
		}
		
		if(isset($args['category4']) && $args['category4']){
			$builder->category4 = $args['category4'];
		}
		else{
			$builder->category4 = '';
		}
		
		if(isset($args['category5']) && $args['category5']){
			$builder->category5 = $args['category5'];
		}
		else{
			$builder->category5 = '';
		}
		
		$with_notice = true;
		if(isset($args['with_notice']) && $args['with_notice'] == 'false'){
			$with_notice = false;
		}
		
		if(isset($args['dayofweek']) && $args['dayofweek']){
			if(strpos($args['dayofweek'], '|') !== false){
				$dayofweek = explode('|', $args['dayofweek']);
				$args['dayofweek'] = reset($dayofweek);
			}
			$builder->setDayOfWeek($args['dayofweek']);
		}
		
		if(isset($args['within_days']) && $args['within_days']){
			$within_days = intval($args['within_days']);
			$builder->setWithinDays($within_days);
		}
		
		if(isset($args['random']) && $args['random'] == 'true'){
			$builder->setRandom(true);
		}
		
		if(isset($args['sort_random']) && $args['sort_random'] == 'true'){
			$builder->setSortRandom(true);
		}
		
		$args['type'] = 'latest';
		$latest = $builder->createLatest($with_notice, $args);
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $latest;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 게시판입니다.';
	}
}
add_shortcode('kboard_latest', 'kboard_latest_shortcode');

/**
 * 최신글 모아보기 생성 숏코드 [kboard_latestview]
 * @param array $args
 * @return string
 */
function kboard_latestview_shortcode($args){
	if(!isset($args['id']) || !$args['id']) return 'KBoard 알림 :: id=null, 아이디값은 필수입니다.';
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_switch_to_blog', $args);
	}
	
	$latestview = new KBLatestview($args['id']);
	
	if($latestview->uid){
		$count = 0;
		foreach($latestview->getLinkedBoard() as $board_id){
			$board = new Kboard($board_id);
			$count += $board->getListTotal();
		}
		
		$empty_list_message = isset($args['empty_list_message']) ? trim($args['empty_list_message']) : '';
		if(!$count && $empty_list_message){
			return sprintf('<div class="kboard-latest-empty-list-message">%s</div>', $empty_list_message);
		}
		
		$builder = new KBoardBuilder($latestview->getLinkedBoard(), true);
		$builder->board = new KBoard();
		$builder->setSkin($latestview->skin);
		
		if(wp_is_mobile()){
			$builder->setRpp($latestview->mobile_rpp);
		}
		else{
			$builder->setRpp($latestview->rpp);
		}
		$builder->setSorting($latestview->sort);
		
		if(isset($args['category1']) && $args['category1']){
			$builder->category1 = $args['category1'];
		}
		else{
			$builder->category1 = '';
		}
		
		if(isset($args['category2']) && $args['category2']){
			$builder->category2 = $args['category2'];
		}
		else{
			$builder->category2 = '';
		}
		
		if(isset($args['category3']) && $args['category3']){
			$builder->category3 = $args['category3'];
		}
		else{
			$builder->category3 = '';
		}
		
		if(isset($args['category4']) && $args['category4']){
			$builder->category4 = $args['category4'];
		}
		else{
			$builder->category4 = '';
		}
		
		if(isset($args['category5']) && $args['category5']){
			$builder->category5 = $args['category5'];
		}
		else{
			$builder->category5 = '';
		}
		
		$with_notice = true;
		if(isset($args['with_notice']) && $args['with_notice'] == 'false'){
			$with_notice = false;
		}
		
		if(isset($args['dayofweek']) && $args['dayofweek']){
			if(strpos($args['dayofweek'], '|') !== false){
				$dayofweek = explode('|', $args['dayofweek']);
				$args['dayofweek'] = reset($dayofweek);
			}
			$builder->setDayOfWeek($args['dayofweek']);
		}
		
		if(isset($args['within_days']) && $args['within_days']){
			$within_days = intval($args['within_days']);
			$builder->setWithinDays($within_days);
		}
		
		if(isset($args['random']) && $args['random'] == 'true'){
			$builder->setRandom(true);
		}
		
		$args['type'] = 'latestview';
		$latest = $builder->createLatest($with_notice, $args);
		
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return $latest;
	}
	else{
		if(isset($args['blog']) && $args['blog']){
			do_action('kboard_restore_current_blog', $args);
		}
		
		return 'KBoard 알림 :: id='.$args['id'].', 생성되지 않은 최신글 뷰 입니다.';
	}
}
add_shortcode('kboard_latestview', 'kboard_latestview_shortcode');

/**
 * 회원 본인의 글만 표시하는 숏코드 [kboard_my_list]
 * @param array $args
 * @return string
 */
function kboard_my_list_shortcode($args){
	global $wpdb;
	
	$user_id = get_current_user_id();
	
	if(!$user_id){
		return sprintf('<div class="kboard-my-list-login-message" style="margin: 20px 0; font-size: 16px;"><p>화면을 보시려면 <a href="%s" title="로그인">로그인</a> 해주세요.</p></div>', esc_url(wp_login_url($_SERVER['REQUEST_URI'])));
	}
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_switch_to_blog', $args);
	}
	
	$where = array();
	$where[] = "`member_uid`='{$user_id}'";
	
	$where = implode(' AND ', $where);
	
	$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}kboard_board_content` WHERE {$where} ORDER BY `date` DESC LIMIT 100");
	
	$empty_list_message = isset($args['empty_list_message']) ? trim($args['empty_list_message']) : '';
	if(!$results && $empty_list_message){
		return sprintf('<div class="kboard-my-list-empty-message" style="margin: 20px 0; font-size: 16px;">%s</div>', $empty_list_message);
	}
	
	$url = new KBUrl();
	$url->is_latest = true;
	
	?>
	<div class="kboard-my-list" style="margin: 20px 0; font-size: 16px;">
		<ul>
			<?php
			foreach($results as $item):
				$content = new KBContent();
				$content->initWithUID($item->uid);
				?>
				<li><span class="board-name">[<?php echo esc_html($content->getBoard()->getTitle())?>]</span> <a class="content-title" href="<?php echo esc_url($url->getDocumentRedirect($content->uid))?>"><?php echo esc_html($content->getTitle())?></a> <span class="content-date" style="color:gray">(<?php echo $content->getDate()?>)</span></li>
			<?php endforeach?>
		</ul>
	</div>
	<?php
	
	if(isset($args['blog']) && $args['blog']){
		do_action('kboard_restore_current_blog', $args);
	}
}
add_shortcode('kboard_my_list', 'kboard_my_list_shortcode');

/**
 * 비동기 게시판 빌더 실행
 */
function kboard_ajax_builder(){
	check_ajax_referer('kboard_ajax_security', 'security');
	
	if(isset($_SESSION['kboard_board_id']) && $_SESSION['kboard_board_id']){
		$board_id = intval($_SESSION['kboard_board_id']);
	}
	
	if(isset($_REQUEST['board_id']) && $_REQUEST['board_id']){
		$board_id = intval($_REQUEST['board_id']);
	}
	
	$board = new KBoard();
	$board->setID($board_id);
	
	if($board->id){
		// AJAX 목록도 일반 게시판 문서와 동일하게 게시판 읽기 권한을 적용한다.
		if(!$board->isReader(0, '')){
			wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')), 403);
		}

		$builder = new KBoardBuilder($board->id);
		$builder->mod = 'list';
		$builder->board = $board;
		$builder->is_ajax = true;
		$builder->view_iframe = $board->meta->view_iframe;
		
		if(isset($_REQUEST['view_iframe'])){
			$builder->view_iframe = $_REQUEST['view_iframe'] ? '1' : '';
		}
		
		if(isset($_REQUEST['rpp']) && $_REQUEST['rpp']){
			$builder->setRpp($_REQUEST['rpp']);
		}
		else if(wp_is_mobile() && $board->meta->mobile_page_rpp){
			$builder->setRpp($board->meta->mobile_page_rpp);
		}
		else{
			$builder->setRpp($board->page_rpp);
		}
		
		if(isset($_REQUEST['sort']) && $_REQUEST['sort']){
			$sort = sanitize_key($_REQUEST['sort']);
			$builder->setSorting($sort);
		}
		
		if(isset($_REQUEST['base_url']) && $_REQUEST['base_url']){
			$builder->setURL($_REQUEST['base_url']);
		}
		else{
			$builder->setURL(wp_get_referer());
		}
		
		if(isset($_REQUEST['skin']) && $_REQUEST['skin']){
			$skin = sanitize_key($_REQUEST['skin']);
			$builder->setSkin($skin);
		}
		else{
			$builder->setSkin($board->skin);
		}
		
		$ajax_builder_type = 'array';
		if(isset($_REQUEST['ajax_builder_type']) && in_array($_REQUEST['ajax_builder_type'], array('array', 'html'))){
			$ajax_builder_type = $_REQUEST['ajax_builder_type'];
		}
		
		if($ajax_builder_type == 'array'){
			wp_send_json(array('result'=>'success', 'data'=>$builder->getListArray()));
		}
		else if($ajax_builder_type == 'html'){
			wp_send_json(array('result'=>'success', 'data'=>$builder->getListHTML()));
		}
	}
	
	wp_send_json(array('result'=>'error', 'message'=>__('You do not have permission.', 'kboard')));
}
add_action('wp_ajax_kboard_ajax_builder', 'kboard_ajax_builder');
add_action('wp_ajax_nopriv_kboard_ajax_builder', 'kboard_ajax_builder');

/**
 * 에디터 이미지 AJAX 업로드 (Editor.js 등에서 사용)
 */
function kboard_media_ajax_upload(){
	check_ajax_referer('kboard_ajax_security', 'security');

	global $wpdb;

	$board_id = intval(isset($_POST['board_id']) ? $_POST['board_id'] : 0);
	$media_group = kboard_htmlclear(isset($_POST['media_group']) ? $_POST['media_group'] : '');
	$content_uid = intval(isset($_POST['content_uid']) ? $_POST['content_uid'] : 0);

	$media = new KBContentMedia();
	$media->board_id = $board_id;
	$media->media_group = $media_group;
	$media->content_uid = $content_uid;

	if(!$media->hasPermission()){
		wp_send_json(array('success' => 0, 'message' => __('You do not have permission.', 'kboard')));
	}

	if(empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK){
		wp_send_json(array('success' => 0, 'message' => __('File upload failed.', 'kboard')));
	}

	$_FILES['kboard_media_file'] = $_FILES['image'];
	$media->upload();

	$row = $wpdb->get_row("SELECT `file_path` FROM `{$wpdb->prefix}kboard_meida` WHERE `media_group`='" . esc_sql($media_group) . "' ORDER BY `uid` DESC LIMIT 1");

	if($row && $row->file_path){
		wp_send_json(array('success' => 1, 'file' => array('url' => site_url($row->file_path))));
	}

	wp_send_json(array('success' => 0, 'message' => __('File upload failed.', 'kboard')));
}
add_action('wp_ajax_kboard_media_ajax_upload', 'kboard_media_ajax_upload');
add_action('wp_ajax_nopriv_kboard_media_ajax_upload', 'kboard_media_ajax_upload');

/**
 * 관리자 알림 출력
 */
function kboard_admin_notices(){
	if(KBOARD_CONNECT_COSMOSFARM && current_user_can('manage_kboard')){
		
		// 관리자 알림 시작
		include_once KBOARD_DIR_PATH . '/class/KBAdminNotices.class.php';
		
		if(!is_writable(WP_CONTENT_DIR . '/uploads')){
			echo KBAdminNotices::get_upload_folder_not_writable_message();
		}
		
		if(!get_option('kboard_updates_notify_disabled')){
			$upgrader = KBUpgrader::getInstance();
			$vsersion = $upgrader->getLatestVersion()->kboard;
			
			if(version_compare(KBOARD_VERSION, $vsersion, '<')){
				echo KBAdminNotices::get_kboard_update_notice_message_message($vsersion);
			}
		}
		
		$svg_scan_pending = get_option('kboard_svg_batch_scan_pending');
		if($svg_scan_pending == KBOARD_VERSION){
			echo '<div class="notice notice-warning"><p>';
			echo sprintf(
				__('KBoard SVG cleanup is required after this update. %s and run the SVG cleanup card once.', 'kboard'),
				'<a href="' . esc_url(KBOARD_DASHBOARD_PAGE) . '">' . esc_html__('Open the dashboard', 'kboard') . '</a>'
			);
			echo '</p></div>';
		}
	}
}

/**
 * 게시글 검색 인덱스를 동기화한다.
 */
function kboard_search_indexer_sync_content_list($content){
	if(isset($content->uid) && $content->uid){
		KBSearchIndexer::sync($content->uid);
	}
}
add_action('kboard_document_insert', array('KBSearchIndexer', 'sync'), 10, 1);
add_action('kboard_document_update', array('KBSearchIndexer', 'sync'), 10, 1);
add_action('kboard_document_delete', array('KBSearchIndexer', 'delete'), 10, 1);
add_action('kboard_content_list_update', 'kboard_search_indexer_sync_content_list', 10, 1);
add_action('admin_notices', 'kboard_admin_notices');

/**
 * 업데이트 후 1회 SVG 파일 정리 작업을 실행한다.
 */


/**
 * 스크립트와 스타일 파일 등록
 */
function kboard_style(){
	// KBoard 미디어 추가 스타일 속성 등록
	wp_enqueue_style('kboard-editor-media', KBOARD_URL_PATH . '/template/css/editor_media.css', array(), KBOARD_VERSION);
	
	// 활성화된 스킨의 style.css 등록
	$skin = KBoardSkin::getInstance();
	foreach($skin->getActiveList() as $skin_name){
		wp_enqueue_style("kboard-skin-{$skin_name}", $skin->url($skin_name, 'style.css'), array(), KBOARD_VERSION);
	}
	
	wp_register_style('kboard-jquery-flick-style', KBOARD_URL_PATH . '/template/css/jquery-ui.css', array(), '1.12.1');
}
add_action('wp_enqueue_scripts', 'kboard_style', 999);
add_action('kboard_switch_to_blog', 'kboard_style');

/**
 * 스크립트와 스타일 파일 등록
 */
function kboard_scripts(){
	wp_enqueue_script('jquery');
	wp_enqueue_script('kboard-script', KBOARD_URL_PATH . '/template/js/script.js', array(), KBOARD_VERSION, true);
	
	// Tags Input 등록
	wp_register_style('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.css', array(), '1.3.3');
	wp_register_script('tagsinput', KBOARD_URL_PATH . '/assets/tagsinput/jquery.tagsinput.js', array('jquery'), '1.3.3');
	
	// Moment.js 등록
	wp_register_script('moment', KBOARD_URL_PATH . '/assets/moment/moment.js', array('jquery'), '2.17.1');
	
	// jQuery Date Range Picker Plugin 등록
	wp_register_style('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/daterangepicker.css', array(), '0.0.8');
	wp_register_script('daterangepicker', KBOARD_URL_PATH . '/assets/daterangepicker/jquery.daterangepicker.js', array('jquery', 'moment'), '0.0.8');
	
	// jQuery lightSlider 등록
	wp_register_style('lightslider', KBOARD_URL_PATH . '/assets/lightslider/css/lightslider.css', array(), '1.1.6');
	wp_register_script('lightslider', KBOARD_URL_PATH . '/assets/lightslider/js/lightslider.js', array('jquery'), '1.1.6');
	
	// 아임포트 등록
	wp_register_script('iamport-payment', 'https://cdn.iamport.kr/js/iamport.payment-1.1.7.js', array('jquery'), '1.1.7');
	
	// 구글 리캡차 등록
	wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
	
	// Summernot 등록
	wp_register_style('summernote', KBOARD_URL_PATH . '/assets/summernote/summernote-lite.css', array(), '0.8.18');
	wp_register_script('summernote', KBOARD_URL_PATH . '/assets/summernote/summernote-lite.js', array('jquery'), '0.8.18', true);
	wp_register_script('summernote-ko-KR', KBOARD_URL_PATH . '/assets/summernote/lang/summernote-ko-KR.js', array('summernote'), '0.8.18', true);
	wp_register_script('summernote-ja-JP', KBOARD_URL_PATH . '/assets/summernote/lang/summernote-ja-JP.js', array('summernote'), '0.8.18', true);

	// 신규 에디터 스캐폴드 런타임 등록
	wp_register_script('kboard-editor-registry', KBOARD_URL_PATH . '/assets/editor-scaffold/kboard-editor-registry.js', array(), KBOARD_VERSION, true);
	wp_register_script('kboard-editor-tiptap-runtime', KBOARD_URL_PATH . '/assets/editor-scaffold/kboard-editor-tiptap-runtime.js', array(), KBOARD_VERSION, true);
	wp_register_script('kboard-editor-tiptap-adapter', KBOARD_URL_PATH . '/assets/editor-scaffold/adapters/tiptap-adapter.js', array('kboard-editor-registry', 'kboard-editor-tiptap-runtime'), KBOARD_VERSION, true);
	wp_register_script('kboard-editor-editorjs-adapter', KBOARD_URL_PATH . '/assets/editor-scaffold/adapters/editorjs-adapter.js', array('kboard-editor-registry', 'kboard-editorjs-core', 'kboard-editorjs-header', 'kboard-editorjs-list', 'kboard-editorjs-quote', 'kboard-editorjs-image', 'kboard-editorjs-delimiter', 'kboard-editorjs-code', 'kboard-editorjs-table', 'kboard-editorjs-warning'), KBOARD_VERSION, true);
	wp_register_script('kboard-editor-bootstrap', KBOARD_URL_PATH . '/assets/editor-scaffold/kboard-editor-bootstrap.js', array('kboard-editor-registry'), KBOARD_VERSION, true);
	wp_register_script('kboard-editorjs-core', 'https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest', array(), 'latest', true);
	wp_register_script('kboard-editorjs-header', 'https://cdn.jsdelivr.net/npm/@editorjs/header@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-list', 'https://cdn.jsdelivr.net/npm/@editorjs/list@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-quote', 'https://cdn.jsdelivr.net/npm/@editorjs/quote@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-image', 'https://cdn.jsdelivr.net/npm/@editorjs/image@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-delimiter', 'https://cdn.jsdelivr.net/npm/@editorjs/delimiter@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-code', 'https://cdn.jsdelivr.net/npm/@editorjs/code@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-table', 'https://cdn.jsdelivr.net/npm/@editorjs/table@latest', array('kboard-editorjs-core'), 'latest', true);
	wp_register_script('kboard-editorjs-warning', 'https://cdn.jsdelivr.net/npm/@editorjs/warning@latest', array('kboard-editorjs-core'), 'latest', true);
	
	// jQuery Timepicker 등록
	wp_register_style('jquery-timepicker', KBOARD_URL_PATH . '/template/css/jquery.timepicker.css', array(), '1.3.5');
	wp_register_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array('jquery'), '1.3.5');
	
	// PG 등록
	wp_register_script('kboard-builtin-pg', KBOARD_URL_PATH . '/template/js/pg.js', array(), KBOARD_VERSION, true);
	
	// 우편번호 주소 검색
	wp_enqueue_script('daum-postcode', '//t1.daumcdn.net/mapjsapi/bundle/postcode/prod/postcode.v2.js', array(), NULL, true);
	
	// 필드 관련 스크립트 등록
	wp_register_script('kboard-field-date', KBOARD_URL_PATH . '/template/js/field-date.js', array('jquery'), KBOARD_VERSION, true);
	wp_register_script('kboard-field-time', KBOARD_URL_PATH . '/template/js/field-time.js', array('jquery'), KBOARD_VERSION, true);
	wp_register_script('kboard-field-address', KBOARD_URL_PATH . '/template/js/field-address.js', array('jquery', 'daum-postcode'), KBOARD_VERSION, true);
	
	// 설정 등록
	$localize = array(
		'version' => KBOARD_VERSION,
		'home_url' => home_url('/', 'relative'),
		'site_url' => site_url('/', 'relative'),
		'post_url' => admin_url('admin-post.php'),
		'ajax_url' => admin_url('admin-ajax.php'),
		'plugin_url' => KBOARD_URL_PATH,
		'media_group' => kboard_media_group(),
		'view_iframe' => kboard_view_iframe(),
		'locale' => get_locale(),
		'ajax_security' => wp_create_nonce('kboard_ajax_security'),
	);
	$kboard_iamport_id = get_option('kboard_iamport_id');
	if($kboard_iamport_id){
		$localize['iamport_id'] = $kboard_iamport_id;
	}
	wp_localize_script('kboard-script', 'kboard_settings', apply_filters('kboard_settings', $localize));
	
	// 번역 등록
	$localize = array(
		'kboard_add_media' => __('KBoard Add Media', 'kboard'),
		'next' => __('Next', 'kboard'),
		'prev' => __('Prev', 'kboard'),
		'required' => __('%s is required.', 'kboard'),
		'please_enter_the_title' => __('Please enter the title.', 'kboard'),
		'please_enter_the_author' => __('Please enter the author.', 'kboard'),
		'please_enter_the_password' => __('Please enter the password.', 'kboard'),
		'please_enter_the_CAPTCHA' => __('Please enter the CAPTCHA.', 'kboard'),
		'please_enter_the_name' => __('Please enter the name.', 'kboard'),
		'please_enter_the_email' => __('Please enter the email.', 'kboard'),
		'you_have_already_voted' => __('You have already voted.', 'kboard'),
		'please_wait' => __('Please wait.', 'kboard'),
		'newest' => __('Newest', 'kboard'),
		'best' => __('Best', 'kboard'),
		'updated' => __('Updated', 'kboard'),
		'viewed' => __('Viewed', 'kboard'),
		'yes' => __('Yes', 'kboard'),
		'no' => __('No', 'kboard'),
		'did_it_help' => __('Did it help?', 'kboard'),
		'hashtag' => __('Hashtag', 'kboard'),
		'tag' => __('Tag', 'kboard'),
		'add_a_tag' => __('Add a Tag', 'kboard'),
		'removing_tag' => __('Removing tag', 'kboard'),
		'changes_you_made_may_not_be_saved' => __('Changes you made may not be saved.', 'kboard'),
		'name' => __('Name', 'kboard'),
		'email' => __('Email', 'kboard'),
		'address' => __('Address', 'kboard'),
		'address_2' => __('Address 2', 'kboard'),
		'postcode' => __('Postcode', 'kboard'),
		'phone_number' => __('Phone number', 'kboard'),
		'mobile_phone' => __('Mobile phone', 'kboard'),
		'phone' => __('Phone', 'kboard'),
		'company_name' => __('Company name', 'kboard'),
		'vat_number' => __('VAT number', 'kboard'),
		'bank_account' => __('Bank account', 'kboard'),
		'name_of_deposit' => __('Name of deposit', 'kboard'),
		'find' => __('Find', 'kboard'),
		'rate' => __('Rate', 'kboard'),
		'ratings' => __('Ratings', 'kboard'),
		'waiting' => __('Waiting', 'kboard'),
		'complete' => __('Complete', 'kboard'),
		'question' => __('Question', 'kboard'),
		'answer' => __('Answer', 'kboard'),
		'notify_me_of_new_comments_via_email' => __('Notify me of new comments via email', 'kboard'),
		'ask_question' => __('Ask Question', 'kboard'),
		'categories' => __('Categories', 'kboard'),
		'pages' => __('Pages', 'kboard'),
		'all_products' => __('All Products', 'kboard'),
		'your_orders' => __('Your Orders', 'kboard'),
		'your_sales' => __('Your Sales', 'kboard'),
		'my_orders' => __('My Orders', 'kboard'),
		'my_sales' => __('My Sales', 'kboard'),
		'new_product' => __('New Product', 'kboard'),
		'edit_product' => __('Edit Product', 'kboard'),
		'delete_product' => __('Delete Product', 'kboard'),
		'seller' => __('Seller', 'kboard'),
		'period' => __('Period', 'kboard'),
		'period_of_use' => __('Period of use', 'kboard'),
		'last_updated' => __('Last updated', 'kboard'),
		'list_price' => __('List price', 'kboard'),
		'price' => __('Price', 'kboard'),
		'total_price' => __('Total price', 'kboard'),
		'amount' => __('Amount', 'kboard'),
		'quantity' => __('Quantity', 'kboard'),
		'use_points' => __('Use points', 'kboard'),
		'my_points' => __('My points', 'kboard'),
		'available_points' => __('Available points', 'kboard'),
		'apply_points' => __('Apply points', 'kboard'),
		'buy_it_now' => __('Buy It Now', 'kboard'),
		'sold_out' => __('Sold Out', 'kboard'),
		'for_free' => __('For free', 'kboard'),
		'pay_s' => __('Pay %s', 'kboard'),
		'payment_method' => __('Payment method', 'kboard'),
		'credit_card' => __('Credit card', 'kboard'),
		'make_a_deposit' => __('Make a deposit', 'kboard'),
		'reward_point' => __('Reward point', 'kboard'),
		'download_expiry' => __('Download expiry', 'kboard'),
		'checkout' => __('Checkout', 'kboard'),
		'buyer_information' => __('Buyer information', 'kboard'),
		'applying_cash_receipts' => __('Applying cash receipts', 'kboard'),
		'applying_cash_receipt' => __('Applying cash receipt', 'kboard'),
		'cash_receipt' => __('Cash receipt', 'kboard'),
		'privacy_policy' => __('Privacy policy', 'kboard'),
		'i_agree_to_the_privacy_policy' => __('I agree to the privacy policy.', 'kboard'),
		'i_confirm_the_terms_of_the_transaction_and_agree_to_the_payment_process' => __('I confirm the terms of the transaction and agree to the payment process.', 'kboard'),
		'today' => __('Today', 'kboard'),
		'yesterday' => __('Yesterday', 'kboard'),
		'this_month' => __('This month', 'kboard'),
		'last_month' => __('Last month', 'kboard'),
		'last_30_days' => __('Last 30 days', 'kboard'),
		'agree' => __('Agree', 'kboard'),
		'disagree' => __('Disagree', 'kboard'),
		'opinion' => __('Opinion', 'kboard'),
		'comment' => __('Comment', 'kboard'),
		'comments' => __('Comments', 'kboard'),
		'your_order_has_been_cancelled' => __('Your order has been cancelled.', 'kboard'),
		'order_information_has_been_changed' => __('Order information has been changed.', 'kboard'),
		'order_date' => __('Order date', 'kboard'),
		'point_payment' => __('Point payment', 'kboard'),
		'cancel_point_payment' => __('Cancel point payment', 'kboard'),
		'paypal' => __('PayPal', 'kboard'),
		'point' => __('Point', 'kboard'),
		'zipcode' => __('Zip Code', 'kboard'),
		'this_year' => __('This year', 'kboard'),
		'last_year' => __('Last year', 'kboard'),
		'period_total' => __('Period total', 'kboard'),
		'total_revenue' => __('Total revenue', 'kboard'),
		'terms_of_service' => __('Terms of service', 'kboard'),
		'i_agree_to_the_terms_of_service' => __('I agree to the terms of service.', 'kboard'),
		'your_shopping_cart_is_empty' => __('Your Shopping Cart Is Empty!', 'kboard'),
		'category' => __('Category', 'kboard'),
		'select' => __('Select', 'kboard'),
		'category_select' => __('Category select', 'kboard'),
		'information' => __('Information', 'kboard'),
		'telephone' => __('Telephone', 'kboard'),
		'items' => __('Items', 'kboard'),
		'total_amount' => __('Total amount', 'kboard'),
		'total_quantity' => __('Total quantity', 'kboard'),
		'make_payment' => __('Make Payment', 'kboard'),
		'add' => __('Add', 'kboard'),
		'close' => __('Close', 'kboard'),
	);
	wp_localize_script('kboard-script', 'kboard_localize_strings', apply_filters('kboard_localize_strings', $localize));
}
add_action('wp_enqueue_scripts', 'kboard_scripts', 999);
add_action('kboard_switch_to_blog', 'kboard_scripts');

function kboard_editor_tiptap_runtime_script_loader_tag($tag, $handle, $src){
	if($handle !== 'kboard-editor-tiptap-runtime'){
		return $tag;
	}

	if(stripos($tag, 'type=') !== false){
		return preg_replace('/type=("|\')([^"\']*)("|\')/i', 'type="module"', $tag, 1);
	}

	return str_replace('<script', '<script type="module"', $tag);
}
add_filter('script_loader_tag', 'kboard_editor_tiptap_runtime_script_loader_tag', 10, 3);

/**
 * 관리자 페이지 스타일 파일을 출력한다.
 */
function kboard_admin_style($hook_suffix){
	if($hook_suffix == 'kboard_page_kboard_content_list'){
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array(), KBOARD_VERSION);
	}
	wp_enqueue_script('kboard-cosmosfarm-apis', KBOARD_URL_PATH . '/pages/cosmosfarm-apis.js', array(), KBOARD_VERSION);
	wp_enqueue_style('kboard-admin', KBOARD_URL_PATH . '/pages/kboard-admin.css', array(), KBOARD_VERSION);
	
	wp_register_script('jquery-timepicker', KBOARD_URL_PATH . '/template/js/jquery.timepicker.js', array(), KBOARD_VERSION);
	wp_register_style('jquery-flick-style', KBOARD_URL_PATH.'/template/css/jquery-ui.css', array(), '1.12.1');
	wp_register_style('jquery-timepicker', KBOARD_URL_PATH.'/template/css/jquery.timepicker.css', array(), KBOARD_VERSION);
	wp_register_script('nested-sortable', KBOARD_URL_PATH . '/assets/nested-sortable/jquery.mjs.nestedSortable.js', array('jquery', 'jquery-ui-sortable'), '2.1a');
	wp_register_script('kboard-setting-script', KBOARD_URL_PATH . '/pages/kboard-setting-script.js', array(), KBOARD_VERSION, false);
}
add_action('admin_enqueue_scripts', 'kboard_admin_style', 999, 1);

/**
 * 읽기권한이 없어서 로그인 페이지로 이동한다.
 */
function kboard_cannot_read_document_go_login($action, $url, $content, $board, $builder){
	if($action == 'go_login'){
		echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");</script>';
		echo '<script>top.window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}
add_action('kboard_cannot_read_document', 'kboard_cannot_read_document_go_login', 10, 5);

/**
 * 읽기권한이 없어서 게시판 리스트로 돌아간다.
 */
function kboard_cannot_read_document_go_back($action, $url, $content, $board, $builder){
	if($action == 'go_back'){
		echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
		echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}
add_action('kboard_cannot_read_document', 'kboard_cannot_read_document_go_back', 10, 5);

/**
 * 포인트 부족으로 게시판 리스트로 돌아간다.
 */
function kboard_not_enough_points_read_document_go_back($action, $url, $content, $board, $builder){
	if($action == 'not_enough_points'){
		echo '<script>alert("'.__('You have not enough points.', 'kboard').'");</script>';
		echo '<script>window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}
add_action('kboard_cannot_read_document', 'kboard_not_enough_points_read_document_go_back', 10, 5);

/**
 * 첨부파일 다운로드 권한이 없어서 로그인 페이지로 이동한다.
 */
function kboard_cannot_download_file_go_login($action, $url, $content, $board, $comment){
	if($action == 'go_login'){
		echo '<script>alert("'.__('Please Log in to continue.', 'kboard').'");</script>';
		echo '<script>top.window.location.href="' . esc_url_raw($url) . '";</script>';
	}
}
add_action('kboard_cannot_download_file', 'kboard_cannot_download_file_go_login', 10, 5);

/**
 * 첨부파일 다운로드 권한이 없어서 이전 페이지로 돌아간다.
 */
function kboard_cannot_download_file_go_back($action, $url, $content, $board, $comment){
	if($action == 'go_back'){
		echo '<script>alert("'.__('You do not have permission.', 'kboard').'");</script>';
		echo '<script>history.go(-1);</script>';
	}
}
add_action('kboard_cannot_download_file', 'kboard_cannot_download_file_go_back', 10, 5);

/**
 * 첨부파일 다운로드 포인트 부족으로 이전 페이지로 돌아간다.
 */
function kboard_not_enough_points_download_file_go_back($action, $url, $content, $board, $comment){
	if($action == 'not_enough_points'){
		echo '<script>alert("'.__('You have not enough points.', 'kboard').'");</script>';
		echo '<script>history.go(-1);</script>';
	}
}
add_action('kboard_cannot_download_file', 'kboard_not_enough_points_download_file_go_back', 10, 5);

/**
 * 어드민바에 게시판 설정 페이지 링크를 추가한다.
 */
function kboard_add_toolbar_link($wp_admin_bar){
	global $post, $wpdb;
	if(!is_admin() && current_user_can('manage_kboard') && $post && $post->ID){
		$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='auto_page' AND `value`='{$post->ID}'");
		
		if(!$board_id){
			$board_id = $wpdb->get_var("SELECT `board_id` FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='latest_target_page' AND `value`='{$post->ID}'");
		}
		
		if($board_id){
			$args = array(
				'id'    => 'kboard-setting-page',
				'title' => __('KBoard Forum Settings', 'kboard'),
				'href'  => admin_url("admin.php?page=kboard_list&board_id={$board_id}"),
				'meta'  => array('class' => 'kboard-setting-page')
			);
			$wp_admin_bar->add_node($args);
		}
	}
}
add_action('admin_bar_menu', 'kboard_add_toolbar_link', 999);

/**
 * head 태그 사이에 커스텀 CSS 내용을 출력한다.
 */
function kboard_head(){
	wp_print_head_scripts();
	print_late_styles();
	
	$custom_css = get_option('kboard_custom_css');
	if($custom_css){
		echo "<style type=\"text/css\">{$custom_css}</style>";
	}
}
add_action('wp_head', 'kboard_head', 999);
add_action('kboard_iframe_head', 'kboard_head');

/**
 * 게시글 내용의 문단을 나눈다.
 */
function kboard_content_paragraph_breaks($content, $builder=''){
	$content = nl2br($content);
	$content = preg_replace("/(<(|\/)(table|thead|tfoot|tbody|th|tr|td|ul|ol|li|h1|h2|h3|h4|h5|h6|hr|p).*>)(<br \/>)/", "\$1", $content);
	return $content;
}
add_filter('kboard_content_paragraph_breaks', 'kboard_content_paragraph_breaks', 10, 2);

/**
 * 우커머스 상품 탭에 게시판을 표시한다.
 */
function kboard_woocommerce_product_tabs_add($tabs){
	$board_list = new KBoardList();
	foreach($board_list->getWoocommerceProductTabsAdd() as $list){
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['title'] = $list->board_name;
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['priority'] = $list->priority;
		$tabs['kboard_woocommerce_product_tabs_' . $list->board_id]['callback'] = function() use ($list){
			global $product;
			
			echo '<h2>' . esc_html($list->board_name) . '</h2>';
			
			$board_id = $list->board_id;
			$iframe_id = uniqid();
			$product_id = $product->get_id();
			
			$url = new KBUrl();
			$_SESSION['kboard_board_id'] = $board_id;
			
			echo '<iframe id="kboard-iframe-' . $iframe_id . '" class="kboard-iframe kboard-iframe-' . $board_id . '" src="' . $url->set('kboard_id', $board_id)->set('category1', $product_id)->set('iframe_id', $iframe_id)->set('woocommerce_product_tabs_inside', $product_id)->toString() . '" style="width:100%" scrolling="no" frameborder="0"></iframe>';
		};
	}
	return $tabs;
}
add_filter('woocommerce_product_tabs', 'kboard_woocommerce_product_tabs_add');

/**
 * KBoad 시스템 업데이트
 */
function kboard_update_check(){
	// 시스템 업데이트를 이미 진행 했다면 중단한다.
	if(version_compare(KBOARD_VERSION, get_option('kboard_version'), '<=')) return;
	
	// 시스템 업데이트를 확인하기 위해서 버전 등록
	if(get_option('kboard_version') !== false){
		update_option('kboard_version', KBOARD_VERSION);
		update_option('kboard_svg_batch_scan_pending', KBOARD_VERSION, 'no');
		delete_option('kboard_svg_batch_scan_result');
		delete_option('kboard_svg_batch_scan_running');
		delete_option('kboard_svg_batch_restore_manifest');
		delete_option('kboard_svg_batch_restore_result');
		
		// 관리자 알림 시작
		include_once KBOARD_DIR_PATH . '/class/KBAdminNotices.class.php';
		KBAdminNotices::kboard_updated_notice();
	}
	else{
		add_option('kboard_version', KBOARD_VERSION, null, 'no');
	}
	
	kboard_activation_execute();
}
add_action('plugins_loaded', 'kboard_update_check');

/**
 * KBoad 활성화
 */
function kboard_activation($networkwide){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		if($networkwide){
			$old_blog = $wpdb->blogid;
			$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
			foreach($blogids as $blog_id){
				switch_to_blog($blog_id);
				kboard_activation_execute();
			}
			switch_to_blog($old_blog);
			return;
		}
	}
	kboard_activation_execute();
}
register_activation_hook(__FILE__, 'kboard_activation');

/**
 * KBoard 활성화 실행
 */
function kboard_activation_execute(){
	global $wpdb;
	
	/*
	 * KBoard 2.5
	 * table 이름에 prefix 추가
	 */
	$tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
	foreach($tables as $table){
		$table = $table[0];
		$prefix = substr($table, 0, 7);
		if($prefix == 'kboard_') $wpdb->query("RENAME TABLE `{$table}` TO `{$wpdb->prefix}{$table}`");
	}
	unset($tables, $table, $prefix);
	
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	$charset_collate = $wpdb->get_charset_collate();
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_setting` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`board_name` varchar(127) NOT NULL,
	`skin` varchar(127) NOT NULL,
	`use_comment` varchar(5) NOT NULL,
	`use_editor` varchar(16) NOT NULL,
	`permission_read` varchar(127) NOT NULL,
	`permission_write` varchar(127) NOT NULL,
	`admin_user` text NOT NULL,
	`use_category` varchar(5) NOT NULL,
	`category1_list` text NOT NULL,
	`category2_list` text NOT NULL,
	`category3_list` text NOT NULL,
	`category4_list` text NOT NULL,
	`category5_list` text NOT NULL,
	`page_rpp` int(10) unsigned NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `skin` (`skin`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_attached` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`content_uid` bigint(20) unsigned NOT NULL,
	`comment_uid` bigint(20) unsigned NOT NULL,
	`file_key` varchar(127) NOT NULL,
	`date` char(14) NOT NULL,
	`file_path` varchar(255) NOT NULL,
	`file_name` varchar(127) NOT NULL,
	`file_size` bigint(20) unsigned NOT NULL,
	`download_count` int(10) unsigned NOT NULL,
	`metadata` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `content_uid` (`content_uid`),
	KEY `comment_uid` (`comment_uid`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_content` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`board_id` bigint(20) unsigned NOT NULL,
	`parent_uid` bigint(20) unsigned NOT NULL,
	`member_uid` bigint(20) unsigned NOT NULL,
	`member_display` varchar(127) NOT NULL,
	`title` varchar(127) NOT NULL,
	`content` longtext NOT NULL,
	`date` char(14) NOT NULL,
	`update` char(14) NOT NULL,
	`view` int(10) unsigned NOT NULL,
	`comment` int(10) unsigned NOT NULL,
	`like` int(10) unsigned NOT NULL,
	`unlike` int(10) unsigned NOT NULL,
	`vote` int(11) NOT NULL,
	`thumbnail_file` varchar(127) NOT NULL,
	`thumbnail_name` varchar(127) NOT NULL,
	`category1` varchar(127) NOT NULL,
	`category2` varchar(127) NOT NULL,
	`category3` varchar(127) NOT NULL,
	`category4` varchar(127) NOT NULL,
	`category5` varchar(127) NOT NULL,
	`secret` varchar(5) NOT NULL,
	`notice` varchar(5) NOT NULL,
	`notice_expired_date` char(14) NULL,
	`search` char(1) NOT NULL,
	`status` varchar(20) NOT NULL,
	`password` varchar(127) NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `board_id` (`board_id`),
	KEY `parent_uid` (`parent_uid`),
	KEY `member_uid` (`member_uid`),
	KEY `date` (`date`),
	KEY `update` (`update`),
	KEY `view` (`view`),
	KEY `vote` (`vote`),
	KEY `category1` (`category1`),
	KEY `category2` (`category2`),
	KEY `category3` (`category3`),
	KEY `category4` (`category4`),
	KEY `category5` (`category5`),
	KEY `notice` (`notice`),
	KEY `status` (`status`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_option` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`content_uid` bigint(20) unsigned NOT NULL,
	`option_key` varchar(127) NOT NULL,
	`option_value` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `content_uid` (`content_uid`),
	KEY `option_key` (`option_key`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_meta` (
	`board_id` bigint(20) unsigned NOT NULL,
	`key` varchar(127) NOT NULL,
	`value` longtext NOT NULL,
	UNIQUE KEY `meta_index` (`board_id`,`key`),
	KEY `key` (`key`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_latestview` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(127) NOT NULL,
	`skin` varchar(127) NOT NULL,
	`rpp` int(10) unsigned NOT NULL,
	`mobile_rpp` int(10) unsigned NOT NULL,
	`sort` varchar(20) NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `skin` (`skin`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_board_latestview_link` (
	`latestview_uid` bigint(20) unsigned NOT NULL,
	`board_id` bigint(20) unsigned NOT NULL,
	UNIQUE KEY `latestview_uid` (`latestview_uid`,`board_id`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_meida` (
	`uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`media_group` varchar(127) DEFAULT NULL,
	`date` char(14) DEFAULT NULL,
	`file_path` varchar(127) DEFAULT NULL,
	`file_name` varchar(127) DEFAULT NULL,
	`file_size` bigint(20) unsigned NOT NULL,
	`download_count` int(10) unsigned NOT NULL,
	`metadata` longtext NOT NULL,
	PRIMARY KEY (`uid`),
	KEY `media_group` (`media_group`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_meida_relationships` (
	`content_uid` bigint(20) unsigned NOT NULL,
	`comment_uid` bigint(20) unsigned NOT NULL,
	`media_uid` bigint(20) unsigned NOT NULL,
	KEY `content_uid_2` (`content_uid`),
	KEY `comment_uid` (`comment_uid`),
	KEY `media_uid` (`media_uid`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_order_item` (
	`order_item_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`order_id` bigint(20) unsigned NOT NULL,
	PRIMARY KEY (`order_item_id`),
	KEY `order_id` (`order_id`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_order_item_meta` (
	`order_item_meta_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`order_item_id` bigint(20) unsigned NOT NULL,
	`meta_key` varchar(127) DEFAULT NULL,
	`meta_value` longtext,
	PRIMARY KEY (`order_item_meta_id`),
	UNIQUE KEY `order_item_id` (`order_item_id`,`meta_key`),
	KEY `meta_key` (`meta_key`)
	) {$charset_collate};");
	
	dbDelta("CREATE TABLE `{$wpdb->prefix}kboard_vote` (
	`vote_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
	`target_uid` bigint(20) unsigned NOT NULL,
	`target_type` varchar(20) NOT NULL,
	`target_vote` varchar(20) NOT NULL,
	`user_id` bigint(20) unsigned NOT NULL,
	`ip_address` varchar(127) NOT NULL,
	`created` char(14) NOT NULL,
	PRIMARY KEY (`vote_id`),
	KEY `target_uid` (`target_uid`),
	KEY `user_id` (`user_id`)
	) {$charset_collate};");

	// kboard_search_document 테이블 생성 (FULLTEXT 검색용 그림자 테이블)
	$search_doc_table = "{$wpdb->prefix}kboard_search_document";
	$search_doc_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $search_doc_table));
	$ngram_clause = '';
	$ngram_check = $wpdb->get_var("SELECT @@ngram_token_size");
	$ngram_supported = ($ngram_check !== null && $ngram_check !== false);
	if($ngram_supported){
		$ngram_clause = ' WITH PARSER ngram';
	}

	if(!$search_doc_exists){
		$wpdb->query("CREATE TABLE `{$search_doc_table}` (
		`content_uid` bigint(20) unsigned NOT NULL,
		`board_id` bigint(20) unsigned NOT NULL,
		`member_display` varchar(127) NOT NULL,
		`title_plain` text NOT NULL,
		`content_plain` longtext NOT NULL,
		`option_plain` text NOT NULL,
		PRIMARY KEY (`content_uid`),
		KEY `board_id` (`board_id`),
		FULLTEXT INDEX `ft_search` (`title_plain`, `content_plain`, `option_plain`){$ngram_clause},
		FULLTEXT INDEX `ft_member` (`member_display`){$ngram_clause}
		) ENGINE=InnoDB {$charset_collate}");
	}
	else if($ngram_supported){
		// 기존 테이블의 FULLTEXT 인덱스에 ngram 파서가 없으면 재생성
		$create_sql = $wpdb->get_var("SHOW CREATE TABLE `{$search_doc_table}`", 1);
		if($create_sql && strpos($create_sql, 'ngram') === false){
			$wpdb->query("ALTER TABLE `{$search_doc_table}` DROP INDEX `ft_search`, ADD FULLTEXT INDEX `ft_search` (`title_plain`, `content_plain`, `option_plain`) WITH PARSER ngram");
			$wpdb->query("ALTER TABLE `{$search_doc_table}` DROP INDEX `ft_member`, ADD FULLTEXT INDEX `ft_member` (`member_display`) WITH PARSER ngram");
		}
	}
	
	/*
	 * KBoard 2.9
	 * kboard_board_meta 테이블의 value 컬럼 데이터형 text로 변경
	 */
	list($name, $type) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_meta` `value`", ARRAY_N);
	if(stristr($type, 'varchar')){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_meta` CHANGE `value` `value` TEXT NOT NULL");
	}
	unset($name, $type);
	
	/*
	 * KBoard 3.5
	 * kboard_board_content 테이블에 search 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `search`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `search` char(1) DEFAULT NULL AFTER `notice`");
	}
	unset($name);
	
	/*
	 * KBoard 4.1
	 * kboard_board_content 테이블에 comment 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `comment`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `comment` int(10) unsigned DEFAULT NULL AFTER `view`");
	}
	if(defined('KBOARD_COMMNETS_VERSION')){
		// comment 컬럼에 댓글 입력 숫자를 등록한다.
		$contents = $wpdb->get_results("SELECT `uid` FROM `{$wpdb->prefix}kboard_board_content` WHERE 1");
		foreach($contents as $content){
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE `content_uid`='".intval($content->uid)."'");
			$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `comment`='$count' WHERE `uid`='".intval($content->uid)."'");
		}
	}
	unset($name, $count);
	
	/*
	 * KBoard 4.2
	 * kboard_board_content 테이블에 parent_uid 컬럼 생성 확인
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `parent_uid`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `parent_uid` BIGINT UNSIGNED NOT NULL AFTER `board_id`");
	}
	unset($name);
	
	/*
	 * KBoard 4.5
	 * kboard_board_meta 테이블의 content 컬럼 데이터형 longtext로 변경
	 */
	list($name, $type) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `content`", ARRAY_N);
	if(stristr($type, 'text')){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` CHANGE `content` `content` LONGTEXT NOT NULL");
	}
	unset($name, $type);
	
	/*
	 * KBoard 4.5
	 * kboard_board_content 테이블에 like 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `like`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `like` int(10) unsigned DEFAULT NULL AFTER `comment`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_option 테이블에 content_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_option` WHERE `Key_name`='content_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_option` ADD UNIQUE (`content_uid`, `option_key`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 unlike 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `unlike`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `unlike` int(10) UNSIGNED NULL AFTER `like`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 vote 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `vote`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `vote` int(11) NULL AFTER `unlike`");
	}
	unset($name);
	
	/*
	 * KBoard 5.1
	 * kboard_board_content 테이블에 parent_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='parent_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`parent_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.1
	 * kboard_board_attached 테이블에 content_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_attached` WHERE `Key_name`='content_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_attached` ADD INDEX (`content_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.2
	 * kboard_board_content 테이블에 update 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `update`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `update` char(14) NULL AFTER `date`");
		$wpdb->query("UPDATE `{$wpdb->prefix}kboard_board_content` SET `update`=`date` WHERE 1");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 status 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `status`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `status` varchar(20) DEFAULT NULL AFTER `search`");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 member_uid 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='member_uid'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`member_uid`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 date 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='date'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`date`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 update 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='update'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`update`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 view 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='view'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`view`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 vote 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='vote'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`vote`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 category1 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='category1'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`category1`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 category2 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='category2'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`category2`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 notice 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='notice'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`notice`)");
	}
	unset($index);
	
	/*
	 * KBoard 5.3
	 * kboard_board_content 테이블에 status 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_content` WHERE `Key_name`='status'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD INDEX (`status`)");
	}
	unset($index);
	
	/*
	 * KBoard 6.9
	 * kboard_board_content 테이블에 notice_expired_date 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_content` `notice_expired_date`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_content` ADD `notice_expired_date` char(14) NULL AFTER `notice`");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_latestview 테이블에 sort 컬럼 추가
	 */
	list($name) = $wpdb->get_row("DESCRIBE `{$wpdb->prefix}kboard_board_latestview` `sort`", ARRAY_N);
	if(!$name){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_latestview` ADD `sort` varchar(20) NOT NULL AFTER `rpp`");
	}
	unset($name);
	
	/*
	 * KBoard 5.3
	 * kboard_board_meta 테이블에서 게시판의 total, list_total 데이터를 지움으로써 초기화한다.
	 */
	$wpdb->query("DELETE FROM `{$wpdb->prefix}kboard_board_meta` WHERE `key`='total' OR `key`='list_total'");
	
	/*
	 * KBoard 5.3.11
	 * kboard_meida_relationships 테이블에 content_uid 인덱스 삭제
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_meida_relationships` WHERE `Key_name`='content_uid'");
	if(count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_meida_relationships` DROP INDEX `content_uid`");
	}
	unset($index);
	
	/*
	 * KBoard 6.5
	 * kboard_board_meta 테이블에 key 컬럼 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_meta` WHERE `Key_name`='key'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_meta` ADD INDEX `key` (`key`)");
	}
	unset($index);
	
	/*
	 * KBoard 6.5
	 * kboard_board_latestview 테이블에 skin 컬럼 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_latestview` WHERE `Key_name`='skin'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_latestview` ADD INDEX `skin` (`skin`)");
	}
	unset($index);
	
	/*
	 * KBoard 6.5
	 * kboard_board_setting 테이블에 skin 컬럼 인덱스 추가
	 */
	$index = $wpdb->get_results("SHOW INDEX FROM `{$wpdb->prefix}kboard_board_setting` WHERE `Key_name`='skin'");
	if(!count($index)){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_setting` ADD INDEX `skin` (`skin`)");
	}
	unset($index);

	/*
	 * KBoard 6.6
	 * kboard_board_setting 테이블의 use_editor 컬럼 길이 확장
	 */
	$column = $wpdb->get_row("SHOW COLUMNS FROM `{$wpdb->prefix}kboard_board_setting` LIKE 'use_editor'");
	if(isset($column->Type) && preg_match('/varchar\((\d+)\)/i', $column->Type, $matches) && intval($matches[1]) < 16){
		$wpdb->query("ALTER TABLE `{$wpdb->prefix}kboard_board_setting` MODIFY `use_editor` varchar(16) NOT NULL");
	}
	unset($column, $matches);



	if(get_option('kboard_use_search_index') === false){
		add_option('kboard_use_search_index', '0', null, 'no');
	}
	
	// 관리자에게 manage_kboard 권한 추가
	$admin_role = get_role('administrator');
	if(!$admin_role->has_cap('manage_kboard')){
		$admin_role->add_cap('manage_kboard', true);
	}
}

/**
 * KBoard 비활성화
 */
function kboard_deactivation($networkwide){
	
}
register_deactivation_hook(__FILE__, 'kboard_deactivation');

/**
 * KBoard 언인스톨
 */
function kboard_uninstall(){
	global $wpdb;
	if(function_exists('is_multisite') && is_multisite()){
		$old_blog = $wpdb->blogid;
		$blogids = $wpdb->get_col("SELECT `blog_id` FROM {$wpdb->blogs}");
		foreach($blogids as $blog_id){
			switch_to_blog($blog_id);
			kboard_uninstall_execute();
		}
		switch_to_blog($old_blog);
		return;
	}
	kboard_uninstall_execute();
}
register_uninstall_hook(__FILE__, 'kboard_uninstall');

/**
 * KBoard 언인스톨 실행
 */
function kboard_uninstall_execute(){
	global $wpdb;
	$wpdb->query("DROP TABLE
	`{$wpdb->prefix}kboard_board_attached`,
	`{$wpdb->prefix}kboard_board_content`,
	`{$wpdb->prefix}kboard_board_option`,
	`{$wpdb->prefix}kboard_board_setting`,
	`{$wpdb->prefix}kboard_board_meta`,
	`{$wpdb->prefix}kboard_board_latestview`,
	`{$wpdb->prefix}kboard_board_latestview_link`,
	`{$wpdb->prefix}kboard_meida`,
	`{$wpdb->prefix}kboard_meida_relationships`,
	`{$wpdb->prefix}kboard_order_item`,
	`{$wpdb->prefix}kboard_order_item_meta`,
	`{$wpdb->prefix}kboard_vote`,
	`{$wpdb->prefix}kboard_search_document`
	");
}
