<?php

/**
 * Plugin Name: Dubai Project Engine
 * Description: Full Real Estate CPT + Form-Style Meta Box + Gallery Hijacking.
 * Version: 1.6.0
 * Author: Ahmed Gagan
 */

if (! defined('ABSPATH')) exit;

/**
 * Plugin Settings
 */
function dvp_get_settings()
{
  $defaults = [
    'lead_email'  => '',
    'unlock_days' => 7,
  ];
  $settings = get_option('dvp_settings', []);
  if (!is_array($settings)) {
    $settings = [];
  }
  return array_merge($defaults, $settings);
}

function dvp_register_settings()
{
  register_setting('dvp_settings_group', 'dvp_settings', function ($input) {
    $output = [];
    $output['lead_email'] = isset($input['lead_email']) ? sanitize_email($input['lead_email']) : '';
    $output['unlock_days'] = isset($input['unlock_days']) ? max(1, absint($input['unlock_days'])) : 7;
    return $output;
  });
}
add_action('admin_init', 'dvp_register_settings');

/**
 * 0. Leads Table (Activation)
 */
function dvp_create_leads_table()
{
  global $wpdb;
  $table = $wpdb->prefix . 'dvp_leads';
  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    project_id bigint(20) unsigned NOT NULL,
    name varchar(191) NOT NULL,
    email varchar(191) NOT NULL,
    phone varchar(50) NOT NULL,
    ip_address varchar(45) DEFAULT '',
    user_agent text,
    created_at datetime NOT NULL,
    PRIMARY KEY  (id),
    KEY project_id (project_id),
    KEY created_at (created_at)
  ) $charset_collate;";

  require_once ABSPATH . 'wp-admin/includes/upgrade.php';
  dbDelta($sql);
}
register_activation_hook(__FILE__, 'dvp_create_leads_table');

/**
 * Admin Menus
 */
function dvp_register_admin_menus()
{
  $parent = 'edit.php?post_type=dvp_project';

  add_submenu_page(
    $parent,
    'Leads',
    'Leads',
    'manage_options',
    'dvp-leads',
    'dvp_render_leads_page'
  );

  add_submenu_page(
    $parent,
    'Settings',
    'Settings',
    'manage_options',
    'dvp-settings',
    'dvp_render_settings_page'
  );
}
add_action('admin_menu', 'dvp_register_admin_menus');

function dvp_render_settings_page()
{
  $settings = dvp_get_settings();
?>
  <div class="wrap">
    <h1>Dubai Project Engine Settings</h1>
    <form method="post" action="options.php">
      <?php settings_fields('dvp_settings_group'); ?>
      <table class="form-table">
        <tr>
          <th scope="row"><label for="dvp_lead_email">Lead Email</label></th>
          <td>
            <input type="email" id="dvp_lead_email" name="dvp_settings[lead_email]" value="<?php echo esc_attr($settings['lead_email']); ?>" class="regular-text" placeholder="Leave empty to use site admin email">
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="dvp_unlock_days">Unlock Days</label></th>
          <td>
            <input type="number" id="dvp_unlock_days" name="dvp_settings[unlock_days]" value="<?php echo esc_attr($settings['unlock_days']); ?>" min="1" max="365" class="small-text">
            <p class="description">How long downloads stay unlocked after a lead submits the form.</p>
          </td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
<?php
}

function dvp_render_leads_page()
{
  if (!current_user_can('manage_options')) return;

  global $wpdb;
  $table = $wpdb->prefix . 'dvp_leads';

  $per_page = 20;
  $paged = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
  $offset = ($paged - 1) * $per_page;

  $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table");
  $rows = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table ORDER BY created_at DESC LIMIT %d OFFSET %d", $per_page, $offset));
  $total_pages = max(1, (int) ceil($total / $per_page));
?>
  <div class="wrap">
    <h1>Leads</h1>
    <table class="widefat fixed striped">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Project</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)) : ?>
          <tr>
            <td colspan="5">No leads yet.</td>
          </tr>
        <?php else : ?>
          <?php foreach ($rows as $row) : ?>
            <tr>
              <td><?php echo esc_html($row->name); ?></td>
              <td><?php echo esc_html($row->email); ?></td>
              <td><?php echo esc_html($row->phone); ?></td>
              <td>
                <?php
                $title = $row->project_id ? get_the_title($row->project_id) : '';
                echo esc_html($title ?: '—');
                ?>
              </td>
              <td><?php echo esc_html(date_i18n('Y-m-d H:i', strtotime($row->created_at))); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <?php if ($total_pages > 1) : ?>
      <div class="tablenav">
        <div class="tablenav-pages">
          <?php
          $base_url = remove_query_arg('paged');
          for ($i = 1; $i <= $total_pages; $i++) {
            $url = esc_url(add_query_arg('paged', $i, $base_url));
            $class = $i === $paged ? 'class="page-numbers current"' : 'class="page-numbers"';
            echo '<a ' . $class . ' href="' . $url . '">' . esc_html($i) . '</a> ';
          }
          ?>
        </div>
      </div>
    <?php endif; ?>
  </div>
