<?php

function vite($path) {
	if (IS_DEV) {
		return VITE_SERVER . "/$path";
	} else {
		$manifest = json_decode(file_get_contents(__DIR__ . '/../public/manifest.json'), true);
		return "/{$manifest[$path]['file']}";
	}
}
