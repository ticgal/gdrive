# GDrive - GLPI Plugin CHANGELOG
## 3.0.1 - 2026-10-21
### Security
- The Google access token is no longer stored in a browser cookie. Closing the Google popup no longer reuses the token of a previous user of the same browser.
- Configuration values are encoded before they are written into the page.
### Added
- "Attach files from Google Drive" right, in the GDrive tab of central profiles. Central profiles get it when the plugin is installed or updated. Self-service profiles no longer see the GDrive button.
### Fixed
- Uninstalling the plugin removes its configuration table and its right.

## 3.0.0 - 2026-02-04
## Added
- GLPI 11 support

## 2.0.0 - 2023-01-20
### Features
- Compatibility with GLPI 10 (#12836)
- Sign in with Google Authentication & Authorization (#13177)
- GDrive button on Assets > Documents (#12937)
- GDrive for Tickets, Problems and Changes
- Translation with Localazy, including Polish (#13373)

## 1.3.0 - 2020-07-31
### Features
- Compatibility with GLPI 9.5 

## 1.2.0 - 2019-01-17
### Features
- Compatibility with GLPI 9.4 

## 1.1.0 - 2018-08-14
### Features
- Check compatibility with GLPI 9.3 (#1089)
- Added Czech localization thanks @p-bo
- Added ru_RU localization thanks @Smiley-k

## 1.0.0 - 2018-05-31
### Features
- GDrive is a GLPi Plugin to allow file upload to tickets right from Google Drive (#896)