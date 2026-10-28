# Witnesses (M7 surveillance participative)

Signalements de témoins (navire de l'État, pêcheur, caboteur, autorité portuaire) reçus par e-mail, téléphone, fax, courrier ou fiche papier. Le témoin n'accède jamais à l'application : un agent enregistre le rapport.

- Tables : `witnesses`, `witness_reports`, `reported_vessels` (deux navires au plus), `report_usabilities`, `report_investigations`, `report_decisions`, `authorized_list_distributions` (modèles dans `app/Models`).
- Actions : `RecordWitnessReport`, `SaveReportUsability`, `SaveReportInvestigation`, `LinkReportOperation`, `DecideWitnessReport`, `RecordAuthorizedListDistribution`.
- Flux : reçu, exploitable (vérifié), enquête, classement sans suite (classé) ou transmission au groupe Instruction (transmis) ; un signalement transmis propose d'ouvrir un dossier d'infraction ou une mission préremplis. L'opération INN se crée dans le module Opérations puis se lie au signalement (seule une opération INN est acceptée).
- Un navire signalé qui correspond à une immatriculation ou à un indicatif du registre est rattaché au navire et écrit un évènement dans son journal.
- Diffusion de la liste des navires autorisés : destinataire et date par version publiée.
- Permissions : `witnesses.view`, `witnesses.create` (Agent compris), `witnesses.manage` (exploitabilité, enquête, décision, diffusion : Chef d'équipe, Directeur).
- Hypothèses : H-6-8 (`docs/assumptions/phase-6.md`).
