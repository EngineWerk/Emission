<?php

$files = shell_exec('git diff-index --name-only HEAD');
$changedFiles = $files ? explode("\n", trim($files)) : [];

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers(require_once __DIR__ . '/php-cs-fixer-settings.php')
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->filter(function (\SplFileInfo $file) use ($changedFiles) {
                if (isFileAllowedToChange($file, $changedFiles)) {
                    return false;
                }

                return true;
            })
            ->in(__DIR__ . '/src/')
    );

/**
 * @param SplFileInfo $file
 * @param array $allowedFiles
 *
 * @return bool
 */
function isFileAllowedToChange(\SplFileInfo $file, array $allowedFiles)
{
    return !in_array(
        str_replace(__DIR__ . '/', '', $file->getRealPath()),
        $allowedFiles
    );
}
