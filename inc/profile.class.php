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

class PluginGdriveProfile extends Profile
{
    public static $rightname = 'profile';

    public const RIGHTNAME = 'plugin_gdrive_use';

    /**
     * getTabNameForItem
     *
     * Self-service profiles get no tab: the GDrive button is a central-only feature.
     *
     * @param  mixed $item
     * @param  mixed $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0): string
    {
        if (
            $item instanceof Profile
            && ($item->fields['interface'] ?? '') === 'central'
            && Session::haveRight('profile', READ)
        ) {
            return self::createTabEntry(PluginGdriveConfig::getTypeName());
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
        if ($item instanceof Profile && ($item->fields['interface'] ?? '') === 'central') {
            $profile = new self();
            return $profile->showProfileForm($item);
        }

        return false;
    }

    /**
     * showProfileForm
     *
     * @param  Profile $profile
     * @return bool
     */
    public function showProfileForm(Profile $profile): bool
    {
        if (!Session::haveRight('profile', READ)) {
            return false;
        }

        $canedit = Session::haveRight('profile', UPDATE);

        echo "<div class='spaced'>";
        if ($canedit) {
            echo "<form method='post' action='" . htmlescape($profile::getFormURL()) . "'>";
        }

        $profile->displayRightsChoiceMatrix(self::getAllRights(), [
            'canedit' => $canedit,
            'title'   => PluginGdriveConfig::getTypeName(),
        ]);

        if ($canedit) {
            echo "<div class='text-center'>";
            echo Html::hidden('id', ['value' => $profile->getID()]);
            echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
            echo "</div>";
            Html::closeForm();
        }
        echo '</div>';

        return true;
    }

    /**
     * getAllRights
     *
     * @param  bool $all
     * @return array
     */
    public static function getAllRights($all = false): array
    {
        return [
            [
                'rights'   => [READ => __('Use', 'gdrive')],
                'itemtype' => PluginGdriveTicket::class,
                'label'    => __('Attach files from Google Drive', 'gdrive'),
                'field'    => self::RIGHTNAME,
            ],
        ];
    }

    /**
     * install
     *
     * Central profiles keep the button they had before the right existed; self-service
     * profiles are left at 0.
     *
     * @param  mixed $migration
     * @return void
     */
    public static function install(Migration $migration): void
    {
        /** @var \DBmysql $DB */
        global $DB;

        if (countElementsInTable(ProfileRight::getTable(), ['name' => self::RIGHTNAME]) > 0) {
            return;
        }

        $migration->displayMessage('Adding right ' . self::RIGHTNAME);
        ProfileRight::addProfileRights([self::RIGHTNAME]);

        $iterator = $DB->request([
            'SELECT' => ['id'],
            'FROM'   => Profile::getTable(),
            'WHERE'  => ['interface' => 'central'],
        ]);
        foreach ($iterator as $row) {
            ProfileRight::updateProfileRights($row['id'], [self::RIGHTNAME => READ]);
        }
    }

    /**
     * uninstall
     *
     * @return void
     */
    public static function uninstall(): void
    {
        ProfileRight::deleteProfileRights([self::RIGHTNAME]);
    }
}
