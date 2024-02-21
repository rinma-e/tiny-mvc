# About TinyMVC

Personal project to explore MVC structure. It comes with built in login/registration system. Built with (also min requirements):

- HTML5,
- CSS3,
- JavaScript,
- jQuery 3.1 (included in repo),
- Bootstrap 5.3 (included in repo),
- PHP 8.0,
- MySQL 8.0.

# Quick start

## Installation

1. Make sure your envirement has minimum requirements met for PHP and MySQL,
2. Clone this repo,
3. In 'app/config/config.php' set your database configuration,
4. Open your consol and navigate to folder where TinyMVC is cloned,
5. For any system: run 'php setup-script.php'. This will create database with name specified in config file and create necessary tables for login/registration.<br>
   NOTE: Avast or AVG may alert for php files to contain treat when run from shell. If this happens when You run this script just put it to exception,
7. For each step (4 steps) You will be prompted,
8. If you like to reset databese or reset any of tables just run setup script again and follow prompt,
9. When database and tables are installed, in your browser navigate to TinyMVC folder on your host,
10. In repo is also .sql file if you need to install database and tables manually just make sure to change database name in .sql file.

## Info

### Routing

Dynamic routing that is using prity URLs. URL structure is controller/method/parameters.

### Core

_File requests:_<br>
All file request are routed to public folder and index.php with .htaccess file. Public folder will never show in URL.

_Core modes:_<br>
Core mode is set in config file. Core have two modes:

1.  CORE_SHOW_ERROR set to true => when error ocurs due to controller/method not found it redirects to error page,
2.  CORE_SHOW_ERROR set to false => when error ocurs due to controller/method not found it redirest to default controller/method that are set in config file.

### Folder structure and file naming convention:

There are folders 'controllers', 'models' and 'views' where you put all coresponding files. They are root folders in which files are looked for. All controllers must end with "Controller.php" and all models with "Model.php". For folders 'controllers' and 'models' no subfolders are supported. For 'view' folder any subfolder structure is alowed just need to include all subfolders in path to view file you are calling.

### Errors handeling

Core class 'ErrorsHandler.php' is using 'ErrorController.php' for showing frendly errors to users if DEVELOPMENT is set to 'false' in config file. ErrorController is simple and is just calling error views (404.phtml and 500.phtml). So **don't** delete 'ErrorController.php' just adjust if needed to your needs.

### Plugins

All js and jQ plugins are in 'public/assets/plugins' folder.
