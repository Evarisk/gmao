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
 * \file    core/triggers/interface_99_modGmao_GmaoTriggers.class.php
 * \ingroup gmao
 * \brief   GMAO trigger
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/triggers/dolibarrtriggers.class.php';

/**
 * Class of triggers for GMAO module
 */
class InterfaceGMAOTriggers extends DolibarrTriggers
{
    /**
     * @var DoliDB Database handler
     */
    protected $db;

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;

        $this->name        = preg_replace('/^Interface/i', '', get_class($this));
        $this->family      = 'demo';
        $this->description = 'GMAO triggers';
        $this->version     = '1.0.0';
        $this->picto       = 'gmao@gmao';
    }

    /**
     * Trigger name
     *
     * @return string Name of trigger file
     */
    public function getName(): string
    {
        return parent::getName();
    }

    /**
     * Trigger description
     *
     * @return string Description of trigger file
     */
    public function getDesc(): string
    {
        return parent::getDesc();
    }

    /**
     * Function called when a Dolibarr business event is done
     * All functions "runTrigger" are triggered if file
     * is inside directory core/triggers
     *
     * @param  string       $action Event action code
     * @param  CommonObject $object Object
     * @param  User         $user   Object user
     * @param  Translate    $langs  Object langs
     * @param  Conf         $conf   Object conf
     * @return int                  0 < if KO, 0 if no triggered ran, >0 if OK
     * @throws Exception
     */
    public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf): int
    {
        if (!isModEnabled('gmao')) {
            return 0; // If module is not enabled, we do nothing
        }

        saturne_load_langs();

        // Data and type of action are stored into $object and $action.
        dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . '. id=' . $object->id);

        require_once DOL_DOCUMENT_ROOT . '/comm/action/class/actioncomm.class.php';
        $now        = dol_now();
        $actionComm = new ActionComm($this->db);

        $actionComm->elementtype = $object->element . '@gmao';
        $actionComm->type_code   = 'AC_OTH_AUTO';
        $actionComm->code        = 'AC_' . $action;
        $actionComm->datep       = $now;
        $actionComm->fk_element  = $object->id;
        $actionComm->userownerid = $user->id;
        $actionComm->percentage  = -1;

        if (getDolGlobalInt('GMAO_ADVANCED_TRIGGER') && !empty($object->fields)) {
            $actionComm->note_private = method_exists($object, 'getTriggerDescription') ? $object->getTriggerDescription($object) : '';
        }

        switch ($action) {
            case 'TICKET_CREATE' :
                require_once TCPDF_PATH . 'tcpdf_barcodes_2d.php';

                $url = dol_buildpath('public/ticket/view.php?track_id=' . $object->track_id . '&entity=' . $conf->entity, 3);

                $barcode = new TCPDF2DBarcode($url, 'QRCODE,L');

                dol_mkdir($conf->ticket->multidir_output[$conf->entity] . '/' . $object->ref . '/qrcode/');
                $file = $conf->ticket->multidir_output[$conf->entity] . '/' . $object->ref . '/qrcode/' . 'barcode_' . $object->track_id . '.png';

                $imageData = $barcode->getBarcodePngData();
                $imageData = imagecreatefromstring($imageData);
                imagepng($imageData, $file);
                break;
        }

        return 0;
    }
}
