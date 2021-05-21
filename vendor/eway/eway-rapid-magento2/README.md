# eWAY Rapid PHP Magento 2 Module

Accept payments with eWAY using Magento 2.

## Requirements

 - Magento 2.1.0+
 - An eWAY account
 
For testing, get a free eWAY Partner account: https://www.eway.com.au/developers

## Installation

If you wish to install this module manually, you can do so using Composer.

Note: To install the extension with Composer still requires adding the extension to your 
Magneto account by "purchasing" it from the [Marketplace](https://marketplace.magento.com/eway-eway-rapid-magento2.html). 
It can take up to half an hour after adding the extension to your account for it to be 
available via Composer.

1. Log into your server and navigate to your Magento directory
2. Run the command (note, please ensure you are using the latest version) `composer require eway/eway-rapid-magento2 3.0.5`
3. Enable the module by running the folling: 
`bin/magento module:enable --clear-static-content Eway_DirectConnection Eway_SharedPage Eway_SecureFields Eway_IFrame Eway_TransparentRedirect Eway_EwayRapid`
5. Install the module by running `bin/magento setup:upgrade`
6. Run the compile command to finish: `bin/magento setup:di:compile`

## Configuration

Once installed, this module can be configured in the usual way by logging into
the Magento admin area and navigating to:
Stores > Configuration > Sales > Payment Methods > eWAY Rapid 3.1

More details are available in the eWAY Community:
https://go.eway.io/s/article/How-do-I-configure-the-eWAY-Magento-2-extension