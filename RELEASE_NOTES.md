# [GMAO] [1.2.0] - Entrepôt des lots et numéros de série - Compatible Dolibarr 23 et 24

Description : Chaque lot ou numéro de série indique l'entrepôt où se trouve l'équipement, sur sa fiche et dans la liste des lots. GMAO s'installe sur Dolibarr 23 et 24, où la génération des documents de ticket fonctionne de nouveau, refuse de s'activer sur un Saturne trop ancien, et cette version corrige plusieurs erreurs de la fiche ticket et des installations où les modules ne sont pas dans custom/.

**Cette version demande Saturne 23.10.1 ou supérieur, et Dolibarr 23 ou 24.**

## Nouvelles fonctionnalités et innovations

### Lots et numéros de série : l'entrepôt de chaque équipement

* Nouveau champ **Entrepôt** sur la fiche d'un lot ou d'un numéro de série, et en colonne dans la liste des lots, marqué du picto GMAO : on sait où se trouve chaque équipement suivi.

<img src="https://raw.githubusercontent.com/nicolas-eoxia/gmao/refs/heads/assets/release-1.2.0/lot-entrepot.png" width="70%" alt="Fiche d'un lot : champ Entrepôt marqué du picto GMAO">

### Dolibarr 23 et 24

* GMAO s'installe sur **Dolibarr 23 et 24**, et la génération des documents depuis la fiche ticket (fiches client et technicien, étiquette 89 × 36 mm…) fonctionne de nouveau sur Dolibarr 24.
* GMAO refuse de s'activer sur un Saturne trop ancien et indique la version demandée, au lieu de s'activer puis de tomber en erreur.

## Améliorations & corrections

### Fiche ticket

* Un utilisateur sans le droit « Supprimer les tickets » voit de nouveau la fiche ticket en entier : elle s'arrêtait sur une erreur fatale au niveau des documents.
* Générer ou supprimer un document de ticket n'écrit plus d'avertissement dans le journal PHP, ni pour GMAO, ni pour Digirisk.
* Annuler un formulaire d'un autre module (par exemple une enveloppe DoliLetter) ne fait plus tomber les pages en erreur fatale.

### Installation et événements

* GMAO fonctionne quand les modules ne sont pas installés dans custom/ : liens, images et inclusion de Saturne suivent le dossier réel.
* Quand Saturne est introuvable, le message d'erreur dit où l'installer, au lieu d'un simple « Include of saturne main fails ».
* Les événements de l'agenda créés par GMAO affichent son picto, et un événement sur un objet qui n'est pas un objet Dolibarr n'écrit plus d'avertissement.
* Le champ Entrepôt est bien rattaché aux lots, et le message « fichier introuvable » indique le fichier concerné.
* La génération des catégories de tickets passe dans les outils d'administration.

### Livraison

* Le zip de la version ne contient plus les fichiers de développement, et il est construit et publié automatiquement à chaque version.

## Testez les nouveautés maintenant

**Allez tester GMAO sur la démo : https://demodoli.digirisk.com/**

## Comparaison des versions [1.1.0](https://github.com/Evarisk/gmao/compare/1.1.0...1.2.0) et 1.2.0

