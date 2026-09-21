<?php
/**
 * KBoard 게시판 리스트 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBoardListTable extends WP_List_Table {
	
	var $board;
	var $list;
	
	public function __construct(){
		parent::__construct();
		
		$this->board = new KBoard();
		$this->list = new KBoardList();
	}
	
	public function prepare_items(){
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = array();
		$this->_column_headers = array($columns, $hidden, $sortable);
		
		$keyword = isset($_GET['s'])?esc_attr($_GET['s']):'';
		
		$this->list->rpp = 20;
		$this->list->page = $this->get_pagenum();
		$this->list->initWithKeyword($keyword);
		$this->items = $this->list->resource;
		
		$this->set_pagination_args(array('total_items'=>$this->list->total, 'per_page'=>$this->list->rpp));
	}
	
	public function get_table_classes(){
		$classes = parent::get_table_classes();
		$classes[] = 'kboard';
		$classes[] = 'kboard-list';
		return $classes;
	}
	
	public function no_items(){
		echo __('No forum found.', 'kboard');
	}
	
	public function get_columns(){
		return array(
				'cb' => '<input type="checkbox">',
				'thumbnail' => __('썸네일', 'kboard'),
				'board_name' => __('게시판 이름', 'kboard'),
				'auto_page' => __('설치된 페이지', 'kboard'),
				'skin' => __('스킨', 'kboard'),
				'permission_read' => __('읽기권한', 'kboard'),
				'permission_write' => __('쓰기권한', 'kboard'),
				'permission_comments_write' => __('댓글쓰기권한', 'kboard'),
				'date' => __('생성일', 'kboard'),
		);
	}
	
	function get_bulk_actions(){
		return array(
				'reset_total' => '게시글 숫자 초기화',
				'truncate' => '모든 게시글 비우기',
				'delete' => __('Delete Permanently', 'kboard'),
		);
	}
	
	public function display_rows(){
		foreach($this->items as $item){
			$this->board->initWithRow($item);
			$this->single_row($this->board);
		}
	}
	
	public function single_row($item){
		$edit_url = admin_url("admin.php?page=kboard_list&board_id={$item->uid}");
		
		echo '<tr data-board-id="' . $item->uid . '">';
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="board_id[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td><a href="'.$edit_url.'" title="'.__('편집', 'kboard').'" style="display:block">';
		echo '<img src="' . KBoardSkin::getInstance()->url($item->skin, 'thumbnail.png') . '" style="width:100px;height:100px;" alt="">';
		echo '</a></td>';
		
		echo '<td><a href="' . $edit_url . '" title="'.__('편집', 'kboard').'" style="display:block">';
		echo $item->board_name;
		echo '</a></td>';
		
		echo '<td>';
		if($item->meta->auto_page || $item->meta->latest_target_page){
			$page_id = $item->meta->auto_page ? $item->meta->auto_page : $item->meta->latest_target_page;
			$post = get_post($page_id);
			echo '<a href="' . get_permalink($post) . '" title="'.__('페이지 보기', 'kboard').'" style="display:block">';
			if(isset($post->post_title)){
				echo $post->post_title;
			}
			echo '</a>';
		}
		else{
			echo __('페이지 연결 없음', 'kboard');
		}
		echo '</td>';
		
		echo '<td>';
		echo $item->skin;
		echo '</td>';
		
		echo '<td>';
		echo kboard_permission($item->permission_read);
		echo '</td>';
		
		echo '<td>';
		echo kboard_permission($item->permission_write);
		echo '</td>';
		
		echo '<td>';
		if(!$item->meta->permission_comment_write){
			echo kboard_permission('all');
		}
		else if($item->meta->permission_comment_write == 1){
			echo kboard_permission('author');
		}
		else if($item->meta->permission_comment_write == 'roles'){
			echo kboard_permission('roles');
		}
		echo '</td>';
		
		echo '<td>';
		echo esc_html(kboard_date_format($item->created, 'Y-m-d H:i:s'));
		echo '</td>';
		
		echo '</tr>';
	}
	
	public function search_box($text, $input_id){
	?>
	<p class="search-box">
		<input type="search" id="<?php echo $input_id?>" name="s" value="<?php _admin_search_query()?>">
		<?php submit_button($text, 'button', false, false, array('id'=>'search-submit'))?>
	</p>
	<?php }
}
?>
