<?php
require_once __DIR__ . '/../config.php';
session_destroy();
header('Location: /event-manage/public/index.php');