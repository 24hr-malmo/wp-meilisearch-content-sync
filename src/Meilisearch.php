<?php

namespace Meilisearch24hr;

use Meilisearch24hr\Admin\AdminScreen;
use Meilisearch24hr\Api\Sync;

class Meilisearch
{
    protected $draftContentUrl;
    protected $liveContentUrl;
    protected $meilisearchHost;
    protected $apiToken;
    protected $CRON_HOOK = '24hr-meilisearch-cron';

    public function __construct(
        string $draftContentUrl = '',
        string $liveContentUrl = '',
        string $meilisearchHost = '',
        string $apiToken = ''
    ) {
        $this->draftContentUrl = $draftContentUrl;
        $this->liveContentUrl = $liveContentUrl;
        $this->meilisearchHost = $meilisearchHost;
        $this->apiToken = $apiToken;
    }

    public function init(): void
    {
        add_action('admin_menu', fn () => (new AdminScreen($this->meilisearchHost))->init());
        add_action('init', fn () => $this->triggerSync());

        if (!wp_next_scheduled($this->CRON_HOOK)) {
            wp_schedule_event(time(), 'weekly', $this->CRON_HOOK);
        }

        add_action($this->CRON_HOOK, fn () => $this->triggerCronSync());
    }

    protected function triggerSync(): void
    {
        if (!isset($_POST['24hr-sync-trigger'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['24hr-sync-nonce-verified'], '24hr-sync-nonce')) {
            wp_die(__('Invalid nonce!', 'meilisearch-24hr'));
            exit;
        }

        $context = $_POST['24hr-sync-env'] ?? 'draft';
        $contextUrl = ($context === 'draft') ? $this->draftContentUrl : $this->liveContentUrl;

        (new Sync($contextUrl, $this->apiToken))->sync();
    }

    protected function triggerCronSync(): void
    {
        error_log('Meilisearch::triggerCronSync() -> Triggered');
        $response = (new Sync('live', $this->apiToken))->apiSync();

        if (is_wp_error($response)) {
            $errorMessage = $response->get_error_message();
            error_log("Meilisearch::triggerCronSync() -> {$errorMessage}");
        }
    }
}
