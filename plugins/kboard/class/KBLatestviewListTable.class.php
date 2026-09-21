<?php
/**
 * KBoard 최신글 모아보기 테이블
 * @link www.cosmosfarm.com
 * @copyright Copyright 2021 Cosmosfarm. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
 */
class KBLatestviewListTable extends WP_List_Table {
	
	var $latestview;
	var $list;
	
	public function __construct(){
		parent::__construct();
		
		$this->latestview = new KBLatestview();
		$this->list = new KBLatestviewList();
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
		$classes[] = 'kboard-latestview';
		return $classes;
	}
	
	public function no_items(){
		echo __('No latestview found.', 'kboard');
	}
	
	public function get_columns(){
		return array(
				'cb' => '<input type="checkbox">',
				'name' => __('이름', 'kboard'),
				'skin' => __('스킨', 'kboard'),
				'rpp' => __('게시글 표시 수', 'kboard'),
				'Sort' => __('정렬 순서', 'kboard'),
				'date' => __('생성일', 'kboard'),
		);
	}
	
	function get_bulk_actions(){
		return array(
				'delete' => __('Delete Permanently', 'kboard'),
		);
	}
	
	public function display_rows(){
		foreach($this->items as $item){
			$this->latestview->initWithRow($item);
			$this->single_row($this->latestview);
		}
	}
	
	public function single_row($item){
		
		echo '<tr data-uid="'.$item->uid.'">';
		
		echo '<th scope="row" class="check-column">';
		echo '<input type="checkbox" name="latestview_uid[]" value="'.$item->uid.'">';
		echo '</th>';
		
		echo '<td><a href="'.admin_url("admin.php?page=kboard_latestview&latestview_uid={$item->uid}").'" title="'.__('Edit', 'kboard').'" style="display:block">';
		echo $item->name;
		echo '</a></td>';
		
		echo '<td>';
		echo $item->skin;
		echo '</td>';
		
		echo '<td>';
		echo "{$item->rpp}개";
		echo '</td>';
		
		echo '<td>';
		echo __(ucfirst($item->sort), 'kboard');
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
