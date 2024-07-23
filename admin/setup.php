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

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

// Load GMAO libraries
require_once __DIR__ . '/../lib/gmao.lib.php';

// Global variables definitions
global $conf, $db, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$action     = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// Security check - Protection if external user
$permissionToRead = $user->rights->gmao->adminpage->read;
saturne_check_access($permissionToRead);

/*
 * Actions
 */

if ($action == 'update') {
    $proposalServiceID = GETPOST('proposalService', 'int');
    dolibarr_set_const($db, 'GMAO_PROPOSAL_SERVICE_ID', $proposalServiceID, 'integer', 0, '', $conf->entity);

    setEventMessage('SavedConfig');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

if ($action == 'generate_ticket_categories' && isModEnabled('categorie')) {
    $tagParentID = saturne_create_category($langs->transnoentities('GMAO'), 'ticket', 0, 'pictogram_GMAO_64px.png');

    saturne_create_category($langs->transnoentities('Preventive'), 'ticket', $tagParentID, 'pictogram_Preventive_64px.png');
    saturne_create_category($langs->transnoentities('Curative'), 'ticket', $tagParentID, 'pictogram_Curative_64px.png');
    saturne_create_category($langs->transnoentities('Improvement'), 'ticket', $tagParentID, 'pictogram_Improvement_64px.png');

    dolibarr_set_const($db, 'GMAO_TICKET_MAIN_CATEGORY', $tagParentID, 'integer', 0, '', $conf->entity);
    dolibarr_set_const($db, 'GMAO_TICKET_CATEGORIES_SET', 1, 'integer', 0, '', $conf->entity);

    setEventMessage('SavedConfig');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

/*
 * View
 */

$title    = $langs->trans('ModuleSetup', 'GMAO');
$help_url = 'FR:Module_GMAO';

saturne_header(0,'', $title, $help_url);

// Subheader
$linkBack = '<a href="' . ($backtopage ?: DOL_URL_ROOT . '/admin/modules.php?restore_lastsearch_values=1') . '">' . $langs->trans('BackToModuleList') . '</a>';
print load_fiche_titre($title, $linkBack, 'title_setup');

// Configuration header
$head = gmao_admin_prepare_head();
print dol_get_fiche_head($head, 'settings', $title, -1, 'gmao_color@gmao');

// Proposal service
print load_fiche_titre($langs->transnoentities('Service'), '', 'service');

print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '" name="proposal_service_form">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="update">';
print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->transnoentities('Name') . '</td>';
print '<td>' . $langs->transnoentities('Service') . '</td>';
print '</tr>';

// Proposal service
print '<tr class="oddeven"><td><label for="proposalService">' . $langs->transnoentities('ProposalService') . '</label></td><td>';
print img_picto('', 'service', 'class="pictofixedwidth"');
$form->select_produits((GETPOSTISSET('proposalService') ? GETPOST('proposalService', 'int') : getDolGlobalInt('GMAO_PROPOSAL_SERVICE_ID')), 'proposalService', 1, 0, 0, -1, 2, '', 0, [], 0, '1', 0, 'maxwidth500 widthcentpercentminusxx');
print '<a href="' . DOL_URL_ROOT . '/product/card.php?action=create&type=1&backtopage=' . urlencode(DOL_URL_ROOT . '/custom/gmao/admin/setup.php?proposalService=&#95;&#95;ID&#95;&#95;') . '"> <span class="fa fa-plus-circle valignmiddle" title="' . $langs->transnoentities('AddService') . '"></span></a>';
print '</td></tr>';

print '</table>';
print '<div class="tabsAction"><input type="submit" class="butAction" name="save" value="' . $langs->trans('Save') . '"></div>';
print '</form>';

$constArray['gmao'] = [
    'EnableTicketProposal' => [
        'name'        => 'EnableTicketProposal',
        'description' => 'EnableTicketProposalDescription',
        'code'        => 'GMAO_ENABLE_TICKET_PROPOSAL',
    ],
    'EnableTicketProposalGMAO' => [
        'name'        => 'EnableTicketProposalGMAO',
        'description' => 'EnableTicketProposalGMAODescription',
        'code'        => 'GMAO_ENABLE_TICKET_PROPOSAL_GMAO',
    ]
];
require __DIR__ . '/../../saturne/core/tpl/admin/object/object_const_view.tpl.php';

// Generate categories
print load_fiche_titre($langs->trans('ConfigGMAOTicketCategories'), '', '');

print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="action" value="generate_ticket_categories">';

print '<table class="noborder centpercent">';
print '<tr class="liste_titre">';
print '<td>' . $langs->trans('Name') . '</td>';
print '<td class="center">' . $langs->trans('Status') . '</td>';
print '<td class="center">' . $langs->trans('Action') . '</td>';
print '<td class="center">' . $langs->trans('ShortInfo') . '</td>';
print '</tr>';

if (!isModEnabled('categorie')) {
    print '<tr><td>' . $langs->trans('GenerateGMAOTicketCategories') . '</td>';
    print '<td class="center">';
    print getDolGlobalInt('GMAO_TICKET_CATEGORIES_SET') ? $langs->trans('AlreadyGenerated') : $langs->trans('NotCreated');
    print '</td>';
    print '<td class="center">';
    print getDolGlobalInt('GMAO_TICKET_CATEGORIES_SET') ? '<a class="butActionRefused">' . $langs->trans('Create') . '</a>' : '<input type="submit" class="button" value="' . $langs->trans('Create') . '">';
    print '</td>';
    print '<td class="center">';
    print $form->textwithpicto('', $langs->trans('GenerateGMAOTicketCategoriesDescription'));
    print '</td></tr>';
} else {
    print '<tr><td colspan="4">' . $langs->trans('EnableCategory') . '</td></tr>';
}

print '</table>';
print '</form>';

// Page end
print dol_get_fiche_end();
$db->close();
llxFooter();
