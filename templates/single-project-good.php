<?php

/**
 * The Definitive Dubai Project Template
 * Displays ALL CPT Meta Fields: Financials, Specs, Gallery, Video, 360 Tour, and Map.
 */

get_header();

$post_id = get_the_ID();

/**
 * DATA LAYER: Pulling all fields from your Meta Box
 */
$get_meta = function ($key) use ($post_id) {
  return get_post_meta($post_id, $key, true);
};

$price          = $get_meta('dvp_price_from');
$handover       = $get_meta('dvp_handover_date');
$payment_plan   = $get_meta('dvp_payment_plan');
$down_payment   = $get_meta('dvp_down_payment');
$total_units    = $get_meta('dvp_total_units');
$ownership      = $get_meta('dvp_ownership');
$height         = $get_meta('dvp_building_height');
$plot_size      = $get_meta('dvp_plot_size');
$video_url      = $get_meta('dvp_video_url');
$tour_url       = $get_meta('dvp_360_tour');
$lat            = $get_meta('dvp_latitude');
$lng            = $get_meta('dvp_longitude');
$landmarks      = $get_meta('dvp_distance_landmarks');
$gallery_ids    = $get_meta('dvp_gallery_ids');
$brochure_id    = $get_meta('dvp_brochure_id');
$floorplan_id   = $get_meta('dvp_floorplan_id');

// Taxonomies
$developers = get_the_term_list($post_id, 'dvp_developer', '', ', ', '');
$districts  = get_the_term_list($post_id, 'dvp_district', '', ', ', '');
$amenities  = get_the_terms($post_id, 'dvp_amenities');
?>

<style>
  :root {
    --dvp-gold: #c5a059;
    --dvp-dark: #111;
    --dvp-accent: #2563eb;
  }

  .dvp-engine-wrap {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: 'Inter', -apple-system, sans-serif;
    color: #333;
  }

  /* Hero Header */
  .dvp-hero-header {
    position: relative;
    height: 60vh;
    /* border-radius: 24px; */
    overflow: hidden;
    margin-bottom: 40px;
    background: #000;
    display: flex;
    align-items: flex-end;
  }

  .dvp-hero-header img.main-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0.7;
  }

  .dvp-hero-info {
    position: relative;
    z-index: 2;
    padding: 60px;
    color: #fff;
    width: 100%;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
  }

  .dvp-hero-info h1 {
    font-size: 52px;
    margin: 0;
    font-weight: 800;
  }

  /* Layout Content */
  .dvp-main-grid {
    display: grid;
    grid-template-columns: 1fr 380px;
    gap: 40px;
  }

  /* Highlights Card */
  .dvp-stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 1px;
    background: #eee;
    border: 1px solid #eee;
    /* border-radius: 12px; */
    overflow: hidden;
    margin-bottom: 40px;
  }

  .dvp-stat-card {
    background: #fff;
    padding: 20px;
    text-align: center;
  }

  .dvp-stat-card small {
    display: block;
    font-size: 11px;
    text-transform: uppercase;
    color: #888;
    letter-spacing: 1px;
    margin-bottom: 5px;
  }

  .dvp-stat-card span {
    font-size: 16px;
    font-weight: 700;
    color: var(--dvp-dark);
  }

  /* Media Tabs */
  .dvp-media-tabs {
    margin: 40px 0;
  }

  .dvp-tabs-nav {
    display: flex;
    gap: 20px;
    border-bottom: 1px solid #ddd;
    margin-bottom: 20px;
  }

  .dvp-tab-trigger {
    padding: 15px 0;
    border: none;
    background: none;
    font-weight: 700;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    color: #888;
  }

  .dvp-tab-trigger.active {
    color: var(--dvp-gold);
    border-bottom-color: var(--dvp-gold);
  }

  .dvp-tab-panel {
    display: none;
    /* border-radius: 16px; */
    overflow: hidden;
    height: 500px;
    background: #f4f4f4;
  }

  .dvp-tab-panel.active {
    display: block;
  }

  .dvp-tab-panel iframe {
    width: 100%;
    height: 100%;
    border: none;
  }

  /* Gallery Lightbox Grid */
  .dvp-gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 15px;
  }

  .dvp-gallery-grid a {
    height: 200px;
    /* border-radius: 12px; */
    overflow: hidden;
  }

  .dvp-gallery-grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: 0.3s;
  }

  .dvp-gallery-grid a:hover img {
    transform: scale(1.05);
  }

  /* Sidebar Conversion Card */
  .dvp-sticky-sidebar {
    position: sticky;
    top: 40px;
    background: #fff;
    border: 1px solid #e5e7eb;
    /* border-radius: 20px; */
    padding: 30px;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  }

  .dvp-price-tag {
    font-size: 32px;
    font-weight: 800;
    color: var(--dvp-dark);
    margin-bottom: 5px;
  }

  .dvp-btn-action {
    display: block;
    text-align: center;
    padding: 16px;
    /* border-radius: 12px; */
    font-weight: 700;
    text-decoration: none;
    margin-top: 15px;
    transition: 0.2s;
  }

  .dvp-btn-black {
    background: var(--dvp-dark);
    color: #fff;
  }

  .dvp-btn-outline {
    border: 2px solid var(--dvp-dark);
    color: var(--dvp-dark);
  }

  /* Amenities Pills */
  .dvp-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 20px;
  }

  .dvp-pill {
    background: #f3f4f6;
    padding: 8px 16px;
    /* border-radius: 100px; */
    font-size: 14px;
    font-weight: 500;
  }

  @media (max-width: 900px) {
    .dvp-main-grid {
      grid-template-columns: 1fr;
    }

    .dvp-stats-grid {
      grid-template-columns: repeat(2, 1fr);
    }
  }
