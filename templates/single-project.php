<?php

/**
 * Lemar Properties - Luxury Master Template
 * Fixed for Twenty Twenty-Five Block Theme Integration
 */

// 1. Standard WordPress Head (Replaces get_header for Block Themes)
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <div class="wp-site-blocks">
    <header class="wp-block-template-part">
      <?php block_header_area(); ?>
    </header>

    <main class="wp-block-group">

      <?php
      $post_id = get_the_ID();
      $get_meta = function ($key) use ($post_id) {
        return get_post_meta($post_id, $key, true);
      };

      // Data Layer
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

      $developers = get_the_term_list($post_id, 'dvp_developer', '', ', ', '');
      $districts  = get_the_term_list($post_id, 'dvp_district', '', ', ', '');
      $amenities  = get_the_terms($post_id, 'dvp_amenities');
      ?>

      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@500;700&family=Quicksand:wght@400;500;700&display=swap" rel="stylesheet">

      <style>
        :root {
          --lemar-bg: #f2ede4;
          --lemar-gold: #72592d;
          --lemar-dark: #1a1c1e;
          --lemar-border: #e0d8cc;
          --font-heading: 'League Spartan', sans-serif;
          --font-body: 'Quicksand', sans-serif;
        }

        strong {
          font-weight: 900;
        }

        p {
          font-weight: 400;
        }

        /* Scope styles only to our custom article content */
        .lemar-project-page {
          background-color: var(--lemar-bg);
          color: var(--lemar-dark);
          font-family: var(--font-body);
          font-size: 22px;
          line-height: 1.6;
          -webkit-font-smoothing: antialiased;
        }

        .lemar-body-text {
          color: #2b2d2f;
        }

        .lemar-project-page h1,
        .lemar-project-page h2,
        .lemar-project-page h3,
        .lemar-project-page h4,
        .lemar-price,
        .lemar-btn,
        .lemar-tab-trigger,
        .lemar-spec-item small,
        .spec-value {
          font-family: var(--font-heading) !important;
          font-weight: 700;
          text-transform: uppercase;
          margin: 0;
        }

        .lemar-project-page h3 {
          color: var(--lemar-gold);
        }

        .lemar-container {
          max-width: 1200px;
          margin: 0 auto;
          padding: 0 30px;
        }

        .lemar-hero {
          position: relative;
          height: 75vh;
          background: #000;
          display: flex;
          align-items: center;
          justify-content: center;
          text-align: center;
          overflow: hidden;
          margin-bottom: 30px;
        }

        .lemar-hero img.hero-img {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          object-fit: cover;
          opacity: 0.6;
        }

        .lemar-hero-content {
          position: relative;
          z-index: 10;
          color: #fff;
        }

        .lemar-hero-content h1 {
          font-size: clamp(40px, 6vw, 85px);
          line-height: 0.95;
          letter-spacing: 2px;
        }

        .lemar-hero-content p {
          font-size: 18px;
          letter-spacing: 4px;
          margin-top: 20px;
          opacity: 0.9;
        }

        .lemar-specs-grid {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          border: 1px solid var(--lemar-border);
          margin-bottom: 40px;
        }

        .lemar-spec-item {
          padding: 30px 15px;
          border-right: 1px solid var(--lemar-border);
          text-align: center;
        }

        .lemar-spec-item:last-child {
          border-right: none;
        }

        .lemar-spec-item small {
          color: var(--lemar-gold);
          letter-spacing: 2px;
          font-size: 13px;
          display: block;
          margin-bottom: 5px;
        }

        .lemar-spec-item .spec-value {
          font-size: 24px;
          color: var(--lemar-dark);
        }

        .lemar-main-split {
          display: grid;
          grid-template-columns: 1fr 400px;
          gap: 60px;
          align-items: flex-start;
          position: relative;
        }

        .lemar-main-body {
          width: 100%;
        }

        .lemar-content-section {
          width: 100%;
          margin-bottom: 50px;
        }

        .lemar-title-wrap {
          margin-bottom: 15px;
        }

        .lemar-title-wrap h2 {
          font-size: 28px;
          letter-spacing: 3px;
        }

        .lemar-title-wrap h2 span {
          color: var(--lemar-gold);
        }

        .lemar-tabs-nav {
          width: 100%;
          display: flex;
          gap: 40px;
          margin-bottom: 20px;
          border-bottom: 1px solid var(--lemar-border);
        }

        .lemar-tab-trigger {
          border: none;
          background: none;
          padding: 15px 0;
          font-size: 15px;
          cursor: pointer;
          color: #888;
          border-bottom: 3px solid transparent;
          transition: 0.3s;
        }

        .lemar-tab-trigger.active {
          color: var(--lemar-dark);
          border-bottom-color: var(--lemar-gold);
        }

        .lemar-tab-panel {
          display: none;
          width: 100%;
          height: 550px;
          background: #000;
          border: 1px solid var(--lemar-border);
          overflow: hidden;
          position: relative;
        }

        .lemar-tab-panel.active {
          display: block;
        }

        .lemar-tab-panel iframe {
          position: absolute;
          top: 0;
          left: 0;
          width: 100% !important;
          height: 100% !important;
          border: none;
        }

        .lemar-gallery {
          display: grid;
          grid-template-columns: repeat(2, 1fr);
          gap: 12px;
        }

        .lemar-gallery-link {
          aspect-ratio: 5/3;
          border: 1px solid var(--lemar-border);
          overflow: hidden;
          display: block;
        }

        .lemar-gallery-link img {
          width: 100%;
          height: 100%;
          object-fit: cover;
          transition: 0.6s ease;
        }

        .lemar-gallery-link:hover img {
          transform: scale(1.06);
        }

        .lemar-map-wrap {
          width: calc(100% - 2px);
          height: 450px;
          border: 1px solid var(--lemar-border);
          overflow: hidden;
        }

        .lemar-map-iframe {
          width: 100% !important;
          height: 100% !important;
          filter: grayscale(1) contrast(1.1);
          border: none;
        }

        .lemar-sidebar {
          position: sticky;
          top: 40px;
          z-index: 10;
        }

        .lemar-sidebar-card {
          border: 1px solid var(--lemar-border);
          padding: 40px;
          text-align: center;
          background-color: var(--lemar-bg);
        }

        .lemar-price {
          font-size: 42px;
          margin-bottom: 25px;
        }

        .lemar-sidebar-details {
          font-size: 18px;
          line-height: 2;
          border-top: 1px solid var(--lemar-border);
          padding-top: 25px;
          margin-bottom: 30px;
        }

        .lemar-btn {
          display: block;
          width: 100%;
          padding: 20px;
          border: 1.5px solid var(--lemar-dark);
          text-decoration: none;
          font-size: 14px;
          margin-bottom: 12px;
          transition: 0.3s;
          box-sizing: border-box;
          text-align: center;
        }

        .lemar-btn-solid {
          background: var(--lemar-dark);
          color: #fff;
        }

        .lemar-btn-gold {
          background: var(--lemar-gold);
          color: #fff;
          border-color: var(--lemar-gold);
        }

        @media (max-width: 1024px) {
          .lemar-main-split {
            display: flex;
            /* Turns the container into a flexbox */
            flex-direction: column-reverse;
            /* Flips the order: bottom item (sidebar) moves to top */
            gap: 40px;
            /* Maintains spacing between the flipped elements */
          }

          .lemar-sidebar {
            width: 100%;
            /* Ensures pricing card takes full width on mobile */
            position: static;
            /* Disables 'sticky' behavior for mobile */
          }

          .lemar-specs-grid {
            grid-template-columns: repeat(2, 1fr);
          }

          .lemar-map-wrap {
            height: 200px;
          }

          .lemar-tab-panel {
            height: 220px;
          }
        }
      </style>

      <article class="lemar-project-page">
        <section class="lemar-hero">
          <?php if (has_post_thumbnail()): the_post_thumbnail('full', ['class' => 'hero-img']);
          endif; ?>
          <div class="lemar-hero-content">
            <small>Dubai Global Landmark</small>
            <h1><?php the_title(); ?></h1>
            <p><?php echo $districts; ?> • Developed by <?php echo $developers; ?></p>
          </div>
        </section>

        <div class="lemar-container">
          <div class="lemar-specs-grid">
            <div class="lemar-spec-item"><small>Handover</small><span class="spec-value"><?php echo $handover; ?></span></div>
            <div class="lemar-spec-item"><small>Ownership</small><span class="spec-value"><?php echo $ownership ?: 'Freehold'; ?></span></div>
            <div class="lemar-spec-item"><small>Height</small><span class="spec-value"><?php echo $height; ?></span></div>
            <div class="lemar-spec-item"><small>Down Payment</small><span class="spec-value"><?php echo $down_payment; ?>%</span></div>
          </div>

          <div class="lemar-main-split">
            <div class="lemar-main-body">
              <section class="lemar-content-section">
                <div class="lemar-title-wrap">
                  <h2>The <span>Overview</span></h2>
                </div>
                <div class="lemar-body-text" style="font-weight: 400;"><?php the_content(); ?></div>
              </section>

              <?php if ($video_url || $tour_url): ?>
                <section class="lemar-content-section">
                  <div class="lemar-tabs-nav">
                    <?php if ($video_url): ?>
                      <button class="lemar-tab-trigger active" onclick="lemarTab(event, 'lemar-video')">Film Presentation</button>
                    <?php endif; ?>
                    <?php if ($tour_url): ?>
                      <button class="lemar-tab-trigger <?php echo !$video_url ? 'active' : ''; ?>" onclick="lemarTab(event, 'lemar-360')">360° Experience</button>
                    <?php endif; ?>
                  </div>
                  <?php if ($video_url): ?>
                    <div id="lemar-video" class="lemar-tab-panel active">
                      <iframe src="<?php echo str_replace('watch?v=', 'embed/', $video_url); ?>" allowfullscreen></iframe>
                    </div>
                  <?php endif; ?>
                  <?php if ($tour_url): ?>
                    <div id="lemar-360" class="lemar-tab-panel <?php echo !$video_url ? 'active' : ''; ?>">
                      <iframe src="<?php echo esc_url($tour_url); ?>"></iframe>
                    </div>
                  <?php endif; ?>
                </section>
              <?php endif; ?>

              <section class="lemar-content-section">
                <div class="lemar-title-wrap">
                  <h2>Project <span>Gallery</span></h2>
                </div>
                <div class="lemar-gallery">
                  <?php
                  if ($gallery_ids) {
                    $ids = explode(',', $gallery_ids);
                    foreach ($ids as $id) {
                      $thumb_url = wp_get_attachment_image_url($id, 'large');
                      if ($thumb_url) : ?>
                        <a href="<?php echo wp_get_attachment_image_url($id, 'full'); ?>" class="lemar-gallery-link dvp-gallery-item">
                          <img src="<?php echo esc_url($thumb_url); ?>" loading="lazy" />
                        </a>
                  <?php endif;
                    }
                  }
                  ?>
                </div>
              </section>

              <?php if ($amenities): ?>
                <section class="lemar-content-section">
                  <div class="lemar-title-wrap">
                    <h2>Lifestyle <span>Amenities</span></h2>
                  </div>
                  <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                    <?php foreach ($amenities as $a) echo '<span style="border:2px solid var(--lemar-border); padding:10px 25px; font-size:16px; text-transform:uppercase; letter-spacing:2px; font-weight: 600; font-family: var(--font-heading);">' . $a->name . '</span>'; ?>
                  </div>
                </section>
              <?php endif; ?>

              <?php if ($lat && $lng): ?>
                <section class="lemar-content-section">
                  <div class="lemar-title-wrap">
                    <h2>The <span>Location</span></h2>
                  </div>
                  <div class="lemar-map-wrap">
                    <iframe class="lemar-map-iframe" src="https://maps.google.com/maps?q=<?php echo $lat; ?>,<?php echo $lng; ?>&z=15&output=embed"></iframe>
                  </div>
                  <div style="background: #fff; border: 2px solid var(--lemar-border); border-top: none; padding: 40px; box-sizing: border-box;">
                    <h4 style="letter-spacing: 3px; font-size: 18px; margin-bottom: 25px; color: var(--lemar-gold);">Connectivity & Landmarks</h4>
                    <p style="white-space: pre-line; color: #444; font-size: 22px; line-height: 1.6; font-weight: 500;"><?php echo esc_html($landmarks); ?></p>
                  </div>
                </section>
              <?php endif; ?>
            </div>

            <aside class="lemar-sidebar">
              <div class="lemar-sidebar-card">
                <h3>Guide Price</h3>
                <div class="lemar-price">AED <?php echo number_format($price); ?></div>
                <div class="lemar-sidebar-details">
                  <p style="margin:0;"><strong>Plan:</strong> <?php echo $payment_plan; ?></p>
                  <p style="margin:0;"><strong>Units:</strong> <?php echo $total_units; ?></p>
                  <p style="margin:0;"><strong>Plot:</strong> <?php echo number_format($plot_size); ?> sqft.</p>
                </div>
                <?php if ($brochure_id): ?>
                  <a href="<?php echo wp_get_attachment_url($brochure_id); ?>" class="lemar-btn lemar-btn-solid" target="_blank">Download Brochure</a>
                <?php endif; ?>
                <?php if ($floorplan_id): ?>
                  <a href="<?php echo wp_get_attachment_url($floorplan_id); ?>" class="lemar-btn" target="_blank">Floor Plans</a>
                <?php endif; ?>
                <a href="https://wa.me/YOUR_NUMBER" class="lemar-btn lemar-btn-gold">Consult with Expert</a>
              </div>
            </aside>
          </div>
        </div>
      </article>
    </main>

    <footer class="wp-block-template-part">
      <?php block_footer_area(); ?>
    </footer>
  </div>

  <script>
    function lemarTab(evt, tabId) {
      var i, panels, triggers;
      panels = document.getElementsByClassName("lemar-tab-panel");
      for (i = 0; i < panels.length; i++) {
        panels[i].style.display = "none";
      }
      triggers = document.getElementsByClassName("lemar-tab-trigger");
      for (i = 0; i < triggers.length; i++) {
        triggers[i].classList.remove("active");
      }
      document.getElementById(tabId).style.display = "block";
      evt.currentTarget.classList.add("active");
    }
  </script>

  <?php
  wp_enqueue_stored_styles(); // Critical for Block Themes to render design settings
  wp_footer();
  ?>
</body>

</html>