<?php
require __DIR__ . '/../src/bootstrap.php';

logout();

// Start a fresh session so the goodbye flash survives the redirect.
session_start();
session_regenerate_id(true);
flash('You have been logged out.', 'info');
redirect('index.php');
