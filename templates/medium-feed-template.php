<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get configuration from Customizer
$posts_count = Medium_Customizer::get_posts_count();
$show_images = Medium_Customizer::show_images();
$show_categories = Medium_Customizer::show_categories();
$show_excerpt = Medium_Customizer::show_excerpt();
$button_color = Medium_Customizer::get_button_color();
$outside_text_color = Medium_Customizer::get_outside_text_color();

// Obtener los posts desde la base de datos de WordPress
$args = [
    'post_type' => 'post',
    'posts_per_page' => $posts_count,
    'meta_key' => 'medium_post_link', // Asegúrate de que este meta_key exista
];
$query = new WP_Query($args);
$posts = $query->posts;

if (empty($posts)) {
    return '<p>' . __('No posts found.', 'jec-medium') . '</p>';
}

shuffle($posts);
?>
<div class="wrap bg-dark-muted section-padding" style="--jec-medium-button-color: <?php echo esc_attr($button_color); ?>; --jec-medium-outside-text-color: <?php echo esc_attr($outside_text_color); ?>;">
    <h2 class="text-center mb-4 jec-medium-outside-text"><?php _e('Articles', 'jec-medium'); ?></h2>
    <div id="mediumCarousel" class="carousel slide mb-5" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($posts as $index => $post): ?>
                <?php
                $featured_image = get_the_post_thumbnail_url($post->ID, 'large');
                $fallback_image = get_post_meta($post->ID, 'medium_post_image', true);
                $image = !empty($featured_image) ? $featured_image : $fallback_image;
                $link = get_post_meta($post->ID, 'medium_post_link', true);
                ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <div class="card mb-4 custom-card bg-secondary-muted text-white">
                        <?php if ($show_images && !empty($image)): ?>
                        <div class="card-img-top-wrapper">
                            <img src="<?php echo esc_url($image); ?>" class="d-block w-100 card-img" alt="<?php echo esc_attr($post->post_title); ?>">
                        </div>
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column flex-grow-1 justify-content-end pt-5">
                            <h5 class="card-title"><a href="<?php echo esc_url($link); ?>" target="_blank"><?php echo esc_html($post->post_title); ?></a></h5>
                            <?php if ($show_excerpt): ?>
                            <p class="card-text"><?php echo esc_html($post->post_excerpt); ?></p>
                            <?php endif; ?>
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