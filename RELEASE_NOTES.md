# [GMAO] [1.2.1] - Conversion PDF soumise au droit de générer le document - Styles à jour après une mise à jour

Description : la conversion en PDF d'un document depuis la fiche ticket demande désormais le droit de générer ce document, et le navigateur charge les nouveaux styles et scripts après une mise à jour, sans Ctrl+F5.

**Cette version demande Saturne 23.10.1 ou supérieur.**

**À installer avant Saturne 23.11.0, ou en même temps.** Avec une version antérieure de GMAO, Saturne 23.11.0 refuserait la conversion en PDF sur la fiche ticket.

## Améliorations & corrections

### Documents

* La conversion en PDF d'un document depuis la fiche ticket demande le droit de générer ce document, comme le bouton « Générer ».

### Affichage

* Après une mise à jour de GMAO ou de Saturne, la fiche ticket charge les nouveaux styles et scripts : plus besoin de vider le cache du navigateur (Ctrl+F5).

## Testez les nouveautés maintenant

**Allez tester la fiche ticket et ses documents sur la démo : [https://demodoli.digirisk.com/](https://demodoli.digirisk.com/)**

## Comparaison des versions [1.2.0](https://github.com/Evarisk/gmao/compare/1.2.0...1.2.1) et 1.2.1

* [#100] [Assets] fix: version the assets printed by hand through saturne_asset_full_url [`148e7667`](https://github.com/Evarisk/gmao/commit/148e7667)
* [Documents] fix: génération PDF manuelle soumise au droit de générer le document [`ad5636d8`](https://github.com/Evarisk/gmao/commit/ad5636d8)
