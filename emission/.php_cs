<?php

$files = shell_exec('git diff-index --name-only HEAD');
$changedFiles = $files ? explode("\n", trim($files)) : [];
$fixers = require_once __DIR__ . '/php-cs-fixer-settings.php';

$finder = Symfony\CS\Finder::create()
    ->filter(function (\SplFileInfo $file) use ($changedFiles) {
        if (isFileAllowedToChange($file, $changedFiles)) {
            // don't include
            return false;
        }

        // include
        return true;
    })
    ->in(__DIR__ . '/src/')
;

return Symfony\CS\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder)
    ;

/**
 * @param SplFileInfo $file
 * @param array $allowedFiles
 *
 * @return bool
 */
function isFileAllowedToChange(\SplFileInfo $file, array $allowedFiles)
{
    return !in_array(
        str_replace(__DIR__ . '/', 'emission/', $file->getRealPath()),
        $allowedFiles
    );
}
