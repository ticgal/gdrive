<?php
/*
-------------------------------------------------------------------------
Gdrive plugin for GLPI
Copyright (C) 2018 by the TICgal Team.
https://github.com/pluginsGLPI/gdrive
-------------------------------------------------------------------------
LICENSE
This file is part of the Gdrive plugin.
Gdrive plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.
Gdrive plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with Gdrive. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
@package   gdrive
@author    the TICgal team
@copyright Copyright (c) 2018 TICgal team
@license   AGPL License 3.0 or (at your option) any later version
http://www.gnu.org/licenses/agpl-3.0-standalone.html
@link      https://tic.gal
@since     2018
---------------------------------------------------------------------- */
if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class PluginGdriveTicket extends CommonDBTM
{

	public static $rightname = 'ticket';

	static function getTypeName($nb = 0)
	{
		return 'GDrive';
	}

	public static function getIcon()
	{
		return "fa-brands fa-google-drive fa-rotate-180";
	}

	static public function postForm($params)
	{
		global $CFG_GLPI;
		$item = $params['item'];
		$config = PluginGdriveConfig::getConfig();

		switch ($item->getType()) {
			//case 'Ticket':
			case 'ITILFollowup':
			case 'ITILSolution':
			case 'Document_Item':
			case 'TicketTask':
			case 'ProblemTask':
			case 'ChangeTask':
			case 'TicketValidation':
			case 'ChangeValidation':
				echo self::addGdriveScripts($config);
				echo self::addGdriveButton($config);
				break;
		}
	}

	static public function postTab($params)
	{
		global $CFG_GLPI;
		$item = $params['item'];
		$itemtype = $params['options']['itemtype'];
		$config = PluginGdriveConfig::getConfig();

		switch ($item->getType()) {
			case 'Computer':
			case 'Monitor':
			case 'Software':
			case 'NetworkEquipment':
			case 'Peripheral':
			case 'Printer':
			case 'CartridgeItem':
			case 'ConsumableItem':
			case 'Phone':
			case 'Rack':
			case 'Enclosure':
			case 'PDU':
			case 'PassiveDCEquipment':
			//case 'Unmanaged':
			//case 'Cable':
			case 'Item_DeviceSimcard':
				if ($itemtype == 'Document_Item') {
					echo '<script type="text/javascript" src="/public/lib/tinymce.min.js"></script>';
					echo self::addGdriveScripts($config);
					echo self::addGdriveButton($config);

					$out = '<script>
					var btnExist = $("#gdrivebtn");
					$(".firstbloc").append(btnExist);
					</script>';

					echo $out;
				}
				break;
		}
	}

	public static function addGdriveScripts($config)
	{
		$out = '';
		$out .= '<script src="https://accounts.google.com/gsi/client" async defer></script>';

		$out .= "<script type='text/javascript'>
				// The Browser API key obtained from the Google API Console.
				// Replace with your own Browser API key, or your own key.
				var developerKey = '" . $config->fields['developer_key'] . "';

				// The Client ID obtained from the Google API Console. Replace with your own Client ID.
				var clientId = '" . $config->fields['client_id'] . "';

				// Replace with your own project number from console.developers.google.com.
				// See 'Project number' under 'IAM & Admin' > 'Settings'
				var appId = '" . $config->fields['app_id'] . "';

				// Scope: See and download Google Drive files
				var scope = 'https://www.googleapis.com/auth/drive.readonly';

				var tokenClient;
				var access_token;
				var pickerInited = false;
				var idEditor = 0;

				// Use the Google API Loader script to load the google.picker script.
				function loadPicker() {
					gapi.load('client', {'callback': initClient});
					gapi.load('picker', {'callback': initializePicker});
					enableButtons();
				}

				/**
				 * Callback after the API client is loaded. Loads the
				 * discovery doc to initialize the API.
				 */
				async function initializePicker() {
					await gapi.client.load('https://www.googleapis.com/discovery/v1/apis/drive/v3/rest');
					pickerInited = true;
				}

				async function initClient(){
					tokenClient = google.accounts.oauth2.initTokenClient({
						client_id: clientId,
						scope: scope,
						prompt: '',
						callback: (tokenResponse) => {
							handleAuthResult(tokenResponse);
						},
					});
				}

				function handleAuthResult(authResult) {
					if (authResult && !authResult.error) {
						access_token = authResult.access_token;
						document.cookie='access_token='+access_token;
						createPicker();
					}else{
						if(authResult.error=='popup_closed_by_user'){
							access_token=getCookie('access_token');
							createPicker();
						}else{
							alert(authResult.error);
						}
					}
				}

				function getToken(){
					tokenClient.requestAccessToken();
				}

				function revokeToken() {
					google.accounts.oauth2.revoke(access_token, () => {console.log('access token revoked')});
				}

				function enableButtons() {
					var authBtns = $('.authbtn');

					for (let btn of authBtns) {
						var form = btn.parentElement.parentElement.parentElement;
						var inputs = form.getElementsByTagName('input');
						var fileupload;
						for(var input of inputs){
							if(input.id.includes('fileupload')){
								fileupload = input.id;
								break;
							};
						}
						
						btn.disabled = false;
						btn.value = fileupload;
						btn.addEventListener('click', function() {
							idEditor = btn.value;
							getToken();
						});
					}
				}

				//Get content of cookie
				function getCookie(cname){
					var name = cname + '=';
					var decodedCookie = decodeURIComponent(document.cookie);
					var ca = decodedCookie.split(';');
					for(var i = 0; i <ca.length; i++) {
						var c = ca[i];
						while (c.charAt(0) == ' ') {
							c = c.substring(1);
						}
						if (c.indexOf(name) == 0) {
							return c.substring(name.length, c.length);
						}
					}
					return '';
				}

				// Create and render a Picker object for picking user Documents.
				function createPicker() {
					var picker = new google.picker.PickerBuilder()
					.enableFeature(google.picker.Feature.MULTISELECT_ENABLED)
					.setAppId(appId)
					.addView(google.picker.ViewId.DOCS)
					.setOAuthToken(access_token)
					.setDeveloperKey(developerKey)
					.setCallback(pickerCallback)
					.build();
					
					picker.setVisible(true);
					
					//z-index issues with GLPI menu
					var elements= document.getElementsByClassName('picker-dialog');
					for(var i=0;i<elements.length;i++)
					{
						elements[i].style.zIndex = '2000';
					}
				}

				// A simple callback implementation.
				function pickerCallback(data) {
					var message = '" . __('Uploaded file', 'gdrive') . "';
					if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
						var fileInput=$('#' + idEditor)[0];
						for(var i=0;i<data[google.picker.Response.DOCUMENTS].length;i++){
							var file=data[google.picker.Response.DOCUMENTS][i];
							downloadFile(file,fileInput,function(res){
								if(data==false){
									message='" . __('Error loading the file', 'gdrive') . "';
								} else {
									document.getElementById('result').innerHTML = ' - ' + message;
								}
							});
						}
					}
				}

				function downloadFile(file, fileInput, callback){
					if (file[google.picker.Document.URL]) {
						var accessToken = access_token;
						var xhr = new XMLHttpRequest();
						xhr.responseType = 'blob';
						xhr.open('GET', 'https://www.googleapis.com/drive/v3/files/'+file[google.picker.Document.ID]+'?alt=media');
						xhr.setRequestHeader('Authorization', 'Bearer ' + accessToken);
						xhr.onload = function() {
							var fil = new File([xhr.response], file[google.picker.Document.NAME], {type: file[google.picker.Document.MIME_TYPE], lastModified: Date.now()});
							//var editor = {targetElm: fileInput};
							var editor = new tinymce.Editor(fileInput.id, {}, tinymce.EditorManager);
							var fileTag = uploadFile(fil,editor);
							callback(true);
						};
						xhr.onerror = function() {
							alert('Error: '+xhr.error);
							callback(false);
						};
						xhr.send();
					} else {
						alert('Sin url');
						callback(false);
					}
				}

				function handleCredentialResponse(response) {
					const responsePayload = decodeResponse(response.credential);
					if(responsePayload.sub){
						loadPicker();
					}
				}
				
				function decodeResponse (token) {
					var base64Url = token.split('.')[1];
					var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
					var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
						return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
					}).join(''));

					return JSON.parse(jsonPayload);
				}

				</script>";

		$out .= '<script async defer src="https://apis.google.com/js/api.js"></script>';

		return $out;
	}

	public static function addGdriveButton($config)
	{
		$out = '';

		$out .= "<div class='d-flex flex-column' id='gdrivebtn'>";

		$out .= "<tr><th colspan='2'><div class='d-flex'>
			<span class='mb-1'><i class='" . self::getIcon() . "'></i> " . self::getTypeName(2) . "</span>
			<div class='mb-1 ms-1' id='result'></div>
			</div></th></tr>";
		$out .= "<tr><td align='center'><div class='d-flex align-items-stretch mb-1'>";
		$out .= '<div id="g_id_onload"
					data-client_id="' . $config->fields['client_id'] . '"
					data-context="signin"
					data-auto_prompt="false"
					data-ux_mode="popup"
					data-callback="handleCredentialResponse">
				</div>
				<div class="g_id_signin" 
					data-type="standard"
					data-type="standard"
					data-shape="rectangular"
					data-theme="outline"
					data-text="signin_with"
					data-size="large"
					data-logo_alignment="left">
				</div>';
		$out .= "<button type='button' class='btn authbtn flex-grow-1' id='auth' disabled>" . __('Select file', 'gdrive') . "</button>";
		$out .= "</div></td></tr>";

		$out .= '</div>';

		return $out;
	}
}