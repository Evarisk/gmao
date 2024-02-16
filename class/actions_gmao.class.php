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
 * \file    class/actions_gmao.class.php
 * \ingroup gmao
 * \brief   GMAO hook overload
 */

/**
 * Class ActionsGmao
 */
class ActionsGmao
{
    /**
     * @var DoliDB Database handler
     */
    public DoliDB $db;

    /**
     * @var string Error code (or message)
     */
    public string $error = '';

    /**
     * @var array Errors.
     */
    public array $errors = [];

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public array $results = [];

    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public string $resprints;

    /**
     * Constructor
     *
     *  @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * Overloading the printCommonFooter function : replacing the parent's function with the one below
     *
     * @param  array     $parameters Hook metadatas (context, etc...)
     * @return int                   0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function printCommonFooter(array $parameters): int
    {
        global $conf, $langs, $object;

        if (strpos($parameters['context'], 'ticketcard') !== false) {
            if (getDolGlobalInt('TICKET_ENABLE_PUBLIC_INTERFACE')) {
                require_once __DIR__ . '/../../saturne/lib/medias.lib.php';

                $publicInterfaceUrl = dol_buildpath('public/ticket/view.php?track_id=' . $object->track_id . '&entity=' . $conf->entity, 3);
                $out  = '<tr><td class="titlefield">' . $langs->trans('PublicInterface') . ' <a href="' . $publicInterfaceUrl . '" target="_blank"><i class="fas fa-qrcode"></i></a>';
                $out .= showValueWithClipboardCPButton($publicInterfaceUrl, 0, '&nbsp;');
                $out .= '</td>';
                $out .= '<td>' . saturne_show_medias_linked('ticket', $conf->ticket->multidir_output[$conf->entity] . '/' . $object->ref . '/qrcode/', 'small', 1, 0, 0, 0, 80, 80, 0, 0, 0, $object->ref . '/qrcode/', $object, '', 0, 0) . '</td></tr>'; ?>

                <script>
                    jQuery('.fichehalfleft table tr:first-child').first().before(<?php echo json_encode($out); ?>)
                </script>
                <?php
            }
        }

        return 0; // or return 1 to replace standard code
    }
}
