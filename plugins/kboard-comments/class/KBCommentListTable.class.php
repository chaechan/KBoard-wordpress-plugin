<?php
/**
 * KBoard 댓글 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBCommentListTable extends WP_List_Table {
	
	var $list;
	var $url;
	var $per_page;
	
	public function __construct(){
		parent::__construct();
		
		$this->list = new KBCommentList();
		$this->url = new KBUrl();
		$per_page = isset($_REQUEST['per_page']) && is_scalar($_REQUEST['per_page']) ? intval($_REQUEST['per_page']) : 20;
		$this->per_page = in_array($per_page, array(10, 20, 30, 50, 100), true) ? $per_page : 20;
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$keyword = isset($_GET['s'])?esc_attr($_GET['s']):'';
		$target = kboard_target();
		
		$this->list->rpp = $this->per_page;
		$this->list->page = $this->get_pagenum();
		$this->list->initWithKeyword($keyword, $target);
		$this->items = $this->list->resource;
		
		$this->set_pagination_args(array('total_items'=>$this->list->total, 'per_page'=>$this->list->rpp));
	}
	
	public function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'kboard';
		$classes[] = 'kboard-comments-list';
		return $classes;
	}
	
	public function no_items(){
		echo __('No comment found.', 'kboard-comments');
	}
	
	public function get_columns(){
		return array(
				'cb'           => '<input type="checkbox">',
				'board_name'   => __('Forum', 'kboard-comments'),
				'user_display' => __('Name', 'kboard-comments'),
				'content'      => __('Content', 'kboard-comments'),
				'status'       => __('Status', 'kboard-comments'),
				'date'         => __('Date', 'kboard-comments')
		);
	}
	
	function get_bulk_actions(){
		return array(
				'delete' => __('Delete', 'kboard-comments')
		);
	}
	
	public function display_tablenav($which){ ?>
		<div class="tablenav <?php echo esc_attr($which)?>">
			<div class="alignleft actions bulkactions"><?php $this->bulk_actions($which)?></div>
			<?php if($which=='top'):?>
			<div class="alignleft actions">
				<label class="screen-reader-text" for="comments-per-page">댓글 표시 수</label>
				<select id="comments-per-page" name="per_page">
					<option value="10"<?php if($this->per_page == 10):?> selected<?php endif?>>10개씩 보기</option>
					<option value="20"<?php if($this->per_page == 20):?> selected<?php endif?>>20개씩 보기</option>
					<option value="30"<?php if($this->per_page == 30):?> selected<?php endif?>>30개씩 보기</option>
					<option value="50"<?php if($this->per_page == 50):?> selected<?php endif?>>50개씩 보기</option>
					<option value="100"<?php if($this->per_page == 100):?> selected<?php endif?>>100개씩 보기</option>
				</select>

				<input type="date" name="start_date" value="<?php echo esc_attr(kboard_start_date()) ?>" placeholder="시작일">
				<input type="date" name="end_date" value="<?php echo esc_attr(kboard_end_date()) ?>" placeholder="종료일">
				<input type="button" name="filter_action" class="button" value="<?php echo __('Filter', 'kboard')?>" onclick="kboard_comment_list_filter(this.form)">
				<span class="spinner"></span>
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
		foreach($this->items as $item){
			$this->single_row($item);
		}
	}
	
	public function single_row($item){
		$board = new KBoard();
		$board->initWithContentUID($item->content_uid);
		$selected = $item->status == 'pending_approval' ? 'selected' : '';
		
		$edit_url = admin_url("admin.php?page=kboard_list&board_id={$board->id}");
		
		echo '<tr data-uid="'.$item->uid.'">';
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="comment_uid[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td class="kboard-comments-list-edit column-primary"><a href="'.$edit_url.'" title="'.__('Edit', 'kboard-comments').'" style="display:block">';
		echo $board->board_name;
		echo '</a>';
		echo '<button type="button" class="toggle-row"><span class="screen-reader-text">상세보기</span></button></td>';
		
		echo '<td class="kboard-comments-list-user" data-colname="'.__('Name', 'kboard-comments').'">';
		if($item->user_uid){
			echo '<a href="'.admin_url('user-edit.php?user_id='.$item->user_uid).'">'.$item->user_display.'</a>';
		}
		else{
			echo $item->user_display;
		}
		echo '</td>';
		
		echo '<td class="kboard-comments-list-content" data-colname="'.__('Content', 'kboard-comments').'">';
		echo $item->content.'<div class="kboard-comments-open"><a href="'.$this->url->getDocumentRedirect($item->content_uid).'" class="button button-small" title="'.__('Open', 'kboard-comments').'" onclick="window.open(this.href);return false;">'.__('Open', 'kboard-comments').'</a></div>';
		echo '</td>';
		
		echo '<td class="kboard-comments-list-status" data-colname="'.__('Status', 'kboard-comments').'">';
		echo '<select name="status['.$item->uid.']" onchange="kboard_comment_list_update()">';
		echo '<option value="">발행됨</option>';
		echo '<option value="pending_approval" '.$selected.'>승인 대기중</option>';
		echo '</select>';
		echo '</td>';
		
		echo '<td class="kboard-comments-list-date" data-colname="'.__('Date', 'kboard-comments').'">';
		echo '<div style="display: flex; align-items: center; gap: 5px; flex-wrap: wrap;">';
		echo '<input type="text" name="comment_date['.$item->uid.']" class="kboard-comment-content-datepicker" size="10" maxlength="10" value="'.date('Y-m-d', strtotime($item->created)).'" style="width: 95px;">';
		echo '<input type="text" name="comment_time['.$item->uid.']" class="kboard-comment-content-timepicker" size="8" maxlength="8" value="'.date('H:i:s', strtotime($item->created)).'" style="width: 75px;">';
		echo '<button type="button" class="button button-small" onclick="kboard_comment_list_update('.$item->uid.')">'.__('Update', 'kboard').'</button>';
		echo '</div>';
		echo '</td>';
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){
	?>
	<p class="search-box" style="margin: 0; display: inline-block; vertical-align: middle;">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
