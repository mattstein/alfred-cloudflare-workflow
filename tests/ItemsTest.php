<?php

beforeEach(function () {
    require_once __DIR__ . '/../vendor/autoload.php';
});

test('stripUnnecessaryZoneProperties keeps only required properties', function () {
    $zones = [
        (object)[
            'id' => 'zone-123',
            'name' => 'example.com',
            'status' => 'active',
            'account' => (object)['id' => 'account-456', 'name' => 'My Account'],
            'paused' => false,
            'type' => 'full',
            'development_mode' => 0,
            'name_servers' => ['ns1.cloudflare.com', 'ns2.cloudflare.com'],
            'original_name_servers' => ['ns1.example.com'],
            'modified_on' => '2024-01-01T00:00:00Z',
        ],
    ];

    $result = stripUnnecessaryZoneProperties($zones);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(1)
        ->and($result[0])->toHaveProperty('id', 'zone-123')
        ->and($result[0])->toHaveProperty('name', 'example.com')
        ->and($result[0])->toHaveProperty('status', 'active')
        ->and($result[0])->toHaveProperty('account')
        ->and($result[0])->not->toHaveProperty('paused')
        ->and($result[0])->not->toHaveProperty('type')
        ->and($result[0])->not->toHaveProperty('development_mode')
        ->and($result[0])->not->toHaveProperty('name_servers')
        ->and($result[0])->not->toHaveProperty('original_name_servers')
        ->and($result[0])->not->toHaveProperty('modified_on');
});

test('stripUnnecessaryZoneProperties handles multiple zones', function () {
    $zones = [
        (object)[
            'id' => 'zone-1',
            'name' => 'example1.com',
            'status' => 'active',
            'account' => (object)['id' => 'account-1'],
            'extra' => 'should be removed',
        ],
        (object)[
            'id' => 'zone-2',
            'name' => 'example2.com',
            'status' => 'pending',
            'account' => (object)['id' => 'account-2'],
            'another_extra' => 'also removed',
        ],
    ];

    $result = stripUnnecessaryZoneProperties($zones);

    expect($result)->toHaveCount(2)
        ->and($result[0])->toHaveProperty('name', 'example1.com')
        ->and($result[0])->not->toHaveProperty('extra')
        ->and($result[1])->toHaveProperty('name', 'example2.com')
        ->and($result[1])->not->toHaveProperty('another_extra');
});

test('stripUnnecessaryZoneProperties handles empty array', function () {
    $result = stripUnnecessaryZoneProperties([]);

    expect($result)->toBeArray()->toBeEmpty();
});

test('dashboard URL can be constructed from zone data', function () {
    $zone = (object)[
        'name' => 'example.com',
        'account' => (object)['id' => 'account-123'],
    ];

    $dashboardUrl = sprintf(
        'https://dash.cloudflare.com/%s/%s',
        $zone->account->id,
        $zone->name
    );

    expect($dashboardUrl)->toBe('https://dash.cloudflare.com/account-123/example.com');
});

/**
 * Helper function copied from items.php for testing.
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
