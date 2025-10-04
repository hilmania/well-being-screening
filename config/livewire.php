<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Uploads
    |--------------------------------------------------------------------------
    |
    | Livewire handles file uploads by storing uploads in a temporary directory
    | before the file is stored permanently. All file uploads are directed to
    | a global endpoint for temporary storage. The configuration below decides
    | which disk and directory temporary files are stored to.
    |
    */
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', 'local'),
        'rules' => null, // Example: ['file', 'mimes:png,jpg', 'max:102400'] (Max 100MB)
        'directory' => 'livewire-tmp',
        'middleware' => null, // Example: 'throttle:5,1'
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
        ],
        'max_upload_time' => 5, // Max 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Manifest File Path
    |--------------------------------------------------------------------------
    |
    | This value sets the path to the Livewire JavaScript manifest file.
    | The default should work for most cases (which is
    | "<app_url>/livewire/livewire.js"), but for some cases like when hosting
    | on a subdomain, you may need to generate your own manifest file.
    |
    */
    'manifest_path' => null,

    /*
    |--------------------------------------------------------------------------
    | Back Button Cache
    |--------------------------------------------------------------------------
    |
    | This value determines whether the back button cache will be used on pages
    | that contain Livewire. By disabling back button cache, it ensures that
    | the back button shows the correct state of components, instead of
    | potentially stale, cached data.
    |
    */
    'back_button_cache' => false,

    /*
    |--------------------------------------------------------------------------
    | Render On Redirect
    |--------------------------------------------------------------------------
    |
    | This value determines whether Livewire will render before it's redirected
    | or not. Setting this to "false" (default) will mean the render method is
    | skipped when redirecting. And "true" will mean the render method is run
    | before redirecting. Browsers bfcache can store a potentially stale view
    | if render is skipped on redirect.
    |
    */
    'render_on_redirect' => false,

    /*
    |--------------------------------------------------------------------------
    | Eloquent Model Binding
    |--------------------------------------------------------------------------
    |
    | Previous versions of Livewire supported binding directly to eloquent model
    | properties using wire:model on any properties that weren't directly assigned
    | to the model. However, this approach has always been confusing and hard to
    | debug. We've removed this functionality, however you can enable it again
    | using the flag below at your own risk.
    |
    */
    'legacy_model_binding' => false,

    /*
    |--------------------------------------------------------------------------
    | Auto-inject Frontend Assets
    |--------------------------------------------------------------------------
    |
    | By default, Livewire automatically injects its JavaScript and CSS into the
    | <head> and before the closing </body> tag of pages containing Livewire
    | components. By disabling this, you need to manually include the assets.
    |
    */
    'inject_assets' => true,

    /*
    |--------------------------------------------------------------------------
    | Navigate (SPA mode)
    |--------------------------------------------------------------------------
    |
    | By default, page navigation in Livewire uses the standard browser behavior
    | (full page reloads). However, you can enable a "SPA mode" that uses
    | Livewire's JavaScript to navigate between pages without full reloads.
    |
    */
    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    /*
    |--------------------------------------------------------------------------
    | HTML Morph Markers
    |--------------------------------------------------------------------------
    |
    | Livewire will inject HTML comments before and after components to track
    | and morph them properly in subsequent requests. However, this may not
    | be desired and can be disabled by setting this to 'false'.
    |
    */
    'inject_morph_markers' => true,

    /*
    |--------------------------------------------------------------------------
    | Pagination Theme
    |--------------------------------------------------------------------------
    |
    | When including the "WithPagination" trait, Livewire will use Tailwind
    | classes by default. You can specify Bootstrap or write your own theme.
    |
    */
    'pagination_theme' => 'tailwind',
];
