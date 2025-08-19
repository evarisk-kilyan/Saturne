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

    // Add JavaScript for dynamic field visibility
    print '<script type="text/javascript">
        jQuery(document).ready(function() {
            function toggleFieldsVisibility() {
                var selectedType = jQuery("select[name=\'add_type\']").val();
                
                // Hide all field containers first
                jQuery(".thirdparty-field").hide();
                jQuery(".user-field").hide();
                jQuery(".contact-field").hide();
                jQuery(".free-field").hide();
                jQuery(".thirdparty-contact-field").hide();
                
                // Show the selected type fields based on the selected type
                if (selectedType === "thirdparty") {
                    jQuery(".thirdparty-field").show();
                    jQuery(".thirdparty-contact-field").show();
                } else {
                    jQuery("." + selectedType + "-field").show();
                }
            }
            
            // Initial state setup
            toggleFieldsVisibility();
            
            // Add change event handler
            jQuery("select[name=\'add_type\']").change(function() {
                toggleFieldsVisibility();
            });
        });
    </script>';

    print '<div class="fichecenter">';

    $backtocard = dol_buildpath('/custom/' . $moduleNameLowerCase . '/view/' . $object->element . '/' . $object->element . '_card.php?id=' . $id, 1);

    $parameters = ['backtocard' => $backtocard];
    $reshook    = $hookmanager->executeHooks('saturneAttendantsBackToCard', $parameters, $object); // Note that $action and $object may have been modified by some hooks
    if ($reshook > 0) {
        $backtocard = $hookmanager->resPrint;
    }

    print '</div>';

    print '<div class="spread-table-container">';

    print load_fiche_titre($langs->trans('Recipients'), '', '');

    print '<table class="border centpercent tableforfield">';

    print '<tr class="liste_titre">';
    print '<td>' . $langs->trans('AddType') . '</td>'; // Type column
    print '<td colspan="2">' . $langs->trans('Recipient') . '</td>'; // Merged recipient column
    if ($attendantTableMode == 'simple') {
        print '<td class="center ' . ($conf->browser->layout != 'classic' && $object->status > $object::STATUS_DRAFT ? 'hidden': '') . '">' . $langs->trans('Role') . '</td>';
    }
    print '<td class="center">' . $langs->trans('SignatureLink') . '</td>';
    print '<td class="center">' . $langs->trans('SendMailDate') . '</td>';
    print '<td class="' . ($conf->browser->layout != 'classic' ? 'hidden': '') . '">' . $langs->trans('SignatureDate') . '</td>';
    print '<td class="center">' . $langs->trans('Attendance') . '</td>';
    print '<td class="center">' . $langs->trans('SignatureActions') . '</td>';
    print '</tr>';

    print '<form method="POST" action="' . $_SERVER['PHP_SELF'] . '?id=' . $id . '&module_name=' . $moduleName . '&object_type=' . $object->element . '&document_type=' . $documentType . '&attendant_table_mode=' . $attendantTableMode . '">';
    print '<input type="hidden" name="token" value="' . newToken() . '">';
    print '<input type="hidden" name="action" value="add_spread">';
    
    print '<tr class="oddeven">';
    print '<td>';
    // Dropdown for selection of addition type
    $addTypeOptions = [
        'thirdparty' => $langs->trans('ThirdParty'),
        'user' => $langs->trans('User'),
        'contact' => $langs->trans('Contact'),
        'free' => $langs->trans('Free')
    ];
    print $form->selectarray('add_type', $addTypeOptions, GETPOST('add_type', 'alpha') ?: 'thirdparty', 0, 0, 0, '', 0, 0, 0, '', 'minwidth100 maxwidth150');
    print '</td>';
    
    // Merged recipient column for all types
    print '<td colspan="2">';
    
    // THIRDPARTY fields (company + associated contacts)
    print '<div class="thirdparty-field">';
    print img_picto('', 'company', 'class="pictofixedwidth"');
    $selectedCompany = GETPOSTISSET('newcompany' . (($attendantTableMode == 'advanced') ? $signatoryRole : '')) ? GETPOST('newcompany' . (($attendantTableMode == 'advanced') ? $signatoryRole : ''), 'int') : (empty($object->socid) ? 0 : $object->socid);
    $moreparam       = '&module_name=' . urlencode($moduleName) . '&object_type=' . urlencode($object->element) . '&document_type=' . $documentType . '&attendant_table_mode=' . urlencode($attendantTableMode);
    $moreparam       .= '&backtopage=' . urlencode($_SERVER['PHP_SELF'] . '?id=' . $object->id . $moreparam);
    // Add placeholder attribute via JavaScript since selectCompaniesForNewContact doesn't support it directly
    print '<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(".thirdparty-field select").attr("data-placeholder", "' . $langs->trans('ThirdParty') . '");
        });
    </script>';
    $formcompany->selectCompaniesForNewContact($object, 'id', $selectedCompany, 'newcompany' . (($attendantTableMode == 'advanced') ? $signatoryRole : ''), '', 0, $moreparam, 'minwidth300imp');
    print '</div>';
    
    // Contact field specifically for thirdparty type
    print '<div class="thirdparty-contact-field marginleftonly">';
    print img_object('', 'contact', 'class="pictofixedwidth"');
    // Add placeholder attribute via JavaScript
    print '<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(".thirdparty-contact-field select").attr("data-placeholder", "' . $langs->trans('Contact') . '");
        });
    </script>';
    print $form->selectcontacts(($selectedCompany > 0 ? $selectedCompany : -1), GETPOST('contactID'), 'attendant_thirdparty_contact', 1, $alreadyAddedSignatories['socpeople'] ?? [], '', 1, 'minwidth200 widthcentpercentminusx maxwidth300');
    if (!empty($selectedCompany) && $selectedCompany > 0 && $user->rights->societe->creer) {
        $newcardbutton = '<a href="' . DOL_URL_ROOT . '/contact/card.php?socid=' . $selectedCompany . '&action=create' . $moreparam . urlencode('&newcompany' . (($attendantTableMode == 'advanced') ? $signatoryRole : '') . '=' . GETPOST('newcompany' . (($attendantTableMode == 'advanced') ? $signatoryRole : '')) . '&contactID=&#95;&#95;ID&#95;&#95;') . '" title="' . $langs->trans('NewContact') . '"><span class="fa fa-plus-circle valignmiddle paddingleft"></span></a>';
        print $newcardbutton;
    }
    print '</div>';
    
    // USER field
    print '<div class="user-field">';
    print img_picto('', 'user', 'class="pictofixedwidth"');
    // Add placeholder attribute via JavaScript
    print '<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(".user-field select").attr("data-placeholder", "' . $langs->trans('User') . '");
        });
    </script>';
    print $form->select_dolusers('', 'attendant_user', 1, $alreadyAddedSignatories['user'] ?? [], 0, '', '', $conf->entity, 0, 0, '', 0, '', 'minwidth300 widthcentpercentminusx');
    print '</div>';
    
    // CONTACT field (standalone contact selection)
    print '<div class="contact-field">';
    print img_object('', 'contact', 'class="pictofixedwidth"');
    // Add placeholder attribute via JavaScript
    print '<script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery(".contact-field select").attr("data-placeholder", "' . $langs->trans('Contact') . '");
        });
    </script>';
    print $form->selectcontacts(-1, GETPOST('contactID'), 'attendant_contact', 1, $alreadyAddedSignatories['socpeople'] ?? [], '', 1, 'minwidth300 widthcentpercentminusx');
    print '</div>';
    
    // FREE input fields
    print '<div class="free-field">';
    print img_picto('', 'object_generic', 'class="pictofixedwidth"');
    print '<input type="text" name="free_attendant_name" class="flat minwidth300" placeholder="' . $langs->trans('Name') . '" value="' . GETPOST('free_attendant_name', 'alpha') . '"><br>';
    print '<div class="marginleftonly paddingtop">';
    print img_picto('', 'email', 'class="pictofixedwidth"');
    print '<input type="email" name="free_attendant_email" class="flat minwidth300" placeholder="' . $langs->trans('Email') . '" value="' . GETPOST('free_attendant_email', 'alpha') . '">';
    print '</div>';
    print '</div>';
    
    print '</td>';
    
    if ($attendantTableMode == 'simple') {
        print '<td class="center">';
        print saturne_select_dictionary('attendant_role','c_' . $object->element . '_attendants_role', 'ref');
        print '</td>';
    }
    print '<td colspan="' . ($conf->browser->layout != 'classic' ? 3 : 4) . '"></td>';
    print '<td class="center">';
    print '<button type="submit" class="wpeo-button button-blue"><i class="fas fa-plus"></i></button>';
    print '</td></tr>';
    print '</form>';

    print '</table>';

    print '</div>';
}

// End of page
llxFooter();
$db->close();
