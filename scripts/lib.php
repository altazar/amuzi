<?php

/**
 *
 * @package Amuzi
 * @version 1.0
 * Amuzi - Online music
 * Copyright (C) 2010-2013  Diogo Oliveira de Melo
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once '../scripts/env.php';

/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Loader/Autoloader.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->getBootstrap()->bootstrap();

function mapInsert($value, $map)
{
    if(array_search($value, $map) === FALSE)
        $map[] = $value;

    return $map;
}

function countSimilaritiesOnIncBoard($artistMusicTitleId, $degree = 0)
{
    $musicSimilarityModel = new MusicSimilarity();
    $rowSet = $musicSimilarityModel->findByArtistMusicTitleIdAndDegree(
        $artistMusicTitleId, $degree
    );

    $ids = array();
    foreach ($rowSet as $row) {
        $ids = mapInsert($row->fArtistMusicTitleId, $ids);
        $ids = mapInsert($row->sArtistMusicTitleId, $ids);

        if (count($ids) >= 98)
            break;
    }

    $completeRowSet = $musicSimilarityModel->findByArtistMusicTitleIdSetAndDegree($ids, $degree);
    $similarityMap = array();
    $nroSimilar = 0;

    foreach ($completeRowSet as $row) {
        if (!array_key_exists($row->fArtistMusicTitleId, $similarityMap))
            $similarityMap[$row->fArtistMusicTitleId] = array();
        $similarityMap[$row->fArtistMusicTitleId][$row->sArtistMusicTitleId] =
            $row->similarity;

        if (array_search($row->fArtistMusicTitleId, $ids) !== false &&
            array_search($row->sArtistMusicTitleId, $ids) !== false) {

            $nroSimilar++;
        }
    }

    $nroIds = count($ids);
    $percentSimilar = ($nroSimilar * 100) / (($nroIds * ($nroIds - 1) ) / 2);
    return array($nroSimilar, $percentSimilar);
}

function paramsToUri($params)
{
    $uri = '';
    foreach ($params as $key => $value)
        $uri .= '/' . urlencode($key) . '/' . urlencode($value);

    return $uri;
}
