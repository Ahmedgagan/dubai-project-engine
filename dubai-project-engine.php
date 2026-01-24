<?php

/**
 * Plugin Name: Dubai Project Engine
 * Description: Full Real Estate CPT + Form-Style Meta Box + Gallery Hijacking.
 * Version: 1.6.0
 * Author: Ahmed Gagan
 */

if (! defined('ABSPATH')) exit;

/**
 * 1. Register Custom Post Type: Project
 */
function dvp_register_project_cpt()
{
  $labels = [
    'name'               => _x('Projects', 'Post Type General Name', 'dvp'),
    'singular_name'      => _x('Project', 'Post Type Singular Name', 'dvp'),
    'menu_name'          => __('Dubai Projects', 'dvp'),
    'all_items'          => __('All Projects', 'dvp'),
    'add_new_item'       => __('Add New Project', 'dvp'),
    'edit_item'          => __('Edit Project', 'dvp'),
  ];

  $args = [
    'labels'              => $labels,
    // Removed 'title' and 'editor' to move them into our custom Meta Box UI
    'supports'            => ['thumbnail', 'excerpt', 'revisions', 'custom-fields'],
    'public'              => true,
    'show_in_rest'        => true,
    'has_archive'         => true,
    'rewrite'             => ['slug' => 'projects'],
    'menu_icon'           => 'dashicons-building',
  ];

  register_post_type('dvp_project', $args);
}
add_action('init', 'dvp_register_project_cpt');

/**
 * 2. Register Custom Taxonomies
 */
function dvp_register_taxonomies()
{
  $taxonomies = [
    'dvp_developer'      => ['label' => 'Developer', 'slug' => 'developer'],
    'dvp_district'       => ['label' => 'District', 'slug' => 'district'],
    'dvp_project_status' => ['label' => 'Project Status', 'slug' => 'status'],
    'dvp_property_type'  => ['label' => 'Property Type', 'slug' => 'type'],
    'dvp_lifestyle'      => ['label' => 'Lifestyle', 'slug' => 'lifestyle'],
    'dvp_amenities'      => ['label' => 'Amenities', 'slug' => 'amenities'],
  ];

  foreach ($taxonomies as $key => $data) {
    register_taxonomy($key, ['dvp_project'], [
      'hierarchical'      => true,
      'labels'            => ['name' => $data['label']],
      'show_ui'           => true,
      'show_admin_column' => true,
      'show_in_rest'      => true,
      'rewrite'           => ['slug' => $data['slug']],
    ]);
  }
}
add_action('init', 'dvp_register_taxonomies');

/**
 * 3. Register Custom Meta Fields (REST API Enabled)
 */
function dvp_register_project_meta()
{
  $meta_fields = [
    'dvp_price_from'         => 'number',
    'dvp_handover_date'      => 'string',
    'dvp_payment_plan'       => 'string',
    'dvp_down_payment'       => 'number',
    'dvp_total_units'        => 'number',
    'dvp_ownership'          => 'string',
    'dvp_building_height'    => 'string',
    'dvp_plot_size'          => 'string',
    'dvp_brochure_id'        => 'number',
    'dvp_floorplan_id'       => 'number',
    'dvp_gallery_ids'        => 'string',
    'dvp_video_url'          => 'string',
    'dvp_360_tour'           => 'string',
    'dvp_latitude'           => 'string',
    'dvp_longitude'          => 'string',
    'dvp_distance_landmarks' => 'string',
  ];

  foreach ($meta_fields as $key => $type) {
    register_post_meta('dvp_project', $key, [
      'show_in_rest' => true,
      'single'       => true,
      'type'         => $type,
      'auth_callback' => function () {
        return current_user_can('edit_posts');
      }
    ]);
  }
}
add_action('init', 'dvp_register_project_meta');

/**
 * 4. Add Meta Box
 */
function dvp_add_project_metaboxes()
{
  add_meta_box('dvp_project_details', __('Project Specifications & Data', 'dvp'), 'dvp_render_project_metabox', 'dvp_project', 'normal', 'high');
}
add_action('add_meta_boxes', 'dvp_add_project_metaboxes');

/**
 * 5. Render Meta Box UI
 */
