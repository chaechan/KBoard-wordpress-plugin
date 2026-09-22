<?php
if(!defined('ABSPATH')) exit;
/**
 * KBoard 워드프레스 게시판 사용자 함수
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */

/**
 * 배열에서 허가된 데이터만 남긴다.
 * @param array $array
 * @param array $whitelist
 * @return array
 */
function kboard_array_filter($array, $whitelist){
	if(function_exists('array_intersect_key')){
		return array_intersect_key($array, array_flip($whitelist));
	}
	foreach($array as $key=>$value){
		if(!in_array($key, $whitelist)) unset($array[$key]);
	}
	return $array;
}

/**
 * KBoard 날짜 값을 Unix timestamp로 변환한다.
 *
 * KBoard는 게시글 날짜를 YmdHis 형식으로 저장하지만 strtotime()은 이
 * 형식을 모든 PHP 버전에서 안정적으로 해석하지 못한다. 저장 형식을 먼저
 * 명시적인 날짜 문자열로 변환한 뒤 파싱해 잘못된 값이 Unix epoch로
 * 대체되지 않도록 한다.
 *
 * @param string $date
 * @param string $timezone
 * @return int|false
 */
function kboard_date_to_timestamp($date, $timezone=''){
	$date = trim((string)$date);
	if(!$date) return false;

	if(preg_match('/^\d{14}$/', $date)){
		$date = substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6, 2) . ' ' . substr($date, 8, 2) . ':' . substr($date, 10, 2) . ':' . substr($date, 12, 2);
	}

	if($timezone) $date = $timezone . ' ' . $date;
	$timestamp = strtotime($date);
	return $timestamp === false ? false : $timestamp;
}

/**
 * KBoard 날짜 값을 지정한 형식으로 출력한다.
 *
 * @param string $date
 * @param string $format
 * @return string
 */
function kboard_date_format($date, $format){
	$timestamp = kboard_date_to_timestamp($date);
	return $timestamp === false ? '' : date($format, $timestamp);
}

/**
 * JSON 인코더
 * @param array $val
 * @return string
 */
function kboard_json_encode($val){
	/*
	 * http://kr1.php.net/json_encode#113219
	 */
	if(function_exists('json_encode')){
		return json_encode($val);
	}
	
	if(is_string($val)) return '"'.addslashes($val).'"';
	if(is_numeric($val)) return $val;
	if($val === null) return 'null';
	if($val === true) return 'true';
	if($val === false) return 'false';
	
	$assoc = false;
	$i = 0;
	foreach($val as $k=>$v){
		if($k !== $i++){
			$assoc = true;
			break;
		}
	}
	$res = array();
	foreach($val as $k=>$v){
		$v = kboard_json_encode($v);
		if($assoc){
			$k = '"'.addslashes($k).'"';
			$v = $k.':'.$v;
		}
		$res[] = $v;
	}
	$res = implode(',', $res);
	
	return ($assoc)? '{'.$res.'}' : '['.$res.']';
}

/**
 * 파일의 MIME Content-type을 반환한다.
 * @param string $file
 * @return string
 */
function kboard_mime_type($file){
	/*
	 * http://php.net/manual/en/function.mime-content-type.php#87856
	 */
	$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
				
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			'webp' => 'image/webp',
			'avif' => 'image/avif',
				
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			'7z' => 'application/x-7z-compressed',
				
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
				
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
				
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
			'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
			'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
			'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
			'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
				
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
				
			// etc
			'hwp' => 'application/hangul',
	);

	$mime_type = '';
	$temp = basename($file);
	$temp = explode('.', $temp);
	$temp = array_pop($temp);
	$ext = strtolower($temp);

	if(isset($mime_types[$ext])){
		$mime_type = $mime_types[$ext];
	}
	else if(function_exists('mime_content_type') && file_exists($file)){
		$mime_type = mime_content_type($file);
	}
	else if(function_exists('finfo_open') && file_exists($file)){
		$finfo = finfo_open(FILEINFO_MIME);
		$mime_type = finfo_file($finfo, $file);
		finfo_close($finfo);
	}

	if($mime_type){
		return $mime_type;
	}
	return 'application/octet-stream';
}

/**
 * 권한을 한글로 출력한다.
 * @param string $permission
 * @return string
 */
function kboard_permission($permission){
	if($permission == 'all'){
		return __('제한없음', 'kboard');
	}
	else if($permission == 'author'){
		return __('로그인 사용자', 'kboard');
	}
	else if($permission == 'editor'){
		return __('선택된 관리자', 'kboard');
	}
	else if($permission == 'administrator'){
		return __('최고관리자', 'kboard');
	}
	else if($permission == 'comment_owner'){
		return __('본인의 댓글만 보기', 'kboard');
	}
	else if($permission == 'roles'){
		return __('직접선택', 'kboard');
	}
	else{
		return $permission;
	}
}

