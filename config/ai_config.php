<?php

function load_ai_config() {
    static $config = null;

    if ($config !== null) {
        return $config;
    }

    $config = [
        'mistral_api_key' => getenv('MISTRAL_API_KEY') ?: '',
        'mistral_model' => getenv('MISTRAL_MODEL') ?: 'mistral-large-latest'
    ];

    $localPath = __DIR__ . '/ai_config.local.php';
    if (is_file($localPath)) {
        $localConfig = include $localPath;
        if (is_array($localConfig)) {
            foreach ($localConfig as $key => $value) {
                if ($value !== null && $value !== '') {
                    $config[$key] = $value;
                }
            }
        }
    }

    return $config;
}

function ai_config($key, $default = null) {
    $config = load_ai_config();
    return array_key_exists($key, $config) ? $config[$key] : $default;
}