function dvp_render_project_metabox($post)
{
  wp_nonce_field('dvp_save_project_meta', 'dvp_project_nonce');
  $get_meta = function ($key) use ($post) {
    return get_post_meta($post->ID, $key, true);
  };
?>
  <style>
    .dvp-admin-wrap {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      padding: 10px;
    }

    .dvp-field {
      margin-bottom: 12px;
    }

    .dvp-field label {
      display: block;
      font-weight: 600;
      margin-bottom: 4px;
      color: #2c3338;
    }

    .dvp-field input,
    .dvp-field select,
    .dvp-field textarea {
      width: 100%;
      border-radius: 4px;
      border: 1px solid #8c8f94;
      padding: 6px 8px;
    }

    .dvp-full {
      grid-column: 1 / -1;
    }

    .dvp-header {
      grid-column: 1 / -1;
      background: #2c3338;
      color: #fff;
      padding: 8px 12px;
      margin: 15px 0 5px;
      border-radius: 4px;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .dvp-gallery-preview {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 10px;
      background: #f0f0f1;
      padding: 10px;
      border-radius: 4px;
      min-height: 50px;
    }

    .dvp-gallery-preview img {
      width: 70px;
      height: 70px;
      object-fit: cover;
      border: 1px solid #ccc;
      border-radius: 3px;
    }

    .media-controls {
      display: flex;
      gap: 5px;
      align-items: center;
    }
  </style>

  <div class="dvp-admin-wrap">

    <div class="dvp-header"><?php _e('Project Identity', 'dvp'); ?></div>
    <div class="dvp-field dvp-full">
      <label>Project Name (Marketing Title)</label>
      <input type="text" name="dvp_project_title" value="<?php echo esc_attr($post->post_title); ?>" placeholder="e.g. Creek Vistas Reserve" style="font-size: 16px; font-weight: 600;">
    </div>
    <div class="dvp-field dvp-full">
      <label>Project Long Description</label>
      <?php
      wp_editor($post->post_content, 'dvp_project_description', [
        'textarea_name' => 'dvp_project_description',
        'media_buttons' => false,
        'textarea_rows' => 8,
        'teeny'         => true
      ]);
      ?>
    </div>

    <div class="dvp-header"><?php _e('Financials & Launch', 'dvp'); ?></div>
    <div class="dvp-field">
      <label>Starting Price (AED)</label>
      <input type="number" name="dvp_price_from" value="<?php echo esc_attr($get_meta('dvp_price_from')); ?>">
    </div>
    <div class="dvp-field">
      <label>Handover Date</label>
      <input type="text" name="dvp_handover_date" value="<?php echo esc_attr($get_meta('dvp_handover_date')); ?>" placeholder="Q4 2027">
    </div>
    <div class="dvp-field">
      <label>Payment Plan</label>
      <input type="text" name="dvp_payment_plan" value="<?php echo esc_attr($get_meta('dvp_payment_plan')); ?>">
    </div>
    <div class="dvp-field">
      <label>Down Payment (%)</label>
      <input type="number" name="dvp_down_payment" value="<?php echo esc_attr($get_meta('dvp_down_payment')); ?>">
    </div>

    <div class="dvp-header"><?php _e('Project Specifications', 'dvp'); ?></div>
    <div class="dvp-field">
      <label>Total Units</label>
      <input type="number" name="dvp_total_units" value="<?php echo esc_attr($get_meta('dvp_total_units')); ?>">
    </div>
    <div class="dvp-field">
      <label>Ownership Type</label>
      <input type="text" name="dvp_ownership" value="<?php echo esc_attr($get_meta('dvp_ownership')); ?>" placeholder="Freehold">
    </div>
    <div class="dvp-field">
      <label>Building Height</label>
      <input type="text" name="dvp_building_height" value="<?php echo esc_attr($get_meta('dvp_building_height')); ?>" placeholder="G+P+20">
    </div>
    <div class="dvp-field">
      <label>Plot Size / Area</label>
      <input type="text" name="dvp_plot_size" value="<?php echo esc_attr($get_meta('dvp_plot_size')); ?>">
    </div>

    <div class="dvp-header"><?php _e('Project Photo Gallery', 'dvp'); ?></div>
    <div class="dvp-field dvp-full">
      <input type="hidden" name="dvp_gallery_ids" id="dvp_gallery_ids" class="dvp-file-id" value="<?php echo esc_attr($get_meta('dvp_gallery_ids')); ?>">
      <button type="button" class="button button-primary dvp-gallery-upload-btn"><?php _e('Add/Edit Gallery Images', 'dvp'); ?></button>
      <button type="button" class="button dvp-clear-btn"><?php _e('Clear Gallery', 'dvp'); ?></button>
      <div id="dvp-gallery-preview" class="dvp-gallery-preview">
        <?php
        $g_ids = $get_meta('dvp_gallery_ids');
        if ($g_ids) {
          foreach (explode(',', $g_ids) as $id) {
            echo wp_get_attachment_image($id, [70, 70]);
          }
        } else {
          echo '<p class="description">No images selected.</p>';
        }
        ?>
      </div>
    </div>

    <div class="dvp-header"><?php _e('Media & Assets', 'dvp'); ?></div>
    <div class="dvp-field">
      <label>Brochure (PDF)</label>
      <div class="media-controls">
        <input type="hidden" name="dvp_brochure_id" class="dvp-file-id" value="<?php echo esc_attr($get_meta('dvp_brochure_id')); ?>">
        <button type="button" class="button dvp-upload-btn"><?php _e('Select File', 'dvp'); ?></button>
        <span class="dvp-file-status">ID: <?php echo $get_meta('dvp_brochure_id') ?: 'None'; ?></span>
      </div>
    </div>
    <div class="dvp-field">
      <label>Floorplan (PDF/ZIP)</label>
      <div class="media-controls">
        <input type="hidden" name="dvp_floorplan_id" class="dvp-file-id" value="<?php echo esc_attr($get_meta('dvp_floorplan_id')); ?>">
        <button type="button" class="button dvp-upload-btn"><?php _e('Select File', 'dvp'); ?></button>
        <span class="dvp-file-status">ID: <?php echo $get_meta('dvp_floorplan_id') ?: 'None'; ?></span>
      </div>
    </div>
    <div class="dvp-field"><label>Video URL</label><input type="url" name="dvp_video_url" value="<?php echo esc_url($get_meta('dvp_video_url')); ?>"></div>
    <div class="dvp-field"><label>360 Virtual Tour URL</label><input type="url" name="dvp_360_tour" value="<?php echo esc_url($get_meta('dvp_360_tour')); ?>"></div>

    <div class="dvp-header"><?php _e('Location Intelligence', 'dvp'); ?></div>
    <div class="dvp-field"><label>Latitude</label><input type="text" name="dvp_latitude" value="<?php echo esc_attr($get_meta('dvp_latitude')); ?>"></div>
    <div class="dvp-field"><label>Longitude</label><input type="text" name="dvp_longitude" value="<?php echo esc_attr($get_meta('dvp_longitude')); ?>"></div>
    <div class="dvp-field dvp-full">
      <label>Distance to Landmarks</label>
      <textarea name="dvp_distance_landmarks" rows="3"><?php echo esc_textarea($get_meta('dvp_distance_landmarks')); ?></textarea>
    </div>
  </div>
<?php
}

/**
 * 6. Save Meta Box Data
 */
function dvp_save_project_data($post_id)
{
  if (!isset($_POST['dvp_project_nonce']) || !wp_verify_nonce($_POST['dvp_project_nonce'], 'dvp_save_project_meta')) return;
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  // Sync Title and Content from the Meta Box
  remove_action('save_post', 'dvp_save_project_data');
  wp_update_post([
    'ID'           => $post_id,
    'post_title'   => sanitize_text_field($_POST['dvp_project_title']),
    'post_content' => wp_kses_post($_POST['dvp_project_description']),
  ]);
  add_action('save_post', 'dvp_save_project_data');

  $fields = [
    'dvp_price_from'         => 'absint',
    'dvp_handover_date'      => 'sanitize_text_field',
    'dvp_payment_plan'       => 'sanitize_text_field',
    'dvp_down_payment'       => 'absint',
    'dvp_total_units'        => 'absint',
    'dvp_ownership'          => 'sanitize_text_field',
    'dvp_building_height'    => 'sanitize_text_field',
    'dvp_plot_size'          => 'sanitize_text_field',
    'dvp_brochure_id'        => 'absint',
    'dvp_floorplan_id'       => 'absint',
    'dvp_gallery_ids'        => 'sanitize_text_field',
    'dvp_video_url'          => 'esc_url_raw',
    'dvp_360_tour'           => 'esc_url_raw',
    'dvp_latitude'           => 'sanitize_text_field',
    'dvp_longitude'          => 'sanitize_text_field',
    'dvp_distance_landmarks' => 'sanitize_textarea_field',
  ];

  foreach ($fields as $key => $func) {
    if (isset($_POST[$key])) update_post_meta($post_id, $key, call_user_func($func, $_POST[$key]));
  }
}
add_action('save_post', 'dvp_save_project_data');

/**
 * 7. Admin Scripts
 */
function dvp_admin_scripts($hook)
{
  if (!in_array($hook, ['post.php', 'post-new.php'])) return;
  if (get_current_screen()->post_type !== 'dvp_project') return;

  wp_enqueue_media();
  wp_add_inline_script('jquery', "
        jQuery(document).ready(function($){
            $('.dvp-gallery-upload-btn').on('click', function(e){
                e.preventDefault();
                var btn = $(this), input = $('#dvp_gallery_ids'), preview = $('#dvp-gallery-preview');
                var frame = wp.media({ title: 'Select Gallery Images', multiple: 'add', library: { type: 'image' } }).on('select', function(){
                    var selection = frame.state().get('selection'), ids = [], html = '';
                    selection.map(function(attachment){
                        attachment = attachment.toJSON();
                        ids.push(attachment.id);
                        html += '<img src=\"'+attachment.url+'\">';
                    });
                    input.val(ids.join(',')); preview.html(html);
                }).open();
            });

            $('.dvp-upload-btn').on('click', function(e){
                e.preventDefault();
                var btn = $(this), input = btn.siblings('.dvp-file-id'), status = btn.siblings('.dvp-file-status');
                var frame = wp.media({ title: 'Select File', multiple: false }).on('select', function(){
                    var attachment = frame.state().get('selection').first().toJSON();
                    input.val(attachment.id); status.text('ID: ' + attachment.id);
                }).open();
            });

            $('.dvp-clear-btn').on('click', function(){
                $('#dvp_gallery_ids').val(''); $('#dvp-gallery-preview').html('<p class=\"description\">No images selected.</p>');
            });
        });
    ");
}
add_action('admin_enqueue_scripts', 'dvp_admin_scripts');

/**
 * 8. Gallery Block Hijacker
 */
function dvp_filter_gallery_block_output($block_content, $block)
{
  if ('core/gallery' !== $block['blockName'] || !is_singular('dvp_project')) return $block_content;
  $ids_raw = get_post_meta(get_the_ID(), 'dvp_gallery_ids', true);
  if (empty($ids_raw)) return $block_content;
  $ids = explode(',', $ids_raw);
  $inner_html = '';
  foreach ($ids as $id) {
    $url = wp_get_attachment_image_url($id, 'large');
    $inner_html .= '<figure class="wp-block-image size-large"><img src="' . esc_url($url) . '" class="wp-image-' . esc_attr($id) . '"/></figure>';
  }
  $cols = isset($block['attrs']['columns']) ? $block['attrs']['columns'] : 3;
  return '<figure class="wp-block-gallery has-nested-images columns-' . $cols . '">' . $inner_html . '</figure>';
}
add_filter('render_block', 'dvp_filter_gallery_block_output', 10, 2);

/**
 * 9. Force the Custom Template for Projects
 */
function dvp_force_project_template($template)
{
  if (is_singular('dvp_project')) {
    $plugin_template = plugin_dir_path(__FILE__) . 'templates/single-project.php';
    if (file_exists($plugin_template)) {
      return $plugin_template;
    }
  }
  return $template;
}
add_filter('template_include', 'dvp_force_project_template');

/**
 * Enqueue GLightbox for Luxury Gallery with Arrows
 */
function dvp_enqueue_luxury_lightbox()
{
  if (is_singular('dvp_project')) {
    // Enqueue GLightbox CSS & JS
    wp_enqueue_style('glightbox-css', 'https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css');
    wp_enqueue_script('glightbox-js', 'https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js', array(), null, true);

    // Initialize with Gallery Arrows enabled
    wp_add_inline_script('glightbox-js', "
            document.addEventListener('DOMContentLoaded', function() {
                const lightbox = GLightbox({
                    selector: '.dvp-gallery-item',
                    touchNavigation: true,
                    loop: true,
                    autoplayVideos: true
                });
            });
        ");
  }
}
add_action('wp_enqueue_scripts', 'dvp_enqueue_luxury_lightbox');
