<?php

/**
 * @package WPTriggerGatsbyPreview
 */
/*
Plugin Name: WP Trigger Gatsby Preview
Plugin URI: https://github.com/gglukmann/wp-trigger-gatsby-preview
Description: Save or update action triggers Gatsby Cloud webhook.
Version: 1.1.0
Author: Gert Glükmann
Author URI: https://github.com/gglukmann
License: GPLv3
Text-Domain: wp-trigger-gatsby-preview
 */

if (!defined('ABSPATH')) {
  die;
}

class WPTriggerGatsbyPreview
{
  function __construct()
  {
    add_action('admin_init', [$this, 'generalSettingsSection']);
    add_action('wp_dashboard_setup', [$this, 'buildDashboardWidget']);
    add_action('save_post', [$this, 'runHook'], 10, 3);
  }

  public function activate()
  {
    flush_rewrite_rules();
    $this->generalSettingsSection();
  }

  public function deactivate()
  {
    flush_rewrite_rules();
  }

  function runHook($post_id)
  {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id) || wp_is_post_autosave($post_id)) return;

    $webhook = get_option('gp_option_webhook');

    if ($webhook) {
      $url = $webhook;
      $args = array(
        'method'  => 'POST',
      );

      wp_remote_post($url, $args);
    }
  }

  function generalSettingsSection()
  {
    add_settings_section(
      'gp_general_settings_section',
      'WP Trigger Gatsby Cloud Preview',
      [$this, 'mySectionOptionsCallback'],
      'general'
    );
    add_settings_field(
      'gp_option_webhook',
      'Webhook',
      [$this, 'myTextboxCallback'],
      'general',
      'gp_general_settings_section',
      ['gp_option_webhook']
    );
    add_settings_field(
      'gp_option_preview_url',
      'Preview URL',
      [$this, 'myTextboxCallback'],
      'general',
      'gp_general_settings_section',
      ['gp_option_preview_url']
    );

    register_setting('general', 'gp_option_webhook', 'esc_attr');
    register_setting('general', 'gp_option_preview_url', 'esc_attr');
  }

  function mySectionOptionsCallback()
  {
    echo '<p>Add webhook url. You can find it from Site Settings under Builds Webhook in Gatsby Cloud.<br />After first deploy you\'ll get preview url from Gatsby Cloud, add it here to access it easily from dashboard.</p>';
  }

  function myTextboxCallback($args)
  {
    $option = get_option($args[0]);
    echo '<input type="text" id="' . $args[0] . '" name="' . $args[0] . '" value="' . $option . '" />';
  }

  function buildDashboardWidget()
  {
    global $wp_meta_boxes;

    wp_add_dashboard_widget('gp_preview_dashboard_status', 'Preview URL', [$this, 'buildDashboardWidgetContent']);
  }

  function buildDashboardWidgetContent()
  {
    $preview_url = get_option('gp_option_preview_url');

    $markup = '<a href="' . $preview_url . '" target="_blank" rel="noopener noreferrer">Gatsby Cloud Preview URL</a>';

    echo $markup;
  }
}


if (class_exists('WPTriggerGatsbyPreview')) {
  $WPTriggerGatsbyPreview = new WPTriggerGatsbyPreview();
}

// activation
register_activation_hook(__FILE__, array($WPTriggerGatsbyPreview, 'activate'));

// deactivate
register_deactivation_hook(__FILE__, array($WPTriggerGatsbyPreview, 'deactivate'));
