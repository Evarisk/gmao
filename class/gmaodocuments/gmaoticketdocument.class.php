<?php
/* Copyright (C) 2024 EVARISK <technique@evarisk.com>
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
 * \file    class/gmaodocuments/gmaoticketdocument.class.php
 * \ingroup gmao
 * \brief   This file is a class file for GMAOTicketDocument
 */

// Load Dolibarr libraries
require_once DOL_DOCUMENT_ROOT . '/core/lib/images.lib.php';

// Load Saturne libraries
require_once __DIR__ . '/../../../saturne/class/saturnedocuments.class.php';

/**
 * Class for GMAOTicketDocument
 */
class GMAOTicketDocument extends SaturneDocuments
{
    /**
     * @var string Module name
     */
    public $module = 'gmao';

    /**
     * @var string Element type of object
     */
    public $element = 'gmaoticketdocument';

    /**
     * Constructor
     *
     * @param DoliDb $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        parent::__construct($db, $this->module, $this->element);
    }

     /**
     * Create a QR Code if the file doesn't already exist
     *
     * @param array  $moreParams Manage all the contents of the QRCode
     * @param Ticket $ticket     Allow to use track_id of the ticket
     */
    public function createQRCode(array $moreParams, Ticket $ticket)
    {
        global $conf;

        require_once TCPDF_PATH . 'tcpdf_barcodes_2d.php';

        dol_mkdir($conf->ticket->multidir_output[$conf->entity] . '/' . $ticket->ref . '/qrcode/');
        foreach ($moreParams as $key => $params) {
            $techBarcode = new TCPDF2DBarcode(dol_buildpath($params['url'], 3), 'QRCODE,H');

            $file = $conf->ticket->multidir_output[$conf->entity] . '/' . $ticket->ref . '/qrcode/' . 'barcode_' . $key . '_' . $ticket->track_id . '.png';
            if (!file_exists($file)) {
                $qrImageData = $techBarcode->getBarcodePngData();
                $qrImageData = imagecreatefromstring($qrImageData);
                imagepng($qrImageData, $file);
                vignette($file, 70, 70);
            }
        }
    }
}
