<?
;  /*
;  File : conf/users.conf
;  This file specifies a list of allowed users to interact with pDB
;  One line is used for each user.
;
; 	Example :
; 	user=password:allowed_db1,allowed_db2,allowed_db3
;
;  NOTE : Put a (=) after your username,
;  a colon (:) after your password,
;  while the dbs are delimeted with commas (,)
;
;  ATTENTION : Using incorrect syntax in this file may cause
;  pDB stop workin correctly or at all !
;
;  Create private DBS here
pDB_GUI=pedebe:_private
;
;  For the administrator there's a standard entry named 'root'.
;  Please CHANGE the password)
;  NOTE : 	You don't have to define any database after the root-password,
; 			cause root has access to everything !
root=pDB
;
;
;  Define pDB users and their respective DB's here
user=password:
; */
?>