/**
 * Captcha 이미지를 생성하고 이미지 주소를 반환한다.
 * @return string
 */
function kboard_captcha(){
	include_once KBOARD_DIR_PATH . '/class/KBCaptcha.class.php';
	$captcha = new KBCaptcha();
	return $captcha->createImage();
}

/**
 * 이미지 사이즈를 조절한다.
 * @param string $image_src
 * @param int $width
 * @param int $height
 * @return string
 */
function kboard_resize($image_src, $width, $height){
	$upload_dir = wp_upload_dir();
	$basedir = str_replace(ABSPATH, '', $upload_dir['basedir']);
	$dirname = dirname($image_src);
	$dirname = explode("/{$basedir}", $dirname);
	$resize_dir = end($dirname);
	
	$basename = basename($image_src);
	$fileinfo = pathinfo($basename);
	$resize_name = basename($image_src, '.'.$fileinfo['extension']) . "-{$width}x{$height}.{$fileinfo['extension']}";
	
	$new_image = $upload_dir['basedir'] . "{$resize_dir}/{$resize_name}";
	$new_image_src = $upload_dir['baseurl'] . "{$resize_dir}/{$resize_name}";
	
	if(file_exists($new_image)){
		return $new_image_src;
	}
	
	$image_editor = wp_get_image_editor($upload_dir['basedir'] . "{$resize_dir}/{$basename}");
	if(!is_wp_error($image_editor)){
		$image_editor->resize($width, $height, true);
		$image_editor->save($new_image);
		return $new_image_src;
	}
	else{
		return site_url($image_src);
	}
}

/**
 * 리사이즈 이미지를 지운다.
 * @param string $image_src
 */
function kbaord_delete_resize($image_src){
	if(file_exists($image_src)){
		$size = getimagesize($image_src);
		if($size){
			$fileinfo = pathinfo($image_src);
			$original_name = basename($image_src, '.'.$fileinfo['extension']).'-';
			$dir = dirname($image_src);
			if($dh = @opendir($dir)){
				while(($file = readdir($dh)) !== false){
					if($file == "." || $file == "..") continue;
					if(strpos($file, $original_name) !== false){
						@unlink($dir . '/' . $file);
					}
				}
			}
			closedir($dh);
		}
	}
}

/**
 * 새글 알림 시간을 반환한다.
 * @return int
 */
function kboard_new_document_notify_time(){
	return get_option('kboard_new_document_notify_time', '86400');
}

/**
 * 업로드 가능한 파일 크기를 반환한다.
 */
function kboard_limit_file_size(){
	return intval(get_option('kboard_limit_file_size', kboard_upload_max_size()));
}

/**
 * 서버에 설정된 최대 업로드 크기를 반환한다.
 * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
 * @return int
 */
function kboard_upload_max_size(){
	static $max_size = -1;
	if($max_size < 0){
		$max_size = kboard_parse_size(ini_get('post_max_size'));
		$upload_max = kboard_parse_size(ini_get('upload_max_filesize'));
		if($upload_max > 0 && $upload_max < $max_size){
			$max_size = $upload_max;
		}
	}
	return $max_size;
}

/**
 * 바이트로 크기를 변환한다.
 * @link http://stackoverflow.com/questions/13076480/php-get-actual-maximum-upload-size
 * @return int
 */
function kboard_parse_size($size){
	$unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
	$size = preg_replace('/[^0-9\.]/', '', $size);
	if($unit){
		return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
	}
	else{
		return round($size);
	}
}

/**
 * 허가된 첨부파일 확장자를 반환한다.
 * @param boolean $to_array
 */
function kboard_allow_file_extensions($to_array=false){
	$file_extensions = get_option('kboard_allow_file_extensions', 'jpg, jpeg, gif, png, bmp, pjp, pjpeg, jfif, svg, webp, avif, ico, zip, 7z, hwp, ppt, xls, doc, txt, pdf, xlsx, pptx, docx, torrent, smi, mp4, mp3');
	$file_extensions = trim($file_extensions);
	
	if($to_array){
		$file_extensions = explode(',', $file_extensions);
		return array_map('trim', $file_extensions);
	}
	return $file_extensions;
}

/**
 * 작성자 금지단어를 반환한다.
 * @param string $to_array
 */
function kboard_name_filter($to_array=false){
	$name_filter = get_option('kboard_name_filter', '관리자, 운영자, admin, administrator');
	
	if($to_array){
		$name_filter = explode(',', $name_filter);
		return array_map('trim', $name_filter);
	}
	return $name_filter;
}

/**
 * 본문/제목/댓글 금지단어를 반환한다.
 * @param string $to_array
 */
function kboard_content_filter($to_array=false){
	$content_filter = get_option('kboard_content_filter', '');
	
	if($to_array){
		$content_filter = explode(',', $content_filter);
		return array_map('trim', $content_filter);
	}
	return $content_filter;
}

