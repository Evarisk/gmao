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
     * Overloading the addHtmlHeader function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function addHtmlHeader(array $parameters): int
    {
        if (strpos($parameters['context'], 'ticketcard') !== false) {
            $resourcesRequired = [
                'css' => '/custom/saturne/css/saturne.min.css',
                'js'  => '/custom/saturne/js/saturne.min.js'
            ];

            $out  = '<!-- Includes CSS added by module saturne -->';
            $out .= '<link rel="stylesheet" type="text/css" href="' . dol_buildpath($resourcesRequired['css'], 1) . '">';
            $out .= '<!-- Includes JS added by module saturne -->';
            $out .= '<script src="' . dol_buildpath($resourcesRequired['js'], 1) . '"></script>';

            $this->resprints = $out;
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param  array  $parameters Hook metadata (context, etc...)
     * @param  object $object     The object to process
     * @param  string $action     Current action (if set). Generally create or edit or null
     * @return int                0 < on error, 0 on success, 1 to replace standard code
     */
    public function doActions(array $parameters, $object, string $action): int
    {
        global $conf, $langs, $user;

        if (strpos($parameters['context'], 'ticketcard') !== false) {
            if ($action == 'builddoc' && strstr(GETPOST('model'), 'gmaoticketdocument_odt')) {
                require_once __DIR__ . '/gmaodocuments/gmaoticketdocument.class.php';

                $document = new GMAOTicketDocument($this->db);

                $moduleNameLowerCase = 'gmao';
                $permissiontoadd     = $user->rights->ticket->write;

                require_once __DIR__ . '/../../saturne/core/tpl/documents/documents_action.tpl.php';
            }

            if ($action == 'pdfGeneration') {
                $moduleName          = 'GMAO';
                $moduleNameLowerCase = strtolower($moduleName);
                $upload_dir          = $conf->gmao->multidir_output[$conf->entity ?? 1];

                // Action to generate pdf from odt file
                require_once __DIR__ . '/../../saturne/core/tpl/documents/saturne_manual_pdf_generation_action.tpl.php';

                $urlToRedirect = $_SERVER['REQUEST_URI'];
                $urlToRedirect = preg_replace('/#pdfGeneration$/', '', $urlToRedirect);
                $urlToRedirect = preg_replace('/action=pdfGeneration&?/', '', $urlToRedirect); // To avoid infinite loop

                header('Location: ' . $urlToRedirect);
                exit;
            }
        }

        return 0; // or return 1 to replace standard code
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
            if (GETPOST('action') != 'create') {
                global $user;

                $langs->load('gmao@gmao');

                $moduleNameLowerCase = 'gmao';

                require_once __DIR__ . '/../../saturne/lib/documents.lib.php';

                $upload_dir = $conf->gmao->multidir_output[$object->entity ?? 1];
                $objRef     = dol_sanitizeFileName($object->ref);
                $dirFiles   = 'gmao' . $object->element . 'document/' . $objRef;
                $fileDir    = $upload_dir . '/' . $dirFiles;
                $urlSource  = $_SERVER['PHP_SELF'] . '?id=' . $object->id;

                $out = saturne_show_documents('gmao:GMAOTicketDocument', $dirFiles, $fileDir, $urlSource, $user->rights->ticket->write, $user->rights->ticket->delete, '', 1, 0, 0, 0, '', '', '', '', '', $object); ?>

                <script>
                    jQuery('.fichehalfleft .div-table-responsive-no-min').append(<?php echo json_encode($out) ; ?>)
                </script>
                <?php
            }
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the saturneAdminDocumentData function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadatas (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     */
    public function saturneAdminDocumentData(array $parameters): int
    {
        // Do something only for the current context
        if (strpos($parameters['context'], 'gmaoadmindocuments') !== false) {
            $types = [
                'GMAOTicketDocument' => [
                    'documentType' => 'gmaoticketdocument',
                    'picto'        => 'fontawesome_fa-fa-ticket-alt_fas_#d35968'
                ]
            ];
            $this->results = $types;
        }

        return 0; // or return 1 to replace standard code
    }
}
