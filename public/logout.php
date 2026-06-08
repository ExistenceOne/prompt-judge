<?php
require __DIR__ . '/../src/bootstrap.php';

logout();

// Start a fresh session so the goodbye flash survives the redirect.
session_start();
session_regenerate_id(true);
flash('로그아웃 되었습니다.', 'info');
redirect('index.php');