/**
 * 사용중인 스킨을 등록한다.
 * @param string $skin_name
 * @param string $current_name
 */
function kboard_register_current_skin($skin_name, $current_name){
	if(!isset($GLOBALS['KBOARD']['skin_dir']) || !is_array($GLOBALS['KBOARD']['skin_dir'])){
		$GLOBALS['KBOARD']['skin_dir'] = array();
	}
	if(!isset($GLOBALS['KBOARD']['skin_dir'][$skin_name]) || !is_array($GLOBALS['KBOARD']['skin_dir'][$skin_name])){
		$GLOBALS['KBOARD']['skin_dir'][$skin_name] = array();
	}
	$GLOBALS['KBOARD']['skin_dir'][$skin_name][$current_name] = $current_name;
}

/**
 * 등록된 스킨이 맞는지 체크한다.
 * @param string $skin_name
 * @param string $current_name
 * @return boolean
 */
function kboard_check_current_skin($skin_name, $current_name){
	if(!isset($GLOBALS['KBOARD']['skin_dir']) || !is_array($GLOBALS['KBOARD']['skin_dir'])){
		return false;
	}
	if(!isset($GLOBALS['KBOARD']['skin_dir'][$skin_name]) || !is_array($GLOBALS['KBOARD']['skin_dir'][$skin_name])){
		return false;
	}
	if(!isset($GLOBALS['KBOARD']['skin_dir'][$skin_name][$current_name]) || !$GLOBALS['KBOARD']['skin_dir'][$skin_name][$current_name]){
		return false;
	}
	if($GLOBALS['KBOARD']['skin_dir'][$skin_name][$current_name] != $current_name){
		return false;
	}
	return true;
}

/**
 * 구글 reCAPTCHA 사용 여부를 체크한다.
 * @return boolean
 */
function kboard_use_recaptcha(){
	static $use_recaptcha;
	if($use_recaptcha === null){
		$site_key = get_option('kboard_recaptcha_site_key');
		$secret_key = get_option('kboard_recaptcha_secret_key');
		
		if(kboard_recaptcha_type() != 'disabled' && $site_key && $secret_key){
			$use_recaptcha = true;
		}
		else{
			$use_recaptcha = false;
		}
	}
	return $use_recaptcha;
}

/**
 * 구글 reCAPTCHA 타입(v2/v3/disabled)을 반환한다.
 * @return string
 */
function kboard_recaptcha_type(){
	static $recaptcha_type;
	if($recaptcha_type === null){
		$recaptcha_type = sanitize_key(get_option('kboard_recaptcha_type', ''));
		if(!in_array($recaptcha_type, array('v2', 'v3'), true)){
			$recaptcha_type = 'disabled';
		}
	}
	return $recaptcha_type;
}

/**
 * 구글 reCAPTCHA v3 action을 반환한다.
 * @return string
 */
function kboard_recaptcha_v3_action(){
	return 'kboard_submit';
}

/**
 * 구글 reCAPTCHA의 Site key를 반환한다.
 * @return string
 */
function kboard_recaptcha_site_key(){
	static $recaptcha_site_key;
	if($recaptcha_site_key === null){
		$recaptcha_site_key = get_option('kboard_recaptcha_site_key', '');
		
		if(kboard_recaptcha_type() != 'disabled' && $recaptcha_site_key){
			if(kboard_recaptcha_type() == 'v3'){
				wp_enqueue_script('recaptcha-v3', add_query_arg('render', $recaptcha_site_key, 'https://www.google.com/recaptcha/api.js'), array(), null, true);
			}
			else{
				wp_enqueue_script('recaptcha');
			}
		}
	}
	return $recaptcha_site_key;
}

/**
 * 구글 reCAPTCHA의 Secret key를 반환한다.
 * @return string
 */
function kboard_recaptcha_secret_key(){
	static $recaptcha_secret_key;
	if($recaptcha_secret_key === null){
		$recaptcha_secret_key = get_option('kboard_recaptcha_secret_key');
	}
	return $recaptcha_secret_key;
}

/**
 * 현재 사용자의 역할을 반환한다.
 * @param int $user_id
 * @return array
 */
function kboard_current_user_roles($user_id=''){
	$roles = array();
	if(is_user_logged_in() || $user_id){
		
		if($user_id){
			$user = get_userdata($user_id);
		}
		else{
			$user = wp_get_current_user();
		}
		
		if($user->roles){
			$roles = (array) $user->roles;
		}
		else{
			$user = new WP_User($user->ID, '', get_current_blog_id());
			if($user->roles){
				$roles = (array) $user->roles;
			}
		}
	}
	return apply_filters('kboard_current_user_roles', $roles);
}

/**
 * 사용자 IP 주소를 반환한다.
 * @return string
 */
