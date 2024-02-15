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

                $propal->ref             = $modPropal->getNextValue(0, $propal);
                $propal->socid           = $object->fk_soc;
                $propal->date            = dol_now();
                $propal->duree_validite  = getDolGlobalInt('PROPALE_VALIDITY_DURATION');
                $propal->fk_project      = $object->fk_project;
                $propal->model_pdf       = (getDolGlobalString('PROPALE_ADDON_PDF_ODT_DEFAULT') ? getDolGlobalString('PROPALE_ADDON_PDF_ODT_DEFAULT') : getDolGlobalString('PROPALE_ADDON_PDF'));

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
                print dolGetButtonAction('', img_picto('', 'fa-file-signature') . ' ' . $langs->trans('CreateGMAO'), 'default', $_SERVER['PHP_SELF'] . '?action=create_gmao&id=' . $object->id . '&token=' . newToken(), '', $user->rights->propale->creer);
            }
        }

        return 0; // or return 1 to replace standard code
    }
}
