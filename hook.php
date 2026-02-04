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

/**
 * plugin_gdrive_install
 *
 * @return void
 */
function plugin_gdrive_install()
{
    $migration = new Migration(PLUGIN_GDRIVE_VERSION);

    // Parse inc directory
    foreach (glob(dirname(__FILE__) . '/inc/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
            $classname = 'PluginGdrive' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'install')) {
                $classname::install($migration);
            }
        }
    }

    return true;
}

/**
 * plugin_gdrive_uninstall
 *
 * @return bool
 */
function plugin_gdrive_uninstall(): bool
{
    // Parse inc directory
    foreach (glob(dirname(__FILE__) . '/inc/*') as $filepath) {
        // Load *.class.php files and get the class name
        if (preg_match("/inc.(.+)\.class.php/", $filepath, $matches)) {
            $classname = 'PluginGdrive' . ucfirst($matches[1]);
            include_once($filepath);
            // If the install method exists, load it
            if (method_exists($classname, 'uninstall')) {
                $classname::uninstall();
            }
        }
    }

    return true;
}
