<?php

declare(strict_types=1);

require_once __DIR__ . '/function.php';

logout_user();

header('Location: login.php');
exit;
