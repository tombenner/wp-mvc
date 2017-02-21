<h2><?php echo MvcInflector::pluralize_titleize($model->name); ?></h2>

<form id="posts-filter" action="<?php echo MvcRouter::admin_url(); ?>" method="get">

    <p class="search-box">
        <label class="screen-reader-text" for="post-search-input"><?php _e("Search", 'wpmvc'); ?>:</label>
        <input type="hidden" name="page" value="<?php echo MvcRouter::admin_page_param($model->name); ?>" />
        <input type="text" name="q" value="<?php echo empty($params['q']) ? '' : $params['q']; ?>" />
        <input type="submit" value="<?php _e("Search", 'wpmvc'); ?>" class="button" />
    </p>

</form>

<div class="tablenav">

    <div class="tablenav-pages">
    
        <?php echo paginate_links($pagination); ?>
    
    </div>

</div>

<div class="clear"></div>

<table class="widefat post fixed striped" cellspacing="0">

    <thead>
        <?php echo $helper->admin_header_cells($this); ?>
    </thead>

    <tfoot>
        <?php echo $helper->admin_header_cells($this); ?>
    </tfoot>

    <tbody>
        <?php echo $helper->admin_table_cells($this, $objects); ?>
    </tbody>
    
</table>

<div class="tablenav">

    <div class="tablenav-pages">
    
        <?php echo paginate_links($pagination); ?>
    
    </div>

</div>

<br class="clear" />
