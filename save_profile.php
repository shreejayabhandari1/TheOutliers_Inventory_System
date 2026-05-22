<?php
// Profile is handled directly in client/profile.php via POST
// This file is a stub redirect for safety
require_once __DIR__ . '/../../includes/session.php';
header("Location: /storehub/client/profile.php");
exit();
