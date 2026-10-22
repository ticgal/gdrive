<?php

/**
 * -------------------------------------------------------------------------
 * Gdrive plugin for GLPI
 * Copyright (C) 2026 by the TICGAL Team.
 * https://github.com/pluginsGLPI/gdrive
 * -------------------------------------------------------------------------
 * LICENSE
 * This file is part of the Gdrive plugin.
 * Gdrive plugin is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * Gdrive plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Gdrive. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 * @package   gdrive
 * @author    the TICGAL team
 * @copyright Copyright (c) 2018-2026 TICGAL team
 * @license   AGPL License 3.0 or (at your option) any later version
 * http://www.gnu.org/licenses/agpl-3.0-standalone.html
 * @link      https://tic.gal
 * @since     2018
 * --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginGdriveTicket extends CommonDBTM
{
    public static $rightname = 'ticket';

    private static bool $scripts_added = false;

    /**
     * getTypeName
     *
     * @param  mixed $nb
     * @return string
     */
    public static function getTypeName($nb = 0): string
    {
        return 'GDrive';
    }

    /**
     * getIcon
     *
     * @return string
     */
    public static function getIcon(): string
    {
        return "fa-brands fa-google-drive fa-rotate-180";
    }

    /**
     * postForm
     *
     * @param  mixed $params
     * @return void
     */
    public static function postForm($params): void
    {
        if (!Session::haveRight(PluginGdriveProfile::RIGHTNAME, READ)) {
            return;
        }

        $item = $params['item'];
        $config = PluginGdriveConfig::getConfig();
        if (!$config->isConfigured()) {
            return;
        }

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

    /**
     * postTab
     *
     * @param  mixed $params
     * @return void
     */
    public static function postTab($params): void
    {
        if (!Session::haveRight(PluginGdriveProfile::RIGHTNAME, READ)) {
            return;
        }

        $item = $params['item'];
        $itemtype = $params['options']['itemtype'] ?? '';
        $config = PluginGdriveConfig::getConfig();
        if (!$config->isConfigured()) {
            return;
        }

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
                    $rand = mt_rand();
                    echo self::addGdriveScripts($config);
                    echo self::addGdriveButton($config, $rand);

                    echo "<script>
                        $('.firstbloc').append($('#gdrivebtn$rand'));
                        </script>";
                }
                break;
        }
    }

    /**
     * addGdriveScripts
     *
     * The scripts are emitted once per request, however many forms show the button.
     *
     * @param  PluginGdriveConfig $config
     * @return string
     */
    public static function addGdriveScripts(PluginGdriveConfig $config): string
    {
        if (self::$scripts_added) {
            return '';
        }
        self::$scripts_added = true;

        $out = '<script src="https://accounts.google.com/gsi/client" async defer></script>';
        $out .= '<div id="g_id_onload"
                    data-client_id="' . htmlescape($config->fields['client_id']) . '"
                    data-context="signin"
                    data-auto_prompt="false"
                    data-ux_mode="popup"
                    data-callback="handleCredentialResponse">
                </div>';

        $out .= "<script type='text/javascript'>
                // The Browser API key obtained from the Google API Console.
                // Replace with your own Browser API key, or your own key.
                var developerKey = " . self::jsValue($config->fields['developer_key']) . ";

                // The Client ID obtained from the Google API Console. Replace with your own Client ID.
                var clientId = " . self::jsValue($config->fields['client_id']) . ";

                // Replace with your own project number from console.developers.google.com.
                // See 'Project number' under 'IAM & Admin' > 'Settings'
                var appId = " . self::jsValue($config->fields['app_id']) . ";

                // Expire the token cookie written by GDrive up to 3.0.0
                document.cookie = 'access_token=; Max-Age=0; path=' + window.location.pathname.replace(/\/[^\/]*$/, '');

                // Scope: See and download Google Drive files
                var scope = 'https://www.googleapis.com/auth/drive.readonly';

                var tokenClient;
                var access_token;
                var pickerInited = false;
                var idEditor = 0;
                var currentBlock = null;

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

                // The access token only lives in this page's memory: never persist it
                function handleAuthResult(authResult) {
                    if (authResult && !authResult.error) {
                        access_token = authResult.access_token;
                        createPicker();
                    } else if (authResult && authResult.error != 'popup_closed_by_user') {
                        alert(authResult.error);
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
                        var form = btn.closest('.gdrive-block').parentElement;
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
                            currentBlock = btn.closest('.gdrive-block');
                            getToken();
                        });
                    }
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
                    if (data[google.picker.Response.ACTION] == google.picker.Action.PICKED) {
                        var fileInput=$('#' + idEditor)[0];
                        var result = currentBlock ? currentBlock.querySelector('.gdrive-result') : null;
                        // One failed file keeps the error shown, whatever order the downloads finish in
                        var failed = false;
                        for(var i=0;i<data[google.picker.Response.DOCUMENTS].length;i++){
                            var file=data[google.picker.Response.DOCUMENTS][i];
                            downloadFile(file,fileInput,function(res){
                                failed = failed || !res;
                                if (result) {
                                    result.textContent = ' - ' + (!failed
                                        ? " . self::jsValue(__('Uploaded file', 'gdrive')) . "
                                        : " . self::jsValue(__('Error loading the file', 'gdrive')) . ");
                                }
                            });
                        }
                    }
                }

                // Google Docs, Sheets, Slides and Drawings have no binary content to download
                // (alt=media answers 403): they are exported to an Office format or PDF instead
                var exportFormats = {
                    'application/vnd.google-apps.document': {
                        mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        extension: 'docx'
                    },
                    'application/vnd.google-apps.spreadsheet': {
                        mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        extension: 'xlsx'
                    },
                    'application/vnd.google-apps.presentation': {
                        mimeType: 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                        extension: 'pptx'
                    },
                    'application/vnd.google-apps.drawing': {
                        mimeType: 'application/pdf',
                        extension: 'pdf'
                    }
                };

                function downloadFile(file, fileInput, callback){
                    if (!file[google.picker.Document.URL]) {
                        alert(" . self::jsValue(__('The selected file cannot be downloaded', 'gdrive')) . ");
                        callback(false);
                        return;
                    }

                    var name = file[google.picker.Document.NAME];
                    var mimeType = file[google.picker.Document.MIME_TYPE];
                    var url = 'https://www.googleapis.com/drive/v3/files/' + encodeURIComponent(file[google.picker.Document.ID]);
                    if (mimeType && mimeType.indexOf('application/vnd.google-apps.') === 0) {
                        var format = exportFormats[mimeType];
                        if (!format) {
                            // Forms, sites, folders...: nothing to export
                            alert(" . self::jsValue(__('The selected file cannot be downloaded', 'gdrive')) . ");
                            callback(false);
                            return;
                        }
                        url += '/export?mimeType=' + encodeURIComponent(format.mimeType);
                        mimeType = format.mimeType;
                        if (!name.toLowerCase().endsWith('.' + format.extension)) {
                            name += '.' + format.extension;
                        }
                    } else {
                        url += '?alt=media';
                    }

                    var xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob';
                    xhr.open('GET', url);
                    xhr.setRequestHeader('Authorization', 'Bearer ' + access_token);
                    xhr.onload = function() {
                        // An HTTP error still fires onload: its body is Google's error, not the file
                        if (xhr.status !== 200) {
                            callback(false);
                            return;
                        }
                        var fil = new File([xhr.response], name, {type: mimeType, lastModified: Date.now()});
                        var editor = new tinymce.Editor(fileInput.id, {}, tinymce.EditorManager);
                        uploadFile(fil,editor);
                        callback(true);
                    };
                    xhr.onerror = function() {
                        callback(false);
                    };
                    xhr.send();
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

    /**
     * jsValue
     *
     * Encode a PHP value as a JavaScript literal that is safe inside an inline <script>.
     *
     * @param  mixed $value
     * @return string
     */
    private static function jsValue($value): string
    {
        // Invalid UTF-8 (a broken translation) is substituted, so the result is never false
        return json_encode(
            $value,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    /**
     * addGdriveButton
     *
     * @param  PluginGdriveConfig $config
     * @param  int|null $rand
     * @return string
     */
    public static function addGdriveButton(PluginGdriveConfig $config, ?int $rand = null): string
    {
        $id = $rand === null ? '' : " id='gdrivebtn$rand'";
        $out = "<div class='d-flex flex-column gdrive-block'$id>";

        $out .= "<div class='d-flex'>
            <span class='mb-1'><i class='" . self::getIcon() . "'></i> " . htmlescape(self::getTypeName(2)) . "</span>
            <div class='mb-1 ms-1 gdrive-result'></div>
            </div>";
        $out .= "<div class='d-flex align-items-stretch mb-1'>";
        $out .= '<div class="g_id_signin"
                    data-type="standard"
                    data-shape="rectangular"
                    data-theme="outline"
                    data-text="signin_with"
                    data-size="large"
                    data-logo_alignment="left">
                </div>';
        $out .= "<button type='button' class='btn authbtn flex-grow-1' disabled>";
        $out .= htmlescape(__('Select file', 'gdrive')) . "</button>";
        $out .= "</div>";

        $out .= '</div>';

        return $out;
    }
}
