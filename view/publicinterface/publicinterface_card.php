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
 * \file    view/publicinterface/publicinterface_card.php
 * \ingroup saturne
 * \brief   Saturne public interface card view
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

    if (($action == 'add' || $action == 'update') && $permissiontoadd && !$cancel) {

    }

    // Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen.
    $triggermodname = 'PUBLICINTERFACE_MODIFY'; // Name of trigger action code to execute when we modify record.
    require_once DOL_DOCUMENT_ROOT . '/core/actions_addupdatedelete.inc.php';

    // Actions builddoc, forcebuilddoc, remove_file.
    require_once __DIR__ . '/../../../saturne/core/tpl/documents/documents_action.tpl.php';

    // Action confirm_archive.
    require_once __DIR__ . '/../../../saturne/core/tpl/actions/object_workflow_actions.tpl.php';
}

/*
 * View.
 */

$title    = $langs->trans(ucfirst($object->element));
$help_url = 'FR:Module_Saturne';

saturne_header(0, '', $title, $help_url);

// Part to create;
if ($action == 'create') {
    if (empty($permissiontoadd)) {
        accessforbidden($langs->trans('NotEnoughPermissions'), 0);
        exit;
    }

    print load_fiche_titre($langs->trans('New' . ucfirst($object->element)), '', 'object_' . $object->picto);

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="add">';
    if ($backtopage) {
        print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    }
    if ($backtopageforcancel) {
        print '<input type="hidden" name="backtopageforcancel" value="'. $backtopageforcancel . '">';
    }

    print dol_get_fiche_head();

    print '<table class="border centpercent tableforfieldcreate">';

    // Common attributes.
    require_once DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

    // Other attributes.
    include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

    print '</table>';

    print dol_get_fiche_end();

    print $form->buttonsSaveCancel('Create');

    print '</form>';
}

// Part to edit record.
if (($id || $ref) && $action == 'edit') {
    print load_fiche_titre($langs->trans('Modify' . ucfirst($object->element)), '', 'object_' . $object->picto);

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="update">';
    print '<input type="hidden" name="id" value="' . $object->id . '">';
    if ($backtopage) {
        print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
    }
    if ($backtopageforcancel) {
        print '<input type="hidden" name="backtopageforcancel" value="' . $backtopageforcancel . '">';
    }

    print dol_get_fiche_head();

    print '<table class="border centpercent tableforfieldedit">';

    // Common attributes.
    require_once DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

    // Other attributes.
    require_once DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

    print '</table>';

    print dol_get_fiche_end();

    print $form->buttonsSaveCancel();

    print '</form>';
}

// Part to show record.
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create'))) {
    $object->fetch_optionals();

    saturne_get_fiche_head($object, 'card', $title);
    saturne_banner_tab($object);

    $formConfirm = '';

    // Archive confirmation
    if (($action == 'set_archive' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile))) || (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))) {
        $formConfirm .= $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id . '&forcebuilddoc=true', $langs->trans('ArchiveObject', $langs->transnoentities('The' . ucfirst($object->element))), $langs->trans('ConfirmArchiveObject', $langs->transnoentities('The' . ucfirst($object->element))), 'confirm_archive', '', 'yes', 'actionButtonArchive', 350, 600);
    }
    // Delete confirmation
    if ($action == 'delete') {
        $formConfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('DeleteObject', $langs->transnoentities('The' . ucfirst($object->element))), $langs->trans('ConfirmDeleteObject', $langs->transnoentities('The' . ucfirst($object->element))), 'confirm_delete', '', 'yes', 1);
    }

    // Call Hook formConfirm.
    $parameters = ['formConfirm' => $formConfirm, 'lineid' => $lineid];
    $reshook    = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook.
    if (empty($reshook)) {
        $formConfirm .= $hookmanager->resPrint;
    } elseif ($reshook > 0) {
        $formConfirm = $hookmanager->resPrint;
    }

    // Print form confirm.
    print $formConfirm;

    if ($conf->browser->layout == 'phone') {
        $onPhone = 1;
    } else {
        $onPhone = 0;
    }

    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
    print '<table class="border centpercent tableforfield">';

    require_once DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

    // Other attributes. Fields from hook formObjectOptions and Extrafields.
    require_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

    print '</table>';
    print '</div>';
    print '</div>';

    require_once DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page.
llxFooter();
$db->close();