<?php
}

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
 * Amenities Taxonomy Meta (Logo)
 */
function dvp_amenities_add_form_fields()
{
?>
  <div class="form-field term-group">
    <label for="dvp_amenity_logo_id">Amenity Logo</label>
    <input type="hidden" id="dvp_amenity_logo_id" name="dvp_amenity_logo_id" value="">
    <button type="button" class="button dvp-amenity-logo-upload">Select Logo</button>
    <button type="button" class="button dvp-amenity-logo-clear">Clear</button>
    <div class="dvp-amenity-logo-preview" style="margin-top:10px;"></div>
  </div>
<?php
}
add_action('dvp_amenities_add_form_fields', 'dvp_amenities_add_form_fields');

function dvp_amenities_edit_form_fields($term)
{
  $logo_id = get_term_meta($term->term_id, 'dvp_amenity_logo_id', true);
  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
?>
  <tr class="form-field term-group-wrap">
    <th scope="row"><label for="dvp_amenity_logo_id">Amenity Logo</label></th>
    <td>
      <input type="hidden" id="dvp_amenity_logo_id" name="dvp_amenity_logo_id" value="<?php echo esc_attr($logo_id); ?>">
      <button type="button" class="button dvp-amenity-logo-upload">Select Logo</button>
      <button type="button" class="button dvp-amenity-logo-clear">Clear</button>
      <div class="dvp-amenity-logo-preview" style="margin-top:10px;">
        <?php if ($logo_url): ?>
          <img src="<?php echo esc_url($logo_url); ?>" alt="" style="max-width:80px;height:auto;">
        <?php endif; ?>
      </div>
    </td>
  </tr>
<?php
}
add_action('dvp_amenities_edit_form_fields', 'dvp_amenities_edit_form_fields');

function dvp_save_amenity_logo_meta($term_id)
{
  if (isset($_POST['dvp_amenity_logo_id'])) {
    update_term_meta($term_id, 'dvp_amenity_logo_id', absint($_POST['dvp_amenity_logo_id']));
  }
}
add_action('created_dvp_amenities', 'dvp_save_amenity_logo_meta');
add_action('edited_dvp_amenities', 'dvp_save_amenity_logo_meta');

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
    'dvp_bedrooms'           => 'string',
    'dvp_size_range'         => 'string',
    'dvp_service_charge'     => 'string',
    'dvp_ownership'          => 'string',
    'dvp_building_height'    => 'string',
    'dvp_plot_size'          => 'string',
    'dvp_brochure_id'        => 'number',
    'dvp_floorplan_id'       => 'number',
    'dvp_master_plan_id'     => 'number',
    'dvp_cluster_plan_id'    => 'number',
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
      <label>Number of Bedrooms</label>
      <input type="text" name="dvp_bedrooms" value="<?php echo esc_attr($get_meta('dvp_bedrooms')); ?>" placeholder="1,2,3 BHK">
    </div>
    <div class="dvp-field">
      <label>Size Range</label>
      <input type="text" name="dvp_size_range" value="<?php echo esc_attr($get_meta('dvp_size_range')); ?>" placeholder="1,000 - 2,000 sqft">
    </div>
    <div class="dvp-field">
      <label>Service Charge</label>
      <input type="text" name="dvp_service_charge" value="<?php echo esc_attr($get_meta('dvp_service_charge')); ?>" placeholder="1,000 AED or 5%">
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
      <input type="number" name="dvp_plot_size" value="<?php echo esc_attr($get_meta('dvp_plot_size')); ?>">
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
    <div class="dvp-field">
      <label>Master Plan (PDF/ZIP)</label>
      <div class="media-controls">
        <input type="hidden" name="dvp_master_plan_id" class="dvp-file-id" value="<?php echo esc_attr($get_meta('dvp_master_plan_id')); ?>">
        <button type="button" class="button dvp-upload-btn"><?php _e('Select File', 'dvp'); ?></button>
        <span class="dvp-file-status">ID: <?php echo $get_meta('dvp_master_plan_id') ?: 'None'; ?></span>
      </div>
    </div>
    <div class="dvp-field">
      <label>Cluster Plan (PDF/ZIP)</label>
      <div class="media-controls">
        <input type="hidden" name="dvp_cluster_plan_id" class="dvp-file-id" value="<?php echo esc_attr($get_meta('dvp_cluster_plan_id')); ?>">
        <button type="button" class="button dvp-upload-btn"><?php _e('Select File', 'dvp'); ?></button>
        <span class="dvp-file-status">ID: <?php echo $get_meta('dvp_cluster_plan_id') ?: 'None'; ?></span>
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
    'dvp_bedrooms'           => 'sanitize_text_field',
    'dvp_size_range'         => 'sanitize_text_field',
    'dvp_service_charge'     => 'sanitize_text_field',
    'dvp_ownership'          => 'sanitize_text_field',
    'dvp_building_height'    => 'sanitize_text_field',
    'dvp_plot_size'          => 'sanitize_text_field',
    'dvp_brochure_id'        => 'absint',
    'dvp_floorplan_id'       => 'absint',
    'dvp_master_plan_id'     => 'absint',
    'dvp_cluster_plan_id'    => 'absint',
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

