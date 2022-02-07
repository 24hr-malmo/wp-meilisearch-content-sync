<?php

namespace Meilisearch24hr\Traits;

use Exception;

trait HasSiteId
{
    protected function getSiteId(): string
    {
        $siteId = get_option('dls_settings_site_id');

        if (!$siteId) {
            throw new Exception('dls_settings_site_id setting not found for site!');
        }

        return $siteId;
    }
}
