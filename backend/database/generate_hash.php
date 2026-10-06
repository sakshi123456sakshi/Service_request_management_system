<?php
/**
 * Run this once from the command line to print a bcrypt hash for the
 * seed password, if you ever change it:
 *   php generate_hash.php "Password@123"
 */
$password = $argv[1] ?? 'Password@123';
echo password_hash($password, PASSWORD_BCRYPT) . PHP_EOL;