</style>

<div class="dvp-engine-wrap">

  <header class="dvp-hero-header">
    <?php if (has_post_thumbnail()): the_post_thumbnail('full', ['class' => 'main-bg']);
    endif; ?>
    <div class="dvp-hero-info">
      <h1><?php the_title(); ?></h1>
      <p>Developer: <?php echo $developers ?: 'Premium Developer'; ?> | <?php echo $districts; ?></p>
    </div>
  </header>

  <div class="dvp-stats-grid">
    <div class="dvp-stat-card"><small>Handover</small><span><?php echo $handover; ?></span></div>
    <div class="dvp-stat-card"><small>Ownership</small><span><?php echo $ownership ?: 'Freehold'; ?></span></div>
    <div class="dvp-stat-card"><small>Bldg Height</small><span><?php echo $height; ?></span></div>
    <div class="dvp-stat-card"><small>Down Payment</small><span><?php echo $down_payment; ?>%</span></div>
  </div>

  <div class="dvp-main-grid">

    <div class="dvp-main-content">

      <section class="dvp-section">
        <h3 style="font-size:24px; margin-bottom:20px;">Project Overview</h3>
        <div style="font-size:18px; line-height:1.7; color:#4b5563;">
          <?php the_content(); ?>
        </div>
      </section>

      <?php if ($amenities): ?>
        <section style="margin-top:50px;">
          <h3>Premium Amenities</h3>
          <div class="dvp-pills">
            <?php foreach ($amenities as $a) echo '<span class="dvp-pill">✓ ' . $a->name . '</span>'; ?>
          </div>
        </section>
      <?php endif; ?>

      <?php if ($video_url || $tour_url): ?>
        <div class="dvp-media-tabs">
          <div class="dvp-tabs-nav">
            <?php if ($video_url): ?><button class="dvp-tab-trigger active" onclick="dvpTab(event, 'dvp-video')">Cinematic Video</button><?php endif; ?>
            <?php if ($tour_url): ?><button class="dvp-tab-trigger <?php echo !$video_url ? 'active' : ''; ?>" onclick="dvpTab(event, 'dvp-360')">360° Virtual Tour</button><?php endif; ?>
          </div>

          <?php if ($video_url): ?>
            <div id="dvp-video" class="dvp-tab-panel active">
              <iframe src="<?php echo str_replace('watch?v=', 'embed/', $video_url); ?>" allowfullscreen></iframe>
            </div>
          <?php endif; ?>

          <?php if ($tour_url): ?>
            <div id="dvp-360" class="dvp-tab-panel <?php echo !$video_url ? 'active' : ''; ?>">
              <iframe src="<?php echo esc_url($tour_url); ?>"></iframe>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <section style="margin-top:50px;">
        <h3 style="margin-bottom:20px;">Project Gallery</h3>
        <div class="dvp-gallery-grid">
          <?php
          if ($gallery_ids) {
            $ids = explode(',', $gallery_ids);
            foreach ($ids as $id) {
              $full_url  = wp_get_attachment_image_url($id, 'full');
              $thumb_url = wp_get_attachment_image_url($id, 'large');
              $caption   = wp_get_attachment_caption($id);

              if ($full_url) : ?>
                <a href="<?php echo esc_url($full_url); ?>"
                  class="dvp-gallery-item"
                  data-glightbox="title: <?php echo esc_attr(get_the_title()); ?>; description: <?php echo esc_attr($caption); ?>">
                  <img src="<?php echo esc_url($thumb_url); ?>"
                    loading="lazy"
                    style="width:100%; height:220px; object-fit:cover; border: 1px solid #eee;" />
                </a>
          <?php endif;
            }
          }
          ?>
        </div>
      </section>

      <?php if ($lat && $lng): ?>
        <section style="margin-top:60px;">
          <h3>Location & Connectivity</h3>
          <div style="height:400px; overflow:hidden; margin-bottom:20px; border:1px solid #eee;width:100%;">
            <iframe style="height:100%;width:100%;" src="https://maps.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng; ?>&z=15&output=embed"></iframe>
          </div>
          <div style="background:#f9fafb; padding:25px;">
            <h4 style="margin-top:0">Proximity to Landmarks</h4>
            <p style="white-space: pre-line; color:#6b7280;"><?php echo esc_html($landmarks); ?></p>
          </div>
        </section>
      <?php endif; ?>

    </div>

    <aside class="dvp-sidebar">
      <div class="dvp-sticky-sidebar">
        <small style="color:#888; text-transform:uppercase;">Prices Starting From</small>
        <div class="dvp-price-tag">AED <?php echo number_format($price); ?></div>

        <div style="margin: 20px 0; font-size: 14px; border-top: 1px solid #eee; padding-top:20px;">
          <p><strong>Payment Plan:</strong> <?php echo $payment_plan; ?></p>
          <p><strong>Total Units:</strong> <?php echo $total_units; ?></p>
          <p><strong>Plot Size:</strong> <?php echo $plot_size; ?></p>
        </div>

        <?php if ($brochure_id): ?>
          <a href="<?php echo wp_get_attachment_url($brochure_id); ?>" class="dvp-btn-action dvp-btn-black" target="_blank">Download Brochure</a>
        <?php endif; ?>

        <?php if ($floorplan_id): ?>
          <a href="<?php echo wp_get_attachment_url($floorplan_id); ?>" class="dvp-btn-action dvp-btn-outline" target="_blank">View Floor Plans</a>
        <?php endif; ?>

        <a href="https://wa.me/YOUR_NUMBER?text=Interested%20in%20<?php echo urlencode(get_the_title()); ?>" class="dvp-btn-action" style="background:#25d366; color:#fff;">WhatsApp Expert</a>
      </div>
    </aside>
  </div>
</div>

<script>
  /** Media Tab Switcher Logic **/
  function dvpTab(evt, tabId) {
    var i, panels, triggers;
    panels = document.getElementsByClassName("dvp-tab-panel");
    for (i = 0; i < panels.length; i++) {
      panels[i].style.display = "none";
    }
    triggers = document.getElementsByClassName("dvp-tab-trigger");
    for (i = 0; i < triggers.length; i++) {
      triggers[i].className = triggers[i].className.replace(" active", "");
    }
    document.getElementById(tabId).style.display = "block";
    evt.currentTarget.className += " active";
  }
</script>

<?php get_footer(); ?>