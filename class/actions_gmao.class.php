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
            if ($action == 'create_gmao') {
                // Load Dolibarr libraries
                require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';

                // Load Saturne libraries
                require_once __DIR__ . '/../../saturne/lib/object.lib.php';

                $propal = new Propal($this->db);

                $numberingModuleName = ['propale' => $conf->global->PROPALE_ADDON];
                list($modPropal)     = saturne_require_objects_mod($numberingModuleName);

                $propal->ref            = $modPropal->getNextValue(0, $propal);
                $propal->socid          = $object->fk_soc;
                $propal->date           = dol_now();
                $propal->duree_validite = getDolGlobalInt('PROPALE_VALIDITY_DURATION');
                $propal->fk_project     = $object->fk_project;
                $propal->model_pdf      = (getDolGlobalString('PROPALE_ADDON_PDF_ODT_DEFAULT') ? getDolGlobalString('PROPALE_ADDON_PDF_ODT_DEFAULT') : getDolGlobalString('PROPALE_ADDON_PDF'));

                $propalID = $propal->create($user);

                $object->add_object_linked('propal', $propalID);

                if (getDolGlobalInt('GMAO_PROPOSAL_SERVICE_ID') && $propalID > 0) {
                    // Load Dolibarr libraries
                    require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

                    $product    = new Product($this->db);
                    $propalLine = new PropaleLigne($this->db);

                    $product->fetch(getDolGlobalInt('GMAO_PROPOSAL_SERVICE_ID'));

                    $propalLine->fk_propal    = $propalID;
                    $propalLine->fk_product   = $product->id;
                    $propalLine->desc         = $product->description;
                    $propalLine->qty          = 1;
                    $propalLine->product_type = 1;
                    $propalLine->rang         = 1;

                    $propalLine->insert($user);
                }
              
                header('Location: ' . DOL_URL_ROOT . '/comm/propal/card.php?id=' . $propalID);
                exit;
            }
        }

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

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the addMoreActionsButtons function : replacing the parent's function with the one below
     *
     * @param  array  $parameters Hook metadata (context, etc...)
     * @param  object $object     The object to process
     * @return int                0 < on error, 0 on success, 1 to replace standard code
     */
    public function addMoreActionsButtons(array $parameters, &$object, &$action): int
    {
        global $langs, $user;

        if (strpos($parameters['context'], 'ticketcard') !== false) {
            $langs->load('gmao@gmao');

            if (getDolGlobalInt('GMAO_ENABLE_TICKET_PROPOSAL')) {
                print dolGetButtonAction('', img_picto('', 'fa-file-signature') . ' ' . $langs->trans('AddProp'), 'default', dol_buildpath('/comm/propal/card.php?action=create&socid=' . $object->socid . '&projectid=' . $object->fk_project, 1), '', $user->rights->propale->creer);
            }
            if (getDolGlobalInt('GMAO_ENABLE_TICKET_PROPOSAL_GMAO')) {
                if (getDolGlobalInt('GMAO_PROPOSAL_SERVICE_ID') > 0 && $object->fk_soc > 0) {
                    print dolGetButtonAction('', img_picto('', 'fa-file-signature') . ' ' . $langs->trans('CreateGMAO'), 'default', $_SERVER['PHP_SELF'] . '?action=create_gmao&id=' . $object->id . '&token=' . newToken(), '', $user->rights->propale->creer);
                } elseif (empty($object->fk_soc)) {
                    print '<span class="butActionRefused classfortooltip" title="' . dol_escape_htmltag($langs->trans('ErrorFieldRequired', $langs->trans($object->fields['fk_soc']['label']))) . '">' . img_picto('', 'fa-file-signature') . ' ' . $langs->trans('CreateGMAO') . '</span>';
                } else {
                    print '<span class="butActionRefused classfortooltip" title="' . dol_escape_htmltag($langs->trans('ErrorConfigProposalService')) . '">' . img_picto('', 'fa-file-signature') . ' ' . $langs->trans('CreateGMAO') . '</span>';
                }
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
