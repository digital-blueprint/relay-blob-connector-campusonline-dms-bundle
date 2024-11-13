# Changelog

## Unreleased

* Add workaround for clients uploading files with missing 'filename' directive in 'Content-Disposition'
header of multipart/form-data POST
* Add unit tests

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