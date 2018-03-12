# men-to-zen-migration
Menalto (Gallery3) to Zenphoto Migration

Hello migrants!

After a few years of hibernation by Menalto (Gallery3) I switched to Zenphoto and wrote PHP scripts for the migration, which you are welcome to use, at your own risk.


What to consider:

First install Zenphoto completely.
You can also test Zenphoto first.

Tip: copy the database tables "albums", "images", "tags" and "obj_to_tag", right after the installation, if they are still empty. After trying out Zenphoto, you can then swap them for the originals before starting the migration. It is best to always keep an empty original of these tables and copy this only. After migration and checking for any errors, you can delete the blank backups.  However, Zenphoto is designed to run at any time a setup that creates new database tables that do not exist.

After testing Zenphoto, delete all test photos and test albums, in the backend, from the database and folder /albums.
Then upload all your photo files to the folder /albums.
Start a scan in the backend (Overview -> Refresh database), so that all albums and photo files are entered in the database.
Now use the migration scripts at your own risk.

If you use tables Prefixe in the database, they must be written in the right place in the script.

With the albums and images script you can do a test run by commenting out the lines of the SQL UPDATE at the end and the associated if/else (successfull/error) rule. In addition, an (commented-out) option for a limited test run is included (value $i with continue and break).

Start the Scripts:

Please don't start the PHP Scripts in a Browser, if you have many Photos. Please use the command line on a shell like this:

```php migrate-albums.php | tee logfile-migrate-albums.txt```

The "tee" is for a logfile. Please look into the logfile, when the script has finished, and search for errors and warnings.

good byte!
