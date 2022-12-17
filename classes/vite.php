<?php

function vite($path) {
    if (CONFIG['IS_DEV']) {
        return CONFIG['VITE_SERVER'] . "/$path";
    } else {
        $manifest = json_decode(file_get_contents(__DIR__ . '/../public/manifest.json'), true);
        return "/{$manifest[$path]['file']}";
    }
}
