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

use Glpi\Plugin\Hooks;

define('PLUGIN_GDRIVE_VERSION', '3.0.0');
define("PLUGIN_GDRIVE_MIN_GLPI_VERSION", "11.0");
define("PLUGIN_GDRIVE_MAX_GLPI_VERSION", "12.0");

/**
 * plugin_version_gdrive
 *
 * @return array
 */
function plugin_version_gdrive(): array
{
    return [
        'name'          => 'GDrive',
        'version'       => PLUGIN_GDRIVE_VERSION,
        'author'        => '<a href="https://tic.gal">TICGAL</a>',
        'homepage'      => 'https://tic.gal/en/project/gdrive-integration-glpi-google-drive/',
        'license'       => 'GPLv3+',
        'requirements'  => [
            'glpi' => [
                'min' => PLUGIN_GDRIVE_MIN_GLPI_VERSION,
                'max' => PLUGIN_GDRIVE_MAX_GLPI_VERSION,
            ]
        ]
    ];
}

/**
 * plugin_init_gdrive
 *
 * @return void
 */
function plugin_init_gdrive(): void
{
    /** @var array $PLUGIN_HOOKS */
    global $PLUGIN_HOOKS;

    if (Session::haveRightsOr('config', [READ, UPDATE])) {
        Plugin::registerClass(PluginGdriveConfig::class, ['addtabon' => 'Config']);
        $PLUGIN_HOOKS['config_page']['gdrive'] = 'front/config.form.php';
    }

    $PLUGIN_HOOKS[Hooks::POST_ITEM_FORM]['gdrive'] = [PluginGdriveTicket::class, 'postForm'];

    $PLUGIN_HOOKS[Hooks::POST_SHOW_TAB]['gdrive'] = [PluginGdriveTicket::class, 'postTab'];
}
