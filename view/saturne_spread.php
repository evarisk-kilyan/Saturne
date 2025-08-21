<?php
/* Copyright (C) 2023 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       view/saturne_attendants.php
 *  \ingroup    saturne
 *  \brief      Tab of attendants on generic element
 */

// Load Saturne environment
if (file_exists('../saturne.main.inc.php')) {
    require_once __DIR__ . '/../saturne.main.inc.php';
} elseif (file_exists('../../saturne.main.inc.php')) {
    require_once __DIR__ . '/../../saturne.main.inc.php';
} else {
    die('Include of saturne main fails');
}

// Get module parameters
$moduleName   = GETPOST('module_name', 'alpha');
$objectType   = GETPOST('object_type', 'alpha');
$documentType = GETPOST('document_type', 'alpha');

$moduleNameLowerCase = strtolower($moduleName);

// Libraries
if (isModEnabled('societe')) {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcompany.class.php';
    require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';
}

require_once __DIR__ . '/../class/saturnesignature.class.php';
require_once __DIR__ . '/../class/saturnemail.class.php';
require_once __DIR__ . '/../../' . $moduleNameLowerCase . '/class/' . $objectType . '.class.php';
require_once __DIR__ . '/../../' . $moduleNameLowerCase . '/lib/' . $moduleNameLowerCase . '_' . $objectType . '.lib.php';

require_once __DIR__ . '/../class/saturnedocuments/signinsheetdocument.class.php';

// Global variables definitions
global $conf, $db, $hookmanager, $langs, $user;

// Load translation files required by the page
saturne_load_langs();

// Get parameters
$id                 = GETPOST('id', 'int');
$ref                = GETPOST('ref', 'alpha');
$action             = GETPOST('action', 'aZ09');
$contextpage        = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : $objectType . 'signature'; // To manage different context of search
$cancel             = GETPOST('cancel', 'aZ09');
$backtopage         = GETPOST('backtopage', 'alpha');
$attendantTableMode = (GETPOSTISSET('attendant_table_mode') ? GETPOST('attendant_table_mode', 'alpha') : 'advanced');
$subaction          = GETPOST('subaction', 'alpha');

// Initialize technical objects
$className   = ucfirst($objectType);
$object      = new $className($db);
$signatory   = new SaturneSignature($db, $moduleNameLowerCase, $object->element);
$saturneMail = new SaturneMail($db, $moduleNameLowerCase, $object->element);
$usertmp     = new User($db);
$document    = new SigninSheetDocument($db);
if (isModEnabled('societe')) {
    $thirdparty = new Societe($db);
    $contact    = new Contact($db);
}

// Initialize view objects
$form        = new Form($db);
$formcompany = new FormCompany($db);

$hookmanager->initHooks([$objectType . 'signature', $object->element . 'signature', 'saturneglobal', 'globalcard']); // Note that conf->hooks_modules contains array

// Load object
include DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be included, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

// Security check - Protection if external user
$permissiontoread   = $user->rights->$moduleNameLowerCase->$objectType->read || $user->rights->$moduleNameLowerCase->assignedtome->$objectType;
$permissiontoadd    = $user->rights->$moduleNameLowerCase->$objectType->write;
$permissiontodelete = $user->rights->$moduleNameLowerCase->$objectType->delete;
saturne_check_access($permissiontoread, null, true);

/*
*  Actions
*/

$parameters = ['id' => $id];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    // Cancel
    if ($cancel && !empty($backtopage)) {
        header('Location: ' . $backtopage);
        exit;
    }

    if ($action == 'add_spread') {
        // Add logic to handle the spread addition based on the selected type
        $addType = GETPOST('add_type', 'alpha');
        // Process form data according to the selected type
    }

    // Actions set_thirdparty, set_project
    require_once __DIR__ . '/../core/tpl/actions/banner_actions.tpl.php';
}

require_once __DIR__ . '/../core/tpl/documents/documents_action.tpl.php';

/*
*	View
*/

$title   = $langs->trans('Attendants') . ' - ' . $langs->trans(ucfirst($object->element));
$helpUrl = 'FR:Module_' . $moduleName;

saturne_header(0,'', $title, $helpUrl);

if ($id > 0 || !empty($ref) && empty($action)) {
    $object->fetch_optionals();

    saturne_get_fiche_head($object, 'spread', $title);
    saturne_banner_tab($object, 'ref', '', 1, 'ref', 'ref', '', !empty($object->photo));

    print '<div class="fichecenter">';

    $backtocard = dol_buildpath('/custom/' . $moduleNameLowerCase . '/view/' . $object->element . '/' . $object->element . '_card.php?id=' . $id, 1);

    $parameters = ['backtocard' => $backtocard];
    $reshook    = $hookmanager->executeHooks('saturneAttendantsBackToCard', $parameters, $object); // Note that $action and $object may have been modified by some hooks
    if ($reshook > 0) {
        $backtocard = $hookmanager->resPrint;
    }

    print '</div>';

    // Add link to public interface
    $publicUrl = dol_buildpath('/saturne/public/spread/add_spread.php', 1) . '?id=' . $object->id . '&module_name=' . $moduleName . '&object_type=' . $objectType . '&document_type=' . (!empty($moreparam['documentType']) ? $moreparam['documentType'] : '') . '&attendant_table_mode=' . (empty($moreparam['attendantTableMode']) ? 'advanced' : $moreparam['attendantTableMode']);
    print '<div class="tabsAction">';
    print '<a class="butAction" href="' . $publicUrl . '" target="_blank">' . $langs->trans('PublicInterface') . '</a>';
    print '</div>';

    print '<div class="spread-table-container">';

    $modulePart = 'saturne:SigninSheet';
    $objref    = dol_sanitizeFileName($attendanceSheet->ref);
    $dirFiles  = $attendanceSheet->element . '/' . $objref;
    $fileDir   = $upload_dir . '/' . $dirFiles;
    // Protocole (http ou https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // Nom de domaine + port (si différent de 80/443)
    $host = $_SERVER['HTTP_HOST'];

    // URI + query string
    $requestUri = $_SERVER['REQUEST_URI'];

    // URL complète
    $urlSource = $protocol . $host . $requestUri;

    print saturne_show_documents($modulePart, $dirFiles, $fileDir, $urlSource, 1, 1, '', 1, 0, 0, 0, 0, '', '', $langs->defaultlang, 0, $object);

    print '</div>';
}

// End of page
llxFooter();
$db->close();
