<?php

namespace CrossSiteLinker\Admin;

class Settings
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_menu_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_menu_page()
    {
        add_options_page(
            'Cross-Site Linker',
            'Cross-Site Linker',
            'manage_options',
            'cross-site-linker',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings()
    {
        register_setting('cross_site_linker_settings', 'cross_site_linker_sites');
        register_setting('cross_site_linker_settings', 'cross_site_linker_api_key');

        add_settings_section(
            'cross_site_linker_api_key_section',
            'API Key Settings',
            null,
            'cross-site-linker'
        );

        add_settings_field(
            'cross_site_linker_api_key',
            'API Key',
            [$this, 'render_api_key_field'],
            'cross-site-linker',
            'cross_site_linker_api_key_section'
        );

        add_settings_section(
            'cross_site_linker_sites_section',
            'Sites',
            null,
            'cross-site-linker'
        );

        add_settings_field(
            'cross_site_linker_sites',
            'Sites',
            [$this, 'render_sites_field'],
            'cross-site-linker',
            'cross_site_linker_sites_section'
        );
    }

    public function render_settings_page()
    {
        ?>
        <div class="wrap">
            <h1>Cross-Site Linker Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('cross_site_linker_settings');
                do_settings_sections('cross-site-linker');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function render_api_key_field()
    {
        $api_key = get_option('cross_site_linker_api_key');
        echo '<input type="text" name="cross_site_linker_api_key" value="' . esc_attr($api_key) . '" size="40" />';
        echo '<p class="description">If you want to protect your API, enter an API key here.</p>';
    }

    public function render_sites_field()
    {
        $sites = get_option('cross_site_linker_sites', []);
        ?>
        <div id="cross-site-linker-sites-wrapper">
            <?php if (!empty($sites)) : ?>
                <?php foreach ($sites as $index => $site) : ?>
                    <div class="site-fields">
                        <input type="text" name="cross_site_linker_sites[<?php echo $index; ?>][name]" value="<?php echo esc_attr($site['name']); ?>" placeholder="Site Name" />
                        <input type="text" name="cross_site_linker_sites[<?php echo $index; ?>][url]" value="<?php echo esc_attr($site['url']); ?>" placeholder="Site URL" />
                        <input type="text" name="cross_site_linker_sites[<?php echo $index; ?>][api_key]" value="<?php echo esc_attr($site['api_key']); ?>" placeholder="API Key (optional)" />
                        <button type="button" class="button remove-site">Remove</button>
                        <button type="button" class="button test-connection">Test Connection</button>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <button type="button" class="button" id="add-site">Add Site</button>

        <script>
            jQuery(document).ready(function($) {
                let siteIndex = <?php echo count($sites); ?>;

                $('#add-site').on('click', function() {
                    let newSite = `
                        <div class="site-fields">
                            <input type="text" name="cross_site_linker_sites[${siteIndex}][name]" placeholder="Site Name" />
                            <input type="text" name="cross_site_linker_sites[${siteIndex}][url]" placeholder="Site URL" />
                            <input type="text" name="cross_site_linker_sites[${siteIndex}][api_key]" placeholder="API Key (optional)" />
                            <button type="button" class="button remove-site">Remove</button>
                            <button type="button" class="button test-connection">Test Connection</button>
                        </div>
                    `;
                    $('#cross-site-linker-sites-wrapper').append(newSite);
                    siteIndex++;
                });

                $('#cross-site-linker-sites-wrapper').on('click', '.remove-site', function() {
                    $(this).closest('.site-fields').remove();
                });

                $('#cross-site-linker-sites-wrapper').on('click', '.test-connection', function() {
                    let siteUrl = $(this).siblings('input[name$="[url]"]').val();
                    let apiKey = $(this).siblings('input[name$="[api_key]"]').val();
                    let button = $(this);

                    button.text('Testing...').prop('disabled', true);

                    $.ajax({
                        url: siteUrl + '/wp-json/crosslinker/v1/posts?q=test',
                        beforeSend: function(xhr) {
                            if (apiKey) {
                                xhr.setRequestHeader('X-API-KEY', apiKey);
                            }
                        },
                        success: function() {
                            alert('Connection successful!');
                            button.text('Test Connection').prop('disabled', false);
                        },
                        error: function() {
                            alert('Connection failed!');
                            button.text('Test Connection').prop('disabled', false);
                        }
                    });
                });
            });
        </script>
        <?php
    }
}
