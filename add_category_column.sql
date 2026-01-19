-- Script SQL per aggiungere la colonna category alla tabella events
-- Eseguire questo script se la migration non funziona

ALTER TABLE `events` 
ADD COLUMN `category` VARCHAR(50) NULL AFTER `description`;
