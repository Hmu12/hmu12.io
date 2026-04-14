-- Run this once in phpMyAdmin or MySQL to allow storing base64 images in the DB
-- Go to phpMyAdmin > mywebsite > SQL tab, paste and run this

ALTER TABLE restaurants MODIFY COLUMN image MEDIUMTEXT;
