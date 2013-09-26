# Cm_Saas

This module aims to make building a scalable SaaS-type service on Magento easy.

## Features

The Cm_Saas module provides the following (planned) features out of the box:

* Adds an "saas" area that is in addition to the "frontend" and "adminhtml" for your SaaS clients
  to use while leaving adminhtml for your internal admins and frontend for your public site.
* SaaS clients are implemented by using a separate Store Scope for each client.
* Each SaaS client may have their own database!
** The core Magento functionality (Mage_* modules) all use a single database, but your own modules
   can use one database-per-client seamlessly.
** Can add connections via the admin panel and choose or create your own assignment algorithm.

## Modifications

* Website contains the following additional fields:
** account_id
** db_id

