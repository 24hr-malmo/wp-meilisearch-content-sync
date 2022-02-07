<?php

namespace Meilisearch24hr\Api;

use Meilisearch24hr\Traits\HasSiteId;

class Sync
{
    use HasSiteId;

    protected $contentUri;
    protected $apiToken;

    public function __construct(string $contentUri, string $apiToken)
    {
        $this->contentUri = $contentUri;
        $this->apiToken = $apiToken;
    }

    public function sync(): void
    {
        $response = wp_remote_get($this->contentUri.'/content-admin/reindex-content?redis=1', [
            'headers' => [
                'Authorization' => "Bearer {$this->apiToken}",
                'credentials' => 'include',
                'x-site-id' => $this->getSiteId(),
            ],
        ]);

        if (is_wp_error($response)) {
            $notice = $response->get_error_message();
            $noticeType = 'error';
        } else {
            $notice = __('Sync initiated. It may take a few minutes to update the search-index.', '24hr-meilisearch');
            $noticeType = 'success';
        }

        wp_safe_redirect(add_query_arg([
            '24hr-admin-notice' => $notice,
            '24hr-admin-notice-type' => $noticeType,
        ]));

        exit;
    }
}
