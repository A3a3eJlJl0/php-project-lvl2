<?php

namespace Gendiff;

function genDiff($firstFile, $secondFile, $format): string
{
    $firstFilePath = realpath($firstFile);
    $secondFilePath = realpath($secondFile);

    if (!file_exists($firstFilePath) || !file_exists($secondFilePath)) {
        return 'File not exists' . PHP_EOL;
    }

    return gendiffJSON($firstFilePath, $secondFilePath);
}

function gendiffJSON($firstFile, $secondFile): string
{
    $firstMap = json_decode(file_get_contents($firstFile), true);
    $secondMap = json_decode(file_get_contents($secondFile), true);

    $same = array_intersect_assoc($firstMap, $secondMap);
    $added = array_diff_assoc($secondMap, $firstMap);
    $removed = array_diff_assoc($firstMap, $secondMap);

    $same = addKeyPrefix($same, '  ');
    $added = addKeyPrefix($added, '+ ');
    $removed = addKeyPrefix($removed, '- ');

    $resultMap = array_merge($same, $added, $removed);

    uksort($resultMap, function ($a, $b) {
        $a = substr($a, 2);
        $b = substr($b, 2);

        return $a <=> $b;
    });

    $result = '{' . PHP_EOL;
    foreach ($resultMap as $k => $v) {
        $result .= '  ' . $k . ': ' . var_export($v, true) . PHP_EOL;
    }
    return $result . '}' . PHP_EOL;
}

function addKeyPrefix(array $map, string $prefix): array
{
    $result = [];

    foreach ($map as $k => $v) {
        $result[$prefix . $k] = $v;
    }
    return $result;
}