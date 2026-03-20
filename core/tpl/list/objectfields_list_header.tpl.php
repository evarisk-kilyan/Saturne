<?php
/* Copyright (C) 2025 EVARISK <technique@evarisk.com>
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
 * \file    core/tpl/list/objectfields_list_header.tpl.php
 * \ingroup saturne
 * \brief   Template page for object fields list header
 */

/**
 * The following vars must be defined :
 * Globals    : $conf, $db, $hookmanager, $langs, $user
 * Parameters : $action, $limit, $contextpage, $massaction, $mode, $optioncss, $page, $searchAll, $sortfield, $sortorder, $toselect
 * Objects    : $categorie, $extrafields (extrafields_list_search_param.tpl), $form, $object
 * Variables  : $arrayfields, $createUrl (optional), $fieldsToSearchAll, $formMoreParams (optional), $helpText (optional),
 *              $nbTotalOfRecords, $num, $permissiontoadd, $resql, $search, $search_array_options (extrafields_list_search_param.tpl),
 *              $searchCategoriesFilter (optional, signed int array: +id=include, -id=exclude), $sql, $title
 */

// Output page
// --------------------------------------------------------------------
$arrayofselected = is_array($toselect) ? $toselect : [];

$param = '';
if (!empty($mode)) {
    $param .= '&mode=' . urlencode($mode);
}
if (!empty($contextpage) && $contextpage != $_SERVER['PHP_SELF']) {
    $param .= '&contextpage=' . urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
    $param .= '&limit=' . ((int) $limit);
}
if ($optioncss != '') {
    $param .= '&optioncss=' . urlencode($optioncss);
}
//if ($groupby != '') {
//    $param .= '&groupby=' . urlencode($groupby);
//}
if (!empty($formMoreParams)) {
    foreach ($formMoreParams as $formMoreParamKey => $formMoreParamVal) {
        $param .= $param .= '&' . $formMoreParamKey . '=' . urlencode($formMoreParamVal);
    }
}
foreach ($search as $key => $val) {
    if (is_array($val)) {
        foreach ($val as $skey) {
            if ($skey != '') {
                $param .= '&search_' . $key . '[]=' . urlencode($skey);
            }
        }
    } elseif (preg_match('/(_dtstart|_dtend)$/', $key) && !empty($val)) {
        $param .= '&search_' . $key . 'min=' . GETPOSTINT('search_' . $key . 'min');
        $param .= '&search_' . $key . 'hour=' . GETPOSTINT('search_' . $key . 'hour');
        $param .= '&search_' . $key . 'month=' . GETPOSTINT('search_' . $key . 'month');
        $param .= '&search_' . $key . 'day=' . GETPOSTINT('search_' . $key . 'day');
        $param .= '&search_' . $key . 'year=' . GETPOSTINT('search_' . $key . 'year');
    } elseif ($val != '') {
        $param .= '&search_' . $key . '=' . urlencode($val);
    }
}

if (!empty($searchCategoriesFilter) && is_array($searchCategoriesFilter)) {
    foreach ($searchCategoriesFilter as $filterVal) {
        $param .= '&search_categories_filter[]=' . urlencode((string)(int) $filterVal);
    }
}

// Add $param from extra fields
require_once DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_list_search_param.tpl.php';

