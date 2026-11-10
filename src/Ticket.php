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

namespace GlpiPlugin\Gdrive;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

final class Ticket
{
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
        return "ti ti-brand-google-drive";
    }

    /**
     * postForm
     *
     * @param  mixed $params
     * @return void
     */
    public static function postForm($params): void
    {
        $item = $params['item'];
        $config = Config::getConfig();

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
        $item = $params['item'];
        $itemtype = $params['options']['itemtype'] ?? '';
        $config = Config::getConfig();

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
                    // Unique id per render: several buttons can share a page
                    $btn_id = 'gdrivebtn-' . bin2hex(random_bytes(4));
                    echo self::addGdriveScripts($config);
                    echo self::addGdriveButton($config, $btn_id);

                    $out = '<script>
                        $(".firstbloc").append($("#' . $btn_id . '"));
                        </script>';

                    echo $out;
                }
                break;
        }
    }

    /**
     * jsString: literal JS seguro (entre comillas) para incrustar en un <script> inline
     *
     * @param  mixed $value
     * @return string
     */
    private static function jsString($value): string
    {
        return json_encode((string) $value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    }

    /**
     * addGdriveScripts
     *
     * @param  Config $config
     * @return string
     */
    public static function addGdriveScripts(Config $config): string
    {
        $out = "<script type='text/javascript'>
                // The Browser API key obtained from the Google API Console.
                var developerKey = " . self::jsString($config->fields['developer_key']) . ";

                // The Client ID obtained from the Google API Console.
                var clientId = " . self::jsString($config->fields['client_id']) . ";

                // Project number from console.developers.google.com ('IAM & Admin' > 'Settings').
                var appId = " . self::jsString($config->fields['app_id']) . ";

                var gdriveMessages = {
                    uploaded: " . self::jsString(__('Uploaded file', 'gdrive')) . ",
                    error: " . self::jsString(__('Error loading the file', 'gdrive')) . ",
                    notReady: " . self::jsString(__('Google services could not be loaded', 'gdrive')) . "
                };
                </script>";

        $out .= <<<'JS'
            <script type='text/javascript'>
                // Scope: See and download Google Drive files
                var scope = 'https://www.googleapis.com/auth/drive.readonly';

                var tokenClient;
                var access_token;
                var pickerInited = false;
                var idEditor = 0;
                var currentPicker = null;

                // Google native files cannot be downloaded: they are exported to a regular format
                var gdriveExportTypes = {
                    'application/vnd.google-apps.document': {mime: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', ext: 'docx'},
                    'application/vnd.google-apps.spreadsheet': {mime: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', ext: 'xlsx'},
                    'application/vnd.google-apps.presentation': {mime: 'application/vnd.openxmlformats-officedocument.presentationml.presentation', ext: 'pptx'},
                    'application/vnd.google-apps.drawing': {mime: 'image/png', ext: 'png'}
                };

                // Show a status message next to the button that started the upload
                function setResult(text) {
                    var target = currentPicker ? currentPicker.querySelector('.gdrive-result') : null;
                    if (target) {
                        target.textContent = text;
                    }
                }

                function gdriveLoadScript(src, isLoaded, onLoad) {
                    if (isLoaded()) {
                        onLoad();
                        return;
                    }
                    var script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    script.onload = onLoad;
                    document.head.appendChild(script);
                }

                // Google Identity Services: OAuth token client
                function initClient() {
                    if (tokenClient || typeof google === 'undefined' || !google.accounts) {
                        return;
                    }
                    tokenClient = google.accounts.oauth2.initTokenClient({
                        client_id: clientId,
                        scope: scope,
                        prompt: '',
                        callback: (tokenResponse) => {
                            handleAuthResult(tokenResponse);
                        },
                    });
                }

                // Google API loader: Picker
                function initPicker() {
                    if (pickerInited || typeof gapi === 'undefined') {
                        return;
                    }
                    gapi.load('picker', {'callback': function () {
                        pickerInited = true;
                    }});
                }

                function handleAuthResult(authResult) {
                    if (authResult && !authResult.error) {
                        // The token only lives in page memory: it is never persisted, so
                        // a reload or a new login always starts without one
                        access_token = authResult.access_token;
                        createPicker();
                    }else{
                        // Popup closed by the user: abort, never reuse a previous token
                        access_token = '';
                        if(authResult.error!='popup_closed_by_user'){
                            alert(authResult.error);
                        }
                    }
                }

                function revokeToken() {
                    google.accounts.oauth2.revoke(access_token, () => {console.log('access token revoked')});
                }

                // The file upload input of the form that contains the button
                function gdriveFindUploadInput(btn) {
                    var node = btn.parentElement;
                    while (node) {
                        var input = node.querySelector('input[id*="fileupload"]');
                        if (input) {
                            return input;
                        }
                        node = node.parentElement;
                    }
                    return null;
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
                        var failed = false;
                        for(var i=0;i<data[google.picker.Response.DOCUMENTS].length;i++){
                            var file=data[google.picker.Response.DOCUMENTS][i];
                            downloadFile(file,fileInput,function(res){
                                failed = failed || !res;
                                setResult(failed ? gdriveMessages.error : gdriveMessages.uploaded);
                            });
                        }
                    }
                }

                function downloadFile(file, fileInput, callback){
                    var fileId = file[google.picker.Document.ID];
                    var name = file[google.picker.Document.NAME];
                    var mimeType = file[google.picker.Document.MIME_TYPE] || '';
                    if (!fileId) {
                        callback(false);
                        return;
                    }

                    var url = 'https://www.googleapis.com/drive/v3/files/' + encodeURIComponent(fileId);
                    if (mimeType.indexOf('application/vnd.google-apps.') === 0) {
                        // Docs, Sheets, Slides...: export to a regular format
                        var target = gdriveExportTypes[mimeType];
                        if (!target) {
                            callback(false);
                            return;
                        }
                        url += '/export?mimeType=' + encodeURIComponent(target.mime);
                        mimeType = target.mime;
                        if (name.toLowerCase().slice(-(target.ext.length + 1)) !== '.' + target.ext) {
                            name += '.' + target.ext;
                        }
                    } else {
                        url += '?alt=media&supportsAllDrives=true';
                    }

                    var xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob';
                    xhr.open('GET', url);
                    xhr.setRequestHeader('Authorization', 'Bearer ' + access_token);
                    xhr.onload = function() {
                        if (xhr.status < 200 || xhr.status >= 300) {
                            callback(false);
                            return;
                        }
                        var fil = new File([xhr.response], name, {type: mimeType, lastModified: Date.now()});
                        var editor = new tinymce.Editor(fileInput.id, {}, tinymce.EditorManager);
                        uploadFile(fil, editor);
                        callback(true);
                    };
                    xhr.onerror = function() {
                        callback(false);
                    };
                    xhr.send();
                }

                // One delegated listener for every "Select file" button of the page
                if (!window.gdriveBound) {
                    window.gdriveBound = true;
                    document.addEventListener('click', function (e) {
                        var btn = e.target.closest ? e.target.closest('.gdrive-picker .authbtn') : null;
                        if (!btn) {
                            return;
                        }
                        currentPicker = btn.closest('.gdrive-picker');
                        setResult('');

                        var input = gdriveFindUploadInput(btn);
                        initClient();
                        initPicker();
                        if (!input || !tokenClient || !pickerInited) {
                            setResult(gdriveMessages.notReady);
                            return;
                        }
                        idEditor = input.id;
                        tokenClient.requestAccessToken();
                    });
                }

                gdriveLoadScript('https://accounts.google.com/gsi/client', function () {
                    return typeof google !== 'undefined' && !!google.accounts;
                }, initClient);
                gdriveLoadScript('https://apis.google.com/js/api.js', function () {
                    return typeof gapi !== 'undefined';
                }, initPicker);
            </script>
            JS;

        return $out;
    }

    /**
     * addGdriveButton
     *
     * @param  Config $config
     * @param  string $id Unique DOM id of the button container (generated when empty)
     * @return string
     */
    public static function addGdriveButton(Config $config, string $id = ''): string
    {
        if ($id === '') {
            $id = 'gdrivebtn-' . bin2hex(random_bytes(4));
        }
        $out = "<div class='gdrive-picker d-flex flex-wrap align-items-center gap-2 mb-2' id='" . htmlescape($id) . "'>";

        $out .= "<span class='d-inline-flex align-items-center text-muted'>
            <i class='" . self::getIcon() . " me-1'></i>" . self::getTypeName(2) . "</span>";
        $out .= "<button type='button' class='btn btn-outline-secondary authbtn'>";
        $out .= "<i class='ti ti-folder-open me-1'></i>" . __('Select file', 'gdrive') . "</button>";
        $out .= "<span class='gdrive-result text-muted small' role='status' aria-live='polite'></span>";

        $out .= '</div>';

        return $out;
    }
}
