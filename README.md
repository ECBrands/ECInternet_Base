# Magento2 Module ECInternet Base
``ecinternet/base - 1.3.4.0``

- [Requirements](#requirements-header)
- [Overview](#overview-header)
- [Installation](#installation-header)
- [Configuration](#configuration-header)
- [Specifications](#specifications-header)
- [Attributes](#attributes-header)
- [Notes](#notes-header)
- [Version History](#version-history-header)

## Requirements

## Overview
The EC Internet Base extension serves as the core extension for all other EC Internet extensions to build on.

## Installation
- Unzip the zip file in `app/code/ECInternet`
- Enable the module by running `php bin/magento module:enable ECInternet_Base`
- Apply database updates by running `php bin/magento setup:upgrade`
- Flush the cache by running `php bin/magento cache:flush`

## Configuration

## Specifications
- Adds `SyncComplete()` API call to fire off other extension's events once a sync has been completed.

## Attributes
- `Customer`
  - Customer Number (`customer_number`)
- `CustomerAddress`
  - Ship-To Id (`ship_to_id`)

## Notes

## Version History
- 1.3.2.0 - Updated UpgradeData to not show Customer attributes in grid.  This could lead to compilation errors for some databases.
- 1.3.1.0 - Remove unused `FolderCleanDevoCommand` class.
