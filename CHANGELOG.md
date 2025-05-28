# Changelog

## Unreleased

- Update the blob library and adapt to the new interface signature
- Cleanup blob test files (from storage)

## v0.2.0

- Remove relay-blob-bundle (FileApi) dependency and replace by relay-blob-library, which can be configured to access
  blob locally (via PHP) or remotely (via HTTP)

## v0.1.11

* Fix broken error responses with api-platform 4.1

## v0.1.10

* Drop support for PHP 8.1
* Add support for api-platform 4.1 (and drop for <3.4)

## v0.1.9

* Update core and add missing parent constructor calls

## v0.1.8

* Update to phpstan v2
* Adjust for blob test API changes

## v0.1.7

* Update core and authz policies
* Add unit tests
* Ensure (empty) metadata is encoded as object (and displayed as object in OpenAPI)

## v0.1.5

* Adapt to CAMPUSonline DMS API spec version 1.5.0

## v0.1.4
 
* Fix POST /co-dms-api/api/documents/{uid}/version by creating a temporary fake UpdatedFile

## v0.1.3

* Add ROLE_USER policy to configure who is granted access to the API

## v0.1.2

* Integrate Blob File API

## v0.1.0

* Routes and entities all set up