<!-- 
This file contains the HTML structure for the Medium widget, which displays the latest posts fetched from the RSS feed.
-->

<div class="medium-widget">
    <h2 class="medium-widget-title">Latest Medium Posts</h2>
    <ul class="medium-posts-list">
        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>
                <li class="medium-post-item">
                    <a href="<?php echo esc_url($post['link']); ?>" target="_blank" rel="noopener noreferrer">
                        <h3 class="medium-post-title"><?php echo esc_html($post['title']); ?></h3>
                        <p class="medium-post-excerpt"><?php echo esc_html($post['excerpt']); ?></p>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="medium-post-item">No posts available.</li>
        <?php endif; ?>
    </ul>
</div>