<?php
$lang = array (
	'appmarket' => 'Application market',
    'installed' => 'installed',
    'upgrade' => 'Upgrade',
    'open_way'=>'Open mode',
    'permission_management'=>'Authority management',
    'system_cant_disable'=>'System apps cannot use this action',
    'app_newest'=>'Scalable app',
    'new_edition_function'=>'New features',
    'enable_file_disappear'=>'Application open execution script file missing',
	'app'=>'application',
	'ge'=>'one',
	'selected'=>'chosen',
	'update_onekey'=>'One-click upgrade',
	'install_onekey'=>'A key installation',
	'appname'=>'Application market',
	'no_need_update_applist'=>'No apps to update',
	'appInLocal'=>'Local application',
    'app_upgrade_uninstall_successful'=>'Application uninstallation is successful!{upgradeurl}',
    'app_upgrade_check_need_update2' => 'Detect new version',
    
    'app_upgrade_check_is_install' => 'Check if it is installed...',
    'app_upgrade_check_need_update' => 'Detect new version...',
    'app_upgrade_to_lastversion' => 'Already the latest version',
    'app_upgrade_installed' => 'The app is already installed',
    'app_upgrade_installed_local' => 'Installed local app conflicts with the app',
    'app_upgrade_identifier_error' => 'Application ID is empty',
    'app_upgrade_dzzversion_error' => 'oaooa version requirements: {version}',
    'app_upgrade_phpversion_error' => 'Php version requirements: {version}',
    'app_upgrade_mysqlversion_error' => 'Mysql Version requirements: {version}',
    'app_upgrade_newversion_will_start'=>'Upgrade is about to begin',
    'app_upgrade_newversioninfo_error'=>'The version information is empty. Please check the update again.',
    'app_upgrade_newversion_folder_error'=> 'The new version of the directory already exists:{path}',
    'app_upgrade_newversion_start'=> 'The upgrade begins...',
    'app_upgrade_newversion_ing'=> 'upgrading...',
    
    'app_upgrade_data_error' => 'Data error, please refresh and try again',
    'app_upgrade_none' => 'Installation or update file missing {upgradeurl}', //-1
    'app_upgrade_exchange_none' => 'No verification file exists {upgradeurl}',//-9
    'app_upgrade_downloading' => 'To be updated or installed in the file download...',
    'app_upgrade_downloading_error' => 'There is a problem with the file {file} download, please ensure that the network connection and data directory write permission {upgradeurl}',//-3
    'app_upgrade_download_complete' => 'Pending update or installation file download completed {upgradeurl}',//-2 
    'app_upgrade_download_complete_to_compare' => 'To be updated or the installation file is downloaded, the local file comparison will be performed {upgradeurl}', //待修改，，，，
    'app_upgrade_downloading_file' => 'Downloading update file from official {file} <br>completed {percent} {upgradeurl}',
    'app_upgrade_check_download_complete' => 'Check application download integrity...',
    'app_upgrade_installing' => 'Application file installation...', 
    'app_upgrade_cannot_access_file' => 'Directory and files have no modification rights, please fill in the ftp account or modify the file permissions to read and write and try again {upgradeurl}',//-4
    'app_upgrade_ftp_upload_error' => 'Ftp upload file {file} error, please modify file permissions and re-upload or reset ftp account {upgradeurl}',//-6
    'app_upgrade_copy_error' => 'Error copying file {file}, please check if the original file exists, re-copy or upload the copied file via ftp {upgradeurl}',//-7
    'app_upgrade_move_success' => 'Application file copying is complete... imminently entering the database installation {upgradeurl}',
    'app_upgrade_xmlfile_error'=> 'Application file {file} is missing {upgradeurl}',//-8
    'app_upgrade_install_will_success' => 'Installation is almost complete...',
    'app_upgrade_install_success' => 'Successful installation Please go to the installed list to launch the app {upgradeurl}',//1
    'app_upgrade_already_downloadfile' => 'Prepare to download the update file...',
    'app_upgrade_backuping' => 'Backing up the original file... {upgradeurl}', //2
    'app_upgrade_backup_error' => 'Error backing up the original file {upgradeurl}',//-5
    'app_upgrade_backup_complete' => 'Backup is complete, upgrade is in progress... {upgradeurl}',//3
    'app_upgrade_file_success' => 'The file upgrade is successful and will enter the update database. {upgradeurl}',//4
    'app_upgrade_database_success' => 'Update database successfully {upgradeurl}',//5
    'app_upgrade_newversion_will_success' => 'Phase upgrade is almost complete...',
    'app_upgrade_newversion_success' => 'update successed{upgradeurl}',//1
    
    'application_identifier'=>'Application identifier',
	'app_application_identifier_text'=>'<li>Apply unique identifier, please enter the English alphabet string, generally use the directory name of the corresponding application (English).</li>
								<li>If you encounter a duplicate name through online installation, it will be automatically renamed.</li>',
    'not_empty'=>'Can not be empty',
    'already_exist'=>'existed',
    'application_app_path'=>'Application path',
	'app_application_app_path_text'=>'<li>The path relative to the root of the site. If the path of the thing application relative to the root of the site is ./dzz/thame, then fill in the field. dzz </li>
                                    <li>If the application is a link type, fill in the string without a path link </li>',
    'application_appadminurl'=>'Management settings address',
	'app_application_appadminurl_text'=>'<li>Manage settings address, which can be a relative address (relative to the site root) or a network address</li>
									<li>The address of the application can have parameters such as：{dzzscript}?mod=document&op=textviewer&icoid={icoid}</li>
									<li>Parameters: wrap the parameters in the address with "{}", dzzscript: the main program (ie index.php), adminscript: the main program for the background management (ie admin.php), use this parameter to add the application when adding the application Compatibility and portability</li>
									<li>Fields in the dzz_resources table can be brought in as parameters</li>',
    'app_delete_confirm'=>'This will delete the app: <b>{appname}</b> &nbsp;All data, please be careful<br /><br />If you really need to delete, please enter DELETE below to confirm the deletion.',
    'app_sure_delete'=>'Determine uninstall application',
    'app_not_delete'=>'Do not uninstall',
    'installed'=>'Installed',
    'newest'=>'up to date',
	'buy'=>'buy',
	'view_detail'=>'View details',
	'buy_contract'=>'Contact purchase',
	'manual_install_tip'=>'Note: If you cannot install online, please pass <a class="num" href="http://help.oaooa.com/index.php?mod=dzzmarket" target="_blank">Official application market</a> Download application installation package manually download',
	'no_new_updates_were_detected'=>'No new updates detected',
	'upgrading_in_progress_please_wait_a_minute'=>'Upgrading, please wait...',
	'open_app'=>'Open app',
	'install_app'=>'Install app',
	'directory_overwriting'=>'The directory already exists, please rename the directory or remove it to prevent duplication or overwriting',
	'prepare_installation'=>'Prepare before installation...',
	'Application_acquisition'=>'Application file list acquisition...',
	'Application_downloaded'=>'Application file will be downloaded soon...',
	'Update_application'=>'Update application',
	'not_exist'=>'Application does not exist'
);
?>