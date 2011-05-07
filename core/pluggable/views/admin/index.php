<h2><?php echo MvcInflector::pluralize($model->name); ?></h2>

<form id="posts-filter" action="<?php echo MvcRouter::admin_url(array('controller' => MvcInflector::tableize($model->name))); ?>" method="get">

	<p class="search-box">
		<label class="screen-reader-text" for="post-search-input">Search:</label>
		<input type="hidden" name="page" value="<?php echo MvcInflector::tableize($model->name); ?>" />
		<input type="text" name="q" value="<?php empty($params['q']) ? '' : $params['q']; ?>" />
		<input type="submit" value="Search" class="button" />
	</p>

</form>

<div class="tablenav">

	<div class="tablenav-pages">
	
		<?php echo paginate_links($pagination); ?>
	
	</div>

</div>

<div class="clear"></div>

<table class="widefat post fixed" cellspacing="0">

	<thead>
		<?php echo $helper->admin_header_cells($model); ?>
	</thead>

	<tfoot>
		<?php echo $helper->admin_header_cells($model); ?>
	</tfoot>

	<tbody>
		<?php echo $helper->admin_table_cells($model, $objects); ?>
	</tbody>
	
</table>

<div class="tablenav">

	<div class="tablenav-pages">
	
		<?php echo paginate_links($pagination); ?>
	
	</div>

</div>

<br class="clear" />