* [#95] [Ticket] fix: droits de ticket lus par hasRight() sur la fiche ticket [`aae034cd`](https://github.com/Evarisk/gmao/commit/aae034cd)
* [#93] [Release] ci: workflow de release et zip sans fichiers de développement [`a0f40195`](https://github.com/Evarisk/gmao/commit/a0f40195)
* [#91] [Ticket] fix: droits de documents_action.tpl.php à 0 pour l'action d'un autre module [`e0111d4d`](https://github.com/Evarisk/gmao/commit/e0111d4d)
* [#89] [Saturne] fix: message explicite quand l'amorçage ne trouve pas Saturne [`cf475944`](https://github.com/Evarisk/gmao/commit/cf475944)
* [#87] [Trigger] fix: plus d'avertissement sur un événement dont l'objet n'est pas un CommonObject [`eb5ce04a`](https://github.com/Evarisk/gmao/commit/eb5ce04a)
* [#85] [Trigger] fix: picto gmao_color@gmao, l'image gmao@gmao n'existe pas [`0ac02a4c`](https://github.com/Evarisk/gmao/commit/0ac02a4c)
* [#83] [Hook] fix: doActions accepte $action nul [`7646eb18`](https://github.com/Evarisk/gmao/commit/7646eb18)
* [#81] [Module] fix: dol_buildpath('/custom/…') sans custom/ [`29b21b5a`](https://github.com/Evarisk/gmao/commit/29b21b5a)
* [#81] [Module] fix: URL construite avec DOL_URL_ROOT . '/custom/…' [`f822ace6`](https://github.com/Evarisk/gmao/commit/f822ace6)
* [#79] [CI] ci: actions GitHub en Node 24 (checkout@v7, cache@v6) [`f56697ba`](https://github.com/Evarisk/gmao/commit/f56697ba)
* [#77] [CI] ci: contrôle « API Saturne publiée » dans la CI qualité [`a5be03a3`](https://github.com/Evarisk/gmao/commit/a5be03a3)
* [#75] [CI] ci: retrait du workflow Generate Auto Changelog [`9d075222`](https://github.com/Evarisk/gmao/commit/9d075222)
* [#73] [Mod] feat: version minimale de Saturne, activation refusee sur un Saturne trop ancien [`c229ce13`](https://github.com/Evarisk/gmao/commit/c229ce13)
* [#71] [CI] ci: refuser les inclusions relatives de Saturne [`01befc26`](https://github.com/Evarisk/gmao/commit/01befc26)
* [#71] [Saturne] fix: inclure Saturne par dol_include_once / dol_buildpath [`9a753378`](https://github.com/Evarisk/gmao/commit/9a753378)
* [#69] [Module] fix: warehouse extrafield elementtype without trailing spaces [`098bc2b6`](https://github.com/Evarisk/gmao/commit/098bc2b6)
* [#67] [CI] rework: elaguer les entrees mortes de la baseline [`ab491a02`](https://github.com/Evarisk/gmao/commit/ab491a02)
* [#65] [CI] rework: scanner le socle par dossier plutot que l exclure par morceaux [`3c58913e`](https://github.com/Evarisk/gmao/commit/3c58913e)
* [Trigger] fix: redeclarer le type de $version [`f2c99dcc`](https://github.com/Evarisk/gmao/commit/f2c99dcc)
* [#63] [CI] fix: exclure les bouchons phan de saturne de l'analyse [`544292cd`](https://github.com/Evarisk/gmao/commit/544292cd)
* [CI] fix: ajouter expensereport/class aux dossiers vus par PHPStan [`b079b0d9`](https://github.com/Evarisk/gmao/commit/b079b0d9)
* [#61] [CI] feat: PHPStan, lint PHP et parite des langues [`a516abb2`](https://github.com/Evarisk/gmao/commit/a516abb2)
* [#59] [Module] rework: bornes de version Dolibarr 23 minimum, 24 maximum [`e9039b67`](https://github.com/Evarisk/gmao/commit/e9039b67)
* [#57] [Documents] fix: generation de documents cassee sur Dolibarr 24 [`ae074bdd`](https://github.com/Evarisk/gmao/commit/ae074bdd)
* [#55] [i18n] fix: passer le chemin au message ErrorFileNotFound [`2d47020e`](https://github.com/Evarisk/gmao/commit/2d47020e)
* [#50] [Extrafield] add: extrafield for inventory on product lot [`47d42972`](https://github.com/Evarisk/gmao/commit/47d42972)
* [#27] [Admin] fix: wrong check categorie [`e4aea72d`](https://github.com/Evarisk/gmao/commit/e4aea72d)
* [#27] [Admin] fix: move generate_ticket_categories in admin tools [`03fbc7fb`](https://github.com/Evarisk/gmao/commit/03fbc7fb)
