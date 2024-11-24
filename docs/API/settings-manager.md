# Settings Manager

## Overview
The Settings Manager handles plugin configuration, user preferences, and integration settings.

## Features
- WordPress Settings API integration
- AI provider configuration
- Cache management
- Performance optimization
- Export/Import settings

## Basic Usage

```php
// Initialize settings
$settings = new Settings_Manager();

// Get specific setting
$api_key = $settings->get('ai_provider_api_key');

// Update setting
$settings->update('color_cache_ttl', 3600);

// Check if setting exists
if ($settings->has('custom_provider_endpoint')) {
    // Use custom provider
}
```

## WordPress Integration

```php
// Register settings page
add_action('admin_menu', function() {
    $settings = new Settings_Manager();
    $settings->register_settings_page([
        'page_title' => 'Color Palette Generator Settings',
        'menu_title' => 'Color Settings',
        'capability' => 'manage_options',
        'menu_slug' => 'gl-color-palette-settings'
    ]);
});

// Register settings fields
$settings->add_section('ai_provider', [
    'title' => 'AI Provider Settings',
    'fields' => [
        'api_key' => [
            'type' => 'password',
            'label' => 'API Key',
            'sanitize' => 'sanitize_text_field'
        ],
        'model' => [
            'type' => 'select',
            'label' => 'AI Model',
            'options' => [
                'gpt-4' => 'GPT-4',
                'gpt-3.5-turbo' => 'GPT-3.5 Turbo'
            ]
        ]
    ]
]);
```

## Advanced Configuration

```php
// Configure multiple providers
$settings->configure_providers([
    'openai' => [
        'api_key' => 'your_api_key',
        'model' => 'gpt-4'
    ],
    'anthropic' => [
        'api_key' => 'your_api_key',
        'model' => 'claude-2'
    ]
]);

// Set up caching
$settings->configure_cache([
    'enabled' => true,
    'provider' => 'redis',
    'ttl' => 3600
]);
```

## Default Configurations

### Core Settings

```php
// Default configuration array
$default_settings = [
    'color_analysis' => [
        'cache_ttl' => 3600,
        'batch_size' => 50,
        'precision' => 'high'
    ],
    'ai_provider' => [
        'default_provider' => 'openai',
        'fallback_provider' => 'local',
        'timeout' => 30
    ],
    'accessibility' => [
        'wcag_level' => 'AA',
        'check_color_blind' => true,
        'minimum_contrast' => 4.5
    ]
];

// Load defaults
$settings->load_defaults($default_settings);
```

### Environment-Specific Defaults

```php
// Production settings
$settings->set_environment_defaults('production', [
    'cache_enabled' => true,
    'debug_mode' => false,
    'log_level' => 'error'
]);

// Development settings
$settings->set_environment_defaults('development', [
    'cache_enabled' => false,
    'debug_mode' => true,
    'log_level' => 'debug'
]);
```

## Migration Guide

### Version Migration

```php
// Migrate from previous version
$settings->migrate_from_version('1.0', [
    'backup' => true,
    'validate' => true
]);

// Migration callbacks
$settings->register_migration_callback('1.0', '2.0', function($old_settings) {
    // Transform old settings to new format
    return $transformed_settings;
});
```

### Data Structure Changes

| Version | Changes | Migration Steps |
|---------|---------|----------------|
| 1.0 to 2.0 | Renamed keys | Use migration map |
| 2.0 to 3.0 | New structure | Transform data |
| 3.0 to 4.0 | Added fields | Set defaults |

### Backup and Restore

```php
// Backup current settings
$backup = $settings->backup_settings([
    'include_user_data' => true,
    'format' => 'json'
]);

// Restore from backup
$settings->restore_from_backup($backup_data, [
    'validate' => true,
    'merge_strategy' => 'override'
]);
```

## Multisite Considerations

### Network Settings

```php
// Network-wide settings
$settings->set_network_settings([
    'sync_enabled' => true,
    'override_allowed' => false,
    'sync_frequency' => 'hourly'
]);

// Site-specific overrides
$settings->allow_site_overrides([
    'color_analysis.cache_ttl',
    'ai_provider.timeout',
    'accessibility.wcag_level'
]);
```

### Synchronization

```php
// Sync settings across network
$settings->sync_network_settings([
    'mode' => 'push',
    'target_sites' => 'all'
]);

// Handle conflicts
$settings->set_conflict_resolution('newer_wins');
```

### Permission Management

```php
// Network admin capabilities
$settings->add_network_capabilities([
    'manage_network_settings',
    'override_site_settings'
]);

// Site admin restrictions
$settings->restrict_site_settings([
    'ai_provider.api_key' => 'network_only',
    'color_analysis.precision' => 'allow_override'
]);
```

## Performance Optimization

### Caching Strategy

```php
// Configure settings cache
$settings->configure_cache([
    'driver' => 'redis',
    'ttl' => 3600,
    'prefix' => 'gl_settings_'
]);

// Lazy loading
$settings->enable_lazy_loading([
    'groups' => ['ai_provider', 'color_analysis'],
    'threshold' => 1000
]);
```

### Query Optimization

```php
// Optimize database queries
$settings->optimize_queries([
    'batch_size' => 100,
    'index_fields' => ['setting_name', 'setting_group']
]);

// Monitor performance
$stats = $settings->get_performance_stats([
    'period' => 'last_24_hours',
    'metrics' => ['query_time', 'cache_hits']
]);
```