function kboard_user_ip(){
	static $ip;
	if($ip === null){
		$ip = '';
		$candidates = array();
		if(!empty($_SERVER['HTTP_CLIENT_IP'])){
			$candidates[] = $_SERVER['HTTP_CLIENT_IP'];
		}
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			$candidates = array_merge($candidates, explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
		}
		if(!empty($_SERVER['REMOTE_ADDR'])){
			$candidates[] = $_SERVER['REMOTE_ADDR'];
		}

		foreach($candidates as $candidate){
			$candidate = trim($candidate);
			if(filter_var($candidate, FILTER_VALIDATE_IP)){
				$ip = $candidate;
				break;
			}
		}
	}
	return apply_filters('kboard_user_ip', $ip);
}

/**
 * 배열 원소들을 정수형으로 변환한다.
 * @param array $array
 * @return array
 */
function kboard_array2int($array){
	foreach($array as &$item){
		$item = (int)$item;
	}
	return $array;
}

/**
 * KBMail 인스턴스를 반환한다.
 * @return KBMail
 */
function kboard_mail(){
	if(!class_exists('KBMail')){
		include_once KBOARD_DIR_PATH . '/class/KBMail.class.php';
	}
	return new KBMail();
}

/**
 * KBUrl 인스턴스를 반환한다.
 * @return KBUrl
 */
function kboard_url(){
	return new KBUrl();
}

/**
 * 아임포트 클래스를 반환한다.
 */
function kboard_iamport(){
	if(!class_exists('KBIamport')){
		include_once KBOARD_DIR_PATH . '/class/KBIamport.class.php';
	}
	$iamport = new KBIamport();
	$iamport->imp_id = get_option('kboard_iamport_id');
	$iamport->imp_key = get_option('kboard_iamport_api_key');
	$iamport->imp_secret = get_option('kboard_iamport_api_secret');
	return $iamport;
}

/**
 * 사용 가능한 PG 결제 수단을 반환한다.
 * @return array
 */
function kboard_builtin_pg_list(){
	/* 예제
	$builtin_pg_list = array(
		'name' => array(
			'admin_display' => '[PG이름] 신용카드',
			'front_display' => '신용카드',
			'class_name' => 'Class_Name',
		),
	);
	*/
	$builtin_pg_list = array();
	return apply_filters('kboard_builtin_pg_list', $builtin_pg_list);
}

/**
 * 활성화된 PG 결제 수단을 반환한다.
 * @return array()
 */
function kboard_builtin_pg_active_method(){
	$active_method = array();
	if(defined('COSMOSFARM_PAY_KB_VERSION')){
		$active_method = get_option('kboard_builtin_pg_active_method', array());
		if(!$active_method || !is_array($active_method)){
			$active_method = array();
		}
	}
	return $active_method;
}

/**
 * 주문 페이지에서 PG 관련 기능을 초기화한다.
 * @param string|array $pg
 * @param array $args
 */
function kboard_builtin_pg_init($pg, $args=array()){
	if(!is_array($pg) && $pg == 'iamport'){
		wp_enqueue_script('iamport-payment');
	}
	else if(is_array($pg)){
		$pg_list = kboard_builtin_pg_list();
		
		foreach($pg as $pg_name){
			if(!isset($pg_list[$pg_name])) continue;
			
			if(class_exists($pg_list[$pg_name]['class_name'])){
				$pg_instance = new $pg_list[$pg_name]['class_name']();
				$pg_instance->payment_scripts();
			}
		}
		
		$args = array_merge(array(
			'builtin_pg' => $pg,
			'pay_success_url' => '',
			'merchant_uid' => '',
			'product_title' => '',
			'product_price' => '',
			'm_redirect_url' => '',
			'app_scheme' => '',
			'button_display_text' => '',
		), $args);
		
		wp_localize_script('kboard-builtin-pg', 'kboard_builtin_pg_checkout_settings', $args);
	}
	
	do_action('kboard_builtin_pg_init', $pg, $args);
}

/**
 * 유튜브, 비메오 동영상 URL을 iframe 코드로 변환한다.
 * @param string $content
 * @return mixed
 */
