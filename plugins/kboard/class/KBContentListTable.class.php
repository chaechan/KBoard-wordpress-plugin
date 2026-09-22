<?php
/**
 * KBoard 게시글 관리자 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBContentListTable extends WP_List_Table {
	
	var $board_list;
	var $filter_view;
	var $filter_board_id;
	var $filter_category1;
	var $per_page;
	var $active_admin_board;
	
	public function __construct(){
		parent::__construct();
		$this->filter_view = isset($_REQUEST['filter_view'])?$_REQUEST['filter_view']:'';
		$this->filter_board_id = isset($_REQUEST['filter_board_id'])?intval($_REQUEST['filter_board_id']):'';
		$this->filter_category1 = isset($_REQUEST['filter_category1'])?sanitize_text_field($_REQUEST['filter_category1']):'';
		$this->per_page = isset($_REQUEST['per_page'])?intval($_REQUEST['per_page']):20;
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$this->board_list = new KBoardList();
		$this->board_list->init();
		$this->active_admin_board = $this->board_list->getActiveAdmin();
		
		$keyword = isset($_GET['s'])?sanitize_text_field($_GET['s']):'';
		$target = kboard_target();
		
		$list = new KBContentList($this->filter_board_id);
		$list->rpp = $this->per_page;
		$list->page = $this->get_pagenum();
		$list->status = $this->filter_view;
		
		if($this->filter_category1){
			$list->category1 = $this->filter_category1;
		}
		
		$list->initWithKeyword($keyword, $target);
		
		$this->items = $list->resource;
		
		$this->set_pagination_args(array('total_items'=>$list->total, 'per_page'=>$list->rpp));
	}
	
	public function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'kboard';
		$classes[] = 'kboard-content-list';
		return $classes;
	}
	
	public function get_views(){
		global $wpdb;
		
		$views = array();
		$class = '';
		
		$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status` NOT LIKE 'trash'");
		$class = !$this->filter_view ? ' class="current"' : '';
		$views['all'] = '<a href="' . add_query_arg(array('filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . __('All', 'kboard') . " <span class=\"count\">({$count})</span></a>";
		
		$status_list = kboard_content_status_list();
		foreach($status_list as $status=>$status_name){
			$filter_view = $status ? $status : 'published';
			$count = $wpdb->get_var("SELECT COUNT(*) FROM `{$wpdb->prefix}kboard_board_content` WHERE `status`='{$status}'");
			$class = $this->filter_view == $filter_view ? ' class="current"' : '';
			$views[$filter_view] = '<a href="' . add_query_arg(array('filter_view'=>$filter_view, 'filter_board_id'=>$this->filter_board_id), admin_url('admin.php?page=kboard_content_list')) . '"' . $class . '>' . $status_name . " <span class=\"count\">({$count})</span></a>";
		}
		
		return $views;
	}
	
	public function no_items(){
		echo __('No documents found.', 'kboard');
	}
	
	public function get_columns(){
		$columns = array(
			'cb' => '<input type="checkbox">',
			'board' => __('Forum', 'kboard'),
			'title' => __('Title', 'kboard'),
			'category1' => '카테고리1',
			'member' => __('Author', 'kboard'),
			'view' => __('Views', 'kboard'),
			'secret' => __('Secret', 'kboard'),
			'notice' => __('Notice', 'kboard'),
			'date' => __('Date', 'kboard'),
			'status' => __('Status', 'kboard'),
		);
		
		return apply_filters('kboard_admin_content_list_table_columns', $columns);
	}
	
	public function get_bulk_actions(){
		$status_list = kboard_content_status_list();
		$actions = array();
		
		foreach($status_list as $status=>$status_name){
			if($this->filter_view == 'trash'){
				if($status == 'trash') continue;
				$actions[$status ? $status : 'published'] = sprintf(__('%s로 변경', 'kboard'), $status_name);
			}
			else{
				if($status == 'trash'){
					$actions['trash'] = __('Move to Trash', 'kboard');
				}
				else{
					$action_key = $status ? $status : 'published';
					
					if($this->filter_view != $action_key){
						$actions[$action_key] = sprintf(__('%s로 변경', 'kboard'), $status_name);
					}
				}
			}
		}
		
		if($this->filter_view == 'trash'){
			$actions['delete'] = __('Delete Permanently', 'kboard');
		}
		else{
			$actions['delete_immediately'] = __('Delete Permanently', 'kboard');
		}
		
		return $actions;
	}
	
	public function display_tablenav($which){ ?>
		<div class="tablenav <?php echo esc_attr($which)?>">
			<div class="alignleft actions bulkactions"><?php $this->bulk_actions($which)?></div>
			<?php if($which=='top'):?>
			<div class="alignleft actions">
				<input type="hidden" name="filter_view" value="<?php echo esc_attr($this->filter_view)?>">
				
				<label class="screen-reader-text" for="posts-per-page">게시글 표시 수</label>
				<select id="posts-per-page" name="per_page">
					<option value="10"<?php if($this->per_page == 10):?> selected<?php endif?>>10개씩 보기</option>
					<option value="20"<?php if($this->per_page == 20):?> selected<?php endif?>>20개씩 보기</option>
					<option value="30"<?php if($this->per_page == 30):?> selected<?php endif?>>30개씩 보기</option>
					<option value="50"<?php if($this->per_page == 50):?> selected<?php endif?>>50개씩 보기</option>
					<option value="100"<?php if($this->per_page == 100):?> selected<?php endif?>>100개씩 보기</option>
				</select>
				
				<label class="screen-reader-text" for="filter-by-board-id">게시판으로 필터</label>
				<select id="filter-by-board-id" name="filter_board_id">
					<option value="">전체 게시글</option>
					<?php foreach($this->board_list->resource as $board):?>
					<option value="<?php echo $board->uid?>"<?php if($this->filter_board_id == $board->uid):?> selected<?php endif?>><?php echo $board->board_name?></option>
					<?php endforeach?>
				</select>
				
				<label class="screen-reader-text" for="filter-category1">카테고리1</label>
				<input type="text" id="filter-category1" name="filter_category1" value="<?php echo esc_attr($this->filter_category1)?>" placeholder="카테고리1" style="width: 150px;">
				
				<input type="date" name="start_date" value="<?php echo esc_attr(kboard_start_date()) ?>" placeholder="시작일">
				<input type="date" name="end_date" value="<?php echo esc_attr(kboard_end_date()) ?>" placeholder="종료일">
				
				<input type="button" name="filter_action" class="button" value="<?php echo __('Filter', 'kboard')?>" onclick="kboard_content_list_filter(this.form)">
				<span class="spinner"></span>
			</div>
			
			<div class="alignleft actions" style="margin-left: 10px;">
				<label class="screen-reader-text" for="move-to-board">게시판으로 이동</label>
				<select id="move-to-board" name="move_to_board">
					<option value="">게시판 선택</option>
					<?php foreach($this->board_list->resource as $board):?>
					<option value="<?php echo $board->uid?>"><?php echo $board->board_name?></option>
					<?php endforeach?>
				</select>
				<button type="submit" name="kboard_content_list_move_to_board" value="1" class="button" onclick="return kboard_content_list_move_to_board(this.form)">이동</button>
			</div>
			<?php endif?>
			<?php
			$this->extra_tablenav($which);
			$this->pagination($which);
			?>
			<br class="clear">
		</div>
	<?php }
	
	public function display_rows(){
		foreach($this->items as $key=>$item){
			if(in_array($item->board_id, $this->active_admin_board)){
				$item->url = admin_url("admin.php?page=kboard_admin_view_{$item->board_id}&mod=document&uid={$item->uid}");
			}
			else{
				$url = new KBUrl();
				$item->url = $url->getDocumentRedirect($item->uid);
			}
			$this->single_row($item);
		}
	}
	
	public function single_row($item){
		echo '<tr data-uid="'.$item->uid.'">';
		
		foreach($this->get_columns() as $key=>$value){
			if($key == 'cb'){
				echo '<th scope="row" class="check-column">';
				echo '<input type="checkbox" name="uid[]" value="'.$item->uid.'">';
				echo '</th>';
			}
			else if($key == 'board'){
				echo '<td class="kboard-content-list-board column-primary">';
				if($item->board_id){
					echo '<select name="board_id['.$item->uid.']" onchange="kboard_content_list_update('.intval($item->uid).')">';
					foreach($this->board_list->resource as $board){
						echo '<option value="'.$board->uid.'"'.($item->board_id==$board->uid?' selected':'').'>'.$board->board_name.'</option>';
					}
					echo '</select>';
				}
				else{
					echo __('The reply.', 'kboard');
				}
				echo '<button type="button" class="toggle-row"><span class="screen-reader-text">상세보기</span></button>';
				echo '</td>';
			}
			else if($key == 'title'){
				echo '<td class="kboard-content-list-title" data-colname="'.__('Title', 'kboard').'">';
				if($item->comment){
					echo '<h4><a href="'.$item->url.'" onclick="window.open(this.href);return false;">'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').' ('.$item->comment.')</a></h4>';
				}
				else{
					echo '<h4><a href="'.$item->url.'" onclick="window.open(this.href);return false;">'.mb_strimwidth(strip_tags($item->title), 0, 300, '...', 'UTF-8').'</a></h4>';
				}
				echo '<p>'.mb_strimwidth(strip_tags($item->content), 0, 300, '...', 'UTF-8').'</p>';
				echo '</td>';
			}
			else if($key == 'category1'){
				echo '<td class="kboard-content-list-category1" data-colname="카테고리1">';
				echo esc_html($item->category1);
				echo '</td>';
			}
			else if($key == 'member'){
				echo '<td class="kboard-content-list-author" data-colname="'.__('Author', 'kboard').'">';
				if($item->member_uid) echo '<a href="'.admin_url('user-edit.php?user_id='.$item->member_uid).'">';
				echo $item->member_display;
				if($item->member_uid) echo '</a>';
				echo '</td>';
			}
			else if($key == 'view'){
				echo '<td class="kboard-content-list-view" data-colname="'.__('Views', 'kboard').'">';
				echo $item->view;
				echo '</td>';
			}
			else if($key == 'secret'){
				echo '<td class="kboard-content-list-secret" data-colname="'.__('Secret', 'kboard').'">';
				echo $item->secret?__('Yes', 'kboard'):__('No', 'kboard');
				echo '</td>';
			}
			else if($key == 'notice'){
				echo '<td class="kboard-content-list-notice" data-colname="'.__('Notice', 'kboard').'">';
				echo $item->notice?__('Yes', 'kboard'):__('No', 'kboard');
				echo '</td>';
			}
			else if($key == 'date'){
				$date_value = kboard_date_format($item->date, 'Y-m-d');
				$time_value = kboard_date_format($item->date, 'H:i:s');
				echo '<td class="kboard-content-list-date" data-colname="'.__('Date', 'kboard').'">';
				echo '<div class="kboard-content-date-controls">';
				echo '<div class="kboard-content-date-fields">';
				echo '<input type="text" name="date['.$item->uid.']" class="kboard-content-datepicker kboard-content-date-input" size="10" maxlength="10" value="'.esc_attr($date_value).'" aria-label="작성일">';
				echo '<input type="text" name="time['.$item->uid.']" class="kboard-content-timepicker kboard-content-time-input" size="8" maxlength="8" value="'.esc_attr($time_value).'" aria-label="작성 시간">';
				echo '</div>';
				echo '<button type="button" class="button button-small kboard-content-date-update" onclick="kboard_content_list_update('.intval($item->uid).')">'.__('Update', 'kboard').'</button>';
				echo '</div>';
				echo '</td>';
			}
			else if($key == 'status'){
				echo '<td class="kboard-content-list-status" data-colname="'.__('Status', 'kboard').'">';
				echo '<select name="status['.$item->uid.']" onchange="kboard_content_list_update('.intval($item->uid).')">';
				$status_list = kboard_content_status_list();
				foreach($status_list as $key=>$value){
					$selected = ($item->status==$key) ? ' selected' : '';
					echo '<option value="'.esc_attr($key).'"'.$selected.'>'.esc_html($value).'</option>';
				}
				echo '</td>';
			}
			else{
				$content_uid = $item->uid;
				do_action('kboard_admin_content_list_table_custom_column', $key, $content_uid);
			}
		}
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){ ?>
	<p class="search-box" style="margin: 0; display: inline-block; vertical-align: middle;">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
