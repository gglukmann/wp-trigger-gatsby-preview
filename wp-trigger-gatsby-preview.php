<?php

/**
 * @package WPTriggerGatsbyPreview
 */
/*
Plugin Name: WP Trigger Gatsby Preview
Plugin URI: https://github.com/gglukmann/wp-trigger-gatsby-preview
Description: Save or update action triggers Gatsby Cloud Preview webhook.
Version: 1.0.0
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
    add_action('admin_init', array($this, 'general_settings_section'));
    add_action('save_post', array($this, 'run_hook'), 10, 3);
  }

  public function activate()
  {
    flush_rewrite_rules();
    $this->general_settings_section();
  }

  public function deactivate()
  {
    flush_rewrite_rules();
  }

  function run_hook($post_id)
  {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!(wp_is_post_revision($post_id) || wp_is_post_autosave($post_id))) return;

    $webhook = get_option('option_webhook');

    if ($webhook) {
      $url = $webhook;
      $args = array(
        'method'  => 'POST',
      );

      wp_remote_post($url, $args);
    }
  }

  function general_settings_section()
  {
    add_settings_section(
      'general_settings_section',
      'WP Trigger Gatsby Cloud Preview',
      array($this, 'my_section_options_callback'),
      'general'
    );
    add_settings_field(
      'option_webhook',
      'Webhook',
      array($this, 'my_textbox_callback'),
      'general',
      'general_settings_section',
      array(
        'option_webhook'
      )
    );

    register_setting('general', 'option_webhook', 'esc_attr');
  }

  function my_section_options_callback()
  {
    echo '<p>Add webhook url. You can find it from Site Settings in Gatsby Cloud.</p>';
  }

  function my_textbox_callback($args)
  {
    $option = get_option($args[0]);
    echo '<input type="text" id="' . $args[0] . '" name="' . $args[0] . '" value="' . $option . '" />';
  }
}


if (class_exists('WPTriggerGatsbyPreview')) {
  $WPTriggerGatsbyPreview = new WPTriggerGatsbyPreview();
}

// activation
register_activation_hook(__FILE__, array($WPTriggerGatsbyPreview, 'activate'));

// deactivate
register_deactivation_hook(__FILE__, array($WPTriggerGatsbyPreview, 'deactivate'));
