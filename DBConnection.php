<?php
if(!is_dir('./db'))
    mkdir('./db');
if(!defined('db_file')) define('db_file','./db/attendance_db.db');
function my_udf_md5($string) {
    return md5($string);
}

Class DBConnection extends SQLite3{
    protected $db;
    function __construct(){
         $this->open(db_file);
         $this->createFunction('md5', 'my_udf_md5');
         $this->exec("PRAGMA foreign_keys = ON;");
         $this->exec("CREATE TABLE IF NOT EXISTS `user_list` (
            `user_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `fullname` INTEGER NOT NULL,
            `username` TEXT NOT NULL,
            `password` TEXT NOT NULL,
            `type` INTEGER NOT NULL,
            `status` INTEGER NOT NULL Default 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"); 
        $this->exec("CREATE TABLE IF NOT EXISTS department_list (
            `department_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS designation_list (
            `designation_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $this->exec("CREATE TABLE IF NOT EXISTS att_type_list (
            `att_type_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `name` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

         $this->exec("CREATE TABLE IF NOT EXISTS employee_list (
            `employee_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `employee_code` TEXT NOT NULL,
            `firstname` TEXT NOT NULL,
            `middlename` TEXT NULL,
            `lastname` TEXT NOT NULL,
            `gender` TEXT NOT NULL,
            `department_id` INTEGER NULL,
            `designation_id` INTEGER NULL,
            `contact` TEXT NOT NULL,
            `email` TEXT NOT NULL,
            `status` INTEGER NOT NULL DEFAULT 1,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP NULL,
            FOREIGN KEY(`department_id`) REFERENCES `department_list`(`department_id`) ON DELETE SET NULL,
            FOREIGN KEY(`designation_id`) REFERENCES `designation_list`(`designation_id`) ON DELETE SET NULL
        )");

        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_emp AFTER UPDATE on `employee_list`
        BEGIN
            UPDATE `employee_list` SET date_updated = CURRENT_TIMESTAMP where employee_id = employee_id;
        END
        ");

        $this->exec("CREATE TABLE IF NOT EXISTS attendance_list (
            `attendance_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `employee_id` TEXT NOT NULL,
            `att_type_id` TEXT NOT NULL,
            `date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `date_updated` TIMESTAMP NULL,
            FOREIGN KEY(`employee_id`) REFERENCES `employee_list`(`employee_id`) ON DELETE CASCADE,
            FOREIGN KEY(`att_type_id`) REFERENCES `att_type_list`(`att_type_id`) ON DELETE CASCADE
        )");
        $this->exec("CREATE TRIGGER IF NOT EXISTS updatedTime_att AFTER UPDATE on `attendance_list`
        BEGIN
            UPDATE `attendance_list` SET date_updated = CURRENT_TIMESTAMP where attendance_id = attendance_id;
        END
        ");
       
        $this->exec("INSERT or IGNORE INTO `user_list` VALUES (1,'Administrator','admin',md5('admin123'),1,1, CURRENT_TIMESTAMP)");

        $this->exec("INSERT or IGNORE INTO `att_type_list` VALUES 
        (1,'Time In',1, CURRENT_TIMESTAMP),
        (2,'Time Out',1, CURRENT_TIMESTAMP),
        (3,'OT In',1, CURRENT_TIMESTAMP),
        (4,'OT Out',1, CURRENT_TIMESTAMP)");
        
    }
    function __destruct(){
         $this->close();
    }
}

$conn = new DBConnection();