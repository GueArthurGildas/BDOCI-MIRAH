# Mrep

Miroir en lecture seule de l'application MREP (module M6 de la spécification) : DPEP, décisions d'entrée, escales, notifications aux États et organismes, refus GIES, ciblage. Aucun écran de saisie : les données arrivent par la synchronisation du connecteur `dpep-mrep`.

- Tables : `mrep_dpeps`, `mrep_entry_decisions`, `mrep_port_calls`, `mrep_notifications`, `mrep_gies_refusals`, `mrep_targetings` (modèles dans `app/Models`).
- Écrans : liste et fiche d'une DPEP (UC13), notifications (UC14), refus GIES, ciblage.
- Synchronisation : action `Modules\Administration\Actions\SynchroniseConnector` (planifiée toutes les heures par `connectors:sync`, ou lancée depuis la fiche du connecteur) ; connecteur `App\Support\Connectors\Sources\MrepConnector`.
- Permissions : `mrep.view` (Agent, Chef d'équipe, Directeur).
- Hypothèses : H-7-1, H-7-5, H-7-6 (`docs/assumptions/phase-7.md`).
