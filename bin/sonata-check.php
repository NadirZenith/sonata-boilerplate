<?php


/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if (!is_file('composer.json')) {
    throw new \RuntimeException('Can\'t find a composer.json file. Make sure to start this script from the project root folder');
}

$checks = array();
if (extension_loaded('gd')) {
    $checks[] = array('OK', 'gd is installed');

    $info = gd_info();

    if ($info['JPEG Support']) {
        $checks[] = array('OK', 'gd with jpg support is installed');
    } else {
        $checks[] = array('KO', 'missing gd with jpg support');
    }

    if ($info['PNG Support']) {
        $checks[] = array('OK', 'gd with png support is installed');
    } else {
        $checks[] = array('KO', 'missing png with jpg support');
    }
} else {
    $checks[] = array('KO', 'gd is not installed');
}


if (class_exists('Locale')) {
    $checks[] = array('OK', "php-intl extension is installed");
} else {
    $checks[] = array('KO', "Missing php-intl extension");
}

if (is_file('app/check.php')) {
    // find out the default php runtime
    $bin = 'php';

    if (defined('PHP_BINARY')) {
        $bin = PHP_BINARY;
    }

    exec(sprintf('%s app/check.php', $bin), $output, $exit);

    if ($exit === 1) {
        $checks[] = array('KO', "Failed to valid Symfony2's requirements!");

        foreach ($output as $line) {
            $checks[] = array(' ->', $line );
        }

    } else {
        $checks[] = array('OK', "Successfully valid Symfony2's requirements");
    }
}

if (!function_exists('mb_strlen')) {
    $checks[] = array('KO', "Install and enable the <strong>mbstring</strong> extension.");
} else {
    $checks[] = array('OK', 'mb_strlen() is available');
}

if (!class_exists('PDO')) {
    $checks[] = array('KO', 'PDO must be installed');
} else {
    $checks[] = array('OK', "PDO is available");

    $drivers = PDO::getAvailableDrivers();

    if (!in_array("mysql", $drivers)) {
        $checks[] = array('KO', "PDO mysql is not available");
    } else {
        $checks[] = array('OK', "PDO mysql is available");
    }
}

if (!function_exists('bcscale')) {
    $checks[] = array('KO', "bcmath extension is not available");
} else {
    $checks[] = array('OK', "bcmath extension is available");
}

if (!function_exists('curl_init')) {
    $checks[] = array('KO', "curl extension is not available");
} else {
    $checks[] = array('OK', "curl extension is available");
}

if (!extension_loaded('xdebug')) {
    $checks[] = array('KO', "xdebug is not available");
}else if (extension_loaded('xdebug') && ini_get('xdebug.max_nesting_level') < 255) {
    $checks[] = array('KO', "xdebug.max_nesting_level is too low, please make sure the value is greater than 255");
} else {
    $checks[] = array('OK', "xdebug is available");
}

$error = 0;

foreach ($checks as $check) {
    echo sprintf("%s\t%s\n", $check[0], $check[1]);

    if ($check[0] === 'KO') {
        $error = 1;
    }
}

echo "\n";

if ($error) {
    echo "Failed to validate mandatory requirements\n";
} else {
    echo "Successfully validate mandatory requirements\n";
}

exit($error);