function kboard_video_url_to_iframe($content){
	$protected_tags = array();
	$content = preg_replace_callback('/<(iframe|a)\b[^>]*>.*?<\/\1>/is', function($matches) use (&$protected_tags){
		$hash = '__KBOARD_VIDEO_PROTECTED_TAG_' . count($protected_tags) . '__';
		$protected_tags[$hash] = $matches[0];
		return $hash;
	}, $content);
	
	// YouTube
	$content = preg_replace_callback(
		"/(?:https?:\/\/)?(?:www\.)?youtube\.com\/watch\?([^\\s<\"']+)/i",
		function($matches){
			$query = html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8');
			parse_str($query, $params);
			
			if(!empty($params['v']) && preg_match('/^[a-zA-Z0-9\-_]+$/', $params['v'])){
				return '<iframe src="https://www.youtube.com/embed/' . $params['v'] . '" width="560" height="315" frameborder="0" allowfullscreen></iframe>';
			}
			
			return $matches[0];
		},
		$content
	);
	
	// YouTube 숏츠
	$content = preg_replace(
		"/(?:https?:\/\/)?(?:www\.)?youtube\.com\/shorts\/([a-zA-Z0-9\-_]+)(?:[^\\s<\"']*)?/i",
		'<iframe src="https://www.youtube.com/embed/$1" width="560" height="315" frameborder="0" allowfullscreen></iframe>',
		$content
	);
	
	// YouTube 임베드
	$content = preg_replace(
		"/(?:https?:\/\/)?youtu\.be\/([a-zA-Z0-9\-_]+)(?:[^\\s<\"']*)?/i",
		'<iframe src="https://www.youtube.com/embed/$1" width="560" height="315" frameborder="0" allowfullscreen></iframe>',
		$content
	);
	
	// 비메오
	$content = preg_replace(
		"/(?:https?:\/\/)?(?:www\.)?vimeo\.com\/(\d+)(?:[^\\s<\"']*)?/i",
		'<iframe src="https://player.vimeo.com/video/$1" width="560" height="315" frameborder="0" allowfullscreen></iframe>',
		$content
	);
	
	foreach($protected_tags as $hash => $original){
		$content = str_replace($hash, $original, $content);
	}
	
	return $content;
}

/**
 * 사용자가 작성한 모든 게시글의 개수를 반환한다.
 * @param int $user_id
 * @return int
 */
function kboard_get_user_total_count($user_id){
	global $wpdb;
	$user_id = intval($user_id);
	if($user_id){
		$where = array();
		$where[] = "`member_uid`='{$user_id}'";
		
		// 휴지통에 없는 게시글만 불러온다.
		$get_list_status_query = kboard_get_list_status_query();
		if($get_list_status_query){
			$where[] = $get_list_status_query;
		}
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE " . implode(' AND ', $where));
		$wpdb->flush();
		
		return intval($count);
	}
	return 0;
}

/**
 * 사용자가 작성한 모든 댓글의 개수를 반환한다.
 * @param int $user_id
 * @return int
 */
