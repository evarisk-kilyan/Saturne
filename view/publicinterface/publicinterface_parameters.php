<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \file    view/publicinterface/publicinterface_parameters.php
 * \ingroup saturne
 * \brief   Saturne public interface parameters view
 */

// Load Saturne environment
if (file_exists('../saturne.main.inc.php')) {
    require_once __DIR__ . '/../saturne.main.inc.php';
} elseif (file_exists('../../saturne.main.inc.php')) {
    require_once __DIR__ . '/../../saturne.main.inc.php';
} else {
    die('Include of saturne main fails');
}

// Load Dolibarr libraries.
require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

// Include Saturne libraries.
require_once __DIR__ . '/../../lib/saturne.lib.php';
require_once __DIR__ . '/../../lib/saturnepublicinterface.lib.php';
require_once __DIR__ . '/../../class/saturnepublicinterface.class.php';

// Global variables definitions.
global $conf, $db, $hookmanager, $langs, $moduleNameLowerCase, $mysoc, $user;

// Get parameters.
$id                  = GETPOST('id', 'int');
$ref                 = GETPOST('ref', 'alpha');
$action              = GETPOST('action', 'aZ09');
$confirm             = GETPOST('confirm', 'alpha');
$cancel              = GETPOST('cancel', 'aZ09');
$contextPage         = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'timesheetcard'; // To manage different context of search.
$backtopage          = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');

// Initialize technical objects.
$object       = new SaturnePublicInterface($db);

// Initialize view objects
$form = new Form($db);

$hookmanager->initHooks(['pulicinterfacecard', 'globalcard']); // Note that conf->hooks_modules contains array.

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$searchAll = GETPOST('search_all', 'alpha');
$search    = [];
foreach ($object->fields as $key => $val) {
    if (GETPOST('search_' . $key, 'alpha')) {
        $search[$key] = GETPOST('search_' . $key, 'alpha');
    }
}

if (empty($action) && empty($id) && empty($ref)) {
    $action = 'view';
}

// Load object
require_once DOL_DOCUMENT_ROOT . '/core/actions_fetchobject.inc.php'; // Must be included, not include_once.

$upload_dir = $conf->dolisirh->multidir_output[$object->entity ?? 1];

$now = dol_now();

// Security check - Protection if external user
$permissionToRead   = $user->rights->saturne->publicinterface->read;
$permissiontoadd    = $user->rights->saturne->publicinterface->write;
$permissiontodelete = $user->rights->saturne->publicinterface->delete;
saturne_check_access($permissionToRead);

/*
 * Actions.
 */

$parameters = ['id' => $id];
$resHook    = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks.
if ($resHook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($resHook)) {
    $error = 0;

    $backurlforlist = dol_buildpath('/saturne/admin/public_interface.php', 1);

    if (empty($backtopage) || ($cancel && empty($id))) {
        if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
            if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) {
                $backtopage = $backurlforlist;
            } else {
                $backtopage = dol_buildpath('/saturne/view/publicinterface/publicinterface_card.php', 1) . '?id=' . ((!empty($id) && $id > 0) ? $id : '__ID__');
            }
        }
    }
}

/*
 * View.
 */

$title    = $langs->trans(ucfirst($object->element));
$help_url = 'FR:Module_Saturne';

saturne_header(0, '', $title, $help_url);

saturne_get_fiche_head($object, 'parameters', $title);
saturne_banner_tab($object);

// End of page.
llxFooter();
$db->close();
