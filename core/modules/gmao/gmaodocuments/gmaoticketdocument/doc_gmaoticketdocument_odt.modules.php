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
 * or see https://www.gnu.org/
 */

/**
 * \file    core/modules/gmao/gmaodocuments/gmaoticketdocument/doc_gmaoticketdocument_odt.modules.php
 * \ingroup gmao
 * \brief   File of class to build ODT gmao ticket document
 */

// Load Saturne libraries
require_once __DIR__ . '/../../../../../../saturne/core/modules/saturne/modules_saturne.php';

/**
 * Class to build documents using ODF templates generator
 */
class doc_gmaoticketdocument_odt extends SaturneDocumentModel
{
    /**
     * @var string Module
     */
    public string $module = 'gmao';

    /**
     * @var string Document type
     */
    public string $document_type = 'gmaoticketdocument';

    /**
     * Constructor
     *
     * @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        parent::__construct($db, $this->module, $this->document_type);
    }

    /**
     * Return description of a module
     *
     * @param  Translate $langs Lang object to use for output
     * @return string           Description
     */
    public function info(Translate $langs): string
    {
        return parent::info($langs);
    }

    /**
     * Function to build a document on disk
     *
     * @param  SaturneDocuments $objectDocument  Object source to build document
     * @param  Translate        $outputLangs     Lang object to use for output
     * @param  string           $srcTemplatePath Full path of source filename for generator using a template file
     * @param  int              $hideDetails     Do not show line details
     * @param  int              $hideDesc        Do not show desc
     * @param  int              $hideRef         Do not show ref
     * @param  array            $moreParam       More param (Object/user/etc)
     * @return int                               1 if OK, <=0 if KO
     * @throws Exception
     */
    public function write_file(SaturneDocuments $objectDocument, Translate $outputLangs, string $srcTemplatePath, int $hideDetails = 0, int $hideDesc = 0, int $hideRef = 0, array $moreParam): int
    {
        global $conf, $langs;

        $object = $moreParam['object'];

        $outputLangs->load('gmao@gmao');
        $thirdParty = new Societe($this->db);
        $userAssign = new User($this->db);

        $thirdParty->fetch($object->fk_soc);
        $userAssign->fetch($object->fk_user_assign);

        $path    = $conf->ticket->multidir_output[$conf->entity] . '/' . $object->ref . '/qrcode/thumbs/';
        $imgList = dol_dir_list($path, 'files');
        if (!empty($imgList)) {
            foreach ($imgList as $img) {
                if (strpos($img['fullname'], 'gmaoclientticketdocument')) {
                    $tmpArray['photo'] = $img['fullname'];
                } else {
                    $tmpArray['photo_tech'] = $img['fullname'];
                }
            }
        } else {
            $noPhoto                = '/public/theme/common/nophoto.png';
            $tmpArray['photo']      = DOL_DOCUMENT_ROOT . $noPhoto;
            $tmpArray['photo_tech'] = DOL_DOCUMENT_ROOT . $noPhoto;
        }

        $tmpArray['object_ref']      = $object->ref;
        $tmpArray['object_fk_soc']   = dol_trunc($thirdParty->name, 15, 'right', 'UTF-8', 1);
        $tmpArray['object_fk_user']  = dol_trunc($userAssign->getFullName($langs), 15, 'right', 'UTF-8', 1);
        $tmpArray['thirdparty_mail'] = $thirdParty->email;
        $tmpArray['object_track_id'] = $object->track_id;

        $category   = new Categorie($this->db);
        $categories = $category->containing($object->id, Categorie::TYPE_TICKET);
        if (!empty($categories)) {
            $allCategories = [];
            foreach ($categories as $cat) {
                $allCategories[] = $cat->label;
            }
            $tmpArray['object_tags'] = dol_trunc(implode(', ', $allCategories), 15, 'right', 'UTF-8', 1);
        } else {
            $tmpArray['object_tags'] = '';
        }

        $moreParam['tmparray'] = $tmpArray;

        return parent::write_file($objectDocument, $outputLangs, $srcTemplatePath, $hideDetails, $hideDesc, $hideRef, $moreParam);
    }
}
