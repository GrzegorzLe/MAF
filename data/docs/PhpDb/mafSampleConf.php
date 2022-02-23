<?php array(
	'PDB' => array(
		/* pDB is a free database written in php.
		 * This is the main configuration of pDB.
		 * this software is distributed under the LGPL. */
		'CONF_DATA' => array(
			/* Change this to whatever you want
			 * Can be useful to distinguish different configs or installations */
			'APP_NAME' => 'pDB',
			/* OPERATINGSYSTEM TYPE
			 * Please define the type of Operating-System you plan to run pDB on (eg: Linux or WIN32)
			 * HINT: You can disable this line to let php detect the OS */
			'OSTYPE' => 'Linux',
			/* Please set the path of your BASE_DIR (base directory, something like:/var/www/html/POPE/)
			 * If pDB is not working at all, common pitfall is that this one is wrong !!
			 * Do not forget the slash as last char '/' */
			'BASE_DIR' => '',
			/* Please set the path to a writable folder in your filesystem. This is the folder where all your databases are gonna reside.
			 * This path must always be ABSOLUTE in order to guarantee that DB_ROOT can be everywhere in your local filesystem.
			 * It is good practice to NOT place your DB_ROOT under your WEB_ROOT. But under certain circumstances it's unavoidable, 
			 * so the default settings will always point into WEB_ROOT. Feel free to change this to secure your data from beeing accessed directly.
			 * Example: /var/www/html/DB_ROOT/
			 * Do not forget the slash as last char '/' */
			'DB_ROOT' => '',
			/* Please define path to folder (MUST be under BASE_DIR) where pDB's configuration 
			 * files ( 'pDB.conf.php', 'pDB.users.php') reside.
			 * Example: conf/  ->  Now pDB will look for config in folder {BASE_DIR}/conf/ */
			'CONF_DIR' => '',
			/* Define the number of wrong logins allowed before ban applies.
			 * THIS DIRECTIVE IS NOT SUPPORTED IN CURRENT VERSION OF PDB !!! */
			'MAX_LOGINS' => '',
			/* LOG FILE
			 * Path to log file.
			 * Please make sure you have write-permissions for this file.
			 * BASE_DIR is automatically prefixed ! */
			'LOG_FILE' => '',
			/* DEBUG
			 * Enable DEBUG-mode by setting this to 1
			 * WARNING : This directive produces some verbose ouput to 'php://stdout'. 
			 * In certain situations this could turn out very annoying.
			 * In case of error this directive, if enabled, can output sensible 
			 * informations concerning your pDBlib-installation.
			 * Enable this ONLY while debugging your scripts. */
			'DEBUG' => '',
			/* VERBOSE
			 * Enable VERBOSE-mode by setting this to 1
			 * WARNING : This directive produces some verbose ouput to 'php://stdout'. 
			 * In certain situations this could turn out very annoying.
			 * In case of an error, this directive can output sensible informations concerning your pDBlib-installation, when enabled.
			 * Enable this ONLY while debugging your scripts. */
			'VERBOSE' => '',
			/* Enable or disable pDBs internal Accelerator-method.
			 * This option is only available using pDB-0.37a or above. */
			'ACCELERATED' => '',
			/* DO NOT CHANGE THIS DIRECTIVE
			 * This directive is always last and should always be 1 */
			'CONF_LOADED' => '1',
		),
		/*
		 * This specifies a list of allowed users to interact with pDB
		 * One line is used for each user.
		 * Example:
		 * 'user' => 'password:allowed_db1,allowed_db2,allowed_db3'
		 * NOTE : Put a colon (:) after your password, while the dbs are delimeted with commas (,) */
		'USER_DATA' => array(
			/* For the administrator there's a standard entry named 'root'. */
			'root' => 'pDB',
			/* Define pDB users and their respective DB's here */
			'dbuser' => 'password:db_1,db_2',
		),
	)
);
