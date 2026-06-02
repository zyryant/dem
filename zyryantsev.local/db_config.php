<?php
/**
 * НАСТРОЙКИ ПОДКЛЮЧЕНИЯ К БАЗЕ ДАННЫХ.
 * Это единственный файл, который нужно править при запуске на OpenServer.
 *
 * Поменяйте 'dbname' на имя базы данных, которую вы создали в phpMyAdmin.
 * Логин root и пустой пароль — стандартные для OpenServer.
 */

return [
    'host'     => '127.0.1.28',
    'dbname'   => 'zyryantsev',    
    'user'     => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];
