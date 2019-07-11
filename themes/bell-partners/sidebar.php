<div class="sidebar">
    <div class="categories">
        <h2>Categories</h2>
        <ul class="categories-list">
            <?php wp_list_categories( array('title_li' => '') ); ?>
        </ul>
    </div>
    <div class="recent-posts">
        <h2>Recent Posts</h2>
        <ul class="recent-posts-list">
            <?php
            $recent_posts = wp_get_recent_posts();
            foreach( $recent_posts as $recent ){
                echo '<li><a href="' . get_permalink($recent["ID"]) . '" title="Look '.esc_attr($recent["post_title"]).'" >' .   $recent["post_title"].'</a> </li> ';
            }
            ?>
        </ul>
    </div>
    <div class="archives">
        <h2>Archives</h2>
        <ul class="archives-list">
            <?php wp_get_archives(); ?>
        </ul>
    </div>
</div>