# GDrive - GLPI Plugin CHANGELOG
## 4.0.0-beta.1 - 2026-10-08
### Added
- GLPI 12 support
- The configuration table is removed when the plugin is uninstalled
- Google Docs, Sheets, Slides and Drawings can now be attached; they are exported as .docx, .xlsx, .pptx and .png

### Changed
- Requires GLPI 12.0.0 or later; GLPI 11 is no longer supported
- The separate "Sign in with Google" button is removed; "Select file" now starts the Google sign-in directly
- The GDrive button and the configuration form follow the native GLPI look, with shorter field labels

### Fixed
- The error message is now shown when a file cannot be downloaded from Google Drive
- Failed downloads are no longer uploaded as if they were the selected file
- The status message is now shown next to the button that was used when there are several forms on the page

### Security
- Configuration values are escaped before being printed in the page
- The Google access token is no longer stored in a cookie sent to the GLPI server
- Saving the plugin configuration now requires re-entering the password, like the GLPI configuration

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