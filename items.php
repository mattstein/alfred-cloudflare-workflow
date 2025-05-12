<?php

include 'vendor/autoload.php';

use Alfred\Workflows\Workflow;
use Cloudflare\API\Auth\APIToken;
use Cloudflare\API\Auth\APIKey;

$workflow = new Workflow();
$cacheSeconds = $workflow->env('CACHE_SECONDS', 30);

$hasToken = $workflow->env('CLOUDFLARE_API_TOKEN');
$hasGlobalCredentials = $workflow->env('CLOUDFLARE_EMAIL') &&
    $workflow->env('CLOUDFLARE_API_KEY');

if (!$hasToken && !$hasGlobalCredentials) {
    $workflow->output('You must provide an API token or email+key credentials in the workflow environment variables.');
    return;
}

$data = $workflow->cache()->readJson(null, false);
$lastCached = $data->saved ?? null;
$now = time();
$shouldRefreshCache = ! $lastCached || ($now - $lastCached) > $cacheSeconds;
$collectedZones = [];

if ($shouldRefreshCache) {
    $workflow->logger()->info('Refreshing data...');

    if ($hasToken) {
        $authorization = new APIToken(
            $workflow->env('CLOUDFLARE_API_TOKEN')
        );
    } else {
        $authorization = new APIKey(
            $workflow->env('CLOUDFLARE_EMAIL'),
            $workflow->env('CLOUDFLARE_API_KEY')
        );
    }

    $adapter = new Cloudflare\API\Adapter\Guzzle($authorization);
    collectZoneList($adapter);

    $data = (object)[
        'zones' => stripUnnecessaryZoneProperties($collectedZones),
        'saved' => $now,
    ];

    $workflow->cache()->writeJson($data);
} else {
    $workflow->logger()->info('Using cached data...');
}

$zones = $data->zones ?? [];

foreach ($zones as $zone) {
    $dashboardUrl = sprintf('https://dash.cloudflare.com/%s/%s',
        $zone->account->id,
        $zone->name
    );
    $workflow->item()
        ->title($zone->name)
        ->arg($dashboardUrl);
}

$workflow->output();

/**
 * Fetches zone info across pagination and populates `$collectedZones`.
 * @param $adapter
 * @param int $page
 * @return void
 */
function collectZoneList($adapter, int $page = 1): void
{
    global $collectedZones;
    $zonesResponse = (new Cloudflare\API\Endpoints\Zones($adapter))->listZones(page: $page);
    $zonesResponseInfo = $zonesResponse->result_info;
    $collectedZones = array_merge($collectedZones, $zonesResponse->result ?? []);

    if ($zonesResponseInfo->page < $zonesResponseInfo->total_pages) {
        collectZoneList($adapter, $page+1);
    }
}

/**
 * Removes properties from each zone that we don’t need to save.
 * @param $zones
 * @return mixed
 */
function stripUnnecessaryZoneProperties($zones): array
{
    $keepProperties = ['id', 'name', 'status', 'account'];

    foreach ($zones as &$zone) {
        foreach ($zone as $key => $value) {
            if (! in_array($key, $keepProperties, true)) {
                unset($zone->{$key});
            }
        }
    }

    return $zones;
}