// Add $param from hooks
$parameters = ['param' => &$param];
$hookmanager->executeHooks('printFieldListSearchParam', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
$param .= $hookmanager->resPrint;

// List of mass actions available
$arrayOfMassActions = [
    //'validate'=>img_picto('', 'check', 'class="pictofixedwidth"').$langs->trans("Validate"),
    //'generate_doc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("ReGeneratePDF"),
    //'builddoc'=>img_picto('', 'pdf', 'class="pictofixedwidth"').$langs->trans("PDFMerge"),
    //'presend'=>img_picto('', 'email', 'class="pictofixedwidth"').$langs->trans("SendByMail"),
    'prearchive' => '<span class="fas fa-archive paddingrightonly"></span>' . $langs->trans('Archive')
];

if (!empty($permissiontodelete)) {
    $arrayOfMassActions['predelete'] = img_picto('', 'delete', 'class="pictofixedwidth"') . $langs->trans('Delete');
}
if (GETPOSTINT('nomassaction') || in_array($massaction, ['presend', 'predelete'])) {
    $arrayOfMassActions = [];
}
$massActionButton = $form->selectMassAction('', $arrayOfMassActions);

print '<form method="POST" id="searchFormList" action="' . $_SERVER['PHP_SELF'] . '">';
if ($optioncss != '') {
    print '<input type="hidden" name="optioncss" value="' . $optioncss . '">';
}
print '<input type="hidden" name="token" value="' . newToken() . '">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="' . $sortfield . '">';
print '<input type="hidden" name="sortorder" value="' . $sortorder . '">';
print '<input type="hidden" name="page" value="' . $page . '">';
print '<input type="hidden" name="contextpage" value="' . $contextpage . '">';
print '<input type="hidden" name="page_y" value="">';
print '<input type="hidden" name="mode" value="' . $mode . '">';
//print '<input type="hidden" name="groupby" value="' . $groupby . '">';
if (!empty($formMoreParams)) {
    foreach ($formMoreParams as $formMoreParamKey => $formMoreParamVal) {
        print '<input type="hidden" name="' . $formMoreParamKey . '" value="' . $formMoreParamVal . '">';
    }
}

$newCardButton  = ($newCardButton ?? '');
$newCardButton .= dolGetButtonTitle($langs->trans('ViewList'), '', 'fa fa-bars imgforviewmode', $_SERVER['PHP_SELF'] . '?mode=common' . preg_replace('/([&?])*mode=[^&]+/', '', $param), '', ((empty($mode) || $mode == 'common') ? 2 : 1), ['morecss' => 'reposition']);
$newCardButton .= dolGetButtonTitle($langs->trans('ViewKanban'), '', 'fa fa-th-list imgforviewmode', $_SERVER['PHP_SELF'] . '?mode=kanban' . preg_replace('/([&?])*mode=[^&]+/', '', $param), '', ($mode == 'kanban' ? 2 : 1), ['morecss' => 'reposition']);
$newCardButton .= dolGetButtonTitle($langs->trans('ViewPwa'), '', 'fa fa-mobile imgforviewmode', $_SERVER['PHP_SELF'] . '?mode=pwa' . preg_replace('/([&?])*mode=[^&]+/', '', $param), '', ($mode == 'pwa' ? 2 : 1), ['morecss' => 'reposition']);
$cardButton     = dolGetButtonTitle($langs->trans('New' . ucfirst($object->element)), $helpText ?? '', 'fa fa-plus-circle', ($createUrl ?? dol_buildpath('custom/' . $object->module . '/view/' . $object->element . '/' . $object->element . '_card.php', 1) . '?action=create' . ($moreUrlParameters ?? '')), '', $permissiontoadd);

print_barre_liste((($conf->browser->layout == 'classic' && $mode != 'pwa') ? $title : '') . ' ' . $cardButton, $page, $_SERVER['PHP_SELF'], $param, $sortfield, $sortorder, $massActionButton, $num, $nbTotalOfRecords, $object->picto, 0, $newCardButton, '', $limit, 0, 0, 1);

// Add code for pre mass action (confirmation or email presend form)
//$topicmail = "SendMyObjectRef";
//$modelmail = "myobject";
//$objecttmp = new MyObject($db);
//$trackid = 'xxxx'.$object->id;

require_once DOL_DOCUMENT_ROOT . '/core/tpl/massactions_pre.tpl.php';

if ($massaction == 'prearchive') {
    print $form->formconfirm($_SERVER['PHP_SELF'], $langs->trans('ConfirmMassArchive'), $langs->trans('ConfirmMassArchivingQuestion', count($toselect)), 'archive', null, '', 0, 200, 500, 1);
}

if ($searchAll) {
    foreach ($fieldsToSearchAll as $key => $val) {
        $fieldsToSearchAll[$key] = $langs->trans($val);
    }
    print '<div class="divsearchfieldfilter">' . $langs->trans('FilterOnInto', $searchAll) . implode(', ', $fieldsToSearchAll) . '</div>';
}

$moreForFilter = '';
if (isModEnabled('categorie') && $user->hasRight('categorie', 'read') && isset($categorie->MAP_OBJ_CLASS[$object->element])) {
    require_once DOL_DOCUMENT_ROOT . '/core/class/html.formcategory.class.php';
    $formCategory  = new FormCategory($db);
    $rawCategories = $formCategory->select_all_categories($object->element, '', '', 64, 0, 2); // outputmode=2 → full arbo with color
    $langs->load('categories');

    // Build flat map [id => ['label' => string, 'color' => '#rrggbb']]
    $categoryMap = [];
    if (is_array($rawCategories)) {
        foreach ($rawCategories as $cat) {
            $hex               = !empty($cat['color']) ? '#' . ltrim($cat['color'], '#') : '#95a5a6';
            $categoryMap[(int) $cat['id']] = ['label' => $cat['fulllabel'], 'color' => $hex];
        }
    }

    // Read from GET/POST if not set by the calling page
    if (!isset($searchCategoriesFilter)) {
        $searchCategoriesFilter = array_values(array_filter(array_map('intval', GETPOST('search_categories_filter', 'array'))));
    }

    // Decode current filter values into initial tag state
    $initialTags      = [];
    $initialTagCatIds = [];
    foreach (($searchCategoriesFilter ?? []) as $filterVal) {
        $id   = abs((int) $filterVal);
        $mode = ((int) $filterVal < 0) ? 'exc' : 'inc';
        if ($id > 0 && isset($categoryMap[$id])) {
            $initialTags[]      = ['id' => $id, 'label' => $categoryMap[$id]['label'], 'color' => $categoryMap[$id]['color'], 'mode' => $mode];
            $initialTagCatIds[] = $id;
        }
    }

    $elementId = dol_escape_htmltag($object->element);

    // Pass color map to JS: { "5": "#3498db", ... }
    $catColorsJs = json_encode(array_map(fn($v) => $v['color'], $categoryMap));

    $moreForFilter .= '<div class="divsearchfield" style="display:inline-flex;align-items:center;gap:6px;flex-wrap:wrap">';
    $moreForFilter .= img_picto($langs->trans('Categories'), 'category', 'class="pictofixedwidth"');

    // Picker with data-color on each option (select2 will render the colored dot)
    $moreForFilter .= '<select id="cat_filter_picker_' . $elementId . '" class="flat minwidth200" title="' . dol_escape_htmltag($langs->trans('AddCategory')) . '">';
    $moreForFilter .= '<option value="">&nbsp;</option>';
    foreach ($categoryMap as $catId => $catData) {
        if (in_array($catId, $initialTagCatIds)) {
            continue;
        }
        $moreForFilter .= '<option value="' . $catId . '" data-color="' . dol_escape_htmltag($catData['color']) . '">' . dol_escape_htmltag($catData['label']) . '</option>';
    }
    $moreForFilter .= '</select>';

    // Category icon HTML captured once, passed to JS
    $catIcon   = img_picto('', 'category', 'style="width:12px;height:12px;vertical-align:middle;color:inherit;filter:brightness(10)"');
    $catIconJs = json_encode($catIcon);

    // Tag list — pill design: [icon ±] [label ×]
    $moreForFilter .= '<span id="cat_filter_tags_' . $elementId . '" style="display:inline-flex;flex-wrap:wrap;gap:6px;align-items:center">';
    foreach ($initialTags as $tag) {
        $isExc     = $tag['mode'] === 'exc';
        $color     = $tag['color'];
        $sign      = $isExc ? '&minus;' : '+';
        $val       = ($isExc ? '-' : '+') . $tag['id'];
        // Include : section gauche pleine (couleur), icône blanc
        // Exclude : section gauche inversée (fond blanc, icône + signe coloré)
        $moreForFilter .= '<span style="display:inline-flex;align-items:stretch;border-radius:20px;overflow:hidden;border:2px solid ' . $color . ';box-shadow:0 1px 4px rgba(0,0,0,.15);cursor:default;user-select:none;font-size:12px;line-height:1"';
        $moreForFilter .= ' data-catid="' . $tag['id'] . '" data-mode="' . $tag['mode'] . '" data-label="' . dol_escape_htmltag($tag['label']) . '" data-color="' . dol_escape_htmltag($color) . '">';
        $moreForFilter .= '<span class="cat-sign" title="' . dol_escape_htmltag($langs->trans('ToggleIncludeExclude')) . '"';
        $moreForFilter .= ' style="display:flex;align-items:center;gap:3px;padding:4px 8px;background:' . $color . ';color:#fff;cursor:pointer;font-weight:bold">';
        $moreForFilter .= $catIcon . ' ' . $sign;
        $moreForFilter .= '</span>';
        $moreForFilter .= '<span style="display:flex;align-items:center;gap:6px;padding:4px 8px;background:#fff;color:#333">';
        $moreForFilter .= '<span style="max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis' . ($isExc ? ';text-decoration:line-through' : '') . '">' . dol_escape_htmltag($tag['label']) . '</span>';
        $moreForFilter .= '<span class="cat-remove" title="' . dol_escape_htmltag($langs->trans('Remove')) . '"';
        $moreForFilter .= ' style="cursor:pointer;color:#aaa;font-size:14px;line-height:1;font-weight:bold" onmouseover="this.style.color=\'#333\'" onmouseout="this.style.color=\'#aaa\'">&times;</span>';
        $moreForFilter .= '</span>';
        $moreForFilter .= '<input type="hidden" name="search_categories_filter[]" value="' . dol_escape_htmltag($val) . '">';
        $moreForFilter .= '</span>';
    }
    $moreForFilter .= '</span>';

    $moreForFilter .= '<script>(function () {
        var picker    = document.getElementById("cat_filter_picker_' . $elementId . '");
        var tags      = document.getElementById("cat_filter_tags_' . $elementId . '");
        var catIcon   = ' . $catIconJs . ';
        var catColors = ' . $catColorsJs . ';
        var FALLBACK  = "#95a5a6";

        function esc(s) {
            return s.replace(/[<>&"]/g, function(c) { return {"<":"&lt;",">":"&gt;","&":"&amp;","\"":"&quot;"}[c]; });
        }

        function getColor(catId) {
            return catColors[catId] || FALLBACK;
        }

        // Select2: render colored dot before each option label
        if (typeof jQuery !== "undefined" && jQuery.fn.select2) {
            jQuery(picker).select2({
                width: "resolve",
                templateResult: function (opt) {
                    if (!opt.id) return opt.text;
                    var color = jQuery(opt.element).data("color") || FALLBACK;
                    return jQuery("<span>").append(
                        jQuery("<span>").css({ display:"inline-block", width:"10px", height:"10px", borderRadius:"50%", background:color, marginRight:"6px", verticalAlign:"middle" }),
                        document.createTextNode(opt.text)
                    );
                },
                templateSelection: function (opt) {
                    if (!opt.id) return opt.text;
                    var color = jQuery(opt.element).data("color") || FALLBACK;
                    return jQuery("<span>").append(
                        jQuery("<span>").css({ display:"inline-block", width:"10px", height:"10px", borderRadius:"50%", background:color, marginRight:"6px", verticalAlign:"middle" }),
                        document.createTextNode(opt.text)
                    );
                }
            }).on("select2:select", function (e) {
                var opt = e.params.data;
                buildTag(opt.id, opt.text, "inc");
                jQuery(picker).val("").trigger("change.select2");
            });
        } else {
            picker.addEventListener("change", function () {
                var opt = picker.options[picker.selectedIndex];
                if (!opt.value) return;
                buildTag(opt.value, opt.text, "inc");
                picker.selectedIndex = 0;
            });
        }

        function removePickerOption(catId) {
            var opt = picker.querySelector("option[value=\"" + catId + "\"]");
            if (opt) opt.remove();
            if (typeof jQuery !== "undefined" && jQuery.fn.select2) jQuery(picker).trigger("change.select2");
        }

        function restorePickerOption(catId, catLabel, catColor) {
            if (picker.querySelector("option[value=\"" + catId + "\"]")) return;
            var opt          = document.createElement("option");
            opt.value        = catId;
            opt.text         = catLabel;
            opt.dataset.color = catColor;
            picker.appendChild(opt);
            if (typeof jQuery !== "undefined" && jQuery.fn.select2) jQuery(picker).trigger("change.select2");
        }

        function renderTagHTML(catId, catLabel, catColor, mode) {
            var isExc     = mode === "exc";
            var sign      = isExc ? "\u2212" : "+";
            var labelStyle = "max-width:140px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" + (isExc ? ";text-decoration:line-through" : "");
            return "<span class=\"cat-sign\" style=\"display:flex;align-items:center;gap:3px;padding:4px 8px;background:" + catColor + ";color:#fff;cursor:pointer;font-weight:bold\">" + catIcon + " " + sign + "</span>"
                 + "<span style=\"display:flex;align-items:center;gap:6px;padding:4px 8px;background:#fff;color:#333\">"
                 +   "<span style=\"" + labelStyle + "\">" + esc(catLabel) + "</span>"
                 +   "<span class=\"cat-remove\" style=\"cursor:pointer;color:#aaa;font-size:14px;line-height:1;font-weight:bold\">\u00d7</span>"
                 + "</span>"
                 + "<input type=\"hidden\" name=\"search_categories_filter[]\" value=\"" + (isExc ? "-" : "+") + catId + "\">";
        }

        function buildTag(catId, catLabel, mode) {
            if (tags.querySelector("[data-catid=\"" + catId + "\"]")) return;
            var catColor       = getColor(catId);
            var span           = document.createElement("span");
            span.dataset.catid = catId;
            span.dataset.mode  = mode;
            span.dataset.label = catLabel;
            span.dataset.color = catColor;
            span.style.cssText = "display:inline-flex;align-items:stretch;border-radius:20px;overflow:hidden;border:2px solid " + catColor + ";box-shadow:0 1px 4px rgba(0,0,0,.15);cursor:default;user-select:none;font-size:12px;line-height:1";
            span.innerHTML     = renderTagHTML(catId, catLabel, catColor, mode);
            removePickerOption(catId);
            bindTag(span);
            tags.appendChild(span);
        }

        function bindTag(span) {
            span.querySelector(".cat-sign").addEventListener("click", function (e) {
                e.stopPropagation();
                var newMode        = span.dataset.mode === "inc" ? "exc" : "inc";
                span.dataset.mode  = newMode;
                span.innerHTML     = renderTagHTML(span.dataset.catid, span.dataset.label, span.dataset.color, newMode);
                bindTag(span);
            });
            var rem = span.querySelector(".cat-remove");
            rem.addEventListener("mouseover", function () { rem.style.color = "#333"; });
            rem.addEventListener("mouseout",  function () { rem.style.color = "#aaa"; });
            rem.addEventListener("click", function (e) {
                e.stopPropagation();
                restorePickerOption(span.dataset.catid, span.dataset.label, span.dataset.color);
                span.remove();
            });
        }

        tags.querySelectorAll("[data-catid]").forEach(bindTag);
    }());</script>';

    $moreForFilter .= '</div>';
}

$parameters = ['arrayfields' => &$arrayfields];
$reshook    = $hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
if (empty($reshook)) {
    $moreForFilter .= $hookmanager->resPrint;
} else {
    $moreForFilter = $hookmanager->resPrint;
}

if (!empty($moreForFilter)) {
    print '<div class="liste_titre liste_titre_bydiv centpercent">';
    print $moreForFilter;
    print '</div>';
}

$selectedFields = '';
if ($mode != 'pwa' && $mode != 'kanban') {
    $varPage        = $contextpage ?: $_SERVER['PHP_SELF'];
    $selectedFields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varPage, getDolGlobalString('MAIN_CHECKBOX_LEFT_COLUMN'));
}
if (!empty($arrayOfMassActions)) {
    $selectedFields .= $form->showCheckAddButtons('checkforselect', 1);
}

print '<div class="div-table-responsive">'; // You can use div-table-responsive-no-min if you don't need reserved height for your table
print '<table class="tagtable nobottomiftotal noborder liste' . ($moreForFilter ? ' listwithfilterbefore' : '') . '">';
print '<thead>';