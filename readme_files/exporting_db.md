📂 Database Export Guide (For Collaboration)
To make sure the .sql files work on both XAMPP (MariaDB) and MySQL 8.0, please follow these steps when exporting:

1. The Export Settings (One-time Setup)
   When you are ready to export the prcsystem_db:
   Open phpMyAdmin and click on your database.
   Click the Export tab at the top.
   Select Custom - display all possible options.
   Scroll down to the Format-specific options section.
   Find the dropdown: "Database system or older MySQL server to maximize output compatibility with:"
   Select MYSQL40 (This prevents the "Incompatible Collation" errors).
   Ensure "Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER statement" is checked.
   Click Go to download the file.

2. Common Fix (If the error still happens)
   If I get a "Collation Mismatch" error, I might ask you to do a quick "Find and Replace" in your SQL file before sending:
   Find: COLLATE=utf8mb4_general_ci
   Replace with: (Leave this blank to remove it)
   Result: ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; (This is the "Universal" safe version).

Why we do this
MySQL 8 (My PC): Uses a new, strict way of sorting text (utf8mb4_0900_ai_ci).
XAMPP (Your PC): Uses an older version (utf8mb4_general_ci).
The Conflict: If we don't use the MYSQL40 setting, my computer tries to "force" your settings onto my tables, which breaks the Foreign Keys.