function kboard_comments_get_user_total_count($user_id){
	global $wpdb;
	$user_id = intval($user_id);
	if($user_id && defined('KBOARD_COMMNETS_VERSION')){
		$where = array();
		$where[] = "`user_uid`='{$user_id}'";
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_comments` WHERE " . implode(' AND ', $where));
		$wpdb->flush();
		
		return intval($count);
	}
	return 0;
}

/**
 * 게시판 목록에서 보여줄 게시글 상태를 반환한다.
 * @param int $board_id
 * @return array
 */
function kboard_get_list_status($board_id=''){
	$get_list_status = array('', 'pending_approval');
	return apply_filters('kboard_get_list_status', $get_list_status, $board_id);
}

/**
 * 게시판 목록에서 보여줄 게시글 상태를 쿼리문 형태로 반환한다.
 * @param int $board_id
 * @param string $table
 * @return array
 */
function kboard_get_list_status_query($board_id='', $table=''){
	$query = '';
	$table = esc_sql(sanitize_text_field($table));
	$get_list_status = array($table ? "`{$table}`.`status` IS NULL" : "`status` IS NULL");
	
	foreach(kboard_get_list_status($board_id) as $status){
		$status = esc_sql($status);
		$get_list_status[] = $table ? "`{$table}`.`status`='{$status}'" : "`status`='{$status}'";
	}
	
	if($get_list_status){
		$get_list_status = implode(' OR ', $get_list_status);
		$query = "({$get_list_status})";
	}
	
	return $query;
}

/**
 * 게시글 상태 목록을 반환한다.
 * @return array
 */
function kboard_content_status_list(){
	$status_list = array(
		''                 => __('Published', 'kboard'),
		'pending_approval' => __('Pending approval', 'kboard'),
		'trash'            => __('Trash', 'kboard'),
	);
	return apply_filters('kboard_content_status_list', $status_list);
}

/**
 * 게시글 본문 에디터 목록을 반환한다.
 * @return array
 */
function kboard_content_editor_list(){
	$editor_list = array(
		''        => 'textarea 사용',
		'yes'     => '워드프레스 내장 에디터 사용',
		'snote'   => '썸머노트 에디터 사용',
		'tiptap'  => 'TipTap 에디터 사용',
		'editorjs'=> 'Editor.js 에디터 사용',
	);
	return apply_filters('kboard_content_editor_list', $editor_list);
}

function kboard_normalize_editor_value($value){
	$value = trim(strtolower((string)$value));
	if($value === ''){
		return '';
	}
	$editor_aliases = array(
		'tptp' => 'tiptap',
		'edjs' => 'editorjs',
	);
	if(isset($editor_aliases[$value])){
		return $editor_aliases[$value];
	}
	return $value;
}

/**
 * 게시글 본문 에디터 코드를 반환한다.
 * @param array $vars
 * @return string
 */
function kboard_content_editor($vars=array()){
	$vars = array_merge(array(
		'board' => new KBoard(),
		'content' => new KBContent(),
		'required' => '',
		'placeholder' => '',
		'editor_height' => '400',
	), $vars);
	
	$vars = apply_filters('kboard_content_editor_vars', $vars);
	
	extract($vars, EXTR_SKIP);
	
	ob_start();
	
	$current_editor = kboard_normalize_editor_value($board->use_editor);

	if($current_editor == 'yes'){
		wp_editor($content->content, 'kboard_content', array('media_buttons'=>$board->isAdmin(), 'editor_height'=>$editor_height));
	}
	else if($current_editor == 'snote'){ // summernote
		echo sprintf('<textarea id="kboard_content" class="summernote" name="kboard_content" style="height:%dpx;" placeholder="%s">%s</textarea>', $editor_height, esc_attr($placeholder), esc_html(kboard_content_paragraph_breaks($content->content)));
	}
	else if(in_array($current_editor, array('tiptap', 'editorjs'))){
		echo sprintf('<textarea id="kboard_content" class="editor-textarea %s" name="kboard_content" style="display:none;" placeholder="%s">%s</textarea>', esc_attr($required), esc_attr($placeholder), esc_textarea($content->content));
		echo sprintf('<div id="kboard-editor-root" class="kboard-editor-root kboard-editor-root-%s" data-kboard-editor="%s"></div>', esc_attr($current_editor), esc_attr($current_editor));
	}
	else{
		echo sprintf('<textarea id="kboard_content" class="editor-textarea %s" name="kboard_content" placeholder="%s">%s</textarea>', esc_attr($required), esc_attr($placeholder), esc_textarea($content->content));
	}
	
	$editor = ob_get_clean();
	
	return apply_filters('kboard_content_editor', $editor, $vars);
}

/**
 * 게시판 목록의 정렬 타입을 반환한다.
 * @return array
 */
function kboard_list_sorting_types(){
	$sorting_types = array('newest', 'best', 'viewed', 'updated');
	return apply_filters('kboard_list_sorting_types', $sorting_types);
}

/**
 * media_group 값을 반환한다.
 * @return string
 */
function kboard_media_group($reset=false){
	static $media_group;
	if($media_group === null || $reset){
		$media_group = uniqid();
	}
	return apply_filters('kboard_media_group', $media_group);
}

/**
 * category1 값을 반환한다.
 * @return string
 */
function kboard_category1(){
	static $category1;
	if($category1 === null){
		$_GET['category1'] = isset($_GET['category1'])?sanitize_text_field($_GET['category1']):'';
		$category1 = $_GET['category1'];
	}
	return apply_filters('kboard_category1', $category1);
}

/**
 * category2 값을 반환한다.
 * @return string
 */
function kboard_category2(){
	static $category2;
	if($category2 === null){
		$_GET['category2'] = isset($_GET['category2'])?sanitize_text_field($_GET['category2']):'';
		$category2 = $_GET['category2'];
	}
	return apply_filters('kboard_category2', $category2);
}

/**
 * category3 값을 반환한다.
 * @return string
 */
function kboard_category3(){
	static $category3;
	if($category3 === null){
		$_GET['category3'] = isset($_GET['category3'])?sanitize_text_field($_GET['category3']):'';
		$category3 = $_GET['category3'];
	}
	return apply_filters('kboard_category3', $category3);
}

/**
 * category4 값을 반환한다.
 * @return string
 */
function kboard_category4(){
	static $category4;
	if($category4 === null){
		$_GET['category4'] = isset($_GET['category4'])?sanitize_text_field($_GET['category4']):'';
		$category4 = $_GET['category4'];
	}
	return apply_filters('kboard_category4', $category4);
}

/**
 * category5 값을 반환한다.
 * @return string
 */
function kboard_category5(){
	static $category5;
	if($category5 === null){
		$_GET['category5'] = isset($_GET['category5'])?sanitize_text_field($_GET['category5']):'';
		$category5 = $_GET['category5'];
	}
	return apply_filters('kboard_category5', $category5);
}

/**
 * sales_category1 값을 반환한다.
 * @return string
 */
function kboard_sales_category1(){
	static $sales_category1;
	if($sales_category1 === null){
		$sales_category1 = isset($_GET['sales_category1'])?sanitize_text_field($_GET['sales_category1']):'';
	}
	return apply_filters('kboard_sales_category1', $sales_category1);
}

/**
 * sales_category2 값을 반환한다.
 * @return string
 */
function kboard_sales_category2(){
	static $sales_category2;
	if($sales_category2 === null){
		$sales_category2 = isset($_GET['sales_category2'])?sanitize_text_field($_GET['sales_category2']):'';
	}
	return apply_filters('kboard_sales_category2', $sales_category2);
}

/**
 * uid 값을 반환한다.
 * @return string
 */
function kboard_uid(){
	static $uid;
	if($uid === null){
		$_GET['uid'] = isset($_GET['uid'])?intval($_GET['uid']):'';
		$_POST['uid'] = isset($_POST['uid'])?intval($_POST['uid']):'';
		$uid = $_GET['uid']?$_GET['uid']:$_POST['uid'];
	}
	return apply_filters('kboard_uid', $uid);
}

/**
 * execute_uid 값을 반환한다.
 * @return string
 */
function kboard_execute_uid(){
	static $execute_uid;
	if($execute_uid === null){
		$_GET['execute_uid'] = isset($_GET['execute_uid'])?intval($_GET['execute_uid']):'';
		$execute_uid = $_GET['execute_uid'];
	}
	return apply_filters('kboard_execute_uid', $execute_uid);
}

/**
 * mod 값을 반환한다.
 * @param string $default
 * @return string
 */
function kboard_mod($default=''){
	static $mod;
	if($mod === null){
		$_GET['mod'] = isset($_GET['mod'])?sanitize_key($_GET['mod']):'';
		$_POST['mod'] = isset($_POST['mod'])?sanitize_key($_POST['mod']):'';
		$mod = $_GET['mod']?$_GET['mod']:$_POST['mod'];
	}
	if(!in_array($mod, array('list', 'document', 'editor', 'remove', 'order', 'complete', 'history', 'sales'))){
		return $default;
	}
	return apply_filters('kboard_mod', $mod);
}

/**
 * KBoardBuilder 클래스에서 실행된 게시판의 mod 값을 반환한다.
 * @param string $mod
 * @return string
 */
function kboard_builder_mod($mod=''){
	static $builder_mod;
	if($builder_mod === null){
		$builder_mod = '';
	}
	if($mod){
		$builder_mod = $mod;
	}
	if(!in_array($builder_mod, array('list', 'document', 'editor', 'remove', 'order', 'complete', 'history', 'sales'))){
		$builder_mod = '';
	}
	return apply_filters('kboard_builder_mod', $builder_mod);
}

/**
 * keyword 값을 반환한다.
 * @return string
 */
function kboard_keyword(){
	static $keyword;
	if($keyword === null){
		$_GET['keyword'] = isset($_GET['keyword'])?sanitize_text_field($_GET['keyword']):'';
		$keyword = $_GET['keyword'];
	}
	return apply_filters('kboard_keyword', $keyword);
}

/**
 * target 값을 반환한다.
 * @return string
 */
function kboard_target(){
	static $target;
	if($target === null){
		$_GET['target'] = isset($_GET['target'])?sanitize_key($_GET['target']):'';
		$target = $_GET['target'];
	}
	return apply_filters('kboard_target', $target);
}

/**
 * pageid 값을 반환한다.
 * @return string
 */
function kboard_pageid(){
	static $pageid;
	if($pageid === null){
		$_GET['pageid'] = isset($_GET['pageid'])?intval($_GET['pageid']):1;
		$pageid = $_GET['pageid'];
	}
	return apply_filters('kboard_pageid', $pageid);
}

/**
 * parent_uid 값을 반환한다.
 * @return string
 */
function kboard_parent_uid(){
	static $parent_uid;
	if($parent_uid === null){
		$_GET['parent_uid'] = isset($_GET['parent_uid'])?intval($_GET['parent_uid']):'';
		$parent_uid = $_GET['parent_uid'];
	}
	return apply_filters('kboard_parent_uid', $parent_uid);
}

/**
 * kboard_id 값을 반환한다.
 * @return string
 */
function kboard_id(){
	static $kboard_id;
	if($kboard_id === null){
		$_GET['kboard_id'] = isset($_GET['kboard_id'])?intval($_GET['kboard_id']):'';
		$kboard_id = $_GET['kboard_id'];
	}
	return apply_filters('kboard_id', $kboard_id);
}

/**
 * compare 값을 반환한다.
 * @return string
 */
function kboard_compare(){
	static $compare;
	if($compare === null){
		$compare = isset($_REQUEST['compare'])?sanitize_text_field($_REQUEST['compare']):'';
		if(!in_array($compare, array('=', '!=', '>', '>=', '<', '<=', 'LIKE', 'NOT LIKE'))){
			$compare = 'LIKE';
		}
	}
	return apply_filters('kboard_compare', $compare);
}

/**
 * start_date 값을 반환한다.
 * @return string
 */
function kboard_start_date(){
	static $start_date;
	if($start_date === null){
		$start_date = isset($_REQUEST['start_date'])?sanitize_text_field($_REQUEST['start_date']):'';
	}
	return apply_filters('kboard_start_date', $start_date);
}

/**
 * end_date 값을 반환한다.
 * @return string
 */
function kboard_end_date(){
	static $end_date;
	if($end_date === null){
		$end_date = isset($_REQUEST['end_date'])?sanitize_text_field($_REQUEST['end_date']):'';
	}
	return apply_filters('kboard_end_date', $end_date);
}

/**
 * kboard_option 값을 반환한다.
 * @return array
 */
function kboard_search_option(){
	static $search_option;
	if($search_option === null){
		$search_option = (isset($_REQUEST['kboard_search_option'])&&is_array($_REQUEST['kboard_search_option']))?$_REQUEST['kboard_search_option']:array();
	}
	return apply_filters('kboard_search_option', $search_option);
}

/**
 * order_id 값을 반환한다.
 * @return string
 */
function kboard_order_id(){
	static $order_id;
	if($order_id === null){
		$order_id = isset($_REQUEST['order_id'])?intval($_REQUEST['order_id']):'';
	}
	return apply_filters('kboard_order_id', $order_id);
}

/**
 * order_item_id 값을 반환한다.
 * @return string
 */
function kboard_order_item_id(){
	static $order_item_id;
	if($order_item_id === null){
		$order_item_id = isset($_REQUEST['order_item_id'])?intval($_REQUEST['order_item_id']):'';
	}
	return apply_filters('kboard_order_item_id', $order_item_id);
}

/**
 * with_notice 값을 반환한다.
 * @return string
 */
function kboard_with_notice(){
	static $with_notice;
	if($with_notice === null){
		$with_notice = (isset($_REQUEST['with_notice'])&&intval($_REQUEST['with_notice']))?true:false;
	}
	return apply_filters('kboard_with_notice', $with_notice);
}

/**
 * use_prevent_modify_delete 값을 반환한다.
 * @return boolean
 */
function kboard_use_prevent_modify_delete(){
	static $use_prevent_modify_delete;
	if($use_prevent_modify_delete === null){
		$use_prevent_modify_delete = (isset($_REQUEST['use_prevent_modify_delete'])&&intval($_REQUEST['use_prevent_modify_delete']))?true:false;
	}
	return apply_filters('kboard_use_prevent_modify_delete', $use_prevent_modify_delete);
}

/**
 * view_iframe 값을 반환한다.
 * @return string
 */
function kboard_view_iframe(){
	static $view_iframe;
	if($view_iframe === null){
		$view_iframe = (isset($_REQUEST['view_iframe'])&&intval($_REQUEST['view_iframe']))?true:false;
	}
	return apply_filters('kboard_view_iframe', $view_iframe);
}

/**
 * iframe_id 값을 반환한다.
 * @kboard_iframe_id string
 */
function kboard_iframe_id(){
	static $iframe_id;
	if($iframe_id === null){
		$iframe_id = isset($_REQUEST['iframe_id'])?sanitize_text_field($_REQUEST['iframe_id']):'';
	}
	return apply_filters('kboard_iframe_id', $iframe_id);
}

/**
 * 문자열에 특수문자(*)를 추가해서 알아볼 수 없도록 변경한다.
 */
function kboard_text_masking($value){
	if($value){
		if(strpos($value, '@') !== false){
			$value2 = explode('@', $value);
			$value = kboard_text_masking($value2[0]) . '@' . $value2[1];
		}
		else{
			$strlen = mb_strlen($value, 'utf-8');
			
			if($strlen > 4){
				$showlen = 3;
				$value = mb_substr($value, 0, $showlen, 'utf-8') . str_repeat('*', $strlen-$showlen);
			}
			else if($strlen > 3){
				$showlen = 2;
				$value = mb_substr($value, 0, $showlen, 'utf-8') . str_repeat('*', $strlen-$showlen);
			}
			else if($strlen > 2){
				$value = preg_split('//u', $value, null, PREG_SPLIT_NO_EMPTY);
				$value = $value[0] . '*' . $value[2];
			}
			else if($strlen > 1){
				$value = preg_split('//u', $value, null, PREG_SPLIT_NO_EMPTY);
				$value = $value[0] . '*';
			}
		}
	}
	return $value;
}

/**
 * 검색엔진 검색로봇인지 확인한다.
 * @return boolean
 */
function kboard_is_bot(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	
	if(strpos($user_agent, 'google') !== false){
		return true;
	}
	if(strpos($user_agent, 'bingbot') !== false){
		return true;
	}
	if(strpos($user_agent, 'yeti') !== false){
		return true;
	}
	if(strpos($user_agent, 'daum') !== false){
		return true;
	}
	if(strpos($user_agent, 'kakaotalk-scrap') !== false){
		return true;
	}
	
	return false;
}