function dvp_amenities_admin_scripts($hook)
{
  if (!in_array($hook, ['edit-tags.php', 'term.php'])) return;
  $screen = get_current_screen();
  if (!$screen || $screen->taxonomy !== 'dvp_amenities') return;

  wp_enqueue_media();
  wp_add_inline_script('jquery', "
    jQuery(document).ready(function($){
      function setPreview(url){
        var preview = $('.dvp-amenity-logo-preview');
        if (!url) { preview.html(''); return; }
        preview.html('<img src=\"'+url+'\" style=\"max-width:80px;height:auto;\" />');
      }

      $('.dvp-amenity-logo-upload').on('click', function(e){
        e.preventDefault();
        var input = $('#dvp_amenity_logo_id');
        var frame = wp.media({ title: 'Select Amenity Logo', multiple: false }).on('select', function(){
          var attachment = frame.state().get('selection').first().toJSON();
          input.val(attachment.id);
          setPreview(attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url);
        }).open();
      });

      $('.dvp-amenity-logo-clear').on('click', function(e){
        e.preventDefault();
        $('#dvp_amenity_logo_id').val('');
        setPreview('');
      });
    });
  ");
}
add_action('admin_enqueue_scripts', 'dvp_amenities_admin_scripts');

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
 * 9. Property Filter Logic
 */
// Register the short keys for URLs
add_filter('query_vars', function ($vars) {
  $vars[] = 'dev';
  $vars[] = 'dist';
  $vars[] = 'stat';
  $vars[] = 'type';
  $vars[] = 'life';
  $vars[] = 'amen';
  return $vars;
});

// Intercept the query and apply filters
add_action('pre_get_posts', function ($query) {
  if (!is_admin() && $query->is_main_query() && is_post_type_archive('dvp_project')) {
    $tax_query = array('relation' => 'AND');

    $filters = [
      'dev'  => 'dvp_developer',
      'dist' => 'dvp_district',
      'stat' => 'dvp_project_status',
      'type' => 'dvp_property_type',
      'life' => 'dvp_lifestyle',
      'amen' => 'dvp_amenities'
    ];

    foreach ($filters as $query_var => $taxonomy) {
      if ($value = get_query_var($query_var)) {
        $tax_query[] = [
          'taxonomy' => $taxonomy,
          'field'    => 'slug',
          'terms'    => $value
        ];
      }
    }

    if (count($tax_query) > 1) {
      $query->set('tax_query', $tax_query);
    }
  }
});

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

/**
 * Load the Filter CSS file
 */
function lemar_register_assets()
{
  wp_enqueue_style(
    'lemar-filter-style',
    plugins_url('assets/css/filter-style.css', __FILE__),
    array(),
    '1.0.0'
  );
}
add_action('wp_enqueue_scripts', 'lemar_register_assets');

/**
 * Frontend Lead Capture Assets
 */
function dvp_enqueue_lead_assets()
{
  if (!is_singular('dvp_project')) return;

  wp_enqueue_style(
    'dvp-lead-modal',
    plugins_url('assets/css/lead-modal.css', __FILE__),
    array(),
    '1.0.0'
  );

  wp_enqueue_script(
    'dvp-lead-modal',
    plugins_url('assets/js/lead-modal.js', __FILE__),
    array(),
    '1.0.0',
    true
  );

  $settings = dvp_get_settings();
  wp_localize_script('dvp-lead-modal', 'dvpLead', [
    'ajaxUrl'    => admin_url('admin-ajax.php'),
    'nonce'      => wp_create_nonce('dvp_lead_nonce'),
    'unlockDays' => (int) $settings['unlock_days'],
    'cookieName' => 'dvp_docs_unlocked',
  ]);
}
add_action('wp_enqueue_scripts', 'dvp_enqueue_lead_assets');

/**
 * Lead Capture AJAX
 */
function dvp_submit_lead()
{
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'dvp_lead_nonce')) {
    wp_send_json_error(['message' => 'Invalid request.']);
  }

  $name  = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
  $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
  $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
  $project_id = isset($_POST['project_id']) ? absint($_POST['project_id']) : 0;

  if (!$name || !$email || !$phone) {
    wp_send_json_error(['message' => 'All fields are required.']);
  }
  if (!is_email($email)) {
    wp_send_json_error(['message' => 'Please enter a valid email address.']);
  }
  if (!$project_id || get_post_type($project_id) !== 'dvp_project') {
    wp_send_json_error(['message' => 'Invalid project.']);
  }

  global $wpdb;
  $table = $wpdb->prefix . 'dvp_leads';

  $inserted = $wpdb->insert($table, [
    'project_id' => $project_id,
    'name'       => $name,
    'email'      => $email,
    'phone'      => $phone,
    'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '',
    'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_textarea_field($_SERVER['HTTP_USER_AGENT']) : '',
    'created_at' => current_time('mysql'),
  ]);

  if (!$inserted) {
    wp_send_json_error(['message' => 'Unable to save lead. Please try again.']);
  }

  $settings = dvp_get_settings();
  $to = $settings['lead_email'] ? $settings['lead_email'] : get_option('admin_email');
  $subject = 'New Project Lead — ' . get_the_title($project_id);
  $body = "Name: {$name}\nEmail: {$email}\nPhone: {$phone}\nProject: " . get_permalink($project_id);
  wp_mail($to, $subject, $body);

  $days = (int) $settings['unlock_days'];
  $expire = time() + max(1, $days) * DAY_IN_SECONDS;
  setcookie('dvp_docs_unlocked', '1', $expire, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), false);

  wp_send_json_success(['message' => 'Thank you! Your download will start now.']);
}
add_action('wp_ajax_nopriv_dvp_submit_lead', 'dvp_submit_lead');
add_action('wp_ajax_dvp_submit_lead', 'dvp_submit_lead');

