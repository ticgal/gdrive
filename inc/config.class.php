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
    die("Sorry. You can't access this file directly");
}

use Glpi\Application\View\TemplateRenderer;

class PluginGdriveConfig extends CommonDBTM
{
    public static $rightname = 'config';

    private static $instance = null;

    public static function getIcon()
    {
        return "ti ti-brand-google-drive";
    }

    /**
     * getTypeName
     *
     * @param  mixed $nb
     * @return string
     */
    public static function getTypeName($nb = 0): string
    {
        return __('GDrive', 'gdrive');
    }

    /**
     * getName
     *
     * @param  mixed $with_comment
     * @return string
     */
    public function getName($with_comment = 0): string
    {
        return 'Gdrive';
    }

    /**
     * getInstance
     *
     * @param  mixed $n
     * @return PluginGdriveConfig
     */
    public static function getInstance($n = 1): PluginGdriveConfig
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
            if (!self::$instance->getFromDB($n)) {
                self::$instance->getEmpty();
            }
        }
        return self::$instance;
    }

    /**
     * getConfig
     *
     * @return PluginGdriveConfig
     */
    public static function getConfig(): PluginGdriveConfig
    {
        $config = new self();
        $config->getFromDB(1);

        return $config;
    }

    /**
     * showConfigForm
     *
     * @param  mixed $item
     * @return bool
     */
    public static function showConfigForm($item): bool
    {
        $config = self::getInstance();
        $options = [
            'full_width' => true
        ];

        $templatePath = "@gdrive/config.html.twig";
        TemplateRenderer::getInstance()->display($templatePath, [
            'item'      => $config,
            'options'   => $options,
        ]);

        return false;
    }

    /**
     * getTabNameForItem
     *
     * @param  mixed $item
     * @param  mixed $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if ($item->getType() == 'Config') {
            return self::createTabEntry(self::getTypeName());
        }

        return '';
    }

    /**
     * displayTabContentForItem
     *
     * @param  mixed $item
     * @param  mixed $tabnum
     * @param  mixed $withtemplate
     * @return bool
     */
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0): bool
    {
        if ($item->getType() == 'Config') {
            self::showConfigForm($item);
        }

        return true;
    }

    /**
     * install
     *
     * @param  mixed $migration
     * @return void
     */
    public static function install(Migration $migration): void
    {
        /** @var \DBMysql $DB */
        global $DB;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = self::getTable();
        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` INT {$default_key_sign} NOT NULL AUTO_INCREMENT,
                `developer_key` VARCHAR(250) NOT NULL DEFAULT 'xxxxxxxYYYYYYYY-12345678',
                `client_id` VARCHAR(250) NOT NULL DEFAULT '1234567890-abcdef.apps.googleusercontent.com',
                `app_id` VARCHAR(50) NOT NULL DEFAULT '1234567890',
                PRIMARY KEY (`id`)
			)ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            
            $DB->doQuery($query);

            // Default config
            $DB->insert($table, ['id' => 1]);
        }
    }
}
