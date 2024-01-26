<?php
/* Copyright (C) 2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    admin/setup.php
 * \ingroup gmao
 * \brief   GMAO setup page
 */

// Load GMAO environment
if (file_exists('../gmao.main.inc.php')) {
    require_once __DIR__ . '/../gmao.main.inc.php';
} elseif (file_exists('../../gmao.main.inc.php')) {
    require_once __DIR__ . '/../../gmao.main.inc.php';
} else {
    die('Include of gmao main fails');
}

// Load GMAO libraries
require_once __DIR__ . '/../lib/gmao.lib.php';

// Global variables definitions
global $conf, $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action     = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize view objects
$form = new Form($db);

// Security check - Protection if external user
$permissionToRead = $user->rights->gmao->adminpage->read;
saturne_check_access($permissionToRead);

/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'GMAO');
$help_url = 'FR:Module_GMAO';

saturne_header(0,'', $title, $help_url);

// Subheader
$linkback = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkback, 'title_setup');

// Configuration header
$head = gmao_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'gmao_color@gmao');

$db->close();
llxFooter();