function dvp_register_filter_shortcode()
{
  add_shortcode('lemar_filter', function () {
    if (is_admin()) return '';

    $filter_config = [
      ['label' => 'Developer', 'slug' => 'dev',  'taxonomy' => 'dvp_developer'],
      ['label' => 'District',  'slug' => 'dist', 'taxonomy' => 'dvp_district'],
      ['label' => 'Status',    'slug' => 'stat', 'taxonomy' => 'dvp_project_status'],
      ['label' => 'Type',      'slug' => 'type', 'taxonomy' => 'dvp_property_type'],
    ];

    ob_start(); ?>

    <div class="lemar-mobile-trigger-wrap">
      <button type="button" class="lemar-filter-trigger" onclick="document.body.classList.add('filter-open')">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="4" y1="21" x2="4" y2="14"></line>
          <line x1="4" y1="10" x2="4" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12" y2="3"></line>
          <line x1="20" y1="21" x2="20" y2="16"></line>
          <line x1="20" y1="12" x2="20" y2="3"></line>
          <line x1="1" x2="7" y1="14" y2="14"></line>
          <line x1="9" x2="15" y1="8" y2="8"></line>
          <line x1="17" x2="23" y1="16" y2="16"></line>
        </svg>
        <span>Filters</span>
      </button>
    </div>

    <div class="lemar-filter-overlay" onclick="document.body.classList.remove('filter-open')"></div>

    <section id="lemar-results" class="lemar-filter-container">
      <form method="get" action="<?php echo get_post_type_archive_link('dvp_project'); ?>" class="lemar-filter-console">

        <div class="lemar-mobile-header">
          <span>Filter Results</span>
          <button type="button" onclick="document.body.classList.remove('filter-open')">&times;</button>
        </div>

        <div class="lemar-filter-scroll-area">
          <?php foreach ($filter_config as $item) : ?>
            <div class="lemar-filter-segment">
              <?php wp_dropdown_categories([
                'show_option_all' => 'All ' . $item['label'] . 's',
                'taxonomy'        => $item['taxonomy'],
                'name'            => $item['slug'],
                'selected'        => get_query_var($item['slug']),
                'value_field'     => 'slug',
                'hierarchical'    => true,
              ]); ?>
            </div>
          <?php endforeach; ?>
          <div class="lemar-filter-submit-wrap">
            <button type="submit" class="lemar-filter-submit">
              <span class="desktop-text">Update Results</span>
              <span class="mobile-text">Show Properties</span>
            </button>
          </div>
        </div>
      </form>
    </section>
  <?php
    return ob_get_clean();
  });
}
add_action('init', 'dvp_register_filter_shortcode');

