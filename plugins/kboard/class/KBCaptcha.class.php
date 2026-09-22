<?php
/**
 * KBoard Captcha
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCaptcha {
	
	/**
	 * Captcha 이미지를 생성한다.
	 */
	public function createImage(){
		if(!isset($_SESSION['kboard_captcha'])) $_SESSION['kboard_captcha'] = array();
		
		$captcha_folder = WP_CONTENT_DIR.'/uploads/kboard_captcha/';
		$captcha_name = uniqid('captcha_').'.png';
		
		// 디렉토리 생성
		wp_mkdir_p($captcha_folder);
		
		// 1시간이 지난 이미지는 삭제한다.
		$file_handler = new KBFileHandler();
		$captcha_files = $file_handler->getDirlist($captcha_folder);
		foreach($captcha_files as $file){
			$filetime = @filemtime($captcha_folder . $file);
			$created = (time() - $filetime) / 60 / 60;
			if($created > 1) $file_handler->delete($captcha_folder . $file);
		}
		
		$text = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
		shuffle($text);
		$text = substr(implode('', $text), 0, 5);
		
		$image = imagecreate(50, 20);
		$background_color = imagecolorallocate($image, 255, 255, 255);
		$font_color = imagecolorallocate($image, 194, 51, 21);
		
		imagestring($image, 5, 2, 2, $text, $font_color);
		imageline($image, 0, 0, 50, 20, $font_color);
		@imagepng($image, $captcha_folder . $captcha_name);
		imagedestroy($image);
		
		if(file_exists($captcha_folder . $captcha_name)){
			$_SESSION['kboard_captcha'][] = $text;
			$src = content_url('/uploads/kboard_captcha/' . $captcha_name);
		}
		else{
			$_SESSION['kboard_captcha'][] = 'ERROR';
			$src = KBOARD_URL_PATH . '/images/captcha-error.png';
		}
		
		return $src;
	}
	
	/**
	 * Captcha 검증
	 * @return boolean
	 */
	public function validate(){
		if(is_user_logged_in()){
			return true;
		}
		if(kboard_use_recaptcha() && isset($_POST['g-recaptcha-response'])){
			if($this->recaptcha()){
				return true;
			}
		}
		if(isset($_POST['captcha'])){
			if(in_array(strtoupper($_POST['captcha']), $_SESSION['kboard_captcha'])){
				unset($_SESSION['kboard_captcha']);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 구글 reCAPTCHA 검증
	 * @return boolean
	 */
	function recaptcha(){
		if(!kboard_use_recaptcha()){
			return false;
		}

		$siteverify_url = add_query_arg(array(
				'secret'   => kboard_recaptcha_secret_key(),
				'response' => isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '',
				'remoteip' => kboard_user_ip()
		), 'https://www.google.com/recaptcha/api/siteverify');
		
		$response = wp_remote_get($siteverify_url);
		
		if(is_wp_error($response) || empty($response['body']) || !($json = json_decode($response['body'])) || !$json->success){
			return false;
		}
		
		if(kboard_recaptcha_type() == 'v3'){
			$response_action = isset($json->action) ? sanitize_key($json->action) : '';
			$response_score = isset($json->score) ? floatval($json->score) : 0;
			
			if($response_action !== kboard_recaptcha_v3_action()){
				return false;
			}
			if($response_score < 0.5){
				return false;
			}
		}
		return true;
	}
}
