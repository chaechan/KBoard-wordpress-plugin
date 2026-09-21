<div id="kboard-document">
	<div id="kboard-thumbnail-document">
		<div class="kboard-document-wrap" itemscope itemtype="http://schema.org/Article">
			<div class="kboard-title" itemprop="name">
				<h1><?php echo $content->title?></h1>
			</div>
			
			<div class="kboard-detail">
				<?php if($content->category1):?>
				<div class="detail-attr detail-category1">
					<div class="detail-name"><?php echo esc_html($content->category1)?></div>
				</div>
				<?php endif?>
				<?php if($content->category2):?>
				<div class="detail-attr detail-category2">
					<div class="detail-name"><?php echo esc_html($content->category2)?></div>
				</div>
				<?php endif?>
				<?php if($content->category3):?>
				<div class="detail-attr detail-category3">
					<div class="detail-name"><?php echo esc_html($content->category3)?></div>
				</div>
				<?php endif?>
				<?php if($content->category4):?>
				<div class="detail-attr detail-category4">
					<div class="detail-name"><?php echo esc_html($content->category4)?></div>
				</div>
				<?php endif?>
				<?php if($content->category5):?>
				<div class="detail-attr detail-category5">
					<div class="detail-name"><?php echo esc_html($content->category5)?></div>
				</div>
				<?php endif?>
				<?php if($content->option->tree_category_1):?>
				<?php for($i=1; $i<=$content->getTreeCategoryDepth(); $i++):?>
				<div class="detail-attr detail-tree-category-<?php echo $i?>">
					<div class="detail-name"><?php echo esc_html($content->option->{'tree_category_'.$i})?></div>
				</div>
				<?php endfor?>
				<?php endif?>
				<div class="detail-attr detail-writer">
					<div class="detail-name"><?php echo __('Author', 'kboard')?></div>
					<div class="detail-value"><?php echo $content->getUserDisplay()?></div>
				</div>
				<div class="detail-attr detail-date">
					<div class="detail-name"><?php echo __('Date', 'kboard')?></div>
					<div class="detail-value"><?php echo esc_html(kboard_date_format($content->date, 'Y-m-d H:i'))?></div>
				</div>
				<div class="detail-attr detail-view">
					<div class="detail-name"><?php echo __('Views', 'kboard')?></div>
					<div class="detail-value"><?php echo $content->view?></div>
				</div>
			</div>
			
			<div class="kboard-content" itemprop="description">
				<div class="content-view">
					<?php echo $content->getDocumentOptionsHTML()?>
					<?php echo $content->content?>
				</div>
			</div>
			
			<div class="kboard-document-action">
				<?php if(!$board->meta->permission_vote_hide):?>
					<div class="left">
						<button type="button" class="kboard-button-action kboard-button-like" onclick="kboard_document_like(this)" data-uid="<?php echo $content->uid?>" title="<?php echo __('Like', 'kboard')?>"><?php echo __('Like', 'kboard')?> <span class="kboard-document-like-count"><?php echo intval($content->like)?></span></button>
						<button type="button" class="kboard-button-action kboard-button-unlike" onclick="kboard_document_unlike(this)" data-uid="<?php echo $content->uid?>" title="<?php echo __('Unlike', 'kboard')?>"><?php echo __('Unlike', 'kboard')?> <span class="kboard-document-unlike-count"><?php echo intval($content->unlike)?></span></button>
					</div>
				<?php endif?>
				<div class="right">
					<button type="button" class="kboard-button-action kboard-button-print" onclick="kboard_document_print('<?php echo $url->getDocumentPrint($content->uid)?>')" title="<?php echo __('Print', 'kboard')?>"><?php echo __('Print', 'kboard')?></button>
				</div>
			</div>
			
			<?php if($content->isAttached()):?>
			<div class="kboard-attach">
				<?php foreach($content->getAttachmentList() as $key=>$attach):?>
				<button type="button" class="kboard-button-action kboard-button-download" onclick="window.location.href='<?php echo $url->getDownloadURLWithAttach($content->uid, $key)?>'" title="<?php echo sprintf(__('Download %s', 'kboard'), $attach[1])?>"><?php echo $attach[1]?></button>
				<?php endforeach?>
			</div>
			<?php endif?>
		</div>
		
		<?php if($content->visibleComments()):?>
		<div class="kboard-comments-area"><?php echo $board->buildComment($content->uid)?></div>
		<?php endif?>
		
		<div class="kboard-document-navi">
			<div class="kboard-prev-document">
				<?php
				$bottom_content_uid = $content->getPrevUID();
				if($bottom_content_uid):
				$bottom_content = new KBContent();
				$bottom_content->initWithUID($bottom_content_uid);
				?>
				<a href="<?php echo esc_url($url->getDocumentURLWithUID($bottom_content_uid))?>" title="<?php echo esc_attr(wp_strip_all_tags($bottom_content->title))?>">
					<span class="navi-arrow">«</span>
					<span class="navi-document-title kboard-thumbnail-cut-strings"><?php echo wp_strip_all_tags($bottom_content->title)?></span>
				</a>
				<?php endif?>
			</div>
			
			<div class="kboard-next-document">
				<?php
				$top_content_uid = $content->getNextUID();
				if($top_content_uid):
				$top_content = new KBContent();
				$top_content->initWithUID($top_content_uid);
				?>
				<a href="<?php echo esc_url($url->getDocumentURLWithUID($top_content_uid))?>" title="<?php echo esc_attr(wp_strip_all_tags($top_content->title))?>">
					<span class="navi-document-title kboard-thumbnail-cut-strings"><?php echo wp_strip_all_tags($top_content->title)?></span>
					<span class="navi-arrow">»</span>
				</a>
				<?php endif?>
			</div>
		</div>
		
		<div class="kboard-control">
			<div class="left">
				<a href="<?php echo esc_url($url->getBoardList())?>" class="kboard-thumbnail-button-small"><?php echo __('List', 'kboard')?></a>
				<?php if($board->isReply() && !$content->notice):?><a href="<?php echo $url->set('parent_uid', $content->uid)->set('mod', 'editor')->toString()?>" class="kboard-thumbnail-button-small"><?php echo __('Reply', 'kboard')?></a><?php endif?>
			</div>
			<?php if($content->isEditor() || $board->permission_write=='all'):?>
			<div class="right">
				<a href="<?php echo esc_url($url->getContentEditor($content->uid))?>" class="kboard-thumbnail-button-small"><?php echo __('Edit', 'kboard')?></a>
				<a href="<?php echo esc_url($url->getContentRemove($content->uid))?>" class="kboard-thumbnail-button-small" onclick="return confirm('<?php echo __('Are you sure you want to delete?', 'kboard')?>');"><?php echo __('Delete', 'kboard')?></a>
			</div>
			<?php endif?>
		</div>
		
		<?php if($board->contribution() && !$board->meta->always_view_list):?>
		<div class="kboard-thumbnail-poweredby">
			<a href="https://www.cosmosfarm.com/products/kboard" onclick="window.open(this.href);return false;" title="<?php echo __('KBoard is the best community software available for WordPress', 'kboard')?>">Powered by KBoard</a>
		</div>
		<?php endif?>
	</div>
</div>
<script>
	function kboardToggleAuthorMenu(element) {
		const menu = element.nextElementSibling;
		const allMenus = document.querySelectorAll('.kboard-author-menu');
		allMenus.forEach(m => {
			if (m !== menu) m.style.display = 'none';
		});
		menu.style.display = (menu.style.display === 'block') ? 'none' : 'block';
	}

	// 바깥 클릭 시 닫힘
	document.addEventListener('click', function(e) {
		const target = e.target;
		if (!target.closest('.kboard-author-dropdown')) {
			document.querySelectorAll('.kboard-author-menu').forEach(menu => {
				menu.style.display = 'none';
			});
		}
	});
</script>
