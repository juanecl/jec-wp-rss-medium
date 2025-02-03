<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Obtener los posts desde la base de datos de WordPress
$args = [
    'post_type' => 'post',
    'posts_per_page' => 10,
    'meta_key' => 'medium_post_link', // Asegúrate de que este meta_key exista
];
$query = new WP_Query($args);
$posts = $query->posts;

if (empty($posts)) {
    return '<p>' . __('No posts found.', 'jec-medium') . '</p>';
}

shuffle($posts);
?>
<div class="wrap bg-dark-muted section-padding">
    <h2 class="text-center mb-4"><?php _e('Articles', 'jec-medium'); ?></h2>
    <div id="mediumCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($posts as $index => $post): ?>
                <?php
                $image = get_post_meta($post->ID, 'medium_post_image', true);
                $link = get_post_meta($post->ID, 'medium_post_link', true);
                ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="card mb-4 custom-card bg-secondary-muted text-white">
                        <div class="card-img-top-wrapper">
                            <img src="<?php echo esc_url($image); ?>" class="d-block w-100 card-img" alt="<?php echo esc_attr($post->post_title); ?>">
                        </div>
                        <div class="card-body d-flex flex-column flex-grow-1 justify-content-end pt-5">
                            <h5 class="card-title"><a href="<?php echo esc_url($link); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a></h5>
                            <p class="card-text"><?php echo esc_html($post->post_excerpt); ?></p>
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