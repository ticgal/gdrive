# Gdrive

<img src="https://raw.githubusercontent.com/ticgal/gdrive/multimedia/gdrive.png" alt="GDrive Logo" height="250px" width="250px" class="js-lazy-loaded">

[![License](https://img.shields.io/badge/License-GNU%20AGPLv3-blue.svg?style=flat-square)](https://github.com/ticgal/actualtime/blob/master/LICENSE)
[![Twitter](https://img.shields.io/badge/Twitter-TICgal-blue.svg?style=flat-square)](https://twitter.com/ticgalcom)
[![Web](https://img.shields.io/badge/Web-TICgal-blue.svg?style=flat-square)](https://tic.gal/en/glpi-plugins-and-extension/gdrive/)
[![Localazy](https://img.shields.io/badge/Translate-Localazy-cyan)](https://localazy.com/p/gdrive#translations)

Gdrive is Google Drive integration plugin for GLPI. 
You can transfer files from the Google storage to most of the GLPI Assets.

### Setup
Install like any other GLPI plugin from Marketplace or download and install in the plugin folder.

You will additionally need a Google Cloud project with the Google Picker API and the Google Drive API enabled, and two credentials from it:
- a **Browser API key**;
- an **OAuth client ID** of type *Web application*.

Enter them, together with the **project number** (*IAM & Admin > Settings*), in *Setup > General > GDrive*. The button stays hidden until the three values are set.

The key and the client ID are sent to the browser of every user who sees the button, so restrict them in the Google Cloud Console:
- API key: *Application restrictions* = HTTP referrers, with your GLPI URL (e.g. `https://glpi.example.com/*`); *API restrictions* = Google Picker API only.
- OAuth client: *Authorized JavaScript origins* = your GLPI origin (e.g. `https://glpi.example.com`).

### Rights
The button is shown only to profiles with the *Attach files from Google Drive* right (*Administration > Profiles > GDrive*). Central profiles get it when the plugin is installed or updated. Profiles created later start without it. Self-service profiles never see the button.
### How to use
Nice and easy:
1. Click on the Gdrive button
2. Select one or more files
3. Get them added to GLPI
### Translate
Please, click on the Localazy badge to translate it into your language. We will add it on Gdrive next release. 
