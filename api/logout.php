<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

session_destroy();
json_out(['ok' => true]);
