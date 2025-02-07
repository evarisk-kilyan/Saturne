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
 * \file    lib/saturnepublicinterface.lib.php
 * \ingroup saturne
 * \brief   Library files with common functions for SaturnePublicInterface
 */

function saturne_publicinterfacePrepareHead($object)
{
    global $db, $langs, $conf;

    $langs->load("saturne@saturne");

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/saturne/view/publicinterface/publicinterface_card.php", 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Card");
    $head[$h][2] = 'card';
    $h++;

    $head[$h][0] = dol_buildpath("/saturne/view/publicinterface/publicinterface_parameters.php", 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'parameters';
    $h++;

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'saturne@publicinterface');

    complete_head_from_modules($conf, $langs, $object, $head, $h, 'saturne@publicinterface', 'remove');

    return $head;
}
