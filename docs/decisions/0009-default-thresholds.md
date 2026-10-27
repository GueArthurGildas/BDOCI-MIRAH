# 0009 — Seuils par défaut

**Statut** : recommandation acceptée le 2026-10-27 (D9). À appliquer en phases 3b et 4a.

## Contexte

- Spécification : silence VMS de **4 h** (`regle_alerte.seuil_heures` par défaut 4.00, `parametre_seuil` `silence_vms_heures`) et alertes de licence à **30, 60 et 90 jours**, paramétrables (RG07, vue `v_licences_a_echeance` à 90 jours).
- Code actuel (`config/surveillance.php`) : `beacon_silence_threshold_hours` = 2 h et `license_expiry_warning_days` = 30 jours, donc un seul seuil ; ces valeurs alimentent le tableau de bord, la fiche navire et les alertes planifiées.
- L'Admin peut déjà modifier ces seuils depuis Administration › Paramètres (service `Settings`, généralisable en `parametre_seuil`).

## Décision

Les valeurs par défaut sont celles de la spécification : 4 h pour le silence VMS, 30, 60 et 90 jours pour les licences. Elles restent modifiables dans Paramètres.

## Conséquences

- Positives : une seule valeur de référence, conforme à la spécification ; seuils réglables sans changer le code.
- Négatives : passer le silence de 2 h à 4 h change l'état affiché des balises et le décompte du tableau de bord ; les données de démonstration (balises muettes de `DemoDataSeeder`) et certains tests devront être ajustés ; trois seuils de licence exigent de remplacer l'alerte unique actuelle.
- Travail induit : silence 4 h dans la règle d'alerte de la phase 3b ; alertes 30/60/90 jours en phase 4a.

## Alternatives écartées

- **Garder 2 h et 30 jours** : conserve deux valeurs en circulation (code et spécification).
