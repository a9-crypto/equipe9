---
name: installer
description: Étape 0, kit portable. Seulement si Git n'est pas installé sur le poste. Installe Git portable et relie le dossier téléchargé en zip au dépôt GitHub du binôme.
argument-hint: <adresse HTTPS du dépôt du binôme>
disable-model-invocation: true
---

# Installer Git portable

But : rendre le poste prêt pour l'atelier quand Git n'y est pas installé, en 3 minutes.
Adresse du dépôt reçue : `$ARGUMENTS`

## Étapes

1. Vérifie l'adresse du dépôt. Elle doit commencer par `https://github.com/`.
   - Si l'adresse manque, demande-la au binôme : bouton vert **Code**, onglet **HTTPS**, sur la page GitHub de son dépôt.
2. Vérifie que Git est absent : lance `git --version`.
   - Si Git répond, n'installe rien. Indique au binôme de taper `/demarrer`.
3. Lance le script d'installation, dans PowerShell :
   `powershell -ExecutionPolicy Bypass -File outils\installer.ps1 -Depot <adresse>`
   - Préviens le binôme : une fenêtre de connexion GitHub peut s'ouvrir. Il s'y connecte avec le compte du binôme.
4. Lis la sortie du script.
   - En cas d'échec, affiche le message d'erreur exact. Demande au binôme d'appeler l'animateur.
   - Si l'extraction échoue avec « accès refusé », l'antivirus ou AppLocker bloque probablement l'archive. Demande d'appeler l'animateur.

## Message final au binôme

1. Git est installé dans le profil de l'utilisateur, sans droits admin.
2. Le dossier est relié au dépôt GitHub du binôme.
3. La consigne : « Fermez complètement l'app Claude, puis rouvrez-la. Ouvrez une nouvelle session sur ce dossier et tapez /demarrer. »
