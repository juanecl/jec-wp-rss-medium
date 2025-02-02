<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
shuffle($posts);
?>
<div class="wrap bg-dark-muted pt-5 pb-3">
    <h2 class="text-center mb-4"><?php _e('Articles', 'jec-medium'); ?></h2>
    <div id="mediumCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($posts as $index => $post): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="card mb-4 custom-card bg-secondary-muted text-white">
                        <div class="card-img-top-wrapper">
                            <img src="<?php echo esc_url($post['image']); ?>" class="d-block w-100 card-img" alt="<?php echo esc_attr($post['title']); ?>">
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1 justify-content-end pt-5">
                            <h5 class="card-title"><a href="<?php echo esc_url($post['link']); ?>" target="_blank"><?php echo esc_html($post['title']); ?></a></h5>
                            <p class="card-text"><?php echo esc_html($post['description']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#mediumCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden"><?php _e('Previous', 'jec-medium'); ?></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#mediumCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden"><?php _e('Next', 'jec-medium'); ?></span>
        </button>
    </div>
</div>