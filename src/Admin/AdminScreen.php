<?php

namespace Meilisearch24hr\Admin;

class AdminScreen
{
    protected $meilisearchHost;

    public function __construct(string $meilisearchHost = 'http://meilisearch:7700')
    {
        $this->meilisearchHost = $meilisearchHost;
    }

    public function init(): void
    {
        add_management_page(
            __('Meilisearch', 'meilisearch-24hr'),
            __('Meilisearch', 'meilisearch-24hr'),
            'install_plugins',
            'meilisearch-24hr-admin',
            [$this, 'renderAdminPage']
        );

        add_action('admin_notices', function() {
            $this->showAdminNotices();
        });
    }

    public function renderAdminPage(): void
    {
        ?>
        <div class="wrap">
            <h1>Meilisearch</h1>

            <h3><?php esc_html_e('Sync content with Meilisearch', 'meilisearch-24hr'); ?></h3>
            <p><?php esc_html_e('This will remove all items and repopulate the index with all documents currently available on the respective content server.', 'meilisearch-24hr'); ?></p>

            <form method="post" action="">
                <input type="hidden" name="24hr-sync-trigger" value="1"/>
                <?php wp_nonce_field('24hr-sync-nonce', '24hr-sync-nonce-verified'); ?>

                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th>
                                <label for="24hr-sync-env">
                                    <?php esc_html_e('Select environment', 'meilisearch-24hr'); ?>
                                </label>
                            </th>
                            <td>
                                <select name="24hr-sync-env" id="24hr-sync-env">
                                    <option value="draft" selected>
                                        <?php esc_html_e('Draft', 'meilisearch-24hr'); ?>
                                    </option>
                                    <option value="live">
                                        <?php esc_html_e('Live', 'meilisearch-24hr'); ?>
                                    </option>
                                </select>
                                <p class="description">
                                    <?php esc_html_e('Select the environment you wish to sync', 'meilisearch-24hr'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button(
                    __('Sync content', 'meilisearch-24hr')
                ); ?>
            </form>

            <hr/>

            <?php $this->renderMeilisearchStats(); ?>
        </div>
        <?php
    }

    protected function showAdminNotices(): void
    {
        if (!isset($_REQUEST['24hr-admin-notice'])) {
            return;
        }

        $message = $_REQUEST['24hr-admin-notice'];
        $noticeClass = ($_REQUEST['24hr-admin-notice-type'] === 'error') ? 'notice-error' : 'notice-success';
        $classes = ['notice', $noticeClass];
        ?>
        <div class="<?php echo esc_attr(implode(' ', $classes)); ?>">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <?php
    }

    /**
     * @return \WP_Error|array
     */
    protected function getMeilisearchStats()
    {
        $response = wp_remote_get("{$this->meilisearchHost}/stats", [
            'sslverify' => false,
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }

    protected function renderMeilisearchStats(): void
    {
        $data = $this->getMeilisearchStats();

        if (is_wp_error($data)): ?>
            <p><?php echo esc_html($data->get_error_message()); ?></p>
            <?php return;
        endif;

        $indexes = $data['indexes'] ?? [];
        ?>

        <div class="meilisearch-stats">
            <h3><?php esc_html_e('Index stats', 'meilisearch-24hr'); ?></h3>
            <p><?php esc_html_e('Reload this page to refresh', 'meilisearch-24hr'); ?></p>

            <?php if (empty($indexes)): ?>
                <p><strong><?php esc_html_e('No indexes found', 'meilisearch-24hr'); ?></strong></p>
            <?php else: ?>
                <ul>
                    <?php foreach ($indexes as $key => $index) : ?>
                        <li class="meilisearch-stats__index">
                            <?php esc_html_e('Index name:', 'meilisearch-24hr'); ?>
                            <strong><?php echo esc_html($key); ?></strong>
                            <br/>
                            <?php esc_html_e('Current number of indexed documents:', 'meilisearch-24hr'); ?>
                            <strong><?php echo esc_html($index['numberOfDocuments']); ?></strong>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php
    }
}
