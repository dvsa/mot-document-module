<?php

namespace DvsaDocumentModuleTest;

use DvsaDocumentModuleTest\TestBootstrap;

error_reporting(E_ALL | E_STRICT);
chdir(dirname(__DIR__));

/** @var \Composer\Autoload\ClassLoader */
$loader = require('vendor/autoload.php');
$loader->addPsr4('DvsaDocumentModuleTest\\', __DIR__);

TestBootstrap::init();
