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
function plugin_version_gdrive() {
	return array('name'       => 'GDrive',
		'version'        => '1.0.0',
		'author'         => '<a href="https://tic.gal">TICgal</a>',
		'homepage'       => 'https://tic.gal',
		'license'        => 'GPLv3+',
		'minGlpiVersion' => "9.2",
		'requirements'   => [
			'glpi'   => [
				'min' => '9.2'
			]
		]);
}

/**
 * Check plugin's prerequisites before installation
 */
function plugin_gdrive_check_prerequisites() {
	if (version_compare(GLPI_VERSION,'9.2','lt') || version_compare(GLPI_VERSION,'9.3','gt')) {
		echo __('This plugin requires GLPI >= 9.2 and GLPI < 9.3', 'gdrive');
		return false;
	} else {
		return true;
	}
}

/**
 * Check plugin's config before activation
 */
function plugin_gdrive_check_config($verbose=false) {
	return true;
}

function plugin_init_gdrive() {
	global $PLUGIN_HOOKS;

	if (Session::haveRightsOr("config", [READ, UPDATE])) {
		Plugin::registerClass('PluginGdriveConfig', array('addtabon' => 'Config'));
		$PLUGIN_HOOKS['config_page']['gdrive'] = 'front/config.form.php';
	}
	$PLUGIN_HOOKS['csrf_compliant']['gdrive'] = true;

	//Plugin::registerClass('PluginGdriveTicket',array('addtabon' => array('Ticket')));
	$PLUGIN_HOOKS['post_item_form']['gdrive'] = ['PluginGdriveTicket', 'postForm'];
	
}