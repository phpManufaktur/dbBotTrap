### dbBotTrap

This experimental Admin-Tool for the Content Management Systems [WebsiteBaker] [1] or [LEPTON CMS] [2] enable you to view the Bot-Trap Logfiles which are created by a special file of the [Bot-Trap Project] [3].

#### Requirements

* minimum PHP 5.2.x
* using [WebsiteBaker] [1] _or_ [LEPTON CMS] [2]
* Add-on [rhTools] [4] is installed

#### Installation

* download the actual [dbBotTrap_x.xx.zip] [5] installation archive
* in the Backend of the Content Management System select the installation archive from "Add-ons" -> "Modules" -> "Install module"
 
Now you must enable the tracking and blocking functions of Bot-Trap.

* copy the file `/modules/dbbottrap/page.restrictor.php` to the root directory `/` of your domain (along with the `config.php`)
* open the `config.php` with a texteditor

Insert the following code **at the top** of the `config.php`:

    // init Page Restrictor first...
    if (file_exists(dirname(__FILE__).'/page.restrictor.php')) {
      $logfile = dirname(__FILE__).'/modules/dbbottrap/logs/'.date('Ymd').'.log';
      define('PRES_LOG_FILE', $logfile);
      require_once(dirname(__FILE__).'/page.restrictor.php'); 
    }
    
Save the `config.php` at the webserver and dbBotTrap is ready for use.

#### First steps

Open dbBotTrap in the backend of the Content Management System "Admin-Tools" -> "dbBotTrap".

dbBotTrap will prompt you the status of the Bot-Trap function. The list will be empty at this time. 

You may test the installation with the [Bot-Trap Test Site] [6].

Please visit the Website of the Bot-Trap Project [3] to get further informations.

[1]: http://websitebaker2.org
[2]: http://lepton-cms.org
[3]: http://www.bot-trap.de
[4]: https://github.com/phpManufaktur/rhTools/downloads
[5]: https://github.com/phpManufaktur/dbBotTrap/downloads
[6]: http://www.korizon.de/bottraptest.html
    