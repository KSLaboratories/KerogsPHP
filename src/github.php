<?php
/*
 * (c) Kerogs kerogs.labs@gmail.com
 *
 * This source file is subject to the Mozilla Public License Version 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Kerogs\KerogsPhp;

class Github
{
    private $baseUrl = 'https://api.github.com/repos/';

    public function __construct() {}

    public function getRepositoryInfo($owner, $repo)
    {
        $url = $this->baseUrl . "$owner/$repo";

        $options = [
            "http" => [
                "header" => "User-Agent: $owner\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            return ['error' => 'Unable to fetch repository info.'];
        }

        return json_decode($response, true);
    }

    public function getLatestRelease($owner, $repo, $onlyName = true)
    {
        $url = $this->baseUrl . "$owner/$repo/releases/latest";

        $options = [
            "http" => [
                "header" => "User-Agent: Kerogs\r\n"
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === false) {
            return ['error' => 'Unable to fetch latest release.'];
        }

        if ($onlyName) {
            return json_decode($response, true)['name'];
        } else {
            return json_decode($response, true);
        }
    }

    public function compareVersions($versionActual, $versionLatest)
    {
        // Retire tout ce qui se trouve après le "-"
        $version1 = explode('-', $versionActual)[0];
        $version2 = explode('-', $versionLatest)[0];

        // Convertit les versions en tableaux
        $v1 = array_map('intval', explode('.', $version1));
        $v2 = array_map('intval', explode('.', $version2));

        // Compare les versions
        $length = max(count($v1), count($v2));
        for ($i = 0; $i < $length; $i++) {
            $v1Part = $v1[$i] ?? 0; // Défaut à 0 si la version n'a pas cette partie
            $v2Part = $v2[$i] ?? 0; // Défaut à 0 si la version n'a pas cette partie

            if ($v1Part < $v2Part) {
                return ['same' => false, 'comparison' => 'below'];
            } elseif ($v1Part > $v2Part) {
                return ['same' => false, 'comparison' => 'above'];
            }
        }

        return ['same' => true, 'comparison' => null];
    }
}