add_shortcode('lemar_project_details', 'lemar_project_shortcode');

function lemar_project_shortcode()
{
  // 1. Enqueue
  if (!wp_style_is('lemar-filter-style', 'enqueued')) {
    wp_enqueue_style('lemar-filter-style');
  }
  add_action('wp_footer', function () {
    wp_print_styles('lemar-filter-style');
  });

  // 2. Data
  $post_id = get_the_ID();
  if (!$post_id) return '';

  $get_meta = function ($key) use ($post_id) {
    return get_post_meta($post_id, $key, true);
  };

  $price = $get_meta('dvp_price_from');
  $handover = $get_meta('dvp_handover_date');
  $payment_plan = $get_meta('dvp_payment_plan');
  $down_payment = $get_meta('dvp_down_payment');
  $total_units = $get_meta('dvp_total_units');
  $bedrooms = $get_meta('dvp_bedrooms');
  $size_range = $get_meta('dvp_size_range');
  $service_charge = $get_meta('dvp_service_charge');
  $ownership = $get_meta('dvp_ownership');
  $height = $get_meta('dvp_building_height');
  $plot_size = $get_meta('dvp_plot_size');
  $video_url = $get_meta('dvp_video_url');
  $tour_url = $get_meta('dvp_360_tour');
  $lat = $get_meta('dvp_latitude');
  $lng = $get_meta('dvp_longitude');
  $landmarks = $get_meta('dvp_distance_landmarks');
  $gallery_ids = $get_meta('dvp_gallery_ids');
  $brochure_id = $get_meta('dvp_brochure_id');
  $floorplan_id = $get_meta('dvp_floorplan_id');
  $master_plan_id = $get_meta('dvp_master_plan_id');
  $cluster_plan_id = $get_meta('dvp_cluster_plan_id');

  $developers = get_the_term_list($post_id, 'dvp_developer', '', ', ', '');
  $districts  = get_the_term_list($post_id, 'dvp_district', '', ', ', '');
  $amenities  = get_the_terms($post_id, 'dvp_amenities');

  // 3. Output
  ob_start();
  ?>
  <div class="lemar-project-page">
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
      <div class="lemar-specs-grid"><?php
                                    echo '<div class="lemar-spec-item"><small>Handover</small><span class="spec-value">' . esc_html($handover) . '</span></div>';
                                    echo '<div class="lemar-spec-item"><small>Ownership</small><span class="spec-value">' . ($ownership ?: 'Freehold') . '</span></div>';
                                    echo '<div class="lemar-spec-item"><small>Height</small><span class="spec-value">' . esc_html($height) . '</span></div>';
                                    echo '<div class="lemar-spec-item"><small>Down Payment</small><span class="spec-value">' . esc_html($down_payment) . '%</span></div>';
                                    ?></div>

      <div class="lemar-main-split">
        <div class="lemar-main-body">
          <section class="lemar-content-section">
            <div class="lemar-title-wrap">
              <h2>The <span>Overview</span></h2>
            </div>
            <div class="lemar-body-text"><?php the_content(); ?></div>
          </section>

          <?php if ($video_url || $tour_url): ?>
            <section class="lemar-content-section">
              <div class="lemar-tabs-nav">
                <?php if ($video_url): ?><button type="button" class="lemar-tab-trigger active" onclick="lemarTab(event, 'lemar-video')">Film Presentation</button><?php endif; ?>
                <?php if ($tour_url): ?><button type="button" class="lemar-tab-trigger <?php echo !$video_url ? 'active' : ''; ?>" onclick="lemarTab(event, 'lemar-360')">360° Experience</button><?php endif; ?>
              </div>
              <?php if ($video_url): ?>
                <div id="lemar-video" class="lemar-tab-panel active">
                  <iframe src="<?php echo str_replace('watch?v=', 'embed/', esc_url($video_url)); ?>" allowfullscreen></iframe>
                </div>
              <?php endif; ?>
              <?php if ($tour_url): ?>
                <div id="lemar-360" class="lemar-tab-panel <?php echo !$video_url ? 'active' : ''; ?>">
                  <iframe
                    src="<?php echo esc_url($tour_url); ?>"
                    allow="xr-spatial-tracking; gyroscope; accelerometer; fullscreen"
                    allowfullscreen>
                  </iframe>
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
                    <a href="<?php echo esc_url(wp_get_attachment_image_url($id, 'full')); ?>" class="lemar-gallery-link"><img src="<?php echo esc_url($thumb_url); ?>" loading="lazy" /></a>
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
              <div class="lemar-amenities-grid">
                <?php foreach ($amenities as $a): ?>
                  <?php
                  $logo_id = get_term_meta($a->term_id, 'dvp_amenity_logo_id', true);
                  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
                  $desc = wp_strip_all_tags($a->description);
                  if ($desc) {
                    $desc = wp_trim_words($desc, 10, '');
                  }
                  ?>
                  <div class="lemar-amenity-card">
                    <div class="lemar-amenity-logo">
                      <?php if ($logo_url): ?>
                        <img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($a->name); ?>">
                      <?php else: ?>
                        <div class="lemar-amenity-placeholder"><?php echo esc_html(substr($a->name, 0, 1)); ?></div>
                      <?php endif; ?>
                    </div>
                    <div class="lemar-amenity-name"><?php echo esc_html($a->name); ?></div>
                    <?php if ($desc): ?>
                      <div class="lemar-amenity-desc"><?php echo esc_html($desc); ?></div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if ($lat && $lng): ?>
            <section class="lemar-content-section">
              <div class="lemar-title-wrap">
                <h2>The <span>Location</span></h2>
              </div>
              <div class="lemar-map-wrap">
                <iframe class="lemar-map-iframe" src="https://maps.google.com/maps?q=<?php echo esc_attr($lat); ?>,<?php echo esc_attr($lng); ?>&z=15&output=embed"></iframe>
              </div>
              <?php if ($landmarks): ?>
                <div style="background: #fff; border: 2px solid var(--lemar-border); border-top: none; padding: 40px; box-sizing: border-box;">
                  <h4 style="letter-spacing: 3px; font-size: 18px; margin-bottom: 25px; color: var(--lemar-gold);">Connectivity & Landmarks</h4>
                  <p style="white-space: pre-line; color: #444; font-size: 22px; line-height: 1.6; font-weight: 500;"><?php echo esc_html($landmarks); ?></p>
                </div>
              <?php endif; ?>
            </section>
          <?php endif; ?>

          <?php if ($brochure_id || $floorplan_id || $master_plan_id || $cluster_plan_id): ?>
            <section class="lemar-content-section">
              <div class="lemar-title-wrap">
                <h2>Project <span>Documents</span></h2>
              </div>
              <div class="lemar-downloads-wrap">
                <div class="lemar-sidebar-downloads">
                  <?php if ($brochure_id): ?>
                    <a href="<?php echo esc_url(wp_get_attachment_url($brochure_id)); ?>" class="lemar-download-btn dvp-doc-btn" data-doc-label="Brochure" target="_blank" rel="noopener">Download Brochure</a>
                  <?php endif; ?>
                  <?php if ($floorplan_id): ?>
                    <a href="<?php echo esc_url(wp_get_attachment_url($floorplan_id)); ?>" class="lemar-download-btn dvp-doc-btn" data-doc-label="Floor Plans" target="_blank" rel="noopener">Download Floor Plans</a>
                  <?php endif; ?>
                  <?php if ($master_plan_id): ?>
                    <a href="<?php echo esc_url(wp_get_attachment_url($master_plan_id)); ?>" class="lemar-download-btn dvp-doc-btn" data-doc-label="Master Plan" target="_blank" rel="noopener">Download Master Plan</a>
                  <?php endif; ?>
                  <?php if ($cluster_plan_id): ?>
                    <a href="<?php echo esc_url(wp_get_attachment_url($cluster_plan_id)); ?>" class="lemar-download-btn dvp-doc-btn" data-doc-label="Cluster Plan" target="_blank" rel="noopener">Download Cluster Plan</a>
                  <?php endif; ?>
                </div>
              </div>
            </section>
          <?php endif; ?>
        </div>

        <aside class="lemar-sidebar">
          <div class="lemar-sidebar-card">
            <h3>Guide Price</h3>
            <div class="lemar-price">AED <?php echo $price ? number_format((float)$price) : 'Contact us'; ?></div>
            <div class="lemar-sidebar-details">
              <?php if ($payment_plan): ?><p style="margin:0;"><strong>Plan:</strong> <?php echo esc_html($payment_plan); ?></p><?php endif; ?>
              <?php if ($total_units): ?><p style="margin:0;"><strong>Units:</strong> <?php echo esc_html($total_units); ?></p><?php endif; ?>
              <?php if ($bedrooms): ?><p style="margin:0;"><strong>Bedrooms:</strong> <?php echo esc_html($bedrooms); ?></p><?php endif; ?>
              <?php if ($size_range): ?><p style="margin:0;"><strong>Size:</strong> <?php echo esc_html($size_range); ?></p><?php endif; ?>
              <?php if ($service_charge): ?><p style="margin:0;"><strong>Service Charge:</strong> <?php echo esc_html($service_charge); ?></p><?php endif; ?>
              <?php if ($plot_size): ?><p style="margin:0;"><strong>Plot:</strong> <?php echo number_format((float)$plot_size); ?> sqft.</p><?php endif; ?>
            </div>
            <a href="https://wa.me/YOUR_NUMBER" class="lemar-btn lemar-btn-gold">Consult with Expert</a>
          </div>
        </aside>
      </div>
    </div>
  </div>

  <div class="dvp-lead-modal" id="dvp-lead-modal" aria-hidden="true">
    <div class="dvp-lead-backdrop" data-dvp-close></div>
    <div class="dvp-lead-dialog" role="dialog" aria-modal="true" aria-labelledby="dvp-lead-title">
      <button type="button" class="dvp-lead-close" aria-label="Close" data-dvp-close>&times;</button>
      <h3 id="dvp-lead-title">Get Project Documents</h3>
      <p class="dvp-lead-subtitle">Please share your details to access this document.</p>
      <form id="dvp-lead-form">
        <input type="hidden" name="project_id" value="<?php echo esc_attr($post_id); ?>">
        <label>
          <span>Name</span>
          <input type="text" name="name" placeholder="Your full name" required>
        </label>
        <label>
          <span>Email</span>
          <input type="email" name="email" placeholder="you@email.com" required>
        </label>
        <label>
          <span>Phone</span>
          <input type="text" name="phone" placeholder="+971 1234567890" required>
        </label>
        <button type="submit" class="dvp-lead-submit">Unlock & Download</button>
        <div class="dvp-lead-message" role="status" aria-live="polite"></div>
      </form>
    </div>
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
  $content = ob_get_clean();
  $content = preg_replace('/\n|\r|\t/', '', $content); // Remove newlines and tabs
  $content = preg_replace('/\s{2,}/', ' ', $content);  // Remove extra spaces
  // This removes WordPress's "helpfulness" before returning the content
  return $content;
}

add_action('enqueue_block_editor_assets', function () {
  wp_enqueue_script(
    'lemar-block-variations',
    plugins_url('assets/js/block-variations.js', __FILE__),
    ['wp-blocks', 'wp-dom-ready', 'wp-edit-post'],
    filemtime(plugins_url('assets/js/block-variations.js', __FILE__),)
  );
});
