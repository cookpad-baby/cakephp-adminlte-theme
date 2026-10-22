<?php
declare(strict_types=1);

// Use the same constraint parser as Composer without adding a project dependency.
$composer = realpath($argv[1] ?? '/usr/local/bin/composer');
if ($composer === false || !is_file($composer)) {
    fwrite(STDERR, "Usage: php tests/php-requirement.php /path/to/composer.phar\n");
    exit(1);
}
Phar::loadPhar($composer);
require 'phar://composer.phar/vendor/autoload.php';

$root = dirname(__DIR__);
$manifest = json_decode(file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$lock = json_decode(file_get_contents($root . '/composer.lock'), true, 512, JSON_THROW_ON_ERROR);
$failures = [];
$cases = [
    '8.3.0' => false,
    '8.3.35' => false,
    '8.3.99' => false,
    '8.4.0' => false,
    '8.4.26' => false,
    '8.4.27' => true,
    '8.4.28' => true,
    '8.4.99' => true,
    '8.5.0' => false,
];

foreach (['composer.json' => $manifest['require']['php'], 'composer.lock' => $lock['platform']['php']] as $file => $constraint) {
    foreach ($cases as $version => $expected) {
        $actual = Composer\Semver\Semver::satisfies($version, $constraint);
        if ($actual !== $expected) {
            $failures[] = "$file: $version should be " . ($expected ? 'allowed' : 'rejected');
        }
    }
}

foreach (['composer.json' => $manifest['config']['platform']['php'], 'composer.lock' => $lock['platform-overrides']['php']] as $file => $version) {
    if ($version !== '8.4.27') {
        $failures[] = "$file: dependency resolution platform must be 8.4.27";
    }
}

foreach (array_merge($lock['packages'], $lock['packages-dev']) as $package) {
    if (isset($package['require']['php']) && !Composer\Semver\Semver::satisfies('8.4.27', $package['require']['php'])) {
        $failures[] = $package['name'] . ' does not support PHP 8.4.27';
    }
}

if ($failures !== []) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}
echo "PHP requirement boundaries, resolution platform and locked dependencies: PASS\n";
